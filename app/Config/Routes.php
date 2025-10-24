<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->match(['get', 'post'], 'login', 'AuthController::login');
$routes->get('logout', 'AuthController::logout');
$routes->get('/pakar/dashboard', 'PakarDashboardController::index', ['filter' => 'pakarfilter']);
$routes->get('/pakar/dashboard/data', 'PakarDashboardController::data', ['filter' => 'pakarfilter']);
$routes->get('/pakar/dashboard/mothers/(:num)', 'PakarDashboardController::motherDetail/$1', ['filter' => 'pakarfilter']);
$routes->get('/pakar/dashboard/mothers/close', 'PakarDashboardController::clearDetail', ['filter' => 'pakarfilter']);
$routes->get('/pakar/consultations', 'PakarConsultationController::index', ['filter' => 'pakarfilter']);
$routes->get('/pakar/consultations/(:num)', 'PakarConsultationController::conversation/$1', ['filter' => 'pakarfilter']);
$routes->post('/pakar/consultations/(:num)/messages', 'PakarConsultationController::sendMessage/$1', ['filter' => 'pakarfilter']);
$routes->get('/admin/dashboard', 'AdminDashboardController::index', ['filter' => 'adminfilter']);
$routes->get('/admin/rules', 'AdminRulesController::index', ['filter' => 'adminfilter']);
$routes->get('/admin/mothers', 'AdminMotherController::index', ['filter' => 'adminfilter']);
$routes->get('/admin/users', 'AdminUserController::index', ['filter' => 'adminfilter']);

$routes->group('api', ['namespace' => 'App\Controllers\Api'], static function ($routes) {
    $routes->post('auth/login', 'AuthController::login');
    $routes->post('inference/run', 'InferenceController::run');

        $routes->group('', ['filter' => 'auth'], static function ($routes) {
            $routes->group('auth', static function ($routes) {
                $routes->post('register', 'AuthController::register', ['filter' => 'role:admin']);
                $routes->get('me', 'AuthController::me');
            });

            $routes->get('stats', 'StatsController::index', ['filter' => 'role:admin']);

            $routes->get('schedules', 'ScheduleController::index', ['filter' => 'role:pakar,ibu']);
            $routes->post('schedules', 'ScheduleController::create', ['filter' => 'role:pakar']);
            $routes->put('schedules/(:num)', 'ScheduleController::update/$1', ['filter' => 'role:pakar']);
            $routes->put('schedules/(:num)/attendance', 'ScheduleController::updateAttendance/$1', ['filter' => 'role:ibu']);
            $routes->put('schedules/(:num)/evaluation', 'ScheduleController::updateEvaluation/$1', ['filter' => 'role:pakar']);
            $routes->get('schedules/reminder-due', 'ScheduleController::reminderDue', ['filter' => 'role:pakar,admin']);

            $routes->get('notifications', 'NotificationController::index', ['filter' => 'role:pakar,ibu,admin']);
            $routes->post('notifications', 'NotificationController::create', ['filter' => 'role:pakar,admin']);

            $routes->group('', ['filter' => 'role:pakar,ibu'], static function ($routes) {
                $routes->get('mothers', 'MotherController::index');
                $routes->get('mothers/(:num)', 'MotherController::show/$1');
                $routes->get('inference/latest', 'InferenceController::latest');

                $routes->get('consultations', 'ConsultationController::index');
                $routes->post('consultations', 'ConsultationController::create');
                $routes->get('consultations/(:num)/messages', 'MessageController::index/$1');

                $routes->post('messages', 'MessageController::create');
            });

        $routes->group('admin', ['filter' => 'role:admin'], static function ($routes) {
            $routes->group('mothers', static function ($routes) {
                $routes->get('/', 'AdminMotherController::index');
                $routes->get('(:num)', 'AdminMotherController::show/$1');
                $routes->put('(:num)/email', 'AdminMotherController::updateEmail/$1');
                $routes->put('(:num)/password', 'AdminMotherController::updatePassword/$1');
                $routes->delete('(:num)', 'AdminMotherController::delete/$1');
            });
            $routes->group('users', static function ($routes) {
                $routes->get('/', 'AdminUserController::index');
                $routes->post('/', 'AdminUserController::create');
                $routes->get('(:num)', 'AdminUserController::show/$1');
                $routes->put('(:num)', 'AdminUserController::update/$1');
                $routes->put('(:num)/password', 'AdminUserController::updatePassword/$1');
                $routes->delete('(:num)', 'AdminUserController::delete/$1');
            });
        });
    });

    $routes->group('rules', static function ($routes) {
        $routes->get('/', 'RuleController::index');
        $routes->post('/', 'RuleController::create');
        $routes->put('(:num)', 'RuleController::update/$1');
        $routes->delete('(:num)', 'RuleController::delete/$1');
    });
});
