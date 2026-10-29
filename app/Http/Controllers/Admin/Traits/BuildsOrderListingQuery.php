<?php

namespace App\Http\Controllers\Admin\Traits;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait BuildsOrderListingQuery
{
    /**
     * Normalize the order status tab key.
     */
    protected function normalizeOrderStatusTab($status)
    {
        $status = strtolower((string) $status);

        return match ($status) {
            '', '1', 'pending' => 'Pending',
            'partial' => 'Partial',
            'paid' => 'Paid',
            'rejected', 'cancel' => 'Rejected',
            'trash' => 'trash',
            'all', 'all_outstanding', 'all-outstanding' => 'all',
            default => null,
        };
    }

    /**
     * Map order status tab to corresponding payment status.
     */
    protected function orderPaymentStatusForTab($status)
    {
        return match ($status) {
            'Pending', 'Partial', 'Paid' => $status,
            'Rejected' => 'Cancel',
            default => null,
        };
    }

    /**
     * Normalize payment status filter.
     */
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

    /**
     * Build base query for order listing matching OrderController predicates.
     */
    protected function buildOrderListingQuery(Request $req, ?string $status = null): Builder
    {
        $normalizedStatus = $status ? $this->normalizeOrderStatusTab($status) : $this->normalizeOrderStatusTab($req->status ?? 'all');
        $normalizedStatus = $normalizedStatus ?? 'all';

        $paymentStatus = $this->orderPaymentStatusForTab($normalizedStatus)
            ?: $this->normalizePaymentStatus($req->payment_status);

        $query = $normalizedStatus === 'trash'
            ? Order::forUser()->onlyTrashed()
            : Order::forUser();

        $from = $req->from_date ?: null;
        $to = $req->to_date ?: null;

        return $query
            ->when($from, function ($q) use ($from) {
                $q->whereDate('order_date', '>=', $from);
            })
            ->when($to, function ($q) use ($to) {
                $q->whereDate('order_date', '<=', $to);
            })
            ->when($req->shop_id, function ($q) use ($req) {
                $q->where('shop_id', $req->shop_id);
            })
            ->when($paymentStatus, function ($q) use ($paymentStatus) {
                $q->where('payment_status', $paymentStatus);
            })
            ->when($normalizedStatus === 'all' && !$paymentStatus, function ($q) {
                $q->where('payment_status', '!=', 'Cancel');
            })
            ->when($req->search, function ($q) use ($req) {
                $search = trim((string) $req->search);
                $numericId = (ctype_digit($search) || is_numeric($search)) ? (int) ltrim($search, '0') : null;
                $q->where(function ($sub) use ($search, $numericId) {
                    $sub->where('invoice_number', 'like', '%' . $search . '%')
                        ->orWhereHas('shop', function ($shop) use ($search) {
                            $shop->where('name', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('customer', function ($customer) use ($search, $numericId) {
                            $customer->where('name', 'like', '%' . $search . '%')
                                ->orWhere('phone', 'like', '%' . $search . '%')
                                ->orWhere('code', 'like', '%' . $search . '%');
                            if ($numericId && $numericId > 0) {
                                $customer->orWhere('id', $numericId);
                            }
                        })
                        ->orWhereHas('orderDetails.product', function ($product) use ($search) {
                            $product->where('name', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('orderDetails.service', function ($service) use ($search) {
                            $service->where('name', 'like', '%' . $search . '%');
                        });
                });
            });
    }
}
