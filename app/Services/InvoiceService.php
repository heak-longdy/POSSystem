<?php

namespace App\Services;

use App\Models\InvoiceSequence;
use App\Models\InvoiceSetting;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InvoiceService
{
    /**
     * Generate the next unique invoice number in a concurrency-safe atomic transaction.
     */
    public function generateNextInvoiceNumber(?Carbon $date = null): string
    {
        $date = $date ?? now();
        $setting = $this->getSetting();

        $prefixKey = $this->buildPrefixKey($setting, $date);
        $digitLength = max(1, (int) ($setting->digit_length ?? 4));
        $startNumber = max(1, (int) ($setting->start_number ?? 1));

        return DB::transaction(function () use ($prefixKey, $digitLength, $startNumber) {
            // Check if invoice_sequences table exists (safety fallback)
            if (!Schema::hasTable('invoice_sequences')) {
                return $this->fallbackGenerateNext($prefixKey, $digitLength, $startNumber);
            }

            // Lock the sequence row exclusively for update
            $sequence = InvoiceSequence::where('prefix_key', $prefixKey)
                ->lockForUpdate()
                ->first();

            if (!$sequence) {
                // Initialize sequence by detecting highest existing number in orders table
                $maxExisting = $this->detectMaxExistingNumber($prefixKey);
                $initialNumber = max($maxExisting, $startNumber - 1);

                $sequence = InvoiceSequence::create([
                    'prefix_key'  => $prefixKey,
                    'last_number' => $initialNumber,
                ]);
            }

            $nextNumber = $sequence->last_number + 1;
            $sequence->update(['last_number' => $nextNumber]);

            $paddedNumber = str_pad((string) $nextNumber, $digitLength, '0', STR_PAD_LEFT);

            return $prefixKey . $paddedNumber;
        }, 3); // Automatically retry up to 3 times in case of deadlock
    }

    /**
     * Generate preview invoice number string for display in UI.
     */
    public function preview(?array $params = null): string
    {
        $setting = $params ? (object) $params : $this->getSetting();
        $date = now();

        $prefixKey = $this->buildPrefixKey($setting, $date);
        $digitLength = max(1, (int) ($setting->digit_length ?? 4));
        $number = (int) ($setting->start_number ?? 1);

        $paddedNumber = str_pad((string) $number, $digitLength, '0', STR_PAD_LEFT);

        return $prefixKey . $paddedNumber;
    }

    /**
     * Build the prefix key based on prefix, separator, and date format.
     * E.g. "INV-2026-", "NO-", "ORD202609-"
     */
    public function buildPrefixKey($setting, Carbon $date): string
    {
        $prefix = trim((string) ($setting->prefix ?? 'NO'));
        $separator = (string) ($setting->separator ?? '-');
        $dateFormat = (string) ($setting->date_format ?? 'none');

        $parts = [];
        if ($prefix !== '') {
            $parts[] = $prefix;
        }

        if ($dateFormat !== 'none' && !empty($dateFormat)) {
            $dateString = match ($dateFormat) {
                'Y'     => $date->format('Y'),
                'Ym'    => $date->format('Ym'),
                'Y-m'   => $date->format('Y-m'),
                'Ymd'   => $date->format('Ymd'),
                default => '',
            };

            if ($dateString !== '') {
                $parts[] = $dateString;
            }
        }

        if (empty($parts)) {
            return '';
        }

        $joined = implode($separator, $parts);
        if ($separator !== '' && !str_ends_with($joined, $separator)) {
            $joined .= $separator;
        }

        return $joined;
    }

    /**
     * Scan existing orders to detect the maximum existing sequence number for a prefix.
     */
    protected function detectMaxExistingNumber(string $prefixKey): int
    {
        try {
            $orders = Order::withTrashed()
                ->whereNotNull('invoice_number')
                ->where('invoice_number', 'like', $prefixKey . '%')
                ->pluck('invoice_number');

            $max = 0;
            $prefixLen = strlen($prefixKey);

            foreach ($orders as $inv) {
                $numStr = substr($inv, $prefixLen);
                if (is_numeric($numStr)) {
                    $val = (int) $numStr;
                    if ($val > $max) {
                        $max = $val;
                    }
                }
            }

            return $max;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Fallback invoice generator if invoice_sequences table is not yet migrated.
     */
    protected function fallbackGenerateNext(string $prefixKey, int $digitLength, int $startNumber): string
    {
        $code = Order::withTrashed()
            ->whereNotNull('invoice_number')
            ->where('invoice_number', 'like', $prefixKey . '%')
            ->orderBy('id', 'desc')
            ->first();

        if (!$code) {
            return $prefixKey . str_pad((string) $startNumber, $digitLength, '0', STR_PAD_LEFT);
        }

        $numPart = (int) str_replace($prefixKey, '', $code->invoice_number);
        $next = max($numPart + 1, $startNumber);

        return $prefixKey . str_pad((string) $next, $digitLength, '0', STR_PAD_LEFT);
    }

    /**
     * Get active invoice setting instance or defaults.
     */
    public function getSetting(): InvoiceSetting
    {
        if (Schema::hasTable('invoice_settings')) {
            return InvoiceSetting::getActive();
        }

        // Return a mock default if table doesn't exist yet
        $default = new InvoiceSetting();
        $default->prefix = 'NO';
        $default->separator = '-';
        $default->date_format = 'none';
        $default->digit_length = 4;
        $default->start_number = 1;
        $default->reset_cycle = 'never';
        return $default;
    }
}
