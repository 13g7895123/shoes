# Docker 部署說明

## 目錄結構

```
docker/
├── .env                    # 當前使用的環境設定檔 (不納入版控)
├── docker-compose.yml      # Docker Compose 設定檔
├── envs/                   # 環境設定檔範本
│   ├── .env.development    # 開發環境設定
│   ├── .env.production     # 正式環境設定
│   └── .env.staging        # 測試環境設定
├── mysql/                  # MySQL 相關設定
├── nginx/                  # Nginx 相關設定
└── php/                    # PHP 相關設定
```

## 使用方式

### 快速部署

使用根目錄下的 `deploy.sh` 腳本進行部署：

```bash
# 開發環境（預設）
./deploy.sh

# 或明確指定開發環境
./deploy.sh development

# 測試環境
./deploy.sh staging

# 正式環境
./deploy.sh production
```

### 部署流程

1. 腳本會檢查 `docker/.env` 是否存在
2. 如果不存在，會從 `docker/envs/.env.{environment}` 複製對應的環境設定
3. 如果已存在，會詢問是否覆蓋
4. 執行 `docker compose down` 停止現有容器
5. 執行 `docker compose up -d --build` 啟動容器

### 手動操作

如果需要手動操作，請先進入 docker 目錄：

```bash
cd docker

# 啟動服務
docker compose up -d

# 停止服務
docker compose down

# 查看日誌
docker compose logs -f

# 重新啟動
docker compose restart

# 查看容器狀態
docker compose ps
```

## 環境設定

### 容器命名

專案使用 `COMPOSE_PROJECT_NAME` 來統一管理容器名稱，在 `.env` 檔案中設定：

```env
COMPOSE_PROJECT_NAME = bonus_shoes
```

這會讓所有容器名稱自動加上 `bonus_shoes_` 前綴。

### 環境變數

每個環境設定檔都包含：

- Docker Compose 設定（專案名稱）
- CodeIgniter 環境設定
- 應用程式設定（URL、Session 等）
- 資料庫連線設定
- Docker 埠號設定
- MySQL 設定
- 時區設定

### 不同環境的差異

**Development（開發環境）**
- APP_PORT: 8080
- PHPMYADMIN_PORT: 8081
- MYSQL_PORT: 3306
- 使用 HTTP

**Staging（測試環境）**
- APP_PORT: 8082
- PHPMYADMIN_PORT: 8083
- MYSQL_PORT: 3307
- 使用 HTTP

**Production（正式環境）**
- APP_PORT: 80
- PHPMYADMIN_PORT: 8081
- MYSQL_PORT: 3306
- 使用 HTTPS（forceGlobalSecureRequests = true）

## 注意事項

1. `docker/.env` 不會被納入版控（已在 .gitignore 中設定）
2. 請勿直接修改 `docker/envs/` 中的範本檔案，建議複製後修改
3. 正式環境請務必修改預設密碼
4. 確保已安裝 Docker Compose V2（使用 `docker compose` 而非 `docker-compose`）
