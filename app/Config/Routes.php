<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->match(['get', 'post'], 'login', 'AuthController::login');
$routes->get('logout', 'AuthController::logout');
$routes->get('/pakar/dashboard', 'PakarDashboardController::index', ['filter' => 'pakarfilter']);
$routes->get('/admin/dashboard', 'AdminDashboardController::index', ['filter' => 'adminfilter']);

$routes->group('api', ['namespace' => 'App\Controllers\Api'], static function ($routes) {
    $routes->post('auth/login', 'AuthController::login');
    $routes->post('inference/run', 'InferenceController::run');

    $routes->group('auth', ['filter' => 'auth'], static function ($routes) {
        $routes->post('register', 'AuthController::register', ['filter' => 'role:admin']);
        $routes->get('me', 'AuthController::me');
    });
});
