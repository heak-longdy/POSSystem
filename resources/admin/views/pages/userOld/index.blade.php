@extends('admin::shared.layout')
@section('layout')
    <div class="content-wrapper" x-data="xData">
        <div class="header">
            <div class="header-wrapper marginBottom">
                <div class="btn-toggle-sidebar">
                    <span>Admin Management</span>
                </div>
                <div class="navHeaderRight">
                    @can('barber-create')
                        <button class="btn btn-create" @click="createDialog()">
                            <i class='bx bx-plus-circle'></i>
                            <span>@lang('user.button.create')</span>
                        </button>
                    @endcan
                    {{-- <button s-click-link="{!! url()->current() !!}" class="refresh">
                        <i data-feather="refresh-ccw"></i>
                        <span>Reload</span>
                    </button> --}}
                </div>
            </div>
            <div class="header-tab">
                @include('admin::components.tabListing', [
                    'data' => [
                        [
                            'name' => 'Active',
                            'url' => 'admin/user/list/1',
                        ],
                        [
                            'name' => 'Disable',
                            'url' => 'admin/user/list/2',
                        ],
                    ],
                ])
                <div class="header-action-button">
                    <form class="filter" action="{!! url()->current() !!}" method="GET">
                        <div class="form-row w80">
                            <select name="payment_status">
                                <option value="">All Status</option>
                                <option value="Pending" {!! request('payment_status') == 'Pending' ? 'selected' : '' !!}> Pending</option>
                                <option value="Paid" {!! request('payment_status') == 'Pending' ? 'selected' : '' !!}> Paid</option>
                            </select>
                        </div>

                        <button mat-flat-button type="submit" class="btn-create bg-success btnSearch">
                            <i data-feather="search" style="margin-right: 0;"></i>
                        </button>
                    </form>
                    <button type="button" @click="excel()" class="btnExcel">
                        <i class="material-symbols-outlined">upgrade</i>
                        <span>Excel</span>
                    </button>
                    <button s-click-link="{!! url()->current() !!}">
                        <i data-feather="refresh-ccw"></i>
                        <span>Reload</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="content-body">
            @include('admin::pages.user.table')
        </div>
    </div>
    @include('admin::pages.user.form.create')
    @include('admin::pages.user.form.changePassword')
    @include('admin::pages.user.form.permission')
    @include('admin::file-manager.popup')
@stop
@section('script')
    <script>
        Alpine.data('xData', () => ({
            loading: false,
            loadingSubmit: false,
            dataError: {
                payment_type: [],
                price: [],
            },
            dataClosePrint: false,
            total: 0,
            serviceTotal: 0,
            packageType: '',
            formData: {
                price: null,
                labor_charge: null,
                payment_type: null,
            },
            paymentPopup: false,
            data: [],
            dataCheckAcc: [],
            async init() {
                this.loading = true;
                console.log('hiiiiii24424');
            },
            createDialog() {
                create({
                    data: null,
                });
            },
            editDialog(item) {
                create({
                    data: item,
                });
            },
            changePassword(item) {
                changePassword({
                    data: item,
                });
            },
            setPermission($item) {
                userPermission({
                    data: {
                        item: $item,
                        status: $item?.status,
                        id: $item.id,
                        full_name: $item.name
                    },
                });
            },
            paymentSubmit() {
                const data = this.$store.paymentDialogStore?.data;
                this.btnSubmit = 'Payment';
                Swal.fire({
                    customClass: "confirm-message",
                    icon: "warning",
                    html: `Are you sure to ${this.btnSubmit} car. ?`,
                    confirmButtonText: `${this.btnSubmit}`,
                    cancelButtonText: "Cancel",
                    focusConfirm: false,
                    focusCancel: true,
                }).then(result => {
                    if (result.isConfirmed) {
                        if (result.value == 1) {
                            this.loadingSubmit = true;
                            this.dataError = null;
                            let url = `/admin/payment/submit/${data?.id}`;
                            let dataForm = this.formData;
                            setTimeout(() => {
                                Axios({
                                        method: 'post',
                                        url: url,
                                        data: dataForm
                                    })
                                    .then((response) => {
                                        if (response?.data?.data ==
                                            "your_amount_not_enough") {
                                            alert("Your amount not enough to payment");
                                        } else {
                                            this.paymentPopup = true;
                                        }
                                        this.loadingSubmit = false;
                                    })
                                    .catch((error) => {
                                        this.loadingSubmit = false;
                                        this.dataError = error?.response?.data
                                            ?.errors;
                                    });
                            }, 1000);
                        }
                    }
                });
            },
        }));
    </script>
@stop
