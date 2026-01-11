#!/bin/bash

# ============================================
# Bonus Shoes - 完整修復腳本
# 修復 vendor 遺失 + 權限問題
# ============================================

set -e

echo "🔧 ============================================"
echo "   Bonus Shoes 完整修復腳本"
echo "============================================"
echo ""

# Step 0: 確保在專案根目錄執行
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
if [[ "$(basename "$SCRIPT_DIR")" == "scripts" ]]; then
    cd "$(dirname "$SCRIPT_DIR")"
else
    cd "$SCRIPT_DIR"
fi

echo "📍 工作目錄: $(pwd)"
echo ""

# Step 1: 修復主機端權限
echo "📂 [1/6] 修復主機端檔案權限..."
sudo chown -R $USER:$USER .
sudo chmod -R 755 .
sudo chmod -R 777 writable 2>/dev/null || true

# Step 2: 停止所有容器
echo "🐳 [2/6] 停止 Docker 容器..."
docker compose down 2>/dev/null || true

# Step 3: 清理舊的 vendor（如果損壞）
echo "🗑️  [3/6] 清理損壞的 vendor 目錄..."
rm -rf vendor composer.lock 2>/dev/null || true

# Step 4: 啟動容器
echo "🐳 [4/6] 啟動 Docker 容器..."
docker compose up -d

echo "⏳ 等待 MySQL 啟動 (15秒)..."
sleep 15

# Step 5: 在容器內安裝 Composer 依賴 (以 root 執行避免權限問題)
echo "📦 [5/6] 安裝 Composer 依賴 (這可能需要 1-2 分鐘)..."
docker compose exec -T -u root php composer install --no-interaction --optimize-autoloader

# Step 6: 修復容器內的權限
echo "🔐 [6/6] 修復容器內權限..."
docker compose exec -T -u root php chown -R www-data:www-data /var/www/html/writable
docker compose exec -T -u root php chmod -R 775 /var/www/html/writable
docker compose exec -T -u root php chown -R www-data:www-data /var/www/html/vendor
docker compose exec -T -u root php chown www-data:www-data /var/www/html/composer.lock 2>/dev/null || true

# 驗證
echo ""
echo "🧪 驗證安裝..."
if docker compose exec -T php test -f /var/www/html/vendor/codeigniter4/framework/system/Boot.php; then
    echo "✅ Boot.php 存在！"
else
    echo "❌ Boot.php 仍然遺失，請檢查錯誤訊息"
    exit 1
fi

# 取得 Port
APP_PORT=$(grep "APP_PORT" .env 2>/dev/null | cut -d'=' -f2 | tr -d ' ' || echo "8080")

echo ""
echo "============================================"
echo "✅ 修復完成！"
echo "============================================"
echo ""
echo "📍 請訪問："
echo "   - 應用程式: http://localhost:${APP_PORT}"
echo "   - phpMyAdmin: http://localhost:$(grep "PHPMYADMIN_PORT" .env 2>/dev/null | cut -d'=' -f2 | tr -d ' ' || echo "8081")"
echo ""
echo "🧪 測試 API："
echo "   curl http://localhost:${APP_PORT}/api/shoes"
echo ""
