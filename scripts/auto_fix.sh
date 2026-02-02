#!/bin/bash

# ============================================
# Bonus Shoes - 自動修復腳本 (無需 sudo)
# ============================================

set -e

echo "🔧 ============================================"
echo "   Bonus Shoes 自動修復腳本"
echo "============================================"
echo ""

# 確保在專案根目錄執行
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
if [[ "$(basename "$SCRIPT_DIR")" == "scripts" ]]; then
    cd "$(dirname "$SCRIPT_DIR")"
else
    cd "$SCRIPT_DIR"
fi

echo "📍 工作目錄: $(pwd)"
echo ""

# Step 1: 停止所有容器
echo "🐳 [1/5] 停止 Docker 容器..."
docker compose down 2>/dev/null || true

# Step 2: 清理舊的 vendor 和 lock 文件
echo "🗑️  [2/5] 清理舊檔案..."
rm -rf vendor composer.lock 2>/dev/null || true

# Step 3: 創建 .env 文件（如果不存在）
if [ ! -f .env ]; then
    echo "📝 [3/5] 創建 .env 配置文件..."
    cat > .env << 'EOF'
# Application
APP_PORT=8102
APP_TIMEZONE=Asia/Taipei

# MySQL Database
MYSQL_PORT=3307
MYSQL_ROOT_PASSWORD=root_password
MYSQL_DATABASE=bonus_shoes
MYSQL_USER=bonus_user
MYSQL_PASSWORD=bonus_password

# phpMyAdmin
PHPMYADMIN_PORT=8103
EOF
    echo "✅ .env 文件已創建"
else
    echo "📝 [3/5] .env 文件已存在，跳過..."
fi

# Step 4: 啟動容器
echo "🐳 [4/5] 啟動 Docker 容器..."
docker compose up -d

echo "⏳ 等待 MySQL 啟動 (15秒)..."
sleep 15

# Step 5: 在容器內以 root 安裝依賴並修復權限
echo "📦 [5/5] 安裝依賴並修復權限..."
docker compose exec -T -u root php bash -c '
    # 安裝 Composer 依賴
    echo "📦 安裝 Composer 依賴..."
    composer install --no-interaction --optimize-autoloader
    
    # 修復權限
    echo "🔐 修復權限..."
    chown -R www-data:www-data /var/www/html/writable
    chmod -R 775 /var/www/html/writable
    chown -R www-data:www-data /var/www/html/vendor
    chown www-data:www-data /var/www/html/composer.lock 2>/dev/null || true
    
    echo "✅ 完成！"
'

# 驗證
echo ""
echo "🧪 驗證安裝..."
if docker compose exec -T php test -f /var/www/html/vendor/codeigniter4/framework/system/Boot.php; then
    echo "✅ CodeIgniter 框架安裝成功！"
else
    echo "❌ 安裝失敗，請檢查錯誤訊息"
    exit 1
fi

# 取得 Port
APP_PORT=$(grep "^APP_PORT=" .env 2>/dev/null | cut -d'=' -f2 | tr -d ' ' || echo "8102")
PHPMYADMIN_PORT=$(grep "^PHPMYADMIN_PORT=" .env 2>/dev/null | cut -d'=' -f2 | tr -d ' ' || echo "8103")

echo ""
echo "============================================"
echo "✅ 修復完成！專案已就緒"
echo "============================================"
echo ""
echo "📍 訪問地址："
echo "   🌐 應用程式:    http://localhost:${APP_PORT}"
echo "   🗄️  phpMyAdmin:  http://localhost:${PHPMYADMIN_PORT}"
echo ""
echo "🧪 測試 API："
echo "   curl http://localhost:${APP_PORT}"
echo ""
echo "📊 容器狀態："
docker compose ps
echo ""
