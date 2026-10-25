# Implementation Plan: Record-Level Data Access Permissions for Customers and Orders

**Date:** 25 October 2026  
**Status:** Implemented & Verified  
**Target Modules:** **User Management** (`users`), **Customers** (`customers`), **Orders** (`orders`), and **Dashboard** customer/order metrics  
**Security Scope:** Enforce database/backend record-level access control based on a dedicated **`data_access`** field in the User module (`all` vs `own`), separate from existing User Role / Spatie permissions logic.

---

## 1. Executive Summary & Objective

Previously, data access control in many systems is coupled directly to the user's role (e.g. `role === 'admin'`). However, in this system, roles may be assigned broadly (e.g., standard users or managers given administrative roles) while permissions are handled by Spatie permissions.

The objective of this architecture is to provide an explicit, administrator-configurable **Data Access Scope (`data_access`)** field on each user account:

1. **Dedicated User Field (`data_access`):**
   - Available when **creating** or **editing** a user in the User module.
   - Options:
     - **`all` (All Records):** The user can view, search, filter, export, and manage all customer and order records store-wide.
     - **`own` (Own Records Only):** The user is strictly restricted to customer and order records they personally created.
   - **Default for new users:** `'own'` (secure default).
   - **Visual indicator:** A clear status badge in the User listing table (`All Records` vs `Own Records Only`).
   - **Separation of Concerns:** Kept strictly separate from User Roles (`role`) and Spatie permissions (`syncPermissions()`).

2. **Access Control Rules by Data Access Setting:**
   - **Users with `data_access = 'own'`:**
     - Can only view, search, filter, edit, export, and delete customer records they created (`customers.user = Auth::id()`).
     - Can only view, search, filter, edit, export, and delete order records they created (`orders.created_by = Auth::id()`).
     - When creating/editing an order, can only select customers they created (or walk-in customer).
     - On the Dashboard (`/admin/dashboard`), summary metrics (total sales, pending count, remaining balance, customer count, recent transactions, top product sales) are strictly scoped to records created by the logged-in user.
     - Cannot view or manipulate records created by other users via listings, dropdowns, direct URL navigation, or direct POST requests (aborts with `403 Forbidden`).
   - **Users with `data_access = 'all'` (and `super_admin`):**
     - Retain full, unrestricted visibility and management permissions across all customer and order records regardless of who created them.
     - On the Dashboard, metrics and transactions display global store-wide totals across all users.
     - `super_admin` always retains unconditional full access regardless of any setting.
3. **Data Integrity & Security:**
   - Enforce access control directly in database queries via Model Scopes (`scopeForUser`) and direct controller ownership checks (`$record->isOwnedBy(Auth::user())`).
   - Creator tracking must be immutable upon record creation (editing an existing record must never overwrite or reassign its creator).

---

## 2. Current System Architecture & Data Access Model

### 2.1 The `data_access` Field on Users
- Added via migration `database/migrations/2026_10_25_000001_add_data_access_to_users_table.php`:
  ```php
  $table->string('data_access', 20)->default('own')->after('role');
  ```
- **Helper Method on [User.php](file:///Users/longdy/Documents/Projects/Laravel/POSSystem/app/Models/User.php):**
  ```php
  public function hasFullDataAccess(): bool
  {
      if ($this->role === 'super_admin') {
          return true;
      }
      return ($this->data_access ?? 'own') === 'all';
  }
  ```
- **Accessors for UI Badges & Labels:**
  - `$user->data_access_label`: Returns `'All Records'` or `'Own Records Only'` (localized).
  - `$user->data_access_badge`: Returns a styled HTML badge matching the user table's visual design.

### 2.2 Current Creator Tracking & Schema Audit
- **Orders Table (`orders`):**
  - Migration `2026_10_23_000000_add_created_by_to_orders_table.php` added:
    ```php
    $table->unsignedBigInteger('created_by')->nullable()->index();
    ```
  - [Order.php](file:///Users/longdy/Documents/Projects/Laravel/POSSystem/app/Models/Order.php#L54-L57) already defines:
    ```php
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }
    ```
  - **Identified Defect in Current Code:** In [OrderController.php:L244](file:///Users/longdy/Documents/Projects/Laravel/POSSystem/app/Http/Controllers/Admin/OrderController.php#L244), `$order->created_by = Auth::id()` currently runs on **both create and update**. Editing an order currently overwrites the creator. This must be fixed so `created_by` is only assigned when creating a new order (`!$order->exists`).
  - **Legacy Order Records:** Orders created prior to 23 October 2026 have `created_by = NULL`.

- **Customers Table (`customers`):**
  - Migration `2025_05_18_191733_create_customers_table.php` defined:
    ```php
    $table->string('user'); // stores creator user ID as string
    ```
  - In [Tools.php:L19](file:///Users/longdy/Documents/Projects/Laravel/POSSystem/app/Services/Tools.php#L19), `Tools::onSave` assigns `$item["user"] = Auth::user()->id;`.
  - **Identified Defect in Current Code:** On edit, `$item["user"]` is updated. We must ensure that editing an existing customer does not reassign the creator.
  - [Customer.php](file:///Users/longdy/Documents/Projects/Laravel/POSSystem/app/Models/Customer.php) will be updated with a `createdBy()` relationship to [User.php](file:///Users/longdy/Documents/Projects/Laravel/POSSystem/app/Models/User.php).

---

## 3. Strict Scope of Affected Components (Customers & Orders Only)

| Module / Feature | File / Route | Current Query Behavior | Required Access Restriction (Regular User vs Admin) |
| :--- | :--- | :--- | :--- |
| **Customer Listing** | [CustomerController.php](file:///Users/longdy/Documents/Projects/Laravel/POSSystem/app/Http/Controllers/Admin/CustomerController.php) `queryCustomers()` | `Customer::where('status', $status)` | **Regular User:** Scoped to `customers.user = Auth::id()`.<br>**Admin User:** **Unrestricted** (all customer records returned). |
| **Customer Edit / Form** | `admin-customer-edit` (`CustomerController::onEdit`) | `Customer::find($req->id)` | **Regular User:** Abort `403 Forbidden` if customer was created by another user.<br>**Admin User:** Can open and edit any customer record. |
| **Customer Save / Update** | `admin-customer-save` (`CustomerController::Save`) | Calls `Tools::onSave` -> `updateOrCreate` | **Regular User:** Deny updating unowned customer with `403`. On insert, assign creator to `Auth::id()`; on update, preserve original creator.<br>**Admin User:** Can update any customer; creator remains preserved. |
| **Customer Status / Delete** | `admin-customer-status`, `delete`, `restore`, `destroy` | Calls `Tools` methods by `$id` | **Regular User:** Abort `403` if customer not created by user.<br>**Admin User:** Can change status, soft delete, restore, or destroy any customer. |
| **Customer Export** | `admin-customer-export` (`CustomerController::export`) | `$this->queryCustomers($req)->get()` | **Regular User:** Exports only the user's own customers.<br>**Admin User:** Exports all customer records. |
| **Customer Report** | `admin-customer-report` (`CustomerController::report`) | `$this->queryCustomers($req)->get()` | **Regular User:** Returns only the user's own customers.<br>**Admin User:** Returns all customer records. |
| **Customer Lookup (Select2)** | `admin-select-customer` ([SelectController.php](file:///Users/longdy/Documents/Projects/Laravel/POSSystem/app/Http/Controllers/Admin/SelectController.php#L122)) | `Customer::where('status', 1)->take(12)->get()` | **Regular User:** Scoped strictly to user's own customers (+ walk-in option).<br>**Admin User:** Searches and selects all customers. |
| **Order Listing** | [OrderController.php](file:///Users/longdy/Documents/Projects/Laravel/POSSystem/app/Http/Controllers/Admin/OrderController.php#L56) `index()` | `Order::query()` with date/status filters | **Regular User:** Scoped to `orders.created_by = Auth::id()`.<br>**Admin User:** **Unrestricted** (all orders returned). |
| **Order Product Listing** | `admin-order-list-product` (`OrderController::product`) | `Order::with(['shop'])` | **Regular User:** Scoped to `orders.created_by = Auth::id()`.<br>**Admin User:** **Unrestricted** (all orders returned). |
| **Order Detail / Show** | `admin-order-detail` (`OrderController::show`) | `Order::withTrashed()->find($id)` | **Regular User:** Abort `403 Forbidden` if `order.created_by != Auth::id()`.<br>**Admin User:** Can view any order. |
| **Order Edit / Form** | `admin-order-edit` (`OrderController::edit`) | `Order::find($id)` | **Regular User:** Abort `403 Forbidden` if `order.created_by != Auth::id()`.<br>**Admin User:** Can edit any order. |
| **Order Save / Update** | `admin-order-save` (`OrderController::Save`) | `Order::findOrFail($orderId)` | **Regular User:** Abort `403` if editing unowned order. On insert: `$order->created_by = Auth::id()`. On edit: retain original `created_by`.<br>**Admin User:** Can update any order; original creator is preserved. |
| **Order Status & Cancel** | `admin-order-status`, `admin-order-cancel`, `update-payment-status` | `Order::where('id', $id)->firstOrFail()` | **Regular User:** Abort `403` if unowned order.<br>**Admin User:** Can update status or cancel any order. |
| **Order Delete / Restore / Destroy** | `admin-order-delete`, `restore`, `destroy` | `Order::findOrFail($id)` | **Regular User:** Abort `403` if unowned order.<br>**Admin User:** Can delete, restore, or destroy any order. |
| **Order Payments** | `payment-details`, `add-payment`, `update-payment`, `delete-payment` | Finds parent order by `$id` | **Regular User:** Abort `403` if parent order not owned by user.<br>**Admin User:** Can view/manage payments for any order. |
| **Order Payment Reminders** | `send-payment-reminder` | `Order::findOrFail($id)` | **Regular User:** Abort `403` if order not owned by user.<br>**Admin User:** Can send reminders for any order. |
| **Order Report & Excel Export** | `admin-order-report` (`OrderController::report`) | `OrderDetail::whereHas('order', ...)` | **Regular User:** Exports only the user's own orders.<br>**Admin User:** Exports orders across all users. |
| **Order Bulk Printing** | [OrderBulkPrintController.php](file:///Users/longdy/Documents/Projects/Laravel/POSSystem/app/Http/Controllers/Admin/OrderBulkPrintController.php) & [OrderBulkPrintService.php](file:///Users/longdy/Documents/Projects/Laravel/POSSystem/app/Services/OrderBulkPrintService.php) | Listing uses `BuildsOrderListingQuery`; service uses `whereIn('id', $ids)` | **Regular User:** Listing displays only user's orders; printing validates all IDs belong to user.<br>**Admin User:** Lists and bulk-prints any orders. |
| **Dashboard Metrics & Recent Orders** | [DashboardController.php](file:///Users/longdy/Documents/Projects/Laravel/POSSystem/app/Http/Controllers/Admin/DashboardController.php) `index()` | Aggregates `Order` total sales, pending count, remaining amount, `Customer` counts, recent orders, and top products | **Regular User:** Scoped via `Order::forUser()` and `Customer::forUser()` to show only the user's own sales, pending orders, remaining balances, customer counts, recent transactions, and top product sales.<br>**Admin User:** **Unrestricted** (displays global store-wide totals and all transactions across all users). |

*(Note: Other modules such as Staff Expenses, Sales/Transaction Reports, and Customer Remaining Amount Reports remain untouched).*

---

## 4. Architectural Approach

### 4.1 Model Query Scopes + Direct Controller Guards
To eliminate unnecessary files and avoid redundant boilerplate:
- **No separate Policy classes:** We do not create redundant `CustomerPolicy` or `OrderPolicy` classes.
- **Model Scopes (`scopeForUser`):** Centralizes query filtering for listings, searches, dropdowns, exports, and reports.
- **Direct Controller Guards (`$model->isOwnedBy(Auth::user())`):** When accessing, editing, updating, canceling, or deleting a specific record, the controller checks `$model->isOwnedBy(Auth::user())`. If unauthorized, it immediately aborts with `403 Forbidden`.

### 4.2 How the Query Scope Operates
On [Customer.php](file:///Users/longdy/Documents/Projects/Laravel/POSSystem/app/Models/Customer.php):
```php
public function scopeForUser(Builder $query, ?User $user = null): Builder
{
    $user = $user ?? auth()->user();

    // Full Data Access (data_access === 'all' or role === 'super_admin'): No filter applied
    if (!$user || $user->hasFullDataAccess()) {
        return $query;
    }

    // Own Records Only (data_access === 'own'): Restrict to user's created customers
    return $query->where('customers.user', (string) $user->id);
}
```

On [Order.php](file:///Users/longdy/Documents/Projects/Laravel/POSSystem/app/Models/Order.php):
```php
public function scopeForUser(Builder $query, ?User $user = null): Builder
{
    $user = $user ?? auth()->user();

    // Full Data Access (data_access === 'all' or role === 'super_admin'): No filter applied
    if (!$user || $user->hasFullDataAccess()) {
        return $query;
    }

    // Own Records Only (data_access === 'own'): Restrict to user's created orders
    return $query->where('orders.created_by', $user->id);
}
```

### 4.3 How the Single-Record Ownership Guard Operates
On [Customer.php](file:///Users/longdy/Documents/Projects/Laravel/POSSystem/app/Models/Customer.php):
```php
public function isOwnedBy(?User $user): bool
{
    if (!$user) return false;
    if ($user->hasFullDataAccess()) return true;
    return (string) $this->user === (string) $user->id;
}
```

On [Order.php](file:///Users/longdy/Documents/Projects/Laravel/POSSystem/app/Models/Order.php):
```php
public function isOwnedBy(?User $user): bool
{
    if (!$user) return false;
    if ($user->hasFullDataAccess()) return true;
    return (int) $this->created_by === (int) $user->id;
}
```

In Controllers:
```php
if (!$record->isOwnedBy(Auth::user())) {
    abort(403, 'Unauthorized access to record.');
}
```

---

## 5. Confirmed Business & Design Decisions

1. **Customer Selection Policy on Order Create/Edit:**
   - **Decision:** **Strictly restrict**. Regular users can only see and select customers they created (plus the default Walk-in Customer option where `customer_id` is null). They cannot select customers created by other regular users or Admins.
   - Admins can search and select all active customers across the store.
2. **Legacy Record Handling (`created_by IS NULL` or `user IS NULL`):**
   - **Decision:** **Exclude from regular users and backfill to Admin**. Legacy orders and customers without creator information will be invisible to regular users. A migration/script will assign legacy records to Admin (User ID 1).
3. **Creator Immutability on Edit:**
   - **Decision:** On edit/update of an existing order or customer, the original `created_by` / `user` must never be overwritten.

---

## 6. Step-by-Step Implementation Plan

### Phase 1: Foundation, User Helper & Model Scopes
- [x] **Step 1.1 — Update [User.php](file:///Users/longdy/Documents/Projects/Laravel/POSSystem/app/Models/User.php):**
  - Add `isAdmin(): bool`:
    ```php
    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin'], true);
    }
    ```
- [x] **Step 1.2 — Update [Customer.php](file:///Users/longdy/Documents/Projects/Laravel/POSSystem/app/Models/Customer.php):**
  - Add `createdBy()` relationship:
    ```php
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'user')->withTrashed();
    }
    ```
  - Add `scopeForUser(Builder $query, ?User $user = null): Builder`.
  - Add `isOwnedBy(?User $user): bool`.
- [x] **Step 1.3 — Update [Order.php](file:///Users/longdy/Documents/Projects/Laravel/POSSystem/app/Models/Order.php):**
  - Add `'created_by'` to `$fillable`.
  - Add `scopeForUser(Builder $query, ?User $user = null): Builder`.
  - Add `isOwnedBy(?User $user): bool`.

---

### Phase 2: Customers Module Access Control
- [x] **Step 2.1 — Update [CustomerController.php](file:///Users/longdy/Documents/Projects/Laravel/POSSystem/app/Http/Controllers/Admin/CustomerController.php):**
  - In `queryCustomers()`: Apply `Customer::forUser()->where(...)`.
    - **Admin Users:** `Customer::forUser()` applies **no user filter** (returns all customers).
    - **Regular Users:** `Customer::forUser()` adds `WHERE customers.user = Auth::id()`.
  - In `onEdit($req)`:
    ```php
    $customer = Customer::findOrFail($req->id);
    if (!$customer->isOwnedBy(Auth::user())) {
        abort(403, 'Unauthorized access to customer.');
    }
    ```
  - In `Save()`:
    - If updating an existing customer (`$id` provided): verify `$customer->isOwnedBy(Auth::user())`, aborting 403 if unowned.
    - Preserve creator on update: ensure the original `user` value is retained and never overwritten with the updater's ID.
  - In `updateStatus`, `delete`, `restore`, `destroy`:
    - Find customer, check `$customer->isOwnedBy(Auth::user())`, abort 403 if unauthorized.
- [x] **Step 2.2 — Update Customer Lookups in [SelectController.php](file:///Users/longdy/Documents/Projects/Laravel/POSSystem/app/Http/Controllers/Admin/SelectController.php):**
  - In `customer()`: Apply `Customer::forUser()->where('status', 1)...`.
  - In `selectCustomer(Request $req)`: Apply `Customer::forUser()->where('status', 1)...`.

---

### Phase 3: Orders Module Access Control
- [x] **Step 3.1 — Update [BuildsOrderListingQuery.php](file:///Users/longdy/Documents/Projects/Laravel/POSSystem/app/Http/Controllers/Admin/Traits/BuildsOrderListingQuery.php):**
  - In `buildOrderListingQuery()`: Chain `->forUser()`.
    - **Admin Users:** Returns all orders across all creators.
    - **Regular Users:** Adds `WHERE orders.created_by = Auth::id()`.
- [x] **Step 3.2 — Update [OrderController.php](file:///Users/longdy/Documents/Projects/Laravel/POSSystem/app/Http/Controllers/Admin/OrderController.php):**
  - In `product()` (Product-based order view): Add `Order::forUser()->with(...)`.
  - In `show($id)`:
    ```php
    $order = Order::withTrashed()->find($id);
    if (!$order || !$order->isOwnedBy(Auth::user())) {
        abort(403, 'Unauthorized access to order.');
    }
    ```
  - In `edit($id)`:
    ```php
    $order = Order::find($id);
    if (!$order || !$order->isOwnedBy(Auth::user())) {
        abort(403, 'Unauthorized access to order.');
    }
    ```
  - In `Save()`:
    - If updating an existing order (`$orderId`): verify `$order->isOwnedBy(Auth::user())`, aborting 403 if unowned.
    - Fix creator assignment on line 244 so it only runs on creation:
      ```php
      if (!$order->exists || empty($order->created_by)) {
          $order->created_by = Auth::id();
      }
      ```
  - In `delete($id)`, `restore($id)`, `destroy($id)`:
    - Verify `$order->isOwnedBy(Auth::user())`, aborting 403 if unauthorized.
  - In `updateStatus($id)`, `cancelOrder($id)`, `updatePaymentStatus($id)`:
    - Verify `$order->isOwnedBy(Auth::user())`, aborting 403 if unauthorized.
  - In `getPaymentDetails($id)`, `addPayment($req, $id)`:
    - Verify parent order `$order->isOwnedBy(Auth::user())`, aborting 403 if unauthorized.
  - In `updatePayment($req, $paymentId)`, `deletePayment($paymentId)`:
    - Find payment, load order, verify `$order->isOwnedBy(Auth::user())`, aborting 403 if unauthorized.
  - In `sendPaymentReminder($id)`:
    - Verify `$order->isOwnedBy(Auth::user())`, aborting 403 if unauthorized.
  - In `report(Request $req)` (Order Excel Export):
    - In `whereHas('order', ...)` add:
      ```php
      $query->forUser();
      ```
- [x] **Step 3.3 — Update [OrderBulkPrintService.php](file:///Users/longdy/Documents/Projects/Laravel/POSSystem/app/Services/OrderBulkPrintService.php):**
  - In `getOrdersForPrint($orderIds)`:
    - Apply `Order::forUser()->whereIn('id', $distinctIds)...`.
    - If a regular user submits order IDs that do not belong to them, reject with validation error or 403.

---

### Phase 4: Dashboard Restriction (Regular User vs Admin)

#### 4.1 Access Control Rule for Dashboard:
- **Admin Users (`super_admin`, `admin`):**
  - **Shows All Data Store-Wide:** Admins see 100% of all store revenue, all pending orders, all unpaid remaining balances, all registered customers across all staff, all recent transactions from every cashier, and store-wide top-selling products.
  - **Query Mechanism:** `Order::forUser()` and `Customer::forUser()` detect `$user->isAdmin() === true` and **apply NO user filter**, returning all records without adding any `WHERE created_by` clause.
- **Normal / Regular Users (Cashier, Staff, Manager):**
  - **Strictly Restricted to Own Data:** Normal users only see summary numbers for orders and customers **they created**.
  - **Query Mechanism:** `Order::forUser()` and `Customer::forUser()` detect `$user->isAdmin() === false` and automatically attach `WHERE orders.created_by = Auth::id()` and `WHERE customers.user = Auth::id()`.

- [x] **Step 4.1 — Update [DashboardController.php](file:///Users/longdy/Documents/Projects/Laravel/POSSystem/app/Http/Controllers/Admin/DashboardController.php):**
  - Scope `totalOrderRemainingAmount`:
    ```php
    // Admin: Shows total unpaid balance store-wide | Normal User: Shows unpaid balance only for orders created by this user
    $data['totalOrderRemainingAmount'] = (float) Order::forUser()
        ->whereIn('payment_status', ['Pending', 'Partial'])
        ->sum(DB::raw('GREATEST(0, total_price - COALESCE(paid_amount, 0))'));
    ```
  - Scope `remainingOrderCount`:
    ```php
    // Admin: Shows total outstanding order count store-wide | Normal User: Only counts orders created by this user
    $data['remainingOrderCount'] = (int) Order::forUser()
        ->whereIn('payment_status', ['Pending', 'Partial'])
        ->whereRaw('(total_price - COALESCE(paid_amount, 0)) > 0')
        ->count();
    ```
  - Scope `totalOrderPrice`:
    ```php
    // Admin: Shows total revenue store-wide across all staff | Normal User: Shows revenue only for orders created by this user
    $data['totalOrderPrice'] = (float) Order::forUser()->sum('total_price');
    ```
  - Scope `pendingOrderCount`:
    ```php
    // Admin: Shows count of all pending orders store-wide | Normal User: Counts only pending orders created by this user
    $data['pendingOrderCount'] = (int) Order::forUser()->where('payment_status', 'Pending')->count();
    ```
  - Scope Customer counts:
    ```php
    // Admin: Shows total customer count store-wide | Normal User: Counts only customers created by this user
    $data['totalCustomerCount'] = (int) Customer::forUser()->count();
    $data['activeCustomerCount'] = (int) Customer::forUser()->where('status', 1)->count();
    ```
  - Scope Recent Transactions:
    ```php
    // Admin: Shows 6 most recent orders store-wide across all staff | Normal User: Shows only 6 recent orders created by this user
    $data['recentTransactions'] = Order::forUser()
        ->with(['customer', 'shop'])
        ->latest('id')
        ->take(6)
        ->get();
    ```
  - Scope Top Product Performance (`getProductPerformanceData`):
    ```php
    // Admin: Shows top products store-wide | Normal User: Shows top products from this user's orders
    if (Auth::check() && !Auth::user()->isAdmin()) {
        $query->where('orders.created_by', Auth::id());
    }
    ```

---

### Phase 5: Automated Testing & Verification
- [x] **Step 5.1 — Create `tests/Feature/RecordLevelAccessTest.php`:**
  - Test Regular User cannot see other users' customers in listing, export, or report.
  - Test Regular User receives `403 Forbidden` accessing `/admin/customer/edit/{id}` or `POST save/{id}` for an unowned customer.
  - Test Regular User cannot see other users' orders in listing, product listing, or bulk printing.
  - Test Regular User receives `403 Forbidden` accessing `/admin/order/detail/{id}`, `/admin/order/edit/{id}`, or `/admin/order/delete/{id}` for an unowned order.
  - Test Admin User sees and manages all customer and order records regardless of creator.
  - Test updating an existing order preserves the original `created_by`.
  - Test customer lookup in Select2 only returns owned customers for regular users.
  - Test Dashboard metrics and recent transactions isolate data for regular users while showing global totals for admins.

---

## 7. Database Considerations & Legacy Backfill Strategy

> **CRITICAL RULE:** Do NOT execute any database scripts or modify existing database tables until this plan is formally approved.

### 7.1 Existing Schema Compatibility
The solution works immediately with the current database schema:
- `orders.created_by` (unsignedBigInteger, index) already exists.
- `customers.user` (varchar) already exists and stores user IDs.

### 7.2 Legacy Records Backfill Script (Prepared for Execution)
For legacy orders and customers created prior to 23 October 2026 where creator is `NULL`:
```php
// Assign legacy orphaned records to the primary Admin (ID 1)
Order::whereNull('created_by')->update(['created_by' => 1]);
Customer::whereNull('user')->orWhere('user', '')->update(['user' => '1']);
```

---

## 8. Expected Results Matrix

| Scenario / Action | Regular User (e.g. Cashier / Staff) | Admin User (`super_admin` / `admin`) |
| :--- | :--- | :--- |
| **Customer List Page** | Displays only customers created by this user. | Displays all customers across all users. |
| **Customer Search & Filters** | Searches only within this user's customers. | Searches across all customers. |
| **Customer Export & Reports** | Exports and aggregates only this user's customers. | Exports and aggregates all customers. |
| **Direct URL to Unowned Customer** (`/admin/customer/edit/99`) | **`403 Forbidden`** | Opens edit form successfully. |
| **Direct POST to Unowned Customer** (`/admin/customer/save/99`) | **`403 Forbidden`** | Saves updates successfully. |
| **Customer Select2 on Order Create** | Shows only customers created by this user (+ walk-in). | Shows all active customers. |
| **Order List Page** | Displays only orders created by this user. | Displays all orders across all users. |
| **Direct URL to Unowned Order** (`/admin/order/detail/50`) | **`403 Forbidden`** | Opens order detail page. |
| **Direct URL to Edit Unowned Order** (`/admin/order/edit/50`) | **`403 Forbidden`** | Opens order edit page. |
| **Direct POST to Delete Unowned Order** | **`403 Forbidden`** | Deletes order successfully. |
| **Add / Edit Payment on Unowned Order** | **`403 Forbidden`** | Allows adding/editing payments. |
| **Order Bulk Print Listing & Preview** | Lists and prints only orders created by this user. | Lists and prints all selected orders. |
| **Order Excel Export** | Exports only orders created by this user. | Exports orders across all users. |
| **Dashboard Total Order Sales (`totalOrderPrice`)** | Sum of sales for orders created by this user. | Total store-wide order sales across all users. |
| **Dashboard Pending Count (`pendingOrderCount`)** | Count of pending orders created by this user. | Store-wide count of pending orders. |
| **Dashboard Remaining Amount (`totalOrderRemainingAmount`)** | Unpaid balance for orders created by this user. | Store-wide unpaid balance across all orders. |
| **Dashboard Customer Counts** | Counts customers created by this user. | Counts all customers store-wide. |
| **Dashboard Recent Transactions Table** | Shows 6 most recent orders created by this user. | Shows 6 most recent orders store-wide. |
| **Dashboard Top Product Performance** | Top selling products from this user's orders. | Top selling products store-wide. |
| **Record Editing Creator Tracking** | Original creator remains unchanged after edit. | Original creator remains unchanged after edit. |
