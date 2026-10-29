@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => __('remaining_amount.title')])
    <style>
        /* Keep payment labels readable within the listing's horizontal scroll area. */
        .remaining-amount-wrapper .table-wrapper,
        .order-listing-wrapper .table-wrapper {
            min-width: 1280px;
        }
        .remaining-amount-wrapper .table-header > .row,
        .remaining-amount-wrapper .table-body > .column > .row,
        .order-listing-wrapper .table-header > .row,
        .order-listing-wrapper .table-body > .column > .row {
            flex-shrink: 1;
        }
        .remaining-amount-wrapper .table .row.order-payment-status,
        .order-listing-wrapper .table .row.order-payment-status {
            flex: 0 0 180px;
        }
        .remaining-amount-wrapper .table-body .column .row.order-payment-status > span,
        .order-listing-wrapper .table-body .column .row.order-payment-status > span {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 5px;
            padding-right: 20px;
            overflow: visible;
        }
        .remaining-amount-wrapper .table-body .column .row.order-payment-status .badge,
        .order-listing-wrapper .table-body .column .row.order-payment-status .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            padding: 3px 10px;
            margin: 0 !important;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 500;
            line-height: 18px;
            white-space: nowrap;
            word-break: normal;
        }
        .remaining-amount-wrapper .table-body .column .row.order-payment-status .text-muted,
        .order-listing-wrapper .table-body .column .row.order-payment-status .text-muted {
            display: block;
            font-size: 11px;
            line-height: 16px;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }
    </style>
    <div class="content-wrapper order-listing-wrapper remaining-amount-wrapper" id="app" x-data="xRemainingAmount">
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
                    'label' => __('order.tab.partial'),
                    'icon' => 'bx bx-credit-card',
                    'url' => $tabUrl('partial'),
                    'active' => $status === 'partial',
                ],
                [
                    'label' => __('order.tab.paid'),
                    'icon' => 'bx bx-check-circle',
                    'url' => $tabUrl('paid'),
                    'active' => $status === 'paid',
                ],
            ],
            'exportAction' => 'excel()',
            'exportLabel' => __('global.button.export'),
            'data' => $data,
            'status' => $status,
            'tbHeader' => [
                ['field' => 'index', 'title' => __('order.table.no'), 'class' => '', 'colVal' => 5],
                ['field' => 'invoice_title', 'title' => __('order.table.order_id'), 'class' => 'text left', 'colVal' => 8],
                ['field' => 'shop_title', 'title' => __('order.table.shop'), 'class' => 'text left', 'colVal' => 8],
                ['field' => 'customer_title', 'title' => __('order.table.customer'), 'class' => 'text left', 'colVal' => 12],
                ['field' => 'order_items_title', 'title' => __('order.table.products'), 'class' => 'text left', 'colVal' => 20],
                ['field' => 'payment_status_title', 'title' => __('order.table.pay_status'), 'class' => 'text left order-payment-status', 'colVal' => 8],
                ['field' => 'total_price_title', 'title' => __('order.table.total'), 'class' => 'text left', 'colVal' => 8],
                ['field' => 'paid_amount_title', 'title' => __('order.table.paid'), 'class' => 'text left', 'colVal' => 8],
                ['field' => 'remaining_amount_title', 'title' => __('order.table.remaining'), 'class' => 'text left', 'colVal' => 8],
                ['field' => 'order_date_title', 'title' => __('order.table.order_date'), 'class' => 'text left', 'colVal' => 10],
                [
                    'field' => 'action',
                    'title' => __('global.table.action'),
                    'class' => '',
                    'colVal' => 5,
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
                                    'url' => 'paid',
                                    'title' => __('order.action.paid'),
                                    'icon' => 'check_circle',
                                    'type' => 'click',
                                    'handler' => 'markAsPaid',
                                    'class' => 'text-success',
                                    'visible' => ['payment_status' => 'Partial'],
                                ],
                                [
                                    'url' => 'add-payment',
                                    'title' => __('remaining_amount.action.add_payment'),
                                    'icon' => 'add_circle',
                                    'type' => 'click',
                                    'handler' => 'openAddPaymentModal',
                                    'class' => 'text-success',
                                    'visible' => ['can_add_payment' => true],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ])
        @endcomponent

        <!-- Dedicated Add Payment Modal (Similar to Order Detail) -->
        <div class="modal-backdrop-custom" x-show="showAddPaymentModal" x-transition.opacity style="display: none;">
            <div class="modal-dialog-custom" @click.away="if (!addPaymentSubmitting) closeAddPaymentModal()">
                <div class="modal-header-custom">
                    <div class="modal-title-wrap">
                        <i data-feather="plus-circle" class="text-success"></i>
                        <div>
                            <h3>{{ __('order.detail.add_payment') }}</h3>
                            <div class="modal-subtitle-text"
                                x-text="getAddPaymentSubtitle()">
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
                            <strong class="info-val text-danger" x-text="formatCurrency(addPaymentOrder?.remaining_amount)"></strong>
                        </div>

                        <div class="form-group-modal">
                            <label>{{ __('order.amount') }} ($) <span class="text-danger">*</span></label>
                            <div class="input-action-wrap">
                                <input type="number" step="0.01" min="0.01" :max="addPaymentOrder?.remaining_amount"
                                    class="modal-input" x-model="addPaymentForm.amount" required placeholder="0.00">
                                <button type="button" class="btn-fill-max" @click="addPaymentForm.amount = Number(addPaymentOrder?.remaining_amount || 0)">
                                    {{ __('order.button.full_balance') }}
                                </button>
                            </div>
                        </div>

                        <div class="form-group-modal">
                            <label>{{ __('order.detail.method') }}</label>
                            <div class="payment-method-selector">
                                <label class="method-option" :class="addPaymentForm.payment_method === 'Cash' ? 'is-active' : ''">
                                    <input type="radio" value="Cash" x-model="addPaymentForm.payment_method">
                                    <i data-feather="dollar-sign"></i>
                                    <span>{{ __('order.payment.cash') }}</span>
                                </label>
                                <label class="method-option" :class="addPaymentForm.payment_method === 'ABA' ? 'is-active' : ''">
                                    <input type="radio" value="ABA" x-model="addPaymentForm.payment_method">
                                    <i data-feather="credit-card"></i>
                                    <span>{{ __('order.payment.aba') }}</span>
                                </label>
                                <label class="method-option" :class="addPaymentForm.payment_method === 'Card' ? 'is-active' : ''">
                                    <input type="radio" value="Card" x-model="addPaymentForm.payment_method">
                                    <i data-feather="server"></i>
                                    <span>{{ __('order.payment.card') }}</span>
                                </label>
                                <label class="method-option" :class="addPaymentForm.payment_method === 'QR' ? 'is-active' : ''">
                                    <input type="radio" value="QR" x-model="addPaymentForm.payment_method">
                                    <i data-feather="maximize"></i>
                                    <span>{{ __('order.payment.qr') }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group-modal">
                            <label>{{ __('order.table.pay_date') }}</label>
                            <input type="datetime-local" class="modal-input" x-model="addPaymentForm.payment_date">
                        </div>

                        <div class="form-group-modal">
                            <label>{{ __('order.note') }}</label>
                            <textarea class="modal-textarea" rows="3" x-model="addPaymentForm.note"
                                placeholder="{{ __('order.placeholder.note') }}"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer-custom">
                        <button type="button" class="btn btn-system btn-system-outline btn-system-neutral" :disabled="addPaymentSubmitting" @click="closeAddPaymentModal()">
                            <span>{{ __('order.button.close') }}</span>
                        </button>
                        <button type="submit" class="btn btn-create bg-success btn-system btn-system-success" :disabled="addPaymentSubmitting">
                            <span x-show="!addPaymentSubmitting">{{ __('order.detail.add_payment') }}</span>
                            <span x-show="addPaymentSubmitting" style="display: none;">{{ __('order.button.processing_payment') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>


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
            background: none !important;
            border: none !important;
            font-size: 24px !important;
            line-height: 1 !important;
            color: #94a3b8 !important;
            cursor: pointer !important;
            min-width: 0 !important;
            padding: 0 !important;
        }
        .btn-close-modal:hover {
            color: #334155;
            background: none !important;
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
                showAddPaymentModal: false,
                addPaymentOrder: null,
                paymentSubmitting: false,
                addPaymentSubmitting: false,
                addPaymentForm: {
                    amount: null,
                    payment_method: 'Cash',
                    payment_date: moment().format('YYYY-MM-DDTHH:mm'),
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
                        $select2Data($(this.$root).find('#shop_id'), shop.id, shop.name || shop.phone);
                    }
                    if (barber?.id) {
                        $select2Data($(this.$root).find('#barber_id'), barber.id, barber.name || barber.phone);
                    }
                },
                fetchSelectShop() {
                    $(this.$root).find('#shop_id').select2({
                        placeholder: @json(__('order.select_shop')),
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
                        window.$select2FocusInputSearch();
                    });
                },
                fetchSelectBarber() {
                    $(this.$root).find('#barber_id').select2({
                        placeholder: @json(__('order.select_barber')),
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
                        window.$select2FocusInputSearch();
                    });
                },
                paymentStatusLabel(status, isTransferred = false) {
                    if (isTransferred) return @json(__('order.status.paid_through_transfer'));
                    const map = {
                        'Paid': @json(__('order.status.paid')),
                        'Partial': @json(__('order.status.partial')),
                        'Cancel': @json(__('order.status.canceled')),
                        'Pending': @json(__('order.status.pending')),
                    };
                    return map[status] || status || @json(__('order.status.pending'));
                },
                walkInCustomerText: @json(__('order.walk_in_customer')),
                shopText: @json(__('order.table.shop')),
                orderIdText: @json(__('order.table.order_id')),
                getAddPaymentSubtitle() {
                    const invoice = this.addPaymentOrder?.invoice_number || this.addPaymentOrder?.invoice_title || '';
                    const customer = this.addPaymentOrder?.customer_name
                        || (this.addPaymentOrder?.customer ? (this.addPaymentOrder.customer.name || this.addPaymentOrder.customer.phone) : '')
                        || this.walkInCustomerText;
                    return `${invoice} • ${customer}`;
                },
                paymentMethodLabel(method) {
                    const map = {
                        'Cash': @json(__('order.payment.cash')),
                        'ABA': @json(__('order.payment.aba')),
                        'Card': @json(__('order.payment.card')),
                        'QR': @json(__('order.payment.qr')),
                    };
                    return map[method] || method || 'Cash';
                },
                markAsPaid(item) {
                    const confirmTemplate = "{{ __('order.confirm.mark_as_paid', ['invoice' => '__INVOICE__']) }}";
                    let message = confirmTemplate.replace('__INVOICE__', '<b>' + (item?.invoice_title || '') + '</b>');
                    if (item?.payment_status === 'Partial' && item?.remaining_amount_title) {
                        message += `<br><small style="color:#10b981;font-weight:600;display:block;margin-top:6px;">{{ __('order.table.remaining') }}: ${item.remaining_amount_title}</small>`;
                    }
                    this.$store.confirmDialog.open({
                        data: {
                            message: message,
                            btnClose: `{{ __('global.cancel') }}`,
                            btnSave: '{{ __('order.action.paid') }}',
                            item: item,
                            url: `{{ url('admin/order/update-payment-status') }}/${item.id}`,
                            postData: {
                                payment_status: 'Paid',
                            },
                            typeAction: 'paid',
                            digPosition: "posTop",
                            class: "deleteDialog",
                            icon: 'bx bx-check-circle',
                            iconStyle: 'font-size: 55px; margin-bottom: 15px; border: 1px solid rgba(16, 185, 129, 0.8); color: rgb(16 185 129); border-radius: 50%; padding: 4px;',
                            btnSaveClass: 'bg-success',
                            width: "20rem"
                        },
                        afterClosed: (result) => {
                            if (result) {
                                reloadData(`{{ url()->full() }}`);
                            }
                        }
                    });
                },
                openAddPaymentModal(item) {
                    this.addPaymentOrder = item;
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
                                    this.addPaymentOrder = res.data;
                                    if (!this.addPaymentForm.amount && res.data.remaining_amount) {
                                        this.addPaymentForm.amount = Number(res.data.remaining_amount);
                                    }
                                }
                            })
                            .catch((err) => {
                                console.error('Error fetching order details for Add Payment:', err);
                            });
                    }
                },
                closeAddPaymentModal() {
                    this.showAddPaymentModal = false;
                    this.addPaymentOrder = null;
                    this.addPaymentSubmitting = false;
                },
                async submitAddPayment() {
                    if (this.addPaymentSubmitting || !this.addPaymentOrder?.id) return;
                    if (!this.addPaymentForm.amount || Number(this.addPaymentForm.amount) <= 0) {
                        alert(@json(__('order.validation.payment_amount_required') ?: 'Please enter a valid payment amount.'));
                        return;
                    }
                    if (Number(this.addPaymentForm.amount) > Number(this.addPaymentOrder.remaining_amount || 0)) {
                        alert(@json(__('order.validation.payment_amount_exceeds') ?: 'Payment amount exceeds available balance.'));
                        return;
                    }

                    this.addPaymentSubmitting = true;
                    const url = `{{ url('admin/remaining-amount/add-payment') }}/${this.addPaymentOrder.id}`;

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
                                toastr.success(@json(__('order.message.payment_status_success')));
                            } else if (window.iziToast) {
                                iziToast.success({
                                    title: 'Success',
                                    message: @json(__('order.message.payment_status_success')),
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
                        this.paymentSubmitting = false;
                        this.addPaymentSubmitting = false;
                        const errorMsg = error.response?.data?.error ||
                            Object.values(error.response?.data?.errors || {})?.[0]?.[0] ||
                            @json(__('remaining_amount.message.error_record_payment'));
                        alert(errorMsg);
                    }
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
                        { header: @json(__('remaining_amount.excel.order_id')), key: 'order_id', width: 16 },
                        { header: @json(__('remaining_amount.excel.order_date')), key: 'order_date', width: 18 },
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
                            order_id: item?.invoice_number || '',
                            order_date: item?.order_date ? moment(item.order_date).format('YYYY-MM-DD HH:mm') : '',
                            shop: item?.shop?.name || '',
                            barber: item?.barber?.name || '',
                            customer_phone: item?.customer?.phone || '',
                            payment_status: this.paymentStatusLabel(item?.payment_status, Boolean(item?.is_transferred)),
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
