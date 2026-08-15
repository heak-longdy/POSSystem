# Client Booking Partial Payment Implementation Plan

> Planning document only. Do not apply these snippets to the system until the plan is reviewed and approved.

## Feature Overview

The Client Booking module currently stores one booking total and one payment status on the `bookings` table. Payment status is handled as a direct state change from `Pending` to `Paid` or `Cancel`.

This feature will add partial payment support while keeping the existing Laravel structure:

- Admin routes remain under `routes/admin.php` in the `booking` route group.
- Booking payment logic stays in `App\Http\Controllers\Admin\BookingController`.
- Booking totals remain on `App\Models\Booking`.
- Individual payment records are stored in a new `App\Models\BookingPayment` model.
- The active booking form remains `resources/admin/views/pages/booking/createBooking.blade.php`.
- The booking list remains `resources/admin/views/pages/booking/bookings.blade.php`.

The system will store a running `paid_amount` on each booking, calculate `remaining_amount` as `total_price - paid_amount`, and maintain a full payment history for every installment.

### Current State

| Area | Current Behavior |
| --- | --- |
| Booking total | Stored in `bookings.total_price` |
| Discount | Stored in `bookings.total_discount` |
| Commission | Stored in `bookings.total_commission` |
| Payment method | Stored at booking level in `bookings.pay_way` |
| Payment status | Stored in `bookings.payment_status` |
| Paid date | Stored in `bookings.payment_date` when marked `Paid` |
| Payment history | Not available |
| Partial status | Not available |
| Remaining balance | Not available as a stored or computed field |

### Target State

| Area | Target Behavior |
| --- | --- |
| Booking paid amount | Stored in `bookings.paid_amount` |
| Booking remaining amount | Computed as `max(0, total_price - paid_amount)` |
| Payment history | Stored in `booking_payments` |
| Payment status | Supports `Pending`, `Partial`, `Paid`, and `Cancel` |
| Payment edits | Allowed for active non-cancelled bookings |
| Payment deletes | Soft delete payment record, then recalculate booking payment state |
| Overpayment | Blocked on the server and guarded in the UI |

## Business Requirements

1. A booking may receive one or more payment installments.
2. Each payment installment must record:
   - booking id
   - amount
   - optional note
   - user who recorded the payment
   - created and updated timestamps
3. `bookings.paid_amount` must always represent the sum of active payment records.
4. `remaining_amount` must not be stored. It is always calculated from `total_price - paid_amount`.
5. Payment status must be derived from payment totals:

| Condition | Status | Payment Date |
| --- | --- | --- |
| `paid_amount <= 0` | `Pending` | `null` |
| `0 < paid_amount < total_price` | `Partial` | `null` |
| `paid_amount >= total_price` | `Paid` | Set when the booking first becomes fully paid |
| Booking rejected/cancelled | `Cancel` | `null` |

6. Overpayment is not allowed. If the payment amount exceeds the current remaining balance, the request must return validation error `422`.
7. Payment records may be edited or deleted. After any edit or delete, the booking must recalculate `paid_amount`, `remaining_amount`, `payment_status`, and `payment_date`.
8. Cancel/reject remains separate from payment collection. A booking with active payment records should not be cancelled unless a refund/void process is later approved.
9. Existing booking-level `pay_way` stays as-is. Payment method per installment is outside this scope.
10. Any authenticated admin user may add, edit, or delete booking payment records. Do not add a new permission requirement unless the business rule changes.
11. Booking item editing should follow the existing rule: only `Pending` bookings can be updated through `Save()`. Partial and paid bookings should not allow item/total changes unless a separate total-adjustment rule is approved.
12. Dashboard/API totals that currently count pending liabilities must include the remaining balance for both `Pending` and `Partial` bookings.

## Implementation Approach

Use a small payment ledger table and keep `bookings.paid_amount` as a denormalized summary for fast list/dashboard display.

### Data Strategy

- Add `paid_amount` to `bookings`.
- Add `booking_payments` table for installments.
- Use soft deletes on `booking_payments` to preserve payment audit history while excluding deleted records from active totals.
- Keep `remaining_amount` as an accessor on `Booking`.
- Recalculate booking payment state inside database transactions.

### Controller Strategy

The current `updatePaymentStatus()` method should be split into focused actions:

| Action | Purpose |
| --- | --- |
| `addPayment(Request $req, $id)` | Add a new payment installment |
| `updatePayment(Request $req, $paymentId)` | Edit an existing payment installment |
| `deletePayment($paymentId)` | Soft delete a payment installment |
| `cancelBooking($id)` | Reject/cancel a booking without mixing payment logic |
| `syncBookingPaymentState(Booking $booking)` | Shared private helper to recalculate paid amount and status |

The existing one-click "Mark as Paid" action must not directly set `payment_status = Paid` anymore. It should either:

- be removed from the list and replaced with a "Payment" action that opens the booking payment panel, or
- create a final payment record equal to the remaining amount.

The preferred approach is to replace it with a payment action, because every paid amount should come from `booking_payments`.

### UI Strategy

Use the current Alpine/Axios pattern:

- Add payment state to the existing `XDatacreateorder` component.
- Show payment summary only when editing an existing booking.
- Show add-payment controls when the booking is not `Paid` and not `Cancel`.
- Show payment history below the summary.
- Use the existing confirm dialog store for delete/payment confirmation.
- Keep the booking list powered by the existing `listingData` component and controller `decorateRows()` method.

## High-Level Workflow

### Create Booking

1. Admin creates a booking using the current order flow.
2. Booking is saved with:
   - `payment_status = Pending`
   - `paid_amount = 0`
   - `payment_date = null`
3. Admin opens the saved booking to record one or more payments.

### Add Payment

1. Admin opens an existing booking.
2. UI displays total, paid amount, remaining amount, and payment history.
3. Admin enters payment amount and optional note.
4. Client validates amount is greater than `0` and not above remaining amount.
5. Server locks the booking row, validates again, creates `booking_payments` record, and recalculates booking payment state.
6. UI refreshes payment summary and history.

### Edit Payment

1. Admin edits a payment amount or note.
2. Server locks the related booking.
3. Server allows the new amount only if it does not exceed the booking's available balance:
   - `available_balance = current_remaining_amount + old_payment_amount`
4. Server updates the payment and recalculates booking payment state.

### Delete Payment

1. Admin confirms delete.
2. Server soft deletes the payment record.
3. Server recalculates booking payment state from remaining active payments.
4. Status may move from `Paid` to `Partial`, or from `Partial` to `Pending`.

### Cancel Booking

1. Admin chooses reject/cancel for a booking with no active payments.
2. Server reverses the existing booking effects using `reverseBookingEffects()`.
3. Server updates `payment_status = Cancel` and clears `payment_date`.

## Implementation Plan

### Step 1 - Database

Create a migration to add `paid_amount` to `bookings`.

- File pattern: `database/migrations/YYYY_MM_DD_HHMMSS_add_paid_amount_to_bookings_table.php`
- Use `double` to match existing booking monetary columns.
- Default value must be `0`.
- No stored `remaining_amount` column.

Create a migration for payment installments.

- File pattern: `database/migrations/YYYY_MM_DD_HHMMSS_create_booking_payments_table.php`
- Table name: `booking_payments`
- Use integer ids/indexes to match current migration style.
- Include `softDeletes()` so delete actions do not permanently erase audit history.

### Step 2 - Models

Update `app/Models/Booking.php`.

- Add `paid_amount` to `$fillable`.
- Add `paid_amount` to `$casts`.
- Add `payments()` relation.
- Add `getRemainingAmountAttribute()`.
- Optionally append `remaining_amount` for JSON/Blade convenience.

Create `app/Models/BookingPayment.php`.

- Use `SoftDeletes`.
- Add fillable fields.
- Add casts.
- Add `booking()` and `createdBy()` relations.

### Step 3 - Controller

Update `app/Http/Controllers/Admin/BookingController.php`.

- Import `App\Models\BookingPayment`.
- Add `addPayment()`.
- Add `updatePayment()`.
- Add `deletePayment()`.
- Add `cancelBooking()`.
- Add private `syncBookingPaymentState()` helper.
- Update `edit()`/`formData()` to load payment history with `createdBy`.
- Update `normalizePaymentStatus()` to support `Partial`.
- Update `bookingPaymentStatusForTab()` to support `Partial` if a tab is added.
- Update `paymentStatusBadge()` to display `Partial` with a distinct badge.
- Update `decorateRows()` to add `paid_amount_title` and `remaining_amount_title`.
- Remove or stop using direct status updates that mark a booking paid without creating payment records.

### Step 4 - Routes

Update the existing booking route group in `routes/admin.php`.

- Keep current list/create/edit/save/delete/restore/destroy/report routes.
- Replace or deprecate `update-payment-status/{id}`.
- Add payment routes using the current admin route naming style:
  - `admin-booking-add-payment`
  - `admin-booking-update-payment`
  - `admin-booking-delete-payment`
  - `admin-booking-cancel`

### Step 5 - Booking Form View

Update `resources/admin/views/pages/booking/createBooking.blade.php`.

- Add payment summary inside the existing right-side payment panel when `$id` exists.
- Keep order creation controls unchanged for new bookings.
- Add payment history table/list below the summary.
- Add Alpine state:
  - `paidAmount`
  - `remainingAmount`
  - `bookingStatus`
  - `paymentHistory`
  - `paymentAmount`
  - `paymentNote`
  - edit-payment state
- Add Alpine methods:
  - `submitPayment()`
  - `startEditPayment()`
  - `savePaymentEdit()`
  - `deletePayment()`
  - `syncPaymentState()`
- Disable booking item updates when `bookingStatus !== 'Pending'`, matching the existing backend rule.

### Step 6 - Booking List View

Update `resources/admin/views/pages/booking/bookings.blade.php`.

- Add `Partial` tab or filter option.
- Add columns for `Paid` and `Remaining` if table width allows.
- Update list actions:
  - `Edit` remains visible for `Pending`.
  - `Payment` should be visible for `Pending` and `Partial`.
  - `Reject` should be visible only for `Pending` bookings with no active payments.
  - `Delete` remains aligned with existing behavior.
- Update export columns if payment data is part of the report.

### Step 7 - Dashboard and API Totals

Review booking liability queries in:

- `app/Http/Controllers/Api/BookingController.php`
- `app/Http/Controllers/Api/BookingOldController.php`
- any active dashboard data provider that sums pending booking totals

Replace pending-only liability totals with remaining-balance logic:

- Include `Pending` and `Partial`.
- Sum `total_price - paid_amount`.
- Exclude soft-deleted bookings.

### Step 8 - Validation and QA

Manual review checklist:

| Scenario | Expected Result |
| --- | --- |
| Create booking without payment | `Pending`, `paid_amount = 0`, `remaining_amount = total_price` |
| Add partial payment | `Partial`, `paid_amount` increases, remaining decreases |
| Add final payment | `Paid`, remaining becomes `0`, `payment_date` is set |
| Add amount greater than remaining | Validation error `422` |
| Edit payment lower than original | Booking may move from `Paid` to `Partial` |
| Edit payment above available balance | Validation error `422` |
| Delete only payment | Booking returns to `Pending` |
| Delete one of many payments | Booking recalculates from remaining active payments |
| Try to cancel partially paid booking | Validation error until refund/void rules exist |
| Dashboard/API pending liability | Includes remaining balances for `Pending` and `Partial` |

## Logic Flow

### Payment Status Calculation

```text
syncBookingPaymentState(booking)
    totalPaid = SUM(active booking_payments.amount)
    totalPrice = booking.total_price

    IF totalPaid <= 0
        status = Pending
        payment_date = null

    ELSE IF totalPaid >= totalPrice
        status = Paid
        payment_date = existing payment_date OR now()

    ELSE
        status = Partial
        payment_date = null

    UPDATE bookings
        paid_amount = totalPaid
        payment_status = status
        payment_date = payment_date
```

### Add Payment Flow

```text
POST /admin/booking/add-payment/{booking_id}
    validate amount and note
    begin transaction
        lock booking
        reject if booking is Cancel
        reject if booking is already Paid
        reject if amount > remaining_amount
        create booking_payments record
        sync booking payment state
    commit transaction
    return updated payment summary and history
```

### Edit Payment Flow

```text
PUT /admin/booking/update-payment/{payment_id}
    validate amount and note
    begin transaction
        lock payment
        lock related booking
        reject if booking is Cancel
        available = booking.remaining_amount + old payment amount
        reject if new amount > available
        update payment
        sync booking payment state
    commit transaction
    return updated payment summary and history
```

### Delete Payment Flow

```text
DELETE /admin/booking/delete-payment/{payment_id}
    begin transaction
        lock payment
        lock related booking
        reject if booking is Cancel
        soft delete payment
        sync booking payment state
    commit transaction
    return updated payment summary and history
```

## Implementation Code

Reference only. These snippets show the intended structure and naming for review. They are not applied to the system by this document.

### Migration - Add Paid Amount

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->double('paid_amount')->default(0)->after('payment_status');
        });
    }

    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('paid_amount');
        });
    }
};
```

### Migration - Booking Payments

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('booking_payments', function (Blueprint $table) {
            $table->id();
            $table->integer('booking_id')->index();
            $table->double('amount')->default(0);
            $table->text('note')->nullable();
            $table->integer('created_by')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('booking_payments');
    }
};
```

### Model - Booking

```php
// app/Models/Booking.php

protected $fillable = [
    'customer_id',
    'total_price',
    'total_commission',
    'total_discount',
    'paid_amount',
    'shop_id',
    'barber_id',
    'booking_date',
    'payment_status',
    'payment_date',
    'invoice_number',
    'pay_way',
    'total_point',
    'remark',
];

protected $casts = [
    'total_price' => 'double',
    'total_discount' => 'double',
    'total_commission' => 'double',
    'paid_amount' => 'double',
    'booking_date' => 'datetime',
    'payment_date' => 'datetime',
];

protected $appends = ['remaining_amount'];

public function payments()
{
    return $this->hasMany(BookingPayment::class, 'booking_id');
}

public function getRemainingAmountAttribute()
{
    return max(0, (float) ($this->total_price ?? 0) - (float) ($this->paid_amount ?? 0));
}
```

### Model - BookingPayment

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'booking_payments';

    protected $fillable = [
        'booking_id',
        'amount',
        'note',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'double',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
```

### Controller - Payment Actions

```php
// app/Http/Controllers/Admin/BookingController.php

use App\Models\BookingPayment;

public function addPayment(Request $req, $id)
{
    $req->validate([
        'amount' => 'required|numeric|min:0.01',
        'note' => 'nullable|string|max:500',
    ]);

    DB::beginTransaction();
    try {
        $booking = Booking::where('id', $id)->lockForUpdate()->firstOrFail();

        if ($booking->payment_status === 'Cancel') {
            throw ValidationException::withMessages([
                'payment_status' => 'Cannot add payment to a rejected booking.',
            ]);
        }

        if ($booking->payment_status === 'Paid') {
            throw ValidationException::withMessages([
                'payment_status' => 'Booking is already fully paid.',
            ]);
        }

        if ((float) $req->amount > (float) $booking->remaining_amount) {
            throw ValidationException::withMessages([
                'amount' => 'Payment amount exceeds remaining balance.',
            ]);
        }

        BookingPayment::create([
            'booking_id' => $booking->id,
            'amount' => $req->amount,
            'note' => $req->note,
            'created_by' => Auth::id(),
        ]);

        $booking = $this->syncBookingPaymentState($booking);

        DB::commit();

        return response()->json($this->paymentResponse($booking));
    } catch (ValidationException $e) {
        DB::rollBack();

        return response()->json(['message' => 'error', 'errors' => $e->errors()], 422);
    } catch (Throwable $e) {
        DB::rollBack();

        return response()->json(['message' => 'error', 'error' => $e->getMessage()], 500);
    }
}

public function updatePayment(Request $req, $paymentId)
{
    $req->validate([
        'amount' => 'required|numeric|min:0.01',
        'note' => 'nullable|string|max:500',
    ]);

    DB::beginTransaction();
    try {
        $payment = BookingPayment::where('id', $paymentId)->lockForUpdate()->firstOrFail();
        $booking = Booking::where('id', $payment->booking_id)->lockForUpdate()->firstOrFail();

        if ($booking->payment_status === 'Cancel') {
            throw ValidationException::withMessages([
                'payment_status' => 'Cannot edit payment on a rejected booking.',
            ]);
        }

        $availableBalance = (float) $booking->remaining_amount + (float) $payment->amount;
        if ((float) $req->amount > $availableBalance) {
            throw ValidationException::withMessages([
                'amount' => 'Payment amount exceeds available balance.',
            ]);
        }

        $payment->update([
            'amount' => $req->amount,
            'note' => $req->note,
        ]);

        $booking = $this->syncBookingPaymentState($booking);

        DB::commit();

        return response()->json($this->paymentResponse($booking));
    } catch (ValidationException $e) {
        DB::rollBack();

        return response()->json(['message' => 'error', 'errors' => $e->errors()], 422);
    } catch (Throwable $e) {
        DB::rollBack();

        return response()->json(['message' => 'error', 'error' => $e->getMessage()], 500);
    }
}

public function deletePayment($paymentId)
{
    DB::beginTransaction();
    try {
        $payment = BookingPayment::where('id', $paymentId)->lockForUpdate()->firstOrFail();
        $booking = Booking::where('id', $payment->booking_id)->lockForUpdate()->firstOrFail();

        if ($booking->payment_status === 'Cancel') {
            throw ValidationException::withMessages([
                'payment_status' => 'Cannot delete payment on a rejected booking.',
            ]);
        }

        $payment->delete();
        $booking = $this->syncBookingPaymentState($booking);

        DB::commit();

        return response()->json($this->paymentResponse($booking));
    } catch (ValidationException $e) {
        DB::rollBack();

        return response()->json(['message' => 'error', 'errors' => $e->errors()], 422);
    } catch (Throwable $e) {
        DB::rollBack();

        return response()->json(['message' => 'error', 'error' => $e->getMessage()], 500);
    }
}
```

### Controller - Shared Helpers

```php
private function syncBookingPaymentState(Booking $booking)
{
    $totalPaid = BookingPayment::where('booking_id', $booking->id)->sum('amount');
    $totalPrice = (float) ($booking->total_price ?? 0);

    if ((float) $totalPaid <= 0) {
        $paymentStatus = 'Pending';
        $paymentDate = null;
    } elseif ((float) $totalPaid >= $totalPrice) {
        $paymentStatus = 'Paid';
        $paymentDate = $booking->payment_date ?: Carbon::now()->format('Y-m-d H:i:s');
    } else {
        $paymentStatus = 'Partial';
        $paymentDate = null;
    }

    $booking->update([
        'paid_amount' => $totalPaid,
        'payment_status' => $paymentStatus,
        'payment_date' => $paymentDate,
    ]);

    return $booking->fresh(['payments.createdBy']);
}

private function paymentResponse(Booking $booking)
{
    return [
        'message' => 'success',
        'status' => 200,
        'paid_amount' => (float) $booking->paid_amount,
        'remaining_amount' => (float) $booking->remaining_amount,
        'payment_status' => $booking->payment_status,
        'payment_date' => $booking->payment_date,
        'payments' => $booking->payments,
    ];
}
```

### Controller - Cancel Booking

```php
public function cancelBooking($id)
{
    DB::beginTransaction();
    try {
        $booking = Booking::with('bookingDetail')->where('id', $id)->lockForUpdate()->firstOrFail();

        if ($booking->payment_status !== 'Pending' || (float) ($booking->paid_amount ?? 0) > 0) {
            throw ValidationException::withMessages([
                'payment_status' => 'Only unpaid pending bookings can be rejected.',
            ]);
        }

        $this->reverseBookingEffects($booking);

        $booking->update([
            'payment_status' => 'Cancel',
            'payment_date' => null,
        ]);

        DB::commit();
        Session::flash('success', 'Booking rejected successfully.');

        return response()->json(['message' => 'success', 'status' => 200]);
    } catch (ValidationException $e) {
        DB::rollBack();

        return response()->json(['message' => 'error', 'errors' => $e->errors()], 422);
    } catch (Throwable $e) {
        DB::rollBack();

        return response()->json(['message' => 'error', 'error' => $e->getMessage()], 500);
    }
}
```

### Controller - Form Data and Status Helpers

```php
// In edit(), include payments when loading the booking.
$booking = Booking::with([
    'customer',
    'shop',
    'barber',
    'payments.createdBy',
])->find($id);

// In normalizePaymentStatus().
return match ($status) {
    'pending' => 'Pending',
    'partial' => 'Partial',
    'paid' => 'Paid',
    'cancel', 'rejected' => 'Cancel',
    default => null,
};

// In paymentStatusBadge().
$class = match ($status) {
    'Paid' => 'bg-success',
    'Partial' => 'bg-info',
    'Cancel' => 'bg-danger',
    default => 'bg-warning text-dark',
};
```

### Routes

```php
// routes/admin.php
// Inside Route::group(['prefix' => 'booking', 'as' => 'booking-'], ...)

Route::post('add-payment/{id}', [BookingController::class, 'addPayment'])
    ->name('add-payment');

Route::put('update-payment/{paymentId}', [BookingController::class, 'updatePayment'])
    ->name('update-payment');

Route::delete('delete-payment/{paymentId}', [BookingController::class, 'deletePayment'])
    ->name('delete-payment');

Route::post('cancel/{id}', [BookingController::class, 'cancelBooking'])
    ->name('cancel');
```

### Blade - Payment Summary Placement

```blade
{{-- resources/admin/views/pages/booking/createBooking.blade.php --}}
{{-- Place inside .booking-pos-payment and only show after a booking exists. --}}

<template x-if="bookingId">
    <div class="booking-pos-payment-ledger">
        <div class="booking-pos-totals-row">
            <span>Paid</span>
            <span x-text="formatCurrency(paidAmount)"></span>
        </div>
        <div class="booking-pos-totals-row">
            <span>Remaining</span>
            <span x-text="formatCurrency(remainingAmount)"></span>
        </div>
        <div class="booking-pos-totals-row">
            <span>Status</span>
            <span x-text="bookingStatus"></span>
        </div>
    </div>
</template>
```

### Alpine - Payment State

```javascript
paidAmount: 0,
remainingAmount: 0,
bookingStatus: 'Pending',
paymentHistory: [],
paymentAmount: null,
paymentNote: '',
editingPaymentId: null,
editPaymentAmount: null,
editPaymentNote: '',

init() {
    const payload = @json($data);
    const booking = payload?.data ?? payload ?? null;

    this.bookingId = booking?.id || null;
    this.paidAmount = Number(booking?.paid_amount || 0);
    this.remainingAmount = Number(booking?.remaining_amount || 0);
    this.bookingStatus = booking?.payment_status || 'Pending';
    this.paymentHistory = booking?.payments || [];

    // Keep existing init logic below this point.
},

syncPaymentState(data) {
    this.paidAmount = Number(data?.paid_amount || 0);
    this.remainingAmount = Number(data?.remaining_amount || 0);
    this.bookingStatus = data?.payment_status || 'Pending';
    this.paymentHistory = data?.payments || [];
}
```

### Alpine - Add Payment

```javascript
submitPayment() {
    if (!this.paymentAmount || Number(this.paymentAmount) <= 0) {
        return;
    }

    if (Number(this.paymentAmount) > Number(this.remainingAmount)) {
        this.setValidationErrors({
            amount: ['Payment amount exceeds remaining balance.']
        });
        return;
    }

    Axios.post(`{{ route('admin-booking-add-payment', ':id') }}`.replace(':id', this.bookingId), {
        amount: this.paymentAmount,
        note: this.paymentNote,
    }).then((res) => {
        if (res.data.message === 'success') {
            this.syncPaymentState(res.data);
            this.paymentAmount = null;
            this.paymentNote = '';
        }
    }).catch((e) => {
        this.setValidationErrors(e.response?.data?.errors || {
            general: ['Unable to record payment.']
        });
    });
}
```

### Booking List Decorations

```php
// app/Http/Controllers/Admin/BookingController.php
// In decorateRows().

$item->paid_amount_title = number_format((float) ($item->paid_amount ?? 0), 2) . ' KHR';
$item->remaining_amount_title = number_format((float) ($item->remaining_amount ?? 0), 2) . ' KHR';
```

### Dashboard/API Remaining Balance Query

```php
$totalPendingLiability = Booking::whereIn('payment_status', ['Pending', 'Partial'])
    ->sum(DB::raw('total_price - paid_amount'));
```

## Review Notes

- The plan intentionally avoids storing `remaining_amount`.
- The plan keeps payment method per installment out of scope.
- The plan preserves the existing `Cancel` database value while the UI may continue displaying it as `Rejected`.
- The plan uses soft deletes for payment records so deleted payments are excluded from active totals but remain recoverable for audit.
- Before implementation, confirm whether the booking list should remove "Mark as Paid" or convert it into a "Pay Remaining" action that creates a final payment record.
