<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BookingRequest;
use App\Models\Barber;
use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\BookingPayment;
use App\Models\CustomerPoint;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderDetail;
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
use Throwable;

class BookingController extends Controller
{
    protected $layout = 'admin::pages.booking.';
    private $routeName = 'booking';

    public function __construct()
    {
        $this->middleware('permission:booking-view', ['only' => ['index', 'product', 'getPaymentDetails']]);
        $this->middleware('permission:booking-create', ['only' => ['onCreate', 'save', 'Save']]);
        $this->middleware('permission:booking-update', ['only' => ['onEdit', 'edit', 'save', 'Save', 'restore', 'updatePaymentStatus', 'cancelBooking', 'addPayment', 'updatePayment', 'sendPaymentReminder']]);
        $this->middleware('permission:booking-delete', ['only' => ['delete', 'restore', 'destroy', 'deletePayment']]);
    }

    public function index(Request $req)
    {
        if (!$req->status) {
            return redirect()->route('admin-' . $this->routeName . '-list', 'Pending');
        }

        $status = $this->normalizeBookingStatusTab($req->status);
        if (!$status) {
            return redirect()->route('admin-' . $this->routeName . '-list', 'Pending');
        }

        $dates = $this->dateRange($req);
        $paymentStatus = $this->bookingPaymentStatusForTab($status)
            ?: $this->normalizePaymentStatus($req->payment_status);

        $data['status'] = $status;
        $data['routeName'] = $this->routeName;
        $data['shop'] = $req->shop_id ? Shop::find($req->shop_id) : null;
        $data['barber'] = $req->barber_id ? Barber::find($req->barber_id) : null;
        $data['firstMonthDay'] = $dates['from'];
        $data['lastMonthDay'] = $dates['to'];

        $query = $status === 'trash'
            ? Booking::onlyTrashed()
            : Booking::query();

        $data['data'] = $query->with([
                'shop',
                'barber',
                'customer',
                'bookingDetail' => function ($detail) {
                    $detail->withTrashed()->with(['service', 'product']);
                },
            ])
            ->when($dates['from'] && $dates['to'], function ($query) use ($dates) {
                $query->whereBetween(DB::raw('DATE(booking_date)'), [$dates['from'], $dates['to']]);
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
                        ->orWhereHas('bookingDetail.product', function ($product) use ($req) {
                            $product->where('name', 'like', '%' . $req->search . '%');
                        })
                        ->orWhereHas('bookingDetail.service', function ($service) use ($req) {
                            $service->where('name', 'like', '%' . $req->search . '%');
                        });
                });
            })
            ->orderBy('booking_date', 'desc')
            ->paginate(50)
            ->appends($req->query());

        $this->decorateRows($data['data']);

        return view($this->layout . 'bookings', $data);
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
        return view($this->layout . 'createBooking', $this->formData());
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

        $booking = Booking::with(['customer', 'shop', 'barber', 'payments.createdBy'])->find($id);
        if (!$booking) {
            Session::flash('warning', __('booking.message.not_found'));
            return redirect()->route('admin-' . $this->routeName . '-list', 1);
        }

        $booking->setRelation(
            'bookingDetail',
            BookingDetail::with(['service', 'product'])
                ->where('booking_id', $booking->id)
                ->orderBy('id', 'asc')
                ->get()
        );

        return view($this->layout . 'createBooking', $this->formData($booking));
    }

    public function Save(BookingRequest $req, $id = '')
    {
        $bookingId = $id ?: $req->id;
        $dataCarts = $this->decodeCart($req);

        DB::beginTransaction();
        try {
            $booking = $bookingId
                ? Booking::with('bookingDetail')->findOrFail($bookingId)
                : new Booking();

            if ($booking->exists && $booking->payment_status !== 'Pending') {
                throw ValidationException::withMessages([
                    'payment_status' => 'Only Pending bookings can be updated.',
                ]);
            }

            if ($booking->exists) {
                $this->reverseBookingEffects($booking);
                BookingDetail::withTrashed()->where('booking_id', $booking->id)->forceDelete();
            }

            $booking->fill($this->payload($req, $booking));
            if (!$booking->exists) {
                $booking->invoice_number = $this->nextInvoiceNumber();
            }
            $booking->save();

            $totalPoint = 0;
            foreach ($dataCarts as $cart) {
                $detail = $this->createDetail($booking, $cart);
                $this->applyDetailStock($booking, $detail);
                $totalPoint += (int) ($detail->point ?? 0);
            }

            $booking->update(['total_point' => $totalPoint]);
            $booking->setRelation('bookingDetail', BookingDetail::where('booking_id', $booking->id)->get());
            $this->adjustCustomerPoint($booking, 1);
            $this->recordInitialPayment(
                $booking,
                (float) ($req->partial_payment_amount ?? 0),
                $req->pay_way,
                $req->booking_date
            );

            DB::commit();
            Session::flash('success', $bookingId ? __('booking.message.update_success') : __('booking.message.create_success'));

            $savedBooking = $booking->fresh();

            return response()->json([
                'message' => 'success',
                'status' => 200,
                'id' => $savedBooking->id,
                'payment_status' => $savedBooking->payment_status,
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
            'error' => 'Booking status is managed through payment status.',
        ], 422);
    }

    public function updatePaymentStatus(Request $req, $id)
    {
        $newStatus = $req->payment_status;
        if (!in_array($newStatus, ['Paid', 'Cancel'])) {
            return response()->json(['message' => 'error', 'error' => 'Invalid payment status.'], 422);
        }

        if ($newStatus === 'Cancel') {
            return $this->cancelBooking($id);
        }

        DB::beginTransaction();
        try {
            $booking = Booking::where('id', $id)->lockForUpdate()->firstOrFail();
            if ($booking->payment_status === 'Cancel') {
                throw ValidationException::withMessages([
                    'payment_status' => 'Cannot add payment to a rejected booking.',
                ]);
            }

            if ($booking->payment_status !== 'Paid') {
                $remaining = (float) $booking->remaining_amount;
                if ($remaining <= 0) {
                    throw ValidationException::withMessages([
                        'amount' => 'Booking does not have a remaining balance.',
                    ]);
                }

                BookingPayment::create([
                    'booking_id' => $booking->id,
                    'amount' => $remaining,
                    'note' => 'Paid remaining balance.',
                    'created_by' => Auth::id(),
                ]);
            }

            $booking = $this->syncBookingPaymentState($booking);
            DB::commit();
            Session::flash('success', __('booking.message.payment_status_success'));

            return response()->json($this->paymentResponse($booking));
        } catch (ValidationException $e) {
            DB::rollBack();

            return response()->json(['message' => 'error', 'errors' => $e->errors()], 422);
        } catch (Throwable $e) {
            DB::rollBack();
            Session::flash('warning', __('booking.message.payment_status_error'));

            return response()->json(['message' => 'error', 'error' => $e->getMessage()], 500);
        }
    }

    public function getPaymentDetails($id)
    {
        $booking = Booking::with(['payments.createdBy', 'customer', 'shop', 'barber'])->find($id);
        if (!$booking) {
            return response()->json(['message' => 'error', 'error' => 'Booking not found.'], 404);
        }

        $res = $this->paymentResponse($booking);
        $res['customer_name'] = $booking->customer?->name ?: ($booking->customer?->phone ?: 'Walk-in customer');
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
        $totalFormatted = '$' . number_format((float) ($booking->total_price ?? 0), 2);
        $paidFormatted = '$' . number_format((float) ($booking->paid_amount ?? 0), 2);
        $remainingFormatted = '$' . number_format($remaining, 2);
        $bookingDateFormatted = $booking->booking_date ? Carbon::parse($booking->booking_date)->format('d M Y, h:i A') : '---';

        $title = "Payment Reminder: Booking {$invoice}";
        $description = "Dear {$customerName}, this is a friendly reminder that you have an outstanding balance of {$remainingFormatted} (Total: {$totalFormatted}, Paid: {$paidFormatted}) for your booking on {$bookingDateFormatted}.";

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

        return response()->json([
            'message' => 'success',
            'status' => 200,
            'notification' => $notification,
            'success_message' => "Payment reminder sent successfully for booking {$invoice}."
        ]);
    }

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
            Session::flash('success', __('booking.message.reject_success'));

            return response()->json(['message' => 'success', 'status' => 200]);
        } catch (ValidationException $e) {
            DB::rollBack();

            return response()->json(['message' => 'error', 'errors' => $e->errors()], 422);
        } catch (Throwable $e) {
            DB::rollBack();
            Session::flash('warning', __('booking.message.reject_error'));

            return response()->json(['message' => 'error', 'error' => $e->getMessage()], 500);
        }
    }

    public function delete($id = '')
    {
        DB::beginTransaction();
        try {
            $booking = Booking::with('bookingDetail')->findOrFail($id);
            if ($booking->payment_status !== 'Cancel') {
                $this->reverseBookingEffects($booking);
            }

            BookingDetail::where('booking_id', $booking->id)->delete();
            $booking->delete();

            DB::commit();
            Session::flash('success', __('booking.message.delete_success'));

            return response()->json(['message' => 'success', 'status' => 200]);
        } catch (ValidationException $e) {
            DB::rollBack();

            return response()->json(['message' => 'unsuccess', 'errors' => $e->errors()], 422);
        } catch (Throwable $e) {
            DB::rollBack();
            Session::flash('warning', __('booking.message.delete_error'));

            return response()->json(['message' => 'unsuccess', 'status' => 404, 'error' => $e->getMessage()], 404);
        }
    }

    public function restore($id = '')
    {
        DB::beginTransaction();
        try {
            $booking = Booking::withTrashed()->findOrFail($id);
            $booking->restore();
            BookingDetail::withTrashed()->where('booking_id', $booking->id)->restore();
            $booking->setRelation('bookingDetail', BookingDetail::where('booking_id', $booking->id)->get());

            if ($booking->payment_status !== 'Cancel') {
                $this->applyBookingEffects($booking);
            }

            DB::commit();
            Session::flash('success', __('booking.message.restore_success'));

            return response()->json(['message' => 'success', 'status' => 200]);
        } catch (ValidationException $e) {
            DB::rollBack();

            return response()->json(['message' => 'unsuccess', 'errors' => $e->errors()], 422);
        } catch (Throwable $e) {
            DB::rollBack();
            Session::flash('warning', __('booking.message.restore_error'));

            return response()->json(['message' => 'unsuccess', 'status' => 404, 'error' => $e->getMessage()], 404);
        }
    }

    public function destroy($id = '')
    {
        DB::beginTransaction();
        try {
            $booking = Booking::withTrashed()->findOrFail($id);
            $booking->setRelation(
                'bookingDetail',
                BookingDetail::withTrashed()->where('booking_id', $booking->id)->get()
            );

            if (!$booking->trashed() && $booking->payment_status !== 'Cancel') {
                $this->reverseBookingEffects($booking);
            }

            BookingDetail::withTrashed()->where('booking_id', $booking->id)->forceDelete();
            BookingPayment::withTrashed()->where('booking_id', $booking->id)->forceDelete();
            $booking->forceDelete();

            DB::commit();
            Session::flash('success', __('booking.message.delete_success'));

            return response()->json(['message' => 'success', 'status' => 200]);
        } catch (ValidationException $e) {
            DB::rollBack();

            return response()->json(['message' => 'unsuccess', 'errors' => $e->errors()], 422);
        } catch (Throwable $e) {
            DB::rollBack();
            Session::flash('warning', __('booking.message.delete_error'));

            return response()->json(['message' => 'unsuccess', 'status' => 404, 'error' => $e->getMessage()], 404);
        }
    }

    public function report(Request $req)
    {
        $dates = $this->dateRange($req);
        $itemSelect = ['id', 'name', 'phone'];
        $paymentStatus = $this->normalizePaymentStatus($req->payment_status ?: $req->status);

        $data = BookingDetail::with([
            'product:id,name',
            'service:id,name',
            'booking' => function ($booking) use ($itemSelect) {
                $booking->with([
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
            ->whereHas('booking', function ($query) use ($req, $dates, $paymentStatus) {
                $query->whereDate('booking_date', '>=', $dates['from'])
                    ->whereDate('booking_date', '<=', $dates['to'])
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
            'id' => $booking->id,
            'invoice_number' => $booking->invoice_number ?: '#' . $booking->id,
            'total_price' => (float) ($booking->total_price ?? 0),
            'total_price_formatted' => '$' . number_format((float) ($booking->total_price ?? 0), 2),
            'paid_amount' => (float) ($booking->paid_amount ?? 0),
            'paid_amount_formatted' => '$' . number_format((float) ($booking->paid_amount ?? 0), 2),
            'remaining_amount' => (float) $booking->remaining_amount,
            'remaining_amount_formatted' => '$' . number_format((float) $booking->remaining_amount, 2),
            'payment_status' => $booking->payment_status ?: 'Pending',
            'payment_date' => $booking->payment_date,
            'payments' => $payments,
        ];
    }

    private function recordInitialPayment(Booking $booking, float $amount, ?string $paymentMethod = null, ?string $paymentDate = null): void
    {
        if ($amount <= 0) {
            return;
        }

        if ($amount > (float) ($booking->total_price ?? 0)) {
            throw ValidationException::withMessages([
                'partial_payment_amount' => 'Partial payment amount exceeds booking total.',
            ]);
        }

        BookingPayment::create([
            'booking_id' => $booking->id,
            'amount' => $amount,
            'payment_method' => $paymentMethod ?: ($booking->pay_way ?: 'Cash'),
            'payment_date' => $paymentDate
                ? Carbon::parse($paymentDate)->format('Y-m-d H:i:s')
                : ($booking->booking_date ? Carbon::parse($booking->booking_date)->format('Y-m-d H:i:s') : Carbon::now()->format('Y-m-d H:i:s')),
            'note' => 'Initial partial payment.',
            'created_by' => Auth::id(),
        ]);

        $this->syncBookingPaymentState($booking);
    }

    private function formData(Booking $booking = null)
    {
        $shopId = old('shop_id', $booking?->shop_id ?: request('shop_id'));
        $shop = $shopId
            ? Shop::find($shopId)
            : Shop::where('status', 1)->orderBy('id', 'asc')->first();

        if (!$booking) {
            $booking = (object) [
                'shop' => $shop,
                'barber' => null,
                'customer' => null,
                'bookingDetail' => [],
                'payments' => [],
                'paid_amount' => 0,
                'remaining_amount' => 0,
                'payment_status' => 'Pending',
            ];
        }

        $recentBookings = Booking::with(['customer', 'barber'])
            ->withCount('bookingDetail')
            ->when($shop?->id, function ($query) use ($shop) {
                $query->where('shop_id', $shop->id);
            })
            ->latest('booking_date')
            ->take(3)
            ->get();

        return [
            'id' => $booking->id ?? '',
            'data' => $booking,
            'routeName' => $this->routeName,
            'selectedShop' => $shop,
            'selectedBarber' => isset($booking->barber_id) && $booking->barber_id ? Barber::find($booking->barber_id) : null,
            'recentBookings' => $recentBookings,
        ];
    }

    private function payload(BookingRequest $req, Booking $booking)
    {
        return [
            'customer_id' => $req->customer_id,
            'shop_id' => $req->shop_id,
            'barber_id' => $req->barber_id,
            'booking_date' => $req->booking_date,
            'total_price' => $req->subTotal ?? 0,
            'total_discount' => $req->total_discount ?? 0,
            'total_commission' => $req->commissionTotal ?? 0,
            'pay_way' => $req->pay_way ?: $booking->pay_way ?: 'Cash',
            'payment_status' => $booking->payment_status ?: 'Pending',
        ];
    }

    private function decodeCart(BookingRequest $req)
    {
        $dataCarts = json_decode($req->dataCarts);
        if (!is_array($dataCarts) || count($dataCarts) === 0) {
            throw ValidationException::withMessages([
                'dataCarts' => 'Shopping cart is required',
            ]);
        }

        return $dataCarts;
    }

    private function createDetail(Booking $booking, $cart)
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
            ? ShopService::where('shop_id', $booking->shop_id)->where('service_id', $productId)->first()
            : ShopProduct::where('shop_id', $booking->shop_id)->where('product_id', $productId)->first();

        if (!$shopItem) {
            throw ValidationException::withMessages([
                'dataCarts' => 'Selected item does not belong to this shop.',
            ]);
        }

        return BookingDetail::create([
            'booking_id' => $booking->id,
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

    private function applyBookingEffects(Booking $booking)
    {
        foreach ($booking->bookingDetail as $detail) {
            $this->applyDetailStock($booking, $detail);
        }

        $this->adjustCustomerPoint($booking, 1);
    }

    private function reverseBookingEffects(Booking $booking)
    {
        $booking->loadMissing('bookingDetail');

        foreach ($booking->bookingDetail as $detail) {
            $this->reverseDetailStock($booking, $detail);
        }

        $this->adjustCustomerPoint($booking, -1);
    }

    private function applyDetailStock(Booking $booking, BookingDetail $detail)
    {
        if ($detail->type !== 'product' || !$detail->product_id) {
            return;
        }

        $qty = (int) ($detail->qty ?? 1);
        $stockOnHand = StockOnHand::where('shop_id', $booking->shop_id)
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
            'shop_id' => $booking->shop_id,
            'to_id' => $booking->customer_id,
            'qty' => $qty,
            'type' => 'customer',
            'status' => 1,
            'request_by' => Auth::id(),
            'request_by_type' => 'admin',
        ]);

        StockHistory::create([
            'transfer_id' => $booking->id,
            'stock_id' => $stockOut->id,
            'product_id' => $detail->product_id,
            'current_stock' => (int) $stockOnHand->current_stock,
            'stock_in' => 0,
            'stock_out' => $qty,
            'shop_id' => $booking->shop_id,
            'to_id' => $booking->customer_id,
            'qty' => $qty,
            'type' => 'customer',
            'transfer_type' => 'booking',
            'status' => 'stock_out',
            'request_by' => Auth::id(),
            'request_by_type' => 'admin',
        ]);
    }

    private function reverseDetailStock(Booking $booking, BookingDetail $detail)
    {
        if ($detail->type !== 'product' || !$detail->product_id) {
            return;
        }

        $qty = (int) ($detail->qty ?? 1);
        $stockOnHand = StockOnHand::where('shop_id', $booking->shop_id)
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

        $histories = StockHistory::where('transfer_id', $booking->id)
            ->where('shop_id', $booking->shop_id)
            ->where('product_id', $detail->product_id)
            ->where('status', 'stock_out')
            ->where('transfer_type', 'booking')
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

    private function adjustCustomerPoint(Booking $booking, int $direction)
    {
        $point = (int) ($booking->total_point ?? 0);
        $count = $booking->bookingDetail ? count($booking->bookingDetail) : 0;
        if (!$booking->customer_id || !$booking->shop_id || ($point === 0 && $count === 0)) {
            return;
        }

        $shop = $booking->shop ?: Shop::find($booking->shop_id);
        $lookup = [
            'shop_id' => $booking->shop_id,
            'customer_id' => $booking->customer_id,
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

    private function nextInvoiceNumber()
    {
        $code = Booking::withTrashed()
            ->whereNotNull('invoice_number')
            ->lockForUpdate()
            ->orderBy('id', 'desc')
            ->first();

        if (!$code) {
            return 'NO-0001';
        }

        $number = (int) str_replace('NO-', '', $code->invoice_number);

        return 'NO-' . str_pad($number + 1, 4, '0', STR_PAD_LEFT);
    }

    private function normalizeBookingStatusTab($status)
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

    private function bookingPaymentStatusForTab($status)
    {
        return match ($status) {
            'Pending', 'Partial', 'Paid' => $status,
            'Rejected' => 'Cancel',
            default => null,
        };
    }

    private function normalizePaymentStatus($status)
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

    public static function bookingStatusLabel($status)
    {
        return match ($status) {
            'Paid' => __('booking.status.paid'),
            'Partial' => __('booking.status.partial'),
            'Cancel' => __('booking.status.rejected'),
            default => __('booking.status.pending'),
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
            $item->total_price_title = '$' . number_format((float) ($item->total_price ?? 0), 2);
            $item->paid_amount_title = '$' . number_format((float) ($item->paid_amount ?? 0), 2);
            $item->remaining_amount_title = '$' . number_format((float) ($item->remaining_amount ?? 0), 2);
            $item->total_commission_title = '$' . number_format((float) ($item->total_commission ?? 0), 2);
            $item->total_discount_title = '$' . number_format((float) ($item->total_discount ?? 0), 2);
            $item->can_reject = $item->payment_status === 'Pending' && (float) ($item->paid_amount ?? 0) <= 0;
            $item->booking_date_title = $item->booking_date
                ? Carbon::parse($item->booking_date)->format('Y-m-d H:i')
                : '---';
        }
    }

    private function customerTitle(Booking $booking)
    {
        $name = e($booking->customer?->name ?: '---');
        $phone = e($booking->customer?->phone ?: '---');
        $point = (int) ($booking->total_point ?? 0);

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
        })->implode('');
    }

    private function paymentStatusBadge(Booking $booking)
    {
        $status = $booking->payment_status ?: 'Pending';
        $label = $this->bookingStatusLabel($status);
        $class = match ($status) {
            'Paid' => 'bg-success',
            'Partial' => 'bg-info',
            'Cancel' => 'bg-danger',
            default => 'bg-warning text-dark',
        };
        $date = $booking->payment_date
            ? '<small class="text-muted">' . e(Carbon::parse($booking->payment_date)->format('Y-m-d H:i')) . '</small>'
            : '';

        return '<span class="badge ' . $class . '" style="margin-bottom:7px;">' . e($label) . '</span>' . $date;
    }
}
