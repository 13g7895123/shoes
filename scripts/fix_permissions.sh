#!/bin/bash

# 確保在專案根目錄執行
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
if [[ "$(basename "$SCRIPT_DIR")" == "scripts" ]]; then
    cd "$(dirname "$SCRIPT_DIR")"
else
    cd "$SCRIPT_DIR"
fi

# 權限重置終極腳本 - Bonus Shoes 遷移專案專用

echo "🔧 開始執行權限修復..."

# 1. 在主機端奪回所有權 (使用 sudo 確保能處理 Docker 產生的檔案)
echo "📂 [1/4] 主機端：奪回檔案擁有權..."
sudo chown -R $USER:$USER .
sudo chmod -R 755 .

# 2. 針對 CodeIgniter 的寫入目錄進行特殊處理
echo "📂 [2/4] 主機端：設定 writable 目錄權限..."
sudo chmod -R 777 writable

# 3. 確保 Docker 服務正在運行
echo "🐳 [3/4] Docker：重啟容器..."
docker compose stop
docker compose up -d

# 4. 修正容器內部的擁有者 (CodeIgniter 需要 www-data)
echo "🐳 [4/4] 容器：校正內部 writable 權限..."
docker compose exec -u root php chown -R www-data:www-data /var/www/html/writable
docker compose exec -u root php chmod -R 775 /var/www/html/writable

echo ""
echo "✅ 權限修復完成！"
echo "📍 現在您可以嘗試訪問：http://localhost:8102"
echo ""
echo "📊 目前 writable 的主機端狀態："
ls -ld writable
