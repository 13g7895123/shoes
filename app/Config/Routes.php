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

// API 日志管理頁面
$routes->get('admin/api-logs', 'Admin\ApiLogs::index');
$routes->get('admin/api-logs/detail/(:num)', 'Admin\ApiLogs::detail/$1');
$routes->post('admin/api-logs/clean', 'Admin\ApiLogs::clean');
$routes->get('admin/api-logs/export', 'Admin\ApiLogs::export');

// 執行歷史管理頁面
$routes->get('admin/execution-history', 'Admin\ExecutionHistory::index');
$routes->get('admin/execution-history/detail/(:segment)', 'Admin\ExecutionHistory::detail/$1');
$routes->post('admin/execution-history/rollback/(:segment)', 'Admin\ExecutionHistory::rollback/$1');

// 公開 API (不需要認證，供前端頁面使用)
$routes->group('api', ['namespace' => 'App\Controllers\Api', 'filter' => 'apilogger'], function ($routes) {
    $routes->get('shoes', 'ShoesController::index');
    $routes->get('shoes/(:num)', 'ShoesController::show/$1');
});

// API 路由群組
$routes->group('api/v1', ['namespace' => 'App\Controllers\Api', 'filter' => ['apikey', 'apilogger']], function ($routes) {
    // 健康檢查（無需認證）
    $routes->get('health/database', 'HealthController::database');
    
    // 商品管理 API
    $routes->post('shoes/check-status', 'ShoesApiController::checkStatus');
    $routes->get('shoes/codes', 'ShoesApiController::getCodes');
    $routes->post('shoes/batch', 'ShoesApiController::batchCreate');
    $routes->put('shoes/batch', 'ShoesApiController::batchUpdate');
    $routes->delete('shoes/clear/(:segment)', 'ShoesApiController::clearTable/$1');
    $routes->post('shoes/show', 'ShoesApiController::createShow');   // 端點 #6

    // RESTful CRUD
    $routes->post('shoes', 'ShoesApiController::create');
    $routes->get('shoes/(:segment)', 'ShoesApiController::getShoe/$1');  // 端點 #3
    $routes->put('shoes/(:segment)', 'ShoesApiController::update/$1');
    $routes->delete('shoes/(:segment)', 'ShoesApiController::delete/$1');
    
    // 相容舊版 AJAX 端點（如果需要）
    $routes->get('shoes/db-info', 'ShoesController::dbInfo');
    $routes->get('shoes/table-content', 'ShoesController::tableContent');
    
    // 執行歷史記錄 API
    $routes->post('shoes/execution/start', 'ExecutionController::start');
    $routes->post('shoes/execution/log', 'ExecutionController::log');
    $routes->post('shoes/execution/log-batch', 'ExecutionController::logBatch');
    $routes->post('shoes/execution/complete', 'ExecutionController::complete');
    $routes->get('shoes/execution/history', 'ExecutionController::history');
    $routes->get('shoes/execution/statistics', 'ExecutionController::statistics');
    $routes->get('shoes/execution/(:segment)', 'ExecutionController::detail/$1');
    $routes->get('shoes/(:segment)/changes', 'ExecutionController::productChanges/$1');
});
