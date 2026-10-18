# Implementation Plan: Total Amount & Total Cost Valuation for Stock on Hand Module

**Document ID:** PLAN-SOH-003  
**Target Module:** Stock on Hand (`App\Http\Controllers\Admin\Inventory\StockOnHandController`)  
**Status:** Implemented & Verified  
**Author:** AI Pair Programmer  
**Date:** October 18, 2026  
**Supersedes:** PLAN-SOH-001 & PLAN-SOH-002

---

## 1. Executive Summary

The objective of this implementation plan is to calculate, display, and export both **Total Cost** and **Total Amount** for each product in physical inventory across the **Stock on Hand** module:

1. **Total Cost (Inventory Investment Valuation):**
   $$\text{Total Cost} = \text{Current Stock} \times \text{Unit Cost}$$
   - **Financial Meaning:** Capital invested by the business to purchase/acquire on-hand inventory.
   - **Data Source:** Unit purchase cost from [`products.cost`](file:///Users/longdy/Documents/Projects/Laravel/POSSystem/app/Models/Product.php).
   - **Label & Key:** `total_cost` / `total_cost_title` | **Total Cost** (Khmer: `សរុបតម្លៃដើម`).

2. **Total Amount (Total Retail Sales Amount):**
   $$\text{Total Amount} = \text{Current Stock} \times \text{Unit Selling Price}$$
   - **Financial Meaning:** Total gross sales revenue that will be generated if all currently held stock is sold at retail price.
   - **Data Source:** Branch-specific retail price from [`shop_products.price`](file:///Users/longdy/Documents/Projects/Laravel/POSSystem/app/Models/ShopProduct.php) or catalog retail price from [`products.price`](file:///Users/longdy/Documents/Projects/Laravel/POSSystem/app/Models/Product.php).
   - **Label & Key:** `total_amount` / `total_amount_title` | **Total Amount** (Khmer: `សរុបទឹកប្រាក់`).

3. **Potential Gross Profit (Margin):**
   $$\text{Potential Profit} = \text{Total Amount} - \text{Total Cost}$$
   $$\text{Margin \%} = \frac{\text{Total Amount} - \text{Total Cost}}{\text{Total Amount}} \times 100\%$$

> [!IMPORTANT]
> **Zero Code Modifications Notice:** In accordance with pair-programming standards, this document is an architectural and implementation proposal. **No code, database migrations, or business logic modifications have been applied.** Execution will commence only upon explicit user approval.

---

## 2. Current System Architecture & Behavior Analysis

### 2.1 Database Schema & Relationships

```
+-------------------------------------------------------------+
|                          products                           |
+-------------------------------------------------------------+
| id             : bigint (PK)                                |
| code           : varchar(255)                               |
| name           : varchar(255)                               |
| cost           : double (Unit Purchase Cost in USD)         |
| price          : double (Master Retail Selling Price)       |
| category_id    : integer (FK -> categories.id)              |
| uom_id         : integer (FK -> uoms.id)                    |
| status         : integer (1 = active)                       |
| deleted_at     : timestamp (soft deletes)                   |
+-------------------------------------------------------------+
               ^                                   ^
               | product_id                        | product_id
+--------------+---------------+   +---------------+--------------+
|        stock_on_hands        |   |        shop_products         |
+------------------------------+   +------------------------------+
| id             : bigint (PK) |   | id          : bigint (PK)    |
| product_id     : integer     |   | shop_id     : integer        |
| shop_id        : integer     |   | product_id  : integer        |
| current_stock  : integer     |   | price       : double (retail)|
| remark         : text        |   | point       : double         |
| status         : integer     |   | max_qty     : integer        |
| request_by     : integer     |   | commission  : double         |
| request_by_type: varchar(255)|   | status      : integer        |
| created_at     : timestamp   |   | deleted_at  : timestamp      |
| updated_at     : timestamp   |   +------------------------------+
| UNIQUE(product_id, shop_id)  |                   |
+------------------------------+                   |
               |                                   | shop_id
               | shop_id                           |
               v                                   v
+-------------------------------------------------------------+
|                           shops                             |
+-------------------------------------------------------------+
| id             : bigint (PK)                                |
| name           : varchar(255)                               |
| phone          : varchar(255)                               |
| address        : text                                       |
+-------------------------------------------------------------+
```

#### Key Findings:
1. **Unit Cost Source (`products.cost`)**:
   - Master acquisition cost per unit stored as `double`.
   - Cast to double and formatted via `CostUsdFor` (`number_format($this->cost, 2) . ' $'`).
2. **Unit Selling Price Source (`products.price` & `shop_products.price`)**:
   - Master retail selling price stored in `products.price`.
   - Branch-level pricing overrides stored in `shop_products.price` (falls back to `products.price`).
3. **Current Stock Count (`stock_on_hands.current_stock`)**:
   - Enforces unique constraint: `UNIQUE(['product_id', 'shop_id'])`.
   - Updated synchronously and atomically across all transactions (Stock In, Stock Out, Stock Transfer, POS Orders).

---

### 2.2 Current Stock on Hand Gaps

| Area | Current Behavior | Gap to Bridge |
| :--- | :--- | :--- |
| **Listing Table (`index.blade.php`)** | Columns: `[Nº, Product, Category, UOM, Current Stock, Date, Shop, Requested By, Action]`. | Missing **Total Cost** and **Total Amount** columns. |
| **KPI Metrics (Top of Table)** | No financial summary cards exist. | Need cards for **Total Cost** and **Total Amount**. |
| **Detail View (`view.blade.php`)** | Only displays physical stock quantity (`current_stock`). | Lacks financial breakdown for **Total Cost** and **Total Amount**. |
| **CSV & Excel Export** | Only exports product and current stock quantity. | Missing **Total Cost** and **Total Amount** columns and footer totals. |

---

## 3. Calculation Methodology & Precision

### 3.1 Dynamic Accessors vs Stored Columns

We adopt **Dynamic Eloquent Accessors** (`total_cost` & `total_amount`):
- **100% Single Source of Truth:** Guaranteed real-time accuracy whenever `current_stock`, `products.cost`, or `products.price` changes.
- **Zero Schema Migrations:** No ALTER TABLE or migration scripts needed.
- **Negligible Overhead:** Computed in-memory in $O(1)$ time during eager loading.

### 3.2 Exact Formulas & Edge Cases

```php
// Unit Cost (USD)
$unitCost = (float) ($this->product ? $this->product->cost : 0.0);

// Unit Price (USD) - Shop rate with fallback to Product rate
$unitPrice = (float) (
    $this->shopProduct && $this->shopProduct->price > 0 
        ? $this->shopProduct->price 
        : ($this->product ? $this->product->price : 0.0)
);

// Quantity
$quantity = (int) ($this->current_stock ?? 0);

// 1. Total Cost (Capital Investment)
$totalCost = $quantity * $unitCost;

// 2. Total Amount (Retail Sales Amount)
$totalAmount = $quantity * $unitPrice;
```

#### Edge Cases Handled:
- **Null / Deleted Product:** Falls back gracefully to `0.00` ($0.00).
- **Zero Stock:** Results in `$0.00` for both Total Cost and Total Amount.
- **Negative Stock (Audit Discrepancies):** Correctly reflects negative figures (e.g., `-$25.00`), highlighting inventory shrinkage or overselling.
- **Currency Formatting:** Formatted with commas and 2 decimals (`'$' . number_format($val, 2)`).

---

## 4. UI/UX Layout Recommendations

### 4.1 Top Summary KPI Stat Cards (Status: Removed)

> [!NOTE]
> Per user instruction, the top KPI card banner (`.kpi-grid`) and its supporting controller calculation (`getSummaryData()`) have been removed to preserve a minimalist layout directly displaying the table without top cards.

---

### 4.2 Stock on Hand Listing Table (`index.blade.php`)

Add **Total Cost** and **Total Amount** directly after **Current Stock**. This keeps the table clean, uncluttered, and highly readable without requiring horizontal scroll.

#### Recommended 100% Width Column Distribution:

| Column Header | Field Name | Width (`colVal`) | Alignment | Visual Format / Styling |
| :--- | :--- | :---: | :---: | :--- |
| **Nº** | `index` | 4% | Center | Plain number |
| **Product** | `product_title` | 18% | Left | Bold product name |
| **Category** | `category_title` | 12% | Left | Muted category text |
| **UOM** | `uom_title` | 6% | Center | Plain badge |
| **Current Stock** | `current_stock` | 8% | Center | Bold numeric badge |
| **Total Cost** | `total_cost_title` | 11% | Right | **`$XXX.XX`** (Blue-slate `#2563eb`) |
| **Total Amount** | `total_amount_title`| 11% | Right | **`$XXX.XX`** (Emerald green `#16a34a`) |
| **Shop** | `shop_title` | 12% | Left | Shop badge / text |
| **Date** | `created_date` | 8% | Center | `DD/MM/YYYY` |
| **Requested By** | `request_by_title` | 6% | Left | User / Barber name |
| **Action** | `action` | 4% | Center | View icon link |
| **TOTAL** | | **100%** | | |

> [!TIP]
> **Optional Extended View (With Unit Rates):** If store managers also wish to see the individual **Unit Cost** and **Unit Price** columns in the table, the columns can be configured as:
> `Current Stock (7%)` | `Unit Cost (7%)` | `Total Cost (9%)` | `Unit Price (7%)` | `Total Amount (10%)`.
> Both layouts are fully supported. The standard 2-column addition (`Total Cost` + `Total Amount`) is recommended for the cleanest UI.

---

### 4.3 View Detail Screen (`view.blade.php`)

Insert a dedicated financial valuation block directly below `Current Stock`:

```html
<div class="row-2">
    <div class="form-row iconInput">
        <label>{{ __('stock_on_hand.form.total_cost') }}</label>
        <input type="text" value="{{ $data->total_cost_title ?? '$0.00' }}" readonly style="font-weight: 600; color: #2563eb;">
        <i class='bx bx-wallet'></i>
    </div>
    <div class="form-row iconInput">
        <label>{{ __('stock_on_hand.form.total_amount') }}</label>
        <input type="text" value="{{ $data->total_amount_title ?? '$0.00' }}" readonly style="font-weight: 600; color: #16a34a;">
        <i class='bx bx-dollar-circle'></i>
    </div>
</div>
<div class="row-2">
    <div class="form-row iconInput">
        <label>{{ __('stock_on_hand.form.unit_cost') }}</label>
        <input type="text" value="{{ $data->unit_cost_title ?? '$0.00' }}" readonly>
        <i class='bx bx-purchase-tag'></i>
    </div>
    <div class="form-row iconInput">
        <label>{{ __('stock_on_hand.form.unit_price') }}</label>
        <input type="text" value="{{ $data->unit_price_title ?? '$0.00' }}" readonly>
        <i class='bx bx-tag-alt'></i>
    </div>
</div>
```

---

### 4.4 Data Exports (CSV & ExcelJS)

1. **CSV Export (`StockOnHandController@export`)**:
   - Add columns: `Total Cost ($)` and `Total Amount ($)`.
   - Output formatted values for each exported line.
2. **ExcelJS Export (`runExcelExport`)**:
   - Add headers: `Total Cost` and `Total Amount`.
   - Format cells as currency: `$#,##0.00` (right-aligned).
   - **Summary Total Row:** Append an automated bottom summary row calculating:
     - Total Current Stock Units
     - Sum of Total Cost
     - Sum of Total Amount
     - Net Gross Margin

---

## 5. Multi-Shop vs Enterprise Scope

1. **Filtered by Shop (`shop_id` selected):**
   - Row valuations reflect physical inventory at that specific shop.
   - Top KPI cards reflect **Shop Total Cost** and **Shop Total Amount**.
2. **Enterprise View (All Shops):**
   - Each row displays product stock at its respective shop location.
   - Top KPI cards aggregate company-wide **Enterprise Total Cost** and **Enterprise Total Amount**.
3. **Stock Transfers Between Shops:**
   - Source shop valuation decreases; destination shop valuation increases.
   - Enterprise Total Cost and Total Amount remain conserved ($+\$X - \$X = \$0$).

---

## 6. Required Code Changes (Exact File Modifications)

### File 1: `app/Models/StockOnHand.php`
- **Modifications:**
  - Add relationship to `ShopProduct`:
    ```php
    public function shopProduct()
    {
        return $this->hasOne(ShopProduct::class, 'product_id', 'product_id')
            ->where('shop_id', $this->shop_id);
    }
    ```
  - Add accessors:
    ```php
    public function getUnitCostAttribute()
    {
        return (float) ($this->product ? $this->product->cost : 0.0);
    }

    public function getUnitCostTitleAttribute()
    {
        return '$' . number_format($this->unit_cost, 2);
    }

    public function getUnitPriceAttribute()
    {
        if ($this->shopProduct && $this->shopProduct->price > 0) {
            return (float) $this->shopProduct->price;
        }
        return (float) ($this->product ? $this->product->price : 0.0);
    }

    public function getUnitPriceTitleAttribute()
    {
        return '$' . number_format($this->unit_price, 2);
    }

    public function getTotalCostAttribute()
    {
        return (float) ($this->current_stock * $this->unit_cost);
    }

    public function getTotalCostTitleAttribute()
    {
        return '$' . number_format($this->total_cost, 2);
    }

    public function getTotalAmountAttribute()
    {
        return (float) ($this->current_stock * $this->unit_price);
    }

    public function getTotalAmountTitleAttribute()
    {
        return '$' . number_format($this->total_amount, 2);
    }
    ```
  - Add to `$appends`:
    `'total_cost'`, `'total_cost_title'`, `'total_amount'`, `'total_amount_title'`, `'unit_cost'`, `'unit_cost_title'`, `'unit_price'`, `'unit_price_title'`.

---

### File 2: `app/Http/Controllers/Admin/Inventory/StockOnHandController.php`
- **Modifications:**
  - In `queryData()`: Eager load `product` (including cost & price) and `shopProduct` to eliminate N+1 queries.
  - In `index()`:
    - Compute summary aggregates for the active filtered query:
      - `total_items`: Count of records.
      - `total_stock_qty`: Sum of `current_stock`.
      - `total_cost`: Sum of `(current_stock * cost)`.
      - `total_amount`: Sum of `(current_stock * price)`.
    - Pass `$summaryData` to the view.
  - In `export()`:
    - Add `Total Cost` and `Total Amount` to `$columns`.
    - Output `$item->total_cost_title` and `$item->total_amount_title` in CSV rows.

---

### File 3: `resources/admin/views/pages/inventoryManagement/stockOnHand/index.blade.php`
- **Modifications:**
  - Add KPI summary cards banner directly above `@component('admin::components.listingData')`.
  - Update `tbHeader` array:
    - Add `total_cost_title` (`colVal` 11, class `text-right`).
    - Add `total_amount_title` (`colVal` 11, class `text-right`).
    - Adjust adjacent column widths so total equals 100%.
  - In `runExcelExport()`:
    - Add `Total Cost` and `Total Amount` headers.
    - Map `item.total_cost` and `item.total_amount` with `$#,##0.00` format.
    - Add bottom summary total row.

---

### File 4: `resources/admin/views/pages/inventoryManagement/stockOnHand/view.blade.php`
- **Modifications:**
  - Add paired inputs for `Total Cost` and `Total Amount` below `Current Stock`.
  - Add secondary inputs for `Unit Cost` and `Unit Price`.

---

### File 5: `resources/admin/views/pages/inventoryManagement/stockOnHand/table.blade.php`
- **Modifications:**
  - Add `Total Cost` and `Total Amount` column headers and body values to match the main table.

---

### File 6: `lang/en/stock_on_hand.php` & `lang/km/stock_on_hand.php`
- **English (`en`):**
  ```php
  'table' => [
      'total_cost' => 'Total Cost',
      'total_amount' => 'Total Amount',
      'unit_cost' => 'Unit Cost',
      'unit_price' => 'Unit Price',
  ],
  'form' => [
      'total_cost' => 'Total Cost',
      'total_amount' => 'Total Amount',
      'unit_cost' => 'Unit Cost',
      'unit_price' => 'Unit Price',
  ],
  'summary' => [
      'total_items' => 'Total Items',
      'total_qty' => 'Total Stock Units',
      'total_cost' => 'Total Cost',
      'total_amount' => 'Total Amount',
  ],
  ```
- **Khmer (`km`):**
  ```php
  'table' => [
      'total_cost' => 'សរុបតម្លៃដើម',
      'total_amount' => 'សរុបទឹកប្រាក់',
      'unit_cost' => 'តម្លៃដើម',
      'unit_price' => 'តម្លៃលក់',
  ],
  'form' => [
      'total_cost' => 'តម្លៃដើមសរុប',
      'total_amount' => 'ទឹកប្រាក់សរុប',
      'unit_cost' => 'តម្លៃដើម',
      'unit_price' => 'តម្លៃលក់',
  ],
  'summary' => [
      'total_items' => 'ផលិតផលសរុប',
      'total_qty' => 'បរិមាណស្តុកសរុប',
      'total_cost' => 'តម្លៃដើមសរុប',
      'total_amount' => 'ទឹកប្រាក់សរុប',
  ],
  ```

---

### File 7: `tests/Feature/StockOnHandValuationTest.php` (New Automated Test)
- **Coverage:**
  - Verification of `total_cost` (`current_stock * cost`) and `total_amount` (`current_stock * price`).
  - Handling of zero stock, zero cost, and zero price.
  - Multi-shop valuation accuracy under filtered and enterprise queries.
  - CSV and Excel export data validation.

---

## 7. Performance & Optimization Strategy

1. **Zero N+1 Query Guarantee:**
   `StockOnHandController@queryData()` eager-loads `product` and `shopProduct` so accessor evaluations execute in-memory with zero additional SQL round-trips.
2. **Database Aggregates for KPI Banner:**
   Aggregate metrics for the top KPI summary cards are computed directly via SQL queries (`SUM(...)`) rather than loading entire datasets into PHP memory.
3. **MDI Tab Isolation:**
   All filters and exports operate within scoped component state, ensuring no conflict across multiple opened MDI workspace tabs.

---

## 8. Comprehensive Test Matrix

```
+-----+----------------------------+-----------------------------+------------------------------------+
| #   | Scenario                   | Test Inputs                 | Expected Output                    |
+-----+----------------------------+-----------------------------+------------------------------------+
| 1.1 | Standard Calculation       | Stock: 10, Cost: $5, Pr: $8 | Total Cost: $50.00, Amount: $80.00 |
| 1.2 | Zero Current Stock         | Stock: 0, Cost: $10, Pr: $15| Total Cost: $0.00, Amount: $0.00   |
| 1.3 | Missing / Zero Cost        | Stock: 20, Cost: 0, Pr: $10 | Total Cost: $0.00, Amount: $200.00 |
| 1.4 | Missing / Zero Price       | Stock: 20, Cost: $5, Pr: 0  | Total Cost: $100.00, Amount: $0.00 |
| 1.5 | Fractional Cent Valuations | Stock: 7, Cost: $3.25, $5.49| Total Cost: $22.75, Amount: $38.43 |
| 1.6 | Shop Custom Price Override | Base Pr: $6.20, Shop: $18.15| Total Amount uses $18.15 rate      |
| 2.1 | Single Shop Filtering      | Filter: Shop A              | Summary shows Shop A totals only   |
| 2.2 | Enterprise View            | Filter: All Shops           | Summary aggregates company-wide    |
| 3.1 | CSV Export Validation      | Trigger /export             | CSV contains Total Cost & Amount   |
| 3.2 | Excel Export Validation    | Click Export Excel          | Excel has Total Cost & Amount + sum|
| 3.3 | View Details Screen        | Navigate /view/{id}         | Shows Total Cost & Total Amount    |
+-----+----------------------------+-----------------------------+------------------------------------+
```

---

## 9. Next Steps & Approval Workflow

1. **User Review:** The user reviews this implementation plan specifically detailing **Total Cost** and **Total Amount**.
2. **User Confirmation:** The user approves the plan to proceed with execution.
3. **Execution:** Upon approval, implementation begins across the files listed in Section 6.
