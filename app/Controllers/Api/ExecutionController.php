<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ExecutionHistoryModel;
use App\Models\ProductChangeLogModel;
use CodeIgniter\API\ResponseTrait;

class ExecutionController extends BaseController
{
    use ResponseTrait;

    protected $executionModel;
    protected $changeLogModel;

    public function __construct()
    {
        $this->executionModel = new ExecutionHistoryModel();
        $this->changeLogModel = new ProductChangeLogModel();
    }

    /**
     * 生成 UUID v4
     */
    private function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * POST /api/v1/shoes/execution/start
     * 開始執行記錄
     */
    public function start()
    {
        $data = $this->request->getJSON(true);

        // 驗證參數
        if (!isset($data['total_products']) || $data['total_products'] <= 0) {
            return $this->fail('參數錯誤：total_products 必須大於 0', 400, 'INVALID_PARAMETER');
        }

        if (!isset($data['mode']) || !in_array($data['mode'], ['test', 'production'])) {
            return $this->fail('參數錯誤：mode 必須是 test 或 production', 400, 'INVALID_PARAMETER');
        }

        try {
            // 生成 UUID
            $executionId = $this->generateUuid();
            $startTime = date('Y-m-d H:i:s');

            // 創建執行記錄
            $insertData = [
                'execution_id' => $executionId,
                'start_time' => $startTime,
                'total_products' => $data['total_products'],
                'mode' => $data['mode'],
                'status' => 'running'
            ];

            $this->executionModel->insert($insertData);

            return $this->respond([
                'success' => true,
                'data' => [
                    'execution_id' => $executionId,
                    'start_time' => date('c', strtotime($startTime))
                ],
                'message' => '執行記錄已創建'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Execution start error: ' . $e->getMessage());
            return $this->fail('伺服器內部錯誤', 500, 'INTERNAL_ERROR');
        }
    }

    /**
     * POST /api/v1/shoes/execution/log
     * 記錄單個商品變更
     */
    public function log()
    {
        $data = $this->request->getJSON(true);

        // 驗證必填參數
        if (!isset($data['execution_id']) || !isset($data['product_code']) || !isset($data['action_type'])) {
            return $this->fail('缺少必要參數', 400, 'INVALID_PARAMETER');
        }

        // 驗證執行 ID 是否存在
        $execution = $this->executionModel->findByExecutionId($data['execution_id']);
        if (!$execution) {
            return $this->fail('執行 ID 不存在', 404, 'EXECUTION_NOT_FOUND');
        }

        // 檢查執行是否已完成
        if (in_array($execution['status'], ['success', 'failed'])) {
            return $this->fail('執行已完成，無法再記錄', 409, 'EXECUTION_COMPLETED');
        }

        try {
            // 插入變更記錄
            $this->changeLogModel->insertChange([
                'execution_id' => $data['execution_id'],
                'product_code' => $data['product_code'],
                'product_name' => $data['product_name'] ?? null,
                'action_type' => $data['action_type'],
                'before_price' => $data['before_price'] ?? null,
                'after_price' => $data['after_price'] ?? null,
                'before_size' => $data['before_size'] ?? null,
                'after_size' => $data['after_size'] ?? null,
                'before_hope_price' => $data['before_hope_price'] ?? null,
                'after_hope_price' => $data['after_hope_price'] ?? null,
                'before_point' => $data['before_point'] ?? null,
                'after_point' => $data['after_point'] ?? null,
                'change_reason' => $data['change_reason'] ?? null,
            ]);

            return $this->respond([
                'success' => true,
                'message' => '變更記錄已保存'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Change log error: ' . $e->getMessage());
            return $this->fail('資料庫錯誤', 500, 'DATABASE_ERROR');
        }
    }

    /**
     * POST /api/v1/shoes/execution/log-batch
     * 批量記錄變更
     */
    public function logBatch()
    {
        $data = $this->request->getJSON(true);

        // 驗證參數
        if (!isset($data['execution_id']) || !isset($data['changes']) || !is_array($data['changes'])) {
            return $this->fail('缺少必要參數', 400, 'INVALID_PARAMETER');
        }

        // 驗證執行 ID 是否存在
        $execution = $this->executionModel->findByExecutionId($data['execution_id']);
        if (!$execution) {
            return $this->fail('執行 ID 不存在', 404, 'EXECUTION_NOT_FOUND');
        }

        try {
            $changes = [];
            foreach ($data['changes'] as $change) {
                $changes[] = [
                    'execution_id' => $data['execution_id'],
                    'product_code' => $change['product_code'] ?? '',
                    'product_name' => $change['product_name'] ?? null,
                    'action_type' => $change['action_type'] ?? 'skip',
                    'before_price' => $change['before_price'] ?? null,
                    'after_price' => $change['after_price'] ?? null,
                    'before_size' => $change['before_size'] ?? null,
                    'after_size' => $change['after_size'] ?? null,
                    'before_hope_price' => $change['before_hope_price'] ?? null,
                    'after_hope_price' => $change['after_hope_price'] ?? null,
                    'before_point' => $change['before_point'] ?? null,
                    'after_point' => $change['after_point'] ?? null,
                    'change_reason' => $change['change_reason'] ?? null,
                ];
            }

            $this->changeLogModel->insertBatch($changes);

            return $this->respond([
                'success' => true,
                'data' => [
                    'total_logged' => count($changes),
                    'success_count' => count($changes),
                    'failed_count' => 0
                ],
                'message' => '批量記錄完成'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Batch log error: ' . $e->getMessage());
            return $this->fail('資料庫錯誤', 500, 'DATABASE_ERROR');
        }
    }

    /**
     * POST /api/v1/shoes/execution/complete
     * 完成執行記錄
     */
    public function complete()
    {
        $data = $this->request->getJSON(true);

        // 驗證參數
        if (!isset($data['execution_id'])) {
            return $this->fail('缺少執行 ID', 400, 'INVALID_PARAMETER');
        }

        // 驗證執行 ID 是否存在
        $execution = $this->executionModel->findByExecutionId($data['execution_id']);
        if (!$execution) {
            return $this->fail('執行 ID 不存在', 404, 'EXECUTION_NOT_FOUND');
        }

        try {
            $result = $this->executionModel->completeExecution($data['execution_id'], [
                'created_count' => $data['created_count'] ?? 0,
                'updated_count' => $data['updated_count'] ?? 0,
                'skipped_count' => $data['skipped_count'] ?? 0,
                'failed_count' => $data['failed_count'] ?? 0,
                'status' => $data['status'] ?? 'success',
                'error_message' => $data['error_message'] ?? null
            ]);

            if (!$result) {
                return $this->fail('更新失敗', 500, 'DATABASE_ERROR');
            }

            // 取得更新後的記錄
            $updated = $this->executionModel->findByExecutionId($data['execution_id']);

            return $this->respond([
                'success' => true,
                'data' => [
                    'execution_id' => $updated['execution_id'],
                    'end_time' => date('c', strtotime($updated['end_time'])),
                    'duration_seconds' => (float)$updated['duration_seconds']
                ],
                'message' => '執行記錄已完成'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Complete execution error: ' . $e->getMessage());
            return $this->fail('資料庫錯誤', 500, 'DATABASE_ERROR');
        }
    }

    /**
     * GET /api/v1/shoes/execution/history
     * 查詢執行歷史列表
     */
    public function history()
    {
        $page = (int)($this->request->getGet('page') ?? 1);
        $pageSize = (int)($this->request->getGet('page_size') ?? 20);
        
        $filters = [
            'status' => $this->request->getGet('status'),
            'mode' => $this->request->getGet('mode'),
            'start_date' => $this->request->getGet('start_date'),
            'end_date' => $this->request->getGet('end_date'),
        ];

        try {
            $result = $this->executionModel->getHistory($page, $pageSize, $filters);

            // 格式化時間為 ISO 8601
            foreach ($result['executions'] as &$execution) {
                $execution['start_time'] = date('c', strtotime($execution['start_time']));
                if ($execution['end_time']) {
                    $execution['end_time'] = date('c', strtotime($execution['end_time']));
                }
                $execution['duration_seconds'] = (float)$execution['duration_seconds'];
            }

            return $this->respond([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Get history error: ' . $e->getMessage());
            return $this->fail('資料庫錯誤', 500, 'DATABASE_ERROR');
        }
    }

    /**
     * GET /api/v1/shoes/execution/{execution_id}
     * 查詢執行詳情
     */
    public function detail($executionId = null)
    {
        if (!$executionId) {
            return $this->fail('缺少執行 ID', 400, 'INVALID_PARAMETER');
        }

        try {
            $execution = $this->executionModel->findByExecutionId($executionId);
            
            if (!$execution) {
                return $this->fail('執行 ID 不存在', 404, 'EXECUTION_NOT_FOUND');
            }

            // 獲取變更記錄
            $changes = $this->changeLogModel->getByExecutionId($executionId);

            // 格式化時間
            $execution['start_time'] = date('c', strtotime($execution['start_time']));
            if ($execution['end_time']) {
                $execution['end_time'] = date('c', strtotime($execution['end_time']));
            }
            $execution['duration_seconds'] = (float)$execution['duration_seconds'];

            foreach ($changes as &$change) {
                $change['created_at'] = date('c', strtotime($change['created_at']));
                $change['has_price_change'] = (bool)$change['has_price_change'];
                $change['has_size_change'] = (bool)$change['has_size_change'];
            }

            $execution['changes'] = $changes;

            return $this->respond([
                'success' => true,
                'data' => $execution
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Get detail error: ' . $e->getMessage());
            return $this->fail('資料庫錯誤', 500, 'DATABASE_ERROR');
        }
    }

    /**
     * GET /api/v1/shoes/{product_code}/changes
     * 查詢商品變更歷史
     */
    public function productChanges($productCode = null)
    {
        if (!$productCode) {
            return $this->fail('缺少商品編號', 400, 'INVALID_PARAMETER');
        }

        $limit = (int)($this->request->getGet('limit') ?? 50);

        try {
            $changes = $this->changeLogModel->getByProductCode($productCode, $limit);

            // 格式化時間
            foreach ($changes as &$change) {
                $change['created_at'] = date('c', strtotime($change['created_at']));
                $change['has_price_change'] = (bool)$change['has_price_change'];
                $change['has_size_change'] = (bool)$change['has_size_change'];
            }

            return $this->respond([
                'success' => true,
                'data' => [
                    'product_code' => $productCode,
                    'total_changes' => count($changes),
                    'changes' => $changes
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Get product changes error: ' . $e->getMessage());
            return $this->fail('資料庫錯誤', 500, 'DATABASE_ERROR');
        }
    }

    /**
     * GET /api/v1/shoes/execution/statistics
     * 獲取統計數據
     */
    public function statistics()
    {
        $period = $this->request->getGet('period') ?? 'last_7_days';

        try {
            $stats = $this->executionModel->getStatistics($period);

            return $this->respond([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Get statistics error: ' . $e->getMessage());
            return $this->fail('資料庫錯誤', 500, 'DATABASE_ERROR');
        }
    }
}
