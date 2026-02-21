<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddActionToShoesInf extends Migration
{
    private array $tables = ['shoes_inf', 'shoes_show_inf'];

    public function up()
    {
        foreach ($this->tables as $table) {
            if ($this->db->tableExists($table) && ! $this->db->fieldExists('action', $table)) {
                $this->forge->addColumn($table, [
                    'action' => [
                        'type'       => 'ENUM',
                        'constraint' => ['新增', '更新', '刪除'],
                        'default'    => '新增',
                        'null'       => true,
                        'after'      => 'size',
                    ],
                ]);

                // 新增索引
                $this->db->query("ALTER TABLE `{$table}` ADD INDEX `idx_action` (`action`)");
            }
        }
    }

    public function down()
    {
        foreach ($this->tables as $table) {
            if ($this->db->tableExists($table) && $this->db->fieldExists('action', $table)) {
                $this->forge->dropColumn($table, 'action');
            }
        }
    }
}
