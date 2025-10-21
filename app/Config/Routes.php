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
