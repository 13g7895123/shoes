<!doctype html>
<html lang="zh-Hant">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>API Key 管理</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: "Noto Sans TC", sans-serif; margin: 24px; background: #f7f7f7; color: #1f2937; }
        .card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 8px 24px rgba(0,0,0,0.08); margin-bottom: 20px; }
        .title { font-size: 20px; font-weight: 700; margin-bottom: 12px; }
        .row { display: flex; gap: 12px; flex-wrap: wrap; }
        label { display: block; font-size: 14px; margin-bottom: 6px; }
        input, select { width: 240px; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; }
        button { padding: 10px 16px; border: none; border-radius: 8px; background: #0f172a; color: #fff; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 10px; border-bottom: 1px solid #e5e7eb; font-size: 14px; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 999px; font-size: 12px; background: #e5e7eb; }
        .success { background: #dcfce7; color: #166534; }
        .error { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
        <h1 style="font-size: 24px; font-weight: 700;">API Key 管理</h1>
        <div style="display: flex; gap: 12px;">
            <a href="/" style="padding: 10px 16px; background: #e5e7eb; color: #374151; text-decoration: none; border-radius: 8px; font-size: 14px;">🏠 首頁</a>
            <a href="/admin/api-logs" style="padding: 10px 16px; background: #3b82f6; color: #fff; text-decoration: none; border-radius: 8px; font-size: 14px;">📊 查看使用記錄</a>
        </div>
    </div>
    <div class="card">
        <div class="title">建立 API Key</div>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="badge error"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>
        <?php if (!empty($newKey)): ?>
            <div style="margin: 12px 0;">
                <div class="badge success">新的 API Key</div>
                <div style="margin-top: 8px; font-weight: 700; display: flex; gap: 8px; align-items: center;">
                    <span id="newApiKeyText"><?= esc($newKey) ?></span>
                    <button type="button" id="copyKeyBtn">複製</button>
                </div>
            </div>
            <input type="hidden" id="newApiKey" value="<?= esc($newKey) ?>">
        <?php endif; ?>
        <form method="post" action="/admin/api-keys">
            <?= csrf_field() ?>
            <div class="row">
                <div>
                    <label for="name">名稱</label>
                    <input id="name" name="name" type="text" placeholder="例如: crawler-service" required>
                </div>
                <div>
                    <label for="permission">權限</label>
                    <select id="permission" name="permission" required>
                        <option value="READ">READ</option>
                        <option value="WRITE">WRITE</option>
                        <option value="DELETE">DELETE</option>
                        <option value="ADMIN">ADMIN</option>
                    </select>
                </div>
                <div style="align-self: flex-end;">
                    <button type="submit">產生 API Key</button>
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="title">已建立的 API Keys</div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>名稱</th>
                    <th>權限</th>
                    <th>狀態</th>
                    <th>最後使用</th>
                    <th>建立時間</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($keys)): ?>
                    <tr><td colspan="6">尚無資料</td></tr>
                <?php else: ?>
                    <?php foreach ($keys as $key): ?>
                        <tr>
                            <td><?= esc($key['id']) ?></td>
                            <td><?= esc($key['name']) ?></td>
                            <td><?= esc($key['permission']) ?></td>
                            <td><?= esc($key['status']) ?></td>
                            <td><?= esc($key['last_used_at'] ?? '-') ?></td>
                            <td><?= esc($key['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        (function () {
            const keyInput = document.getElementById('newApiKey');
            if (!keyInput) return;

            const keyValue = keyInput.value;
            if (!keyValue) return;

            function showSuccess() {
                Swal.fire({
                    icon: 'success',
                    title: '系統提示',
                    text: 'key已複製到剪貼簿'
                });
            }

            function showFallback() {
                Swal.fire({
                    icon: 'warning',
                    title: '系統提示',
                    text: '無法自動複製，請手動複製'
                });
            }

            function selectKeyText() {
                const keyText = document.getElementById('newApiKeyText');
                if (!keyText) return;
                const range = document.createRange();
                range.selectNodeContents(keyText);
                const selection = window.getSelection();
                selection.removeAllRanges();
                selection.addRange(range);
            }

            // 自動複製函數：使用多種策略確保可靠性
            function autoCopy() {
                // 策略 1: 使用 textarea + execCommand (最可靠)
                const textarea = document.createElement('textarea');
                textarea.value = keyValue;
                textarea.style.position = 'fixed';
                textarea.style.left = '-9999px';
                textarea.style.top = '0';
                document.body.appendChild(textarea);
                textarea.focus();
                textarea.select();
                
                try {
                    const success = document.execCommand('copy');
                    document.body.removeChild(textarea);
                    if (success) {
                        showSuccess();
                        return true;
                    }
                } catch (err) {
                    document.body.removeChild(textarea);
                }

                // 策略 2: 嘗試 Clipboard API
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(keyValue)
                        .then(() => showSuccess())
                        .catch(() => {
                            // 策略 3: 選中文字並嘗試 execCommand
                            selectKeyText();
                            const success = document.execCommand('copy');
                            if (success) {
                                showSuccess();
                            } else {
                                showFallback();
                            }
                        });
                } else {
                    // 策略 3: 選中文字並嘗試 execCommand
                    selectKeyText();
                    const success = document.execCommand('copy');
                    if (success) {
                        showSuccess();
                    } else {
                        showFallback();
                    }
                }
            }

            // 手動複製按鈕
            const copyBtn = document.getElementById('copyKeyBtn');
            if (copyBtn) {
                copyBtn.addEventListener('click', () => {
                    const textarea = document.createElement('textarea');
                    textarea.value = keyValue;
                    textarea.style.position = 'fixed';
                    textarea.style.left = '-9999px';
                    document.body.appendChild(textarea);
                    textarea.focus();
                    textarea.select();
                    
                    try {
                        const success = document.execCommand('copy');
                        document.body.removeChild(textarea);
                        if (success) {
                            showSuccess();
                        } else {
                            throw new Error('execCommand failed');
                        }
                    } catch (err) {
                        document.body.removeChild(textarea);
                        if (navigator.clipboard && window.isSecureContext) {
                            navigator.clipboard.writeText(keyValue)
                                .then(showSuccess)
                                .catch(showFallback);
                        } else {
                            showFallback();
                        }
                    }
                });
            }

            // 等待 SweetAlert2 完全加载并在 DOM ready 后执行自动复制
            function waitForSwalAndCopy() {
                if (typeof Swal !== 'undefined') {
                    // SweetAlert2 已加载，等待一小段时间后执行复制
                    setTimeout(autoCopy, 300);
                } else {
                    // SweetAlert2 尚未加载，100ms 后重试
                    setTimeout(waitForSwalAndCopy, 100);
                }
            }

            // 使用多种方式确保在合适的时机执行
            if (document.readyState === 'complete') {
                // 页面已完全加载
                waitForSwalAndCopy();
            } else {
                // 等待页面加载完成
                window.addEventListener('load', waitForSwalAndCopy);
            }
        })();
    </script>
</body>
</html>
