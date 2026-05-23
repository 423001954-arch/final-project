<?php

namespace App\Controllers\Api;

use App\Services\RoleAccess;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * BaseApiController
 *
 * All API controllers extend this class.
 * Provides JSON response helpers and a reference to the authenticated user.
 */
class BaseApiController extends Controller
{
    /** @var array|null  Populated by ApiAuthFilter on protected routes */
    protected ?array $apiUser = null;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);

        // Pull the user that ApiAuthFilter attached to the request (may be null on public routes)
        $this->apiUser = $request->apiUser ?? null;
    }

    // ── Response helpers ──────────────────────────────────────────────────────

    protected function ok(mixed $data = null, string $message = 'OK'): ResponseInterface
    {
        return $this->response
            ->setStatusCode(200)
            ->setJSON(['status' => 'success', 'message' => $message, 'data' => $data]);
    }

    protected function created(mixed $data = null, string $message = 'Created'): ResponseInterface
    {
        return $this->response
            ->setStatusCode(201)
            ->setJSON(['status' => 'success', 'message' => $message, 'data' => $data]);
    }

    protected function notFound(string $message = 'Not found'): ResponseInterface
    {
        return $this->response
            ->setStatusCode(404)
            ->setJSON(['status' => 'error', 'message' => $message]);
    }

    protected function badRequest(string $message = 'Bad request', mixed $errors = null): ResponseInterface
    {
        return $this->response
            ->setStatusCode(400)
            ->setJSON(['status' => 'error', 'message' => $message, 'errors' => $errors]);
    }

    protected function validationFailed(mixed $errors): ResponseInterface
    {
        return $this->response
            ->setStatusCode(422)
            ->setJSON(['status' => 'error', 'message' => 'Validation failed.', 'errors' => $errors]);
    }

    protected function forbidden(string $message = 'Forbidden'): ResponseInterface
    {
        return $this->response
            ->setStatusCode(403)
            ->setJSON(['status' => 'error', 'message' => $message]);
    }

    protected function conflict(string $message = 'Conflict'): ResponseInterface
    {
        return $this->response
            ->setStatusCode(409)
            ->setJSON(['status' => 'error', 'message' => $message]);
    }

    protected function paginated(array $rows, mixed $pager, string $message = 'OK'): ResponseInterface
    {
        return $this->ok([
            'items' => $rows,
            'pagination' => [
                'current_page' => $pager->getCurrentPage(),
                'per_page'     => $pager->getPerPage(),
                'total'        => $pager->getTotal(),
                'page_count'   => $pager->getPageCount(),
            ],
        ], $message);
    }

    protected function requestPayload(): array
    {
        $payload = $this->request->getJSON(true);

        if (is_array($payload)) {
            return $this->cleanPayload($payload);
        }

        return $this->cleanPayload($this->request->getPost() ?: []);
    }

    protected function requestedPerPage(int $default = 15, int $maximum = 100): int
    {
        $perPage = (int) ($this->request->getGet('per_page') ?? $default);

        return max(1, min($perPage, $maximum));
    }

    protected function requireSupplyChainAccess(): ?ResponseInterface
    {
        $role = $this->apiUser['role_name'] ?? null;

        if (! RoleAccess::canOperateSupplyChain($role)) {
            return $this->forbidden('Supply chain API access requires Manager or SuperAdmin access.');
        }

        return null;
    }

    private function cleanPayload(array $payload): array
    {
        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                $payload[$key] = $this->cleanPayload($value);
                continue;
            }

            if (is_string($value)) {
                $payload[$key] = trim(strip_tags($value));
            }
        }

        return $payload;
    }
}
