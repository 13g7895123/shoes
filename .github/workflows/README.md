# GitHub Actions Workflows

本目錄包含 CI/CD 自動部署流程設定。

---

## deploy.yml — 自動部署至 VPS

每次推送到 `master` 分支時，自動透過 SSH 連線至 VPS 執行 `git pull`，將最新程式碼部署上線。

### 流程說明

```
push to master
      │
      ▼
SSH 連線至 VPS
      │
      ▼
cd <專案路徑> && git pull origin master
```

---

## 必要的 GitHub Secrets 設定

前往 GitHub Repository → **Settings** → **Secrets and variables** → **Actions** → **New repository secret**，新增以下 4 個 Secrets：

| Secret 名稱 | 說明 | 範例值 |
|---|---|---|
| `VPS_HOST` | VPS 的 IP 位址或網域名稱 | `123.456.789.0` 或 `example.com` |
| `VPS_USERNAME` | SSH 登入帳號 | `jarvis` / `ubuntu` / `root` |
| `VPS_SSH_KEY` | SSH 私鑰（完整內容，含 header/footer） | `-----BEGIN OPENSSH PRIVATE KEY-----...` |
| `VPS_PORT` | SSH 連接埠 | `22`（預設）或自訂埠號 |

> ⚠️ **注意**：`VPS_SSH_KEY` 填入的是**私鑰**（`id_rsa` 或 `id_ed25519`），對應的公鑰需事先放到 VPS 的 `~/.ssh/authorized_keys`。

---

## SSH 金鑰設定步驟

若尚未產生 SSH 金鑰，請在本機執行：

```bash
# 產生 ED25519 金鑰對（推薦）
ssh-keygen -t ed25519 -C "github-actions-deploy" -f ~/.ssh/github_deploy

# 將公鑰複製到 VPS
ssh-copy-id -i ~/.ssh/github_deploy.pub <VPS_USERNAME>@<VPS_HOST>

# 查看私鑰內容（貼到 GitHub Secret VPS_SSH_KEY）
cat ~/.ssh/github_deploy
```

---

## VPS 端前置條件

部署前請確認 VPS 上已完成以下設定：

1. **專案已 clone**
   ```bash
   git clone git@github.com:13g7895123/shoes.git /home/jarvis/project/bonus/official/shoes
   ```

2. **Git remote 設定正確**
   ```bash
   cd /home/jarvis/project/bonus/official/shoes
   git remote -v
   # 應顯示 origin 指向 GitHub repo
   ```

3. **SSH 公鑰已加入 authorized_keys**
   ```bash
   cat ~/.ssh/authorized_keys
   # 確認包含 GitHub Actions 使用的公鑰
   ```

4. **執行帳號有專案目錄的讀寫權限**
   ```bash
   ls -la /home/jarvis/project/bonus/official/shoes
   ```

---

## 可選的擴充步驟

`deploy.yml` 中有幾段被註解的指令，視需要取消註解：

```yaml
# 更新 Composer 套件（有新增 PHP 依賴時啟用）
docker compose exec -T php composer install --no-dev --optimize-autoloader

# 執行資料庫 Migration（有新增 migration 檔時啟用）
docker compose exec -T php php spark migrate

# 重新建置並啟動容器（有修改 Dockerfile 或 docker-compose.yml 時啟用）
docker compose down
docker compose up -d --build
```

---

## 常見問題

### Q: 推送後 Actions 顯示失敗，錯誤為 `Permission denied`
確認 `VPS_SSH_KEY` 的**公鑰**已加入 VPS 的 `~/.ssh/authorized_keys`，且私鑰內容完整複製（包含首尾的 `-----BEGIN` / `-----END` 那兩行）。

### Q: 想改成推送到其他分支才觸發部署
修改 `deploy.yml` 的 `branches` 設定：
```yaml
on:
  push:
    branches:
      - main   # 改成目標分支名稱
```

### Q: VPS 上的專案路徑不同
修改 `deploy.yml` 中 `script` 區塊的 `cd` 路徑：
```yaml
script: |
  cd /your/actual/project/path
  git pull origin master
```
