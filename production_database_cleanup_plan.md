# Production Database Cleanup Implementation Plan & SQL Script

**Target Database:** `POSSystem` / `POSSystem_03` (MySQL)  
**Objective:** Purge development and testing data to establish a pristine production environment while protecting critical system configurations, roles, permissions, master catalogs, and automated backup settings.

---

## 1. Executive Summary & Strategy

During the development and testing lifecycle, various test orders, balance transfers, stock movements, loyalty points, petty cash expenses, and non-sales income transactions were recorded across the database. Before opening the system to live business operations, all transactional tables must be cleared so that:
- Order numbers, invoice sequences, and payment numbers restart from their initial index (e.g. `0001` or configured prefix).
- Unpaid order balance transfers (`order_balance_transfers`) linking previous customer balances are completely flushed.
- Other income records (`other_incomes`) and payment receipts (`other_income_payments`) are purged.
- Inventory balances on hand (`stock_on_hands`) and audit movements reflect zero prior transactions (to be received via formal production Stock-In).
- Financial, sales, expense, and profit/loss reports reflect strictly live transactions.
- Test backup execution logs (`backup_records`) are cleared so the backup management dashboard starts clean.
- System users, role-based access controls (RBAC), global settings, invoice formatting, and backup configurations (`backup_settings`) remain intact.

We have structured the cleanup into **two clear options** for your review:

* **Option 1 (Recommended — Transactional Cleanup):** Purges all test orders, order balance transfers, line items, payment records, non-sales income, inventory transactions, stock movements, staff expenses, loyalty points, and test backup logs. Resets customer loyalty points and shop wallet balances to zero, but **preserves** the product catalog, suppliers, staff directory, product categories, income categories, and configuration.
* **Option 2 (Complete Full Reset):** Purges everything in Option 1 **plus** all test customers, test staff, sample products, and test suppliers, leaving only core administrators, settings, permissions, and base reference data.

---

## 2. Table Classification Matrix (All 60 Tables)

The project database currently comprises **60 tables**, classified below by domain, current row volume, and recommended cleanup action:

| Table Name | Current Rows | Classification | Recommended Action | Justification / Description |
|:---|:---:|:---|:---|:---|
| `order_balance_transfers` | 46 | **Transactional** | **TRUNCATE** | Multi-order carried-over unpaid balance transfers. Must be truncated with orders. |
| `order_details` | 1,788 | **Transactional** | **TRUNCATE** | Line items associated with test orders and sales bookings. |
| `order_payments` | 490 | **Transactional** | **TRUNCATE** | Customer payment transactions and receipt logs. |
| `orders` | 566 | **Transactional** | **TRUNCATE** | Test customer orders, bookings, and sales invoices. |
| `customer_points` | 342 | **Transactional** | **TRUNCATE** | Loyalty points ledger accumulated during test purchases. |
| `customer_paid` | 0 | **Transactional** | **TRUNCATE** | Customer payment transaction tracking history. |
| `other_income_payments` | 1 | **Transactional** | **TRUNCATE** | Payment receipts and slips recorded for other income entries. |
| `other_incomes` | 1 | **Transactional** | **TRUNCATE** | Non-sales income transactions (supplier incentives, allowances, rebates, refunds). |
| `stock_ins` | 271 | **Transactional** | **TRUNCATE** | Test stock receipt records from suppliers. |
| `stock_outs` | 2,191 | **Transactional** | **TRUNCATE** | Test stock dispatch, adjustments, and wastage records. |
| `stock_transfers` | 2 | **Transactional** | **TRUNCATE** | Test inter-branch/shop stock transfer logs. |
| `stock_histories` | 2,095 | **Transactional** | **TRUNCATE** | Granular audit trail logs of all inventory movements. |
| `inventory_histories` | 0 | **Transactional** | **TRUNCATE** | Snapshot inventory balance history records. |
| `stock_on_hands` | 85 | **Transactional Balance** | **TRUNCATE** | Physical stock counts derived from test stock movements. |
| `staff_expenses` | 10 | **Transactional** | **TRUNCATE** | Expense vouchers and petty cash transactions. |
| `invoice_sequences` | 1 | **Transactional Counter** | **TRUNCATE** | Sequence tracker for invoice numbering. Must be cleared so production invoices start at #1. |
| `backup_records` | 3 | **Operational Logs** | **TRUNCATE** | Historical log of test backup archives generated during development. |
| `customers` | 455 | **Master Data** | **PRESERVE** (Reset points) or **TRUNCATE** (Option 2) | Customer directory. In Option 1, reset `total_point = 0`. In Option 2, purge test customers. |
| `products` | 94 | **Master Data** | **PRESERVE** or **TRUNCATE** (Option 2) | Retail product catalog with barcodes, pricing, and packaging specifications. |
| `categories` | 5 | **Master Data** | **PRESERVE** | Product category taxonomy (Finished Goods, Raw Material, Packaging, etc.). |
| `uoms` | 10 | **Reference Data** | **PRESERVE** | Units of Measure (Can, Case, Bottle, Kilogram, Pack, Box, etc.). |
| `suppliers` | 11 | **Master Data** | **PRESERVE** | Registered suppliers & distributors. |
| `shops` | 3 | **Master Data** | **PRESERVE** (Reset balances) | Shop branches & central warehouse. Reset `total_wallet = 0, total_point = 0`. |
| `shop_products` | 92 | **Master Data Pivot** | **PRESERVE** or **TRUNCATE** (Option 2) | Shop-specific pricing, commissions, and branch inventory mappings. |
| `staffs` | 21 | **Master Data** | **PRESERVE** or **TRUNCATE** (Option 2) | Staff employee directory and profiles. |
| `positions` | 15 | **Reference Data** | **PRESERVE** | Job designations (General Manager, Cashier, Inventory Officer, etc.). |
| `other_income_categories` | 2 | **Master Data** | **PRESERVE** | Categorization for non-sales revenue (e.g. Incentives/Bonuses, Refunds/Returns). |
| `stock_types` | 3 | **Reference Data** | **PRESERVE** | Stock movement classification types (Stock In, Stock Out, Transfer). |
| `users` | 16 | **System Critical** | **DO NOT TOUCH** | Superadmin and administrative accounts. |
| `roles` | 1 | **System Critical** | **DO NOT TOUCH** | Spatie RBAC user roles. |
| `permissions` | 166 | **System Critical** | **DO NOT TOUCH** | System permission catalog. |
| `model_has_roles` | 3 | **System Critical** | **DO NOT TOUCH** | User-to-role assignments. |
| `model_has_permissions` | 333 | **System Critical** | **DO NOT TOUCH** | Direct user-to-permission mappings. |
| `module_permissions` | 50 | **System Critical** | **DO NOT TOUCH** | System permission modules and navigation hierarchy. |
| `role_has_permissions` | 0 | **System Critical** | **DO NOT TOUCH** | Role-permission assignment matrix. |
| `migrations` | 85 | **System Critical** | **NEVER TOUCH** | Laravel database migration execution history. |
| `settings` | 1 | **Configuration** | **DO NOT TOUCH** | Core system settings (site title, currency, brand logos). |
| `invoice_settings` | 1 | **Configuration** | **DO NOT TOUCH** | Invoice format rules (Prefix `NO`, 4 digits, separator `-`). |
| `backup_settings` | 1 | **Configuration** | **DO NOT TOUCH** | S3/R2 cloud credentials, auto-backup schedule, and disk quota configurations. |
| `file_managers` | 20 | **Assets / System** | **PRESERVE** | Metadata for product photos, invoice logos, and uploaded files. |
| `folders` | 3 | **Assets / System** | **PRESERVE** | File manager directory folder structure. |
| `oauth_access_tokens` | 0 | **Security / Auth** | **PRESERVE** | OAuth / Passport authentication token table. |
| `oauth_auth_codes` | 0 | **Security / Auth** | **PRESERVE** | OAuth authorization codes. |
| `oauth_clients` | 0 | **Security / Auth** | **PRESERVE** | OAuth client registrations. |
| `oauth_personal_access_clients` | 0 | **Security / Auth** | **PRESERVE** | OAuth personal access client references. |
| `oauth_refresh_tokens` | 0 | **Security / Auth** | **PRESERVE** | OAuth refresh tokens. |
| `barbers` | 0 | **Legacy / Inactive** | **DO NOT TOUCH** | Legacy entity (empty). |
| `brand_settings` | 0 | **Configuration (Empty)** | **PRESERVE** | Brand setting parameters ready for production use. |
| `brands` | 0 | **Master Data (Empty)** | **PRESERVE** | Product brand references. |
| `services` | 0 | **Master Data (Empty)** | **PRESERVE** | Service catalog items. |
| `shop_services` | 0 | **Master Data Pivot (Empty)**| **PRESERVE** | Shop-service pricing links. |
| `point_settings` | 0 | **Configuration (Empty)** | **PRESERVE** | Point accrual rules. |
| `rewards` | 0 | **Master Data (Empty)** | **PRESERVE** | Loyalty reward catalog. |
| `discounts` | 0 | **Master Data (Empty)** | **PRESERVE** | Promotional discount schemes. |
| `types` | 0 | **Reference Data (Empty)** | **PRESERVE** | Classification types. |
| `pages` | 0 | **Configuration (Empty)** | **PRESERVE** | CMS pages & content blocks. |
| `fund_ranges` | 0 | **Reference Data (Empty)** | **PRESERVE** | Tiered fund ranges. |
| `rate_ranges` | 0 | **Reference Data (Empty)** | **PRESERVE** | Tiered commission/rate ranges. |
| `data_tables` | 0 | **System (Empty)** | **PRESERVE** | User datatable column preference state. |
| `job_lists` | 0 | **System (Empty)** | **PRESERVE** | Background queue job logs. |

---

## 3. Foreign Key & Dependency Analysis

An inspection of `INFORMATION_SCHEMA.KEY_COLUMN_USAGE` in the database confirms the following foreign key constraints:

1. **Role & Permission Subsystem (Spatie RBAC):**
   - `model_has_permissions.permission_id` $\rightarrow$ `permissions.id`
   - `model_has_roles.role_id` $\rightarrow$ `roles.id`
   - `role_has_permissions.permission_id` $\rightarrow$ `permissions.id`
   - `role_has_permissions.role_id` $\rightarrow$ `roles.id`

2. **Staff Expense Subsystem:**
   - `staff_expenses.created_by` $\rightarrow$ `users.id`
   - `staff_expenses.shop_id` $\rightarrow$ `shops.id`
   - `staff_expenses.staff_id` $\rightarrow$ `staffs.id`

3. **Order Balance Transfer Subsystem (Newly Added):**
   - `order_balance_transfers.new_order_id` $\rightarrow$ `orders.id` (`ON DELETE RESTRICT`)
   - `order_balance_transfers.previous_order_id` $\rightarrow$ `orders.id` (`ON DELETE RESTRICT`)
   - `order_balance_transfers.transferred_by` $\rightarrow$ `users.id` (`ON DELETE SET NULL`)
   - `order_balance_transfers.reversed_by` $\rightarrow$ `users.id` (`ON DELETE SET NULL`)

4. **Other Income Subsystem (Newly Added):**
   - `other_incomes.category_id` $\rightarrow$ `other_income_categories.id` (`ON DELETE RESTRICT`)
   - `other_incomes.supplier_id` $\rightarrow$ `suppliers.id` (`ON DELETE SET NULL`)
   - `other_incomes.shop_id` $\rightarrow$ `shops.id` (`ON DELETE SET NULL`)
   - `other_incomes.created_by` $\rightarrow$ `users.id` (`ON DELETE RESTRICT`)
   - `other_income_payments.other_income_id` $\rightarrow$ `other_incomes.id` (`ON DELETE CASCADE`)
   - `other_income_payments.created_by` $\rightarrow$ `users.id` (`ON DELETE RESTRICT`)

### Key Takeaways on MySQL Truncation
In MySQL InnoDB:
- `TRUNCATE TABLE` does **not** support `CASCADE`.
- MySQL will explicitly reject `TRUNCATE TABLE` on any table referenced by a foreign key constraint (e.g. attempting to truncate `orders` while referenced by `order_balance_transfers`, or `other_incomes` while referenced by `other_income_payments`), even if child tables are already empty.
- To safely execute truncation without triggering foreign key violation errors (error code `1701`), all cleanup operations **must wrap execution in `SET FOREIGN_KEY_CHECKS = 0;` and re-enable with `SET FOREIGN_KEY_CHECKS = 1;`**.
- As best practice, child records (e.g. `order_balance_transfers`, `other_income_payments`, `order_details`) are scheduled for deletion before parent tables (`orders`, `other_incomes`).

---

## 4. Pre-Cleanup Requirements & Precautions

> [!CAUTION]
> **Data loss prevention:** A complete database backup must be created and verified before running any cleanup commands. Once executed, `TRUNCATE` operations cannot be rolled back via SQL transactions because `TRUNCATE` performs an implicit DDL commit in MySQL.

### Step 1: Pre-Execution Database Dump (Mandatory Backup)
Run the following terminal command to export an exact full backup:
```bash
mysqldump -u root -p \
  --single-transaction \
  --quick \
  --routines \
  --triggers \
  POSSystem_03 > POSSystem_backup_before_production_cleanup_$(date +%Y%m%d_%H%M%S).sql
```
*(Replace `POSSystem_03` with your target database name if different).*

### Step 2: Put Application in Maintenance Mode
Prevent users, background jobs, or cron schedulers from writing new records during cleanup:
```bash
php artisan down --message="System is undergoing production deployment preparation. Please wait."
```

### Step 3: Run Pre-Validation Query
Verify the current row counts across all **17 transactional & operational tables** to benchmark the records to be purged:
```sql
SELECT 'order_balance_transfers' AS tbl, COUNT(*) AS count FROM order_balance_transfers
UNION ALL SELECT 'order_details', COUNT(*) FROM order_details
UNION ALL SELECT 'order_payments', COUNT(*) FROM order_payments
UNION ALL SELECT 'orders', COUNT(*) FROM orders
UNION ALL SELECT 'customer_points', COUNT(*) FROM customer_points
UNION ALL SELECT 'customer_paid', COUNT(*) FROM customer_paid
UNION ALL SELECT 'other_income_payments', COUNT(*) FROM other_income_payments
UNION ALL SELECT 'other_incomes', COUNT(*) FROM other_incomes
UNION ALL SELECT 'stock_ins', COUNT(*) FROM stock_ins
UNION ALL SELECT 'stock_outs', COUNT(*) FROM stock_outs
UNION ALL SELECT 'stock_transfers', COUNT(*) FROM stock_transfers
UNION ALL SELECT 'stock_histories', COUNT(*) FROM stock_histories
UNION ALL SELECT 'inventory_histories', COUNT(*) FROM inventory_histories
UNION ALL SELECT 'stock_on_hands', COUNT(*) FROM stock_on_hands
UNION ALL SELECT 'staff_expenses', COUNT(*) FROM staff_expenses
UNION ALL SELECT 'invoice_sequences', COUNT(*) FROM invoice_sequences
UNION ALL SELECT 'backup_records', COUNT(*) FROM backup_records;
```

---

## 5. Proposed SQL Cleanup Scripts

### Option 1: Clean Transactional Data Only (Recommended)
*Clears all test sales, order balance transfers, line items, payment records, other incomes, stock movements, invoice sequences, and backup logs, while preserving the product catalog, suppliers, customers, staff, income categories, and configuration intact.*

```sql
-- =====================================================================
-- PRODUCTION CLEANUP SCRIPT (OPTION 1: TRANSACTIONAL DATA ONLY)
-- Database: POSSystem / POSSystem_03
-- Description: Removes all test transactions, orders, carried balances, 
--              other incomes, inventory logs, and test backup records,
--              while protecting master catalogs and configuration.
-- =====================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Sales, Order Balances & Customer Transactions
TRUNCATE TABLE `order_balance_transfers`;
TRUNCATE TABLE `order_details`;
TRUNCATE TABLE `order_payments`;
TRUNCATE TABLE `orders`;
TRUNCATE TABLE `customer_points`;
TRUNCATE TABLE `customer_paid`;

-- 2. Other Incomes & External Revenue Transactions
TRUNCATE TABLE `other_income_payments`;
TRUNCATE TABLE `other_incomes`;

-- 3. Inventory Movements & Balances
TRUNCATE TABLE `stock_histories`;
TRUNCATE TABLE `inventory_histories`;
TRUNCATE TABLE `stock_outs`;
TRUNCATE TABLE `stock_transfers`;
TRUNCATE TABLE `stock_ins`;
TRUNCATE TABLE `stock_on_hands`;

-- 4. Staff Expenses
TRUNCATE TABLE `staff_expenses`;

-- 5. Invoice Numbering Sequence Counter (Restarts invoice numbering from #1)
TRUNCATE TABLE `invoice_sequences`;

-- 6. Operational Backup Execution Logs (Clears dev/test backup history)
TRUNCATE TABLE `backup_records`;

-- 7. Reset Accumulated Balances on Master Entities
UPDATE `customers` SET `total_point` = 0;
UPDATE `shops` SET `total_wallet` = 0, `total_point` = 0;

SET FOREIGN_KEY_CHECKS = 1;
```

---

### Option 2: Full System Reset (Bare Production Setup)
*Use this option if you want to also remove dummy seeded customers, staff, and sample products, starting with a completely fresh master catalog.*

```sql
-- =====================================================================
-- PRODUCTION CLEANUP SCRIPT (OPTION 2: FULL SYSTEM RESET)
-- Database: POSSystem / POSSystem_03
-- Description: Purges all test transactions PLUS test customers,
--              test staff, sample products, and shop product links.
-- =====================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. All Transactional Tables
TRUNCATE TABLE `order_balance_transfers`;
TRUNCATE TABLE `order_details`;
TRUNCATE TABLE `order_payments`;
TRUNCATE TABLE `orders`;
TRUNCATE TABLE `customer_points`;
TRUNCATE TABLE `customer_paid`;
TRUNCATE TABLE `other_income_payments`;
TRUNCATE TABLE `other_incomes`;
TRUNCATE TABLE `stock_histories`;
TRUNCATE TABLE `inventory_histories`;
TRUNCATE TABLE `stock_outs`;
TRUNCATE TABLE `stock_transfers`;
TRUNCATE TABLE `stock_ins`;
TRUNCATE TABLE `stock_on_hands`;
TRUNCATE TABLE `staff_expenses`;
TRUNCATE TABLE `invoice_sequences`;
TRUNCATE TABLE `backup_records`;

-- 2. Test Master Data Entities
TRUNCATE TABLE `products`;
TRUNCATE TABLE `shop_products`;
TRUNCATE TABLE `customers`;
TRUNCATE TABLE `staffs`;

-- Optional: Uncomment below only if you also want to remove sample products, suppliers, or custom income categories:
-- TRUNCATE TABLE `products`;
-- TRUNCATE TABLE `suppliers`;
-- TRUNCATE TABLE `other_income_categories`;

UPDATE `shops` SET `total_wallet` = 0, `total_point` = 0;

SET FOREIGN_KEY_CHECKS = 1;
```

---

## 6. Post-Cleanup Validation & Recovery Steps

### Post-Validation SQL Check
Execute this verification script to ensure all transactional tables are completely cleared (`0` rows) while critical administrative, configuration, and master records remain:

```sql
SELECT 
    table_name, 
    table_rows 
FROM 
    information_schema.tables 
WHERE 
    table_schema = DATABASE()
    AND table_name IN (
        -- Cleared Transactional Tables (Must be 0)
        'orders', 'order_details', 'order_payments', 'order_balance_transfers',
        'customer_points', 'customer_paid', 'other_incomes', 'other_income_payments',
        'stock_ins', 'stock_outs', 'stock_transfers', 'stock_on_hands', 
        'stock_histories', 'staff_expenses', 'invoice_sequences', 'backup_records',
        -- Preserved System / Configuration Tables (Must have rows)
        'users', 'roles', 'permissions', 'module_permissions',
        'settings', 'invoice_settings', 'backup_settings', 'other_income_categories'
    )
ORDER BY 
    table_name ASC;
```

### Expected Validation Outcome:
- **All 17 Transactional & Operational Tables:** Exactly **0 rows**  
  (`orders`, `order_details`, `order_payments`, `order_balance_transfers`, `customer_points`, `customer_paid`, `other_incomes`, `other_income_payments`, `stock_ins`, `stock_outs`, `stock_transfers`, `stock_histories`, `inventory_histories`, `stock_on_hands`, `staff_expenses`, `invoice_sequences`, `backup_records`)
- **System Administration & RBAC:** Preserved (`users`, `roles`, `permissions`, `module_permissions`)
- **Core Configurations:** Preserved (`settings`, `invoice_settings`, `backup_settings`, `other_income_categories`)
- **Accumulated Balances:** All customer `total_point` reset to `0`; shop `total_wallet` and `total_point` reset to `0`.

### Physical File Storage Cleanup (Optional)
If test backups or temporary files were written to local disk during development, clean them up as needed:
```bash
# Optional: remove local test backup zip files
rm -f storage/app/backups/*.zip
```

### Post-Cleanup Cache Refresh & Bring System Up
After running the cleanup SQL, clear and rebuild all Laravel application caches before taking the application out of maintenance mode:
```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up
```
