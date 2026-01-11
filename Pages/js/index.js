import get_data from './ajax.js'

// 全域變數
let allTableData = [];
const IMG_SIZE = 100;

// 初始化
function init() {
    loadData();
    setupEventListeners();
}

// 載入資料
function loadData() {
    showLoading();
    const table_data = get_data('table_content');
    allTableData = table_data;
    
    hideLoading();
    
    if (table_data && table_data.length > 0) {
        updateTotalCount(table_data.length);
        renderTable(table_data);
    } else {
        showNoData();
    }
}

// 渲染表格
function renderTable(data) {
    const tbody = $('tbody');
    tbody.empty();
    
    if (!data || data.length === 0) {
        showNoData();
        return;
    }
    
    $('#tableContainer').show();
    $('#noDataMessage').hide();
    
    data.forEach((item, index) => {
        const statusBadge = getStatusBadge(item.action);
        const imageUrl = `https://www.kishispo.net/upload/save_image/${item.code}.jpg`;
        
        // 添加動畫延遲效果
        const delay = index * 30;
        
        const row = $(`
            <tr class="hover:bg-gray-50 transition-colors" style="animation: fadeIn 0.3s ease ${delay}ms both;">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
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
                <td class="px-6 py-4 text-sm text-gray-900">
                    <div class="font-medium">${item.eng_name || '-'}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-mono">
                    ${item.code || '-'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${formatPrice(item.hope_price)}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                    ${formatPrice(item.price)}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                    ${item.point || '-'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
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
    $('#noDataMessage').hide();
}

// 隱藏載入中
function hideLoading() {
    $('#loadingSpinner').hide();
}

// 顯示無資料
function showNoData() {
    $('#tableContainer').hide();
    $('#noDataMessage').removeClass('hidden');
    updateTotalCount(0);
}

// 設定事件監聽器
function setupEventListeners() {
    // 重新整理按鈕
    $('#refreshBtn').on('click', function() {
        $(this).addClass('animate-spin');
        setTimeout(() => {
            loadData();
            $(this).removeClass('animate-spin');
        }, 500);
    });
    
    // 搜尋功能
    $('#searchInput').on('input', debounce(function() {
        applyFilters();
    }, 300));
    
    // 狀態篩選
    $('#actionFilter').on('change', function() {
        applyFilters();
    });
    
    // 價格篩選
    $('#priceFilter').on('change', function() {
        applyFilters();
    });
}

// 應用篩選
function applyFilters() {
    const searchTerm = $('#searchInput').val().toLowerCase();
    const actionFilter = $('#actionFilter').val();
    const priceFilter = $('#priceFilter').val();
    
    let filteredData = allTableData.filter(item => {
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
    
    updateTotalCount(filteredData.length);
    renderTable(filteredData);
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

// 添加淡入動畫
const style = document.createElement('style');
style.textContent = `
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
`;
document.head.appendChild(style);

// 當文件載入完成時執行
$(document).ready(function() {
    init();
});
