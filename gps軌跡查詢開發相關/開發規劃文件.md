# GPS 軌跡查詢系統 - 開發規劃文件

## 一、專案概述

### 1.1 系統目標
建立一個高效能的 GPS 軌跡查詢系統，提供以下核心功能：
- EXCEL 檔案上傳與解析
- GPS 軌跡資料儲存與管理
- 地圖軌跡視覺化查詢

### 1.2 技術架構

```
┌─────────────────────────────────────────────────────────┐
│                      Frontend                            │
│                     (Vue.js)                             │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────┐  │
│  │  上傳頁面   │  │  查詢頁面   │  │  Google Map     │  │
│  └─────────────┘  └─────────────┘  └─────────────────┘  │
└───────────────────────────┬─────────────────────────────┘
                            │ HTTP API
┌───────────────────────────▼─────────────────────────────┐
│                      Backend                             │
│                     (Laravel)                            │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────┐  │
│  │ EXCEL解析   │  │  API控制器  │  │  資料驗證       │  │
│  └─────────────┘  └─────────────┘  └─────────────────┘  │
└───────────────────────────┬─────────────────────────────┘
                            │
┌───────────────────────────▼─────────────────────────────┐
│                     Database                             │
│                      (MySQL)                             │
└─────────────────────────────────────────────────────────┘
```

---

## 二、資料庫設計

### 2.1 資料表結構

#### gps_records 資料表

| 欄位名稱 | 資料型態 | 說明 | 備註 |
|---------|---------|------|------|
| id | BIGINT UNSIGNED | 主鍵 | AUTO_INCREMENT |
| license_plate | VARCHAR(20) | 車牌號碼 | 例：TDA-6100 |
| driver_name | VARCHAR(50) | 駕駛人姓名 | 例：高義程 |
| fleet_number | VARCHAR(20) | 車隊編號 | 例：8827 |
| date | DATE | 定位日期 | 從定位時間拆分 |
| time | TIME | 定位時間 | 從定位時間拆分 |
| location | VARCHAR(255) | 位置地址 | |
| longitude | DECIMAL(10,6) | 經度 | |
| latitude | DECIMAL(10,6) | 緯度 | |
| created_at | TIMESTAMP | 建立時間 | |
| updated_at | TIMESTAMP | 更新時間 | |

### 2.2 索引設計

| 索引名稱 | 欄位 | 用途 |
|---------|------|------|
| idx_license_date | license_plate, date | 加速按車牌與日期查詢 |
| idx_date | date | 加速按日期範圍查詢 |

### 2.3 Laravel Migration 範例

```php
Schema::create('gps_records', function (Blueprint $table) {
    $table->id();
    $table->string('license_plate', 20);
    $table->string('driver_name', 50);
    $table->string('fleet_number', 20);
    $table->date('date');
    $table->time('time');
    $table->string('location', 255);
    $table->decimal('longitude', 10, 6);
    $table->decimal('latitude', 10, 6);
    $table->timestamps();

    $table->index(['license_plate', 'date']);
    $table->index('date');
});
```

---

## 三、後端開發規劃 (Laravel)

### 3.1 API 端點設計

| 方法 | 端點 | 說明 |
|-----|------|------|
| POST | /api/upload | 上傳並解析 EXCEL 檔案 |
| GET | /api/tracks | 查詢 GPS 軌跡資料 |
| GET | /api/vehicles | 取得車輛列表 |

### 3.2 EXCEL 解析邏輯

1. **讀取規則**
   - 從第一個工作表開始依序讀取
   - 每個工作表代表一天的資料

2. **資料擷取**
   - 第二行：解析車牌號碼、駕駛人姓名、車隊編號
   - 第四行起：讀取定位時間、位置、經度、緯度

3. **資料轉換**
   - 定位時間拆分為 date 和 time 欄位
   - 驗證經緯度格式

### 3.3 建議使用套件

- `maatwebsite/excel` - EXCEL 檔案解析

---

## 四、前端開發規劃 (Vue.js)

### 4.1 頁面結構

```
src/
├── views/
│   ├── UploadPage.vue      # 上傳頁面
│   └── TrackQueryPage.vue  # 軌跡查詢頁面
├── components/
│   ├── FileUploader.vue    # 檔案上傳元件
│   ├── QueryForm.vue       # 查詢表單元件
│   └── TrackMap.vue        # 地圖顯示元件
└── services/
    └── api.js              # API 呼叫服務
```

### 4.2 元件說明

#### FileUploader.vue
- 拖放上傳 EXCEL 檔案
- 顯示上傳進度
- 上傳結果回饋

#### QueryForm.vue
- 車牌號碼選擇/輸入
- 日期範圍選擇
- 查詢按鈕

#### TrackMap.vue
- Google Map 嵌入
- 軌跡路線繪製（橘色線條）
- 起點/終點標記

### 4.3 Google Map 整合

```javascript
// 軌跡繪製設定
const trackPath = new google.maps.Polyline({
    path: coordinates,  // 經緯度陣列
    strokeColor: '#FF8C00',  // 橘色
    strokeOpacity: 1.0,
    strokeWeight: 3
});
```

---

## 五、開發階段

### Phase 1：環境建置與資料庫設計
- 建立 Laravel 專案
- 設定 MySQL 資料庫連線
- 建立資料表 Migration
- 建立 Vue.js 前端專案

### Phase 2：後端 EXCEL 解析與 API 開發
- 安裝並設定 maatwebsite/excel
- 實作 EXCEL 解析邏輯
- 開發上傳 API
- 開發查詢 API
- 開發車輛列表 API

### Phase 3：前端介面與地圖整合
- 實作檔案上傳頁面
- 實作查詢表單頁面
- 整合 Google Map API
- 實作軌跡繪製功能

### Phase 4：測試與優化
- 功能測試
- 效能優化（大量資料處理）
- 錯誤處理完善

---

## 六、注意事項

1. **EXCEL 解析**
   - 需處理多工作表情況
   - 注意第二行資料格式解析（正則表達式提取）

2. **資料量考量**
   - 建議批次寫入資料庫
   - 考慮分頁查詢

3. **Google Map API**
   - 需申請 API Key
   - 注意 API 使用量限制
