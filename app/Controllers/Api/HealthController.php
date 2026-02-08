<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class HealthController extends ResourceController
{
    protected $format = 'json';

    /**
     * 資料庫健康檢查
     * GET /api/health/database
     */
    public function database()
    {
        try {
            $db = \Config\Database::connect();
            
            $startTime = microtime(true);
            $db->query('SELECT 1');
            $endTime = microtime(true);
            
            $responseTime = round(($endTime - $startTime) * 1000, 2);
            
            return $this->respond([
                'status' => 'success',
                'database' => 'connected',
                'response_time_ms' => $responseTime,
                'timestamp' => date('Y-m-d\TH:i:s\Z')
            ], 200);
            
        } catch (\Exception $e) {
            return $this->respond([
                'status' => 'error',
                'database' => 'disconnected',
                'message' => '資料庫連線失敗',
                'error_code' => 'DATABASE_UNAVAILABLE',
                'timestamp' => date('Y-m-d\TH:i:s\Z')
            ], 503);
        }
    }
}
