<?php

namespace App\Services;

use App\Models\OrderDetail;

class OrderPricing
{
    public const AUTOMATIC = 'auto_unit_price_v1';
    public const MAX_CENTS = 999999999999999;

    /** Parse USD without rounding user input or accepting exponent notation. */
    public function usdCents($value): ?int
    {
        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            return null;
        }
        $value = trim((string) $value);
        $value = strtr($value, [
            '០' => '0', '១' => '1', '២' => '2', '៣' => '3', '៤' => '4',
            '៥' => '5', '៦' => '6', '៧' => '7', '៨' => '8', '៩' => '9',
        ]);
        if (!preg_match('/^([0-9]+)(?:[.,]([0-9]{0,2}))?$/D', $value, $parts)) {
            return null;
        }
        $whole = ltrim($parts[1], '0') ?: '0';
        if (strlen($whole) > 13) {
            return null;
        }

        return (int) $whole * 100 + (int) str_pad($parts[2] ?? '', 2, '0');
    }

    public function usdString(int $cents): string
    {
        return intdiv($cents, 100) . '.' . str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }

    public function automaticAmounts($original, $unit, int $qty): ?array
    {
        $original = $this->usdCents($original);
        $unit = $this->usdCents($unit);
        if ($original === null || $unit === null || $qty < 1) {
            return null;
        }
        $price = max($original, $unit);
        if ($price > 0 && $qty > intdiv(self::MAX_CENTS, $price)) {
            return null;
        }
        $discount = max($original - $unit, 0);

        return [
            'original_price' => $this->usdString($original),
            'unit_price' => $this->usdString($unit),
            // Existing consumers subtract this per-unit discount exactly once.
            'price' => $price / 100,
            'product_discount' => $discount / 100,
            'subtotal' => $price * $qty / 100,
            'discount' => $discount * $qty / 100,
            'total' => $unit * $qty / 100,
        ];
    }

    public function legacyUnitPrice(OrderDetail $detail): float
    {
        $type = $detail->type === 'service' ? 'service' : 'product';
        $price = (float) ($detail->price ?? 0);
        $discount = (float) ($detail->{$type . '_discount'} ?? 0);
        $discountType = $detail->{$type . '_discount_type'};

        if ($discountType === 'percent') {
            return max(0, $price - ($price * $discount / 100));
        }
        if (in_array($discountType, ['usd', 'khr'], true)) {
            return max(0, $price - $discount);
        }

        return $price;
    }

    public function line(OrderDetail $detail): array
    {
        $type = $detail->type === 'service' ? 'service' : 'product';
        $price = (float) ($detail->price ?? 0);
        $qty = max(1, (int) ($detail->qty ?? 1));
        $commission = (float) ($detail->{$type . '_commission'} ?? 0);
        $automatic = null;
        if ($type === 'product' && $detail->pricing_method === self::AUTOMATIC) {
            $automatic = $this->automaticAmounts($detail->original_price, $detail->unit_price, $qty);
            if ($automatic === null) {
                throw new \UnexpectedValueException('Invalid automatic product price.');
            }
            $price = $automatic['price'];
        }

        if ($detail->{$type . '_commission_type'} === 'percent') {
            $commission = $price * $commission / 100;
        }

        // Discounts are per unit. Round extended lines before summing them.
        $subtotal = $automatic['subtotal'] ?? round($price * $qty, 2);
        $total = $automatic['total'] ?? round($this->legacyUnitPrice($detail) * $qty, 2);

        return [
            'subtotal' => $subtotal,
            'discount' => $automatic['discount'] ?? round($subtotal - $total, 2),
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
