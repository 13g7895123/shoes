<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// 主頁
$routes->get('/', 'Home::index');

// API 路由群組
$routes->group('api', ['namespace' => 'App\Controllers\Api'], function ($routes) {
    // 除錯用：查看 DB 連線資訊
    $routes->get('shoes/db-info', 'ShoesController::dbInfo');

    // 相容舊版 AJAX 端點
    $routes->get('shoes/table-content', 'ShoesController::tableContent');

    // Shoes API
    $routes->resource('shoes', ['controller' => 'ShoesController']);
});
