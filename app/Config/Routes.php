<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// 主頁
$routes->get('/', 'Home::index');

// API Key 管理頁面
$routes->get('admin/api-keys', 'Admin\ApiKeys::index');
$routes->post('admin/api-keys', 'Admin\ApiKeys::create');

// 公開 API (不需要認證，供前端頁面使用)
$routes->group('api', ['namespace' => 'App\Controllers\Api'], function ($routes) {
    $routes->get('shoes', 'ShoesController::index');
    $routes->get('shoes/(:num)', 'ShoesController::show/$1');
});

// API 路由群組
$routes->group('api/v1', ['namespace' => 'App\Controllers\Api', 'filter' => 'apikey'], function ($routes) {
    // 健康檢查（無需認證）
    $routes->get('health/database', 'HealthController::database');
    
    // 商品管理 API
    $routes->post('shoes/check-status', 'ShoesApiController::checkStatus');
    $routes->get('shoes/codes', 'ShoesApiController::getCodes');
    $routes->post('shoes/batch', 'ShoesApiController::batchCreate');
    $routes->put('shoes/batch', 'ShoesApiController::batchUpdate');
    $routes->delete('shoes/clear/(:segment)', 'ShoesApiController::clearTable/$1');
    
    // RESTful CRUD
    $routes->post('shoes', 'ShoesApiController::create');
    $routes->put('shoes/(:segment)', 'ShoesApiController::update/$1');
    $routes->delete('shoes/(:segment)', 'ShoesApiController::delete/$1');
    
    // 相容舊版 AJAX 端點（如果需要）
    $routes->get('shoes/db-info', 'ShoesController::dbInfo');
    $routes->get('shoes/table-content', 'ShoesController::tableContent');
});
