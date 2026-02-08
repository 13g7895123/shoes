# 鞋子商品管理系統 API 說明文件

## 文件資訊

| 項目 | 內容 |
|------|------|
| API 名稱 | 鞋子商品管理系統 API |
| 版本 | v1 |
| 文件版本 | 1.0.0 |
| 最後更新 | 2026-02-08 |

---

## Base URL

- 開發環境: http://localhost:8080/api/v1

---

## 認證與授權

### API Key

請在每個請求加上 HTTP 標頭:

```http
X-API-Key: your-api-key
```

權限等級:

- READ: 查詢、健康檢查
- WRITE: 新增、更新、批次操作
- DELETE: 刪除商品、清空資料表
- ADMIN: 所有操作

---

## 資料模型 (Shoe)

```json
{
  "id": "integer",
  "name": "string (max: 200)",
  "eng_name": "string (max: 200)",
  "code": "string (max: 50, unique)",
  "hope_price": "string (max: 20)",
  "price": "string (max: 20)",
  "point": "string (max: 20)",
  "size": "string (max: 500)",
  "action": "string (max: 50)",
  "created_at": "datetime",
  "updated_at": "datetime"
}
```

---

## API 端點

### 1. 健康檢查

#### 1.1 資料庫健康檢查

- 方法: GET
- 端點: /health/database
- 認證: 不需要

成功回應:

```json
{
  "status": "success",
  "database": "connected",
  "response_time_ms": 12.3,
  "timestamp": "2026-02-08T08:00:00Z"
}
```

---

### 2. 商品管理

#### 2.1 檢查商品狀態

- 方法: POST
- 端點: /shoes/check-status
- 權限: READ

請求主體:

```json
{
  "code": "ABC123",
  "price": "5000",
  "size": "27.0, 28.0"
}
```

回應:

```json
{
  "status": "success",
  "action_required": 0,
  "message": "商品資料無需更新",
  "existing_data": { ... }
}
```

---

#### 2.2 新增商品

- 方法: POST
- 端點: /shoes
- 權限: WRITE

請求主體:

```json
{
  "name": "ナイキ エアマックス",
  "eng_name": "Nike Air Max",
  "code": "ABC123",
  "hope_price": "8000",
  "price": "5000",
  "point": "500",
  "size": "27.0, 28.0, 29.0"
}
```

---

#### 2.3 更新商品

- 方法: PUT
- 端點: /shoes/{code}
- 權限: WRITE

請求主體:

```json
{
  "price": "4500",
  "size": "27.0, 28.0, 29.0"
}
```

---

#### 2.4 刪除商品

- 方法: DELETE
- 端點: /shoes/{code}
- 權限: DELETE

---

#### 2.5 取得所有商品編號

- 方法: GET
- 端點: /shoes/codes
- 權限: READ

查詢參數:

- limit (選填)
- offset (選填)

---

#### 2.6 清空展示資料表

- 方法: DELETE
- 端點: /shoes/clear/{table_name}
- 權限: DELETE + ADMIN

請求主體:

```json
{
  "confirm": true
}
```

---

### 3. 批次操作

#### 3.1 批次新增商品

- 方法: POST
- 端點: /shoes/batch
- 權限: WRITE

請求主體:

```json
{
  "products": [
    {
      "name": "Nike Air Max",
      "eng_name": "Nike Air Max",
      "code": "ABC123",
      "hope_price": "8000",
      "price": "5000",
      "point": "500",
      "size": "27.0, 28.0"
    }
  ]
}
```

---

#### 3.2 批次更新商品

- 方法: PUT
- 端點: /shoes/batch
- 權限: WRITE

請求主體:

```json
{
  "updates": [
    {
      "code": "ABC123",
      "price": "4500",
      "size": "27.0, 28.0"
    }
  ]
}
```

---

## 錯誤回應格式

```json
{
  "status": "error",
  "message": "錯誤訊息描述",
  "error_code": "ERROR_CODE",
  "timestamp": "2026-02-08T08:00:00Z"
}
```

---

## 測試紀錄

- 健康檢查: 通過
- 檢查商品狀態: 通過
- 新增商品: 通過
- 更新商品: 通過
- 刪除商品: 通過
- 取得編號: 通過
- 批次新增: 通過
- 批次更新: 通過
- 清空資料表: 通過

---

## 需要你填寫或確認的資料

1. API Key (不同權限的 Key)
2. 正式/測試環境 Base URL
3. 是否需要 JWT 認證 (若需要需補登入 API)
4. 是否提供 OpenAPI 或 Postman Collection

---

**文件結束**
