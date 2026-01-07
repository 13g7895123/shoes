#!/bin/bash

# Bonus Shoes - 啟動腳本

echo "🚀 啟動 Bonus Shoes 專案..."

# 檢查 .env 檔案
if [ ! -f .env ]; then
    echo "📝 複製 .env.example 到 .env..."
    cp .env.example .env
fi

# 啟動 Docker Compose (使用新版指令)
echo "🐳 啟動 Docker 容器..."
docker compose up -d

# 等待服務啟動
echo "⏳ 等待服務啟動..."
sleep 5

# 設定權限 (這裡使用 sudo 來確保主機能修改 Docker 產生的檔案)
echo "🔐 設定檔案權限..."
sudo chown -R $USER:$USER .
docker compose exec php chown -R www-data:www-data writable
docker compose exec php chmod -R 775 writable

# 安裝 Composer 依賴
echo "📦 安裝 Composer 依賴..."
docker compose exec php composer install --no-interaction

echo ""
echo "✅ 專案啟動完成！"
echo ""
echo "📍 服務資訊："
echo "   - 應用程式: http://localhost:8080"
echo "   - phpMyAdmin: http://localhost:8081"
echo "   - MySQL Port: 3306"
echo ""
echo "🔑 資料庫登入資訊："
echo "   - 使用者: bonus_user"
echo "   - 密碼: bonus_password"
echo "   - 資料庫: bonus_shoes"
echo ""
echo "📚 常用指令："
echo "   - 查看日誌: docker compose logs -f"
echo "   - 停止服務: docker compose down"
echo "   - 重啟服務: docker compose restart"
echo ""
