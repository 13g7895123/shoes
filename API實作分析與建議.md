# 鞋子商品管理系統 API 實作分析與建議

## 文件資訊

| 項目 | 內容 |
|------|------|
| **建立日期** | 2026-02-08 |
| **分析目的** | 比對當前資料庫結構與 API 需求文件，提供實作建議 |
| **狀態** | 待決策 |

---

## 一、當前資料表結構

### 1.1 實際資料表欄位 (shoes_show_inf)

```sql
Field        Type                              Null  Key  Default              Extra
------------------------------------------------------------------------------------------
id           int                               NO    PRI  NULL                 auto_increment
images       varchar(255)                      YES       NULL
eng_name     varchar(255)                      YES       NULL
code         varchar(100)                      YES  MUL  NULL
hope_price   decimal(10,2)                     YES       NULL
price        decimal(10,2)                     YES       NULL
point        int                               YES       NULL
size         varchar(50)                       YES       NULL
action       enum('新增','更新','刪除')          YES  MUL  '新增'
created_at   timestamp                         YES       CURRENT_TIMESTAMP    DEFAULT_GENERATED
updated_at   timestamp                         YES       CURRENT_TIMESTAMP    on update CURRENT_TIMESTAMP
```

---

## 二、API 需求文件要求的欄位

### 2.1 需求文件中的欄位定義

| 欄位名稱 | 類型 | 長度 | 必填 | 說明 |
|---------|------|------|------|------|
| `id` | Integer | - | 否 | 系統自動生成 |
| `name` | String | 200 | 是 | 商品名稱（日文）⚠️ |
| `eng_name` | String | 200 | 是 | 商品名稱（英文） |
| `code` | String | 50 | 是 | 商品編號（唯一值） |
| `hope_price` | String | 20 | 否 | 希望售價 |
| `price` | String | 20 | 是 | 實際售價 |
| `point` | String | 20 | 否 | 紅利點數 |
| `size` | String | 500 | 否 | 可用尺寸（逗號分隔） |
| `created_at` | DateTime | - | 否 | 建立時間 |
| `updated_at` | DateTime | - | 否 | 更新時間 |

---

## 三、欄位差異分析

### 3.1 嚴重問題（必須處理）

#### ❌ 問題 1：缺少 `name` 欄位
- **現況**：資料表中沒有 `name` 欄位
- **需求**：API 需求文件要求此欄位為必填
- **影響**：無法完整實作新增商品 API

**解決方案選項：**

**方案 A：新增 name 欄位（建議）**
```sql
ALTER TABLE shoes_show_inf 
ADD COLUMN name VARCHAR(200) NULL AFTER images;
```
- ✅ 優點：完全符合 API 需求文件
- ✅ 優點：未來擴充性好
- ⚠️ 缺點：需要修改資料庫結構

**方案 B：將 name 設為選填**
- 修改 API 規格，將 `name` 改為非必填
- ✅ 優點：不需修改資料庫
- ❌ 缺點：不符合原始需求文件

**方案 C：使用 eng_name 代替**
- API 接收 name 參數，但實際不儲存
- ❌ 缺點：資料遺失，不推薦

**👍 建議採用：方案 A（新增欄位）**

---

### 3.2 資料型別差異（需要決策）

#### ⚠️ 問題 2：價格欄位型別不一致

| 欄位 | 當前型別 | 需求型別 | 差異說明 |
|------|---------|---------|---------|
| `hope_price` | `decimal(10,2)` | `varchar(20)` | 數值型 vs 字串型 |
| `price` | `decimal(10,2)` | `varchar(20)` | 數值型 vs 字串型 |
| `point` | `int` | `varchar(20)` | 整數型 vs 字串型 |

**影響分析：**
- API 接收字串格式的價格（如 "5000"）
- 資料庫儲存為數值格式（5000.00）
- 需要在應用層進行型別轉換

**解決方案選項：**

**方案 A：維持當前資料庫設計（建議）**
- 資料庫保持數值型別
- API 層面接收字串，自動轉換為數值儲存
- 取出時再轉回字串格式回傳

優點：
- ✅ 資料完整性：數值型別可以驗證資料正確性
- ✅ 查詢效能：數值型別支援範圍查詢（價格篩選）
- ✅ 精確計算：避免字串運算誤差
- ✅ 資料庫最佳實踐

缺點：
- ⚠️ 需要在 API 層處理型別轉換

**方案 B：修改為字串型別**
```sql
ALTER TABLE shoes_show_inf 
MODIFY COLUMN hope_price VARCHAR(20),
MODIFY COLUMN price VARCHAR(20),
MODIFY COLUMN point VARCHAR(20);
```

優點：
- ✅ 完全符合 API 規格

缺點：
- ❌ 無法保證資料正確性（可能儲存 "abc"）
- ❌ 查詢效能較差
- ❌ 無法進行數值運算
- ❌ 不符合資料庫設計最佳實踐

**👍 建議採用：方案 A（維持數值型別，API 層轉換）**

---

#### ⚠️ 問題 3：欄位長度差異

| 欄位 | 當前長度 | 需求長度 | 建議 |
|------|---------|---------|------|
| `code` | varchar(100) | varchar(50) | 保持 100（容錯空間） |
| `size` | varchar(50) | varchar(500) | **需要擴充** |
| `eng_name` | varchar(255) | varchar(200) | 保持 255（容錯空間） |

**size 欄位問題：**
- 當前：varchar(50) - 只能存約 10 個尺寸
- 需求：varchar(500) - 可存更多尺寸組合
- 範例：`"25.0, 25.5, 26.0, 26.5, 27.0, 27.5, 28.0, 28.5, 29.0, 29.5, 30.0"` = 65 字元

**解決方案：**
```sql
ALTER TABLE shoes_show_inf 
MODIFY COLUMN size VARCHAR(500);
```

**👍 建議：擴充 size 欄位至 varchar(500)**

---

### 3.3 額外欄位（當前有，需求無）

#### 📌 問題 4：images 欄位

- **現況**：資料表有 `images` 欄位
- **需求**：API 需求文件未提及
- **建議**：保留欄位，但不在 API 中強制要求

#### 📌 問題 5：action 欄位

- **現況**：資料表有 `action` 欄位（enum 類型）
- **需求**：API 需求文件未提及
- **用途**：標記資料操作類型（新增/更新/刪除）
- **建議**：保留欄位，供內部系統使用，API 可選填

---

## 四、建議的資料庫調整方案

### 4.1 推薦的 SQL 修改語句

```sql
-- 1. 新增 name 欄位（日文名稱）
ALTER TABLE shoes_show_inf 
ADD COLUMN name VARCHAR(200) NULL AFTER images;

-- 2. 擴充 size 欄位長度
ALTER TABLE shoes_show_inf 
MODIFY COLUMN size VARCHAR(500);

-- 3. 為 code 欄位建立唯一索引（確保商品編號唯一）
ALTER TABLE shoes_show_inf 
ADD UNIQUE KEY uk_code (code);

-- 4. 建議：為常用查詢欄位加索引
ALTER TABLE shoes_show_inf 
ADD INDEX idx_eng_name (eng_name),
ADD INDEX idx_created_at (created_at);
```

### 4.2 調整後的完整表結構

```sql
CREATE TABLE `shoes_show_inf` (
  `id` int NOT NULL AUTO_INCREMENT,
  `images` varchar(255) DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,                    -- ✅ 新增
  `eng_name` varchar(255) DEFAULT NULL,
  `code` varchar(100) NOT NULL,                        -- ✅ 建議改為 NOT NULL
  `hope_price` decimal(10,2) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,                      -- ✅ 建議改為 NOT NULL
  `point` int DEFAULT NULL,
  `size` varchar(500) DEFAULT NULL,                    -- ✅ 已擴充
  `action` enum('新增','更新','刪除') DEFAULT '新增',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_code` (`code`),                       -- ✅ 新增唯一索引
  KEY `idx_action` (`action`),
  KEY `idx_eng_name` (`eng_name`),                     -- ✅ 新增索引
  KEY `idx_created_at` (`created_at`)                  -- ✅ 新增索引
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 五、API 實作建議

### 5.1 型別轉換處理

在 API Controller 中處理資料型別轉換：

```php
// API 接收字串格式
$inputData = [
    'name' => 'ナイキ エアマックス',          // 新增
    'eng_name' => 'Nike Air Max',
    'code' => 'ABC123',
    'price' => '5000',                       // 字串
    'hope_price' => '8000',                  // 字串
    'point' => '500',                        // 字串
    'size' => '27.0, 28.0, 29.0'
];

// 轉換為資料庫格式
$dbData = [
    'name' => $inputData['name'],
    'eng_name' => $inputData['eng_name'],
    'code' => $inputData['code'],
    'price' => floatval($inputData['price']),           // 轉數值
    'hope_price' => floatval($inputData['hope_price']), // 轉數值
    'point' => intval($inputData['point']),             // 轉整數
    'size' => $inputData['size']
];

// 儲存
$model->insert($dbData);

// 從資料庫取出後，轉回字串格式回傳
$result = $model->find($id);
$apiResponse = [
    'name' => $result['name'],
    'eng_name' => $result['eng_name'],
    'code' => $result['code'],
    'price' => strval($result['price']),           // 轉字串
    'hope_price' => strval($result['hope_price']), // 轉字串
    'point' => strval($result['point']),           // 轉字串
    'size' => $result['size']
];
```

### 5.2 驗證規則調整

```php
protected $validationRules = [
    'name'        => 'required|max_length[200]',           // 新增必填
    'eng_name'    => 'required|max_length[255]',
    'code'        => 'required|max_length[100]|is_unique[shoes_show_inf.code,id,{id}]',
    'hope_price'  => 'permit_empty|numeric|max_length[20]',  // 數值驗證
    'price'       => 'required|numeric|max_length[20]',       // 數值驗證
    'point'       => 'permit_empty|integer|max_length[20]',   // 整數驗證
    'size'        => 'permit_empty|max_length[500]',          // 擴充長度
];
```

---

## 六、API 端點實作狀態

### 6.1 已實作的 API 端點

✅ 表示已完成，⚠️ 表示需要調整

| 端點 | 方法 | 狀態 | 說明 |
|-----|------|------|------|
| `/api/health/database` | GET | ✅ | 資料庫健康檢查 |
| `/api/shoes/check-status` | POST | ⚠️ | 需加入 name 欄位處理 |
| `/api/shoes` | POST | ⚠️ | 需加入 name 欄位、型別轉換 |
| `/api/shoes/{code}` | PUT | ✅ | 更新價格與尺寸 |
| `/api/shoes/{code}` | DELETE | ✅ | 刪除商品 |
| `/api/shoes/codes` | GET | ✅ | 取得所有商品編號 |
| `/api/shoes/clear/{table}` | DELETE | ✅ | 清空資料表 |
| `/api/shoes/batch` | POST | ⚠️ | 批次新增（需調整） |
| `/api/shoes/batch` | PUT | ✅ | 批次更新 |

### 6.2 需要調整的地方

**checkStatus API：**
```php
// 需要加入 name 欄位檢查
if (empty($json['name'])) {
    return $this->fail([
        'status' => 'error',
        'message' => '商品名稱不可為空',
        'error_code' => 'MISSING_REQUIRED_FIELD',
        'field' => 'name'
    ], 400);
}
```

**create API：**
```php
// 更新必填欄位驗證
$required = ['name', 'eng_name', 'code', 'price'];

// 加入型別轉換
$data = [
    'name' => $json['name'],                          // 新增
    'eng_name' => $json['eng_name'],
    'code' => $json['code'],
    'price' => floatval($json['price']),              // 轉換
    'hope_price' => isset($json['hope_price']) ? floatval($json['hope_price']) : null,
    'point' => isset($json['point']) ? intval($json['point']) : null,
    'size' => $json['size'] ?? null,
];
```

---

## 七、完整實作步驟建議

### 步驟 1：資料庫調整（必須）

```bash
# 連接到 MySQL 容器
docker exec -it bonus_shoes_mysql mysql -u bonus_user -pbonus_password bonus_shoes

# 執行以下 SQL
ALTER TABLE shoes_show_inf ADD COLUMN name VARCHAR(200) NULL AFTER images;
ALTER TABLE shoes_show_inf MODIFY COLUMN size VARCHAR(500);
ALTER TABLE shoes_show_inf ADD UNIQUE KEY uk_code (code);
```

### 步驟 2：更新 Migration 檔案

創建新的 migration 檔案記錄變更：

```php
// app/Database/Migrations/2026-02-08-101000_UpdateShoesTableForApi.php
public function up()
{
    $this->forge->addColumn('shoes_show_inf', [
        'name' => [
            'type' => 'VARCHAR',
            'constraint' => '200',
            'null' => true,
            'after' => 'images',
        ],
    ]);
    
    $this->forge->modifyColumn('shoes_show_inf', [
        'size' => [
            'type' => 'VARCHAR',
            'constraint' => '500',
            'null' => true,
        ],
    ]);
    
    $this->db->query('ALTER TABLE shoes_show_inf ADD UNIQUE KEY uk_code (code)');
}
```

### 步驟 3：更新 Model（已完成）

✅ ShoesModel.php 已調整完成，符合當前資料表結構

### 步驟 4：更新 API Controller

需要修改：
- `ShoesApiController::checkStatus()` - 加入 name 欄位
- `ShoesApiController::create()` - 加入 name 欄位 + 型別轉換
- `ShoesApiController::batchCreate()` - 加入 name 欄位 + 型別轉換
- 所有查詢回傳 - 加入型別轉換（數值轉字串）

### 步驟 5：更新認證過濾器（已完成）

✅ ApiKeyAuth.php 已創建完成

### 步驟 6：更新路由設定（已完成）

✅ Routes.php 已配置完成

### 步驟 7：測試所有 API 端點

使用 Postman 或 curl 測試每個端點

---

## 八、測試檢查清單

### 8.1 資料庫測試

- [ ] 確認 name 欄位已新增
- [ ] 確認 size 欄位長度為 500
- [ ] 確認 code 欄位有唯一索引
- [ ] 測試插入含 name 的資料
- [ ] 測試插入長尺寸字串（超過 50 字元）

### 8.2 API 功能測試

- [ ] 健康檢查 API
- [ ] 新增商品（含 name 欄位）
- [ ] 檢查商品狀態
- [ ] 更新商品（價格、尺寸）
- [ ] 刪除商品
- [ ] 取得商品編號清單
- [ ] 批次新增商品
- [ ] 批次更新商品
- [ ] 清空資料表

### 8.3 認證測試

- [ ] 無 API Key 訪問（應回傳 401）
- [ ] 錯誤的 API Key（應回傳 401）
- [ ] READ 權限測試
- [ ] WRITE 權限測試
- [ ] DELETE 權限測試
- [ ] ADMIN 權限測試

### 8.4 錯誤處理測試

- [ ] 缺少必填欄位
- [ ] 重複的商品編號
- [ ] 無效的資料格式
- [ ] 商品不存在
- [ ] 資料庫連線失敗

---

## 九、風險評估

### 9.1 高風險項目

| 風險項目 | 風險等級 | 影響 | 緩解措施 |
|---------|---------|------|---------|
| 新增 name 欄位後現有資料為 NULL | 🔴 高 | 查詢可能失敗 | 設為可選填，逐步填充資料 |
| 型別轉換錯誤 | 🟡 中 | API 回傳錯誤資料 | 嚴格驗證，錯誤處理 |
| 唯一索引衝突 | 🟡 中 | 現有重複資料無法新增索引 | 先清理重複資料 |

### 9.2 相容性考量

**向後相容性：**
- ✅ name 欄位設為可選填，不影響舊資料
- ✅ API 回傳格式保持一致
- ⚠️ 新增的必填欄位可能影響舊客戶端

**資料遷移：**
- 建議：為現有資料的 name 欄位填充預設值
- 方案：`UPDATE shoes_show_inf SET name = eng_name WHERE name IS NULL;`

---

## 十、總結與建議

### 10.1 必須執行的調整

1. ✅ **新增 name 欄位**（資料庫）
2. ✅ **擴充 size 欄位至 500**（資料庫）
3. ⚠️ **加入 code 唯一索引**（需先檢查重複資料）
4. ⚠️ **更新 API Controller 支援 name 欄位**
5. ⚠️ **實作型別轉換邏輯**

### 10.2 建議執行的優化

1. 📌 為常用查詢欄位加索引
2. 📌 實作 API 版本控制
3. 📌 加入請求日誌記錄
4. 📌 實作限流機制
5. 📌 加入快取機制

### 10.3 可選的改進

1. 💡 實作 JWT 認證
2. 💡 加入 API 文檔（Swagger）
3. 💡 實作資料驗證中介層
4. 💡 加入單元測試
5. 💡 實作 API 監控

---

## 十一、下一步行動

### 立即執行

1. **決策確認**
   - [ ] 確認是否新增 name 欄位
   - [ ] 確認是否維持數值型別（價格、點數）
   - [ ] 確認是否擴充 size 欄位

2. **資料庫調整**
   - [ ] 執行 SQL 語句
   - [ ] 驗證表結構
   - [ ] 備份資料

3. **程式碼更新**
   - [ ] 更新 Model（如果需要）
   - [ ] 更新 API Controller
   - [ ] 更新驗證規則

4. **測試驗證**
   - [ ] 執行完整測試
   - [ ] 修正發現的問題
   - [ ] 文檔化測試結果

---

## 附錄

### A. API 測試範例

```bash
# 1. 健康檢查
curl -X GET http://localhost:8080/api/health/database

# 2. 新增商品（含 name 欄位）
curl -X POST http://localhost:8080/api/shoes \
  -H "Content-Type: application/json" \
  -H "X-API-Key: dev_key_123456" \
  -d '{
    "name": "ナイキ エアマックス",
    "eng_name": "Nike Air Max",
    "code": "TEST001",
    "price": "5000",
    "hope_price": "8000",
    "point": "500",
    "size": "27.0, 28.0, 29.0"
  }'

# 3. 檢查商品狀態
curl -X POST http://localhost:8080/api/shoes/check-status \
  -H "Content-Type: application/json" \
  -H "X-API-Key: dev_key_123456" \
  -d '{
    "code": "TEST001",
    "price": "5000",
    "size": "27.0, 28.0, 29.0"
  }'
```

### B. 相關文件連結

- API需求文件.md - 完整 API 規格
- README.md - 專案說明
- DOCKER_GUIDE.md - Docker 使用指南

---

**文件結束**

如有任何問題或需要進一步說明，請聯繫開發團隊。
