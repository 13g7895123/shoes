<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>執行歷史記錄 - 管理後台</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .status-badge {
            @apply px-3 py-1 rounded-full text-xs font-semibold;
        }
        .status-running { @apply bg-blue-100 text-blue-800; }
        .status-success { @apply bg-green-100 text-green-800; }
        .status-failed { @apply bg-red-100 text-red-800; }
        .status-partial { @apply bg-yellow-100 text-yellow-800; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- 頂部導航 -->
        <nav class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-2xl font-bold text-gray-900">
                            <i class="fas fa-history mr-2"></i>執行歷史記錄
                        </h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="/admin/api-keys" class="text-gray-600 hover:text-gray-900">
                            <i class="fas fa-key mr-1"></i>API Keys
                        </a>
                        <a href="/admin/api-logs" class="text-gray-600 hover:text-gray-900">
                            <i class="fas fa-file-alt mr-1"></i>API 日誌
                        </a>
                        <a href="/" class="text-gray-600 hover:text-gray-900">
                            <i class="fas fa-home mr-1"></i>返回首頁
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- 篩選區 -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <form method="GET" action="/admin/execution-history" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">狀態</label>
                        <select name="status" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">全部</option>
                            <option value="running" <?= ($filters['status'] ?? '') === 'running' ? 'selected' : '' ?>>執行中</option>
                            <option value="success" <?= ($filters['status'] ?? '') === 'success' ? 'selected' : '' ?>>成功</option>
                            <option value="failed" <?= ($filters['status'] ?? '') === 'failed' ? 'selected' : '' ?>>失敗</option>
                            <option value="partial" <?= ($filters['status'] ?? '') === 'partial' ? 'selected' : '' ?>>部分成功</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">模式</label>
                        <select name="mode" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">全部</option>
                            <option value="production" <?= ($filters['mode'] ?? '') === 'production' ? 'selected' : '' ?>>正式</option>
                            <option value="test" <?= ($filters['mode'] ?? '') === 'test' ? 'selected' : '' ?>>測試</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">開始日期</label>
                        <input type="date" name="start_date" value="<?= $filters['start_date'] ?? '' ?>" 
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">結束日期</label>
                        <input type="date" name="end_date" value="<?= $filters['end_date'] ?? '' ?>" 
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                            <i class="fas fa-search mr-2"></i>查詢
                        </button>
                    </div>
                </form>
            </div>

            <!-- 統計卡片 -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">總執行次數</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1"><?= number_format($total) ?></p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-full">
                            <i class="fas fa-tasks text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">成功執行</p>
                            <p class="text-2xl font-bold text-green-600 mt-1">
                                <?= count(array_filter($executions, fn($e) => $e['status'] === 'success')) ?>
                            </p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-full">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">失敗執行</p>
                            <p class="text-2xl font-bold text-red-600 mt-1">
                                <?= count(array_filter($executions, fn($e) => $e['status'] === 'failed')) ?>
                            </p>
                        </div>
                        <div class="bg-red-100 p-3 rounded-full">
                            <i class="fas fa-times-circle text-red-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">執行中</p>
                            <p class="text-2xl font-bold text-blue-600 mt-1">
                                <?= count(array_filter($executions, fn($e) => $e['status'] === 'running')) ?>
                            </p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-full">
                            <i class="fas fa-spinner text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 執行歷史列表 -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">執行記錄列表</h2>
                </div>
                
                <?php if (empty($executions)): ?>
                    <div class="p-12 text-center">
                        <i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>
                        <p class="text-gray-500 text-lg">目前沒有執行記錄</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">執行 ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">開始時間</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">狀態</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">模式</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">商品數</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">新增</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">更新</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">略過</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">耗時</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($executions as $execution): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-xs font-mono text-gray-600">
                                                <?= substr($execution['execution_id'], 0, 8) ?>...
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= date('Y-m-d H:i:s', strtotime($execution['start_time'])) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="status-badge status-<?= $execution['status'] ?>">
                                                <?php
                                                    $statusText = [
                                                        'running' => '執行中',
                                                        'success' => '成功',
                                                        'failed' => '失敗',
                                                        'partial' => '部分成功'
                                                    ];
                                                    echo $statusText[$execution['status']] ?? $execution['status'];
                                                ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <?php if ($execution['mode'] === 'production'): ?>
                                                <span class="text-purple-600 font-semibold">正式</span>
                                            <?php else: ?>
                                                <span class="text-gray-600">測試</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= number_format($execution['total_products']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-semibold">
                                            <?= number_format($execution['created_count']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 font-semibold">
                                            <?= number_format($execution['updated_count']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            <?= number_format($execution['skipped_count']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php if ($execution['duration_seconds']): ?>
                                                <?= number_format($execution['duration_seconds'], 1) ?>s
                                            <?php else: ?>
                                                <span class="text-gray-400">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <a href="/admin/execution-history/detail/<?= $execution['execution_id'] ?>" 
                                               class="text-blue-600 hover:text-blue-800 font-medium">
                                                <i class="fas fa-eye mr-1"></i>查看詳情
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- 分頁 -->
                    <?php if ($total_pages > 1): ?>
                        <div class="px-6 py-4 border-t border-gray-200">
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-700">
                                    顯示第 <?= (($page - 1) * $page_size) + 1 ?> 到 <?= min($page * $page_size, $total) ?> 筆，共 <?= number_format($total) ?> 筆
                                </div>
                                <div class="flex space-x-2">
                                    <?php if ($page > 1): ?>
                                        <a href="?page=<?= $page - 1 ?><?= http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : '' ?>" 
                                           class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                                            上一頁
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                        <a href="?page=<?= $i ?><?= http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : '' ?>" 
                                           class="px-4 py-2 border rounded-lg <?= $i === $page ? 'bg-blue-600 text-white' : 'border-gray-300 hover:bg-gray-50' ?>">
                                            <?= $i ?>
                                        </a>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <a href="?page=<?= $page + 1 ?><?= http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : '' ?>" 
                                           class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                                            下一頁
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
