<?php

namespace Config;

use App\Filters\AdminFilter;
use App\Filters\ApiAuthFilter;
use App\Filters\AuthFilter;
use App\Filters\ManagerFilter;
use App\Filters\StaffFilter;
use App\Filters\XssFilter;
use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\SecureHeaders;

class Filters extends BaseConfig
{
    public array $aliases = [
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        'auth'          => AuthFilter::class,
        'staff'         => StaffFilter::class,
        'manager'       => ManagerFilter::class,
        'superadmin'    => AdminFilter::class,
        'xss'           => XssFilter::class,
        'api_auth'      => ApiAuthFilter::class,
    ];

    public array $required = [
        'before' => [],
        'after'  => ['toolbar'],
    ];

    public array $globals = [
        'before' => [
            // Added explicit exceptions for Postman testing routes
            'csrf' => [
                'except' => [
                    'api/*',
                    'login',
                    'register',
                    'supply/*'
                ]
            ],
            'xss',
        ],
        'after'  => [
            'secureheaders',
        ],
    ];

    public array $methods = [];
    public array $filters = [];
}