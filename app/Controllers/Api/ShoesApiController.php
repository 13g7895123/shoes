<?php

namespace App\Controllers\Api;

use App\Models\ShoesModel;
use App\Models\ShoesShowModel;
use CodeIgniter\RESTful\ResourceController;

class ShoesApiController extends ResourceController
{
    protected $modelName = 'App\Models\ShoesModel';
    protected $format = 'json';
    protected $model;
    protected $showModel;

    public function __construct()
    {
        $this->model     = new ShoesModel();
        $this->showModel = new ShoesShowModel();
    }

    /**
     * 檢查商品狀態
     * POST /api/shoes/check-status
     */
    public function checkStatus()
    {
        try {
            $json = $this->request->getJSON(true);

            // 驗證必填欄位
            if (empty($json['code'])) {
                return $this->respond([
                    'success' => false,
                    'message' => '商品編號不可為空',
                    'data'    => null
                ], 400);
            }

            if (empty($json['price'])) {
                return $this->respond([
                    'success' => false,
                    'message' => '價格不可為空',
                    'data'    => null
                ], 400);
            }

            // size 允許為空，不進行驗證

            // 查詢商品是否存在
            $existing = $this->model->where('code', $json['code'])->first();

            if (!$existing) {
                // 商品不存在，需要新增
                return $this->respond(['action_required' => 2], 200);
            }

            // 檢查是否需要更新
            $newSize = $json['size'] ?? '';
            if ($existing['price'] != $json['price'] || $existing['size'] != $newSize) {
                return $this->respond(['action_required' => 1], 200);
            }

            return $this->respond(['action_required' => 0], 200);

        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => '伺服器錯誤',
                'data'    => null
            ], 500);
        }
    }

    /**
     * 新增商品
     * POST /api/shoes
     */
    public function create()
    {
        try {
            $json = $this->request->getJSON(true);

            // 驗證必填欄位
            $required = ['name', 'eng_name', 'code', 'price'];
            foreach ($required as $field) {
                if (empty($json[$field])) {
                    return $this->fail([
                        'status' => 'error',
                        'message' => "{$field} 不可為空",
                        'error_code' => 'MISSING_REQUIRED_FIELD',
                        'field' => $field
                    ], 400);
                }
            }

            // 檢查商品編號是否已存在
            $existing = $this->model->where('code', $json['code'])->first();
            if ($existing) {
                return $this->respond([
                    'success' => false,
                    'message' => '商品編號已存在',
                    'data'    => null
                ], 409);
            }

            // 準備資料
            $data = [
                'name'       => $json['name'],
                'eng_name'   => $json['eng_name'],
                'code'       => $json['code'],
                'price'      => $json['price'],
                'hope_price' => $json['hope_price'] ?? '',
                'point'      => $json['point'] ?? '',
                'size'       => $json['size'] ?? '',
                'action'     => '新增'
            ];

            // 新增商品
            $id = $this->model->insert($data);

            if (!$id) {
                return $this->respond([
                    'success' => false,
                    'message' => '商品新增失敗',
                    'data'    => null
                ], 500);
            }

            return $this->respond([
                'success' => true,
                'message' => '商品新增成功',
                'data'    => null
            ], 201);

        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => '伺服器錯誤: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }

    /**
     * 更新商品
     * PUT /api/shoes/{code}
     */
    public function update($code = null)
    {
        try {
            if (empty($code)) {
                return $this->fail([
                    'status' => 'error',
                    'message' => '商品編號不可為空',
                    'error_code' => 'MISSING_REQUIRED_FIELD',
                    'field' => 'code'
                ], 400);
            }

            $json = $this->request->getJSON(true);

            // 驗證必填欄位
            if (empty($json['price'])) {
                return $this->fail([
                    'status' => 'error',
                    'message' => 'price 不可為空',
                    'error_code' => 'MISSING_REQUIRED_FIELD',
                    'field' => 'price'
                ], 400);
            }

            if (empty($json['size'])) {
                return $this->respond([
                    'success' => false,
                    'message' => 'size 不可為空',
                    'data'    => null
                ], 400);
            }

            // 查詢商品
            $existing = $this->model->where('code', $code)->first();
            if (!$existing) {
                return $this->respond([
                    'success' => false,
                    'message' => '商品不存在',
                    'data'    => null
                ], 404);
            }

            // 只更新 price 與 size
            $this->model->where('code', $code)->set([
                'price'  => $json['price'],
                'size'   => $json['size'],
                'action' => '更新'
            ])->update();

            return $this->respond([
                'success' => true,
                'message' => '商品更新成功',
                'data'    => null
            ], 200);

        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => '伺服器錯誤: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }

    /**
     * 刪除商品
     * DELETE /api/shoes/{code}
     */
    public function delete($code = null)
    {
        try {
            if (empty($code)) {
                return $this->fail([
                    'status' => 'error',
                    'message' => '商品編號不可為空',
                    'error_code' => 'MISSING_REQUIRED_FIELD',
                    'field' => 'code'
                ], 400);
            }

            // 查詢商品
            $existing = $this->model->where('code', $code)->first();
            if (!$existing) {
                return $this->respond([
                    'success' => false,
                    'message' => '商品不存在',
                    'data'    => null
                ], 404);
            }

            // 刪除商品
            $this->model->where('code', $code)->delete();

            return $this->respond([
                'success' => true,
                'message' => '商品已刪除',
                'data'    => null
            ], 200);

        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => '伺服器錯誤: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }

    /**
     * 取得單一商品資料
     * GET /api/v1/shoes/:code
     */
    public function getShoe($code = null)
    {
        try {
            if (empty($code)) {
                return $this->fail([
                    'success' => false,
                    'message' => '商品編號不可為空',
                    'data'    => null
                ], 400);
            }

            $product = $this->model->getByCode($code);

            if (!$product) {
                return $this->fail([
                    'success' => false,
                    'message' => '商品不存在',
                    'data'    => null
                ], 404);
            }

            return $this->respond([
                'success' => true,
                'message' => 'ok',
                'data'    => [
                    'name'       => $product['name'],
                    'eng_name'   => $product['eng_name'],
                    'code'       => $product['code'],
                    'hope_price' => $product['hope_price'],
                    'price'      => $product['price'],
                    'point'      => $product['point'],
                    'size'       => $product['size'],
                ]
            ], 200);

        } catch (\Exception $e) {
            return $this->fail([
                'success' => false,
                'message' => '伺服器錯誤: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }

    /**
     * 新增展示用商品
     * POST /api/v1/shoes/show
     */
    public function createShow()
    {
        try {
            $json = $this->request->getJSON(true);

            // 驗證必填欄位
            $required = ['name', 'eng_name', 'code', 'price'];
            foreach ($required as $field) {
                if (empty($json[$field])) {
                    return $this->fail([
                        'success' => false,
                        'message' => "{$field} 不可為空",
                        'data'    => null
                    ], 400);
                }
            }

            $data = [
                'name'       => $json['name'],
                'eng_name'   => $json['eng_name'],
                'code'       => $json['code'],
                'price'      => $json['price'],
                'hope_price' => $json['hope_price'] ?? '',
                'point'      => $json['point'] ?? '',
                'size'       => $json['size'] ?? '',
            ];

            $id = $this->showModel->insert($data);

            if (!$id) {
                return $this->fail([
                    'success' => false,
                    'message' => '展示商品新增失敗',
                    'data'    => null
                ], 500);
            }

            return $this->respondCreated([
                'success' => true,
                'message' => '展示商品新增成功',
                'data'    => null
            ]);

        } catch (\Exception $e) {
            return $this->fail([
                'success' => false,
                'message' => '伺服器錯誤: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }

    /**
     * 取得所有商品編號
     * GET /api/shoes/codes
     */
    public function getCodes()
    {
        try {
            $limit = $this->request->getGet('limit');
            $offset = $this->request->getGet('offset') ?? 0;

            $builder = $this->model->builder();
            $builder->select('code');

            if ($limit) {
                $builder->limit($limit, $offset);
            }

            $results = $builder->get()->getResultArray();
            $codes = array_column($results, 'code');

            return $this->respond(['codes' => $codes], 200);

        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => '伺服器錯誤: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }

    /**
     * 清空展示資料表
     * DELETE /api/shoes/clear/{table_name}
     */
    public function clearTable($tableName = null)
    {
        try {
            // 允許清空的資料表
            $allowedTables = ['shoes_show_inf'];

            if (!in_array($tableName, $allowedTables)) {
                return $this->respond([
                    'success' => false,
                    'message' => '不允許清空此資料表',
                    'data'    => null
                ], 403);
            }

            $json = $this->request->getJSON(true);

            // 確認參數（防呆機制，必須傳 true 才執行）
            if (!isset($json['confirm']) || $json['confirm'] !== true) {
                return $this->respond([
                    'success' => false,
                    'message' => '請確認清空操作',
                    'data'    => null
                ], 400);
            }

            // 清空資料表
            $this->showModel->truncate();

            return $this->respond([
                'success' => true,
                'message' => '資料表已清空',
                'data'    => null
            ], 200);

        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => '伺服器錯誤: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }

    /**
     * 批次新增商品
     * POST /api/shoes/batch
     */
    public function batchCreate()
    {
        try {
            $json = $this->request->getJSON(true);

            if (empty($json['products']) || !is_array($json['products'])) {
                return $this->fail([
                    'status' => 'error',
                    'message' => 'products 陣列不可為空',
                    'error_code' => 'MISSING_REQUIRED_FIELD',
                    'field' => 'products'
                ], 400);
            }

            // 限制批次數量
            if (count($json['products']) > 100) {
                return $this->fail([
                    'status' => 'error',
                    'message' => '單次最多新增 100 筆商品',
                    'error_code' => 'BATCH_LIMIT_EXCEEDED'
                ], 400);
            }

            $results = [];
            $successCount = 0;
            $failedCount = 0;

            foreach ($json['products'] as $product) {
                try {
                    // 驗證必填欄位
                    if (empty($product['name']) || empty($product['eng_name']) || 
                        empty($product['code']) || empty($product['price'])) {
                        $results[] = [
                            'code' => $product['code'] ?? 'unknown',
                            'status' => 'failed',
                            'error' => '缺少必填欄位'
                        ];
                        $failedCount++;
                        continue;
                    }

                    // 檢查是否已存在
                    $existing = $this->model->where('code', $product['code'])->first();
                    if ($existing) {
                        $results[] = [
                            'code' => $product['code'],
                            'status' => 'failed',
                            'error' => '商品編號已存在'
                        ];
                        $failedCount++;
                        continue;
                    }

                    // 新增商品
                    $data = [
                        'name' => $product['name'],
                        'eng_name' => $product['eng_name'],
                        'code' => $product['code'],
                        'price' => $product['price'],
                        'hope_price' => $product['hope_price'] ?? '',
                        'point' => $product['point'] ?? '',
                        'size' => $product['size'] ?? '',
                        'action' => '新增'
                    ];

                    $id = $this->model->insert($data);

                    if ($id) {
                        $results[] = [
                            'code' => $product['code'],
                            'status' => 'created',
                            'id' => $id
                        ];
                        $successCount++;
                    } else {
                        $results[] = [
                            'code' => $product['code'],
                            'status' => 'failed',
                            'error' => '新增失敗'
                        ];
                        $failedCount++;
                    }

                } catch (\Exception $e) {
                    $results[] = [
                        'code' => $product['code'] ?? 'unknown',
                        'status' => 'failed',
                        'error' => $e->getMessage()
                    ];
                    $failedCount++;
                }
            }

            $statusCode = 201;
            if ($failedCount > 0 && $successCount > 0) {
                $statusCode = 207; // Multi-Status
            } elseif ($failedCount > 0 && $successCount === 0) {
                $statusCode = 400;
            }

            return $this->respond([
                'status' => 'success',
                'message' => '批次新增完成',
                'total' => count($json['products']),
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'results' => $results
            ], $statusCode);

        } catch (\Exception $e) {
            return $this->fail([
                'status' => 'error',
                'message' => '伺服器錯誤: ' . $e->getMessage(),
                'error_code' => 'INTERNAL_SERVER_ERROR',
                'timestamp' => date('Y-m-d\TH:i:s\Z')
            ], 500);
        }
    }

    /**
     * 批次更新商品
     * PUT /api/shoes/batch
     */
    public function batchUpdate()
    {
        try {
            $json = $this->request->getJSON(true);

            if (empty($json['updates']) || !is_array($json['updates'])) {
                return $this->fail([
                    'status' => 'error',
                    'message' => 'updates 陣列不可為空',
                    'error_code' => 'MISSING_REQUIRED_FIELD',
                    'field' => 'updates'
                ], 400);
            }

            // 限制批次數量
            if (count($json['updates']) > 100) {
                return $this->fail([
                    'status' => 'error',
                    'message' => '單次最多更新 100 筆商品',
                    'error_code' => 'BATCH_LIMIT_EXCEEDED'
                ], 400);
            }

            $results = [];
            $successCount = 0;
            $failedCount = 0;

            foreach ($json['updates'] as $update) {
                try {
                    // 驗證必填欄位
                    if (empty($update['code'])) {
                        $results[] = [
                            'code' => 'unknown',
                            'status' => 'failed',
                            'error' => '商品編號不可為空'
                        ];
                        $failedCount++;
                        continue;
                    }

                    // 檢查商品是否存在
                    $existing = $this->model->where('code', $update['code'])->first();
                    if (!$existing) {
                        $results[] = [
                            'code' => $update['code'],
                            'status' => 'failed',
                            'error' => '商品不存在'
                        ];
                        $failedCount++;
                        continue;
                    }

                    // 更新商品
                    $data = [];
                    if (isset($update['price'])) $data['price'] = $update['price'];
                    if (isset($update['size'])) $data['size'] = $update['size'];
                    $data['action'] = '更新';

                    if (empty($data)) {
                        $results[] = [
                            'code' => $update['code'],
                            'status' => 'failed',
                            'error' => '沒有要更新的欄位'
                        ];
                        $failedCount++;
                        continue;
                    }

                    $this->model->where('code', $update['code'])->set($data)->update();

                    $results[] = [
                        'code' => $update['code'],
                        'status' => 'updated'
                    ];
                    $successCount++;

                } catch (\Exception $e) {
                    $results[] = [
                        'code' => $update['code'] ?? 'unknown',
                        'status' => 'failed',
                        'error' => $e->getMessage()
                    ];
                    $failedCount++;
                }
            }

            $statusCode = 200;
            if ($failedCount > 0 && $successCount > 0) {
                $statusCode = 207; // Multi-Status
            } elseif ($failedCount > 0 && $successCount === 0) {
                $statusCode = 400;
            }

            return $this->respond([
                'status' => 'success',
                'message' => '批次更新完成',
                'total' => count($json['updates']),
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'results' => $results
            ], $statusCode);

        } catch (\Exception $e) {
            return $this->fail([
                'status' => 'error',
                'message' => '伺服器錯誤: ' . $e->getMessage(),
                'error_code' => 'INTERNAL_SERVER_ERROR',
                'timestamp' => date('Y-m-d\TH:i:s\Z')
            ], 500);
        }
    }
}
