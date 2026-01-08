<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

class ShoesController extends ResourceController
{
    use ResponseTrait;

    protected $modelName = 'App\Models\ShoesModel';
    protected $format    = 'json';

    /**
     * 取得所有鞋子資料
     * GET /api/shoes
     */
    public function index()
    {
        try {
            $shoes = $this->model->findAll();

            return $this->respond([
                'success' => true,
                'data' => $shoes,
                'message' => '資料取得成功'
            ]);
        } catch (\Exception $e) {
            return $this->fail([
                'success' => false,
                'message' => '資料取得失敗: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 取得單筆鞋子資料
     * GET /api/shoes/{id}
     */
    public function show($id = null)
    {
        try {
            $shoe = $this->model->find($id);

            if (!$shoe) {
                return $this->failNotFound([
                    'success' => false,
                    'message' => '找不到該筆資料'
                ]);
            }

            return $this->respond([
                'success' => true,
                'data' => $shoe,
                'message' => '資料取得成功'
            ]);
        } catch (\Exception $e) {
            return $this->fail([
                'success' => false,
                'message' => '資料取得失敗: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 新增鞋子資料
     * POST /api/shoes
     */
    public function create()
    {
        try {
            $data = $this->request->getJSON(true);

            if (!$this->validate($this->model->getValidationRules())) {
                return $this->failValidationErrors($this->validator->getErrors());
            }

            $id = $this->model->insert($data);

            if (!$id) {
                return $this->fail([
                    'success' => false,
                    'message' => '資料新增失敗'
                ], 400);
            }

            return $this->respondCreated([
                'success' => true,
                'data' => ['id' => $id],
                'message' => '資料新增成功'
            ]);
        } catch (\Exception $e) {
            return $this->fail([
                'success' => false,
                'message' => '資料新增失敗: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 更新鞋子資料
     * PUT /api/shoes/{id}
     */
    public function update($id = null)
    {
        try {
            $data = $this->request->getJSON(true);

            if (!$this->model->find($id)) {
                return $this->failNotFound([
                    'success' => false,
                    'message' => '找不到該筆資料'
                ]);
            }

            if (!$this->validate($this->model->getValidationRules())) {
                return $this->failValidationErrors($this->validator->getErrors());
            }

            $result = $this->model->update($id, $data);

            if (!$result) {
                return $this->fail([
                    'success' => false,
                    'message' => '資料更新失敗'
                ], 400);
            }

            return $this->respond([
                'success' => true,
                'message' => '資料更新成功'
            ]);
        } catch (\Exception $e) {
            return $this->fail([
                'success' => false,
                'message' => '資料更新失敗: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 刪除鞋子資料
     * DELETE /api/shoes/{id}
     */
    public function delete($id = null)
    {
        try {
            if (!$this->model->find($id)) {
                return $this->failNotFound([
                    'success' => false,
                    'message' => '找不到該筆資料'
                ]);
            }

            $result = $this->model->delete($id);

            if (!$result) {
                return $this->fail([
                    'success' => false,
                    'message' => '資料刪除失敗'
                ], 400);
            }

            return $this->respondDeleted([
                'success' => true,
                'message' => '資料刪除成功'
            ]);
        } catch (\Exception $e) {
            return $this->fail([
                'success' => false,
                'message' => '資料刪除失敗: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 取得表格內容（相容舊版 AJAX）
     * GET /api/shoes/table-content
     */
    public function tableContent()
    {
        try {
            $shoes = $this->model->findAll();

            return $this->respond([
                'success' => true,
                'data' => $shoes
            ]);
        } catch (\Exception $e) {
            return $this->fail([
                'success' => false,
                'message' => '資料取得失敗: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 除錯用：顯示資料庫連線資訊
     * GET /api/shoes/db-info
     */
    public function dbInfo()
    {
        $db = \Config\Database::connect();
        $config = config('Database');

        $info = [
            'hostname' => $config->default['hostname'] ?? 'not set',
            'database' => $config->default['database'] ?? 'not set',
            'username' => $config->default['username'] ?? 'not set',
            'password' => str_repeat('*', strlen($config->default['password'] ?? '')),
            'port' => $config->default['port'] ?? 'not set',
            'DBDriver' => $config->default['DBDriver'] ?? 'not set',
        ];

        // 測試連線
        try {
            $db->connect();
            $info['connection_status'] = 'SUCCESS';
            $info['server_info'] = $db->getVersion();
        } catch (\Exception $e) {
            $info['connection_status'] = 'FAILED';
            $info['error'] = $e->getMessage();
        }

        return $this->respond([
            'success' => true,
            'db_info' => $info,
            'env' => [
                'CI_ENVIRONMENT' => env('CI_ENVIRONMENT', 'not set'),
                'app.baseURL' => env('app.baseURL', 'not set'),
            ]
        ]);
    }
}
