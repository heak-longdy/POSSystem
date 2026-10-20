<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class OrderBulkPrintService
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {}

    /**
     * Prepare orders data and context for bulk preview.
     *
     * @param array<int> $orderIds
     * @param string $documentType ('invoice' | 'delivery_slip')
     * @param string $locale ('en' | 'km')
     * @return array
     */
    public function prepareForPreview(array $orderIds, string $documentType, string $locale): array
    {
        $maxOrders = (int) config('order_printing.max_orders', 100);
        $maxDetailRows = (int) config('order_printing.max_detail_rows', 2000);

        $distinctIds = array_values(array_unique(array_filter(array_map('intval', $orderIds))));

        if (empty($distinctIds)) {
            throw ValidationException::withMessages([
                'order_ids' => __('order_bulk_print.validation.no_orders_selected'),
            ]);
        }

        if (count($distinctIds) > $maxOrders) {
            throw ValidationException::withMessages([
                'order_ids' => __('order_bulk_print.validation.order_ids_max', ['max' => $maxOrders]),
            ]);
        }

        // Query orders with all required relations in descending ID order
        $orders = Order::whereIn('id', $distinctIds)
            ->with([
                'customer',
                'shop',
                'barber',
                'orderDetails' => function ($detail) {
                    $detail->withTrashed()->with(['service', 'product.uom']);
                },
                'payments' => function ($payment) {
                    $payment->orderBy('id', 'asc')->with('createdBy');
                },
            ])
            ->orderBy('id', 'desc')
            ->get();

        // Ensure all requested IDs exist
        if ($orders->count() !== count($distinctIds)) {
            $foundIds = $orders->pluck('id')->all();
            $missingIds = array_diff($distinctIds, $foundIds);

            throw ValidationException::withMessages([
                'order_ids' => __('order_bulk_print.validation.some_orders_unavailable', [
                    'ids' => implode(', ', $missingIds),
                ]),
            ]);
        }

        // Count total detail rows across all orders
        $totalDetailRows = (int) $orders->sum(function (Order $order) {
            return $order->orderDetails->count();
        });

        if ($totalDetailRows > $maxDetailRows) {
            throw ValidationException::withMessages([
                'order_ids' => __('order_bulk_print.validation.detail_rows_exceeded', [
                    'total' => $totalDetailRows,
                    'max' => $maxDetailRows,
                ]),
            ]);
        }

        // Calculate previous balances if document is invoice
        $previousBalances = $documentType === 'invoice'
            ? $this->invoiceService->previousBalanceForMany($orders)
            : [];

        // Build isolated per-order render context
        $items = [];
        foreach ($orders as $order) {
            $cName = $order->customer?->name ?: ($order->customer?->phone ?: __('order.walk_in_customer', [], $locale));
            $cPhone = $order->customer?->phone ?: ($documentType === 'delivery_slip' ? '012399252' : '---');
            $cAddress = $order->customer?->address ?: ($order->shop?->address ?: ($locale === 'km' ? 'ផ្លូវជាតិលេខ៤ សង្កាត់ស្នោរ ក្រុងសំរោង ខេត្តតាកែវ' : 'National Road 4, Sangkat Sno, Samraong, Takeo'));

            $items[] = [
                'order' => $order,
                'printLocale' => $locale,
                'invoicePreviousBalance' => (float) ($previousBalances[$order->id] ?? 0.0),
                'customerName' => $cName,
                'customerPhone' => $cPhone,
                'customerAddress' => $cAddress,
                'printAreaId' => 'bulk_print_' . $documentType . '_' . $order->id . '_' . $locale,
            ];
        }

        return [
            'documentType' => $documentType,
            'locale' => $locale,
            'orders' => $orders,
            'items' => $items,
            'totalCount' => $orders->count(),
            'totalDetailRows' => $totalDetailRows,
        ];
    }
}
