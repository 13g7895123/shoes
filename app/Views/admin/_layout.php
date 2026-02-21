<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('page_title') ?> - 鞋類爬蟲系統</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* 統一 badge 樣式 */
        .badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 9999px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
        .badge-success  { background: #d1fae5; color: #065f46; }
        .badge-error    { background: #fee2e2; color: #991b1b; }
        .badge-warn     { background: #fef3c7; color: #92400e; }
        .badge-info     { background: #dbeafe; color: #1e40af; }
        .badge-gray     { background: #f3f4f6; color: #374151; }
        .badge-get      { background: #dbeafe; color: #1e40af; }
        .badge-post     { background: #fef3c7; color: #92400e; }
        .badge-put      { background: #e0e7ff; color: #4338ca; }
        .badge-delete   { background: #fce7f3; color: #9f1239; }

        /* 狀態 badge */
        .status-running { background: #dbeafe; color: #1e40af; }
        .status-success { background: #d1fae5; color: #065f46; }
        .status-failed  { background: #fee2e2; color: #991b1b; }
        .status-partial { background: #fef3c7; color: #92400e; }

        /* 動作 badge */
        .action-create  { background: #d1fae5; color: #065f46; }
        .action-update  { background: #dbeafe; color: #1e40af; }
        .action-skip    { background: #f3f4f6; color: #374151; }
        .action-delete  { background: #fee2e2; color: #991b1b; }

        /* Sidebar active */
        .nav-item { display: flex; align-items: center; gap: 10px; padding: 10px 14px; border-radius: 8px; font-size: 14px; font-weight: 500; color: #94a3b8; text-decoration: none; transition: all 0.15s; }
        .nav-item:hover  { background: #1e293b; color: #f1f5f9; }
        .nav-item.active { background: #3b82f6; color: #fff; }
        .nav-item .icon  { width: 18px; text-align: center; }

        /* 頁面專屬 CSS 插入點 */
        <?= $this->renderSection('head_styles') ?>
    </style>
    <?= $this->renderSection('head_scripts') ?>
</head>
<body class="bg-gray-100 text-gray-900">

<?php
    // 計算目前路徑以高亮側欄
    $currentPath = '/' . ltrim(uri_string(), '/');
?>

<div class="flex min-h-screen">

    <!-- ===== Sidebar ===== -->
    <aside class="w-56 flex-shrink-0 bg-gray-900 flex flex-col" style="min-height:100vh;">
        <div class="px-5 py-5 border-b border-gray-700">
            <div class="text-white font-bold text-base leading-tight">🥿 鞋類爬蟲</div>
            <div class="text-gray-400 text-xs mt-1">管理後台</div>
        </div>

        <nav class="flex-1 px-3 py-4 space-y-1">
            <a href="/"
               class="nav-item <?= $currentPath === '/' ? 'active' : '' ?>">
                <i class="fas fa-home icon"></i> 首頁
            </a>

            <div class="pt-3 pb-1 px-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">API 管理</div>

            <a href="/admin/api-keys"
               class="nav-item <?= str_starts_with($currentPath, '/admin/api-keys') ? 'active' : '' ?>">
                <i class="fas fa-key icon"></i> API Key 管理
            </a>
            <a href="/admin/api-logs"
               class="nav-item <?= str_starts_with($currentPath, '/admin/api-logs') ? 'active' : '' ?>">
                <i class="fas fa-file-alt icon"></i> API 使用記錄
            </a>

            <div class="pt-3 pb-1 px-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">執行歷史</div>

            <a href="/admin/execution-history"
               class="nav-item <?= str_starts_with($currentPath, '/admin/execution-history') ? 'active' : '' ?>">
                <i class="fas fa-history icon"></i> 執行歷史
            </a>

            <div class="pt-3 pb-1 px-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">資料工具</div>

            <a href="/admin/data-sync"
               class="nav-item <?= str_starts_with($currentPath, '/admin/data-sync') ? 'active' : '' ?>">
                <i class="fas fa-wrench icon"></i> 英文名稱修復
            </a>
        </nav>

        <div class="px-5 py-4 border-t border-gray-700 text-xs text-gray-500">
            <?= date('Y-m-d') ?>
        </div>
    </aside>

    <!-- ===== Main area ===== -->
    <div class="flex-1 flex flex-col overflow-hidden">

        <!-- 頂部 header -->
        <header class="bg-white border-b border-gray-200 px-8 py-4 flex items-center justify-between flex-shrink-0">
            <div>
                <h1 class="text-xl font-bold text-gray-900">
                    <?= $this->renderSection('page_title') ?>
                </h1>
                <?php $crumbs = trim($this->renderSection('page_breadcrumbs')); if ($crumbs): ?>
                <div class="flex items-center gap-1 mt-1 text-sm text-gray-500">
                    <?= $crumbs ?>
                </div>
                <?php endif; ?>
            </div>
            <div class="flex items-center gap-3">
                <?= $this->renderSection('header_actions') ?>
            </div>
        </header>

        <!-- 內容區 -->
        <main class="flex-1 overflow-y-auto p-8">

            <!-- Flash 訊息 -->
            <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-6 p-4 bg-green-50 border border-green-300 rounded-lg flex items-center gap-3">
                <i class="fas fa-check-circle text-green-600 text-lg"></i>
                <span class="text-green-800 font-medium"><?= esc(session()->getFlashdata('success')) ?></span>
            </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-300 rounded-lg flex items-center gap-3">
                <i class="fas fa-times-circle text-red-600 text-lg"></i>
                <span class="text-red-800 font-medium"><?= esc(session()->getFlashdata('error')) ?></span>
            </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('warning')): ?>
            <div class="mb-6 p-4 bg-yellow-50 border border-yellow-300 rounded-lg flex items-center gap-3">
                <i class="fas fa-exclamation-triangle text-yellow-600 text-lg"></i>
                <span class="text-yellow-800 font-medium"><?= esc(session()->getFlashdata('warning')) ?></span>
            </div>
            <?php endif; ?>

            <?= $this->renderSection('content') ?>
        </main>
    </div>
</div>

<?= $this->renderSection('modals') ?>
<?= $this->renderSection('scripts') ?>
</body>
</html>
