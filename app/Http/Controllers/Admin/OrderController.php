<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderRequest;
use App\Models\Barber;
use App\Models\CustomerPoint;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderPayment;
use App\Models\Shop;
use App\Models\ShopProduct;
use App\Models\ShopService;
use App\Models\StockHistory;
use App\Models\StockOnHand;
use App\Models\StockOut;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use App\Services\InvoiceService;
use Throwable;

class OrderController extends Controller
{
    protected $layout = 'admin::pages.order.';
    protected $routeName = 'order';

    public function __construct()
    {
        $this->middleware('permission:order-view', ['only' => ['index', 'product', 'getPaymentDetails', 'show']]);
        $this->middleware('permission:order-create', ['only' => ['onCreate', 'save', 'Save']]);
        $this->middleware('permission:order-update', ['only' => ['onEdit', 'edit', 'save', 'Save', 'restore', 'updatePaymentStatus', 'cancelOrder', 'addPayment', 'updatePayment', 'sendPaymentReminder']]);
        $this->middleware('permission:order-delete', ['only' => ['delete', 'restore', 'destroy', 'deletePayment']]);
    }

    public function index(Request $req)
    {
        if (!$req->status) {
            return redirect()->route('admin-' . $this->routeName . '-list', 'Pending');
        }

        $status = $this->normalizeOrderStatusTab($req->status);
        if (!$status) {
            return redirect()->route('admin-' . $this->routeName . '-list', 'Pending');
        }

        $dates = $this->dateRange($req);
        $paymentStatus = $this->orderPaymentStatusForTab($status)
            ?: $this->normalizePaymentStatus($req->payment_status);

        $data['status'] = $status;
        $data['routeName'] = $this->routeName;
        $data['shop'] = $req->shop_id ? Shop::find($req->shop_id) : null;
        $data['barber'] = $req->barber_id ? Barber::find($req->barber_id) : null;
        $data['firstMonthDay'] = $dates['from'];
        $data['lastMonthDay'] = $dates['to'];

        $query = $status === 'trash'
            ? Order::onlyTrashed()
            : Order::query();

        $data['data'] = $query->with([
                'shop',
                'barber',
                'customer',
                'orderDetails' => function ($detail) {
                    $detail->withTrashed()->with(['service', 'product']);
                },
            ])
            ->when($dates['from'] && $dates['to'], function ($query) use ($dates) {
                $query->whereBetween(DB::raw('DATE(order_date)'), [$dates['from'], $dates['to']]);
            })
            ->when($req->shop_id, function ($query) use ($req) {
                $query->where('shop_id', $req->shop_id);
            })
            ->when($req->barber_id, function ($query) use ($req) {
                $query->where('barber_id', $req->barber_id);
            })
            ->when($paymentStatus, function ($query) use ($paymentStatus) {
                $query->where('payment_status', $paymentStatus);
            })
            ->when($req->search, function ($query) use ($req) {
                $query->where(function ($q) use ($req) {
                    $q->where('invoice_number', 'like', '%' . $req->search . '%')
                        ->orWhereHas('shop', function ($shop) use ($req) {
                            $shop->where('name', 'like', '%' . $req->search . '%');
                        })
                        ->orWhereHas('barber', function ($barber) use ($req) {
                            $barber->where('name', 'like', '%' . $req->search . '%');
                        })
                        ->orWhereHas('customer', function ($customer) use ($req) {
                            $customer->where('name', 'like', '%' . $req->search . '%')
                                ->orWhere('phone', 'like', '%' . $req->search . '%');
                        })
                        ->orWhereHas('orderDetails.product', function ($product) use ($req) {
                            $product->where('name', 'like', '%' . $req->search . '%');
                        })
                        ->orWhereHas('orderDetails.service', function ($service) use ($req) {
                            $service->where('name', 'like', '%' . $req->search . '%');
                        });
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(50)
            ->appends($req->query());

        $this->decorateRows($data['data']);

        return view($this->layout . 'orders', $data);
    }

    public function product(Request $req)
    {
        $dates = $this->dateRange($req, false);
        $from = $dates['from'];
        $to = $dates['to'];

        $data['firstMonthDay'] = $from;
        $data['lastMonthDay'] = $to;
        $data['startDate'] = Carbon::now()->firstOfMonth()->format('Y-m-d');
        $data['data'] = Order::with(['shop'])
            ->when($from && $to, function ($q) use ($from, $to) {
                $q->whereBetween(DB::raw('DATE(order_date)'), [$from, $to]);
            })
            ->when($req->shop_id, function ($q) use ($req) {
                $q->where('shop_id', $req->shop_id);
            })
            ->when($req->barber_id, function ($q) use ($req) {
                $q->where('barber_id', $req->barber_id);
            })
            ->paginate(50)
            ->appends($req->query());
        $data['shops'] = Shop::where('status', 1)->get();
        $data['barbers'] = Barber::where('status', 1)->get();

        foreach ($data['data'] as $item) {
            $item->orders = OrderDetail::with(['product'])->where('order_id', $item->id)->get();
        }

        return view($this->layout . 'index', $data);
    }

    public function onCreate(Request $req)
    {
        return view($this->layout . 'createOrder', $this->formData());
    }

    public function onEdit(Request $req)
    {
        return $this->edit($req->id);
    }

    public function edit($id = null)
    {
        if (!$id) {
            return redirect()->route('admin-' . $this->routeName . '-list', 1);
        }

        $order = Order::with(['customer', 'shop', 'barber', 'payments.createdBy', 'orderDetails.service', 'orderDetails.product'])->find($id);
        if (!$order) {
            Session::flash('warning', __('order.message.not_found'));
            return redirect()->route('admin-' . $this->routeName . '-list', 1);
        }

        return view($this->layout . 'createOrder', $this->formData($order));
    }

    public function show($id = null)
    {
        if (!$id) {
            return redirect()->route('admin-' . $this->routeName . '-list', 'Pending');
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
            return redirect()->route('admin-' . $this->routeName . '-list', 'Pending');
        }

        $data['order'] = $order;
        $data['routeName'] = $this->routeName;
        $data['canEdit'] = $order->payment_status === 'Pending' && !$order->trashed();
        $data['canAddPayment'] = $order->payment_status !== 'Cancel' && !$order->trashed() && (float) $order->remaining_amount > 0;
        $data['canCancel'] = $order->payment_status === 'Pending' && !$order->trashed() && (float) ($order->paid_amount ?? 0) <= 0;

        return view($this->layout . 'detail', $data);
    }

    public function Save(OrderRequest $req, $id = '')
    {
        $orderId = $id ?: $req->id;
        $dataCarts = $this->decodeCart($req);

        DB::beginTransaction();
        try {
            $order = $orderId
                ? Order::with('orderDetails')->findOrFail($orderId)
                : new Order();

            if ($order->exists && $order->payment_status !== 'Pending') {
                throw ValidationException::withMessages([
                    'payment_status' => 'Only Pending orders can be updated.',
                ]);
            }

            if ($order->exists) {
                $this->reverseOrderEffects($order);
                OrderDetail::withTrashed()
                    ->where('order_id', $order->id)
                    ->forceDelete();
            }

            $order->fill($this->payload($req, $order));
            if (!$order->exists) {
                $order->invoice_number = $this->nextInvoiceNumber();
            }
            $order->save();

            $totalPoint = 0;
            foreach ($dataCarts as $cart) {
                $detail = $this->createDetail($order, $cart);
                $this->applyDetailStock($order, $detail);
                $totalPoint += (int) ($detail->point ?? 0);
            }

            $order->update(['total_point' => $totalPoint]);
            $order->load('orderDetails');
            $this->adjustCustomerPoint($order, 1);
            $this->recordInitialPayment(
                $order,
                (float) ($req->partial_payment_amount ?? 0),
                $req->pay_way,
                $req->order_date,
                $req->partial_payment_note
            );

            DB::commit();
            Session::flash('success', $orderId ? __('order.message.update_success') : __('order.message.create_success'));

            $savedOrder = $order->fresh();
            if ($savedOrder->shop_id) {
                session(['last_order_shop_id' => $savedOrder->shop_id]);
            }

            return response()->json([
                'message' => 'success',
                'status' => 200,
                'id' => $savedOrder->id,
                'payment_status' => $savedOrder->payment_status,
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();

            return response()->json(['message' => 'error', 'errors' => $e->errors()], 422);
        } catch (Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateStatus($id = '', $status = '')
    {
        return response()->json([
            'message' => 'unsuccess',
            'status' => 422,
            'error' => 'Order status is managed through payment status.',
        ], 422);
    }

    public function updatePaymentStatus(Request $req, $id)
    {
        $newStatus = $req->payment_status;
        if (!in_array($newStatus, ['Paid', 'Cancel'])) {
            return response()->json(['message' => 'error', 'error' => 'Invalid payment status.'], 422);
        }

        if ($newStatus === 'Cancel') {
            return $this->cancelOrder($id);
        }

        DB::beginTransaction();
        try {
            $order = Order::where('id', $id)->lockForUpdate()->firstOrFail();
            if ($order->payment_status === 'Cancel') {
                throw ValidationException::withMessages([
                    'payment_status' => 'Cannot add payment to a rejected order.',
                ]);
            }

            if ($order->payment_status !== 'Paid') {
                $remaining = (float) $order->remaining_amount;
                if ($remaining > 0) {
                    OrderPayment::create([
                        'order_id' => $order->id,
                        'amount' => $remaining,
                        'payment_method' => $req->payment_method ?: ($order->pay_way ?: 'Cash'),
                        'payment_date' => Carbon::now()->format('Y-m-d H:i:s'),
                        'note' => $req->note ?: ($order->payment_status === 'Partial' ? 'Paid remaining balance.' : 'Paid in full.'),
                        'created_by' => Auth::id(),
                    ]);
                }
            }

            $order = $this->syncOrderPaymentState($order);
            DB::commit();
            Session::flash('success', __('order.message.payment_status_success'));

            return response()->json($this->paymentResponse($order));
        } catch (ValidationException $e) {
            DB::rollBack();

            return response()->json(['message' => 'error', 'errors' => $e->errors()], 422);
        } catch (Throwable $e) {
            DB::rollBack();
            Session::flash('warning', __('order.message.payment_status_error'));

            return response()->json(['message' => 'error', 'error' => $e->getMessage()], 500);
        }
    }

    public function getPaymentDetails($id)
    {
        $order = Order::with(['payments.createdBy', 'customer', 'shop', 'barber'])->find($id);
        if (!$order) {
            return response()->json(['message' => 'error', 'error' => 'Order not found.'], 404);
        }

        $res = $this->paymentResponse($order);
        $res['customer_name'] = $order->customer?->name ?: ($order->customer?->phone ?: 'Walk-in customer');
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
        ]);

        DB::beginTransaction();
        try {
            $order = Order::where('id', $id)->lockForUpdate()->firstOrFail();
            if ($order->payment_status === 'Cancel') {
                throw ValidationException::withMessages([
                    'payment_status' => 'Cannot add payment to a rejected order.',
                ]);
            }
            if ($order->payment_status === 'Paid') {
                throw ValidationException::withMessages([
                    'payment_status' => 'Order is already fully paid.',
                ]);
            }
            if ((float) $req->amount > (float) $order->remaining_amount) {
                throw ValidationException::withMessages([
                    'amount' => 'Payment amount exceeds remaining balance.',
                ]);
            }

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
        ]);

        DB::beginTransaction();
        try {
            $payment = OrderPayment::where('id', $paymentId)->lockForUpdate()->firstOrFail();
            $order = Order::where('id', $payment->order_id)->lockForUpdate()->firstOrFail();
            if ($order->payment_status === 'Cancel') {
                throw ValidationException::withMessages([
                    'payment_status' => 'Cannot edit payment on a rejected order.',
                ]);
            }

            $availableBalance = (float) $order->remaining_amount + (float) $payment->amount;
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
                    'payment_status' => 'Cannot delete payment on a rejected order.',
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
            return response()->json(['message' => 'error', 'error' => 'Order not found.'], 404);
        }

        if ($order->payment_status === 'Cancel') {
            return response()->json(['message' => 'error', 'error' => 'Cannot send reminder for a rejected order.'], 422);
        }

        $remaining = (float) $order->remaining_amount;
        if ($remaining <= 0) {
            return response()->json(['message' => 'error', 'error' => 'This order does not have any outstanding balance.'], 422);
        }

        $customerName = $order->customer?->name ?: 'Customer';
        $invoice = $order->invoice_number ?: '#' . $order->id;
        $totalFormatted = '$' . number_format((float) ($order->total_price ?? 0), 2);
        $paidFormatted = '$' . number_format((float) ($order->paid_amount ?? 0), 2);
        $remainingFormatted = '$' . number_format($remaining, 2);
        $orderDateFormatted = $order->order_date ? Carbon::parse($order->order_date)->format('d M Y, h:i A') : '---';

        $title = "Payment Reminder: Order {$invoice}";
        $description = "Dear {$customerName}, this is a friendly reminder that you have an outstanding balance of {$remainingFormatted} (Total: {$totalFormatted}, Paid: {$paidFormatted}) for your order on {$orderDateFormatted}.";

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
            'success_message' => "Payment reminder sent successfully for order {$invoice}."
        ]);
    }

    public function cancelOrder($id)
    {
        DB::beginTransaction();
        try {
            $order = Order::with('orderDetails')->where('id', $id)->lockForUpdate()->firstOrFail();
            if ($order->payment_status !== 'Pending' || (float) ($order->paid_amount ?? 0) > 0) {
                throw ValidationException::withMessages([
                    'payment_status' => 'Only unpaid pending orders can be rejected.',
                ]);
            }

            $this->reverseOrderEffects($order);
            $order->update([
                'payment_status' => 'Cancel',
                'payment_date' => null,
            ]);

            DB::commit();
            Session::flash('success', __('order.message.reject_success'));

            return response()->json(['message' => 'success', 'status' => 200]);
        } catch (ValidationException $e) {
            DB::rollBack();

            return response()->json(['message' => 'error', 'errors' => $e->errors()], 422);
        } catch (Throwable $e) {
            DB::rollBack();
            Session::flash('warning', __('order.message.reject_error'));

            return response()->json(['message' => 'error', 'error' => $e->getMessage()], 500);
        }
    }

    public function delete($id = '')
    {
        DB::beginTransaction();
        try {
            $order = Order::with('orderDetails')->findOrFail($id);
            if ($order->payment_status !== 'Cancel') {
                $this->reverseOrderEffects($order);
            }

            OrderDetail::where('order_id', $order->id)->delete();
            $order->delete();

            DB::commit();
            Session::flash('success', __('order.message.delete_success'));

            return response()->json(['message' => 'success', 'status' => 200]);
        } catch (ValidationException $e) {
            DB::rollBack();

            return response()->json(['message' => 'unsuccess', 'errors' => $e->errors()], 422);
        } catch (Throwable $e) {
            DB::rollBack();
            Session::flash('warning', __('order.message.delete_error'));

            return response()->json(['message' => 'unsuccess', 'status' => 404, 'error' => $e->getMessage()], 404);
        }
    }

    public function restore($id = '')
    {
        DB::beginTransaction();
        try {
            $order = Order::withTrashed()->findOrFail($id);
            $order->restore();
            OrderDetail::withTrashed()
                ->where('order_id', $order->id)
                ->restore();
            $order->load('orderDetails');

            if ($order->payment_status !== 'Cancel') {
                $this->applyOrderEffects($order);
            }

            DB::commit();
            Session::flash('success', __('order.message.restore_success'));

            return response()->json(['message' => 'success', 'status' => 200]);
        } catch (ValidationException $e) {
            DB::rollBack();

            return response()->json(['message' => 'unsuccess', 'errors' => $e->errors()], 422);
        } catch (Throwable $e) {
            DB::rollBack();
            Session::flash('warning', __('order.message.restore_error'));

            return response()->json(['message' => 'unsuccess', 'status' => 404, 'error' => $e->getMessage()], 404);
        }
    }

    public function destroy($id = '')
    {
        DB::beginTransaction();
        try {
            $order = Order::withTrashed()->findOrFail($id);
            $order->load('orderDetails');

            if (!$order->trashed() && $order->payment_status !== 'Cancel') {
                $this->reverseOrderEffects($order);
            }

            OrderDetail::withTrashed()
                ->where('order_id', $order->id)
                ->forceDelete();
            OrderPayment::withTrashed()
                ->where('order_id', $order->id)
                ->forceDelete();
            $order->forceDelete();

            DB::commit();
            Session::flash('success', __('order.message.delete_success'));

            return response()->json(['message' => 'success', 'status' => 200]);
        } catch (ValidationException $e) {
            DB::rollBack();

            return response()->json(['message' => 'unsuccess', 'errors' => $e->errors()], 422);
        } catch (Throwable $e) {
            DB::rollBack();
            Session::flash('warning', __('order.message.delete_error'));

            return response()->json(['message' => 'unsuccess', 'status' => 404, 'error' => $e->getMessage()], 404);
        }
    }

    public function report(Request $req)
    {
        $dates = $this->dateRange($req);
        $itemSelect = ['id', 'name', 'phone'];
        $paymentStatus = $this->normalizePaymentStatus($req->payment_status ?: $req->status);

        $data = OrderDetail::with([
            'product:id,name',
            'service:id,name',
            'order' => function ($order) use ($itemSelect) {
                $order->with([
                    'shop' => function ($query) use ($itemSelect) {
                        $query->select($itemSelect);
                    },
                    'barber' => function ($query) use ($itemSelect) {
                        $query->select($itemSelect);
                    },
                    'customer' => function ($query) use ($itemSelect) {
                        $query->select($itemSelect);
                    },
                ]);
            },
        ])
            ->whereHas('order', function ($query) use ($req, $dates, $paymentStatus) {
                $query->whereDate('order_date', '>=', $dates['from'])
                    ->whereDate('order_date', '<=', $dates['to'])
                    ->when($req->shop_id, function ($q) use ($req) {
                        $q->where('shop_id', $req->shop_id);
                    })
                    ->when($req->barber_id, function ($q) use ($req) {
                        $q->where('barber_id', $req->barber_id);
                    })
                    ->when($paymentStatus, function ($q) use ($paymentStatus) {
                        $q->where('payment_status', $paymentStatus);
                    });
            })
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($data);
    }

    protected function syncOrderPaymentState(Order $order)
    {
        $totalPaid = OrderPayment::where('order_id', $order->id)->sum('amount');
        $totalPrice = (float) ($order->total_price ?? 0);

        if ((float) $totalPaid <= 0 && $totalPrice > 0) {
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

    protected function paymentResponse(Order $order)
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

    protected function recordInitialPayment(Order $order, float $amount, ?string $paymentMethod = null, ?string $paymentDate = null, ?string $note = null): void
    {
        $existingPayment = OrderPayment::where('order_id', $order->id)
            ->orderBy('id', 'asc')->first();

        if ($amount <= 0) {
            if ($existingPayment && $order->payments()->count() === 1 && $order->payment_status === 'Pending') {
                $existingPayment->delete();
                $this->syncOrderPaymentState($order);
            }
            return;
        }

        if ($amount > (float) ($order->total_price ?? 0)) {
            throw ValidationException::withMessages([
                'partial_payment_amount' => __('order.validation.partial_max'),
            ]);
        }

        $paymentData = [
            'order_id' => $order->id,
            'amount' => $amount,
            'payment_method' => $paymentMethod ?: ($order->pay_way ?: 'Cash'),
            'payment_date' => $paymentDate
                ? Carbon::parse($paymentDate)->format('Y-m-d H:i:s')
                : ($order->order_date ? Carbon::parse($order->order_date)->format('Y-m-d H:i:s') : Carbon::now()->format('Y-m-d H:i:s')),
            'note' => filled($note) ? trim($note) : 'Initial partial payment.',
            'created_by' => Auth::id(),
        ];

        if ($existingPayment && $order->payments()->count() === 1) {
            $existingPayment->update($paymentData);
        } else {
            OrderPayment::create($paymentData);
        }

        $this->syncOrderPaymentState($order);
    }

    protected function formData(Order $order = null)
    {
        $shopId = old('shop_id', $order?->shop_id ?: (request('shop_id') ?: session('last_order_shop_id')));
        $shop = $shopId ? Shop::find($shopId) : null;

        if (!$shop) {
            $latestShopId = Order::latest('id')->value('shop_id');
            $shop = $latestShopId ? Shop::find($latestShopId) : null;
        }

        if (!$shop) {
            $shop = Shop::where('status', 1)->orderBy('id', 'asc')->first();
        }

        if (!$order) {
            $order = (object) [
                'id' => null,
                'shop' => $shop,
                'shop_id' => $shop?->id,
                'barber' => null,
                'barber_id' => null,
                'customer' => null,
                'customer_id' => null,
                'orderDetails' => collect([]),
                'payments' => collect([]),
                'paid_amount' => 0,
                'remaining_amount' => 0,
                'payment_status' => 'Pending',
                'order_date' => Carbon::now()->format('Y-m-d'),
                'delivery_date' => null,
            ];
        }

        $recentOrders = Order::with(['customer', 'barber'])
            ->withCount('orderDetails')
            ->when($shop?->id, function ($query) use ($shop) {
                $query->where('shop_id', $shop->id);
            })
            ->latest('id')
            ->take(3)
            ->get();

        if ($recentOrders->isEmpty()) {
            $recentOrders = Order::with(['customer', 'barber'])
                ->withCount('orderDetails')
                ->latest('id')
                ->take(3)
                ->get();
        }

        return [
            'id' => $order->id ?? '',
            'data' => $order,
            'order' => $order,
            'routeName' => $this->routeName,
            'selectedShop' => $shop,
            'selectedBarber' => isset($order->barber_id) && $order->barber_id ? Barber::find($order->barber_id) : null,
            'recentOrders' => $recentOrders,
        ];
    }

    protected function payload(OrderRequest $req, Order $order)
    {
        return [
            'customer_id' => $req->customer_id,
            'shop_id' => $req->shop_id,
            'barber_id' => $req->barber_id,
            'order_date' => $req->order_date,
            'delivery_date' => $req->delivery_date ?: null,
            'total_price' => $req->subTotal ?? 0,
            'total_discount' => $req->total_discount ?? 0,
            'total_commission' => $req->commissionTotal ?? 0,
            'pay_way' => $req->pay_way ?: ($order->pay_way ?: 'Cash'),
            'payment_status' => $order->payment_status ?: 'Pending',
        ];
    }

    protected function decodeCart(OrderRequest $req)
    {
        $dataCarts = json_decode($req->dataCarts);
        if (!is_array($dataCarts) || count($dataCarts) === 0) {
            throw ValidationException::withMessages([
                'dataCarts' => 'Shopping cart is required',
            ]);
        }

        return $dataCarts;
    }

    protected function createDetail(Order $order, $cart)
    {
        $type = $cart->product_type ?? null;
        $productId = (int) ($cart->product_id ?? 0);
        $qty = max(1, (int) ($cart->product_qty ?? 1));
        $itemData = $cart->itemData ?? (object) [];

        if (!in_array($type, ['product', 'service']) || !$productId) {
            throw ValidationException::withMessages([
                'dataCarts' => 'Shopping cart item is invalid.',
            ]);
        }

        $shopItem = $type === 'service'
            ? ShopService::where('shop_id', $order->shop_id)->where('service_id', $productId)->first()
            : ShopProduct::where('shop_id', $order->shop_id)->where('product_id', $productId)->first();

        if (!$shopItem) {
            throw ValidationException::withMessages([
                'dataCarts' => 'Selected item does not belong to this shop.',
            ]);
        }

        return OrderDetail::create([
            'order_id' => $order->id,
            'service_id' => $type === 'service' ? $productId : null,
            'product_id' => $type === 'product' ? $productId : null,
            'price' => $itemData->price ?? $shopItem->price ?? 0,
            'qty' => $type === 'service' ? 1 : $qty,
            'type' => $type,
            'point' => $shopItem->point ?? 0,
            'product_discount' => $type === 'product' ? ($itemData->discount ?? 0) : 0,
            'product_discount_type' => $type === 'product' ? ($itemData->discountType ?? null) : null,
            'service_discount' => $type === 'service' ? ($itemData->discount ?? 0) : 0,
            'service_discount_type' => $type === 'service' ? ($itemData->discountType ?? null) : null,
            'product_commission' => $type === 'product' ? ($itemData->commission ?? 0) : 0,
            'product_commission_type' => $type === 'product' ? ($itemData->commissionType ?? 'usd') : null,
            'service_commission' => $type === 'service' ? ($itemData->commission ?? 0) : 0,
            'service_commission_type' => $type === 'service' ? ($itemData->commissionType ?? 'usd') : null,
        ]);
    }

    protected function applyOrderEffects(Order $order)
    {
        $order->loadMissing('orderDetails');
        foreach ($order->orderDetails as $detail) {
            $this->applyDetailStock($order, $detail);
        }

        $this->adjustCustomerPoint($order, 1);
    }

    protected function reverseOrderEffects(Order $order)
    {
        $order->loadMissing('orderDetails');
        foreach ($order->orderDetails as $detail) {
            $this->reverseDetailStock($order, $detail);
        }

        $this->adjustCustomerPoint($order, -1);
    }

    protected function applyDetailStock(Order $order, OrderDetail $detail)
    {
        if ($detail->type !== 'product' || !$detail->product_id) {
            return;
        }

        $qty = (int) ($detail->qty ?? 1);
        $stockOnHand = StockOnHand::where('shop_id', $order->shop_id)
            ->where('product_id', $detail->product_id)
            ->lockForUpdate()
            ->first();

        if (!$stockOnHand || (int) $stockOnHand->current_stock < $qty) {
            throw ValidationException::withMessages([
                'dataCarts' => 'Qty is limited or out of stock.',
            ]);
        }

        $stockOnHand->update([
            'current_stock' => (int) $stockOnHand->current_stock - $qty,
            'request_by' => Auth::id(),
            'request_by_type' => 'admin',
        ]);

        $stockOut = StockOut::create([
            'product_id' => $detail->product_id,
            'shop_id' => $order->shop_id,
            'to_id' => $order->customer_id,
            'qty' => $qty,
            'type' => 'customer',
            'status' => 1,
            'request_by' => Auth::id(),
            'request_by_type' => 'admin',
        ]);

        StockHistory::create([
            'transfer_id' => $order->id,
            'stock_id' => $stockOut->id,
            'product_id' => $detail->product_id,
            'current_stock' => (int) $stockOnHand->current_stock,
            'stock_in' => 0,
            'stock_out' => $qty,
            'shop_id' => $order->shop_id,
            'to_id' => $order->customer_id,
            'qty' => $qty,
            'type' => 'customer',
            'transfer_type' => 'order',
            'status' => 'stock_out',
            'request_by' => Auth::id(),
            'request_by_type' => 'admin',
        ]);
    }

    protected function reverseDetailStock(Order $order, OrderDetail $detail)
    {
        if ($detail->type !== 'product' || !$detail->product_id) {
            return;
        }

        $qty = (int) ($detail->qty ?? 1);
        $stockOnHand = StockOnHand::where('shop_id', $order->shop_id)
            ->where('product_id', $detail->product_id)
            ->lockForUpdate()
            ->first();

        if ($stockOnHand) {
            $stockOnHand->update([
                'current_stock' => (int) $stockOnHand->current_stock + $qty,
                'request_by' => Auth::id(),
                'request_by_type' => 'admin',
            ]);
        }

        $histories = StockHistory::where('transfer_id', $order->id)
            ->where('shop_id', $order->shop_id)
            ->where('product_id', $detail->product_id)
            ->where('status', 'stock_out')
            ->where('transfer_type', 'order')
            ->get();

        foreach ($histories as $history) {
            if ($history->stock_id) {
                $stockOut = StockOut::withTrashed()->find($history->stock_id);
                if ($stockOut) {
                    $stockOut->delete();
                }
            }

            $history->delete();
        }
    }

    protected function adjustCustomerPoint(Order $order, int $direction)
    {
        $point = (int) ($order->total_point ?? 0);
        $count = $order->orderDetails ? count($order->orderDetails) : 0;
        if (!$order->customer_id || !$order->shop_id || ($point === 0 && $count === 0)) {
            return;
        }

        $shop = $order->shop ?: Shop::find($order->shop_id);
        $lookup = [
            'shop_id' => $order->shop_id,
            'customer_id' => $order->customer_id,
        ];
        $customerPoint = $direction > 0
            ? CustomerPoint::firstOrCreate($lookup, [
                'brand_id' => $shop?->brand_id,
                'total_point' => 0,
                'total_receving_point' => 0,
                'used_point' => 0,
                'count_of_using_service' => 0,
            ])
            : CustomerPoint::where($lookup)->first();

        if (!$customerPoint) {
            return;
        }

        $customerPoint->update([
            'brand_id' => $customerPoint->brand_id ?: $shop?->brand_id,
            'total_point' => max(0, (int) $customerPoint->total_point + ($direction * $point)),
            'total_receving_point' => max(0, (int) $customerPoint->total_receving_point + ($direction * $point)),
            'used_point' => max(0, (int) $customerPoint->used_point + ($direction * $point)),
            'count_of_using_service' => max(0, (int) $customerPoint->count_of_using_service + ($direction * $count)),
        ]);
    }

    protected function nextInvoiceNumber()
    {
        return app(InvoiceService::class)->generateNextInvoiceNumber();
    }

    protected function normalizeOrderStatusTab($status)
    {
        $status = strtolower((string) $status);

        return match ($status) {
            '', '1', 'pending' => 'Pending',
            'partial' => 'Partial',
            'paid' => 'Paid',
            'rejected', 'cancel' => 'Rejected',
            'trash' => 'trash',
            default => null,
        };
    }

    protected function orderPaymentStatusForTab($status)
    {
        return match ($status) {
            'Pending', 'Partial', 'Paid' => $status,
            'Rejected' => 'Cancel',
            default => null,
        };
    }

    protected function normalizePaymentStatus($status)
    {
        $status = strtolower((string) $status);

        return match ($status) {
            'pending' => 'Pending',
            'partial' => 'Partial',
            'paid' => 'Paid',
            'cancel', 'rejected' => 'Cancel',
            default => null,
        };
    }

    public static function orderStatusLabel($status)
    {
        return match ($status) {
            'Paid' => __('order.status.paid'),
            'Partial' => __('order.status.partial'),
            'Cancel' => __('order.status.rejected'),
            default => __('order.status.pending'),
        };
    }

    protected function dateRange(Request $req, $defaultToMonth = true)
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

    protected function decorateRows($rows)
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
            $item->total_commission_title = '$' . number_format((float) ($item->total_commission ?? 0), 2);
            $item->total_discount_title = '$' . number_format((float) ($item->total_discount ?? 0), 2);
            $item->can_reject = $item->payment_status === 'Pending' && (float) ($item->paid_amount ?? 0) <= 0;
            $itemDate = $item->order_date;
            $item->order_date_title = $itemDate
                ? Carbon::parse($itemDate)->format('Y-m-d H:i')
                : '---';
        }
    }

    protected function customerTitle(Order $order)
    {
        $name = e($order->customer?->name ?: ($order->customer?->phone ?: __('order.walk_in_customer')));
        $phone = e($order->customer?->phone ?: '');

        $phoneHtml = $phone ? "<small class=\"customer-phone\"><i class=\"bx bx-phone\"></i>{$phone}</small>" : '';

        return "<div class=\"customer-cell\"><span class=\"customer-name\">{$name}</span>{$phoneHtml}</div>";
    }

    protected function orderItemsTitle(Order $order)
    {
        return self::renderOrderItems($order);
    }

    public static function renderOrderItems(Order $order)
    {
        $details = $order->orderDetails;
        if (!$details || $details->count() === 0) {
            return '<span class="text-muted">---</span>';
        }

        $totalCount = $details->count();
        $isService = fn($detail) => $detail->type === 'service';
        $getLabel = fn($detail) => $isService($detail) ? __('order.tab.service') : __('order.tab.product');
        $getTypeClass = fn($detail) => $isService($detail) ? 'order-item-type--service' : 'order-item-type--product';

        $renderSingleItem = function ($detail) use ($getLabel, $getTypeClass) {
            $name = e($detail->type === 'service' ? ($detail->service?->name ?: '---') : ($detail->product?->name ?: '---'));
            $qty = (int) ($detail->qty ?: 1);
            $typeBadge = $detail->type === 'service'
                ? '<span class="order-item-type order-item-type--service">' . e($getLabel($detail)) . '</span>'
                : '';
            $itemIcon = $detail->type === 'service'
                ? '<i class="bx bx-wrench order-item-icon order-item-icon--service"></i>'
                : '<i class="bx bx-package order-item-icon"></i>';

            return "
                <div class=\"order-table-item\">
                    {$itemIcon}
                    {$typeBadge}
                    <span class=\"order-item-name\" title=\"{$name}\">{$name}</span>
                    <span class=\"order-item-qty\">×{$qty}</span>
                </div>
            ";
        };

        if ($totalCount <= 2) {
            $html = '<div class="order-table-items">';
            foreach ($details as $detail) {
                $html .= $renderSingleItem($detail);
            }
            $html .= '</div>';
            return $html;
        }

        // More than 2 items: display first 2 items + "+N more" expand & hover preview
        $firstTwo = $details->take(2);
        $remaining = $details->slice(2);
        $remainingCount = $remaining->count();

        $moreText = "+{$remainingCount} " . __('order.more_items');
        $collapseText = __('order.collapse_items');
        $allItemsText = __('order.all_items');

        $html = '<div class="order-table-items" x-data="{ expanded: false }">';

        // First 2 items
        foreach ($firstTwo as $detail) {
            $html .= $renderSingleItem($detail);
        }

        // Collapsible remaining items
        $html .= '<div class="order-table-items-extra" x-show="expanded" x-cloak>';
        foreach ($remaining as $detail) {
            $html .= $renderSingleItem($detail);
        }
        $html .= '</div>';

        // "+N more" interactive wrapper
        $html .= "
            <div class=\"order-table-more-wrapper\">
                <button type=\"button\" class=\"order-items-more-btn\" @click.stop=\"expanded = !expanded\" title=\"{$moreText}\">
                    <span x-text=\"expanded ? '{$collapseText}' : '{$moreText}'\">{$moreText}</span>
                    <i class=\"bx\" :class=\"expanded ? 'bx-chevron-up' : 'bx-chevron-down'\"></i>
                </button>
                <div class=\"order-items-hover-popover\" x-show=\"!expanded\">
                    <div class=\"popover-header-title\">
                        <i class=\"bx bx-package\"></i>
                        <span>{$allItemsText} ({$totalCount})</span>
                    </div>
        ";

        foreach ($remaining as $detail) {
            $html .= $renderSingleItem($detail);
        }

        $html .= '</div></div></div>';

        return $html;
    }


    protected function paymentStatusBadge(Order $order)
    {
        $status = $order->payment_status ?: 'Pending';
        $label = $this->orderStatusLabel($status);
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
