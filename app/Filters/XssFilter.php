<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class XssFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (method_exists($request, 'setGlobal')) {
            $request->setGlobal('post', $this->clean($request->getPost() ?? []));
            $request->setGlobal('get', $this->clean($request->getGet() ?? []));
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }

    private function clean(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(fn ($item) => $this->clean($item), $value);
        }

        if (! is_string($value)) {
            return $value;
        }

        return trim(strip_tags($value));
    }
}
