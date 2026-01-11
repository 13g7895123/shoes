# Bonus Shoes 管理系統

基於 CodeIgniter 4 框架的鞋子資料管理系統，使用 Docker Compose 建置開發環境。

## 📋 專案特色

- ✅ **現代化框架**: CodeIgniter 4.6
- ✅ **容器化部署**: Docker Compose
- ✅ **RESTful API**: 標準化 API 設計
- ✅ **環境變數管理**: .env 配置
- ✅ **資料庫管理**: MySQL 8.0 + phpMyAdmin
- ✅ **前端框架**: Tailwind CSS

## 🚀 快速開始

### 系統需求

- Docker
- Docker Compose
- Git

### 安裝步驟

1. **Clone 專案**
```bash
git clone <repository-url>
cd bonus.shoes
```

2. **啟動專案**
```bash
./scripts/start.sh
```

或手動啟動：
```bash
# 複製環境變數檔案
cp .env.example .env

# 啟動 Docker 容器
docker-compose up -d

# 安裝依賴
docker-compose exec php composer install

# 設定權限
docker-compose exec php chown -R www-data:www-data writable/
docker-compose exec php chmod -R 775 writable/
```

3. **訪問應用程式**
- 主應用: http://localhost:8080
- phpMyAdmin: http://localhost:8081

## 📁 專案結構

```
bonus.shoes/
├── app/                      # 應用程式核心
│   ├── Config/              # 配置檔案
│   ├── Controllers/         # 控制器
│   │   └── Api/            # API 控制器
│   ├── Models/             # 資料模型
│   └── Views/              # 視圖檔案
│       └── shoes/          # 鞋子相關視圖
├── docker/                  # Docker 配置
│   ├── nginx/              # Nginx 配置
│   ├── php/                # PHP-FPM 配置
│   └── mysql/              # MySQL 初始化
├── public/                  # 公開目錄
│   ├── dist/               # Tailwind 編譯輸出
│   └── index.php           # 入口檔案
├── writable/               # 可寫入目錄
├── _old_project/           # 舊專案備份
├── .env                    # 環境變數
├── docker-compose.yml      # Docker Compose 配置
├── scripts/                # 腳本工具目錄
│   ├── start.sh           # 啟動腳本
│   ├── full_fix.sh        # 完整修復腳本
│   └── fix_permissions.sh # 權限修復腳本
```

## 🔧 環境配置

所有環境變數都在 `.env` 檔案中管理：

```env
# 應用程式 Port
APP_PORT=8080

# phpMyAdmin Port
PHPMYADMIN_PORT=8081

# MySQL Port
MYSQL_PORT=3306

# 資料庫配置
MYSQL_DATABASE=bonus_shoes
MYSQL_USER=bonus_user
MYSQL_PASSWORD=bonus_password
```

## 📡 API 端點

### 鞋子資料 API

| 方法 | 端點 | 說明 |
|------|------|------|
| GET | `/api/shoes` | 取得所有鞋子資料 |
| GET | `/api/shoes/{id}` | 取得單筆鞋子資料 |
| POST | `/api/shoes` | 新增鞋子資料 |
| PUT | `/api/shoes/{id}` | 更新鞋子資料 |
| DELETE | `/api/shoes/{id}` | 刪除鞋子資料 |
| GET | `/api/shoes/table-content` | 取得表格內容（相容舊版） |

### API 回應格式

成功回應：
```json
{
    "success": true,
    "data": [...],
    "message": "操作成功"
}
```

錯誤回應：
```json
{
    "success": false,
    "message": "錯誤訊息"
}
```

## 🗄️ 資料庫

### 資料表結構

**shoes_show_inf**

| 欄位 | 類型 | 說明 |
|------|------|------|
| id | INT | 主鍵 |
| images | VARCHAR(255) | 圖片路徑 |
| eng_name | VARCHAR(255) | 英文名稱 |
| code | VARCHAR(100) | 商品代碼 |
| hope_price | DECIMAL(10,2) | 希望價格 |
| price | DECIMAL(10,2) | 實際價格 |
| point | INT | 點數 |
| size | VARCHAR(50) | 尺寸 |
| action | ENUM | 動作（新增/更新/刪除） |
| created_at | TIMESTAMP | 建立時間 |
| updated_at | TIMESTAMP | 更新時間 |

## 🐳 Docker 指令

```bash
# 啟動所有服務
docker-compose up -d

# 停止所有服務
docker-compose down

# 查看服務狀態
docker-compose ps

# 查看日誌
docker-compose logs -f

# 查看特定服務日誌
docker-compose logs -f php
docker-compose logs -f nginx
docker-compose logs -f mysql

# 重啟服務
docker-compose restart

# 進入 PHP 容器
docker-compose exec php bash

# 執行 Composer 指令
docker-compose exec php composer install
docker-compose exec php composer update

# 執行 CodeIgniter 指令
docker-compose exec php php spark list
docker-compose exec php php spark migrate
```

## 🛠️ 開發指令

### CodeIgniter Spark CLI

```bash
# 查看所有可用指令
docker-compose exec php php spark list

# 建立控制器
docker-compose exec php php spark make:controller ControllerName

# 建立模型
docker-compose exec php php spark make:model ModelName

# 建立 Migration
docker-compose exec php php spark make:migration MigrationName

# 執行 Migration
docker-compose exec php php spark migrate

# 回滾 Migration
docker-compose exec php php spark migrate:rollback

# 清除快取
docker-compose exec php php spark cache:clear
```

### Tailwind CSS

```bash
# 編譯 CSS
npm run build

# 監看模式
npm run watch
```

## 📝 從舊專案遷移

舊專案程式碼已備份至 `_old_project/` 目錄：

- `_old_project/__Class/` - 舊的類別庫
- `_old_project/Pages/` - 舊的頁面和 AJAX
- `_old_project/config/` - 舊的配置檔案
- `_old_project/index.php` - 舊的主頁面

## 🔒 安全性

- ✅ CSRF 保護已啟用
- ✅ XSS 過濾
- ✅ SQL 注入防護（使用 Query Builder）
- ✅ 環境變數管理敏感資訊
- ✅ .env 檔案已加入 .gitignore

## 🐛 疑難排解

### 權限問題

```bash
docker-compose exec php chown -R www-data:www-data writable/
docker-compose exec php chmod -R 775 writable/
```

### 資料庫連線失敗

1. 確認 MySQL 容器已啟動：`docker-compose ps`
2. 檢查 .env 中的資料庫配置
3. 查看 MySQL 日誌：`docker-compose logs mysql`

### Port 衝突

修改 `.env` 檔案中的 Port 設定：

```env
APP_PORT=8080        # 改成其他 Port
PHPMYADMIN_PORT=8081 # 改成其他 Port
MYSQL_PORT=3306      # 改成其他 Port
```

然後重啟服務：
```bash
docker-compose down
docker-compose up -d
```

## 📚 相關資源

- [CodeIgniter 4 官方文件](https://codeigniter.com/user_guide/)
- [Docker 官方文件](https://docs.docker.com/)
- [Tailwind CSS 文件](https://tailwindcss.com/docs)

## 📄 授權

MIT License

## 👥 貢獻

歡迎提交 Issue 和 Pull Request！

---

**開發時間**: 2026-01-07  
**框架版本**: CodeIgniter 4.6  
**PHP 版本**: 8.1  
**資料庫**: MySQL 8.0
