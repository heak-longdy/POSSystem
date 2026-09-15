@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => __('remaining_amount.title')])
    <div class="content-wrapper booking-listing-wrapper" id="app" x-data="xRemainingAmount">
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
                    'label' => __('remaining_amount.tab.all_outstanding'),
                    'icon' => 'bx bx-list-ul',
                    'url' => $tabUrl('all'),
                    'active' => $status === 'all',
                ],
                [
                    'label' => __('remaining_amount.tab.partial_paid'),
                    'icon' => 'bx bx-credit-card',
                    'url' => $tabUrl('partial'),
                    'active' => $status === 'partial',
                ],
                [
                    'label' => __('remaining_amount.tab.pending_payment'),
                    'icon' => 'bx bx-time-five',
                    'url' => $tabUrl('pending'),
                    'active' => $status === 'pending',
                ],
                [
                    'label' => __('remaining_amount.tab.fully_paid'),
                    'icon' => 'bx bx-check-circle',
                    'url' => $tabUrl('paid'),
                    'active' => $status === 'paid',
                ],
            ],
            'exportAction' => 'excel()',
            'exportLabel' => __('remaining_amount.excel_report'),
            'exportClass' => 'btnExcel',
            'data' => $data,
            'status' => $status,
            'tbHeader' => [
                ['field' => 'index', 'title' => __('booking.table.no'), 'class' => '', 'colVal' => 4],
                ['field' => 'invoice_title', 'title' => __('booking.table.booking_id'), 'class' => 'text left', 'colVal' => 10],
                ['field' => 'shop_title', 'title' => __('booking.table.shop'), 'class' => 'text left', 'colVal' => 12],
                ['field' => 'customer_title', 'title' => __('booking.table.customer'), 'class' => 'text left', 'colVal' => 14],
                ['field' => 'booking_items_title', 'title' => __('booking.table.services_products'), 'class' => 'text left', 'colVal' => 18],
                ['field' => 'payment_status_title', 'title' => __('booking.table.pay_status'), 'class' => '', 'colVal' => 10],
                ['field' => 'total_price_title', 'title' => __('booking.table.total'), 'class' => '', 'colVal' => 8],
                ['field' => 'paid_amount_title', 'title' => __('booking.table.paid'), 'class' => '', 'colVal' => 8],
                ['field' => 'remaining_amount_title', 'title' => __('booking.table.remaining'), 'class' => 'text-danger font-weight-bold', 'colVal' => 8],
                ['field' => 'booking_date_title', 'title' => __('booking.table.booking_date'), 'class' => '', 'colVal' => 10],
                [
                    'field' => 'action',
                    'title' => __('global.table.action'),
                    'class' => '',
                    'colVal' => 8,
                    'actions' => [
                        [
                            'key' => 'active',
                            'action' => [
                                [
                                    'url' => 'detail',
                                    'title' => __('remaining_amount.action.view_detail'),
                                    'icon' => 'visibility',
                                    'type' => 'link',
                                ],
                                [
                                    'url' => 'add-payment',
                                    'title' => __('remaining_amount.action.add_payment'),
                                    'icon' => 'add_circle',
                                    'type' => 'click',
                                    'handler' => 'openAddPaymentModal',
                                    'class' => 'text-success font-weight-bold',
                                    'visible' => ['can_add_payment' => true],
                                ],
                                [
                                    'url' => 'payment',
                                    'title' => __('remaining_amount.action.manage_payment'),
                                    'icon' => 'payments',
                                    'type' => 'click',
                                    'handler' => 'openPaymentModal',
                                    'class' => 'text-primary',
                                    'visible' => ['payment_status' => 'Pending'],
                                ],
                                [
                                    'url' => 'payment',
                                    'title' => __('remaining_amount.action.manage_payment'),
                                    'icon' => 'payments',
                                    'type' => 'click',
                                    'handler' => 'openPaymentModal',
                                    'class' => 'text-success',
                                    'visible' => ['payment_status' => 'Partial'],
                                ],
                                [
                                    'url' => 'payment',
                                    'title' => __('remaining_amount.action.payment_history'),
                                    'icon' => 'history',
                                    'type' => 'click',
                                    'handler' => 'openPaymentModal',
                                    'class' => 'text-info',
                                    'visible' => ['payment_status' => 'Paid'],
                                ],
                                [
                                    'url' => 'reminder',
                                    'title' => __('remaining_amount.action.send_reminder'),
                                    'icon' => 'notifications_active',
                                    'type' => 'click',
                                    'handler' => 'quickSendReminder',
                                    'class' => 'text-warning',
                                    'visible' => ['payment_status' => 'Partial'],
                                ],
                                [
                                    'url' => 'reminder',
                                    'title' => __('remaining_amount.action.send_reminder'),
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

        <!-- Dedicated Add Payment Modal (Similar to Booking Detail) -->
        <div class="modal-backdrop-custom" x-show="showAddPaymentModal" x-transition.opacity style="display: none;">
            <div class="modal-dialog-custom" @click.away="if (!addPaymentSubmitting) closeAddPaymentModal()">
                <div class="modal-header-custom">
                    <div class="modal-title-wrap">
                        <i data-feather="plus-circle" class="text-success"></i>
                        <div>
                            <h3>{{ __('booking.detail.add_payment') }}</h3>
                            <div class="modal-subtitle-text"
                                x-text="`${addPaymentBooking?.invoice_number || addPaymentBooking?.invoice_title || ''} • ${addPaymentBooking?.customer_name || (addPaymentBooking?.customer ? (addPaymentBooking.customer.name || addPaymentBooking.customer.phone) : '') || @json(__('booking.walk_in_customer'))}`">
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-close-modal" :disabled="addPaymentSubmitting" @click="closeAddPaymentModal()">
                        &times;
                    </button>
                </div>

                <form @submit.prevent="submitAddPayment()">
                    <div class="modal-body-custom">
                        <div class="remaining-info-box">
                            <span class="info-label">{{ __('remaining_amount.ledger.remaining_balance') }}</span>
                            <strong class="info-val text-danger" x-text="formatCurrency(addPaymentBooking?.remaining_amount)"></strong>
                        </div>

                        <div class="form-group-modal">
                            <label>{{ __('booking.amount') }} ($) <span class="text-danger">*</span></label>
                            <div class="input-action-wrap">
                                <input type="number" step="0.01" min="0.01" :max="addPaymentBooking?.remaining_amount"
                                    class="modal-input" x-model="addPaymentForm.amount" required placeholder="0.00">
                                <button type="button" class="btn-fill-max" @click="addPaymentForm.amount = Number(addPaymentBooking?.remaining_amount || 0)">
                                    {{ __('booking.button.full_balance') }}
                                </button>
                            </div>
                        </div>

                        <div class="form-group-modal">
                            <label>{{ __('booking.detail.method') }}</label>
                            <div class="payment-method-selector">
                                <label class="method-option" :class="addPaymentForm.payment_method === 'Cash' ? 'is-active' : ''">
                                    <input type="radio" value="Cash" x-model="addPaymentForm.payment_method">
                                    <i data-feather="dollar-sign"></i>
                                    <span>{{ __('booking.payment.cash') }}</span>
                                </label>
                                <label class="method-option" :class="addPaymentForm.payment_method === 'ABA' ? 'is-active' : ''">
                                    <input type="radio" value="ABA" x-model="addPaymentForm.payment_method">
                                    <i data-feather="credit-card"></i>
                                    <span>{{ __('booking.payment.aba') }}</span>
                                </label>
                                <label class="method-option" :class="addPaymentForm.payment_method === 'Card' ? 'is-active' : ''">
                                    <input type="radio" value="Card" x-model="addPaymentForm.payment_method">
                                    <i data-feather="server"></i>
                                    <span>{{ __('booking.payment.card') }}</span>
                                </label>
                                <label class="method-option" :class="addPaymentForm.payment_method === 'QR' ? 'is-active' : ''">
                                    <input type="radio" value="QR" x-model="addPaymentForm.payment_method">
                                    <i data-feather="maximize"></i>
                                    <span>{{ __('booking.payment.qr') }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group-modal">
                            <label>{{ __('booking.table.pay_date') }}</label>
                            <input type="datetime-local" class="modal-input" x-model="addPaymentForm.payment_date">
                        </div>

                        <div class="form-group-modal">
                            <label>{{ __('booking.note') }}</label>
                            <textarea class="modal-textarea" rows="3" x-model="addPaymentForm.note"
                                placeholder="{{ __('booking.placeholder.note') }}"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer-custom">
                        <button type="button" class="btn btn-system btn-system-outline btn-system-neutral" :disabled="addPaymentSubmitting" @click="closeAddPaymentModal()">
                            <span>{{ __('booking.button.close') }}</span>
                        </button>
                        <button type="submit" class="btn btn-create bg-success btn-system btn-system-success" :disabled="addPaymentSubmitting">
                            <span x-show="!addPaymentSubmitting">{{ __('booking.detail.add_payment') }}</span>
                            <span x-show="addPaymentSubmitting" style="display: none;">{{ __('booking.button.processing_payment') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

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
                                <h3 class="modal-title" x-text="`{{ __('booking.table.booking_id') }}: ${activeBooking?.invoice_number || ''}`"></h3>
                                <p class="modal-subtitle" x-text="`${activeBooking?.customer_name || @json(__('booking.walk_in_customer'))} • ${activeBooking?.shop_name || @json(__('booking.table.shop'))}`"></p>
                            </div>
                        </div>
                        <button type="button" class="btn-close-modal" @click="closePaymentModal()">&times;</button>
                    </div>

                    <!-- Modal Body -->
                    <div class="payment-modal-body">
                        <!-- Financial Breakdown Card -->
                        <div class="modal-ledger-summary">
                            <div class="summary-col">
                                <span class="summary-label">{{ __('remaining_amount.ledger.total_amount') }}</span>
                                <strong class="summary-val" x-text="activeBooking?.total_price_formatted || '$0.00'"></strong>
                            </div>
                            <div class="summary-col">
                                <span class="summary-label">{{ __('remaining_amount.ledger.amount_paid') }}</span>
                                <strong class="summary-val text-success" x-text="activeBooking?.paid_amount_formatted || '$0.00'"></strong>
                            </div>
                            <div class="summary-col">
                                <span class="summary-label">{{ __('remaining_amount.ledger.remaining_balance') }}</span>
                                <strong class="summary-val text-danger" x-text="activeBooking?.remaining_amount_formatted || '$0.00'"></strong>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="modal-progress-wrap">
                            <div class="progress-bar-rail">
                                <div class="progress-bar-fill" :style="`width: ${modalProgressPercent()}%`"
                                    :class="modalStatusClass()"></div>
                            </div>
                            <div class="progress-meta">
                                <span x-text="`{{ __('booking.status_label') }}: ${paymentStatusLabel(activeBooking?.payment_status)}`"
                                    :class="'badge-' + (activeBooking?.payment_status || 'pending').toLowerCase()"></span>
                                <span x-text="`${modalProgressPercent()}% {{ __('booking.paid') }}`"></span>
                            </div>
                        </div>

                        <!-- Modal Tabs -->
                        <div class="modal-nav-tabs">
                            <button type="button" class="modal-tab-btn" :class="{ 'is-active': activeModalTab === 'pay' }"
                                @click="activeModalTab = 'pay'" x-show="activeBooking?.remaining_amount > 0">
                                <i class='bx bx-plus-circle'></i> {{ __('remaining_amount.modal.make_payment') }}
                            </button>
                            <button type="button" class="modal-tab-btn" :class="{ 'is-active': activeModalTab === 'history' }"
                                @click="activeModalTab = 'history'">
                                <i class='bx bx-history'></i> {{ __('remaining_amount.modal.payment_history') }} (<span x-text="activeBooking?.payments?.length || 0"></span>)
                            </button>
                        </div>

                        <!-- Tab 1: Make Payment Form -->
                        <div class="modal-tab-content" x-show="activeModalTab === 'pay' && activeBooking?.remaining_amount > 0">
                            <form @submit.prevent="submitModalPayment()">
                                <div class="modal-form-grid">
                                    <div class="modal-form-group">
                                        <label>{{ __('remaining_amount.form.payment_amount') }} <span class="text-danger">*</span></label>
                                        <div class="input-with-action">
                                            <input type="number" step="0.01" min="0.01" :max="activeBooking?.remaining_amount"
                                                x-model="paymentForm.amount" class="modal-input" placeholder="0.00" required>
                                            <button type="button" class="btn-quick-full"
                                                @click="paymentForm.amount = activeBooking?.remaining_amount">
                                                {{ __('remaining_amount.form.full_balance') }}
                                            </button>
                                        </div>
                                    </div>
                                    <div class="modal-form-group" style="grid-column: span 2;">
                                        <label>{{ __('remaining_amount.form.payment_method') }}</label>
                                        <div class="payment-method-selector">
                                            <label class="method-option" :class="paymentForm.payment_method === 'Cash' ? 'is-active' : ''">
                                                <input type="radio" value="Cash" x-model="paymentForm.payment_method">
                                                <i data-feather="dollar-sign"></i>
                                                <span>{{ __('booking.payment.cash') }}</span>
                                            </label>
                                            <label class="method-option" :class="paymentForm.payment_method === 'ABA' ? 'is-active' : ''">
                                                <input type="radio" value="ABA" x-model="paymentForm.payment_method">
                                                <i data-feather="credit-card"></i>
                                                <span>{{ __('booking.payment.aba') }}</span>
                                            </label>
                                            <label class="method-option" :class="paymentForm.payment_method === 'Card' ? 'is-active' : ''">
                                                <input type="radio" value="Card" x-model="paymentForm.payment_method">
                                                <i data-feather="server"></i>
                                                <span>{{ __('booking.payment.card') }}</span>
                                            </label>
                                            <label class="method-option" :class="paymentForm.payment_method === 'QR' ? 'is-active' : ''">
                                                <input type="radio" value="QR" x-model="paymentForm.payment_method">
                                                <i data-feather="maximize"></i>
                                                <span>{{ __('booking.payment.qr') }}</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="modal-form-group" style="grid-column: span 2;">
                                        <label>{{ __('remaining_amount.form.payment_date_time') }}</label>
                                        <input type="datetime-local" x-model="paymentForm.payment_date" class="modal-input">
                                    </div>
                                    <div class="modal-form-group" style="grid-column: span 2;">
                                        <label>{{ __('remaining_amount.form.note_reference') }}</label>
                                        <input type="text" x-model="paymentForm.note" class="modal-input" placeholder="{{ __('remaining_amount.placeholder.note') }}" maxlength="500">
                                    </div>
                                </div>
                                <div class="modal-form-actions">
                                    <button type="button" class="btn-secondary-action" :disabled="reminderLoading"
                                        @click="sendReminderNotification()" x-show="activeBooking?.remaining_amount > 0">
                                        <i class='bx bx-bell'></i>
                                        <span x-show="!reminderLoading">{{ __('remaining_amount.action.send_reminder') }}</span>
                                        <span x-show="reminderLoading" style="display: none;">{{ __('remaining_amount.button.sending') }}</span>
                                    </button>
                                    <button type="submit" class="btn-primary-action" :disabled="paymentSubmitting">
                                        <i class='bx bx-check-circle' x-show="!paymentSubmitting"></i>
                                        <span x-show="!paymentSubmitting">{{ __('booking.record_payment') }}</span>
                                        <span x-show="paymentSubmitting" style="display: none;">{{ __('remaining_amount.button.processing_payment') }}</span>
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
                                        <p>{{ __('remaining_amount.history.empty') }}</p>
                                    </div>
                                </template>
                                <template x-if="activeBooking?.payments && activeBooking?.payments?.length > 0">
                                    <table class="history-data-table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('remaining_amount.history.date_time') }}</th>
                                                <th>{{ __('remaining_amount.history.method') }}</th>
                                                <th>{{ __('remaining_amount.history.amount') }}</th>
                                                <th>{{ __('remaining_amount.history.staff') }}</th>
                                                <th>{{ __('remaining_amount.history.note') }}</th>
                                                <th style="text-align:right;">{{ __('remaining_amount.history.actions') }}</th>
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
                                                                x-text="paymentMethodLabel(pay.payment_method)"></span>
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
                                                            <button type="button" class="btn-table-icon" @click="startEditPayment(pay)" title="{{ __('booking.action.edit') }}">
                                                                <i class='bx bx-edit'></i>
                                                            </button>
                                                            <button type="button" class="btn-table-icon text-danger" @click="deletePayment(pay)" title="{{ __('booking.action.delete') }}">
                                                                <i class='bx bx-trash'></i>
                                                            </button>
                                                        </td>
                                                    </template>

                                                    <!-- Inline Edit Mode -->
                                                    <template x-if="editingPaymentId === pay.id">
                                                        <td colspan="6" class="cell-edit-container">
                                                            <div class="inline-edit-grid">
                                                                <div class="edit-field">
                                                                    <label>{{ __('remaining_amount.history.amount') }}</label>
                                                                    <input type="number" step="0.01" min="0.01" x-model="editPaymentForm.amount" class="modal-input-sm">
                                                                </div>
                                                                <div class="edit-field">
                                                                    <label>{{ __('remaining_amount.history.method') }}</label>
                                                                    <select x-model="editPaymentForm.payment_method" class="modal-select-sm">
                                                                        <option value="Cash">{{ __('booking.payment.cash') }}</option>
                                                                        <option value="ABA">{{ __('booking.payment.aba') }}</option>
                                                                        <option value="Card">{{ __('booking.payment.card') }}</option>
                                                                        <option value="QR">{{ __('booking.payment.qr') }}</option>
                                                                    </select>
                                                                </div>
                                                                <div class="edit-field">
                                                                    <label>{{ __('remaining_amount.history.date') }}</label>
                                                                    <input type="datetime-local" x-model="editPaymentForm.payment_date" class="modal-input-sm">
                                                                </div>
                                                                <div class="edit-field" style="grid-column: span 3;">
                                                                    <label>{{ __('remaining_amount.history.note') }}</label>
                                                                    <input type="text" x-model="editPaymentForm.note" class="modal-input-sm" placeholder="{{ __('booking.placeholder.note') }}">
                                                                </div>
                                                                <div class="edit-actions" style="grid-column: span 3; justify-content: flex-end; display: flex; gap: 8px;">
                                                                    <button type="button" class="btn-cancel-sm" @click="cancelEditPayment()">{{ __('action_button.cancel') }}</button>
                                                                    <button type="button" class="btn-save-sm" :disabled="paymentSubmitting" @click="saveEditPayment(pay)">{{ __('action_button.save') }}</button>
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
                    <label>{{ __('remaining_amount.export_loading') }}</label>
                </div>
            </div>
        </template>
    </div>

    <style>
        .modal-backdrop-custom {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(15, 23, 42, 0.6);
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-dialog-custom {
            background: #ffffff;
            border-radius: 12px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            animation: modalFadeInCustom 0.2s ease-out;
        }
        @keyframes modalFadeInCustom {
            from { opacity: 0; transform: scale(0.96); }
            to { opacity: 1; transform: scale(1); }
        }
        .modal-header-custom {
            padding: 16px 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .modal-title-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .modal-title-wrap svg,
        .modal-title-wrap i {
            width: 22px;
            height: 22px;
            color: #00b74a;
        }
        .modal-title-wrap h3 {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
        }
        .modal-subtitle-text {
            font-size: 12px;
            color: #64748b;
            margin-top: 2px;
        }
        .btn-close-modal {
            background: none;
            border: none;
            font-size: 24px;
            line-height: 1;
            color: #94a3b8;
            cursor: pointer;
        }
        .btn-close-modal:hover {
            color: #334155;
        }
        .modal-body-custom {
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .remaining-info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .remaining-info-box .info-label {
            font-size: 13px;
            font-weight: 500;
            color: #64748b;
        }
        .remaining-info-box .info-val {
            font-size: 16px;
            font-weight: 700;
            color: #dc2626;
        }
        .form-group-modal {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .form-group-modal label {
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin: 0;
        }
        .modal-input, .modal-textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
            box-sizing: border-box;
        }
        .modal-input:focus, .modal-textarea:focus {
            border-color: #3C91E6;
            box-shadow: 0 0 0 3px rgba(60, 145, 230, 0.15);
        }
        .input-action-wrap {
            display: flex;
            gap: 8px;
        }
        .btn-fill-max {
            background: #ffffff;
            border: 1px solid rgba(152, 152, 152, 0.25);
            border-radius: 20px;
            padding: 0 12px;
            font-size: 12px;
            font-weight: 500;
            color: #333333;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s ease;
        }
        .btn-fill-max:hover {
            background: #f5f5f5;
            color: #111111;
        }
        .payment-method-selector {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
        }
        .method-option {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 6px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            transition: all 0.15s;
            margin: 0;
            background: #ffffff;
        }
        .method-option input {
            display: none;
        }
        .method-option svg {
            width: 16px;
            height: 16px;
        }
        .method-option.is-active {
            border-color: #00c753 !important;
            background: #ecfdf5 !important;
            color: #00c753 !important;
        }
        .modal-footer-custom {
            padding: 16px 20px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            background: #f8fafc;
        }
        .btn-system {
            height: 40px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            grid-gap: 7px !important;
            gap: 7px !important;
            padding: 0 16px !important;
            border-radius: 20px !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            cursor: pointer !important;
            text-decoration: none !important;
            text-transform: none !important;
            border: unset !important;
            box-sizing: border-box !important;
            white-space: nowrap !important;
            transition: all 0.2s ease !important;
            line-height: 1 !important;
        }
        .btn-system svg {
            width: 18px !important;
            height: 18px !important;
            stroke-width: 2 !important;
            line-height: 0 !important;
            display: inline-block !important;
            vertical-align: middle !important;
        }
        .btn-system span {
            font-size: 13px !important;
            line-height: normal !important;
        }
        .btn-system-success {
            background-color: #00b74a !important;
            color: #ffffff !important;
            border: unset !important;
            box-shadow: 0 0 3px rgba(0, 0, 0, 0.1) !important;
        }
        .btn-system-success:hover:not(:disabled) {
            background-color: #009e40 !important;
            color: #ffffff !important;
            box-shadow: 0 2px 6px rgba(0, 183, 74, 0.3) !important;
        }
        .btn-system-outline,
        .btn-system-neutral {
            background-color: #ffffff !important;
            color: #333333 !important;
            border: 1px solid rgba(152, 152, 152, 0.25) !important;
            box-shadow: 0 0 3px rgba(0, 0, 0, 0.05) !important;
        }
        .btn-system-outline:hover:not(:disabled),
        .btn-system-neutral:hover:not(:disabled) {
            background-color: #f5f5f5 !important;
            border-color: rgba(152, 152, 152, 0.45) !important;
            color: #111111 !important;
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
                showAddPaymentModal: false,
                activeModalTab: 'pay',
                activeBooking: null,
                addPaymentBooking: null,
                paymentSubmitting: false,
                addPaymentSubmitting: false,
                reminderLoading: false,
                editingPaymentId: null,
                addPaymentForm: {
                    amount: null,
                    payment_method: 'Cash',
                    payment_date: moment().format('YYYY-MM-DDTHH:mm'),
                    note: '',
                },
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
                        placeholder: @json(__('booking.select_shop')),
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
                        placeholder: @json(__('booking.select_barber')),
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
                paymentStatusLabel(status) {
                    const map = {
                        'Paid': @json(__('booking.status.paid')),
                        'Partial': @json(__('booking.status.partial')),
                        'Cancel': @json(__('booking.status.rejected')),
                        'Pending': @json(__('booking.status.pending')),
                    };
                    return map[status] || status || @json(__('booking.status.pending'));
                },
                paymentMethodLabel(method) {
                    const map = {
                        'Cash': @json(__('booking.payment.cash')),
                        'ABA': @json(__('booking.payment.aba')),
                        'Card': @json(__('booking.payment.card')),
                        'QR': @json(__('booking.payment.qr')),
                    };
                    return map[method] || method || 'Cash';
                },
                openAddPaymentModal(item) {
                    this.addPaymentBooking = item;
                    this.showAddPaymentModal = true;
                    this.addPaymentSubmitting = false;
                    const initialAmount = (item && Number(item.remaining_amount) > 0)
                        ? Number(item.remaining_amount)
                        : null;
                    this.addPaymentForm = {
                        amount: initialAmount,
                        payment_method: 'Cash',
                        payment_date: moment().format('YYYY-MM-DDTHH:mm'),
                        note: '',
                    };

                    this.$nextTick(() => {
                        if (window.feather) {
                            feather.replace();
                        }
                    });

                    if (item?.id) {
                        Axios.get(`{{ url('admin/remaining-amount/payment-details') }}/${item.id}`)
                            .then((res) => {
                                if (res.data) {
                                    this.addPaymentBooking = res.data;
                                    if (!this.addPaymentForm.amount && res.data.remaining_amount) {
                                        this.addPaymentForm.amount = Number(res.data.remaining_amount);
                                    }
                                }
                            })
                            .catch((err) => {
                                console.error('Error fetching booking details for Add Payment:', err);
                            });
                    }
                },
                closeAddPaymentModal() {
                    this.showAddPaymentModal = false;
                    this.addPaymentBooking = null;
                    this.addPaymentSubmitting = false;
                },
                async submitAddPayment() {
                    if (this.addPaymentSubmitting || !this.addPaymentBooking?.id) return;
                    if (!this.addPaymentForm.amount || Number(this.addPaymentForm.amount) <= 0) {
                        alert(@json(__('booking.validation.payment_amount_required') ?: 'Please enter a valid payment amount.'));
                        return;
                    }
                    if (Number(this.addPaymentForm.amount) > Number(this.addPaymentBooking.remaining_amount || 0)) {
                        alert(@json(__('booking.validation.payment_amount_exceeds') ?: 'Payment amount exceeds available balance.'));
                        return;
                    }

                    this.addPaymentSubmitting = true;
                    const url = `{{ url('admin/remaining-amount/add-payment') }}/${this.addPaymentBooking.id}`;

                    try {
                        const response = await Axios.post(url, {
                            _token: '{{ csrf_token() }}',
                            amount: this.addPaymentForm.amount,
                            payment_method: this.addPaymentForm.payment_method,
                            payment_date: this.addPaymentForm.payment_date,
                            note: this.addPaymentForm.note,
                        });

                        if (response.data && (response.data.message === 'success' || response.status === 200)) {
                            if (window.toastr) {
                                toastr.success(@json(__('booking.message.payment_status_success')));
                            } else if (window.iziToast) {
                                iziToast.success({
                                    title: 'Success',
                                    message: @json(__('booking.message.payment_status_success')),
                                    position: 'topRight'
                                });
                            }
                            this.closeAddPaymentModal();
                            reloadData(`{{ url()->full() }}`);
                        } else {
                            alert(response.data?.error || @json(__('remaining_amount.message.error_record_payment')));
                            this.addPaymentSubmitting = false;
                        }
                    } catch (error) {
                        this.addPaymentSubmitting = false;
                        const errorMsg = error.response?.data?.error ||
                            Object.values(error.response?.data?.errors || {})?.[0]?.[0] ||
                            @json(__('remaining_amount.message.error_record_payment'));
                        alert(errorMsg);
                    }
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

                    this.$nextTick(() => {
                        if (window.feather) {
                            feather.replace();
                        }
                    });

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
                            @json(__('remaining_amount.message.error_record_payment'));
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
                            @json(__('remaining_amount.message.error_update_payment'));
                        alert(message);
                    }).finally(() => {
                        this.paymentSubmitting = false;
                    });
                },
                confirmPaymentAction(message, btnSave, onConfirm) {
                    const confirmDialog = this.$store?.confirmDialog;
                    if (confirmDialog && typeof confirmDialog.open === 'function') {
                        confirmDialog.open({
                            data: {
                                message: message,
                                btnClose: `{{ __('action_button.cancel') }}`,
                                btnSave: btnSave || @json(__('action_button.delete')),
                                typeAction: 'manual',
                                digPosition: 'posTop',
                                class: 'deleteDialog',
                                width: '18rem',
                            },
                            afterClosed: (result) => {
                                if (result) {
                                    onConfirm();
                                }
                            }
                        });
                        return;
                    }

                    if (window.confirm(message.replace(/<[^>]*>/g, ''))) {
                        onConfirm();
                    }
                },
                deletePayment(pay) {
                    const amount = pay.amount_formatted || ('$' + Number(pay.amount || 0).toFixed(2));
                    const method = this.paymentMethodLabel(pay.payment_method);
                    const template = @json(__('remaining_amount.confirm.delete_payment_record'));
                    const message = template.replace(':amount', amount).replace(':method', method);

                    this.confirmPaymentAction(message, @json(__('booking.action.delete_payment')), () => {
                        this.paymentSubmitting = true;
                        Axios.delete(`{{ url('admin/remaining-amount/delete-payment') }}/${pay.id}`, {
                            data: { _token: '{{ csrf_token() }}' }
                        }).then((res) => {
                            this.activeBooking = res.data;
                            reloadData(`{{ url()->full() }}`);
                        }).catch((err) => {
                            const message = err.response?.data?.error ||
                                Object.values(err.response?.data?.errors || {})?.[0]?.[0] ||
                                @json(__('remaining_amount.message.error_delete_payment'));
                            alert(message);
                        }).finally(() => {
                            this.paymentSubmitting = false;
                        });
                    });
                },
                sendReminderNotification() {
                    if (!this.activeBooking?.id) return;
                    this.reminderLoading = true;
                    Axios.post(`{{ url('admin/remaining-amount/send-reminder') }}/${this.activeBooking.id}`, {
                        _token: '{{ csrf_token() }}'
                    }).then((res) => {
                        alert(res.data.success_message || @json(__('remaining_amount.message.reminder_sent_success')));
                    }).catch((err) => {
                        alert(err.response?.data?.error || @json(__('remaining_amount.message.error_send_reminder')));
                    }).finally(() => {
                        this.reminderLoading = false;
                    });
                },
                quickSendReminder(item) {
                    if (!item?.id) return;
                    Axios.post(`{{ url('admin/remaining-amount/send-reminder') }}/${item.id}`, {
                        _token: '{{ csrf_token() }}'
                    }).then((res) => {
                        alert(res.data.success_message || @json(__('remaining_amount.message.reminder_sent_success')));
                    }).catch((err) => {
                        alert(err.response?.data?.error || @json(__('remaining_amount.message.error_send_reminder')));
                    });
                },
                formatCurrency(val) {
                    return '$' + (Number(val || 0)).toFixed(2);
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
                    const worksheet = workbook.addWorksheet(@json(__('remaining_amount.excel.sheet_name')));
                    worksheet.columns = [
                        { header: @json(__('remaining_amount.excel.booking_id')), key: 'booking_id', width: 16 },
                        { header: @json(__('remaining_amount.excel.booking_date')), key: 'booking_date', width: 18 },
                        { header: @json(__('remaining_amount.excel.shop')), key: 'shop', width: 22 },
                        { header: @json(__('remaining_amount.excel.barber')), key: 'barber', width: 22 },
                        { header: @json(__('remaining_amount.excel.customer_phone')), key: 'customer_phone', width: 18 },
                        { header: @json(__('remaining_amount.excel.pay_status')), key: 'payment_status', width: 14 },
                        { header: @json(__('remaining_amount.excel.total_price')), key: 'total_price', width: 14 },
                        { header: @json(__('remaining_amount.excel.paid_amount')), key: 'paid_amount', width: 14 },
                        { header: @json(__('remaining_amount.excel.remaining_balance')), key: 'remaining_amount', width: 18 },
                        { header: @json(__('remaining_amount.excel.last_pay_date')), key: 'payment_date', width: 18 },
                    ];

                    rows.forEach((item) => {
                        worksheet.addRow({
                            booking_id: item?.invoice_number || '',
                            booking_date: item?.booking_date ? moment(item.booking_date).format('YYYY-MM-DD HH:mm') : '',
                            shop: item?.shop?.name || '',
                            barber: item?.barber?.name || '',
                            customer_phone: item?.customer?.phone || '',
                            payment_status: this.paymentStatusLabel(item?.payment_status),
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
                        saveAs(blob, @json(__('remaining_amount.excel.file_prefix')) + moment().format('YYYY_MM_DD_HHmmss'));
                    });
                },
            }));
        });
    </script>
@stop
