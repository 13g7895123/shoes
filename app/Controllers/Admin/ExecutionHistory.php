<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ExecutionHistoryModel;
use App\Models\ProductChangeLogModel;
use App\Models\ShoesModel;

class ExecutionHistory extends BaseController
{
    protected $executionModel;
    protected $changeLogModel;
    protected $shoesModel;

    public function __construct()
    {
        $this->executionModel = new ExecutionHistoryModel();
        $this->changeLogModel = new ProductChangeLogModel();
        $this->shoesModel     = new ShoesModel();
    }

    /**
     * 執行歷史列表頁面
     * GET /admin/execution-history
     */
    public function index()
    {
        $page     = (int)($this->request->getGet('page') ?? 1);
        $pageSize = 20;

        $filters = [
            'status'     => $this->request->getGet('status'),
            'mode'       => $this->request->getGet('mode'),
            'start_date' => $this->request->getGet('start_date'),
            'end_date'   => $this->request->getGet('end_date'),
        ];

        try {
            $data = $this->executionModel->getHistory($page, $pageSize, $filters);
        } catch (\Exception $e) {
            // 資料表不存在（尚未執行 migration）時給出友善提示
            return view('admin/execution_history_index', [
                'total'       => 0,
                'page'        => 1,
                'page_size'   => $pageSize,
                'executions'  => [],
                'total_pages' => 0,
                'filters'     => $filters,
                'db_error'    => '資料表尚未建立，請先執行：php spark migrate',
            ]);
        }

        $data['total_pages'] = ceil($data['total'] / $pageSize);
        $data['filters']     = $filters;
        $data['db_error']    = null;

        return view('admin/execution_history_index', $data);
    }

    /**
     * 執行詳情頁面
     * GET /admin/execution-history/detail/:executionId
     */
    public function detail($executionId = null)
    {
        if (!$executionId) {
            return redirect()->to('/admin/execution-history')->with('error', '缺少執行 ID');
        }

        try {
            $execution = $this->executionModel->findByExecutionId($executionId);
        } catch (\Exception $e) {
            return redirect()->to('/admin/execution-history')->with('error', '資料表尚未建立，請先執行 migrate');
        }

        if (!$execution) {
            return redirect()->to('/admin/execution-history')->with('error', '執行記錄不存在');
        }

        $changes = $this->changeLogModel->getByExecutionId($executionId);

        $actionStats = ['create' => 0, 'update' => 0, 'skip' => 0];
        foreach ($changes as $change) {
            if (isset($actionStats[$change['action_type']])) {
                $actionStats[$change['action_type']]++;
            }
        }

        // 判斷是否可退回（有 create 或 update 記錄且資料現存）
        $canRollback = ($actionStats['create'] + $actionStats['update']) > 0
                       && $execution['status'] !== 'running';

        return view('admin/execution_history_detail', [
            'execution'   => $execution,
            'changes'     => $changes,
            'action_stats' => $actionStats,
            'can_rollback' => $canRollback,
        ]);
    }

    /**
     * 退回此次執行的資料變更
     * POST /admin/execution-history/rollback/:executionId
     */
    public function rollback($executionId = null)
    {
        if (!$executionId) {
            return redirect()->to('/admin/execution-history')->with('error', '缺少執行 ID');
        }

        $execution = $this->executionModel->findByExecutionId($executionId);
        if (!$execution) {
            return redirect()->to('/admin/execution-history')->with('error', '執行記錄不存在');
        }

        if ($execution['status'] === 'running') {
            return redirect()->to('/admin/execution-history/detail/' . $executionId)
                             ->with('error', '執行中的記錄無法退回');
        }

        // 取得此次執行中有實際變更的紀錄
        $changes = $this->changeLogModel
            ->whereIn('action_type', ['update', 'create'])
            ->where('execution_id', $executionId)
            ->findAll();

        if (empty($changes)) {
            return redirect()->to('/admin/execution-history/detail/' . $executionId)
                             ->with('warning', '此次執行沒有可退回的變更記錄');
        }

        $rolledBack = 0;
        $errors     = 0;
        $db         = \Config\Database::connect();
        $db->transStart();

        foreach ($changes as $change) {
            try {
                if ($change['action_type'] === 'update') {
                    // 將 price / size 還原為變更前的值
                    // 直接用 builder 操作，避免 CI4 Model->update(null,null) 覆蓋 set() 資料的問題
                    $affected = $this->shoesModel->builder()
                        ->where('code', $change['product_code'])
                        ->update([
                            'price' => $change['before_price'],
                            'size'  => $change['before_size'],
                        ]);
                    if ($affected !== false) {
                        $rolledBack++;
                    } else {
                        $errors++;
                    }
                } elseif ($change['action_type'] === 'create') {
                    // 刪除此次新建的商品
                    $affected = $this->shoesModel->builder()
                        ->where('code', $change['product_code'])
                        ->delete();
                    if ($affected !== false) {
                        $rolledBack++;
                    } else {
                        $errors++;
                    }
                }
            } catch (\Exception $e) {
                $errors++;
            }
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            return redirect()->to('/admin/execution-history/detail/' . $executionId)
                             ->with('error', '退回失敗，資料庫交易錯誤，所有變更已取消');
        }

        $msg = $errors > 0
            ? "退回完成，成功 {$rolledBack} 筆，失敗 {$errors} 筆"
            : "已成功退回 {$rolledBack} 筆商品至此次執行前的狀態";

        $type = $errors > 0 ? 'warning' : 'success';

        return redirect()->to('/admin/execution-history/detail/' . $executionId)
                         ->with($type, $msg);
    }
}
