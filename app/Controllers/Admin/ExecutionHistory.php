<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ExecutionHistoryModel;
use App\Models\ProductChangeLogModel;

class ExecutionHistory extends BaseController
{
    protected $executionModel;
    protected $changeLogModel;

    public function __construct()
    {
        $this->executionModel = new ExecutionHistoryModel();
        $this->changeLogModel = new ProductChangeLogModel();
    }

    /**
     * 執行歷史列表頁面
     */
    public function index()
    {
        $page = (int)($this->request->getGet('page') ?? 1);
        $pageSize = 20;
        
        $filters = [
            'status' => $this->request->getGet('status'),
            'mode' => $this->request->getGet('mode'),
            'start_date' => $this->request->getGet('start_date'),
            'end_date' => $this->request->getGet('end_date'),
        ];

        $data = $this->executionModel->getHistory($page, $pageSize, $filters);
        
        // 計算分頁資訊
        $data['total_pages'] = ceil($data['total'] / $pageSize);
        $data['filters'] = $filters;

        return view('admin/execution_history_index', $data);
    }

    /**
     * 執行詳情頁面
     */
    public function detail($executionId = null)
    {
        if (!$executionId) {
            return redirect()->to('/admin/execution-history')->with('error', '缺少執行 ID');
        }

        $execution = $this->executionModel->findByExecutionId($executionId);
        
        if (!$execution) {
            return redirect()->to('/admin/execution-history')->with('error', '執行記錄不存在');
        }

        // 獲取變更記錄
        $changes = $this->changeLogModel->getByExecutionId($executionId);

        // 統計各類型數量
        $actionStats = [
            'create' => 0,
            'update' => 0,
            'skip' => 0
        ];
        
        foreach ($changes as $change) {
            if (isset($actionStats[$change['action_type']])) {
                $actionStats[$change['action_type']]++;
            }
        }

        $data = [
            'execution' => $execution,
            'changes' => $changes,
            'action_stats' => $actionStats
        ];

        return view('admin/execution_history_detail', $data);
    }
}
