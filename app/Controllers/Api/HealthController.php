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
            $db->query('SELECT 1');

            return $this->respond(['database' => 'connected'], 200);

        } catch (\Exception $e) {
            return $this->respond(['database' => 'disconnected'], 503);
        }
    }
}
