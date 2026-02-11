#!/bin/bash

# 顏色定義
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 設定預設環境為 development
ENVIRONMENT="${1:-development}"

# Docker 相關路徑
DOCKER_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/docker"
ENV_FILE="${DOCKER_DIR}/.env"
ENV_SOURCE="${DOCKER_DIR}/envs/.env.${ENVIRONMENT}"

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Bonus Shoes Docker Deployment${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# 驗證環境參數
if [[ ! "${ENVIRONMENT}" =~ ^(development|staging|production)$ ]]; then
    echo -e "${RED}錯誤: 不支援的環境 '${ENVIRONMENT}'${NC}"
    echo -e "${YELLOW}使用方式: ./deploy.sh [development|staging|production]${NC}"
    echo -e "${YELLOW}預設環境: development${NC}"
    exit 1
fi

echo -e "部署環境: ${GREEN}${ENVIRONMENT}${NC}"
echo ""

# 檢查 docker 目錄是否存在
if [ ! -d "${DOCKER_DIR}" ]; then
    echo -e "${RED}錯誤: Docker 目錄不存在: ${DOCKER_DIR}${NC}"
    exit 1
fi

# 檢查 .env 檔案
if [ -f "${ENV_FILE}" ]; then
    echo -e "${YELLOW}發現現有的 .env 檔案${NC}"
    read -p "是否要覆蓋現有的 .env 檔案? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo -e "${GREEN}使用現有的 .env 檔案繼續部署${NC}"
    else
        # 覆蓋 .env 檔案
        if [ -f "${ENV_SOURCE}" ]; then
            echo -e "${GREEN}從 ${ENV_SOURCE} 複製環境設定檔...${NC}"
            cp "${ENV_SOURCE}" "${ENV_FILE}"
        else
            echo -e "${RED}錯誤: 找不到環境設定檔 ${ENV_SOURCE}${NC}"
            exit 1
        fi
    fi
else
    # .env 檔案不存在，從範本複製
    if [ -f "${ENV_SOURCE}" ]; then
        echo -e "${GREEN}從 ${ENV_SOURCE} 建立環境設定檔...${NC}"
        cp "${ENV_SOURCE}" "${ENV_FILE}"
    else
        echo -e "${RED}錯誤: 找不到環境設定檔 ${ENV_SOURCE}${NC}"
        echo -e "${YELLOW}可用的環境設定檔:${NC}"
        ls -1 "${DOCKER_DIR}/envs/"
        exit 1
    fi
fi

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  啟動 Docker Compose${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# 切換到 docker 目錄
cd "${DOCKER_DIR}" || exit 1

# 停止現有容器（如果有）
echo -e "${YELLOW}停止現有容器...${NC}"
docker compose down

# 啟動容器
echo -e "${GREEN}啟動 Docker 容器...${NC}"
docker compose up -d --build

# 檢查啟動狀態
if [ $? -eq 0 ]; then
    echo ""
    echo -e "${GREEN}========================================${NC}"
    echo -e "${GREEN}  部署完成!${NC}"
    echo -e "${GREEN}========================================${NC}"
    echo ""
    echo -e "環境: ${GREEN}${ENVIRONMENT}${NC}"
    echo ""
    echo "容器狀態:"
    docker compose ps
    echo ""
    echo -e "${YELLOW}提示:${NC}"
    echo "  - 查看日誌: docker compose logs -f"
    echo "  - 停止服務: docker compose down"
    echo "  - 重新啟動: docker compose restart"
else
    echo ""
    echo -e "${RED}========================================${NC}"
    echo -e "${RED}  部署失敗!${NC}"
    echo -e "${RED}========================================${NC}"
    echo ""
    echo "請檢查錯誤訊息並修正問題。"
    exit 1
fi
