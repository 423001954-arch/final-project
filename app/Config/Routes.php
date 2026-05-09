<?php

// app/Config/Routes.php
//
// RBAC Route Organization:
//   Public              → no filter
//   Any logged-in user  → filter: 'auth'
//   Student only        → filter: ['auth', 'student']
//   Teacher + Admin     → filter: ['auth', 'teacher']
//   Admin only          → filter: ['auth', 'admin']
//
// Filters stack as an array — CI4 4.5+ requires array syntax for multiple filters.
// AuthFilter always runs first to confirm a session exists,
// then the role filter checks the specific permission level.

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// ════════════════════════════════════════════════════════════
//  PUBLIC ROUTES — no authentication required
// ════════════════════════════════════════════════════════════
$routes->get('/',          'AuthController::login');
$routes->get('/login',     'AuthController::login');
$routes->post('/login',    'AuthController::loginProcess');
$routes->get('/register',  'AuthController::register');
$routes->post('/register', 'AuthController::registerProcess');
$routes->get('/logout',    'AuthController::logout');
$routes->get('/unauthorized', 'AuthController::unauthorized');

// ════════════════════════════════════════════════════════════
//  STUDENT ROUTES — role: student only
// ════════════════════════════════════════════════════════════
$routes->group('', ['filter' => ['auth', 'student']], function ($routes) {

    $routes->get('/student/dashboard', 'StudentController::dashboard');
    $routes->get('/profile',           'ProfileController::show');
    $routes->get('/profile/edit',      'ProfileController::edit');
    $routes->post('/profile/update',   'ProfileController::update');

});

// ════════════════════════════════════════════════════════════
//  TEACHER ROUTES — role: teacher OR admin
//  TeacherFilter allows both roles
// ════════════════════════════════════════════════════════════
$routes->group('', ['filter' => ['auth', 'teacher']], function ($routes) {

    $routes->get('/dashboard',            'Home::index');
    $routes->get('/students',             'StudentManagementController::index');
    $routes->get('/students/create',      'StudentInfo::create');
    $routes->post('/students/store',      'StudentInfo::store');
    $routes->get('/students/show/(:num)', 'StudentManagementController::show/$1');

});

// ════════════════════════════════════════════════════════════
//  ADMIN ROUTES — role: admin only
// ════════════════════════════════════════════════════════════
$routes->group('admin', ['filter' => ['auth', 'admin']], function ($routes) {

    // Role Management
    $routes->get('roles',                'Admin\RoleController::index');
    $routes->get('roles/create',         'Admin\RoleController::create');
    $routes->post('roles/store',         'Admin\RoleController::store');
    $routes->get('roles/edit/(:num)',    'Admin\RoleController::edit/$1');
    $routes->post('roles/update/(:num)', 'Admin\RoleController::update/$1');
    $routes->get('roles/delete/(:num)',  'Admin\RoleController::delete/$1');

    // User Role Assignment
    $routes->get('users',                       'Admin\UserAdminController::index');
    $routes->post('users/assign-role/(:num)',    'Admin\UserAdminController::assignRole/$1');

});

// ════════════════════════════════════════════════════════════
//  API v1 — token-authenticated JSON endpoints
//
//  Public:    POST /api/v1/auth/token  (issue token)
//  Protected: DELETE /api/v1/auth/token, GET /api/v1/students(/{id})
//
//  Header: Authorization: Bearer <token>
// ════════════════════════════════════════════════════════════

// Issue token — no auth filter needed here
$routes->post('api/v1/auth/login', 'Api\AuthController::issueToken');
// Protected API routes
$routes->group('api/v1', ['filter' => 'api_auth'], function ($routes) {

    // Auth
    $routes->delete('auth/token', 'Api\AuthController::revokeToken');

    // Students resource
    $routes->get('students',       'Api\StudentsController::index');
    $routes->get('students/(:num)', 'Api\StudentsController::show/$1');

});

// ════════════════════════════════════════════════════════════
//  ITEMS CRUD — teacher + admin
// ════════════════════════════════════════════════════════════
$routes->group('', ['filter' => ['auth', 'teacher']], function ($routes) {

    $routes->get('/items',                'ItemController::index');
    $routes->get('/items/create',         'ItemController::create');
    $routes->post('/items/store',         'ItemController::store');
    $routes->get('/items/show/(:num)',    'ItemController::show/$1');
    $routes->get('/items/edit/(:num)',    'ItemController::edit/$1');
    $routes->post('/items/update/(:num)', 'ItemController::update/$1');
    $routes->get('/items/delete/(:num)',  'ItemController::delete/$1');

});

$routes->get('/makehash', function () {
    echo password_hash('password123', PASSWORD_DEFAULT);
});