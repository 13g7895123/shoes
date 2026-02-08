<?php

namespace App\Filters;

use App\Models\ApiKeyModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ApiKeyAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // 健康檢查端點不需要認證
        if (strpos($request->getUri()->getPath(), '/health/') !== false) {
            return $request;
        }

        // 從標頭取得 API Key
        $apiKey = $request->getHeaderLine('X-API-Key');

        if (empty($apiKey)) {
            return $this->unauthorizedResponse('缺少 API Key');
        }

        $model = new ApiKeyModel();
        $record = $model->where('api_key', $apiKey)->where('status', 'active')->first();

        if (!$record) {
            return $this->unauthorizedResponse('API Key 無效');
        }

        // 檢查權限
        $permission = $record['permission'];
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();

        // 權限檢查邏輯
        if (!$this->checkPermission($method, $path, $permission)) {
            return $this->forbiddenResponse('權限不足');
        }

        // 將權限資訊加入請求，以便控制器使用
        $request->apiPermission = $permission;

        $model->update($record['id'], ['last_used_at' => date('Y-m-d H:i:s')]);

        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // 添加限流標頭
        $response->setHeader('X-RateLimit-Limit', '60');
        $response->setHeader('X-RateLimit-Remaining', '45');
        $response->setHeader('X-RateLimit-Reset', (string)time() + 60);

        return $response;
    }

    /**
     * 檢查權限
     */
    private function checkPermission(string $method, string $path, string $permission): bool
    {
        // ADMIN 有所有權限
        if ($permission === 'ADMIN') {
            return true;
        }

        // READ 權限檢查
        if ($permission === 'READ' && in_array($method, ['GET', 'HEAD'])) {
            return true;
        }

        // READ 權限允許查詢用 POST（check-status）
        if ($permission === 'READ' && $method === 'POST' && strpos($path, '/shoes/check-status') !== false) {
            return true;
        }

        // WRITE 權限檢查
        if ($permission === 'WRITE' && in_array($method, ['GET', 'HEAD', 'POST', 'PUT', 'PATCH'])) {
            return true;
        }

        // DELETE 權限檢查
        if ($permission === 'DELETE' && $method === 'DELETE') {
            return true;
        }

        return false;
    }

    /**
     * 回應未授權錯誤
     */
    private function unauthorizedResponse(string $message): ResponseInterface
    {
        $response = service('response');
        $response->setStatusCode(401);
        $response->setJSON([
            'status'    => 'error',
            'message'   => $message,
            'error_code' => 'UNAUTHORIZED',
            'timestamp' => date('Y-m-d\TH:i:s\Z')
        ]);
        return $response;
    }

    /**
     * 回應權限不足錯誤
     */
    private function forbiddenResponse(string $message): ResponseInterface
    {
        $response = service('response');
        $response->setStatusCode(403);
        $response->setJSON([
            'status'    => 'error',
            'message'   => $message,
            'error_code' => 'FORBIDDEN',
            'timestamp' => date('Y-m-d\TH:i:s\Z')
        ]);
        return $response;
    }
}
