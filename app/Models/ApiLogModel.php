<?php

namespace App\Models;

use CodeIgniter\Model;

class ApiLogModel extends Model
{
    protected $table            = 'api_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'api_key_id',
        'api_key_name',
        'endpoint',
        'method',
        'request_headers',
        'request_body',
        'request_params',
        'response_code',
        'response_body',
        'response_time',
        'ip_address',
        'user_agent',
        'status',
        'error_message',
        'created_at'
    ];

    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    /**
     * 记录 API 调用
     */
    public function logRequest($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->insert($data);
    }

    /**
     * 获取最近的日志
     */
    public function getRecentLogs($limit = 100, $offset = 0)
    {
        return $this->orderBy('created_at', 'DESC')
                    ->limit($limit, $offset)
                    ->findAll();
    }

    /**
     * 按状态获取日志
     */
    public function getLogsByStatus($status, $limit = 100)
    {
        return $this->where('status', $status)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * 按端点获取日志
     */
    public function getLogsByEndpoint($endpoint, $limit = 100)
    {
        return $this->like('endpoint', $endpoint)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * 获取统计信息
     */
    public function getStats($startDate = null, $endDate = null)
    {
        $builder = $this->builder();
        
        if ($startDate) {
            $builder->where('created_at >=', $startDate);
        }
        if ($endDate) {
            $builder->where('created_at <=', $endDate);
        }

        $total = $builder->countAllResults(false);
        $success = $builder->where('status', 'success')->countAllResults(false);
        $error = $builder->where('status', 'error')->countAllResults();

        return [
            'total' => $total,
            'success' => $success,
            'error' => $error,
            'success_rate' => $total > 0 ? round(($success / $total) * 100, 2) : 0,
        ];
    }

    /**
     * 清理旧日志
     */
    public function cleanOldLogs($days = 30)
    {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        return $this->where('created_at <', $cutoffDate)->delete();
    }
}
