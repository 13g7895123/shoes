<?php

namespace App\Models;

use CodeIgniter\Model;

class ExecutionHistoryModel extends Model
{
    protected $table = 'execution_history';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'execution_id',
        'start_time',
        'end_time',
        'total_products',
        'created_count',
        'updated_count',
        'skipped_count',
        'failed_count',
        'duration_seconds',
        'status',
        'mode',
        'error_message'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = '';
    protected $deletedField = '';

    protected $validationRules = [
        'execution_id' => 'required|max_length[36]',
        'start_time' => 'required|valid_date',
        'total_products' => 'required|integer',
        'status' => 'required|in_list[running,success,failed,partial]',
        'mode' => 'required|in_list[test,production]',
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * 根據 execution_id 查詢
     */
    public function findByExecutionId(string $executionId)
    {
        return $this->where('execution_id', $executionId)->first();
    }

    /**
     * 獲取分頁歷史記錄
     */
    public function getHistory(int $page = 1, int $pageSize = 20, array $filters = [])
    {
        $builder = $this->builder();

        // 狀態篩選
        if (!empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        // 模式篩選
        if (!empty($filters['mode'])) {
            $builder->where('mode', $filters['mode']);
        }

        // 日期篩選
        if (!empty($filters['start_date'])) {
            $builder->where('start_time >=', $filters['start_date'] . ' 00:00:00');
        }
        if (!empty($filters['end_date'])) {
            $builder->where('start_time <=', $filters['end_date'] . ' 23:59:59');
        }

        // 計算總數
        $total = $builder->countAllResults(false);

        // 分頁查詢
        $offset = ($page - 1) * $pageSize;
        $executions = $builder->orderBy('start_time', 'DESC')
                             ->limit($pageSize, $offset)
                             ->get()
                             ->getResultArray();

        return [
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
            'executions' => $executions
        ];
    }

    /**
     * 獲取統計數據
     */
    public function getStatistics(string $period = 'last_7_days')
    {
        $builder = $this->builder();
        
        // 設定時間範圍
        $startDate = match($period) {
            'last_24_hours' => date('Y-m-d H:i:s', strtotime('-24 hours')),
            'last_7_days' => date('Y-m-d 00:00:00', strtotime('-7 days')),
            'last_30_days' => date('Y-m-d 00:00:00', strtotime('-30 days')),
            default => date('Y-m-d 00:00:00', strtotime('-7 days'))
        };

        $builder->where('start_time >=', $startDate);

        // 總體統計
        $allRecords = $builder->get()->getResultArray();
        
        $totalExecutions = count($allRecords);
        $successExecutions = count(array_filter($allRecords, fn($r) => $r['status'] === 'success'));
        $failedExecutions = count(array_filter($allRecords, fn($r) => $r['status'] === 'failed'));
        
        $totalProductsCrawled = array_sum(array_column($allRecords, 'total_products'));
        $totalCreated = array_sum(array_column($allRecords, 'created_count'));
        $totalUpdated = array_sum(array_column($allRecords, 'updated_count'));
        $totalSkipped = array_sum(array_column($allRecords, 'skipped_count'));
        
        $durations = array_filter(array_column($allRecords, 'duration_seconds'));
        $avgDuration = !empty($durations) ? round(array_sum($durations) / count($durations), 2) : 0;

        // 每日統計
        $dailyStats = [];
        foreach ($allRecords as $record) {
            $date = date('Y-m-d', strtotime($record['start_time']));
            if (!isset($dailyStats[$date])) {
                $dailyStats[$date] = [
                    'date' => $date,
                    'executions' => 0,
                    'created' => 0,
                    'updated' => 0,
                    'skipped' => 0
                ];
            }
            $dailyStats[$date]['executions']++;
            $dailyStats[$date]['created'] += $record['created_count'];
            $dailyStats[$date]['updated'] += $record['updated_count'];
            $dailyStats[$date]['skipped'] += $record['skipped_count'];
        }

        return [
            'period' => $period,
            'total_executions' => $totalExecutions,
            'success_executions' => $successExecutions,
            'failed_executions' => $failedExecutions,
            'total_products_crawled' => $totalProductsCrawled,
            'total_created' => $totalCreated,
            'total_updated' => $totalUpdated,
            'total_skipped' => $totalSkipped,
            'avg_duration_seconds' => $avgDuration,
            'daily_stats' => array_values($dailyStats)
        ];
    }

    /**
     * 更新執行狀態為完成
     */
    public function completeExecution(string $executionId, array $data)
    {
        $record = $this->findByExecutionId($executionId);
        if (!$record) {
            return false;
        }

        $updateData = [
            'end_time' => date('Y-m-d H:i:s'),
            'created_count' => $data['created_count'],
            'updated_count' => $data['updated_count'],
            'skipped_count' => $data['skipped_count'],
            'failed_count' => $data['failed_count'],
            'status' => $data['status'],
            'error_message' => $data['error_message'] ?? null
        ];

        // 計算執行時長
        $startTime = strtotime($record['start_time']);
        $endTime = time();
        $updateData['duration_seconds'] = round(($endTime - $startTime), 2);

        return $this->where('execution_id', $executionId)->set($updateData)->update();
    }
}
