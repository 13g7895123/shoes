<?= $this->extend('admin/_layout') ?>
<?= $this->section('head_styles') ?>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: "Noto Sans TC", "Microsoft JhengHei", sans-serif; background: #f5f7fa; color: #1f2937; }
        .container { max-width: 1400px; margin: 0 auto; padding: 24px; }
        
        /* 顶部导航 */
        .top-nav { background: #fff; padding: 16px 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-bottom: 24px; border-radius: 12px; }
        .top-nav h1 { font-size: 24px; font-weight: 700; color: #0f172a; margin-bottom: 8px; }
        .breadcrumb { display: flex; gap: 8px; font-size: 14px; color: #64748b; }
        .breadcrumb a { color: #3b82f6; text-decoration: none; }
        .breadcrumb a:hover { text-decoration: underline; }
        
        /* 统计卡片 */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px; }
        .stat-card { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .stat-card .label { font-size: 13px; color: #64748b; margin-bottom: 8px; }
        .stat-card .value { font-size: 28px; font-weight: 700; color: #0f172a; }
        .stat-card.success .value { color: #10b981; }
        .stat-card.error .value { color: #ef4444; }
        .stat-card .sub { font-size: 12px; color: #94a3b8; margin-top: 4px; }
        
        /* 筛选区 */
        .filter-panel { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .filter-row { display: flex; gap: 12px; flex-wrap: wrap; align-items: end; }
        .filter-group { flex: 1; min-width: 200px; }
        .filter-group label { display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px; }
        .filter-group input, .filter-group select { width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; }
        .filter-group button { padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; }
        .btn-primary { background: #3b82f6; color: #fff; }
        .btn-primary:hover { background: #2563eb; }
        .btn-secondary { background: #e5e7eb; color: #374151; }
        .btn-secondary:hover { background: #d1d5db; }
        .btn-danger { background: #ef4444; color: #fff; }
        .btn-danger:hover { background: #dc2626; }
        
        /* 表格 */
        .table-container { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; }
        thead { background: linear-gradient(to right, #f8fafc, #f1f5f9); }
        th { padding: 14px 12px; text-align: left; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; border-bottom: 2px solid #e2e8f0; }
        td { padding: 12px; font-size: 14px; border-bottom: 1px solid #f1f5f9; }
        tr:hover { background: #f8fafc; }
        
        /* 徽章 */
        .badge { display: inline-block; padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .badge.success { background: #d1fae5; color: #065f46; }
        .badge.error { background: #fee2e2; color: #991b1b; }
        .badge.get { background: #dbeafe; color: #1e40af; }
        .badge.post { background: #fef3c7; color: #92400e; }
        .badge.put { background: #e0e7ff; color: #4338ca; }
        .badge.delete { background: #fce7f3; color: #9f1239; }
        
        /* 分页 */
        .pagination { display: flex; justify-content: center; gap: 8px; margin-top: 20px; padding: 20px; }
        .pagination a, .pagination span { padding: 8px 14px; border-radius: 6px; border: 1px solid #e5e7eb; background: #fff; color: #374151; text-decoration: none; font-size: 14px; }
        .pagination a:hover { background: #f3f4f6; }
        .pagination .active { background: #3b82f6; color: #fff; border-color: #3b82f6; }
        
        /* 详情弹窗样式 */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: #fff; margin: 40px auto; padding: 30px; border-radius: 12px; max-width: 900px; max-height: 90vh; overflow-y: auto; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid #e5e7eb; }
        .modal-header h2 { font-size: 20px; font-weight: 700; }
        .close { font-size: 28px; font-weight: 700; color: #9ca3af; cursor: pointer; line-height: 1; }
        .close:hover { color: #374151; }
        .detail-section { margin-bottom: 20px; }
        .detail-section h3 { font-size: 14px; font-weight: 700; color: #475569; margin-bottom: 8px; text-transform: uppercase; }
        .detail-content { background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e5e7eb; }
        pre { white-space: pre-wrap; word-wrap: break-word; font-family: 'Courier New', monospace; font-size: 13px; margin: 0; }
        .info-grid { display: grid; grid-template-columns: 150px 1fr; gap: 8px; }
        .info-label { font-weight: 600; color: #64748b; font-size: 13px; }
        .info-value { color: #1f2937; font-size: 13px; }
        
        /* 响应式 */
        @media (max-width: 768px) {
            .stats-grid { grid-template-columns: 1fr 1fr; }
            .filter-row { flex-direction: column; }
            .filter-group { width: 100%; }
            th, td { padding: 8px 6px; font-size: 12px; }
        }
<?= $this->endSection() ?>
<?= $this->section('page_title') ?><i class="fas fa-file-alt mr-2"></i>API 使用記錄<?= $this->endSection() ?>
<?= $this->section('page_breadcrumbs') ?><a href="/" class="hover:text-blue-600">首頁</a><span class="mx-1">/</span><a href="/admin/api-keys" class="hover:text-blue-600">API Key 管理</a><span class="mx-1">/</span><span>API 使用記錄</span><?= $this->endSection() ?>
<?= $this->section('content') ?>

        <!-- 统计卡片 -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="label">總請求數</div>
                <div class="value"><?= number_format($stats['total']) ?></div>
                <div class="sub">今日: <?= number_format($todayStats['total']) ?></div>
            </div>
            <div class="stat-card success">
                <div class="label">成功請求</div>
                <div class="value"><?= number_format($stats['success']) ?></div>
                <div class="sub">今日: <?= number_format($todayStats['success']) ?></div>
            </div>
            <div class="stat-card error">
                <div class="label">失敗請求</div>
                <div class="value"><?= number_format($stats['error']) ?></div>
                <div class="sub">今日: <?= number_format($todayStats['error']) ?></div>
            </div>
            <div class="stat-card">
                <div class="label">成功率</div>
                <div class="value"><?= $stats['success_rate'] ?>%</div>
                <div class="sub">今日: <?= $todayStats['success_rate'] ?>%</div>
            </div>
        </div>

        <!-- 筛选面板 -->
        <div class="filter-panel">
            <form method="get" action="/admin/api-logs">
                <div class="filter-row">
                    <div class="filter-group">
                        <label>狀態</label>
                        <select name="status">
                            <option value="">全部狀態</option>
                            <option value="success" <?= $filters['status'] === 'success' ? 'selected' : '' ?>>成功</option>
                            <option value="error" <?= $filters['status'] === 'error' ? 'selected' : '' ?>>失敗</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>端點</label>
                        <input type="text" name="endpoint" placeholder="例如: /api/v1/shoes" value="<?= esc($filters['endpoint']) ?>">
                    </div>
                    <div class="filter-group">
                        <label>方法</label>
                        <select name="method">
                            <option value="">全部方法</option>
                            <option value="GET" <?= $filters['method'] === 'GET' ? 'selected' : '' ?>>GET</option>
                            <option value="POST" <?= $filters['method'] === 'POST' ? 'selected' : '' ?>>POST</option>
                            <option value="PUT" <?= $filters['method'] === 'PUT' ? 'selected' : '' ?>>PUT</option>
                            <option value="DELETE" <?= $filters['method'] === 'DELETE' ? 'selected' : '' ?>>DELETE</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <button type="submit" class="btn-primary">🔍 搜尋</button>
                    </div>
                    <div class="filter-group">
                        <a href="/admin/api-logs" class="btn-secondary" style="display: inline-block; padding: 10px 20px; text-decoration: none; text-align: center;">🔄 重置</a>
                    </div>
                    <div class="filter-group">
                        <a href="/admin/api-logs/export?<?= http_build_query($filters) ?>" class="btn-secondary" style="display: inline-block; padding: 10px 20px; text-decoration: none; text-align: center;">📥 匯出 CSV</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- 日志表格 -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th style="width: 120px;">API Key</th>
                        <th>端點</th>
                        <th style="width: 80px;">方法</th>
                        <th style="width: 80px;">狀態碼</th>
                        <th style="width: 80px;">狀態</th>
                        <th style="width: 100px;">響應時間</th>
                        <th style="width: 120px;">IP</th>
                        <th style="width: 160px;">時間</th>
                        <th style="width: 80px;">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="10" style="text-align: center; padding: 40px; color: #94a3b8;">
                                📭 目前沒有記錄
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= esc($log['id']) ?></td>
                                <td><?= esc($log['api_key_name'] ?? 'Public') ?></td>
                                <td style="font-family: monospace; font-size: 12px;"><?= esc($log['endpoint']) ?></td>
                                <td><span class="badge <?= strtolower($log['method']) ?>"><?= esc($log['method']) ?></span></td>
                                <td><?= esc($log['response_code']) ?></td>
                                <td><span class="badge <?= esc($log['status']) ?>"><?= esc($log['status']) ?></span></td>
                                <td><?= esc($log['response_time']) ?>s</td>
                                <td style="font-family: monospace; font-size: 12px;"><?= esc($log['ip_address']) ?></td>
                                <td style="font-size: 12px;"><?= esc($log['created_at']) ?></td>
                                <td>
                                    <button onclick="showDetail(<?= $log['id'] ?>)" style="background: #3b82f6; color: #fff; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 12px;">詳情</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- 分页 -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=1&<?= http_build_query($filters) ?>">首頁</a>
                        <a href="?page=<?= $page - 1 ?>&<?= http_build_query($filters) ?>">上一頁</a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <?php if ($i === $page): ?>
                            <span class="active"><?= $i ?></span>
                        <?php else: ?>
                            <a href="?page=<?= $i ?>&<?= http_build_query($filters) ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?>&<?= http_build_query($filters) ?>">下一頁</a>
                        <a href="?page=<?= $totalPages ?>&<?= http_build_query($filters) ?>">末頁</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 详情弹窗 -->
    <div id="detailModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>📋 請求詳情</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div id="detailBody"></div>
        </div>
    </div>

    <script>
        function showDetail(id) {
            fetch(`/admin/api-logs/detail/${id}`)
                .then(response => response.json())
                .then(data => {
                    const log = data.log;
                    const html = `
                        <div class="detail-section">
                            <h3>基本資訊</h3>
                            <div class="detail-content">
                                <div class="info-grid">
                                    <div class="info-label">ID:</div>
                                    <div class="info-value">${log.id}</div>
                                    <div class="info-label">API Key:</div>
                                    <div class="info-value">${log.api_key_name || 'Public'}</div>
                                    <div class="info-label">端點:</div>
                                    <div class="info-value">${log.endpoint}</div>
                                    <div class="info-label">方法:</div>
                                    <div class="info-value">${log.method}</div>
                                    <div class="info-label">狀態碼:</div>
                                    <div class="info-value">${log.response_code}</div>
                                    <div class="info-label">狀態:</div>
                                    <div class="info-value"><span class="badge ${log.status}">${log.status}</span></div>
                                    <div class="info-label">響應時間:</div>
                                    <div class="info-value">${log.response_time}s</div>
                                    <div class="info-label">IP 地址:</div>
                                    <div class="info-value">${log.ip_address}</div>
                                    <div class="info-label">User Agent:</div>
                                    <div class="info-value" style="word-break: break-all;">${log.user_agent}</div>
                                    <div class="info-label">時間:</div>
                                    <div class="info-value">${log.created_at}</div>
                                </div>
                            </div>
                        </div>
                        
                        ${log.request_params ? `
                        <div class="detail-section">
                            <h3>請求參數</h3>
                            <div class="detail-content">
                                <pre>${formatJson(log.request_params)}</pre>
                            </div>
                        </div>
                        ` : ''}
                        
                        ${log.request_headers ? `
                        <div class="detail-section">
                            <h3>請求標頭</h3>
                            <div class="detail-content">
                                <pre>${formatJson(log.request_headers)}</pre>
                            </div>
                        </div>
                        ` : ''}
                        
                        ${log.request_body ? `
                        <div class="detail-section">
                            <h3>請求內容</h3>
                            <div class="detail-content">
                                <pre>${formatJson(log.request_body)}</pre>
                            </div>
                        </div>
                        ` : ''}
                        
                        <div class="detail-section">
                            <h3>響應內容</h3>
                            <div class="detail-content">
                                <pre>${formatJson(log.response_body)}</pre>
                            </div>
                        </div>
                        
                        ${log.error_message ? `
                        <div class="detail-section">
                            <h3>錯誤訊息</h3>
                            <div class="detail-content" style="background: #fef2f2; border-color: #fecaca;">
                                <pre style="color: #991b1b;">${log.error_message}</pre>
                            </div>
                        </div>
                        ` : ''}
                    `;
                    
                    document.getElementById('detailBody').innerHTML = html;
                    document.getElementById('detailModal').style.display = 'block';
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: '錯誤',
                        text: '無法加載詳情'
                    });
                });
        }

        function closeModal() {
            document.getElementById('detailModal').style.display = 'none';
        }

        function formatJson(str) {
            try {
                const obj = typeof str === 'string' ? JSON.parse(str) : str;
                return JSON.stringify(obj, null, 2);
            } catch (e) {
                return str;
            }
        }

        // 点击外部关闭弹窗
        window.onclick = function(event) {
            const modal = document.getElementById('detailModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
<?= $this->endSection() ?>
