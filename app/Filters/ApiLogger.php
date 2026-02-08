<?php

namespace App\Filters;

use App\Models\ApiLogModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ApiLogger implements FilterInterface
{
    protected $startTime;
    protected $apiKeyInfo;

    public function before(RequestInterface $request, $arguments = null)
    {
        // 记录开始时间
        $this->startTime = microtime(true);
        
        // 如果有 API Key 信息，保存起来
        if (isset($request->apiPermission)) {
            $this->apiKeyInfo = $request->apiKeyInfo ?? null;
        }
        
        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // 计算响应时间
        $responseTime = microtime(true) - $this->startTime;
        
        // 只记录 API 相关的请求
        $path = $request->getUri()->getPath();
        if (strpos($path, '/api/') !== 0) {
            return $response;
        }

        try {
            $logModel = new ApiLogModel();
            
            // 获取 API Key 信息
            $apiKey = $request->getHeaderLine('X-API-Key');
            $apiKeyId = null;
            $apiKeyName = null;
            
            if (!empty($apiKey)) {
                $apiKeyModel = new \App\Models\ApiKeyModel();
                $keyRecord = $apiKeyModel->where('api_key', $apiKey)->first();
                if ($keyRecord) {
                    $apiKeyId = $keyRecord['id'];
                    $apiKeyName = $keyRecord['name'];
                }
            }

            // 获取请求头（过滤敏感信息）
            $headers = $request->headers();
            $sanitizedHeaders = [];
            foreach ($headers as $name => $value) {
                if (!in_array(strtolower($name), ['authorization', 'x-api-key', 'cookie'])) {
                    $sanitizedHeaders[$name] = $value->getValue();
                }
            }

            // 获取请求体
            $requestBody = null;
            $rawBody = $request->getBody();
            if (!empty($rawBody)) {
                // 尝试解析为 JSON
                $jsonBody = json_decode($rawBody, true);
                $requestBody = $jsonBody ? json_encode($jsonBody, JSON_UNESCAPED_UNICODE) : $rawBody;
                
                // 限制大小
                if (strlen($requestBody) > 10000) {
                    $requestBody = substr($requestBody, 0, 10000) . '... (truncated)';
                }
            }

            // 获取查询参数
            $queryParams = $request->getGet();
            $requestParams = !empty($queryParams) ? json_encode($queryParams, JSON_UNESCAPED_UNICODE) : null;

            // 获取响应体
            $responseBody = $response->getBody();
            if (strlen($responseBody) > 10000) {
                $responseBody = substr($responseBody, 0, 10000) . '... (truncated)';
            }

            // 判断状态
            $statusCode = $response->getStatusCode();
            $status = ($statusCode >= 200 && $statusCode < 400) ? 'success' : 'error';
            
            // 提取错误信息
            $errorMessage = null;
            if ($status === 'error') {
                $jsonResponse = json_decode($responseBody, true);
                $errorMessage = $jsonResponse['message'] ?? $jsonResponse['error'] ?? 'Unknown error';
                if (is_array($errorMessage)) {
                    $errorMessage = json_encode($errorMessage, JSON_UNESCAPED_UNICODE);
                }
            }

            // 记录日志
            $logModel->logRequest([
                'api_key_id'       => $apiKeyId,
                'api_key_name'     => $apiKeyName,
                'endpoint'         => $path,
                'method'           => $request->getMethod(),
                'request_headers'  => json_encode($sanitizedHeaders, JSON_UNESCAPED_UNICODE),
                'request_body'     => $requestBody,
                'request_params'   => $requestParams,
                'response_code'    => $statusCode,
                'response_body'    => $responseBody,
                'response_time'    => round($responseTime, 3),
                'ip_address'       => $request->getIPAddress(),
                'user_agent'       => $request->getUserAgent()->__toString(),
                'status'           => $status,
                'error_message'    => $errorMessage,
            ]);
        } catch (\Exception $e) {
            // 记录日志失败不应影响正常响应
            log_message('error', 'Failed to log API request: ' . $e->getMessage());
        }

        return $response;
    }
}
