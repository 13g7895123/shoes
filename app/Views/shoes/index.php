<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bonus Shoes - 鞋子資料管理系統</title>

    <!-- 引入Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- 引入jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <style>
        /* 全域字體放大 */
        body {
            font-size: 16px;
        }
        
        /* 隱藏 CodeIgniter Debug Toolbar */
        #toolbarContainer {
            display: none !important;
        }
        
        /* 自定義滾動條 */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        
        /* 載入動畫 */
        .loading-spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3b82f6;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* 淡入動畫 */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* 表格動畫 */
        tbody tr {
            transition: all 0.2s ease;
        }
        
        tbody tr:hover {
            transform: translateX(4px);
            box-shadow: -4px 0 0 #3b82f6;
        }
        
        /* 圖片容器 */
        .img-container {
            position: relative;
            overflow: hidden;
            border-radius: 8px;
            transition: transform 0.3s ease;
        }
        
        .img-container:hover {
            transform: scale(1.05);
        }
        
        .img-container img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        /* 狀態徽章 */
        .status-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .badge-add {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .badge-update {
            background-color: #fed7aa;
            color: #92400e;
        }
        
        .badge-delete {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        /* 分頁按鈕 */
        .pagination-btn {
            transition: all 0.2s ease;
        }
        
        .pagination-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .pagination-btn.active {
            background-color: #3b82f6 !important;
            color: white !important;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="bg-blue-600 rounded-lg p-2">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Bonus Shoes</h1>
                        <p class="text-base text-gray-500">鞋子資料管理系統</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/admin/api-keys" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors text-base font-medium">
                        🔑 API Key
                    </a>
                    <a href="/admin/api-logs" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-base font-medium">
                        📊 使用記錄
                    </a>
                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700">每頁顯示</label>
                        <select id="itemsPerPage" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-base">
                            <option value="10">10 筆</option>
                            <option value="20" selected>20 筆</option>
                            <option value="50">50 筆</option>
                            <option value="100">100 筆</option>
                            <option value="all">全部</option>
                        </select>
                    </div>
                    <button id="refreshBtn" class="inline-flex items-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200 shadow-sm hover:shadow-md text-base">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        重新整理
                    </button>
                    <div id="totalCount" class="text-base text-gray-600 bg-gray-100 px-5 py-2.5 rounded-lg">
                        載入中...
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- 搜尋和篩選區域 -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-base font-medium text-gray-700 mb-2">搜尋</label>
                    <input type="text" id="searchInput" placeholder="搜尋商品名稱或代碼..." class="w-full px-4 py-2.5 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>
                <div>
                    <label class="block text-base font-medium text-gray-700 mb-2">狀態篩選</label>
                    <select id="actionFilter" class="w-full px-4 py-2.5 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <option value="">全部狀態</option>
                        <option value="新增">新增</option>
                        <option value="更新">更新</option>
                        <option value="刪除">刪除</option>
                    </select>
                </div>
                <div>
                    <label class="block text-base font-medium text-gray-700 mb-2">價格範圍</label>
                    <select id="priceFilter" class="w-full px-4 py-2.5 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <option value="">全部價格</option>
                        <option value="0-3000">0 - 3,000</option>
                        <option value="3000-5000">3,000 - 5,000</option>
                        <option value="5000-10000">5,000 - 10,000</option>
                        <option value="10000+">10,000+</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Loading Spinner -->
        <div id="loadingSpinner" class="loading-spinner"></div>

        <!-- 資料表格 -->
        <div id="tableContainer" class="bg-white rounded-xl shadow-sm overflow-hidden" style="display: none;">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">圖片</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">商品名稱</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">商品代碼</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">希望價格</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">價格</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">點數</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">尺寸</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">狀態</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200"></tbody>
                </table>
            </div>
        </div>

        <!-- 分頁控制 -->
        <div id="paginationContainer" class="bg-white rounded-xl shadow-sm p-6 mt-6 hidden">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-base text-gray-600">
                    顯示第 <span id="rangeStart" class="font-semibold text-gray-900">1</span> - 
                    <span id="rangeEnd" class="font-semibold text-gray-900">20</span> 筆，
                    共 <span id="totalItems" class="font-semibold text-gray-900">0</span> 筆
                </div>
                
                <div class="flex items-center space-x-2">
                    <button id="firstPageBtn" class="pagination-btn px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed text-base">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                        </svg>
                    </button>
                    <button id="prevPageBtn" class="pagination-btn px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed text-base">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        let filteredData = [];
        let currentPage = 1;
        let itemsPerPage = 20;
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    
                    <div id="pageNumbers" class="flex items-center space-x-2"></div>
                    
                    <button id="nextPageBtn" class="pagination-btn px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed text-base">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                    <button id="lastPageBtn" class="pagination-btn px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed text-base">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- 無資料訊息 -->
        <div id="noDataMessage" class="hidden">
            <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                <svg class="w-20 h-20 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">目前沒有資料</h3>
                <p class="text-gray-500">請稍後再試或聯繫管理員</p>
            </div>
        </div>
    </main>

    <script>
        // API 端點配置
        const API_BASE_URL = '<?= base_url('api/shoes') ?>';
        const IMG_SIZE = 100;
        let allTableData = [];
        let filteredData = [];
        let currentPage = 1;
        let itemsPerPage = 20;

        // 初始化
        function init() {
            loadTableData();
            setupEventListeners();
        }

        // 載入資料
        async function loadTableData() {
            showLoading();
            
            try {
                const response = await fetch(API_BASE_URL);
                const result = await response.json();

                hideLoading();

                if (result.success && result.data.length > 0) {
                    allTableData = result.data;
                    filteredData = result.data;
                    updateTotalCount(result.data.length);
                    currentPage = 1;
                    renderTable(getCurrentPageData());
                    renderPagination();
                } else {
                    showNoDataMessage();
                }
            } catch (error) {
                console.error('載入資料失敗:', error);
                hideLoading();
                showNoDataMessage();
            }
        }

        // 渲染表格
        function renderTable(data) {
            const tbody = $('tbody');
            tbody.empty();
            
            if (!data || data.length === 0) {
                showNoDataMessage();
                return;
            }
            $('#paginationContainer').removeClass('hidden');
            
            $('#tableContainer').show();
            $('#noDataMessage').addClass('hidden');
            
            data.forEach((item, index) => {
                const statusBadge = getStatusBadge(item.action);
                const imageUrl = `https://www.kishispo.net/upload/save_image/${item.code}.jpg`;
                const delay = index * 30;
                
                const row = $(`
                    <tr class="hover:bg-gray-50 transition-colors" style="animation: fadeIn 0.3s ease ${delay}ms both;">
                        <td class="px-6 py-4 whitespace-nowrap text-lg font-medium text-gray-900">
                            ${item.id || '-'}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="img-container" style="width: ${IMG_SIZE}px; height: ${IMG_SIZE}px;">
                                <img src="${imageUrl}" 
                                     alt="${item.eng_name || '商品圖片'}"
                                     loading="lazy"
                                     onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22${IMG_SIZE}%22 height=%22${IMG_SIZE}%22%3E%3Crect fill=%22%23e5e7eb%22 width=%22${IMG_SIZE}%22 height=%22${IMG_SIZE}%22/%3E%3Ctext fill=%22%239ca3af%22 x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 font-family=%22sans-serif%22 font-size=%2212%22%3E無圖片%3C/text%3E%3C/svg%3E';">
                            </div>
                        </td>
                        <td class="px-6 py-4 text-lg text-gray-900">
                            <div class="font-medium">${item.eng_name || '-'}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-lg text-gray-600 font-mono">
                            ${item.code || '-'}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-lg text-gray-900">
                            ${formatPrice(item.hope_price)}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-lg font-semibold text-gray-900">
                            ${formatPrice(item.price)}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-lg text-gray-600">
                            ${item.point || '-'}
                        </td>
                        <td class="px-6 py-4 text-lg text-gray-600 max-w-xs break-words">
                            ${item.size || '-'}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            ${statusBadge}
                        </td>
                    </tr>
                `);
                
                tbody.append(row);
            });
        }

        // 取得狀態徽章
        function getStatusBadge(action) {
            const badges = {
                '新增': '<span class="status-badge badge-add">新增</span>',
                '更新': '<span class="status-badge badge-update">更新</span>',
                '刪除': '<span class="status-badge badge-delete">刪除</span>'
            };
            
            return badges[action] || `<span class="status-badge">${action || '-'}</span>`;
        }

        // 格式化價格
        function formatPrice(price) {
            if (!price || price === '-') return '-';
            return `NT$ ${parseInt(price).toLocaleString('zh-TW')}`;
        }

        // 更新總數量
        function updateTotalCount(count) {
            $('#totalCount').html(`
                <span class="font-semibold text-gray-900">${count}</span> 
                <span class="text-gray-500">筆資料</span>
            `);
        }

        // 顯示載入中
        function showLoading() {
            $('#loadingSpinner').show();
            $('#tableContainer').hide();
            $('#noDataMessage').addClass('hidden');
        }

        // 隱藏載入中
        function hideLoading() {
            $('#loadingSpinner').hide();
        }

        // 顯示無資料
        function showNoDataMessage() {
            $('#tableContainer').hide();
            $('#noDataMessage').removeClass('hidden');
            $('#paginationContainer').addClass('hidden');
            updateTotalCount(0);
        }
        
        // 獲取當前頁面資料
        function getCurrentPageData() {
            // 如果選擇全部，返回所有資料
            if (itemsPerPage === 'all') {
                return filteredData;
            }
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;
            return filteredData.slice(startIndex, endIndex);
        }
        
        // 渲染分頁
        function renderPagination() {
            // 如果選擇全部，隱藏分頁控制
            if (itemsPerPage === 'all') {
                $('#paginationContainer').addClass('hidden');
                return;
            }
            
            const totalPages = Math.ceil(filteredData.length / itemsPerPage);
            
            if (totalPages <= 1) {
                $('#paginationContainer').addClass('hidden');
                return;
            }
            
            $('#paginationContainer').removeClass('hidden');
            
            // 更新範圍顯示
            if (itemsPerPage === 'all') {
                $('#rangeStart').text(1);
                $('#rangeEnd').text(filteredData.length);
                $('#totalItems').text(filteredData.length);
            } else {
                const startIndex = (currentPage - 1) * itemsPerPage + 1;
                const endIndex = Math.min(currentPage * itemsPerPage, filteredData.length);
                $('#rangeStart').text(startIndex);
                $('#rangeEnd').text(endIndex);
                $('#totalItems').text(filteredData.length);
            }
            
            // 更新按鈕狀態
            $('#firstPageBtn').prop('disabled', currentPage === 1);
            $('#prevPageBtn').prop('disabled', currentPage === 1);
            $('#nextPageBtn').prop('disabled', currentPage === totalPages);
            $('#lastPageBtn').prop('disabled', currentPage === totalPages);
            
            // 渲染頁碼
            const $pageNumbers = $('#pageNumbers');
            $pageNumbers.empty();
            
            let startPage = Math.max(1, currentPage - 2);
            let endPage = Math.min(totalPages, currentPage + 2);
            
            if (currentPage <= 3) {
                endPage = Math.min(5, totalPages);
            }
            if (currentPage >= totalPages - 2) {
                startPage = Math.max(1, totalPages - 4);
            }
            
            for (let i = startPage; i <= endPage; i++) {
                const activeClass = i === currentPage ? 'active bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50';
                const btn = $(`
                    <button class="pagination-btn px-4 py-2 border border-gray-300 rounded-lg ${activeClass} text-base min-w-[44px]" data-page="${i}">
                        ${i}
                    </button>
                `);
                
                btn.on('click', function() {
                    goToPage($(this).data('page'));
                });
                
                $pageNumbers.append(btn);
            }
        }
        
        // 前往指定頁面
        function goToPage(page) {
            currentPage = page;
            renderTable(getCurrentPageData());
            renderPagination();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // 設定事件監聽器
        function setupEventListeners() {
            // 每頁顯示數量變更
            $('#itemsPerPage').on('change', function() {
                const value = $(this).val();
                itemsPerPage = value === 'all' ? 'all' : parseInt(value);
                currentPage = 1;
                renderTable(getCurrentPageData());
                renderPagination();
            });
            
            // 重新整理按鈕
            $('#refreshBtn').on('click', function() {
                const btn = $(this);
                btn.find('svg').addClass('animate-spin');
                loadTableData().finally(() => {
                    setTimeout(() => {
                        btn.find('svg').removeClass('animate-spin');
                    }, 500);
                });
            });
            
            // 搜尋功能
            $('#searchInput').on('input', debounce(applyFilters, 300));
            
            // 狀態篩選
            $('#actionFilter').on('change', applyFilters);
            
            // 價格篩選
            $('#priceFilter').on('change', applyFilters);
            
            // 分頁按鈕
            $('#firstPageBtn').on('click', () => goToPage(1));
            $('#prevPageBtn').on('click', () => goToPage(Math.max(1, currentPage - 1)));
            $('#nextPageBtn').on('click', () => {
                if (itemsPerPage !== 'all') {
                    goToPage(Math.min(Math.ceil(filteredData.length / itemsPerPage), currentPage + 1));
                }
            });
            $('#lastPageBtn').on('click', () => {
                if (itemsPerPage !== 'all') {
                    goToPage(Math.ceil(filteredData.length / itemsPerPage));
                }
            });
        }

        // 應用篩選
        function applyFilters() {
            const searchTerm = $('#searchInput').val().toLowerCase();
            const actionFilter = $('#actionFilter').val();
            const priceFilter = $('#priceFilter').val();
            
            filteredData = allTableData.filter(item => {
                // 搜尋篩選
                const matchSearch = !searchTerm || 
                    (item.eng_name && item.eng_name.toLowerCase().includes(searchTerm)) ||
                    (item.code && item.code.toLowerCase().includes(searchTerm));
                
                // 狀態篩選
                const matchAction = !actionFilter || item.action === actionFilter;
                
                // 價格篩選
                let matchPrice = true;
                if (priceFilter) {
                    const price = parseInt(item.price) || 0;
                    switch(priceFilter) {
                        case '0-3000':
                            matchPrice = price <= 3000;
                            break;
                        case '3000-5000':
                            matchPrice = price > 3000 && price <= 5000;
                            break;
                        case '5000-10000':
                            matchPrice = price > 5000 && price <= 10000;
                            break;
                        case '10000+':
                            matchPrice = price > 10000;
                            break;
                    }
                }
                
                return matchSearch && matchAction && matchPrice;
            });
            
            currentPage = 1;
            updateTotalCount(filteredData.length);
            renderTable(getCurrentPageData());
            renderPagination();
        }

        // 防抖函數
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // 當文件載入完成時執行
        $(document).ready(function() {
            init();
        });
    </script>
</body>

</html>