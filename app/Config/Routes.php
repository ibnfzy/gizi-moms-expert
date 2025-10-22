<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->match(['get', 'post'], 'login', 'AuthController::login');
$routes->get('logout', 'AuthController::logout');
$routes->get('/pakar/dashboard', 'PakarDashboardController::index', ['filter' => 'pakarfilter']);
$routes->get('/pakar/consultations', 'PakarConsultationController::index', ['filter' => 'pakarfilter']);
$routes->get('/admin/dashboard', 'AdminDashboardController::index', ['filter' => 'adminfilter']);
$routes->get('/admin/rules', 'AdminRulesController::index', ['filter' => 'adminfilter']);

$routes->group('api', ['namespace' => 'App\Controllers\Api'], static function ($routes) {
    $routes->post('auth/login', 'AuthController::login');
    $routes->post('inference/run', 'InferenceController::run');

    $routes->group('', ['filter' => 'auth'], static function ($routes) {
        $routes->group('auth', static function ($routes) {
            $routes->post('register', 'AuthController::register', ['filter' => 'role:admin']);
            $routes->get('me', 'AuthController::me');
        });

        $routes->group('', ['filter' => 'role:pakar,ibu'], static function ($routes) {
            $routes->get('mothers', 'MotherController::index');
            $routes->get('mothers/(:num)', 'MotherController::show/$1');

            $routes->post('consultations', 'ConsultationController::create');
            $routes->get('consultations/(:num)/messages', 'MessageController::index/$1');

            $routes->post('messages', 'MessageController::create');
        });
    });

    $routes->group('rules', static function ($routes) {
        $routes->get('/', 'RuleController::index');
        $routes->post('/', 'RuleController::create');
        $routes->put('(:num)', 'RuleController::update/$1');
        $routes->delete('(:num)', 'RuleController::delete/$1');
    });
});
