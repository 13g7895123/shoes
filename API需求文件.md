# Go 爬蟲專案 API 需求文件

**版本**：1.0  
**更新日期**：2026-02-21  
**專案路徑**：`go/`

---

## 目錄

1. [概述](#概述)
2. [通用規格](#通用規格)
3. [API 端點清單](#api-端點清單)
4. [端點詳細規格](#端點詳細規格)
   - [健康檢查](#1-健康檢查)
   - [檢查商品狀態](#2-檢查商品狀態)
   - [取得單一商品](#3-取得單一商品)
   - [新增商品](#4-新增商品)
   - [更新商品](#5-更新商品)
   - [新增展示商品](#6-新增展示用商品)
   - [清空資料表](#7-清空資料表)
   - [開始執行記錄](#8-開始執行記錄)
   - [記錄單筆商品變更](#9-記錄單筆商品變更)
   - [批量記錄商品變更](#10-批量記錄商品變更)
   - [完成執行記錄](#11-完成執行記錄)
5. [資料模型](#資料模型)
6. [執行流程與 API 呼叫順序](#執行流程與-api-呼叫順序)
7. [錯誤處理規格](#錯誤處理規格)

---

## 概述

本文件定義 Go 爬蟲程式所需的全部後端 API 規格。爬蟲程式負責從 kishispo.net 抓取鞋類商品資訊，透過 API 與資料庫進行比對、新增、更新，並支援執行歷史記錄功能。

**資料庫涉及的資料表**

| 資料表名稱 | 用途 |
|---|---|
| `shoes_inf` | 儲存所有商品主資料 |
| `shoes_show_inf` | 展示用資料表，每次執行後完整替換 |
| `execution_history`（選用） | 執行歷史主表 |
| `product_changes`（選用） | 商品變更明細表 |
| `api_check` | API 連線確認用表（需含至少一筆資料） |

---

## 通用規格

### Base URL

```
http://localhost:8080/api/v1
```

可透過 `config.yaml` 的 `api.base_url` 設定調整。

### 認證方式

所有端點均需在 HTTP Header 中傳入 API 金鑰：

```
X-API-Key: <api_key>
```

> 若 `api_key` 在 `config.yaml` 中為空字串，爬蟲程式將直接跳過 API 連線，終止執行。

### 請求格式

```
Content-Type: application/json
Accept: application/json
```

### 統一回應格式

除健康檢查外，所有 API 回應皆遵循以下格式：

```json
{
  "success": true,
  "message": "操作說明訊息",
  "data": { ... }
}
```

| 欄位 | 型別 | 說明 |
|---|---|---|
| `success` | `boolean` | 操作是否成功 |
| `message` | `string` | 說明訊息 |
| `data` | `object \| null` | 回應資料（無資料時可為 `null`） |

### HTTP 逾時設定

爬蟲預設請求逾時：**30 秒**（可透過 `config.yaml` 的 `api.timeout` 調整）。

---

## API 端點清單

| # | 方法 | 端點 | 功能 | 必要性 |
|---|---|---|---|---|
| 1 | `GET` | `/health/database` | 健康檢查／確認資料庫連線 | **必要** |
| 2 | `POST` | `/shoes/check-status` | 檢查單一商品狀態（需新增/更新/略過） | **必要** |
| 3 | `GET` | `/shoes/:code` | 取得單一商品資料 | 啟用歷史記錄時必要 |
| 4 | `POST` | `/shoes` | 新增商品至 `shoes_inf` | **必要** |
| 5 | `PUT` | `/shoes/:code` | 更新商品價格與尺寸 | **必要** |
| 6 | `POST` | `/shoes/show` | 新增展示用商品至 `shoes_show_inf` | **必要** |
| 7 | `DELETE` | `/shoes/clear/:table_name` | 清空指定資料表 | **必要** |
| 8 | `POST` | `/shoes/execution/start` | 開始執行記錄 | 啟用歷史記錄時必要 |
| 9 | `POST` | `/shoes/execution/log` | 記錄單筆商品變更 | 啟用歷史記錄時必要 |
| 10 | `POST` | `/shoes/execution/log-batch` | 批量記錄商品變更（最多 50 筆） | 啟用歷史記錄時必要 |
| 11 | `POST` | `/shoes/execution/complete` | 完成執行記錄 | 啟用歷史記錄時必要 |

---

## 端點詳細規格

---

### 1. 健康檢查

確認資料庫連線狀態，程式啟動時第一個呼叫的端點。

- **方法**：`GET`
- **路徑**：`/health/database`
- **請求 Body**：無

**成功回應** `200 OK`

```json
{
  "database": "connected"
}
```

**失敗回應** `503 Service Unavailable`

```json
{
  "database": "disconnected"
}
```

**爬蟲行為說明**

- 若 `database` 不等於 `"connected"`，爬蟲記錄警告並終止執行。
- 若 HTTP 錯誤或連線失敗，同樣終止執行。

---

### 2. 檢查商品狀態

比對邏輯的核心端點。對單一商品判斷應執行「略過」、「更新」還是「新增」。

- **方法**：`POST`
- **路徑**：`/shoes/check-status`

**請求 Body**

```json
{
  "code": "ABC12345",
  "price": "¥12,800",
  "size": "25.0, 25.5, 26.0"
}
```

| 欄位 | 型別 | 必填 | 說明 |
|---|---|---|---|
| `code` | `string` | ✅ | 商品唯一編號 |
| `price` | `string` | ✅ | 目前爬取到的實際售價 |
| `size` | `string` | ✅ | 目前爬取到的可用尺寸（逗號分隔字串） |

**成功回應** `200 OK`

```json
{
  "action_required": 0
}
```

| `action_required` 值 | 意義 | 後續動作 |
|---|---|---|
| `0` | 資料一致，無需處理 | 略過，不呼叫其他 API |
| `1` | 商品已存在，但 price 或 size 有變動 | 呼叫 `PUT /shoes/:code` 更新 |
| `2` | 商品不存在於資料庫 | 呼叫 `POST /shoes` 新增 |

**後端比對邏輯（必須實作）**

```sql
-- 第一步：以 code 查詢是否存在
SELECT * FROM shoes_inf WHERE code = :code

-- 若無資料 → 回傳 action_required = 2（新增）

-- 若有資料，進行第二步
SELECT * FROM shoes_inf WHERE code = :code AND price = :price AND size = :size

-- 若有資料 → 回傳 action_required = 0（略過）
-- 若無資料 → 回傳 action_required = 1（更新）
```

---

### 3. 取得單一商品

取得商品現有資料，用於記錄執行歷史時保存「變更前」狀態。

- **方法**：`GET`
- **路徑**：`/shoes/:code`
- **路徑參數**：`code` = 商品編號

**成功回應** `200 OK`

```json
{
  "success": true,
  "message": "ok",
  "data": {
    "name": "ナイキ エア ズーム",
    "eng_name": "Nike Air Zoom",
    "code": "ABC12345",
    "hope_price": "¥15,000",
    "price": "¥12,800",
    "point": "128",
    "size": "25.0, 25.5, 26.0"
  }
}
```

**回應 data 欄位**

| 欄位 | 型別 | 說明 |
|---|---|---|
| `name` | `string` | 商品名稱（日文） |
| `eng_name` | `string` | 商品名稱（英文） |
| `code` | `string` | 商品編號 |
| `hope_price` | `string` | 希望售價 |
| `price` | `string` | 實際售價 |
| `point` | `string` | 紅利點數 |
| `size` | `string` | 可用尺寸字串 |

**失敗回應** `404 Not Found`

```json
{
  "success": false,
  "message": "商品不存在",
  "data": null
}
```

---

### 4. 新增商品

將新商品寫入 `shoes_inf` 資料表（對應比對結果 `action_required = 2`）。

- **方法**：`POST`
- **路徑**：`/shoes`

**請求 Body**

```json
{
  "name": "ナイキ エア ズーム",
  "eng_name": "Nike Air Zoom",
  "code": "ABC12345",
  "hope_price": "¥15,000",
  "price": "¥12,800",
  "point": "128",
  "size": "25.0, 25.5, 26.0"
}
```

| 欄位 | 型別 | 必填 | 說明 |
|---|---|---|---|
| `name` | `string` | ✅ | 商品名稱（日文） |
| `eng_name` | `string` | ✅ | 商品名稱（英文翻譯） |
| `code` | `string` | ✅ | 商品唯一編號 |
| `hope_price` | `string` | ✅ | 希望售價（可為空字串） |
| `price` | `string` | ✅ | 實際售價 |
| `point` | `string` | ✅ | 紅利點數 |
| `size` | `string` | ✅ | 可用尺寸（逗號分隔字串） |

**對應 SQL**

```sql
INSERT INTO shoes_inf (name, eng_name, code, hope_price, price, point, size)
VALUES (:name, :eng_name, :code, :hope_price, :price, :point, :size)
```

**成功回應** `201 Created`

```json
{
  "success": true,
  "message": "商品新增成功",
  "data": null
}
```

**失敗回應** `409 Conflict`（商品編號已存在）

```json
{
  "success": false,
  "message": "商品編號已存在",
  "data": null
}
```

---

### 5. 更新商品

更新 `shoes_inf` 中已存在之商品的 `price` 與 `size` 欄位（對應比對結果 `action_required = 1`）。

- **方法**：`PUT`
- **路徑**：`/shoes/:code`
- **路徑參數**：`code` = 商品編號

**請求 Body**

```json
{
  "price": "¥11,500",
  "size": "25.0, 26.0, 27.0"
}
```

| 欄位 | 型別 | 必填 | 說明 |
|---|---|---|---|
| `price` | `string` | ✅ | 新的實際售價 |
| `size` | `string` | ✅ | 新的可用尺寸 |

**注意**：只更新 `price` 與 `size`，`name`、`eng_name`、`hope_price`、`point` 等欄位**不做異動**。

**對應 SQL**

```sql
UPDATE shoes_inf SET price = :price, size = :size WHERE code = :code
```

**成功回應** `200 OK`

```json
{
  "success": true,
  "message": "商品更新成功",
  "data": null
}
```

**失敗回應** `404 Not Found`

```json
{
  "success": false,
  "message": "商品不存在",
  "data": null
}
```

---

### 6. 新增展示用商品

將本次有異動（新增或更新）的商品寫入 `shoes_show_inf` 展示資料表。

**執行時機**：在清空 `shoes_show_inf`（端點 7）之後，對每一筆有異動的商品逐一呼叫此端點。

- **方法**：`POST`
- **路徑**：`/shoes/show`

**請求 Body**（欄位與端點 4 相同）

```json
{
  "name": "ナイキ エア ズーム",
  "eng_name": "Nike Air Zoom",
  "code": "ABC12345",
  "hope_price": "¥15,000",
  "price": "¥12,800",
  "point": "128",
  "size": "25.0, 25.5, 26.0"
}
```

**對應 SQL**

```sql
INSERT INTO shoes_show_inf (name, eng_name, code, hope_price, price, point, size)
VALUES (:name, :eng_name, :code, :hope_price, :price, :point, :size)
```

**成功回應** `201 Created`

```json
{
  "success": true,
  "message": "展示商品新增成功",
  "data": null
}
```

---

### 7. 清空資料表

清空指定資料表的所有資料（`TRUNCATE TABLE`）。

**執行時機**：有任何異動資料、準備輸出 Excel 前，先清空 `shoes_show_inf`。

- **方法**：`DELETE`
- **路徑**：`/shoes/clear/:table_name`
- **路徑參數**：`table_name` = 資料表名稱

**目前使用的表名**

| 傳入值 | 對應資料表 |
|---|---|
| `shoes_show_inf` | 展示用資料表 |

**請求 Body**

```json
{
  "confirm": true
}
```

> `confirm` 欄位為防呆機制，必須傳 `true` 才執行清空。

**對應 SQL**

```sql
TRUNCATE TABLE shoes_show_inf
```

**成功回應** `200 OK`

```json
{
  "success": true,
  "message": "資料表已清空",
  "data": null
}
```

**失敗回應** `400 Bad Request`（`confirm` 非 `true`）

```json
{
  "success": false,
  "message": "請確認清空操作",
  "data": null
}
```

---

### 8. 開始執行記錄

建立本次爬蟲執行的歷史記錄，回傳一組執行 ID 供後續呼叫使用。

**此端點只在 `config.yaml` 中 `api.enable_history: true` 時才會呼叫。**

- **方法**：`POST`
- **路徑**：`/shoes/execution/start`

**請求 Body**

```json
{
  "total_products": 150,
  "mode": "production"
}
```

| 欄位 | 型別 | 必填 | 說明 |
|---|---|---|---|
| `total_products` | `integer` | ✅ | 本次預計處理的商品總數 |
| `mode` | `string` | ✅ | 執行模式：`"test"` 或 `"production"` |

**成功回應** `200 OK`

```json
{
  "success": true,
  "message": "執行記錄已建立",
  "data": {
    "execution_id": "550e8400-e29b-41d4-a716-446655440000",
    "start_time": "2026-02-21T10:30:00+09:00"
  }
}
```

| `data` 欄位 | 型別 | 說明 |
|---|---|---|
| `execution_id` | `string` | 本次執行的 UUID，後續記錄變更時需帶入 |
| `start_time` | `string` | 執行開始時間（ISO 8601） |

---

### 9. 記錄單筆商品變更

記錄單一商品的變更詳情到歷史資料表。

**注意**：爬蟲實際上優先使用「批量記錄」（端點 10），此端點為備用。

- **方法**：`POST`
- **路徑**：`/shoes/execution/log`

**請求 Body**

```json
{
  "execution_id": "550e8400-e29b-41d4-a716-446655440000",
  "product_code": "ABC12345",
  "product_name": "ナイキ エア ズーム",
  "action_type": "update",
  "before_price": "¥12,800",
  "after_price": "¥11,500",
  "before_size": "25.0, 25.5, 26.0",
  "after_size": "25.0, 26.0, 27.0",
  "before_hope_price": "¥15,000",
  "after_hope_price": "¥15,000",
  "before_point": "128",
  "after_point": "128",
  "change_reason": "價格變動 ¥12,800 → ¥11,500，尺寸變動"
}
```

| 欄位 | 型別 | 必填 | 說明 |
|---|---|---|---|
| `execution_id` | `string` | ✅ | 執行 ID（來自端點 8） |
| `product_code` | `string` | ✅ | 商品編號 |
| `product_name` | `string` | ❌ | 商品名稱 |
| `action_type` | `string` | ✅ | `"create"` / `"update"` / `"skip"` |
| `before_price` | `string` | ❌ | 變更前價格（update 時有值） |
| `after_price` | `string` | ✅ | 變更後價格 |
| `before_size` | `string` | ❌ | 變更前尺寸（update 時有值） |
| `after_size` | `string` | ✅ | 變更後尺寸 |
| `before_hope_price` | `string` | ❌ | 變更前希望售價 |
| `after_hope_price` | `string` | ❌ | 變更後希望售價 |
| `before_point` | `string` | ❌ | 變更前點數 |
| `after_point` | `string` | ❌ | 變更後點數 |
| `change_reason` | `string` | ❌ | 變更原因說明 |

**成功回應** `200 OK`

```json
{
  "success": true,
  "message": "變更記錄已儲存",
  "data": null
}
```

---

### 10. 批量記錄商品變更

批量提交多筆商品變更記錄，每 50 筆觸發一次呼叫。

- **方法**：`POST`
- **路徑**：`/shoes/execution/log-batch`

**請求 Body**

```json
{
  "execution_id": "550e8400-e29b-41d4-a716-446655440000",
  "changes": [
    {
      "execution_id": "550e8400-e29b-41d4-a716-446655440000",
      "product_code": "ABC12345",
      "product_name": "ナイキ エア ズーム",
      "action_type": "update",
      "before_price": "¥12,800",
      "after_price": "¥11,500",
      "before_size": "25.0, 25.5, 26.0",
      "after_size": "25.0, 26.0, 27.0",
      "change_reason": "價格變動 ¥12,800 → ¥11,500"
    },
    {
      "execution_id": "550e8400-e29b-41d4-a716-446655440000",
      "product_code": "XYZ67890",
      "product_name": "アディダス ウルトラブースト",
      "action_type": "create",
      "after_price": "¥18,000",
      "after_size": "26.0, 26.5, 27.0",
      "change_reason": "新商品"
    }
  ]
}
```

| 欄位 | 型別 | 必填 | 說明 |
|---|---|---|---|
| `execution_id` | `string` | ✅ | 執行 ID |
| `changes` | `array` | ✅ | 變更記錄陣列，單次最多 **50 筆** |
| `changes[n]` | `object` | ✅ | 欄位定義同端點 9 |

**成功回應** `200 OK`

```json
{
  "success": true,
  "message": "已批量記錄 2 筆變更",
  "data": null
}
```

---

### 11. 完成執行記錄

關閉本次執行記錄，寫入最終統計數字與結束時間。

- **方法**：`POST`
- **路徑**：`/shoes/execution/complete`

**請求 Body**

```json
{
  "execution_id": "550e8400-e29b-41d4-a716-446655440000",
  "created_count": 5,
  "updated_count": 12,
  "skipped_count": 133,
  "failed_count": 0,
  "status": "success",
  "error_message": ""
}
```

| 欄位 | 型別 | 必填 | 說明 |
|---|---|---|---|
| `execution_id` | `string` | ✅ | 執行 ID（來自端點 8） |
| `created_count` | `integer` | ✅ | 本次新增商品數 |
| `updated_count` | `integer` | ✅ | 本次更新商品數 |
| `skipped_count` | `integer` | ✅ | 本次略過商品數 |
| `failed_count` | `integer` | ✅ | 本次爬取失敗商品數 |
| `status` | `string` | ✅ | `"success"` / `"failed"` / `"partial"` |
| `error_message` | `string` | ❌ | 失敗時的錯誤訊息 |

**成功回應** `200 OK`

```json
{
  "success": true,
  "message": "執行記錄已完成",
  "data": {
    "execution_id": "550e8400-e29b-41d4-a716-446655440000",
    "end_time": "2026-02-21T10:45:30+09:00",
    "duration_seconds": 930.5
  }
}
```

---

## 資料模型

### Product（商品）

```
shoes_inf 資料表欄位：
┌─────────────┬──────────────────────────────────────────┐
│ 欄位        │ 說明                                     │
├─────────────┼──────────────────────────────────────────┤
│ name        │ 商品名稱（日文）                          │
│ eng_name    │ 商品名稱（英文）                          │
│ code        │ 商品編號（唯一鍵）                        │
│ hope_price  │ 希望售價                                  │
│ price       │ 實際售價                                  │
│ point       │ 紅利點數                                  │
│ size        │ 可用尺寸（逗號分隔字串，如 "25.0, 26.0"） │
└─────────────┴──────────────────────────────────────────┘
```

### Action Type 值定義

| 值 | 說明 |
|---|---|
| `"create"` | 新增的商品 |
| `"update"` | 已更新的商品 |
| `"skip"` | 資料無異動，略過 |

### Execution Status 值定義

| 值 | 說明 |
|---|---|
| `"success"` | 全部執行成功 |
| `"failed"` | 執行失敗 |
| `"partial"` | 部分成功（有失敗商品但程式正常結束） |

---

## 執行流程與 API 呼叫順序

```
程式啟動
    │
    ├─ [1] GET /health/database ──→ 失敗 → 程式終止
    │
    ├─ 爬蟲執行（取得商品列表）
    │
    ├─ [enable_history=true] POST /shoes/execution/start  ←── 取得 execution_id
    │
    ├─ 對每一個商品（或每 50 筆批量）：
    │   ├─ [2] POST /shoes/check-status  ←── 判斷 action_required
    │   │
    │   ├─ action=1（更新）
    │   │   ├─ [enable_history=true] GET /shoes/:code  ←── 取得變更前狀態
    │   │   └─ [5] PUT /shoes/:code
    │   │
    │   ├─ action=2（新增）
    │   │   └─ [4] POST /shoes
    │   │
    │   └─ action=0（略過）→ 不呼叫 API
    │
    ├─ [enable_history=true] POST /shoes/execution/log-batch（每 50 筆觸發）
    │
    ├─ 若有異動（created > 0 OR updated > 0）：
    │   ├─ [7] DELETE /shoes/clear/shoes_show_inf  ←── 清空展示表
    │   └─ [6] POST /shoes/show（逐筆寫入有異動商品）
    │
    ├─ 匯出 Excel
    │
    └─ [enable_history=true] POST /shoes/execution/complete
```

---

## 錯誤處理規格

### HTTP 狀態碼

| 狀態碼 | 說明 | 爬蟲行為 |
|---|---|---|
| `200 OK` | 操作成功 | 繼續執行 |
| `201 Created` | 新增成功 | 繼續執行 |
| `400 Bad Request` | 請求格式錯誤 | 記錄錯誤，略過該商品 |
| `401 Unauthorized` | API Key 錯誤或未提供 | 記錄錯誤，略過該商品 |
| `404 Not Found` | 資源不存在 | 記錄錯誤，略過該商品 |
| `409 Conflict` | 資料衝突（如 code 重複） | 記錄錯誤，略過該商品 |
| `500 Internal Server Error` | 伺服器錯誤 | 記錄錯誤，略過該商品 |
| `503 Service Unavailable` | 服務不可用 | 拋出例外，終止程式 |

### 錯誤回應格式

```json
{
  "success": false,
  "message": "錯誤說明",
  "data": null
}
```

### 爬蟲端的重試機制

- 單一商品 API 呼叫失敗時，記錄錯誤日誌並 **略過該商品**，不影響其他商品的處理。
- 執行歷史相關 API（端點 8～11）失敗時，**記錄警告但不中止主流程**（歷史記錄為非必要功能）。
- `GET /health/database` 失敗時，**直接終止程式**。
