<?php

namespace App\Services;

use App\Models\OtherIncome;
use App\Models\OtherIncomeCategory;
use App\Models\OtherIncomePayment;
use App\Models\Supplier;
use App\Models\UploadFile;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OtherIncomeService
{
    /**
     * Build base filtered query for Other Incomes
     */
    public function queryIncomes(Request $req, ?int $status = 1): Builder
    {
        if ($status === null || $status === 'trash') {
            $query = OtherIncome::onlyTrashed();
        } else {
            $query = OtherIncome::where('status', $status);
        }

        $query->with(['category', 'supplier', 'shop', 'createdBy', 'payments']);

        $query->dateBetween($req->get('from_date'), $req->get('to_date'))
              ->ofSupplier($req->filled('supplier_id') ? (int) $req->supplier_id : null)
              ->ofCategory($req->filled('category_id') ? (int) $req->category_id : null)
              ->ofShop($req->filled('shop_id') ? (int) $req->shop_id : null)
              ->ofPaymentStatus($req->get('payment_status'));

        if ($req->filled('quarter')) {
            $query->where('calculation_quarter', $req->quarter);
        }

        if ($req->filled('search')) {
            $search = trim($req->search);
            $query->where(function ($q) use ($search) {
                $q->where('income_number', 'LIKE', '%' . $search . '%')
                  ->orWhere('title', 'LIKE', '%' . $search . '%')
                  ->orWhere('reference_no', 'LIKE', '%' . $search . '%')
                  ->orWhere('description', 'LIKE', '%' . $search . '%')
                  ->orWhereHas('supplier', function ($sq) use ($search) {
                      $sq->where('name', 'LIKE', '%' . $search . '%');
                  })
                  ->orWhereHas('category', function ($cq) use ($search) {
                      $cq->where('name', 'LIKE', '%' . $search . '%');
                  });
            });
        }

        return $query->orderBy('income_date', 'desc')->orderBy('id', 'desc');
    }

    /**
     * Calculate summary metrics across filtered records
     */
    public function calculateSummary(Builder $query): array
    {
        $incomes = (clone $query)->get();

        $totalExpected   = 0.0;
        $totalReceived   = 0.0;
        $totalRemaining  = 0.0;
        $supplierBonusTotal = 0.0;
        $supplierAmounts = [];
        $categoryCounts  = [];
        $statusCounts    = [
            'paid'    => 0,
            'partial' => 0,
            'pending' => 0,
        ];

        foreach ($incomes as $item) {
            $exp = (float) ($item->expected_amount ?? 0);
            $rec = (float) ($item->received_amount ?? 0);
            $rem = (float) ($item->remaining_amount ?? 0);

            $totalExpected  += $exp;
            $totalReceived  += $rec;
            $totalRemaining += $rem;

            $statusKey = $item->payment_status ?? 'pending';
            if (isset($statusCounts[$statusKey])) {
                $statusCounts[$statusKey]++;
            }

            if ($item->supplier_id) {
                $sId = $item->supplier_id;
                $supplierAmounts[$sId] = ($supplierAmounts[$sId] ?? 0.0) + $rec;
                $supplierBonusTotal += $rec;
            }

            $catName = $item->category?->name ?: 'General';
            $categoryCounts[$catName] = ($categoryCounts[$catName] ?? 0.0) + $rec;
        }

        $topSupplierName = '---';
        $topSupplierAmount = 0.0;
        if (!empty($supplierAmounts)) {
            arsort($supplierAmounts);
            $topId = array_key_first($supplierAmounts);
            $topSupplierAmount = $supplierAmounts[$topId];
            $topSupplierName = Supplier::find($topId)?->name ?: "Supplier #{$topId}";
        }

        return [
            'total_transactions'     => $incomes->count(),
            'total_expected'         => round($totalExpected, 2),
            'total_received'         => round($totalReceived, 2),
            'total_remaining'        => round($totalRemaining, 2),
            'supplier_bonus_total'   => round($supplierBonusTotal, 2),
            'status_counts'          => $statusCounts,
            'top_supplier_name'      => $topSupplierName,
            'top_supplier_amount'    => round($topSupplierAmount, 2),
            'category_breakdown'     => $categoryCounts,
        ];
    }

    /**
     * Save an Other Income record (create or update)
     */
    public function saveIncome(Request $request, $id = null): OtherIncome
    {
        return DB::transaction(function () use ($request, $id) {
            $income = $id ? OtherIncome::lockForUpdate()->findOrFail($id) : new OtherIncome();
            $receivedAmount = round((float) $request->input('received_amount'), 2);
            $income->received_amount = $receivedAmount;

            $income->category_id  = $request->input('category_id');
            $income->supplier_id  = $request->filled('supplier_id') ? $request->input('supplier_id') : null;
            $income->shop_id      = $request->filled('shop_id') ? $request->input('shop_id') : null;
            $income->title        = $request->input('title');
            $income->income_date  = $request->input('income_date', date('Y-m-d'));
            $income->reference_no = $request->input('reference_no', $income->reference_no);
            $income->description  = $request->input('description');
            $income->status       = (int) $request->input('status', 1);

            // Handle file upload
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $destination = '/other_income/documents';
                $oldPath = $income->attachment_path;
                $uploaded = UploadFile::saveFile2($destination, $file, $oldPath ? basename($oldPath) : '');
                $income->attachment_path = 'uploads' . $destination . '/' . $uploaded['unique_name'];
                $income->attachment_name = $uploaded['original_name'];
            }

            $income->save();

            // Record initial payment record if paid right away
            if (!$id && $initialPayment > 0) {
                $paymentNumber = $this->generatePaymentNumber();
                $payment = new OtherIncomePayment();
                $payment->other_income_id = $income->id;
                $payment->payment_number  = $paymentNumber;
                $payment->amount          = $initialPayment;
                $payment->payment_date    = $request->input('payment_date', $income->income_date);
                $payment->payment_method  = $request->input('payment_method', 'Cash');
                $payment->reference_no    = $request->input('payment_reference_no');
                $payment->note            = $request->input('payment_note', 'Initial payment');
                $payment->created_by      = auth()->check() ? auth()->id() : 1;

                if ($request->hasFile('payment_receipt')) {
                    $receiptFile = $request->file('payment_receipt');
                    $destination = '/other_income/receipts';
                    $uploadedReceipt = UploadFile::saveFile2($destination, $receiptFile);
                    $payment->receipt_path = 'uploads' . $destination . '/' . $uploadedReceipt['unique_name'];
                    $payment->receipt_name = $uploadedReceipt['original_name'];
                }

                $payment->save();
                $this->syncIncomeBalances($income);
            }

            if ($id) {
                $recordedAmount = (float) $income->payments()->sum('amount');
                $adjustment = round($receivedAmount - $recordedAmount, 2);

                if ($adjustment != 0) {
                    // Keep original receipts; a signed adjustment reconciles the edited total.
                    $this->recordPayment($income, [
                        'amount' => $adjustment,
                        'payment_date' => date('Y-m-d'),
                        'payment_method' => 'Adjustment',
                        'note' => __('other_income.message.received_amount_adjusted', [
                            'from' => number_format($recordedAmount, 2, '.', ''),
                            'to' => number_format($receivedAmount, 2, '.', ''),
                        ]),
                    ]);
                } else {
                    $this->syncIncomeBalances($income);
                }
            }

            return $income;
        });
    }

    /**
     * Record a new installment payment on an Other Income record
     */
    public function recordPayment(OtherIncome $income, array $data, $receiptFile = null): OtherIncomePayment
    {
        return DB::transaction(function () use ($income, $data, $receiptFile) {
            $payment = new OtherIncomePayment();
            $payment->other_income_id = $income->id;
            $payment->payment_number  = $this->generatePaymentNumber();
            $payment->amount          = (float) $data['amount'];
            $payment->payment_date    = $data['payment_date'] ?? date('Y-m-d');
            $payment->payment_method  = $data['payment_method'] ?? 'Cash';
            $payment->reference_no    = $data['reference_no'] ?? null;
            $payment->note            = $data['note'] ?? null;
            $payment->created_by      = auth()->check() ? auth()->id() : 1;

            if ($receiptFile) {
                $destination = '/other_income/receipts';
                $uploadedReceipt = UploadFile::saveFile2($destination, $receiptFile);
                $payment->receipt_path = 'uploads' . $destination . '/' . $uploadedReceipt['unique_name'];
                $payment->receipt_name = $uploadedReceipt['original_name'];
            }

            $payment->save();

            $this->syncIncomeBalances($income);

            return $payment;
        });
    }

    /**
     * Delete an installment payment and recalculate parent balance
     */
    public function deletePayment(OtherIncomePayment $payment): bool
    {
        return DB::transaction(function () use ($payment) {
            $income = $payment->otherIncome;
            $deleted = $payment->delete();
            if ($income) {
                $this->syncIncomeBalances($income);
            }
            return (bool) $deleted;
        });
    }

    /**
     * Recalculate and synchronize parent received amount
     */
    public function syncIncomeBalances(OtherIncome $income): void
    {
        $totalPaid = (float) $income->payments()->sum('amount');
        $income->update([
            'received_amount' => $totalPaid,
            'payment_status'  => 'paid',
        ]);
    }

    /**
     * Generate sequential payment identifier
     */
    private function generatePaymentNumber(): string
    {
        $datePrefix = date('ym');
        $lastRecord = OtherIncomePayment::withTrashed()
            ->where('payment_number', 'like', "PAY-INC-{$datePrefix}-%")
            ->orderBy('id', 'desc')
            ->first();

        $sequence = 1;
        if ($lastRecord && preg_match('/PAY-INC-\d{4}-(\d+)/', $lastRecord->payment_number, $matches)) {
            $sequence = ((int) $matches[1]) + 1;
        }

        return sprintf('PAY-INC-%s-%04d', $datePrefix, $sequence);
    }
}
