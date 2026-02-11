<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductChangeLogModel extends Model
{
    protected $table = 'product_change_log';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'execution_id',
        'product_code',
        'product_name',
        'action_type',
        'before_price',
        'after_price',
        'before_size',
        'after_size',
        'before_hope_price',
        'after_hope_price',
        'before_point',
        'after_point',
        'change_reason',
        'has_price_change',
        'has_size_change'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = '';
    protected $deletedField = '';

    protected $validationRules = [
        'execution_id' => 'required|max_length[36]',
        'product_code' => 'required|max_length[100]',
        'action_type' => 'required|in_list[create,update,skip]',
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * 根據執行 ID 獲取所有變更記錄
     */
    public function getByExecutionId(string $executionId)
    {
        return $this->where('execution_id', $executionId)
                    ->orderBy('created_at', 'ASC')
                    ->findAll();
    }

    /**
     * 根據商品編號獲取變更歷史
     */
    public function getByProductCode(string $productCode, int $limit = 50)
    {
        return $this->select('product_change_log.*, execution_history.start_time')
                    ->join('execution_history', 'execution_history.execution_id = product_change_log.execution_id')
                    ->where('product_code', $productCode)
                    ->orderBy('product_change_log.created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * 批量插入變更記錄
     */
    public function insertBatch(array $changes)
    {
        if (empty($changes)) {
            return true;
        }

        // 處理每個變更記錄，檢測是否有變動
        foreach ($changes as &$change) {
            $change['has_price_change'] = isset($change['before_price']) && 
                                          isset($change['after_price']) && 
                                          $change['before_price'] !== $change['after_price'];
            
            $change['has_size_change'] = isset($change['before_size']) && 
                                         isset($change['after_size']) && 
                                         $change['before_size'] !== $change['after_size'];
        }

        return $this->builder()->insertBatch($changes);
    }

    /**
     * 單個記錄插入（自動檢測變更）
     */
    public function insertChange(array $data)
    {
        // 檢測價格變更
        $data['has_price_change'] = isset($data['before_price']) && 
                                    isset($data['after_price']) && 
                                    $data['before_price'] !== $data['after_price'];
        
        // 檢測尺寸變更
        $data['has_size_change'] = isset($data['before_size']) && 
                                   isset($data['after_size']) && 
                                   $data['before_size'] !== $data['after_size'];

        return $this->insert($data);
    }
}
