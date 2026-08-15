@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => 'Remaining Amount Management'])
    <div class="content-wrapper" id="app" x-data="xRemainingAmount">
        <!-- Top Financial Metric Cards -->
        <div class="remaining-summary-ribbon">
            <div class="remaining-stat-card remaining-stat-card--total">
                <div class="stat-card__icon">
                    <i class='bx bx-receipt'></i>
                </div>
                <div class="stat-card__content">
                    <span class="stat-card__label">Total Amount</span>
                    <span class="stat-card__value">{{ number_format($financialTotal, 2) }}៛</span>
                </div>
            </div>
            <div class="remaining-stat-card remaining-stat-card--paid">
                <div class="stat-card__icon">
                    <i class='bx bx-check-shield'></i>
                </div>
                <div class="stat-card__content">
                    <span class="stat-card__label">Total Paid</span>
                    <span class="stat-card__value">{{ number_format($financialPaid, 2) }}៛</span>
                </div>
            </div>
            <div class="remaining-stat-card remaining-stat-card--remaining">
                <div class="stat-card__icon">
                    <i class='bx bx-wallet-alt'></i>
                </div>
                <div class="stat-card__content">
                    <span class="stat-card__label">Total Remaining</span>
                    <span class="stat-card__value">{{ number_format($financialRemaining, 2) }}៛</span>
                </div>
            </div>
        </div>

        @php
            $tabQuery = request()->except(['page', 'payment_status']);
            $tabUrl = function ($tabStatus) use ($tabQuery) {
                $url = route('admin-remaining-amount-list', $tabStatus);
                return empty($tabQuery) ? $url : $url . '?' . http_build_query($tabQuery);
            };
        @endphp

        @component('admin::components.listingData', [
            'routeName' => $routeName,
            'showCreate' => false,
            'createName' => '',
            'createPermission' => '',
            'filterStatus' => false,
            'filterData' => [
                'shop' => $shop,
                'barber' => $barber,
                'firstMonthDay' => $firstMonthDay,
                'lastMonthDay' => $lastMonthDay,
            ],
            'showSearch' => true,
            'showTabs' => true,
            'tabs' => [
                [
                    'label' => 'All Outstanding',
                    'icon' => 'bx bx-list-ul',
                    'url' => $tabUrl('all'),
                    'active' => $status === 'all',
                ],
                [
                    'label' => 'Partial Paid',
                    'icon' => 'bx bx-credit-card',
                    'url' => $tabUrl('partial'),
                    'active' => $status === 'partial',
                ],
                [
                    'label' => 'Pending Payment',
                    'icon' => 'bx bx-time-five',
                    'url' => $tabUrl('pending'),
                    'active' => $status === 'pending',
                ],
                [
                    'label' => 'Fully Paid',
                    'icon' => 'bx bx-check-circle',
                    'url' => $tabUrl('paid'),
                    'active' => $status === 'paid',
                ],
            ],
            'exportAction' => 'excel()',
            'exportLabel' => 'Excel Report',
            'exportClass' => 'btnExcel',
            'data' => $data,
            'status' => $status,
            'tbHeader' => [
                ['field' => 'index', 'title' => 'Nº', 'class' => '', 'colVal' => 4],
                ['field' => 'invoice_title', 'title' => 'Booking ID', 'class' => 'text left', 'colVal' => 10],
                ['field' => 'shop_title', 'title' => 'Shop', 'class' => 'text left', 'colVal' => 12],
                ['field' => 'customer_title', 'title' => 'Customer', 'class' => 'text left', 'colVal' => 14],
                ['field' => 'booking_items_title', 'title' => 'Services / Products', 'class' => 'text left', 'colVal' => 18],
                ['field' => 'payment_status_title', 'title' => 'Pay Status', 'class' => '', 'colVal' => 10],
                ['field' => 'total_price_title', 'title' => 'Total', 'class' => '', 'colVal' => 8],
                ['field' => 'paid_amount_title', 'title' => 'Paid', 'class' => '', 'colVal' => 8],
                ['field' => 'remaining_amount_title', 'title' => 'Remaining', 'class' => 'text-danger font-weight-bold', 'colVal' => 8],
                ['field' => 'booking_date_title', 'title' => 'Booking Date', 'class' => '', 'colVal' => 10],
                [
                    'field' => 'action',
                    'title' => 'Actions',
                    'class' => '',
                    'colVal' => 8,
                    'actions' => [
                        [
                            'key' => 'active',
                            'action' => [
                                [
                                    'url' => 'payment',
                                    'title' => 'Manage Payment',
                                    'icon' => 'payments',
                                    'type' => 'click',
                                    'handler' => 'openPaymentModal',
                                    'class' => 'text-primary',
                                    'visible' => ['payment_status' => 'Pending'],
                                ],
                                [
                                    'url' => 'payment',
                                    'title' => 'Manage Payment',
                                    'icon' => 'payments',
                                    'type' => 'click',
                                    'handler' => 'openPaymentModal',
                                    'class' => 'text-success',
                                    'visible' => ['payment_status' => 'Partial'],
                                ],
                                [
                                    'url' => 'payment',
                                    'title' => 'Payment History',
                                    'icon' => 'history',
                                    'type' => 'click',
                                    'handler' => 'openPaymentModal',
                                    'class' => 'text-info',
                                    'visible' => ['payment_status' => 'Paid'],
                                ],
                                [
                                    'url' => 'reminder',
                                    'title' => 'Send Reminder',
                                    'icon' => 'notifications_active',
                                    'type' => 'click',
                                    'handler' => 'quickSendReminder',
                                    'class' => 'text-warning',
                                    'visible' => ['payment_status' => 'Partial'],
                                ],
                                [
                                    'url' => 'reminder',
                                    'title' => 'Send Reminder',
                                    'icon' => 'notifications_active',
                                    'type' => 'click',
                                    'handler' => 'quickSendReminder',
                                    'class' => 'text-warning',
                                    'visible' => ['payment_status' => 'Pending'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ])
        @endcomponent

        <!-- Standalone Payment & Payment History Modal -->
        <template x-if="showPaymentModal">
            <div class="payment-modal-backdrop" @click.self="closePaymentModal()">
                <div class="payment-modal-card">
                    <!-- Modal Header -->
                    <div class="payment-modal-header">
                        <div class="header-left">
                            <div class="modal-badge-icon">
                                <i class='bx bx-wallet'></i>
                            </div>
                            <div>
                                <h3 class="modal-title" x-text="`Booking ${activeBooking?.invoice_number || ''}`"></h3>
                                <p class="modal-subtitle" x-text="`${activeBooking?.customer_name || 'Walk-in'} • ${activeBooking?.shop_name || 'Shop'}`"></p>
                            </div>
                        </div>
                        <button type="button" class="btn-close-modal" @click="closePaymentModal()">&times;</button>
                    </div>

                    <!-- Modal Body -->
                    <div class="payment-modal-body">
                        <!-- Financial Breakdown Card -->
                        <div class="modal-ledger-summary">
                            <div class="summary-col">
                                <span class="summary-label">Total Amount</span>
                                <strong class="summary-val" x-text="activeBooking?.total_price_formatted || '0.00៛'"></strong>
                            </div>
                            <div class="summary-col">
                                <span class="summary-label">Amount Paid</span>
                                <strong class="summary-val text-success" x-text="activeBooking?.paid_amount_formatted || '0.00៛'"></strong>
                            </div>
                            <div class="summary-col">
                                <span class="summary-label">Remaining Balance</span>
                                <strong class="summary-val text-danger" x-text="activeBooking?.remaining_amount_formatted || '0.00៛'"></strong>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="modal-progress-wrap">
                            <div class="progress-bar-rail">
                                <div class="progress-bar-fill" :style="`width: ${modalProgressPercent()}%`"
                                    :class="modalStatusClass()"></div>
                            </div>
                            <div class="progress-meta">
                                <span x-text="`Status: ${activeBooking?.payment_status || 'Pending'}`"
                                    :class="'badge-' + (activeBooking?.payment_status || 'pending').toLowerCase()"></span>
                                <span x-text="`${modalProgressPercent()}% Paid`"></span>
                            </div>
                        </div>

                        <!-- Modal Tabs -->
                        <div class="modal-nav-tabs">
                            <button type="button" class="modal-tab-btn" :class="{ 'is-active': activeModalTab === 'pay' }"
                                @click="activeModalTab = 'pay'" x-show="activeBooking?.remaining_amount > 0">
                                <i class='bx bx-plus-circle'></i> Make Payment
                            </button>
                            <button type="button" class="modal-tab-btn" :class="{ 'is-active': activeModalTab === 'history' }"
                                @click="activeModalTab = 'history'">
                                <i class='bx bx-history'></i> Payment History (<span x-text="activeBooking?.payments?.length || 0"></span>)
                            </button>
                        </div>

                        <!-- Tab 1: Make Payment Form -->
                        <div class="modal-tab-content" x-show="activeModalTab === 'pay' && activeBooking?.remaining_amount > 0">
                            <form @submit.prevent="submitModalPayment()">
                                <div class="modal-form-grid">
                                    <div class="modal-form-group">
                                        <label>Payment Amount (៛) <span class="text-danger">*</span></label>
                                        <div class="input-with-action">
                                            <input type="number" step="0.01" min="0.01" :max="activeBooking?.remaining_amount"
                                                x-model="paymentForm.amount" class="modal-input" placeholder="0.00" required>
                                            <button type="button" class="btn-quick-full"
                                                @click="paymentForm.amount = activeBooking?.remaining_amount">
                                                Full Balance
                                            </button>
                                        </div>
                                    </div>
                                    <div class="modal-form-group">
                                        <label>Payment Method</label>
                                        <select x-model="paymentForm.payment_method" class="modal-select">
                                            <option value="Cash">Cash</option>
                                            <option value="ABA">ABA</option>
                                            <option value="Card">Card</option>
                                            <option value="QR">QR</option>
                                        </select>
                                    </div>
                                    <div class="modal-form-group" style="grid-column: span 2;">
                                        <label>Payment Date & Time</label>
                                        <input type="datetime-local" x-model="paymentForm.payment_date" class="modal-input">
                                    </div>
                                    <div class="modal-form-group" style="grid-column: span 2;">
                                        <label>Note / Reference (Optional)</label>
                                        <input type="text" x-model="paymentForm.note" class="modal-input" placeholder="e.g. Transaction ID, receipt note..." maxlength="500">
                                    </div>
                                </div>
                                <div class="modal-form-actions">
                                    <button type="button" class="btn-secondary-action" :disabled="reminderLoading"
                                        @click="sendReminderNotification()" x-show="activeBooking?.remaining_amount > 0">
                                        <i class='bx bx-bell'></i>
                                        <span x-text="reminderLoading ? 'Sending...' : 'Send Reminder'"></span>
                                    </button>
                                    <button type="submit" class="btn-primary-action" :disabled="paymentSubmitting">
                                        <i class='bx bx-check-circle' x-show="!paymentSubmitting"></i>
                                        <span x-text="paymentSubmitting ? 'Processing Payment...' : 'Record Payment'"></span>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Tab 2: Payment History Table -->
                        <div class="modal-tab-content" x-show="activeModalTab === 'history' || activeBooking?.remaining_amount <= 0">
                            <div class="history-table-container">
                                <template x-if="!activeBooking?.payments || activeBooking?.payments?.length === 0">
                                    <div class="empty-history-state">
                                        <i class='bx bx-folder-open'></i>
                                        <p>No payment transactions recorded yet.</p>
                                    </div>
                                </template>
                                <template x-if="activeBooking?.payments && activeBooking?.payments?.length > 0">
                                    <table class="history-data-table">
                                        <thead>
                                            <tr>
                                                <th>Date & Time</th>
                                                <th>Method</th>
                                                <th>Amount</th>
                                                <th>Staff</th>
                                                <th>Note</th>
                                                <th style="text-align:right;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="pay in activeBooking.payments" :key="pay.id">
                                                <tr>
                                                    <!-- Normal Row View -->
                                                    <template x-if="editingPaymentId !== pay.id">
                                                        <td class="cell-date" x-text="pay.payment_date_formatted || '---'"></td>
                                                    </template>
                                                    <template x-if="editingPaymentId !== pay.id">
                                                        <td>
                                                            <span class="badge-method" :class="'badge-' + (pay.payment_method || 'cash').toLowerCase()"
                                                                x-text="pay.payment_method || 'Cash'"></span>
                                                        </td>
                                                    </template>
                                                    <template x-if="editingPaymentId !== pay.id">
                                                        <td class="cell-amount text-success" x-text="pay.amount_formatted || formatCurrency(pay.amount)"></td>
                                                    </template>
                                                    <template x-if="editingPaymentId !== pay.id">
                                                        <td class="cell-staff" x-text="pay.created_by || '---'"></td>
                                                    </template>
                                                    <template x-if="editingPaymentId !== pay.id">
                                                        <td class="cell-note" x-text="pay.note || '-'"></td>
                                                    </template>
                                                    <template x-if="editingPaymentId !== pay.id">
                                                        <td style="text-align:right;">
                                                            <button type="button" class="btn-table-icon" @click="startEditPayment(pay)" title="Edit">
                                                                <i class='bx bx-edit'></i>
                                                            </button>
                                                            <button type="button" class="btn-table-icon text-danger" @click="deletePayment(pay)" title="Delete">
                                                                <i class='bx bx-trash'></i>
                                                            </button>
                                                        </td>
                                                    </template>

                                                    <!-- Inline Edit Mode -->
                                                    <template x-if="editingPaymentId === pay.id">
                                                        <td colspan="6" class="cell-edit-container">
                                                            <div class="inline-edit-grid">
                                                                <div class="edit-field">
                                                                    <label>Amount</label>
                                                                    <input type="number" step="0.01" min="0.01" x-model="editPaymentForm.amount" class="modal-input-sm">
                                                                </div>
                                                                <div class="edit-field">
                                                                    <label>Method</label>
                                                                    <select x-model="editPaymentForm.payment_method" class="modal-select-sm">
                                                                        <option value="Cash">Cash</option>
                                                                        <option value="ABA">ABA</option>
                                                                        <option value="Card">Card</option>
                                                                        <option value="QR">QR</option>
                                                                    </select>
                                                                </div>
                                                                <div class="edit-field">
                                                                    <label>Date</label>
                                                                    <input type="datetime-local" x-model="editPaymentForm.payment_date" class="modal-input-sm">
                                                                </div>
                                                                <div class="edit-field" style="grid-column: span 3;">
                                                                    <label>Note</label>
                                                                    <input type="text" x-model="editPaymentForm.note" class="modal-input-sm" placeholder="Note...">
                                                                </div>
                                                                <div class="edit-actions" style="grid-column: span 3; justify-content: flex-end; display: flex; gap: 8px;">
                                                                    <button type="button" class="btn-cancel-sm" @click="cancelEditPayment()">Cancel</button>
                                                                    <button type="button" class="btn-save-sm" :disabled="paymentSubmitting" @click="saveEditPayment(pay)">Save</button>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </template>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <template x-if="exportLoading">
            <div class="loadingFullSizeLayout">
                <div class="loading loadingSubmit">
                    <span id="spinner"></span>
                    <label>Exporting Remaining Amount Report ...</label>
                </div>
            </div>
        </template>
    </div>

    <style>
        /* Top Financial Summary Ribbon */
        .remaining-summary-ribbon {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }
        .remaining-stat-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
            border: 1px solid #e2e8f0;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .remaining-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.08);
        }
        .remaining-stat-card .stat-card__icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        .remaining-stat-card--total .stat-card__icon {
            background: #eff6ff;
            color: #3b82f6;
        }
        .remaining-stat-card--paid .stat-card__icon {
            background: #ecfdf5;
            color: #10b981;
        }
        .remaining-stat-card--remaining .stat-card__icon {
            background: #fff1f2;
            color: #f43f5e;
        }
        .stat-card__content {
            display: flex;
            flex-direction: column;
        }
        .stat-card__label {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .stat-card__value {
            font-size: 20px;
            font-weight: 700;
            color: #1e293b;
            margin-top: 2px;
        }

        /* Payment Modal Styling */
        .payment-modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            padding: 20px;
        }
        .payment-modal-card {
            background: #ffffff;
            width: 100%;
            max-width: 680px;
            max-height: 90vh;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            animation: modalFadeIn 0.25s ease-out;
        }
        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.96) translateY(10px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
        .payment-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 24px;
            border-bottom: 1px solid #f1f5f9;
            background: #f8fafc;
        }
        .payment-modal-header .header-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .modal-badge-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: #e0e7ff;
            color: #4f46e5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }
        .modal-title {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
        }
        .modal-subtitle {
            margin: 2px 0 0;
            font-size: 13px;
            color: #64748b;
        }
        .btn-close-modal {
            background: none;
            border: none;
            font-size: 26px;
            line-height: 1;
            color: #94a3b8;
            cursor: pointer;
            padding: 4px;
            border-radius: 6px;
        }
        .btn-close-modal:hover {
            color: #1e293b;
            background: #e2e8f0;
        }
        .payment-modal-body {
            padding: 24px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* Modal Financial Breakdown Cards */
        .modal-ledger-summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 18px;
        }
        .summary-col {
            display: flex;
            flex-direction: column;
        }
        .summary-label {
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
        }
        .summary-val {
            font-size: 17px;
            font-weight: 700;
            color: #1e293b;
            margin-top: 2px;
        }

        /* Progress Bar */
        .modal-progress-wrap {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .progress-bar-rail {
            height: 8px;
            background: #e2e8f0;
            border-radius: 999px;
            overflow: hidden;
        }
        .progress-bar-fill {
            height: 100%;
            transition: width 0.3s ease;
        }
        .progress-bar-fill.fill-pending { background: #f59e0b; }
        .progress-bar-fill.fill-partial { background: #3b82f6; }
        .progress-bar-fill.fill-paid { background: #10b981; }
        .progress-meta {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
        }

        /* Modal Tabs */
        .modal-nav-tabs {
            display: flex;
            gap: 8px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 8px;
        }
        .modal-tab-btn {
            background: none;
            border: none;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }
        .modal-tab-btn:hover {
            background: #f1f5f9;
            color: #1e293b;
        }
        .modal-tab-btn.is-active {
            background: #e0e7ff;
            color: #4338ca;
        }

        /* Form Grid */
        .modal-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }
        .modal-form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .modal-form-group label {
            font-size: 12px;
            font-weight: 600;
            color: #475569;
        }
        .modal-input, .modal-select {
            width: 100%;
            height: 40px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 0 12px;
            font-size: 14px;
            color: #1e293b;
            background: #ffffff;
            outline: none;
            transition: border-color 0.2s;
        }
        .modal-input:focus, .modal-select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }
        .input-with-action {
            display: flex;
            gap: 8px;
        }
        .btn-quick-full {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 0 12px;
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            cursor: pointer;
            white-space: nowrap;
        }
        .btn-quick-full:hover {
            background: #e2e8f0;
            color: #1e293b;
        }
        .modal-form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 18px;
        }
        .btn-primary-action {
            background: #4f46e5;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.2s;
        }
        .btn-primary-action:hover {
            background: #4338ca;
        }
        .btn-secondary-action {
            background: #fefce8;
            color: #854d0e;
            border: 1px solid #fef08a;
            border-radius: 8px;
            padding: 10px 16px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .btn-secondary-action:hover {
            background: #fef9c3;
        }

        /* History Table */
        .history-table-container {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
        }
        .history-data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .history-data-table th {
            background: #f8fafc;
            padding: 10px 14px;
            font-weight: 600;
            color: #64748b;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
        }
        .history-data-table td {
            padding: 10px 14px;
            border-bottom: 1px solid #f1f5f9;
            color: #1e293b;
        }
        .badge-method {
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-cash { background: #dcfce7; color: #15803d; }
        .badge-aba { background: #e0f2fe; color: #0369a1; }
        .badge-card { background: #f3e8ff; color: #7e22ce; }
        .badge-qr { background: #ffedd5; color: #c2410c; }
        .btn-table-icon {
            background: none;
            border: none;
            font-size: 16px;
            color: #64748b;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
        }
        .btn-table-icon:hover {
            background: #f1f5f9;
            color: #1e293b;
        }
        .empty-history-state {
            padding: 40px 20px;
            text-align: center;
            color: #94a3b8;
        }
        .empty-history-state i {
            font-size: 36px;
            margin-bottom: 8px;
        }

        /* Inline Edit */
        .cell-edit-container {
            background: #f8fbff;
            padding: 14px !important;
        }
        .inline-edit-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
        }
        .modal-input-sm, .modal-select-sm {
            width: 100%;
            height: 32px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 0 8px;
            font-size: 12px;
        }
        .btn-save-sm {
            background: #4f46e5;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 4px 12px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-cancel-sm {
            background: #e2e8f0;
            color: #475569;
            border: none;
            border-radius: 6px;
            padding: 4px 10px;
            font-size: 12px;
            cursor: pointer;
        }
    </style>
@stop

@section('script')
    <script src="{{ asset('admin-public/js/exceljs.min.js') }}"></script>
    <script src="{{ asset('admin-public/js/FileSaver.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $("#from_date").datepicker({
                dateFormat: 'yy-mm-dd',
                changeYear: true,
                changeMonth: true,
                gotoCurrent: true,
                yearRange: "-50:+0",
                onSelect: function(selected) {
                    $("#to_date").datepicker("option", "minDate", selected);
                }
            });
            $("#to_date").datepicker({
                minDate: `{{ $firstMonthDay }}`,
                dateFormat: 'yy-mm-dd',
                changeYear: true,
                changeMonth: true,
                gotoCurrent: true,
                yearRange: "-50:+0",
                onSelect: function(selected) {
                    $("#from_date").datepicker("option", "maxDate", selected);
                }
            });
        });
    </script>
    <script lang="ts">
        document.addEventListener('alpine:init', () => {
            Alpine.data('xRemainingAmount', () => ({
                exportLoading: false,
                showPaymentModal: false,
                activeModalTab: 'pay',
                activeBooking: null,
                paymentSubmitting: false,
                reminderLoading: false,
                editingPaymentId: null,
                paymentForm: {
                    amount: null,
                    payment_method: 'Cash',
                    payment_date: moment().format('YYYY-MM-DDTHH:mm'),
                    note: '',
                },
                editPaymentForm: {
                    amount: null,
                    payment_method: 'Cash',
                    payment_date: '',
                    note: '',
                },
                formData: {
                    status: @json($status),
                    shop_id: @json(request('shop_id')),
                    barber_id: @json(request('barber_id')),
                    from_date: @json($firstMonthDay),
                    to_date: @json($lastMonthDay),
                    search: @json(request('search')),
                },
                init() {
                    const shop = @json($shop);
                    const barber = @json($barber);

                    if (shop?.id) {
                        $select2Data('#shop_id', shop.id, shop.name || shop.phone);
                    }
                    if (barber?.id) {
                        $select2Data('#barber_id', barber.id, barber.name || barber.phone);
                    }
                },
                fetchSelectShop() {
                    $('#shop_id').select2({
                        placeholder: 'Select Shop',
                        ajax: {
                            url: '{{ route('admin-select-shop') }}',
                            dataType: 'json',
                            type: 'GET',
                            quietMillis: 50,
                            data: (param) => ({ search: param.term }),
                            processResults: (data) => ({
                                results: $.map(data.data, (item) => ({
                                    text: item?.name ? item.name : '',
                                    id: item.id
                                }))
                            })
                        }
                    }).on('select2:open', () => {
                        document.querySelector('.select2-search__field').focus();
                    });
                },
                fetchSelectBarber() {
                    $('#barber_id').select2({
                        placeholder: 'Select Barber',
                        ajax: {
                            url: '{{ route('admin-select-barber') }}',
                            dataType: 'json',
                            type: 'GET',
                            quietMillis: 50,
                            data: (param) => ({ search: param.term }),
                            processResults: (data) => ({
                                results: $.map(data.data, (item) => ({
                                    text: item?.name ? item.name : '',
                                    id: item.id
                                }))
                            })
                        }
                    }).on('select2:open', () => {
                        document.querySelector('.select2-search__field').focus();
                    });
                },
                openPaymentModal(item) {
                    this.activeBooking = item;
                    this.showPaymentModal = true;
                    this.activeModalTab = (item?.remaining_amount > 0) ? 'pay' : 'history';
                    this.editingPaymentId = null;
                    this.paymentForm = {
                        amount: item?.remaining_amount || null,
                        payment_method: 'Cash',
                        payment_date: moment().format('YYYY-MM-DDTHH:mm'),
                        note: '',
                    };

                    Axios.get(`{{ url('admin/remaining-amount/payment-details') }}/${item.id}`)
                        .then((res) => {
                            this.activeBooking = res.data;
                            if (this.activeBooking.remaining_amount <= 0) {
                                this.activeModalTab = 'history';
                            }
                        })
                        .catch((err) => {
                            console.error(err);
                        });
                },
                closePaymentModal() {
                    this.showPaymentModal = false;
                    this.activeBooking = null;
                    this.editingPaymentId = null;
                },
                modalProgressPercent() {
                    if (!this.activeBooking) return 0;
                    const total = Number(this.activeBooking.total_price || 0);
                    const paid = Number(this.activeBooking.paid_amount || 0);
                    if (total <= 0) return 0;
                    return Math.min(100, Math.max(0, Math.round((paid / total) * 100)));
                },
                modalStatusClass() {
                    const status = this.activeBooking?.payment_status;
                    if (status === 'Paid') return 'fill-paid';
                    if (status === 'Partial') return 'fill-partial';
                    return 'fill-pending';
                },
                submitModalPayment() {
                    if (!this.activeBooking?.id) return;
                    this.paymentSubmitting = true;

                    Axios.post(`{{ url('admin/remaining-amount/add-payment') }}/${this.activeBooking.id}`, {
                        amount: this.paymentForm.amount,
                        payment_method: this.paymentForm.payment_method,
                        payment_date: this.paymentForm.payment_date,
                        note: this.paymentForm.note,
                    }).then((res) => {
                        this.activeBooking = res.data;
                        this.paymentForm.amount = this.activeBooking.remaining_amount || null;
                        this.paymentForm.note = '';
                        this.paymentForm.payment_date = moment().format('YYYY-MM-DDTHH:mm');
                        if (this.activeBooking.remaining_amount <= 0) {
                            this.activeModalTab = 'history';
                        }
                        reloadData(`{{ url()->full() }}`);
                    }).catch((err) => {
                        const message = err.response?.data?.error ||
                            Object.values(err.response?.data?.errors || {})?.[0]?.[0] ||
                            'Unable to record payment.';
                        alert(message);
                    }).finally(() => {
                        this.paymentSubmitting = false;
                    });
                },
                startEditPayment(pay) {
                    this.editingPaymentId = pay.id;
                    this.editPaymentForm = {
                        amount: pay.amount,
                        payment_method: pay.payment_method || 'Cash',
                        payment_date: pay.payment_date ? moment(pay.payment_date).format('YYYY-MM-DDTHH:mm') : moment().format('YYYY-MM-DDTHH:mm'),
                        note: pay.note || '',
                    };
                },
                cancelEditPayment() {
                    this.editingPaymentId = null;
                },
                saveEditPayment(pay) {
                    this.paymentSubmitting = true;
                    Axios.put(`{{ url('admin/remaining-amount/update-payment') }}/${pay.id}`, {
                        amount: this.editPaymentForm.amount,
                        payment_method: this.editPaymentForm.payment_method,
                        payment_date: this.editPaymentForm.payment_date,
                        note: this.editPaymentForm.note,
                    }).then((res) => {
                        this.activeBooking = res.data;
                        this.cancelEditPayment();
                        reloadData(`{{ url()->full() }}`);
                    }).catch((err) => {
                        const message = err.response?.data?.error ||
                            Object.values(err.response?.data?.errors || {})?.[0]?.[0] ||
                            'Unable to update payment.';
                        alert(message);
                    }).finally(() => {
                        this.paymentSubmitting = false;
                    });
                },
                deletePayment(pay) {
                    if (!confirm('Are you sure you want to delete this payment record?')) {
                        return;
                    }
                    this.paymentSubmitting = true;
                    Axios.delete(`{{ url('admin/remaining-amount/delete-payment') }}/${pay.id}`, {
                        data: { _token: '{{ csrf_token() }}' }
                    }).then((res) => {
                        this.activeBooking = res.data;
                        reloadData(`{{ url()->full() }}`);
                    }).catch((err) => {
                        alert(err.response?.data?.error || 'Unable to delete payment.');
                    }).finally(() => {
                        this.paymentSubmitting = false;
                    });
                },
                sendReminderNotification() {
                    if (!this.activeBooking?.id) return;
                    this.reminderLoading = true;
                    Axios.post(`{{ url('admin/remaining-amount/send-reminder') }}/${this.activeBooking.id}`, {
                        _token: '{{ csrf_token() }}'
                    }).then((res) => {
                        alert(res.data.success_message || 'Payment reminder sent successfully.');
                    }).catch((err) => {
                        alert(err.response?.data?.error || 'Unable to send reminder.');
                    }).finally(() => {
                        this.reminderLoading = false;
                    });
                },
                quickSendReminder(item) {
                    if (!item?.id) return;
                    Axios.post(`{{ url('admin/remaining-amount/send-reminder') }}/${item.id}`, {
                        _token: '{{ csrf_token() }}'
                    }).then((res) => {
                        alert(res.data.success_message || 'Payment reminder sent successfully.');
                    }).catch((err) => {
                        alert(err.response?.data?.error || 'Unable to send reminder.');
                    });
                },
                formatCurrency(val) {
                    return (Number(val || 0)).toFixed(2) + '៛';
                },
                async excel() {
                    this.exportLoading = true;
                    await Axios.get(`{{ route('admin-remaining-amount-report') }}`, {
                        params: this.formData,
                    }).then((response) => {
                        this.runExcelExport(response.data);
                    }).finally(() => {
                        this.exportLoading = false;
                    });
                },
                runExcelExport(rows) {
                    const workbook = new ExcelJS.Workbook();
                    const worksheet = workbook.addWorksheet('Remaining Amount Report');
                    worksheet.columns = [
                        { header: 'Booking ID', key: 'booking_id', width: 16 },
                        { header: 'Booking Date', key: 'booking_date', width: 18 },
                        { header: 'Shop', key: 'shop', width: 22 },
                        { header: 'Barber', key: 'barber', width: 22 },
                        { header: 'Customer Phone', key: 'customer_phone', width: 18 },
                        { header: 'Pay Status', key: 'payment_status', width: 14 },
                        { header: 'Total Price', key: 'total_price', width: 14 },
                        { header: 'Paid Amount', key: 'paid_amount', width: 14 },
                        { header: 'Remaining Balance', key: 'remaining_amount', width: 18 },
                        { header: 'Last Pay Date', key: 'payment_date', width: 18 },
                    ];

                    rows.forEach((item) => {
                        worksheet.addRow({
                            booking_id: item?.invoice_number || '',
                            booking_date: item?.booking_date ? moment(item.booking_date).format('YYYY-MM-DD HH:mm') : '',
                            shop: item?.shop?.name || '',
                            barber: item?.barber?.name || '',
                            customer_phone: item?.customer?.phone || '',
                            payment_status: item?.payment_status || '',
                            total_price: Number(item?.total_price || 0),
                            paid_amount: Number(item?.paid_amount || 0),
                            remaining_amount: Number(item?.remaining_amount || 0),
                            payment_date: item?.payment_date ? moment(item.payment_date).format('YYYY-MM-DD HH:mm') : '',
                        });
                    });

                    worksheet.getRow(1).font = { bold: true };
                    workbook.xlsx.writeBuffer().then((data) => {
                        const blob = new Blob([data], {
                            type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        });
                        saveAs(blob, 'Remaining_Amount_Report_' + moment().format('YYYY_MM_DD_HHmmss'));
                    });
                },
            }));
        });
    </script>
@stop
