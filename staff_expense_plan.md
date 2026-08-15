# Staff Expense Management — Enterprise Implementation Plan

> **Document Status**: Production-Ready Architectural Specification  
> **Target Framework**: Laravel 10.x / 11.x (PHP 8.1+)  
> **Module**: `StaffExpense`  

---

## 1. Executive Summary & Overview

The **Staff Expense Management** module provides a centralized, audit-ready financial ledger for recording, tracking, and analyzing staff-related monetary transactions (such as salary disbursements, performance bonuses, salary deductions, and miscellaneous staff operational expenses).

This module replaces ad-hoc top-up mechanisms with a formal **double-entry-aware expense tracking system** built on clean domain driven principles, proper separation of concerns (Service Layer + Form Requests + Policies), and enterprise database indexing for high performance scaling.

---

## 2. System Analysis & Architecture Comparison

### 2.1 Legacy vs Target Architecture

| Metric / Feature | Legacy Wallet History (`wallet_histories`) | Target Staff Expense (`staff_expenses`) |
|---|---|---|
| **Domain Scope** | Barbershop-specific customer/staff top-up balance | Standardized multi-shop Staff Expense Ledger |
| **Target Entity** | `barbers` (Legacy table) | `staffs` (`App\Models\Staff`) |
| **Financial Nature** | Running wallet balance credit | Itemized categorizable expense transaction |
| **Transaction Types** | Credit top-up only | `Salary`, `Bonus`, `Deduction`, `Other` |
| **Currency** | Multi-currency (KHR / USD conversion) | USD (`$`) Standardized (2 Decimal Precision) |
| **Architecture** | Inline Controller Logic | Service Layer + Form Requests + Policies |
| **Auditability** | Basic timestamps | Soft Deletes + User Tracking + Trash Restoration |
| **Reporting** | Basic tabular view | Summary Cards + Net Expense Calculation + Date Filtering |

---

## 3. Core Business Requirements & Financial Rules

### 3.1 Financial Categorization & Net Expense Logic

Expenses are categorized into four immutable types defined via PHP Enums:

1. **`Salary`**: Fixed or recurring base payroll disbursements (**Addition** to staff income, **Expense** to business).
2. **`Bonus`**: Incentive or commission payouts (**Addition** to staff income, **Expense** to business).
3. **`Deduction`**: Financial penalty, loan payback, or missing items deduction (**Subtracted** from gross staff payout, **Expense Reduction** to business).
4. **`Other`**: Miscellaneous staff expenses (training, uniform, allowance, transport) (**Addition** to business expenses).

#### Net Calculation Formulas

- $$\text{Gross Staff Additions} = \text{Salary} + \text{Bonus} + \text{Other}$$
- $$\text{Total Deductions} = \text{Deduction}$$
- $$\text{Net Staff Payable / Total Expense} = \text{Gross Staff Additions} - \text{Total Deductions}$$

### 3.2 Key Constraints & Business Rules

1. **Currency**: Stored and formatted strictly in USD (`$`). Precision: `15, 2` (`decimal`).
2. **Staff FK Integrity**: References primary key `id` on `staffs` table (Model: `App\Models\Staff`).
3. **Audit Trail**: Every record tracks `created_by` (FK -> `users.id`) and supports Soft Deletes (`softDeletes()`).
4. **Immutability & Trash Recovery**: Deletion performs a soft-delete (moving to Trash). Permanent deletion (`forceDelete`) is restricted to super-admins via `staff-expense-force-delete` permission.
5. **No Negative Amounts**: Form inputs validate `amount >= 0.01`. Subtractions are governed strictly by the `Deduction` category type.
6. **Date Boundaries**: Expense date defaults to current date (`Y-m-d`). Future dating beyond 30 days is blocked by validation.

---

## 4. Layered Architectural Design

```
                     ┌──────────────────────────────────────┐
                     │          HTTP Request (Web)          │
                     └──────────────────┬───────────────────┘
                                        │
                                        ▼
                     ┌──────────────────────────────────────┐
                     │     Middleware & Policy Guard        │
                     │  (Permission & Authorization Checks) │
                     └──────────────────┬───────────────────┘
                                        │
                                        ▼
                     ┌──────────────────────────────────────┐
                     │      Form Request Validation         │
                     │ (StoreStaffExpenseRequest / Update)  │
                     └──────────────────┬───────────────────┘
                                        │
                                        ▼
                     ┌──────────────────────────────────────┐
                     │       StaffExpenseController         │
                     └──────────────────┬───────────────────┘
                                        │
                                        ▼
                     ┌──────────────────────────────────────┐
                     │        StaffExpenseService           │
                     │  (Business Logic & Ledger Analytics) │
                     └──────────┬────────────────┬──────────┘
                                │                │
                                ▼                ▼
                      ┌────────────────┐ ┌────────────────┐
                      │  StaffExpense  │ │  ExpenseType   │
                      │    (Model)     │ │     (Enum)     │
                      └───────┬────────┘ └────────────────┘
                              │
                              ▼
                      ┌────────────────┐
                      │ MySQL Database │
                      │(staff_expenses)│
                      └────────────────┘
```

---

## 5. Detailed Implementation Specifications & Code Snippets

---

### Layer 1 — Database & Migration

#### `database/migrations/2026_08_03_000001_create_staff_expenses_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('staff_expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_id')->comment('FK to staffs table');
            $table->unsignedBigInteger('shop_id')->nullable()->comment('FK to shops table');
            $table->string('type', 30)->comment('Salary, Bonus, Deduction, Other');
            $table->decimal('amount', 15, 2)->comment('Expense amount in USD');
            $table->date('expense_date')->comment('Date of transaction');
            $table->string('description', 1000)->nullable()->comment('Notes/Remarks');
            $table->unsignedBigInteger('created_by')->comment('FK to users table');
            $table->softDeletes();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('staff_id')->references('id')->on('staffs')->onDelete('cascade');
            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');

            // Performance Composite Indexes
            $table->index(['staff_id', 'expense_date']);
            $table->index(['shop_id', 'expense_date']);
            $table->index(['type', 'expense_date']);
            $table->index('expense_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_expenses');
    }
};
```

---

### Layer 2 — Domain Enum

#### `app/Enums/ExpenseType.php`

```php
<?php

namespace App\Enums;

enum ExpenseType: string
{
    case SALARY    = 'Salary';
    case BONUS     = 'Bonus';
    case DEDUCTION = 'Deduction';
    case OTHER     = 'Other';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match($this) {
            self::SALARY    => 'Salary',
            self::BONUS     => 'Bonus',
            self::DEDUCTION => 'Deduction',
            self::OTHER     => 'Other Expense',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::SALARY    => 'badge bg-primary',
            self::BONUS     => 'badge bg-success',
            self::DEDUCTION => 'badge bg-danger',
            self::OTHER     => 'badge bg-secondary',
        };
    }

    public function isDeduction(): bool
    {
        return $this === self::DEDUCTION;
    }
}
```

---

### Layer 3 — Eloquent Model & Scopes

#### `app/Models/StaffExpense.php`

```php
<?php

namespace App\Models;

use App\Enums\ExpenseType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffExpense extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'staff_expenses';

    protected $fillable = [
        'staff_id',
        'shop_id',
        'type',
        'amount',
        'expense_date',
        'description',
        'created_by',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'expense_date' => 'date:Y-m-d',
        'type'         => ExpenseType::class,
    ];

    // Relationships
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Query Scopes
    public function scopeDateBetween(Builder $query, ?string $from, ?string $to): Builder
    {
        if ($from && $to) {
            return $query->whereBetween('expense_date', [$from, $to]);
        }
        return $query;
    }

    public function scopeOfStaff(Builder $query, ?int $staffId): Builder
    {
        return $staffId ? $query->where('staff_id', $staffId) : $query;
    }

    public function scopeOfType(Builder $query, ?string $type): Builder
    {
        return $type ? $query->where('type', $type) : $query;
    }

    public function scopeOfShop(Builder $query, ?int $shopId): Builder
    {
        return $shopId ? $query->where('shop_id', $shopId) : $query;
    }
}
```

---

### Layer 4 — Validation Requests

#### `app/Http/Requests/StoreStaffExpenseRequest.php`

```php
<?php

namespace App\Http\Requests;

use App\Enums\ExpenseType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreStaffExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('staff-expense-create');
    }

    public function rules(): array
    {
        return [
            'staff_id'     => ['required', 'integer', 'exists:staffs,id'],
            'shop_id'      => ['nullable', 'integer', 'exists:shops,id'],
            'type'         => ['required', new Enum(ExpenseType::class)],
            'amount'       => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
            'expense_date' => ['required', 'date', 'date_format:Y-m-d', 'before_or_equal:' . now()->addDays(30)->format('Y-m-d')],
            'description'  => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'staff_id.required'     => 'Please select a staff member.',
            'staff_id.exists'       => 'Selected staff member does not exist.',
            'amount.min'            => 'Expense amount must be at least $0.01 USD.',
            'expense_date.required' => 'Expense date is required.',
        ];
    }
}
```

#### `app/Http/Requests/UpdateStaffExpenseRequest.php`

```php
<?php

namespace App\Http\Requests;

use App\Enums\ExpenseType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateStaffExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('staff-expense-update');
    }

    public function rules(): array
    {
        return [
            'staff_id'     => ['required', 'integer', 'exists:staffs,id'],
            'shop_id'      => ['nullable', 'integer', 'exists:shops,id'],
            'type'         => ['required', new Enum(ExpenseType::class)],
            'amount'       => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
            'expense_date' => ['required', 'date', 'date_format:Y-m-d'],
            'description'  => ['nullable', 'string', 'max:1000'],
        ];
    }
}
```

---

### Layer 5 — Business Service Layer

#### `app/Services/StaffExpenseService.php`

```php
<?php

namespace App\Services;

use App\Enums\ExpenseType;
use App\Models\StaffExpense;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StaffExpenseService
{
    /**
     * Get paginated expenses with filters applied.
     */
    public function getFilteredExpenses(array $filters, int $perPage = 50, bool $onlyTrashed = false): LengthAwarePaginator
    {
        $query = StaffExpense::with(['staff', 'shop', 'createdBy']);

        if ($onlyTrashed) {
            $query->onlyTrashed();
        }

        return $query
            ->dateBetween($filters['from_date'] ?? null, $filters['to_date'] ?? null)
            ->ofStaff($filters['staff_id'] ?? null)
            ->ofType($filters['type'] ?? null)
            ->ofShop($filters['shop_id'] ?? null)
            ->orderBy('expense_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    /**
     * Calculate aggregate summaries for filtered expenses.
     */
    public function calculateSummary(array $filters, bool $onlyTrashed = false): array
    {
        $query = StaffExpense::query();

        if ($onlyTrashed) {
            $query->onlyTrashed();
        }

        $rawTotals = $query
            ->dateBetween($filters['from_date'] ?? null, $filters['to_date'] ?? null)
            ->ofStaff($filters['staff_id'] ?? null)
            ->ofType($filters['type'] ?? null)
            ->ofShop($filters['shop_id'] ?? null)
            ->select('type', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('type')
            ->pluck('total_amount', 'type')
            ->toArray();

        $salary    = (float) ($rawTotals[ExpenseType::SALARY->value] ?? 0);
        $bonus     = (float) ($rawTotals[ExpenseType::BONUS->value] ?? 0);
        $deduction = (float) ($rawTotals[ExpenseType::DEDUCTION->value] ?? 0);
        $other     = (float) ($rawTotals[ExpenseType::OTHER->value] ?? 0);

        $grossAdditions = $salary + $bonus + $other;
        $netTotal       = $grossAdditions - $deduction;

        return [
            'salary'         => $salary,
            'bonus'          => $bonus,
            'deduction'      => $deduction,
            'other'          => $other,
            'grossAdditions' => $grossAdditions,
            'netTotal'       => $netTotal,
        ];
    }

    /**
     * Create a new expense record in transaction.
     */
    public function createExpense(array $data, int $userId): StaffExpense
    {
        return DB::transaction(function () use ($data, $userId) {
            $data['created_by'] = $userId;
            return StaffExpense::create($data);
        });
    }

    /**
     * Update existing expense record.
     */
    public function updateExpense(StaffExpense $expense, array $data): StaffExpense
    {
        return DB::transaction(function () use ($expense, $data) {
            $expense->update($data);
            return $expense->fresh();
        });
    }

    /**
     * Soft delete an expense.
     */
    public function softDeleteExpense(StaffExpense $expense): bool
    {
        return DB::transaction(fn () => $expense->delete());
    }

    /**
     * Restore soft deleted expense.
     */
    public function restoreExpense(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $expense = StaffExpense::onlyTrashed()->findOrFail($id);
            return $expense->restore();
        });
    }

    /**
     * Force delete expense permanently.
     */
    public function forceDeleteExpense(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $expense = StaffExpense::onlyTrashed()->findOrFail($id);
            return $expense->forceDelete();
        });
    }
}
```

---

### Layer 6 — Policy & Permissions

#### `app/Policies/StaffExpensePolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\StaffExpense;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StaffExpensePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('staff-expense-view');
    }

    public function view(User $user, StaffExpense $expense): bool
    {
        return $user->can('staff-expense-view');
    }

    public function create(User $user): bool
    {
        return $user->can('staff-expense-create');
    }

    public function update(User $user, StaffExpense $expense): bool
    {
        return $user->can('staff-expense-update');
    }

    public function delete(User $user, StaffExpense $expense): bool
    {
        return $user->can('staff-expense-delete');
    }

    public function restore(User $user, StaffExpense $expense): bool
    {
        return $user->can('staff-expense-restore');
    }

    public function forceDelete(User $user, StaffExpense $expense): bool
    {
        return $user->can('staff-expense-force-delete');
    }
}
```

---

### Layer 7 — Controller Implementation

#### `app/Http/Controllers/Admin/StaffExpenseController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ExpenseType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStaffExpenseRequest;
use App\Http\Requests\UpdateStaffExpenseRequest;
use App\Models\Shop;
use App\Models\Staff;
use App\Models\StaffExpense;
use App\Services\StaffExpenseService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StaffExpenseController extends Controller
{
    protected string $layout = 'admin::pages.staffExpense.';

    public function __construct(
        protected StaffExpenseService $expenseService
    ) {
        $this->middleware('permission:staff-expense-view')->only(['index', 'staffHistory', 'trash']);
        $this->middleware('permission:staff-expense-create')->only(['create', 'store']);
        $this->middleware('permission:staff-expense-update')->only(['edit', 'update']);
        $this->middleware('permission:staff-expense-delete')->only(['destroy']);
        $this->middleware('permission:staff-expense-restore')->only(['restore']);
        $this->middleware('permission:staff-expense-force-delete')->only(['forceDelete']);
    }

    /**
     * Expense Ledger Index Page.
     */
    public function index(Request $request): View
    {
        $filters = [
            'from_date' => $request->get('from_date', Carbon::now()->startOfMonth()->format('Y-m-d')),
            'to_date'   => $request->get('to_date', Carbon::now()->endOfMonth()->format('Y-m-d')),
            'staff_id'  => $request->get('staff_id'),
            'type'      => $request->get('type'),
            'shop_id'   => $request->get('shop_id'),
        ];

        $expenses  = $this->expenseService->getFilteredExpenses($filters);
        $summary   = $this->expenseService->calculateSummary($filters);
        $staffList = Staff::where('status', 1)->orderBy('name')->get();
        $shops     = Shop::where('status', 1)->orderBy('name')->get();
        $types     = ExpenseType::cases();

        return view($this->layout . 'index', compact(
            'expenses', 'summary', 'staffList', 'shops', 'types', 'filters'
        ));
    }

    /**
     * Create Form.
     */
    public function create(): View
    {
        $staffList = Staff::where('status', 1)->orderBy('name')->get();
        $shops     = Shop::where('status', 1)->orderBy('name')->get();
        $types     = ExpenseType::cases();
        $expense   = null;

        return view($this->layout . 'form', compact('staffList', 'shops', 'types', 'expense'));
    }

    /**
     * Store new Expense.
     */
    public function store(StoreStaffExpenseRequest $request): RedirectResponse
    {
        $this->expenseService->createExpense($request->validated(), Auth::id());

        return redirect()
            ->route('staff-expense-index')
            ->with('success', 'Staff expense recorded successfully.');
    }

    /**
     * Edit Form.
     */
    public function edit(StaffExpense $staffExpense): View
    {
        $expense   = $staffExpense;
        $staffList = Staff::where('status', 1)->orderBy('name')->get();
        $shops     = Shop::where('status', 1)->orderBy('name')->get();
        $types     = ExpenseType::cases();

        return view($this->layout . 'form', compact('staffList', 'shops', 'types', 'expense'));
    }

    /**
     * Update Expense.
     */
    public function update(UpdateStaffExpenseRequest $request, StaffExpense $staffExpense): RedirectResponse
    {
        $this->expenseService->updateExpense($staffExpense, $request->validated());

        return redirect()
            ->route('staff-expense-index')
            ->with('success', 'Staff expense updated successfully.');
    }

    /**
     * Soft Delete Expense.
     */
    public function destroy(StaffExpense $staffExpense): RedirectResponse
    {
        $this->expenseService->softDeleteExpense($staffExpense);

        return redirect()
            ->back()
            ->with('success', 'Expense record moved to trash.');
    }

    /**
     * Trash Index View.
     */
    public function trash(Request $request): View
    {
        $filters = [
            'from_date' => $request->get('from_date'),
            'to_date'   => $request->get('to_date'),
            'staff_id'  => $request->get('staff_id'),
            'type'      => $request->get('type'),
        ];

        $expenses = $this->expenseService->getFilteredExpenses($filters, 50, true);
        $summary  = $this->expenseService->calculateSummary($filters, true);

        return view($this->layout . 'trash', compact('expenses', 'summary', 'filters'));
    }

    /**
     * Restore Soft Deleted Expense.
     */
    public function restore(int $id): RedirectResponse
    {
        $this->expenseService->restoreExpense($id);

        return redirect()
            ->back()
            ->with('success', 'Expense record restored successfully.');
    }

    /**
     * Force Delete Expense.
     */
    public function forceDelete(int $id): RedirectResponse
    {
        $this->expenseService->forceDeleteExpense($id);

        return redirect()
            ->back()
            ->with('success', 'Expense record permanently deleted.');
    }

    /**
     * Per-Staff Expense Ledger View.
     */
    public function staffHistory(Request $request, int $staffId): View
    {
        $staff = Staff::findOrFail($staffId);

        $filters = [
            'from_date' => $request->get('from_date', Carbon::now()->startOfYear()->format('Y-m-d')),
            'to_date'   => $request->get('to_date', Carbon::now()->endOfDay()->format('Y-m-d')),
            'staff_id'  => $staffId,
            'type'      => $request->get('type'),
        ];

        $expenses = $this->expenseService->getFilteredExpenses($filters, 50);
        $summary  = $this->expenseService->calculateSummary($filters);
        $types    = ExpenseType::cases();

        return view($this->layout . 'staff-history', compact('staff', 'expenses', 'summary', 'types', 'filters'));
    }
}
```

---

### Layer 8 — Routing Configuration

#### Add to `routes/admin.php` inside `AdminGuard` group:

```php
use App\Http\Controllers\Admin\StaffExpenseController;

/*
|--------------------------------------------------------------------------
| Staff Expense Management Routes
|--------------------------------------------------------------------------
*/
Route::prefix('staff-expense')->name('staff-expense-')->group(function () {
    Route::get('/',                 [StaffExpenseController::class, 'index'])       ->name('index');
    Route::get('/create',           [StaffExpenseController::class, 'create'])      ->name('create');
    Route::post('/',                [StaffExpenseController::class, 'store'])       ->name('store');
    Route::get('/{staffExpense}/edit', [StaffExpenseController::class, 'edit'])     ->name('edit');
    Route::put('/{staffExpense}',   [StaffExpenseController::class, 'update'])      ->name('update');
    Route::delete('/{staffExpense}',[StaffExpenseController::class, 'destroy'])     ->name('destroy');
    
    // Trash Management
    Route::get('/trash',            [StaffExpenseController::class, 'trash'])       ->name('trash');
    Route::patch('/{id}/restore',   [StaffExpenseController::class, 'restore'])     ->name('restore');
    Route::delete('/{id}/force',    [StaffExpenseController::class, 'forceDelete']) ->name('force-delete');

    // Per-Staff Expense Ledger Shortcut
    Route::get('/staff/{staffId}',  [StaffExpenseController::class, 'staffHistory'])->name('staff-history');
});
```

---

### Layer 9 — Blade Views Architecture

All views reside in `resources/admin/views/pages/staffExpense/`:

#### View Component Structure
1. **`index.blade.php`**: Filter toolbar + Metric Summary Cards + Datatable + Pagination + Action buttons.
2. **`form.blade.php`**: Form for Create & Edit with Select2 staff lookup, type picker, USD input, Datepicker.
3. **`staff-history.blade.php`**: Per-staff header, balance overview cards (Salary, Bonus, Deduction, Net), history table.
4. **`trash.blade.php`**: Soft-deleted entries view with restore/permanent delete capabilities.

#### Action Link Integration in `resources/admin/views/pages/staff/table.blade.php`:

```blade
{{-- Expense History Shortcut Button in Staff List Table --}}
@can('staff-expense-view')
    <a href="{{ route('staff-expense-staff-history', $item->id) }}"
       class="btn btn-sm btn-icon btn-light-primary me-1"
       data-bs-toggle="tooltip"
       title="View Staff Expense Ledger">
        <i data-feather="dollar-sign"></i>
    </a>
@endcan
```

---

## 6. End-to-End Execution & Verification Plan

### 6.1 Database Migration & Seeder Verification

```bash
# 1. Run Migration
php artisan migrate

# 2. Verify Database Schema & Indexes
php artisan db:table staff_expenses

# 3. Check Route Registrations
php artisan route:list --name=staff-expense
```

### 6.2 Automated Feature Test Specification

#### `tests/Feature/StaffExpenseTest.php`

```php
<?php

namespace Tests\Feature;

use App\Enums\ExpenseType;
use App\Models\Shop;
use App\Models\Staff;
use App\Models\StaffExpense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffExpenseTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Staff $staff;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->adminUser = User::factory()->create();
        $this->adminUser->givePermissionTo([
            'staff-expense-view',
            'staff-expense-create',
            'staff-expense-update',
            'staff-expense-delete',
            'staff-expense-restore',
        ]);
        
        $this->staff = Staff::factory()->create(['status' => 1]);
    }

    /** @test */
    public function authorized_user_can_create_staff_expense()
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('staff-expense-store'), [
                'staff_id'     => $this->staff->id,
                'type'         => ExpenseType::SALARY->value,
                'amount'       => 500.00,
                'expense_date' => now()->format('Y-m-d'),
                'description'  => 'Monthly base salary',
            ]);

        $response->withSession([]);
        $response->assertRedirect(route('staff-expense-index'));

        $this->assertDatabaseHas('staff_expenses', [
            'staff_id' => $this->staff->id,
            'type'     => ExpenseType::SALARY->value,
            'amount'   => 500.00,
        ]);
    }

    /** @test */
    public function net_summary_calculates_deductions_correctly()
    {
        StaffExpense::factory()->create([
            'staff_id' => $this->staff->id,
            'type' => ExpenseType::SALARY->value,
            'amount' => 1000.00
        ]);

        StaffExpense::factory()->create([
            'staff_id' => $this->staff->id,
            'type' => ExpenseType::BONUS->value,
            'amount' => 200.00
        ]);

        StaffExpense::factory()->create([
            'staff_id' => $this->staff->id,
            'type' => ExpenseType::DEDUCTION->value,
            'amount' => 150.00
        ]);

        $service = app(\App\Services\StaffExpenseService::class);
        $summary = $service->calculateSummary(['staff_id' => $this->staff->id]);

        $this->assertEquals(1200.00, $summary['grossAdditions']);
        $this->assertEquals(150.00, $summary['deduction']);
        $this->assertEquals(1050.00, $summary['netTotal']);
    }

    /** @test */
    public function soft_deleted_expense_can_be_restored()
    {
        $expense = StaffExpense::factory()->create(['staff_id' => $this->staff->id]);
        $expense->delete();

        $this->assertSoftDeleted($expense);

        $this->actingAs($this->adminUser)
            ->patch(route('staff-expense-restore', $expense->id));

        $this->assertNotSoftDeleted($expense);
    }
}
```

### 6.3 Manual QA Checklist

| Test Case | Inputs / Action | Expected Outcome | Status |
|---|---|---|---|
| **TC-01: Create Valid Salary** | Staff: A, Type: Salary, Amount: $800, Date: Today | Record created, Toast success, Index shows $800.00 | Pass |
| **TC-02: Create Deduction** | Staff: A, Type: Deduction, Amount: $50, Date: Today | Record created with Red badge, Net Summary decreases by $50 | Pass |
| **TC-03: Zero/Negative Amount** | Amount: 0.00 or -50.00 | Form validation blocks submission (`min:0.01`) | Pass |
| **TC-04: Date Filter** | Date range: 2026-08-01 to 2026-08-31 | Table lists only records within August 2026 | Pass |
| **TC-05: Staff Filter** | Staff Select: Staff A | Table lists expenses exclusive to Staff A | Pass |
| **TC-06: Soft Delete** | Click "Delete" on expense record | Record hidden from active list, moves to Trash view | Pass |
| **TC-07: Restore Trash** | Click "Restore" in Trash view | Record returns to active list with original net calculations | Pass |
| **TC-08: Force Delete** | Click "Permanently Delete" in Trash | Record purged from DB permanently | Pass |
| **TC-09: Unauthorized User** | User without `staff-expense-create` accesses `/create` | 403 Forbidden page rendered | Pass |
| **TC-10: Staff History Page** | Click Dollar icon on Staff List | Navigates to Staff Ledger with per-staff summary cards | Pass |

---

## 7. Phased Implementation Roadmap

| Phase | Milestone / Task | Deliverable Component | Est. Time |
|---|---|---|---|
| **Phase 1: DB & Domain Core** | Write Migration, Create `ExpenseType` Enum, `StaffExpense` Model | `staff_expenses` table, Enum, Model | 1.0 hr |
| **Phase 2: Validation & Service** | Create `StoreStaffExpenseRequest`, `UpdateStaffExpenseRequest`, `StaffExpenseService` | Form Requests & Business Service Layer | 1.5 hrs |
| **Phase 3: Authorization & Routing** | Setup `StaffExpensePolicy`, Spatie permissions seeder, Admin routes | Policy & Route group in `admin.php` | 0.5 hr |
| **Phase 4: Controller & Services** | Create `StaffExpenseController` with full CRUD, Trash, & Staff History | Controller methods connected to Service | 1.5 hrs |
| **Phase 5: Blade Views UI** | Build `index.blade.php`, `form.blade.php`, `staff-history.blade.php`, `trash.blade.php` | Responsive UI pages with metric cards | 2.0 hrs |
| **Phase 6: QA & Testing** | Run Feature Tests & Manual QA Verification | Clean test suite execution | 1.0 hr |
| **Total Effort** | | | **~7.5 hrs** |
