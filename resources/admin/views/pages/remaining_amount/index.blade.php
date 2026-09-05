@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => __('remaining_amount.title')])
    <div class="content-wrapper" id="app" x-data="xRemainingAmount">
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
                                    <div class="modal-form-group">
                                        <label>{{ __('remaining_amount.form.payment_method') }}</label>
                                        <select x-model="paymentForm.payment_method" class="modal-select">
                                            <option value="Cash">{{ __('booking.payment.cash') }}</option>
                                            <option value="ABA">{{ __('booking.payment.aba') }}</option>
                                            <option value="Card">{{ __('booking.payment.card') }}</option>
                                            <option value="QR">{{ __('booking.payment.qr') }}</option>
                                        </select>
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
