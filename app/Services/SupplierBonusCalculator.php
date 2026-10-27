<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockIn;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SupplierBonusCalculator
{
    /**
     * Resolve date range from quarter string or custom dates
     *
     * @param string|null $quarter e.g. '2026-Q1', '2026-Q2', '2026-Q3', '2026-Q4'
     * @param string|null $startDate YYYY-MM-DD
     * @param string|null $endDate YYYY-MM-DD
     * @return array ['start' => 'YYYY-MM-DD', 'end' => 'YYYY-MM-DD', 'quarter' => '2026-Qx']
     */
    public function resolvePeriod(?string $quarter = null, ?string $startDate = null, ?string $endDate = null): array
    {
        if ($quarter && preg_match('/^(\d{4})-Q([1-4])$/', $quarter, $m)) {
            $year = (int) $m[1];
            $qNum = (int) $m[2];

            $quarterRanges = [
                1 => ['01-01', '03-31'],
                2 => ['04-01', '06-30'],
                3 => ['07-01', '09-30'],
                4 => ['10-01', '12-31'],
            ];

            return [
                'start'   => "{$year}-{$quarterRanges[$qNum][0]}",
                'end'     => "{$year}-{$quarterRanges[$qNum][1]}",
                'quarter' => $quarter,
            ];
        }

        $start = $startDate ?: Carbon::now()->startOfQuarter()->format('Y-m-d');
        $end = $endDate ?: Carbon::now()->endOfQuarter()->format('Y-m-d');

        return [
            'start'   => $start,
            'end'     => $end,
            'quarter' => $this->detectQuarterFromDates($start, $end),
        ];
    }

    /**
     * Helper to detect standard quarter representation from date boundaries
     */
    private function detectQuarterFromDates(string $start, string $end): ?string
    {
        try {
            $s = Carbon::parse($start);
            $e = Carbon::parse($end);

            if ($s->year === $e->year) {
                $q = $s->quarter;
                if ($s->copy()->startOfQuarter()->format('Y-m-d') === $start &&
                    $e->copy()->endOfQuarter()->format('Y-m-d') === $end) {
                    return "{$s->year}-Q{$q}";
                }
            }
        } catch (\Exception) {
            // ignore
        }
        return null;
    }

    /**
     * Calculate purchase metrics from confirmed Stock-In transactions
     */
    public function aggregatePurchases(int $supplierId, string $startDate, string $endDate, ?int $shopId = null): array
    {
        $supplier = Supplier::find($supplierId);
        if (!$supplier) {
            return [
                'supplier_id'         => $supplierId,
                'supplier_name'       => '---',
                'start_date'          => $startDate,
                'end_date'            => $endDate,
                'total_purchases'     => 0.0,
                'total_qty'           => 0,
                'deliveries_count'    => 0,
                'distinct_products'   => 0,
                'items'               => [],
            ];
        }

        $query = StockIn::query()
            ->where('supplier_id', $supplierId)
            ->where('status', 1) // Confirmed stock-in
            ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate]);

        if ($shopId) {
            $query->where('shop_id', $shopId);
        }

        $stockIns = $query->with('product')->get();

        $totalPurchases = 0.0;
        $totalQty = 0;
        $productBreakdown = [];

        foreach ($stockIns as $item) {
            $qty = (int) ($item->qty ?? 0);
            $cost = (float) ($item->product?->cost ?? 0.0);
            $lineTotal = $qty * $cost;

            $totalQty += $qty;
            $totalPurchases += $lineTotal;

            $pId = $item->product_id;
            if (!isset($productBreakdown[$pId])) {
                $productBreakdown[$pId] = [
                    'product_id'   => $pId,
                    'product_name' => $item->product?->name ?? "Product #{$pId}",
                    'product_code' => $item->product?->code ?? '',
                    'unit_cost'    => $cost,
                    'total_qty'    => 0,
                    'total_amount' => 0.0,
                ];
            }
            $productBreakdown[$pId]['total_qty'] += $qty;
            $productBreakdown[$pId]['total_amount'] += $lineTotal;
        }

        // Sort items by total amount descending
        uasort($productBreakdown, fn($a, $b) => $b['total_amount'] <=> $a['total_amount']);

        return [
            'supplier_id'       => $supplier->id,
            'supplier_name'     => $supplier->name,
            'start_date'        => $startDate,
            'end_date'          => $endDate,
            'total_purchases'   => round($totalPurchases, 2),
            'total_qty'         => $totalQty,
            'deliveries_count'  => $stockIns->count(),
            'distinct_products' => count($productBreakdown),
            'items'             => array_values($productBreakdown),
        ];
    }

    /**
     * Calculate bonus using selected model:
     * - 'percentage': total_purchases * (rate / 100)
     * - 'tiered': threshold-based percentage
     * - 'fixed': flat lump-sum if target reached
     * - 'per_unit': total_qty * unit_rate
     */
    public function computeBonus(
        float $totalPurchases,
        int $totalQty,
        string $model = 'percentage',
        float $rateOrTarget = 0.0,
        float $bonusAmount = 0.0
    ): array {
        $suggestedBonus = 0.0;
        $calculationNote = '';

        switch ($model) {
            case 'tiered':
                // Standard default tiered rebate model
                if ($totalPurchases >= 100000) {
                    $effectiveRate = 5.0;
                } elseif ($totalPurchases >= 50000) {
                    $effectiveRate = 3.5;
                } elseif ($totalPurchases >= 20000) {
                    $effectiveRate = 2.0;
                } else {
                    $effectiveRate = 0.0;
                }
                $suggestedBonus = ($totalPurchases * $effectiveRate) / 100.0;
                $calculationNote = sprintf(
                    'Tiered Rebate: Purchases $%s qualified for %.1f%% tier = $%s',
                    number_format($totalPurchases, 2),
                    $effectiveRate,
                    number_format($suggestedBonus, 2)
                );
                break;

            case 'fixed':
                $target = $rateOrTarget;
                if ($totalPurchases >= $target) {
                    $suggestedBonus = $bonusAmount;
                    $calculationNote = sprintf(
                        'Target Achieved: Purchases $%s >= Target $%s. Flat Bonus = $%s',
                        number_format($totalPurchases, 2),
                        number_format($target, 2),
                        number_format($suggestedBonus, 2)
                    );
                } else {
                    $suggestedBonus = 0.0;
                    $calculationNote = sprintf(
                        'Target Not Reached: Purchases $%s < Target $%s. Bonus = $0.00',
                        number_format($totalPurchases, 2),
                        number_format($target, 2)
                    );
                }
                break;

            case 'per_unit':
                $unitRate = $rateOrTarget;
                $suggestedBonus = $totalQty * $unitRate;
                $calculationNote = sprintf(
                    'Per-Unit Incentive: %s units @ $%s/unit = $%s',
                    number_format($totalQty),
                    number_format($unitRate, 2),
                    number_format($suggestedBonus, 2)
                );
                break;

            case 'percentage':
            default:
                $rate = $rateOrTarget;
                $suggestedBonus = ($totalPurchases * $rate) / 100.0;
                $calculationNote = sprintf(
                    'Volume Bonus: $%s purchases @ %.2f%% = $%s',
                    number_format($totalPurchases, 2),
                    $rate,
                    number_format($suggestedBonus, 2)
                );
                break;
        }

        return [
            'model'            => $model,
            'suggested_bonus'  => round($suggestedBonus, 2),
            'calculation_note' => $calculationNote,
        ];
    }
}
