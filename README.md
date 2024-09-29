# 資料表設計

## 說明：
好的，我會根據您提供的表格列表，添加一些假設情境並提供相關的資料庫設計建議。以下是基於假設情境的設計考量：

1. 假設情境：
    - 一個國家可以有多個網站和倉儲
    - 一個產品可以屬於多個類別，一個類別可以包含多個產品
    - 一個產品可以有多種顏色和型號組合
    - 一個產品可以有多個圖檔
    - 特殊價格可能根據不同的國家網站、時間段或客戶群體而有所不同
    - 庫存是按照倉儲、產品、顏色和型號來追蹤的
    - 多語言支援是必須的，幾乎所有的文本內容都需要翻譯

2. 正規化考量：
    - 使用第三正規化（3NF）作為基礎，但在某些情況下可能需要適度反正規化以提高查詢效率
    - 產品、顏色和型號的關係應該使用關聯表來處理，避免在產品表中重複存儲這些信息

3. 欄位設計：
    - 使用 GUID 或 UUID 作為主鍵，特別是對於可能需要跨數據庫或系統同步的表格
    - 對於幣值欄位，使用 DECIMAL 類型而不是 FLOAT 或 DOUBLE，以確保精確計算
    - 對於狀態欄位，使用 ENUM 類型來限制可能的值
    - 在每個表格中添加 CreatedAt, UpdatedAt, CreatedBy, UpdatedBy 欄位以追蹤記錄的變更
    - 資料不刪除，透過IsActive來區分是否啟用

4. 索引設計：
    - 為所有外鍵添加索引
    - 對於經常用於搜尋的欄位添加索引，如產品名稱、SKU、顏色代碼等
    - 考慮使用複合索引來優化多欄位查詢，如 (ProductId, ColorId, ModelId)
    - 對於全文搜索需求，考慮使用全文索引或搜索引擎如 Elasticsearch

5. 關聯設計：
    - Product 和 Category 之間使用多對多關聯
    - Product 和 Color、Model 之間使用多對多關聯
    - Product 和 Image 之間使用一對多關聯
    - Store 和 Warehouse 之間使用多對多關聯
    - Inventory 關聯到 Product、Store

6. 特殊考量：
    - 對於 I18nContent 表，考慮到多語系建立此表，這樣可以更靈活地處理不同語言
    - 對於 SpecialPrice，考慮使用範圍類型來存儲有效期，便於查詢特定時間點的價格
    - 庫存變動應該通過觸發器自動記錄到 InventoryLog 表中

### 國家網站(Store)

    CREATE TABLE CountryWebsite (
        StoreId CHAR(36) COMMENT 'ID',
        CountryCode CHAR(3) NOT NULL COMMENT '國家代碼',
        Country VARCHAR(100) NOT NULL COMMENT '國家名稱',
        WebsiteCode VARCHAR(10) NOT NULL COMMENT '網站代碼',
        Website VARCHAR(255) NOT NULL COMMENT '網站名稱',
        WarehouseId VARCHAR(10) NOT NULL COMMENT '倉儲',
        Currency CHAR(3) NOT NULL COMMENT '幣別',
        IsActive BOOLEAN NOT NULL COMMENT '是否啟用',
        Remarks TEXT COMMENT '備註',
        CreatedBy VARCHAR(10) NOT NULL COMMENT '建立者',
        CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '建立時間',
        UpdatedBy VARCHAR(10) NOT NULL COMMENT '異動者',
        UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '異動時間',
        INDEX(CountryCode, WebsiteCode),
        UNIQUE INDEX (CountryCode, WebsiteCode)
    ) COMMENT='國家網站';

### 倉儲(Warehouse)

    CREATE TABLE Warehouse (
        WarehouseId CHAR(36) COMMENT '倉儲ID',
        Warehouse VARCHAR(20) NOT NULL PRIMARY KEY COMMENT '倉儲',
        WarehouseName VARCHAR(255) NOT NULL COMMENT '倉儲名稱',
        StartDate DATETIME NOT NULL COMMENT '啟用起時間',
        EndDate DATETIME COMMENT '啟用訖時間',
        IsActive BOOLEAN NOT NULL DEFAULT TRUE COMMENT '是否啟用',
        Remarks TEXT COMMENT '備註',
        CreatedBy VARCHAR(10) NOT NULL COMMENT '建立者',
        CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '建立時間',
        UpdatedBy VARCHAR(10) COMMENT '異動者',
        UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '異動時間',
        UNIQUE (Warehouse) -- 確保倉儲名稱唯一
    )  COMMENT='倉儲';

### 類別(Category)

    CREATE TABLE Category (
        CategoryId CHAR(36) COMMENT '類別ID',
        CategoryGroupCode VARCHAR(10) NOT NULL COMMENT '類別群組代碼',
        CategoryGroup VARCHAR(50) NOT NULL COMMENT '類別群組',
        Category VARCHAR(100) NOT NULL COMMENT '類別',
        CategoryName VARCHAR(100) NOT NULL COMMENT '類別名稱',
        IsActive BOOLEAN NOT NULL COMMENT '是否啟用',
        Remarks TEXT COMMENT '備註',
        CreatedBy VARCHAR(10) NOT NULL COMMENT '建立者',
        CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '建立時間',
        UpdatedBy VARCHAR(10) NOT NULL COMMENT '異動者',
        UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '異動時間',
        UNIQUE INDEX (CategoryGroupCode, Category)
    ) COMMENT='類別';

### 品牌(Brand)

    CREATE TABLE Brand (
        BrandId CHAR(36) COMMENT '品牌ID',
        Brand VARCHAR(50) NOT NULL COMMENT '品牌',
        BrandName VARCHAR(50) NOT NULL COMMENT '品牌名稱',
        StoreId VARCHAR(20) NOT NULL ,
        CategoryId VARCHAR(10) NOT NULL COMMENT '類別Id',
        IsActive BOOLEAN NOT NULL COMMENT '是否啟用',
        Remarks TEXT COMMENT '備註',
        CreatedBy VARCHAR(10) NOT NULL COMMENT '建立者',
        CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '建立時間',
        UpdatedBy VARCHAR(10) NOT NULL COMMENT '異動者',
        UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '異動時間',
        INDEX(Brand, StoreId),
        INDEX(Brand, CategoryId),
        INDEX(Brand, StoreId, CategoryId),
        UNIQUE INDEX (Brand, StoreId, CategoryId)
    ) COMMENT='品牌';

### 顏色(Colors)

    CREATE TABLE Colors (
        ColorId CHAR(36) PRIMARY KEY COMMENT '顏色ID',
        Type VARCHAR(30) NULL COMMENT '類別',
        ColorGroupCode VARCHAR(30) NULL COMMENT '顏色群組代碼',
        ColorGroup VARCHAR(30) NULL COMMENT '顏色群組',
        Color VARCHAR(30) NOT NULL UNIQUE COMMENT '顏色',
        ColorName VARCHAR(30) NOT NULL UNIQUE COMMENT '顏色名稱',
        IsActive BOOLEAN NOT NULL DEFAULT TRUE COMMENT '是否啟用',
        Remarks TEXT COMMENT '備註',
        ModifiedTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '異動時間',
        INDEX(Color),
        INDEX(ColorGroupCode, Color),
        INDEX(Type, Color),
        UNIQUE INDEX (Type, ColorGroupCode, Color)
    )  COMMENT='顏色';

### 型號(Models)

    CREATE TABLE Models (
        ModelID CHAR(36) COMMENT '型號ID',
        ModelCategoryCode VARCHAR(30) NOT NULL COMMENT '型號類別代碼',
        ModelCategory VARCHAR(30) NOT NULL COMMENT '型號類別',
        ModelGroupCode VARCHAR(30) NOT NULL COMMENT '型號群組代碼',
        ModelGroup VARCHAR(30) NOT NULL COMMENT '型號群組',
        Model VARCHAR(50) NOT NULL UNIQUE COMMENT '型號',
        ModelName VARCHAR(50) NOT NULL UNIQUE COMMENT '型號名稱',
        ColorId VARCHAR(10) NOT NULL COMMENT '顏色',
        shelvesStartDate DATETIME NOT NULL COMMENT '上架起時間',
        shelvesEndDate DATETIME COMMENT '上架訖時間',
        DisplayStartDate DATE COMMENT '顯示起時間',
        DisplayEndDate DATE COMMENT '顯示訖時間',
        IsDisplayed BOOLEAN NOT NULL DEFAULT TRUE COMMENT '是否顯示',
        IsActive BOOLEAN NOT NULL DEFAULT TRUE COMMENT '是否啟用',
        Remarks TEXT COMMENT '備註',
        CreatedBy VARCHAR(10) NOT NULL COMMENT '建立者',
        CreatedTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '建立時間',
        ModifiedBy VARCHAR(10) COMMENT '異動者',
        ModifiedTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '異動時間',
        INDEX(ModelCategoryCode),
        INDEX(ModelGroupCode),
        INDEX(ModelCategoryCode, ModelGroupCode, Model),
        UNIQUE INDEX(ModelCategoryCode, ModelGroupCode, Model,ColorId)
    )  COMMENT='型號';

### 產品類型(ProductType)

    CREATE TABLE ProductType (
        ProductTypeId CHAR(36)  COMMENT '產品類型Id',
        ProductTypeGroupCode VARCHAR(30) NOT NULL COMMENT '產品類型群組代碼',
        ProductTypeGroup VARCHAR(30) NOT NULL COMMENT '產品類型群組',
        ProductType VARCHAR(50) NOT NULL COMMENT '產品類型',
        ProductTypeName VARCHAR(255) NOT NULL COMMENT '產品類型名稱',
        Icon VARCHAR(500) COMMENT '圖檔Icon',
        ActiveStartDate DATETIME NOT NULL COMMENT '啟用起時間',
        ActiveEndDate DATETIME COMMENT '啟用訖時間',
        IsActive BOOLEAN NOT NULL DEFAULT TRUE COMMENT '是否啟用',
        Remarks TEXT COMMENT '備註',
        CreatedBy VARCHAR(10) NOT NULL COMMENT '建立者',
        CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '建立時間',
        UpdatedBy VARCHAR(10) COMMENT '異動者',
        UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '異動時間',
        INDEX(ProductTypeGroupCode),
        INDEX(ProductType),
        INDEX(ProductTypeGroupCode, ProductType),
        UNIQUE INDEX(ProductTypeGroupCode, ProductType)
    )  COMMENT='產品類型';

### 產品內容介紹(ProductContent)

    CREATE TABLE ProductContent (
        ProductContentId CHAR(36) PRIMARY KEY COMMENT '產品內容Id',
        Content VARCHAR(255) NOT NULL COMMENT '產品內容',
        StartDate DATETIME NOT NULL COMMENT '啟用起時間',
        EndDate DATETIME COMMENT '啟用訖時間',
        IsActive BOOLEAN NOT NULL DEFAULT TRUE COMMENT '是否啟用',
        Remarks TEXT COMMENT '備註',
        CreatedBy VARCHAR(10) NOT NULL COMMENT '建立者',
        CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '建立時間',
        UpdatedBy VARCHAR(10) COMMENT '異動者',
        UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '異動時間'
    )  COMMENT='產品內容介紹';

### 折扣碼(CouponCode)

    CREATE TABLE CouponCode (
    CouponCodeId CHAR(36) COMMENT '折扣碼Id',
    CouponCode VARCHAR(20) NOT NULL PRIMARY KEY COMMENT '折扣代碼',
    CouponName VARCHAR(255) NOT NULL COMMENT '折扣名稱',
    StartDate DATETIME NOT NULL COMMENT '啟用起時間',
    EndDate DATETIME COMMENT '啟用訖時間',
    IsActive BOOLEAN NOT NULL DEFAULT TRUE COMMENT '是否啟用',
    Remarks TEXT COMMENT '備註',
    CreatedBy VARCHAR(10) NOT NULL COMMENT '建立者',
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '建立時間',
    UpdatedBy VARCHAR(10) COMMENT '異動者',
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '異動時間',
    )  COMMENT='折扣碼';

### 產品(Product)

    CREATE TABLE Product (
        ProductId CHAR(36) COMMENT '產品Id',
        BrandId INT NOT NULL COMMENT '品牌Id',
        CategoryId INT NOT NULL COMMENT '類別Id',
        ProductTypeId INT NOT NULL COMMENT '產品類型Id',
        ProductCategory ENUM('single', 'config', 'bundle') NOT NULL COMMENT '產品類別',
        ProductName VARCHAR(255) NOT NULL COMMENT '產品名稱',
        ProductSKU VARCHAR(20) NOT NULL PRIMARY KEY  COMMENT '產品SKU',
        StoreId INT NOT NULL COMMENT '商店Id',
        Gins VARCHAR(20) COMMENT 'UPC/EAN碼',
        Price DECIMAL(10, 2) NOT NULL COMMENT '價錢',
        SpecialPriceId INT COMMENT '特殊價錢Id',
        ApplicableContentIds TEXT COMMENT '適用的產品內容Id',
        ApplicableCouponCodeIds TEXT COMMENT '適用的折扣碼Id',
        ProductImageIds TEXT COMMENT '產品圖片Id',
        IsActive BOOLEAN NOT NULL DEFAULT TRUE COMMENT '是否啟用',
        Remarks TEXT COMMENT '備註',
        CreatedBy VARCHAR(10) NOT NULL COMMENT '建立者',
        CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '建立時間',
        UpdatedBy VARCHAR(10) COMMENT '異動者',
        UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '異動時間'
    )  COMMENT='產品';

### 圖檔(Image)

    CREATE TABLE Image (
        ImageId CHAR(36) PRIMARY KEY COMMENT '圖檔Id',
        ImageName VARCHAR(255) NOT NULL COMMENT '圖檔名稱',
        ImagePath VARCHAR(255) NOT NULL COMMENT '圖檔路徑',
        Remarks TEXT COMMENT '備註',
        CreatedBy VARCHAR(10) NOT NULL COMMENT '建立者',
        CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '建立時間',
        UpdatedBy VARCHAR(10) COMMENT '異動者',
        UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '異動時間'
    ) COMMENT='圖檔';

### 特殊價格(SpecialPrice)

    CREATE TABLE SpecialPrice (
        SpecialPriceId CHAR(36) PRIMARY KEY COMMENT '特殊價格Id',
        Price DECIMAL(10, 2) NOT NULL COMMENT '價格',
        StartDate DATETIME NOT NULL COMMENT '生效起時間',
        EndDate DATETIME COMMENT '生效迄時間',
        IsActive BOOLEAN NOT NULL DEFAULT TRUE COMMENT '是否啟用',
        Remarks TEXT COMMENT '備註',
        IsCouponApplicable BOOLEAN NOT NULL DEFAULT TRUE COMMENT '是否可使用折扣碼',
        CreatedBy VARCHAR(10) NOT NULL COMMENT '建立者',
        CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '建立時間',
        UpdatedBy VARCHAR(10) COMMENT '異動者',
        UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '異動時間'
    )  COMMENT='特殊價格表';

### 庫存(Inventory)

    CREATE TABLE Inventory (
        InventoryID CHAR(36) COMMENT '庫存Id',
        ProductSKU VARCHAR(50) NOT NULL COMMENT '產品的唯一識別碼',
        StoreID INT NOT NULL COMMENT '店鋪的唯一識別碼',
        ActualStock INT NOT NULL COMMENT '實際存在的庫存數量',
        AvailableStock INT NOT NULL COMMENT '可供銷售的庫存數量',
        SAPReconciledStock INT DEFAULT 0 COMMENT '記錄 SAP 系統沖銷後的庫存數量',
        ModifiedBy VARCHAR(50) NOT NULL COMMENT '異動者',
        ModifiedTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '異動時間',
        CreatedTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '建立時間',
        UNIQUE INDEX (ProductSKU, StoreID),
        INDEX (StoreID)
        INDEX (ProductSKU, StoreID)
    ) COMMENT='庫存';

### 庫存Log(InventoryLog)

    CREATE TABLE InventoryLog (
        LogID CHAR(36) PRIMARY KEY COMMENT '庫存Log Id',
        InventoryID INT NOT NULL COMMENT ' InventoryID',
        ChangeType VARCHAR(50) NOT NULL COMMENT '異動類型',
        BeforeChangeQuantity INT NOT NULL COMMENT '異動前的數量',
        AfterChangeQuantity INT NOT NULL COMMENT '異動後的數量',
        ModifiedBy VARCHAR(50) NOT NULL COMMENT '異動者',
        ModifiedTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '異動時間',
        Remarks VARCHAR(255) COMMENT '異動備註或原因',
        INDEX (InventoryID),
        FOREIGN KEY (InventoryID) REFERENCES Inventory(InventoryID)
    ) COMMENT='庫存Log';

### I18nContent

    CREATE TABLE I18nContent (
        ContentId CHAR(36) PRIMARY KEY COMMENT 'ID',
        ReferenceId INT NOT NULL COMMENT '關聯ID',
        ReferenceType ENUM('color', 'category', 'model', 'brand', 'product_type', 'product_content', 'Coupon_code', 'product') NOT NULL COMMENT '內容類型',
        LanguageCode VARCHAR(5) NOT NULL COMMENT '語言代碼',
        Content TEXT NOT NULL COMMENT '翻譯內容',
        IsActive BOOLEAN NOT NULL DEFAULT TRUE COMMENT '是否啟用',
        CreatedBy VARCHAR(10) NOT NULL COMMENT '建立者',
        CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '建立時間',
        UpdatedBy VARCHAR(10) NOT NULL COMMENT '異動者',
        UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '異動時間',
        INDEX(ReferenceId, ReferenceType),
        INDEX(LanguageCode),
        UNIQUE INDEX(ReferenceId, ReferenceType, LanguageCode)
    ) COMMENT='多語系內容';

###### 看所有語系翻譯 Sample

    SELECT 
        i.ReferenceId,
        i.ReferenceType,
        MAX(CASE WHEN i.LanguageCode = 'en' THEN i.Content END) AS English,
        MAX(CASE WHEN i.LanguageCode = 'zh' THEN i.Content END) AS Chinese,
        MAX(CASE WHEN i.LanguageCode = 'ja' THEN i.Content END) AS Japanese,
        MAX(CASE WHEN i.LanguageCode = 'ko' THEN i.Content END) AS Korean,
        MAX(CASE WHEN i.LanguageCode = 'fr' THEN i.Content END) AS French,
        MAX(CASE WHEN i.LanguageCode = 'de' THEN i.Content END) AS German
    FROM
    I18nContent i
    WHERE
    i.ReferenceType = 'product' -- 根據需要更改這裡的類型
    GROUP BY
    i.ReferenceId, i.ReferenceType;

