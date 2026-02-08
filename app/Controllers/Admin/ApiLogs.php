<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ApiLogModel;

class ApiLogs extends BaseController
{
    protected $logModel;

    public function __construct()
    {
        $this->logModel = new ApiLogModel();
    }

    /**
     * 显示 API 日志列表
     */
    public function index()
    {
        $perPage = 50;
        $page = $this->request->getGet('page') ?? 1;
        $status = $this->request->getGet('status') ?? '';
        $endpoint = $this->request->getGet('endpoint') ?? '';
        $method = $this->request->getGet('method') ?? '';

        // 构建查询
        $builder = $this->logModel->builder();
        
        if (!empty($status)) {
            $builder->where('status', $status);
        }
        
        if (!empty($endpoint)) {
            $builder->like('endpoint', $endpoint);
        }
        
        if (!empty($method)) {
            $builder->where('method', $method);
        }

        // 获取总数
        $total = $builder->countAllResults(false);
        
        // 获取日志
        $logs = $builder->orderBy('created_at', 'DESC')
                       ->limit($perPage, ($page - 1) * $perPage)
                       ->get()
                       ->getResultArray();

        // 获取统计信息
        $stats = $this->logModel->getStats();
        
        // 获取今日统计
        $todayStats = $this->logModel->getStats(
            date('Y-m-d 00:00:00'),
            date('Y-m-d 23:59:59')
        );

        // 计算分页
        $totalPages = ceil($total / $perPage);

        return view('admin/api_logs', [
            'logs' => $logs,
            'stats' => $stats,
            'todayStats' => $todayStats,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
            'filters' => [
                'status' => $status,
                'endpoint' => $endpoint,
                'method' => $method,
            ]
        ]);
    }

    /**
     * 查看单条日志详情
     */
    public function detail($id)
    {
        $log = $this->logModel->find($id);

        if (!$log) {
            return $this->response->setJSON([
                'success' => false,
                'message' => '日志不存在'
            ])->setStatusCode(404);
        }

        return $this->response->setJSON([
            'success' => true,
            'log' => $log
        ]);
    }

    /**
     * 清理旧日志
     */
    public function clean()
    {
        $days = $this->request->getPost('days') ?? 30;
        
        try {
            $deleted = $this->logModel->cleanOldLogs($days);
            
            return redirect()->to('/admin/api-logs')
                           ->with('success', "成功删除 {$deleted} 条旧日志");
        } catch (\Exception $e) {
            return redirect()->to('/admin/api-logs')
                           ->with('error', '删除失败: ' . $e->getMessage());
        }
    }

    /**
     * 导出日志为 CSV
     */
    public function export()
    {
        $status = $this->request->getGet('status') ?? '';
        $endpoint = $this->request->getGet('endpoint') ?? '';
        
        $builder = $this->logModel->builder();
        
        if (!empty($status)) {
            $builder->where('status', $status);
        }
        
        if (!empty($endpoint)) {
            $builder->like('endpoint', $endpoint);
        }

        $logs = $builder->orderBy('created_at', 'DESC')
                       ->limit(1000)
                       ->get()
                       ->getResultArray();

        // 设置 CSV 响应头
        $filename = 'api_logs_' . date('YmdHis') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // 添加 BOM 以支持 Excel 正确显示中文
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        
        // CSV 表头
        fputcsv($output, [
            'ID', 'API Key', 'Endpoint', 'Method', 'Status Code', 
            'Status', 'Response Time', 'IP Address', 'Created At', 'Error Message'
        ]);
        
        // CSV 数据
        foreach ($logs as $log) {
            fputcsv($output, [
                $log['id'],
                $log['api_key_name'] ?? 'Public',
                $log['endpoint'],
                $log['method'],
                $log['response_code'],
                $log['status'],
                $log['response_time'] . 's',
                $log['ip_address'],
                $log['created_at'],
                $log['error_message'] ?? '-'
            ]);
        }
        
        fclose($output);
        exit;
    }
}
