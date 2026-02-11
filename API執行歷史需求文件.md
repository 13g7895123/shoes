# API 需求文件 - 執行歷史記錄功能

## 📋 概述

為了記錄每次爬蟲執行的異動歷史，後端需要新增以下 API 端點。

**目標**：
- 記錄每次執行的完整歷程
- 追蹤每個商品的變更細節（新增/更新/略過）
- 支援歷史查詢和統計分析

---

## 🗄️ 資料庫設計

### 1. 執行歷史主表 (execution_history)

```sql
CREATE TABLE execution_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    execution_id VARCHAR(36) UNIQUE NOT NULL COMMENT 'UUID 執行ID',
    start_time DATETIME NOT NULL COMMENT '開始時間',
    end_time DATETIME COMMENT '結束時間',
    total_products INT NOT NULL COMMENT '總商品數',
    created_count INT DEFAULT 0 COMMENT '新增數量',
    updated_count INT DEFAULT 0 COMMENT '更新數量',
    skipped_count INT DEFAULT 0 COMMENT '略過數量',
    failed_count INT DEFAULT 0 COMMENT '失敗數量',
    duration_seconds DECIMAL(10,2) COMMENT '執行時長（秒）',
    status VARCHAR(20) NOT NULL COMMENT '執行狀態: running/success/failed/partial',
    mode VARCHAR(20) NOT NULL COMMENT '執行模式: test/production',
    error_message TEXT COMMENT '錯誤訊息（如有）',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_execution_id (execution_id),
    INDEX idx_start_time (start_time),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='執行歷史記錄';
```

### 2. 商品變更記錄表 (product_change_log)

```sql
CREATE TABLE product_change_log (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    execution_id VARCHAR(36) NOT NULL COMMENT '關聯執行ID',
    product_code VARCHAR(100) NOT NULL COMMENT '商品編號',
    product_name VARCHAR(500) COMMENT '商品名稱',
    action_type VARCHAR(20) NOT NULL COMMENT '動作類型: create/update/skip',
    
    -- 變更前後對比
    before_price VARCHAR(50) COMMENT '變更前價格',
    after_price VARCHAR(50) COMMENT '變更後價格',
    before_size TEXT COMMENT '變更前尺寸',
    after_size TEXT COMMENT '變更後尺寸',
    before_hope_price VARCHAR(50) COMMENT '變更前希望價格',
    after_hope_price VARCHAR(50) COMMENT '變更後希望價格',
    before_point VARCHAR(50) COMMENT '變更前點數',
    after_point VARCHAR(50) COMMENT '變更後點數',
    
    change_reason VARCHAR(255) COMMENT '變更原因',
    has_price_change BOOLEAN DEFAULT FALSE COMMENT '價格是否變動',
    has_size_change BOOLEAN DEFAULT FALSE COMMENT '尺寸是否變動',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_execution_id (execution_id),
    INDEX idx_product_code (product_code),
    INDEX idx_action_type (action_type),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (execution_id) REFERENCES execution_history(execution_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商品變更記錄';
```

---

## 🔌 API 端點規格

### 1. 開始執行記錄

**端點**: `POST /api/v1/shoes/execution/start`

**用途**: 在爬蟲開始執行時呼叫，創建執行記錄並獲得 execution_id

**請求 Body**:
```json
{
    "total_products": 528,
    "mode": "production"
}
```

**請求參數說明**:
| 欄位 | 類型 | 必填 | 說明 |
|-----|------|------|------|
| total_products | int | 是 | 預計爬取的總商品數 |
| mode | string | 是 | 執行模式：`test` 或 `production` |

**成功回應** (200):
```json
{
    "success": true,
    "data": {
        "execution_id": "550e8400-e29b-41d4-a716-446655440000",
        "start_time": "2026-02-10T21:30:00Z"
    },
    "message": "執行記錄已創建"
}
```

**錯誤回應** (400/500):
```json
{
    "success": false,
    "message": "參數錯誤：total_products 必須大於 0",
    "error_code": "INVALID_PARAMETER"
}
```

---

### 2. 記錄商品變更

**端點**: `POST /api/v1/shoes/execution/log`

**用途**: 記錄單個商品的變更細節

**請求 Body**:
```json
{
    "execution_id": "550e8400-e29b-41d4-a716-446655440000",
    "product_code": "1103a128-100",
    "product_name": "DSライト クラブ ホワイト×アシックスブルー",
    "action_type": "update",
    "before_price": "7,480",
    "after_price": "6,980",
    "before_size": "24.0, 25.0, 26.0",
    "after_size": "24.0, 25.0, 26.0, 27.0",
    "before_hope_price": "¥ 9,350",
    "after_hope_price": "¥ 9,350",
    "before_point": "340",
    "after_point": "340",
    "change_reason": "價格降低 500 日圓，新增 27.0 尺碼"
}
```

**請求參數說明**:
| 欄位 | 類型 | 必填 | 說明 |
|-----|------|------|------|
| execution_id | string | 是 | 執行 ID (UUID) |
| product_code | string | 是 | 商品編號 |
| product_name | string | 否 | 商品名稱 |
| action_type | string | 是 | `create`/`update`/`skip` |
| before_price | string | 否 | 變更前價格 |
| after_price | string | 是 | 變更後價格 |
| before_size | string | 否 | 變更前尺寸 |
| after_size | string | 是 | 變更後尺寸 |
| before_hope_price | string | 否 | 變更前希望價格 |
| after_hope_price | string | 否 | 變更後希望價格 |
| before_point | string | 否 | 變更前點數 |
| after_point | string | 否 | 變更後點數 |
| change_reason | string | 否 | 變更原因說明 |

**成功回應** (200):
```json
{
    "success": true,
    "message": "變更記錄已保存"
}
```

**錯誤回應**:
```json
{
    "success": false,
    "message": "執行 ID 不存在",
    "error_code": "EXECUTION_NOT_FOUND"
}
```

---

### 3. 批量記錄變更（選用，提升效能）

**端點**: `POST /api/v1/shoes/execution/log-batch`

**用途**: 一次記錄多個商品變更，減少網路往返

**請求 Body**:
```json
{
    "execution_id": "550e8400-e29b-41d4-a716-446655440000",
    "changes": [
        {
            "product_code": "1103a128-100",
            "action_type": "update",
            "before_price": "7,480",
            "after_price": "6,980",
            "change_reason": "價格降低"
        },
        {
            "product_code": "1103a128-001",
            "action_type": "create",
            "after_price": "8,500",
            "after_size": "24.0, 25.0",
            "change_reason": "新商品"
        }
    ]
}
```

**成功回應** (200):
```json
{
    "success": true,
    "data": {
        "total_logged": 2,
        "success_count": 2,
        "failed_count": 0
    },
    "message": "批量記錄完成"
}
```

---

### 4. 完成執行記錄

**端點**: `POST /api/v1/shoes/execution/complete`

**用途**: 標記執行完成，更新統計資訊

**請求 Body**:
```json
{
    "execution_id": "550e8400-e29b-41d4-a716-446655440000",
    "created_count": 50,
    "updated_count": 30,
    "skipped_count": 448,
    "failed_count": 0,
    "status": "success",
    "error_message": null
}
```

**請求參數說明**:
| 欄位 | 類型 | 必填 | 說明 |
|-----|------|------|------|
| execution_id | string | 是 | 執行 ID |
| created_count | int | 是 | 新增商品數 |
| updated_count | int | 是 | 更新商品數 |
| skipped_count | int | 是 | 略過商品數 |
| failed_count | int | 是 | 失敗商品數 |
| status | string | 是 | `success`/`failed`/`partial` |
| error_message | string | 否 | 錯誤訊息（status=failed 時提供） |

**成功回應** (200):
```json
{
    "success": true,
    "data": {
        "execution_id": "550e8400-e29b-41d4-a716-446655440000",
        "end_time": "2026-02-10T21:33:45Z",
        "duration_seconds": 225.5
    },
    "message": "執行記錄已完成"
}
```

---

### 5. 查詢執行歷史列表

**端點**: `GET /api/v1/shoes/execution/history`

**用途**: 查詢歷史執行記錄

**查詢參數**:
```
GET /api/v1/shoes/execution/history?page=1&page_size=20&status=success&mode=production&start_date=2026-02-01&end_date=2026-02-10
```

| 參數 | 類型 | 必填 | 說明 |
|-----|------|------|------|
| page | int | 否 | 頁碼（預設 1） |
| page_size | int | 否 | 每頁筆數（預設 20） |
| status | string | 否 | 狀態篩選 |
| mode | string | 否 | 模式篩選 |
| start_date | date | 否 | 開始日期 |
| end_date | date | 否 | 結束日期 |

**成功回應** (200):
```json
{
    "success": true,
    "data": {
        "total": 156,
        "page": 1,
        "page_size": 20,
        "executions": [
            {
                "execution_id": "550e8400-e29b-41d4-a716-446655440000",
                "start_time": "2026-02-10T21:30:00Z",
                "end_time": "2026-02-10T21:33:45Z",
                "total_products": 528,
                "created_count": 50,
                "updated_count": 30,
                "skipped_count": 448,
                "failed_count": 0,
                "duration_seconds": 225.5,
                "status": "success",
                "mode": "production"
            }
        ]
    }
}
```

---

### 6. 查詢執行詳情

**端點**: `GET /api/v1/shoes/execution/{execution_id}`

**用途**: 查詢單次執行的詳細資訊，包含所有變更記錄

**成功回應** (200):
```json
{
    "success": true,
    "data": {
        "execution_id": "550e8400-e29b-41d4-a716-446655440000",
        "start_time": "2026-02-10T21:30:00Z",
        "end_time": "2026-02-10T21:33:45Z",
        "total_products": 528,
        "created_count": 50,
        "updated_count": 30,
        "skipped_count": 448,
        "failed_count": 0,
        "duration_seconds": 225.5,
        "status": "success",
        "mode": "production",
        "changes": [
            {
                "product_code": "1103a128-100",
                "product_name": "DSライト クラブ",
                "action_type": "update",
                "before_price": "7,480",
                "after_price": "6,980",
                "before_size": "24.0, 25.0, 26.0",
                "after_size": "24.0, 25.0, 26.0, 27.0",
                "change_reason": "價格降低，新增尺碼",
                "has_price_change": true,
                "has_size_change": true,
                "created_at": "2026-02-10T21:31:15Z"
            }
        ]
    }
}
```

---

### 7. 查詢商品變更歷史

**端點**: `GET /api/v1/shoes/{product_code}/changes`

**用途**: 查詢特定商品的所有歷史變更

**查詢參數**:
```
GET /api/v1/shoes/1103a128-100/changes?limit=50
```

**成功回應** (200):
```json
{
    "success": true,
    "data": {
        "product_code": "1103a128-100",
        "total_changes": 15,
        "changes": [
            {
                "execution_id": "550e8400-e29b-41d4-a716-446655440000",
                "action_type": "update",
                "before_price": "7,480",
                "after_price": "6,980",
                "change_reason": "價格降低",
                "created_at": "2026-02-10T21:31:15Z"
            },
            {
                "execution_id": "449d7300-d18a-30c3-9615-335544330000",
                "action_type": "update",
                "before_size": "24.0, 25.0",
                "after_size": "24.0, 25.0, 26.0",
                "change_reason": "新增尺碼",
                "created_at": "2026-02-09T15:20:30Z"
            }
        ]
    }
}
```

---

### 8. 統計數據 API

**端點**: `GET /api/v1/shoes/execution/statistics`

**用途**: 獲取執行統計數據（用於儀表板）

**查詢參數**:
```
GET /api/v1/shoes/execution/statistics?period=last_7_days
```

**成功回應** (200):
```json
{
    "success": true,
    "data": {
        "period": "last_7_days",
        "total_executions": 14,
        "success_executions": 13,
        "failed_executions": 1,
        "total_products_crawled": 7392,
        "total_created": 156,
        "total_updated": 423,
        "total_skipped": 6813,
        "avg_duration_seconds": 198.5,
        "daily_stats": [
            {
                "date": "2026-02-10",
                "executions": 2,
                "created": 50,
                "updated": 30,
                "skipped": 448
            }
        ]
    }
}
```

---

## 🔐 認證與授權

所有 API 端點都需要包含 API Key：

**Header**:
```
X-API-Key: your-api-key-here
```

**錯誤回應** (401):
```json
{
    "success": false,
    "message": "缺少 API Key",
    "error_code": "UNAUTHORIZED"
}
```

---

## 📊 使用流程範例

### 完整執行流程

```
1. 開始執行
   POST /api/v1/shoes/execution/start
   → 取得 execution_id

2. 爬取商品並記錄變更（對每個商品）
   - 檢查狀態: POST /api/v1/shoes/check-status
   - 如果需要新增: POST /api/v1/shoes
   - 如果需要更新: PUT /api/v1/shoes/{code}
   - 記錄變更: POST /api/v1/shoes/execution/log

3. 完成執行
   POST /api/v1/shoes/execution/complete
   → 更新統計資訊

4. 查詢歷史（可選）
   GET /api/v1/shoes/execution/history
```

---

## ⚡ 效能優化建議

### 批量操作
- 使用 `/execution/log-batch` 批量記錄變更（建議每 50-100 筆批量一次）
- 減少 API 呼叫次數，提升整體效能

### 非同步處理
- 變更記錄可以非同步寫入，不阻塞主流程
- 如果記錄失敗，不影響商品資料的新增/更新

### 索引優化
- `execution_id` 和 `product_code` 建立複合索引
- `created_at` 建立索引以支援時間範圍查詢

---

## 🎯 錯誤代碼

| 錯誤碼 | HTTP 狀態 | 說明 |
|-------|----------|------|
| UNAUTHORIZED | 401 | 缺少或無效的 API Key |
| INVALID_PARAMETER | 400 | 請求參數錯誤 |
| EXECUTION_NOT_FOUND | 404 | 執行 ID 不存在 |
| EXECUTION_COMPLETED | 409 | 執行已完成，無法再記錄 |
| DATABASE_ERROR | 500 | 資料庫錯誤 |
| INTERNAL_ERROR | 500 | 伺服器內部錯誤 |

---

## 📝 備註

1. **execution_id 使用 UUID v4** 確保全域唯一性
2. **所有時間使用 UTC** 並以 ISO 8601 格式傳遞
3. **價格字串保留原始格式**（包含逗號和貨幣符號）
4. **建議保留歷史資料至少 6 個月**，超過可歸檔或刪除
5. **批量 API 建議每批 50-100 筆**，避免單次請求過大

---

**文件版本**: 1.0  
**最後更新**: 2026-02-10
