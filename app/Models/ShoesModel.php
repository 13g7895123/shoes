<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 商品資料 Base Model
 *
 * shoes_inf 與 shoes_show_inf 結構相同，共用此基底類別，
 * 子類別只需宣告 $table 即可，避免重複定義。
 */
abstract class BaseShoeModel extends Model
{
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name',
        'eng_name',
        'code',
        'hope_price',
        'price',
        'point',
        'size',
        'action'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'name'       => 'permit_empty|max_length[200]',
        'eng_name'   => 'permit_empty|max_length[200]',
        'code'       => 'permit_empty|max_length[50]',
        'hope_price' => 'permit_empty|max_length[20]',
        'price'      => 'permit_empty|max_length[20]',
        'point'      => 'permit_empty|max_length[20]',
        'size'       => 'permit_empty|max_length[500]',
        'action'     => 'permit_empty|max_length[50]'
    ];
    protected $validationMessages = [
        'name'       => ['max_length' => '商品名稱不能超過 200 個字元'],
        'eng_name'   => ['max_length' => '英文名稱不能超過 200 個字元'],
        'code'       => ['max_length' => '商品代碼不能超過 50 個字元'],
        'hope_price' => ['max_length' => '希望價格不能超過 20 個字元'],
        'price'      => ['max_length' => '價格不能超過 20 個字元'],
        'point'      => ['max_length' => '點數不能超過 20 個字元'],
        'size'       => ['max_length' => '尺寸不能超過 500 個字元'],
        'action'     => ['max_length' => '動作不能超過 50 個字元']
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * 根據商品代碼取得單筆資料
     */
    public function getByCode(string $code): ?array
    {
        return $this->where('code', $code)->first();
    }

    /**
     * 根據動作類型取得資料
     */
    public function getByAction(string $action): array
    {
        return $this->where('action', $action)->findAll();
    }

    /**
     * 搜尋鞋子
     */
    public function search(array $params): array
    {
        $builder = $this->builder();

        if (!empty($params['eng_name'])) {
            $builder->like('eng_name', $params['eng_name']);
        }

        if (!empty($params['code'])) {
            $builder->like('code', $params['code']);
        }

        if (!empty($params['action'])) {
            $builder->where('action', $params['action']);
        }

        if (!empty($params['min_price'])) {
            $builder->where('price >=', $params['min_price']);
        }

        if (!empty($params['max_price'])) {
            $builder->where('price <=', $params['max_price']);
        }

        return $builder->get()->getResultArray();
    }
}

/**
 * 商品主資料表 Model（shoes_inf）
 */
class ShoesModel extends BaseShoeModel
{
    protected $table = 'shoes_inf';
}
