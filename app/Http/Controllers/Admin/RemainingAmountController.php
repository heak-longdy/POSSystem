<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barber;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderPayment;
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
        $this->middleware('permission:order-view', ['only' => ['index', 'getPaymentDetails', 'report', 'show']]);
        $this->middleware('permission:order-update', ['only' => ['addPayment', 'updatePayment', 'sendPaymentReminder']]);
        $this->middleware('permission:order-delete', ['only' => ['deletePayment']]);
    }

    public function index(Request $req)
    {
        $status = $this->normalizeStatusTab($req->status ?? 'all');
        $dates = $this->dateRange($req);

        $query = Order::query()
            ->with([
                'shop',
                'barber',
                'customer',
                'orderDetails' => function ($detail) {
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
            // Default "all" shows active outstanding / all non-canceled orders
            $query->where('payment_status', '!=', 'Cancel');
        }

        // Date range filtering
        if ($dates['from'] && $dates['to']) {
            $query->whereBetween(DB::raw('DATE(order_date)'), [$dates['from'], $dates['to']]);
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

        $orders = $query->orderBy('order_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(50)
            ->appends($req->query());

        $this->decorateRows($orders);

        return view($this->layout . 'index', [
            'data' => $orders,
            'status' => $status,
            'routeName' => $this->routeName,
            'shop' => $req->shop_id ? Shop::find($req->shop_id) : null,
            'barber' => $req->barber_id ? Barber::find($req->barber_id) : null,
            'firstMonthDay' => $dates['from'],
            'lastMonthDay' => $dates['to'],
        ]);
    }

    public function show($id = null)
    {
        if (!$id) {
            return redirect()->route('admin-' . $this->routeName . '-list', 'all');
        }

        $order = Order::withTrashed()->with([
            'customer',
            'shop',
            'barber',
            'orderDetails' => function ($detail) {
                $detail->withTrashed()->with(['service', 'product']);
            },
            'payments.createdBy',
        ])->find($id);

        if (!$order) {
            Session::flash('warning', __('order.message.not_found'));
            return redirect()->route('admin-' . $this->routeName . '-list', 'all');
        }

        $data['order'] = $order;
        $data['routeName'] = $this->routeName;
        $data['canEdit'] = $order->payment_status === 'Pending' && !$order->trashed();
        $data['canAddPayment'] = $order->payment_status !== 'Cancel' && !$order->trashed() && (float) $order->remaining_amount > 0;
        $data['canCancel'] = $order->payment_status === 'Pending' && !$order->trashed() && (float) ($order->paid_amount ?? 0) <= 0;

        return view($this->layout . 'detail', $data);
    }

    public function getPaymentDetails($id)
    {
        $order = Order::with(['payments.createdBy', 'customer', 'shop', 'barber', 'orderDetails.service', 'orderDetails.product'])->find($id);
        if (!$order) {
            return response()->json(['message' => 'error', 'error' => __('order.message.not_found')], 404);
        }

        $res = $this->paymentResponse($order);
        $res['customer_name'] = $order->customer?->name ?: ($order->customer?->phone ?: __('order.walk_in_customer'));
        $res['customer_phone'] = $order->customer?->phone ?: '---';
        $res['shop_name'] = $order->shop?->name ?: '---';
        $res['barber_name'] = $order->barber?->name ?: '---';
        $res['order_date'] = $order->order_date ? Carbon::parse($order->order_date)->format('d M Y, h:i A') : '---';

        return response()->json($res);
    }

    public function addPayment(Request $req, $id)
    {
        $req->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'nullable|string|max:50',
            'payment_date' => 'nullable|date',
            'note' => 'nullable|string|max:500',
        ], [
            'amount.required' => __('order.validation.payment_amount_required'),
            'amount.numeric' => __('order.validation.partial_numeric'),
            'amount.min' => __('order.validation.partial_min'),
        ]);

        $order = Order::findOrFail($id);
        if ($order->payment_status === 'Cancel') {
            return response()->json([
                'message' => 'error',
                'errors' => [
                    'payment_status' => [__('remaining_amount.message.cannot_add_payment_rejected')],
                ],
            ], 422);
        }
        if ($order->payment_status === 'Paid') {
            return response()->json([
                'message' => 'error',
                'errors' => [
                    'payment_status' => [__('remaining_amount.message.order_already_paid')],
                ],
            ], 422);
        }
        if ((float) $req->amount > (float) $order->remaining_amount) {
            return response()->json([
                'message' => 'error',
                'errors' => [
                    'amount' => [__('order.validation.payment_amount_exceeds')],
                ],
            ], 422);
        }

        DB::beginTransaction();
        try {
            $order = Order::where('id', $id)->lockForUpdate()->firstOrFail();
            $paymentMethod = $req->payment_method ?: ($order->pay_way ?: 'Cash');
            $paymentDate = $req->payment_date
                ? Carbon::parse($req->payment_date)->format('Y-m-d H:i:s')
                : Carbon::now()->format('Y-m-d H:i:s');

            OrderPayment::create([
                'order_id' => $order->id,
                'amount' => $req->amount,
                'payment_method' => $paymentMethod,
                'payment_date' => $paymentDate,
                'note' => $req->note,
                'created_by' => Auth::id(),
            ]);

            $order = $this->syncOrderPaymentState($order);
            DB::commit();

            return response()->json($this->paymentResponse($order));
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
        ], [
            'amount.required' => __('order.validation.payment_amount_required'),
            'amount.numeric' => __('order.validation.partial_numeric'),
            'amount.min' => __('order.validation.partial_min'),
        ]);

        DB::beginTransaction();
        try {
            $payment = OrderPayment::where('id', $paymentId)->lockForUpdate()->firstOrFail();
            $order = Order::where('id', $payment->order_id)->lockForUpdate()->firstOrFail();
            if ($order->payment_status === 'Cancel') {
                throw ValidationException::withMessages([
                    'payment_status' => __('remaining_amount.message.cannot_edit_payment_rejected'),
                ]);
            }

            $availableBalance = (float) $order->remaining_amount + (float) $payment->amount;
            if ((float) $req->amount > $availableBalance) {
                throw ValidationException::withMessages([
                    'amount' => __('remaining_amount.message.payment_exceeds_available'),
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

            $order = $this->syncOrderPaymentState($order);
            DB::commit();

            return response()->json($this->paymentResponse($order));
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
            $payment = OrderPayment::where('id', $paymentId)->lockForUpdate()->firstOrFail();
            $order = Order::where('id', $payment->order_id)->lockForUpdate()->firstOrFail();
            if ($order->payment_status === 'Cancel') {
                throw ValidationException::withMessages([
                    'payment_status' => __('remaining_amount.message.cannot_delete_payment_rejected'),
                ]);
            }

            $payment->delete();
            $order = $this->syncOrderPaymentState($order);
            DB::commit();

            return response()->json($this->paymentResponse($order));
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
        $order = Order::with(['customer', 'shop'])->find($id);
        if (!$order) {
            return response()->json(['message' => 'error', 'error' => __('order.message.not_found')], 404);
        }

        if ($order->payment_status === 'Cancel') {
            return response()->json(['message' => 'error', 'error' => __('remaining_amount.message.cannot_send_reminder_rejected')], 422);
        }

        $remaining = (float) $order->remaining_amount;
        if ($remaining <= 0) {
            return response()->json(['message' => 'error', 'error' => __('remaining_amount.message.no_outstanding_balance')], 422);
        }

        $customerName = $order->customer?->name ?: ($order->customer?->phone ?: __('order.walk_in_customer'));
        $invoice = $order->invoice_number ?: '#' . $order->id;
        $totalFormatted = '$' . number_format((float) ($order->total_price ?? 0), 2);
        $paidFormatted = '$' . number_format((float) ($order->paid_amount ?? 0), 2);
        $remainingFormatted = '$' . number_format($remaining, 2);
        $orderDateFormatted = $order->order_date ? Carbon::parse($order->order_date)->format('d M Y, h:i A') : '---';

        $title = __('remaining_amount.notification.reminder_title', ['invoice' => $invoice]);
        $description = __('remaining_amount.notification.reminder_desc', [
            'customer' => $customerName,
            'remaining' => $remainingFormatted,
            'total' => $totalFormatted,
            'paid' => $paidFormatted,
            'date' => $orderDateFormatted,
        ]);

        $notification = null;
        try {
            $notification = Notification::create([
                'title' => $title,
                'description' => $description,
                'type' => 'order_payment_reminder',
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'member_id' => $order->customer_id,
                'garage_id' => $order->shop_id,
                'status' => 1,
                'type_send' => 'admin_to_customer',
            ]);
        } catch (\Throwable $e) {
            // Notification table might not be present or driver-specific
        }

        return response()->json([
            'message' => 'success',
            'status' => 200,
            'notification' => $notification,
            'success_message' => __('remaining_amount.message.reminder_sent_order', ['invoice' => $invoice])
        ]);
    }

    public function report(Request $req)
    {
        $dates = $this->dateRange($req);
        $status = $this->normalizeStatusTab($req->status ?? 'all');

        $data = Order::with([
            'shop:id,name,phone',
            'barber:id,name,phone',
            'customer:id,name,phone',
            'orderDetails.service:id,name',
            'orderDetails.product:id,name',
            'payments.createdBy:id,name,phone',
        ])
            ->when($dates['from'] && $dates['to'], function ($q) use ($dates) {
                $q->whereBetween(DB::raw('DATE(order_date)'), [$dates['from'], $dates['to']]);
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
            ->orderBy('order_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($data);
    }

    private function syncOrderPaymentState(Order $order)
    {
        $totalPaid = OrderPayment::where('order_id', $order->id)->sum('amount');
        $totalPrice = (float) ($order->total_price ?? 0);

        if ((float) $totalPaid <= 0) {
            $paymentStatus = 'Pending';
            $paymentDate = null;
        } elseif ((float) $totalPaid >= $totalPrice) {
            $paymentStatus = 'Paid';
            $paymentDate = $order->payment_date ?: Carbon::now()->format('Y-m-d H:i:s');
        } else {
            $paymentStatus = 'Partial';
            $paymentDate = null;
        }

        $order->update([
            'paid_amount' => $totalPaid,
            'payment_status' => $paymentStatus,
            'payment_date' => $paymentDate,
        ]);

        $order = $order->fresh();
        $order->load(['payments.createdBy']);

        return $order;
    }

    private function paymentResponse(Order $order)
    {
        $order->loadMissing(['payments.createdBy']);

        $payments = $order->payments->map(function ($payment) {
            return [
                'id' => $payment->id,
                'order_id' => $payment->order_id,
                'amount' => (float) ($payment->amount ?? 0),
                'amount_formatted' => '$' . number_format((float) ($payment->amount ?? 0), 2),
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
            'id' => $order->id,
            'invoice_number' => $order->invoice_number ?: '#' . $order->id,
            'total_price' => (float) ($order->total_price ?? 0),
            'total_price_formatted' => '$' . number_format((float) ($order->total_price ?? 0), 2),
            'paid_amount' => (float) ($order->paid_amount ?? 0),
            'paid_amount_formatted' => '$' . number_format((float) ($order->paid_amount ?? 0), 2),
            'remaining_amount' => (float) $order->remaining_amount,
            'remaining_amount_formatted' => '$' . number_format((float) $order->remaining_amount, 2),
            'payment_status' => $order->payment_status ?: 'Pending',
            'payment_date' => $order->payment_date,
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
            $item->order_items_title = $this->orderItemsTitle($item);
            $item->payment_status_title = $this->paymentStatusBadge($item);
            $item->total_price_title = '$' . number_format((float) ($item->total_price ?? 0), 2);
            $item->paid_amount_title = '$' . number_format((float) ($item->paid_amount ?? 0), 2);
            $item->remaining_amount_title = '$' . number_format((float) ($item->remaining_amount ?? 0), 2);
            $item->total_discount_title = '$' . number_format((float) ($item->total_discount ?? 0), 2);
            $item->order_date_title = $item->order_date
                ? Carbon::parse($item->order_date)->format('Y-m-d H:i')
                : '---';
            $item->can_add_payment = ($item->payment_status !== 'Cancel' && (float) ($item->remaining_amount ?? 0) > 0);
        }
    }

    private function customerTitle(Order $order)
    {
        $name = e($order->customer?->name ?: ($order->customer?->phone ?: __('order.walk_in_customer')));
        $phone = e($order->customer?->phone ?: '');

        $phoneHtml = $phone ? "<small class=\"customer-phone\"><i class=\"bx bx-phone\"></i>{$phone}</small>" : '';

        return "<div class=\"customer-cell\"><span class=\"customer-name\">{$name}</span>{$phoneHtml}</div>";
    }

    private function orderItemsTitle(Order $order)
    {
        return OrderController::renderOrderItems($order);
    }

    private function paymentStatusBadge(Order $order)
    {
        $status = $order->payment_status ?: 'Pending';
        $label = OrderController::orderStatusLabel($status);
        $class = match ($status) {
            'Paid' => 'bg-success',
            'Partial' => 'bg-info',
            'Cancel' => 'bg-danger',
            default => 'bg-warning text-dark',
        };
        $date = $order->payment_date
            ? '<small class="text-muted">' . e(Carbon::parse($order->payment_date)->format('Y-m-d H:i')) . '</small>'
            : '';

        return '<span class="badge ' . $class . '" style="margin-bottom:7px;">' . e($label) . '</span>' . $date;
    }
}
