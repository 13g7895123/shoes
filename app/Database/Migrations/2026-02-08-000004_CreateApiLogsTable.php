<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateApiLogsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'api_key_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'API Key ID (NULL for public endpoints)',
            ],
            'api_key_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'API Key Name',
            ],
            'endpoint' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'comment'    => 'API Endpoint',
            ],
            'method' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'comment'    => 'HTTP Method (GET, POST, PUT, DELETE)',
            ],
            'request_headers' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Request Headers (JSON)',
            ],
            'request_body' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Request Body (JSON)',
            ],
            'request_params' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Query Parameters (JSON)',
            ],
            'response_code' => [
                'type'       => 'INT',
                'constraint' => 3,
                'comment'    => 'HTTP Response Code',
            ],
            'response_body' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Response Body (JSON)',
            ],
            'response_time' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,3',
                'null'       => true,
                'comment'    => 'Response time in seconds',
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
                'comment'    => 'Client IP Address',
            ],
            'user_agent' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'User Agent',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['success', 'error'],
                'default'    => 'success',
                'comment'    => 'Request Status',
            ],
            'error_message' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Error Message if any',
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('api_key_id');
        $this->forge->addKey('endpoint');
        $this->forge->addKey('status');
        $this->forge->addKey('created_at');
        $this->forge->createTable('api_logs');
    }

    public function down()
    {
        $this->forge->dropTable('api_logs');
    }
}
