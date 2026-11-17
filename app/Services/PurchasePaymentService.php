<?php

namespace App\Services;

use App\Models\InvoiceSequence;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PurchasePaymentService
{
    /**
     * Generate the next unique payment reference number.
     */
    public function generateNextPaymentNumber(?Carbon $date = null): string
    {
        $date = $date ?? now();
        $prefixKey = 'PAY-PUR-' . $date->format('Ym') . '-';
        $digitLength = 4;
        $startNumber = 1;

        return DB::transaction(function () use ($prefixKey, $digitLength, $startNumber) {
            if (!Schema::hasTable('invoice_sequences')) {
                return $this->fallbackGenerateNext($prefixKey, $digitLength, $startNumber);
            }

            $sequence = InvoiceSequence::where('prefix_key', $prefixKey)
                ->lockForUpdate()
                ->first();

            if (!$sequence) {
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
        }, 3);
    }

    private function detectMaxExistingNumber(string $prefixKey): int
    {
        if (!Schema::hasTable('purchase_payments')) {
            return 0;
        }

        $latest = PurchasePayment::withTrashed()
            ->where('payment_number', 'LIKE', $prefixKey . '%')
            ->orderByRaw('LENGTH(payment_number) DESC')
            ->orderBy('payment_number', 'desc')
            ->value('payment_number');

        if (!$latest) {
            return 0;
        }

        $suffix = substr($latest, strlen($prefixKey));
        return is_numeric($suffix) ? (int) $suffix : 0;
    }

    private function fallbackGenerateNext(string $prefixKey, int $digitLength, int $startNumber): string
    {
        $maxExisting = $this->detectMaxExistingNumber($prefixKey);
        $nextNumber = max($maxExisting + 1, $startNumber);
        return $prefixKey . str_pad((string) $nextNumber, $digitLength, '0', STR_PAD_LEFT);
    }

    /**
     * Record a disbursement payment to a supplier for a purchase.
     */
    public function recordPayment(Purchase $purchase, array $data, ?UploadedFile $receiptFile = null): PurchasePayment
    {
        return DB::transaction(function () use ($purchase, $data, $receiptFile) {
            $amount = round((float) ($data['amount'] ?? 0.0), 2);
            $remaining = $purchase->remaining_amount;

            if ($amount <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'Payment amount must be greater than 0.',
                ]);
            }

            if ($amount > $remaining) {
                throw ValidationException::withMessages([
                    'amount' => "Payment amount ($" . number_format($amount, 2) . ") cannot exceed the remaining balance ($" . number_format($remaining, 2) . ").",
                ]);
            }

            $receiptPath = null;
            $receiptName = null;

            if ($receiptFile && $receiptFile->isValid()) {
                $dir = public_path('uploads/purchases/receipts');
                if (!File::isDirectory($dir)) {
                    File::makeDirectory($dir, 0755, true, true);
                }

                $receiptName = $receiptFile->getClientOriginalName();
                $filename = 'slip_' . time() . '_' . uniqid() . '.' . $receiptFile->getClientOriginalExtension();
                $receiptFile->move($dir, $filename);
                $receiptPath = 'uploads/purchases/receipts/' . $filename;
            }

            $paymentNumber = $this->generateNextPaymentNumber(
                !empty($data['payment_date']) ? Carbon::parse($data['payment_date']) : null
            );

            $payment = PurchasePayment::create([
                'purchase_id'    => $purchase->id,
                'supplier_id'    => $purchase->supplier_id,
                'payment_number' => $paymentNumber,
                'amount'         => $amount,
                'payment_date'   => $data['payment_date'] ?? now()->toDateString(),
                'payment_method' => $data['payment_method'] ?? 'Cash',
                'reference_no'   => $data['reference_no'] ?? null,
                'receipt_path'   => $receiptPath,
                'receipt_name'   => $receiptName,
                'note'           => $data['note'] ?? null,
                'created_by'     => Auth::id() ?? 1,
            ]);

            $this->syncPaymentStatus($purchase);

            return $payment;
        });
    }

    /**
     * Remove / void a payment transaction.
     */
    public function deletePayment(PurchasePayment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $purchase = $payment->purchase;
            $payment->delete();

            if ($purchase) {
                $this->syncPaymentStatus($purchase);
            }
        });
    }

    /**
     * Recompute and update total paid amount and payment status on purchase.
     */
    public function syncPaymentStatus(Purchase $purchase): void
    {
        $totalPaid = round((float) $purchase->payments()->sum('amount'), 2);
        $totalAmount = round((float) $purchase->total_amount, 2);

        $status = 'unpaid';
        if ($totalPaid >= $totalAmount && $totalAmount > 0) {
            $status = 'paid';
        } elseif ($totalPaid > 0) {
            $status = 'partial';
        }

        $purchase->update([
            'paid_amount'    => $totalPaid,
            'payment_status' => $status,
        ]);
    }
}
