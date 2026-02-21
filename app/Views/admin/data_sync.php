<!doctype html>
<html lang="zh-Hant">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>英文名稱修復工具</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: "Noto Sans TC", sans-serif; margin: 24px; background: #f7f7f7; color: #1f2937; }
        .card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 8px 24px rgba(0,0,0,0.08); margin-bottom: 20px; }
        .title { font-size: 18px; font-weight: 700; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 10px 12px; border-bottom: 1px solid #e5e7eb; font-size: 14px; }
        th { background: #f9fafb; font-weight: 600; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .badge-warn  { background: #fef3c7; color: #92400e; }
        .badge-ok    { background: #dcfce7; color: #166534; }
        .badge-error { background: #fee2e2; color: #991b1b; }
        .btn         { padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; }
        .btn-primary { background: #0f172a; color: #fff; }
        .btn-primary:hover { background: #1e293b; }
        .btn-back    { background: #e5e7eb; color: #374151; text-decoration: none; padding: 10px 16px; border-radius: 8px; font-size: 14px; }
        .stat-row    { display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 20px; }
        .stat-box    { background: #f1f5f9; border-radius: 10px; padding: 16px 24px; min-width: 140px; }
        .stat-num    { font-size: 28px; font-weight: 700; }
        .stat-label  { font-size: 12px; color: #64748b; margin-top: 4px; }
        .diff-old    { color: #dc2626; text-decoration: line-through; font-size: 13px; }
        .diff-new    { color: #16a34a; font-size: 13px; }
    </style>
</head>
<body>
    <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
        <h1 style="font-size: 24px; font-weight: 700;">🔧 英文名稱修復工具</h1>
        <div style="display: flex; gap: 12px;">
            <a href="/" class="btn-back">🏠 首頁</a>
            <a href="/admin/api-keys" class="btn-back">🔑 API Keys</a>
        </div>
    </div>

    <!-- 說明 -->
    <div class="card">
        <div class="title">功能說明</div>
        <p style="color: #64748b; font-size: 14px; line-height: 1.8; margin: 0;">
            此工具會比對 <strong>shoes_show_inf</strong> 與 <strong>shoes_inf</strong> 兩張資料表之間的 <code>eng_name</code> 欄位。<br>
            若有不一致的記錄，以 <strong>shoes_inf 的英文名稱為準</strong>，更新 shoes_show_inf 的對應資料。
        </p>
    </div>

    <!-- 執行結果 -->
    <?php if (!empty($result)): ?>
        <?php if ($result['success']): ?>
        <div class="card" style="border-left: 4px solid #16a34a;">
            <div class="title" style="color: #16a34a;">✅ 修復完成</div>
            <div class="stat-row">
                <div class="stat-box">
                    <div class="stat-num"><?= $result['total'] ?></div>
                    <div class="stat-label">偵測到不一致筆數</div>
                </div>
                <div class="stat-box">
                    <div class="stat-num" style="color: #16a34a;"><?= $result['updated'] ?></div>
                    <div class="stat-label">實際更新筆數</div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="card" style="border-left: 4px solid #dc2626;">
            <div class="title" style="color: #dc2626;">❌ 修復失敗</div>
            <p style="font-size: 14px; color: #64748b;"><?= esc($result['message']) ?></p>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- 偵測結果 -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div class="title" style="margin: 0;">
                偵測結果
                <?php if ($mismatch_count > 0): ?>
                    <span class="badge badge-warn" style="margin-left: 10px;"><?= $mismatch_count ?> 筆不一致</span>
                <?php else: ?>
                    <span class="badge badge-ok" style="margin-left: 10px;">✓ 全部一致</span>
                <?php endif; ?>
            </div>
            <?php if ($mismatch_count > 0): ?>
            <form method="post" action="/admin/data-sync/run" id="syncForm">
                <?= csrf_field() ?>
                <button type="button" class="btn btn-primary" onclick="confirmRun()">
                    🔄 執行修復（<?= $mismatch_count ?> 筆）
                </button>
            </form>
            <?php endif; ?>
        </div>

        <?php if ($mismatch_count > 0): ?>
        <table>
            <thead>
                <tr>
                    <th style="width: 140px;">商品編號</th>
                    <th>商品名稱</th>
                    <th>shoes_show_inf（目前）</th>
                    <th>shoes_inf（正確來源）</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mismatches as $row): ?>
                <tr>
                    <td><code><?= esc($row['code']) ?></code></td>
                    <td style="color: #64748b;"><?= esc($row['name']) ?></td>
                    <td>
                        <span class="diff-old"><?= esc($row['show_eng_name'] ?? '（空）') ?></span>
                    </td>
                    <td>
                        <span class="diff-new"><?= esc($row['inf_eng_name'] ?? '（空）') ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p style="color: #64748b; font-size: 14px; margin: 0;">shoes_show_inf 與 shoes_inf 的英文名稱完全一致，無需修復。</p>
        <?php endif; ?>
    </div>

<script>
function confirmRun() {
    Swal.fire({
        title: '確認執行修復？',
        html: '將更新 <strong><?= $mismatch_count ?> 筆</strong> shoes_show_inf 的英文名稱，<br>以 shoes_inf 的資料為準。',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#0f172a',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '確認執行',
        cancelButtonText: '取消',
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('syncForm').submit();
        }
    });
}
</script>

</body>
</html>
