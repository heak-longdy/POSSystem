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
                'createdBy',
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

        // Build isolated per-order render context for invoice/delivery_slip
        $items = [];
        if ($documentType !== 'consolidated') {
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
        }

        // Build consolidated products grouping and metadata
        $consolidatedProducts = [];
        $overallQuantity = 0;

        if ($documentType === 'consolidated') {
            $productMap = [];

            foreach ($orders as $order) {
                foreach ($order->orderDetails as $detail) {
                    if ($detail->deleted_at !== null) {
                        continue;
                    }

                    $isProduct = $detail->type === 'product' || ($detail->product_id && $detail->type !== 'service');
                    if (!$isProduct || !$detail->product_id) {
                        continue;
                    }

                    $qty = (int) ($detail->qty ?: 0);
                    if ($qty <= 0) {
                        continue;
                    }

                    $productId = (int) $detail->product_id;

                    if (!isset($productMap[$productId])) {
                        $product = $detail->product;

                        $rawName = $product?->name ?? '---';
                        if (is_string($rawName) && str_starts_with(trim($rawName), '{')) {
                            $decoded = json_decode($rawName, true);
                            $productName = $decoded[$locale] ?? ($decoded['km'] ?? ($decoded['en'] ?? $rawName));
                        } else {
                            $productName = $rawName;
                        }

                        $rawUom = $product?->uom?->name ?? '---';
                        if (is_string($rawUom) && str_starts_with(trim($rawUom), '{')) {
                            $decodedUom = json_decode($rawUom, true);
                            $uomName = $decodedUom[$locale] ?? ($decodedUom['km'] ?? ($decodedUom['en'] ?? $rawUom));
                        } else {
                            $uomName = $rawUom;
                        }

                        $productCode = $product?->code ?: ($product?->barcode ?: sprintf('%04d', $productId));

                        $productMap[$productId] = [
                            'product_id' => $productId,
                            'product_code' => (string) $productCode,
                            'product_name' => (string) $productName,
                            'uom_name' => (string) ($uomName ?: '---'),
                            'total_qty' => 0,
                            'subunits' => 0,
                            'order_ids' => [],
                        ];
                    }

                    $productMap[$productId]['total_qty'] += $qty;
                    $productMap[$productId]['order_ids'][] = $order->id;
                    $overallQuantity += $qty;
                }
            }

            foreach ($productMap as &$row) {
                $row['order_ids'] = array_values(array_unique($row['order_ids']));
                $row['orders_count'] = count($row['order_ids']);
            }
            unset($row);

            $consolidatedProducts = array_values($productMap);

            usort($consolidatedProducts, function ($a, $b) {
                $cmp = strnatcasecmp($a['product_code'], $b['product_code']);
                if ($cmp !== 0) return $cmp;
                $cmp = strnatcasecmp($a['product_name'], $b['product_name']);
                if ($cmp !== 0) return $cmp;
                return $a['product_id'] <=> $b['product_id'];
            });
        }

        $shopNames = $orders->pluck('shop.name')->filter()->unique()->values();
        $delLocation = $shopNames->isNotEmpty() ? $shopNames->implode(', ') : 'ER-MLP1-TBK(PHE SUYHORTH)';

        $deliveryDates = $orders->pluck('delivery_date')->filter()->unique()->values();
        if ($deliveryDates->isNotEmpty()) {
            $delDate = \Carbon\Carbon::parse($deliveryDates->first())->format('d/m/Y');
        } else {
            $orderDates = $orders->pluck('order_date')->filter()->unique()->values();
            $delDate = $orderDates->isNotEmpty()
                ? \Carbon\Carbon::parse($orderDates->first())->addDays(2)->format('d/m/Y')
                : now()->addDays(2)->format('d/m/Y');
        }

        $drivers = $orders->pluck('barber.name')->filter()->unique()->values();
        $driverName = $drivers->isNotEmpty() ? $drivers->implode(', ') : ($locale === 'km' ? 'សាខាអ្នកដឹក' : 'Delivery Branch');
        $documentNo = sprintf('LOD-%s-%03d', date('ymd'), $orders->count());
        $stopsCount = $orders->count();

        return [
            'documentType' => $documentType,
            'locale' => $locale,
            'orders' => $orders,
            'items' => $items,
            'totalCount' => $orders->count(),
            'totalDetailRows' => $totalDetailRows,
            'consolidatedProducts' => $consolidatedProducts,
            'overallQuantity' => $overallQuantity,
            'totalProducts' => count($consolidatedProducts),
            'delLocation' => $delLocation,
            'delDate' => $delDate,
            'driverName' => $driverName,
            'stopsCount' => $stopsCount,
            'documentNo' => $documentNo,
            'reportDate' => now()->format('m/d/y'),
            'reportTime' => now()->format('g:iA'),
        ];
    }
}
