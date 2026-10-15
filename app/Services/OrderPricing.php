<?php

namespace App\Services;

use App\Models\OrderDetail;

class OrderPricing
{
    public function line(OrderDetail $detail): array
    {
        $type = $detail->type === 'service' ? 'service' : 'product';
        $price = (float) ($detail->price ?? 0);
        $qty = max(1, (int) ($detail->qty ?? 1));
        $discount = (float) ($detail->{$type . '_discount'} ?? 0);
        $discountType = $detail->{$type . '_discount_type'};
        $commission = (float) ($detail->{$type . '_commission'} ?? 0);

        if ($discountType === 'percent') {
            $unitTotal = max(0, $price - ($price * $discount / 100));
        } elseif (in_array($discountType, ['usd', 'khr'], true)) {
            $unitTotal = max(0, $price - $discount);
        } else {
            $unitTotal = $price;
        }

        if ($detail->{$type . '_commission_type'} === 'percent') {
            $commission = $price * $commission / 100;
        }

        // Discounts are per unit. Round extended lines before summing them.
        $subtotal = round($price * $qty, 2);
        $total = round($unitTotal * $qty, 2);

        return [
            'subtotal' => $subtotal,
            'discount' => round($subtotal - $total, 2),
            'commission' => round($commission * $qty, 2),
            'total' => $total,
        ];
    }

    public function totals(iterable $details): array
    {
        $subtotal = $discount = $commission = $total = 0;
        foreach ($details as $detail) {
            $line = $this->line($detail);
            $subtotal += $line['subtotal'];
            $discount += $line['discount'];
            $commission += $line['commission'];
            $total += $line['total'];
        }

        return [
            'subtotal' => round($subtotal, 2),
            'total_discount' => round($discount, 2),
            'total_commission' => round($commission, 2),
            // Preserve the Create Order page's existing commission deduction.
            'total_price' => round($total - $commission, 2),
        ];
    }
}
