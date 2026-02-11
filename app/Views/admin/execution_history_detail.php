<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>執行詳情 - <?= substr($execution['execution_id'], 0, 8) ?></title>
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
        
        .action-badge {
            @apply px-2 py-1 rounded text-xs font-semibold;
        }
        .action-create { @apply bg-green-100 text-green-800; }
        .action-update { @apply bg-blue-100 text-blue-800; }
        .action-skip { @apply bg-gray-100 text-gray-800; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- 頂部導航 -->
        <nav class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <a href="/admin/execution-history" class="text-blue-600 hover:text-blue-800 mr-4">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h1 class="text-2xl font-bold text-gray-900">
                            <i class="fas fa-info-circle mr-2"></i>執行詳情
                        </h1>
                    </div>
                </div>
            </div>
        </nav>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- 執行資訊卡片 -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">基本資訊</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-gray-600">執行 ID:</dt>
                                <dd class="font-mono text-sm text-gray-900"><?= $execution['execution_id'] ?></dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600">狀態:</dt>
                                <dd>
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
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600">執行模式:</dt>
                                <dd class="font-semibold">
                                    <?= $execution['mode'] === 'production' ? '正式環境' : '測試環境' ?>
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600">開始時間:</dt>
                                <dd class="text-gray-900"><?= date('Y-m-d H:i:s', strtotime($execution['start_time'])) ?></dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600">結束時間:</dt>
                                <dd class="text-gray-900">
                                    <?= $execution['end_time'] ? date('Y-m-d H:i:s', strtotime($execution['end_time'])) : '<span class="text-gray-400">執行中...</span>' ?>
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600">執行時長:</dt>
                                <dd class="text-gray-900 font-semibold">
                                    <?= $execution['duration_seconds'] ? number_format($execution['duration_seconds'], 2) . ' 秒' : '-' ?>
                                </dd>
                            </div>
                        </dl>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">執行統計</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-gray-600">總商品數:</dt>
                                <dd class="text-gray-900 font-semibold"><?= number_format($execution['total_products']) ?></dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600">新增:</dt>
                                <dd class="text-green-600 font-bold text-lg"><?= number_format($execution['created_count']) ?></dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600">更新:</dt>
                                <dd class="text-blue-600 font-bold text-lg"><?= number_format($execution['updated_count']) ?></dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600">略過:</dt>
                                <dd class="text-gray-600 font-semibold"><?= number_format($execution['skipped_count']) ?></dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600">失敗:</dt>
                                <dd class="text-red-600 font-semibold"><?= number_format($execution['failed_count']) ?></dd>
                            </div>
                        </dl>
                        
                        <?php if ($execution['error_message']): ?>
                            <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                                <p class="text-sm text-red-800">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    <?= esc($execution['error_message']) ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- 動作類型統計 -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">新增商品</p>
                            <p class="text-3xl font-bold text-green-600 mt-1"><?= number_format($action_stats['create']) ?></p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-full">
                            <i class="fas fa-plus-circle text-green-600 text-2xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">更新商品</p>
                            <p class="text-3xl font-bold text-blue-600 mt-1"><?= number_format($action_stats['update']) ?></p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-full">
                            <i class="fas fa-edit text-blue-600 text-2xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">略過商品</p>
                            <p class="text-3xl font-bold text-gray-600 mt-1"><?= number_format($action_stats['skip']) ?></p>
                        </div>
                        <div class="bg-gray-100 p-3 rounded-full">
                            <i class="fas fa-forward text-gray-600 text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 變更記錄列表 -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-gray-900">商品變更記錄</h2>
                    <span class="text-sm text-gray-500"><?= count($changes) ?> 筆記錄</span>
                </div>
                
                <?php if (empty($changes)): ?>
                    <div class="p-12 text-center">
                        <i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>
                        <p class="text-gray-500 text-lg">目前沒有變更記錄</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">商品編號</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">商品名稱</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">動作</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">價格變化</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">尺寸變化</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">變更原因</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">記錄時間</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($changes as $change): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm font-mono text-gray-900"><?= esc($change['product_code']) ?></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-sm text-gray-900"><?= esc($change['product_name'] ?? '-') ?></span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="action-badge action-<?= $change['action_type'] ?>">
                                                <?php
                                                    $actionText = [
                                                        'create' => '新增',
                                                        'update' => '更新',
                                                        'skip' => '略過'
                                                    ];
                                                    echo $actionText[$change['action_type']] ?? $change['action_type'];
                                                ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <?php if ($change['has_price_change']): ?>
                                                <div class="text-gray-600">
                                                    <span class="line-through"><?= esc($change['before_price']) ?></span>
                                                    <i class="fas fa-arrow-right mx-1 text-xs"></i>
                                                    <span class="font-semibold text-green-600"><?= esc($change['after_price']) ?></span>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-gray-400"><?= esc($change['after_price'] ?? '-') ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            <?php if ($change['has_size_change']): ?>
                                                <div class="text-gray-600 max-w-xs">
                                                    <div class="truncate"><?= esc($change['before_size']) ?></div>
                                                    <i class="fas fa-arrow-down text-xs text-blue-500"></i>
                                                    <div class="truncate font-semibold text-blue-600"><?= esc($change['after_size']) ?></div>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-gray-400 truncate block max-w-xs"><?= esc($change['after_size'] ?? '-') ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600 max-w-md">
                                            <?= esc($change['change_reason'] ?? '-') ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= date('H:i:s', strtotime($change['created_at'])) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
