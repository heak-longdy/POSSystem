<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barber;
use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\BookingPayment;
use App\Models\Notification;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Throwable;

class RemainingAmountController extends Controller
{
    protected $layout = 'admin::pages.remaining_amount.';
    private $routeName = 'remaining-amount';

    public function __construct()
    {
        $this->middleware('permission:booking-view', ['only' => ['index', 'getPaymentDetails', 'report']]);
        $this->middleware('permission:booking-update', ['only' => ['addPayment', 'updatePayment', 'sendPaymentReminder']]);
        $this->middleware('permission:booking-delete', ['only' => ['deletePayment']]);
    }

    public function index(Request $req)
    {
        $status = $this->normalizeStatusTab($req->status ?? 'all');
        $dates = $this->dateRange($req);

        $query = Booking::query()
            ->with([
                'shop',
                'barber',
                'customer',
                'bookingDetail' => function ($detail) {
                    $detail->withTrashed()->with(['service', 'product']);
                },
                'payments.createdBy',
            ]);

        // Status tab filtering
        if ($status === 'partial') {
            $query->where('payment_status', 'Partial');
        } elseif ($status === 'pending') {
            $query->where('payment_status', 'Pending');
        } elseif ($status === 'paid') {
            $query->where('payment_status', 'Paid');
        } elseif ($status === 'all') {
            // Default "all" shows active outstanding / all non-canceled bookings
            $query->where('payment_status', '!=', 'Cancel');
        }

        // Date range filtering
        if ($dates['from'] && $dates['to']) {
            $query->whereBetween(DB::raw('DATE(booking_date)'), [$dates['from'], $dates['to']]);
        }

        // Shop / Barber filters
        if ($req->shop_id) {
            $query->where('shop_id', $req->shop_id);
        }
        if ($req->barber_id) {
            $query->where('barber_id', $req->barber_id);
        }

        // Search
        if ($req->search) {
            $search = $req->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('shop', function ($shop) use ($search) {
                        $shop->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('barber', function ($barber) use ($search) {
                        $barber->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('customer', function ($customer) use ($search) {
                        $customer->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        $bookings = $query->orderBy('booking_date', 'desc')
            ->paginate(50)
            ->appends($req->query());

        $this->decorateRows($bookings);

        return view($this->layout . 'index', [
            'data' => $bookings,
            'status' => $status,
            'routeName' => $this->routeName,
            'shop' => $req->shop_id ? Shop::find($req->shop_id) : null,
            'barber' => $req->barber_id ? Barber::find($req->barber_id) : null,
            'firstMonthDay' => $dates['from'],
            'lastMonthDay' => $dates['to'],
        ]);
    }

    public function getPaymentDetails($id)
    {
        $booking = Booking::with(['payments.createdBy', 'customer', 'shop', 'barber', 'bookingDetail.service', 'bookingDetail.product'])->find($id);
        if (!$booking) {
            return response()->json(['message' => 'error', 'error' => 'Booking not found.'], 404);
        }

        $res = $this->paymentResponse($booking);
        $res['customer_name'] = $booking->customer?->name ?: ($booking->customer?->phone ?: 'Walk-in customer');
        $res['customer_phone'] = $booking->customer?->phone ?: '---';
        $res['shop_name'] = $booking->shop?->name ?: '---';
        $res['barber_name'] = $booking->barber?->name ?: '---';
        $res['booking_date'] = $booking->booking_date ? Carbon::parse($booking->booking_date)->format('d M Y, h:i A') : '---';

        return response()->json($res);
    }

    public function addPayment(Request $req, $id)
    {
        $req->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'nullable|string|max:50',
            'payment_date' => 'nullable|date',
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

            $paymentMethod = $req->payment_method ?: ($booking->pay_way ?: 'Cash');
            $paymentDate = $req->payment_date
                ? Carbon::parse($req->payment_date)->format('Y-m-d H:i:s')
                : Carbon::now()->format('Y-m-d H:i:s');

            BookingPayment::create([
                'booking_id' => $booking->id,
                'amount' => $req->amount,
                'payment_method' => $paymentMethod,
                'payment_date' => $paymentDate,
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
            'payment_method' => 'nullable|string|max:50',
            'payment_date' => 'nullable|date',
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

            $updateData = [
                'amount' => $req->amount,
                'note' => $req->note,
            ];

            if ($req->filled('payment_method')) {
                $updateData['payment_method'] = $req->payment_method;
            }
            if ($req->filled('payment_date')) {
                $updateData['payment_date'] = Carbon::parse($req->payment_date)->format('Y-m-d H:i:s');
            }

            $payment->update($updateData);

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

    public function sendPaymentReminder(Request $req, $id)
    {
        $booking = Booking::with(['customer', 'shop'])->find($id);
        if (!$booking) {
            return response()->json(['message' => 'error', 'error' => 'Booking not found.'], 404);
        }

        if ($booking->payment_status === 'Cancel') {
            return response()->json(['message' => 'error', 'error' => 'Cannot send reminder for a rejected booking.'], 422);
        }

        $remaining = (float) $booking->remaining_amount;
        if ($remaining <= 0) {
            return response()->json(['message' => 'error', 'error' => 'This booking does not have any outstanding balance.'], 422);
        }

        $customerName = $booking->customer?->name ?: 'Customer';
        $invoice = $booking->invoice_number ?: '#' . $booking->id;
        $totalFormatted = number_format((float) ($booking->total_price ?? 0), 2) . '៛';
        $paidFormatted = number_format((float) ($booking->paid_amount ?? 0), 2) . '៛';
        $remainingFormatted = number_format($remaining, 2) . '៛';
        $bookingDateFormatted = $booking->booking_date ? Carbon::parse($booking->booking_date)->format('d M Y, h:i A') : '---';

        $title = "Payment Reminder: Booking {$invoice}";
        $description = "Dear {$customerName}, this is a friendly reminder that you have an outstanding balance of {$remainingFormatted} (Total: {$totalFormatted}, Paid: {$paidFormatted}) for your booking on {$bookingDateFormatted}.";

        $notification = null;
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('notifications')) {
                $notification = Notification::create([
                    'title' => $title,
                    'description' => $description,
                    'type' => 'booking_payment_reminder',
                    'booking_id' => $booking->id,
                    'user_id' => Auth::id(),
                    'member_id' => $booking->customer_id,
                    'garage_id' => $booking->shop_id,
                    'status' => 1,
                    'type_send' => 'admin_to_customer',
                ]);
            }
        } catch (\Throwable $e) {
            // Notification table might not be present or driver-specific
        }

        return response()->json([
            'message' => 'success',
            'status' => 200,
            'notification' => $notification,
            'success_message' => "Payment reminder sent successfully for booking {$invoice}."
        ]);
    }

    public function report(Request $req)
    {
        $dates = $this->dateRange($req);
        $status = $this->normalizeStatusTab($req->status ?? 'all');

        $data = Booking::with([
            'shop:id,name,phone',
            'barber:id,name,phone',
            'customer:id,name,phone',
            'bookingDetail.service:id,name',
            'bookingDetail.product:id,name',
            'payments.createdBy:id,name,phone',
        ])
            ->when($dates['from'] && $dates['to'], function ($q) use ($dates) {
                $q->whereBetween(DB::raw('DATE(booking_date)'), [$dates['from'], $dates['to']]);
            })
            ->when($req->shop_id, function ($q) use ($req) {
                $q->where('shop_id', $req->shop_id);
            })
            ->when($req->barber_id, function ($q) use ($req) {
                $q->where('barber_id', $req->barber_id);
            })
            ->when($status === 'partial', function ($q) {
                $q->where('payment_status', 'Partial');
            })
            ->when($status === 'pending', function ($q) {
                $q->where('payment_status', 'Pending');
            })
            ->when($status === 'paid', function ($q) {
                $q->where('payment_status', 'Paid');
            })
            ->when($status === 'all', function ($q) {
                $q->where('payment_status', '!=', 'Cancel');
            })
            ->orderBy('booking_date', 'desc')
            ->get();

        return response()->json($data);
    }

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

        $booking = $booking->fresh();
        $booking->load(['payments.createdBy']);

        return $booking;
    }

    private function paymentResponse(Booking $booking)
    {
        $booking->loadMissing(['payments.createdBy']);

        $payments = $booking->payments->map(function ($payment) {
            return [
                'id' => $payment->id,
                'booking_id' => $payment->booking_id,
                'amount' => (float) ($payment->amount ?? 0),
                'amount_formatted' => number_format((float) ($payment->amount ?? 0), 2) . '៛',
                'payment_method' => $payment->payment_method ?: 'Cash',
                'payment_date' => $payment->payment_date
                    ? Carbon::parse($payment->payment_date)->format('Y-m-d H:i:s')
                    : ($payment->created_at ? Carbon::parse($payment->created_at)->format('Y-m-d H:i:s') : null),
                'payment_date_formatted' => $payment->payment_date
                    ? Carbon::parse($payment->payment_date)->format('d M Y, h:i A')
                    : ($payment->created_at ? Carbon::parse($payment->created_at)->format('d M Y, h:i A') : '---'),
                'note' => $payment->note ?: '',
                'created_by' => $payment->createdBy?->name ?: ($payment->createdBy?->phone ?: 'System'),
                'created_at' => $payment->created_at,
            ];
        });

        return [
            'message' => 'success',
            'status' => 200,
            'id' => $booking->id,
            'invoice_number' => $booking->invoice_number ?: '#' . $booking->id,
            'total_price' => (float) ($booking->total_price ?? 0),
            'total_price_formatted' => number_format((float) ($booking->total_price ?? 0), 2) . '៛',
            'paid_amount' => (float) ($booking->paid_amount ?? 0),
            'paid_amount_formatted' => number_format((float) ($booking->paid_amount ?? 0), 2) . '៛',
            'remaining_amount' => (float) $booking->remaining_amount,
            'remaining_amount_formatted' => number_format((float) $booking->remaining_amount, 2) . '៛',
            'payment_status' => $booking->payment_status ?: 'Pending',
            'payment_date' => $booking->payment_date,
            'payments' => $payments,
        ];
    }

    private function normalizeStatusTab($status)
    {
        $status = strtolower((string) $status);

        return match ($status) {
            'partial' => 'partial',
            'pending' => 'pending',
            'paid' => 'paid',
            default => 'all',
        };
    }

    private function dateRange(Request $req, $defaultToMonth = true)
    {
        if (!$defaultToMonth) {
            return [
                'from' => $req->from_date ?: '',
                'to' => $req->to_date ?: '',
            ];
        }

        $startDate = Carbon::now();

        return [
            'from' => $req->from_date ?: $startDate->copy()->firstOfMonth()->format('Y-m-d'),
            'to' => $req->to_date ?: $startDate->copy()->lastOfMonth()->format('Y-m-d'),
        ];
    }

    private function decorateRows($rows)
    {
        foreach ($rows as $item) {
            $item->invoice_title = $item->invoice_number ?: '---';
            $item->shop_title = $item->shop?->name ?: '---';
            $item->customer_title = $this->customerTitle($item);
            $item->booking_items_title = $this->bookingItemsTitle($item);
            $item->payment_status_title = $this->paymentStatusBadge($item);
            $item->total_price_title = number_format((float) ($item->total_price ?? 0), 2) . '៛';
            $item->paid_amount_title = number_format((float) ($item->paid_amount ?? 0), 2) . '៛';
            $item->remaining_amount_title = number_format((float) ($item->remaining_amount ?? 0), 2) . '៛';
            $item->total_discount_title = number_format((float) ($item->total_discount ?? 0), 2) . '៛';
            $item->booking_date_title = $item->booking_date
                ? Carbon::parse($item->booking_date)->format('Y-m-d H:i')
                : '---';
        }
    }

    private function customerTitle(Booking $booking)
    {
        $name = e($booking->customer?->name ?: '---');
        $phone = e($booking->customer?->phone ?: '---');

        return "<span>{$name}</span><small>{$phone}</small>";
    }

    private function bookingItemsTitle(Booking $booking)
    {
        if (!$booking->bookingDetail || $booking->bookingDetail->count() === 0) {
            return '---';
        }

        return $booking->bookingDetail->map(function ($detail) {
            $name = e($detail->type === 'service' ? $detail->service?->name : $detail->product?->name);
            $qty = (int) ($detail->qty ?: 1);

            return "<span>- {$name} ({$qty})</span>";
        })->implode('<br>');
    }

    private function paymentStatusBadge(Booking $booking)
    {
        $status = $booking->payment_status ?: 'Pending';
        $class = match ($status) {
            'Paid' => 'bg-success',
            'Partial' => 'bg-info',
            'Cancel' => 'bg-danger',
            default => 'bg-warning text-dark',
        };
        $date = $booking->payment_date
            ? '<small class="text-muted">' . e(Carbon::parse($booking->payment_date)->format('Y-m-d H:i')) . '</small>'
            : '';

        return '<span class="badge ' . $class . '" style="margin-bottom:7px;">' . e($status) . '</span>' . $date;
    }
}
