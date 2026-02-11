<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateExecutionHistoryTables extends Migration
{
    public function up()
    {
        // 執行歷史主表
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'execution_id' => [
                'type' => 'VARCHAR',
                'constraint' => 36,
                'unique' => true,
                'comment' => 'UUID 執行ID',
            ],
            'start_time' => [
                'type' => 'DATETIME',
                'null' => false,
                'comment' => '開始時間',
            ],
            'end_time' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => '結束時間',
            ],
            'total_products' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
                'comment' => '總商品數',
            ],
            'created_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'comment' => '新增數量',
            ],
            'updated_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'comment' => '更新數量',
            ],
            'skipped_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'comment' => '略過數量',
            ],
            'failed_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'comment' => '失敗數量',
            ],
            'duration_seconds' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
                'comment' => '執行時長（秒）',
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => false,
                'comment' => '執行狀態: running/success/failed/partial',
            ],
            'mode' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => false,
                'comment' => '執行模式: test/production',
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '錯誤訊息（如有）',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->addKey('execution_id', false, false, 'idx_execution_id');
        $this->forge->addKey('start_time', false, false, 'idx_start_time');
        $this->forge->addKey('status', false, false, 'idx_status');
        $this->forge->createTable('execution_history', true, ['ENGINE' => 'InnoDB', 'COMMENT' => '執行歷史記錄', 'CHARSET' => 'utf8mb4']);

        // 商品變更記錄表
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'execution_id' => [
                'type' => 'VARCHAR',
                'constraint' => 36,
                'null' => false,
                'comment' => '關聯執行ID',
            ],
            'product_code' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
                'comment' => '商品編號',
            ],
            'product_name' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
                'comment' => '商品名稱',
            ],
            'action_type' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => false,
                'comment' => '動作類型: create/update/skip',
            ],
            'before_price' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => '變更前價格',
            ],
            'after_price' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => '變更後價格',
            ],
            'before_size' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '變更前尺寸',
            ],
            'after_size' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '變更後尺寸',
            ],
            'before_hope_price' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => '變更前希望價格',
            ],
            'after_hope_price' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => '變更後希望價格',
            ],
            'before_point' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => '變更前點數',
            ],
            'after_point' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => '變更後點數',
            ],
            'change_reason' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => '變更原因',
            ],
            'has_price_change' => [
                'type' => 'BOOLEAN',
                'default' => false,
                'comment' => '價格是否變動',
            ],
            'has_size_change' => [
                'type' => 'BOOLEAN',
                'default' => false,
                'comment' => '尺寸是否變動',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->addKey('execution_id', false, false, 'idx_pcl_execution_id');
        $this->forge->addKey('product_code', false, false, 'idx_pcl_product_code');
        $this->forge->addKey('action_type', false, false, 'idx_pcl_action_type');
        $this->forge->addKey('created_at', false, false, 'idx_pcl_created_at');
        $this->forge->addForeignKey('execution_id', 'execution_history', 'execution_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('product_change_log', true, ['ENGINE' => 'InnoDB', 'COMMENT' => '商品變更記錄', 'CHARSET' => 'utf8mb4']);
    }

    public function down()
    {
        $this->forge->dropTable('product_change_log', true);
        $this->forge->dropTable('execution_history', true);
    }
}
