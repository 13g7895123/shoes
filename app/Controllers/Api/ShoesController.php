<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

class ShoesController extends ResourceController
{
    use ResponseTrait;

    protected $modelName = 'App\Models\ShoesModel';
    protected $format    = 'json';

    /**
     * 取得所有鞋子資料
     * GET /api/shoes
     */
    public function index()
    {
        try {
            // 詳細除錯資訊
            $config = config('Database');
            $dbConfig = $config->default;
            
            log_message('debug', '[/api/shoes] 嘗試連接資料庫');
            log_message('debug', '[/api/shoes] Hostname: ' . ($dbConfig['hostname'] ?? 'not set'));
            log_message('debug', '[/api/shoes] Database: ' . ($dbConfig['database'] ?? 'not set'));
            log_message('debug', '[/api/shoes] Port: ' . ($dbConfig['port'] ?? 'not set'));
            log_message('debug', '[/api/shoes] Username: ' . ($dbConfig['username'] ?? 'not set'));
            
            $shoes = $this->model->findAll();
            
            log_message('debug', '[/api/shoes] 資料取得成功，筆數: ' . count($shoes));

            return $this->respond([
                'success' => true,
                'data' => $shoes,
                'message' => '資料取得成功'
            ]);
        } catch (\Exception $e) {
            $config = config('Database');
            $dbConfig = $config->default;
            
            log_message('error', '[/api/shoes] 錯誤: ' . $e->getMessage());
            log_message('error', '[/api/shoes] 錯誤類型: ' . get_class($e));
            log_message('error', '[/api/shoes] Stack trace: ' . $e->getTraceAsString());
            
            // 測試連接
            $connectionTest = [
                'hostname' => $dbConfig['hostname'] ?? 'not set',
                'database' => $dbConfig['database'] ?? 'not set',
                'port' => $dbConfig['port'] ?? 'not set',
                'username' => $dbConfig['username'] ?? 'not set',
            ];
            
            // 嘗試連接資料庫獲取更多資訊
            $db = \Config\Database::connect();
            try {
                $db->connect();
                $connectionTest['can_connect'] = 'YES';
                $connectionTest['mysql_version'] = $db->getVersion();
            } catch (\Exception $connErr) {
                $connectionTest['can_connect'] = 'NO';
                $connectionTest['connection_error'] = $connErr->getMessage();
                log_message('error', '[/api/shoes] 連接測試失敗: ' . $connErr->getMessage());
            }
            
            return $this->fail([
                'success' => false,
                'message' => '資料取得失敗: ' . $e->getMessage(),
                'debug_info' => [
                    'error_type' => get_class($e),
                    'db_config' => $connectionTest,
                    'php_version' => PHP_VERSION,
                    'ci_environment' => env('CI_ENVIRONMENT', 'not set'),
                    'server_addr' => $_SERVER['SERVER_ADDR'] ?? 'unknown',
                    'server_name' => $_SERVER['SERVER_NAME'] ?? 'unknown',
                ]
            ], 500);
        }
    }

    /**
     * 取得單筆鞋子資料
     * GET /api/shoes/{id}
     */
    public function show($id = null)
    {
        try {
            $shoe = $this->model->find($id);

            if (!$shoe) {
                return $this->failNotFound([
                    'success' => false,
                    'message' => '找不到該筆資料'
                ]);
            }

            return $this->respond([
                'success' => true,
                'data' => $shoe,
                'message' => '資料取得成功'
            ]);
        } catch (\Exception $e) {
            return $this->fail([
                'success' => false,
                'message' => '資料取得失敗: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 新增鞋子資料
     * POST /api/shoes
     */
    public function create()
    {
        try {
            $data = $this->request->getJSON(true);

            if (!$this->validate($this->model->getValidationRules())) {
                return $this->failValidationErrors($this->validator->getErrors());
            }

            $id = $this->model->insert($data);

            if (!$id) {
                return $this->fail([
                    'success' => false,
                    'message' => '資料新增失敗'
                ], 400);
            }

            return $this->respondCreated([
                'success' => true,
                'data' => ['id' => $id],
                'message' => '資料新增成功'
            ]);
        } catch (\Exception $e) {
            return $this->fail([
                'success' => false,
                'message' => '資料新增失敗: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 更新鞋子資料
     * PUT /api/shoes/{id}
     */
    public function update($id = null)
    {
        try {
            $data = $this->request->getJSON(true);

            if (!$this->model->find($id)) {
                return $this->failNotFound([
                    'success' => false,
                    'message' => '找不到該筆資料'
                ]);
            }

            if (!$this->validate($this->model->getValidationRules())) {
                return $this->failValidationErrors($this->validator->getErrors());
            }

            $result = $this->model->update($id, $data);

            if (!$result) {
                return $this->fail([
                    'success' => false,
                    'message' => '資料更新失敗'
                ], 400);
            }

            return $this->respond([
                'success' => true,
                'message' => '資料更新成功'
            ]);
        } catch (\Exception $e) {
            return $this->fail([
                'success' => false,
                'message' => '資料更新失敗: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 刪除鞋子資料
     * DELETE /api/shoes/{id}
     */
    public function delete($id = null)
    {
        try {
            if (!$this->model->find($id)) {
                return $this->failNotFound([
                    'success' => false,
                    'message' => '找不到該筆資料'
                ]);
            }

            $result = $this->model->delete($id);

            if (!$result) {
                return $this->fail([
                    'success' => false,
                    'message' => '資料刪除失敗'
                ], 400);
            }

            return $this->respondDeleted([
                'success' => true,
                'message' => '資料刪除成功'
            ]);
        } catch (\Exception $e) {
            return $this->fail([
                'success' => false,
                'message' => '資料刪除失敗: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 取得表格內容（相容舊版 AJAX）
     * GET /api/shoes/table-content
     */
    public function tableContent()
    {
        try {
            $shoes = $this->model->findAll();

            return $this->respond([
                'success' => true,
                'data' => $shoes
            ]);
        } catch (\Exception $e) {
            $db = \Config\Database::connect();
            $hostname = $db->hostname ?? 'unknown';
            return $this->fail([
                'success' => false,
                'message' => '資料取得失敗: ' . $e->getMessage() . ' (Host: ' . $hostname . ')'
            ], 500);
        }
    }

    /**
     * 除錯用：顯示資料庫連線資訊
     * GET /api/shoes/db-info
     */
    public function dbInfo()
    {
        $db = \Config\Database::connect();
        $config = config('Database');

        $info = [
            'hostname' => $config->default['hostname'] ?? 'not set',
            'database' => $config->default['database'] ?? 'not set',
            'username' => $config->default['username'] ?? 'not set',
            'password' => str_repeat('*', strlen($config->default['password'] ?? '')),
            'port' => $config->default['port'] ?? 'not set',
            'DBDriver' => $config->default['DBDriver'] ?? 'not set',
        ];

        // 測試連線
        try {
            $db->connect();
            $info['connection_status'] = 'SUCCESS';
            $info['server_info'] = $db->getVersion();
            
            // 測試簡單查詢
            $result = $db->query('SELECT 1 as test');
            $info['query_test'] = $result ? 'SUCCESS' : 'FAILED';
            
        } catch (\Exception $e) {
            $info['connection_status'] = 'FAILED';
            $info['error'] = $e->getMessage();
            $info['error_type'] = get_class($e);
            $info['error_code'] = $e->getCode();
        }

        // 系統資訊
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'mysqli_enabled' => extension_loaded('mysqli') ? 'YES' : 'NO',
            'pdo_mysql_enabled' => extension_loaded('pdo_mysql') ? 'YES' : 'NO',
            'server_addr' => $_SERVER['SERVER_ADDR'] ?? 'unknown',
            'server_name' => $_SERVER['SERVER_NAME'] ?? 'unknown',
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
        ];

        // 網路測試
        $networkTest = [];
        $hostname = $config->default['hostname'] ?? 'localhost';
        $port = $config->default['port'] ?? 3306;
        
        // 測試 DNS 解析
        $ip = gethostbyname($hostname);
        $networkTest['hostname'] = $hostname;
        $networkTest['resolved_ip'] = $ip;
        $networkTest['dns_resolves'] = ($ip !== $hostname) ? 'YES' : 'NO (可能無法解析)';
        
        // 測試端口連接
        $connection = @fsockopen($hostname, $port, $errno, $errstr, 5);
        if ($connection) {
            $networkTest['port_accessible'] = 'YES';
            fclose($connection);
        } else {
            $networkTest['port_accessible'] = 'NO';
            $networkTest['socket_error'] = "$errno: $errstr";
        }

        return $this->respond([
            'success' => true,
            'db_config' => $info,
            'system_info' => $systemInfo,
            'network_test' => $networkTest,
            'env' => [
                'CI_ENVIRONMENT' => env('CI_ENVIRONMENT', 'not set'),
                'app.baseURL' => env('app.baseURL', 'not set'),
            ],
            'suggestions' => $this->getDiagnosticSuggestions($info, $networkTest)
        ]);
    }

    /**
     * 根據診斷結果提供建議
     */
    private function getDiagnosticSuggestions($dbInfo, $networkTest)
    {
        $suggestions = [];

        // 如果 DNS 無法解析
        if ($networkTest['dns_resolves'] === 'NO (可能無法解析)') {
            $suggestions[] = "hostname '{$networkTest['hostname']}' 無法解析。在 VPS 上可能需要使用 'localhost' 或 '127.0.0.1' 而不是 'mysql'";
        }

        // 如果端口無法訪問
        if ($networkTest['port_accessible'] === 'NO') {
            $suggestions[] = "MySQL 端口 {$dbInfo['port']} 無法連接。請檢查：1) MySQL 服務是否運行 (sudo systemctl status mysql), 2) 防火牆設置, 3) MySQL 是否監聽正確的地址";
            $suggestions[] = "執行命令檢查：netstat -tlnp | grep {$dbInfo['port']}";
        }

        // 如果連接失敗但端口可訪問
        if ($dbInfo['connection_status'] === 'FAILED' && $networkTest['port_accessible'] === 'YES') {
            $suggestions[] = "端口可以訪問但連接失敗，可能是認證問題。請檢查用戶名、密碼和資料庫權限";
        }

        // Docker 環境檢查
        if ($networkTest['hostname'] === 'mysql') {
            $suggestions[] = "hostname 是 'mysql' (Docker 容器名)。在 VPS 上如果沒有使用 Docker，請改為 'localhost' 或 '127.0.0.1'";
        }

        return $suggestions;
    }
}
