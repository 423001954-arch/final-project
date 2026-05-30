<?php

namespace App\Controllers\Api;

use App\Models\ApiTokenModel;
use App\Models\UserModel;

class AuthController extends BaseApiController
{
    private const TOKEN_TTL = 86400;

    public function issueToken()
    {
        $email = $this->request->getJsonVar('email') ?? $this->request->getPost('email');
        $password = $this->request->getJsonVar('password') ?? $this->request->getPost('password');

        if (empty($email) || empty($password)) {
            return $this->badRequest('email and password are required.');
        }

        $userModel = new UserModel();
        $user = $userModel->findByEmail($email);

        if (! $user || ! password_verify($password, $user['password'])) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Invalid credentials.',
                ]);
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + self::TOKEN_TTL);

        (new ApiTokenModel())->createToken($user['id'], $token, $expiresAt);

        return $this->created([
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role_id' => $user['role_id'],
            ],
        ], 'Token issued.');
    }

    public function revokeToken()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        $token = trim(substr($authHeader, 7));

        (new ApiTokenModel())->deleteByToken($token);

        return $this->ok(null, 'Token revoked.');
    }
}
