<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNameToShoesTable extends Migration
{
    public function up()
    {
        if ($this->db->fieldExists('name', 'shoes_show_inf')) {
            return;
        }

        $fields = [
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '200',
                'null'       => true,
                'after'      => 'eng_name',
            ],
        ];

        $this->forge->addColumn('shoes_show_inf', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('shoes_show_inf', 'name');
    }
}
