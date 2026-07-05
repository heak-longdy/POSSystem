@extends('admin::shared.layout')
@section('layout')
    <div class="form-admin booking" x-data="XDatacreateorder">
        <div id="form" class="form-wrapper">
            <div class="form-header">
                <h3>
                    <a href="{{ route('admin-booking-list') }}"><i data-feather="arrow-left"></i></a>&nbsp;
                    Update Booking
                </h3>
            </div>
            <div class="orderLayoutGp">
                <div class="div productListingGp">
                    <div class="filterGp">
                        <select class="selectBooking" @change="changeSelectType($event)">
                            <option value="product" :selected="selectType == 'product' ? true : false">Product</option>
                            <option value="service" :selected="selectType == 'service' ? true : false">Service</option>
                        </select>
                        <input type="text" placeholder="Search ..." x-model="searchFilter" x-ref="searchFilter"
                            x-on:input="fiterProduct($refs.searchFilter.value)"><label><i data-feather="search"></i></label>
                    </div>
                    <div class="productListingFilterGp">
                        <template x-if="dataFilter?.length > 0 && !loading">
                            <template x-for="item in dataFilter">
                                <div class="itemProductFilter" :class="item?.addToCart ? 'addToCartBG' : ''">
                                    <div class="itemProductF">
                                        <img :src="selectType == 'product' ? item?.product?.image_url : item?.service?.image_url"
                                            onerror="(this).src='{{ asset('images/logo/default.png') }}'">
                                        <div class="textItemGp">
                                            <h3 x-show="selectType=='product'" x-text="item?.product?.name"></h3>
                                            <h3 x-show="selectType=='service'" x-text="item?.service?.name"></h3>
                                            <div class="listingPriceTypeGp">
                                                <div class="listingGp">
                                                    <div class="itemLS price">
                                                        <label>Price</label>
                                                        <span>:</span>
                                                        <p x-show="selectType=='product'"
                                                            x-text=" (item?.price ? item?.price?.toFixed(2) : item?.product?.price?.toFixed(2)) + '៛'">
                                                        </p>
                                                        <p x-show="selectType=='service'"
                                                            x-text="(item?.price ? item?.price?.toFixed(2) : item?.service?.price?.toFixed(2)) + '៛' ">
                                                        </p>
                                                    </div>
                                                    <div class="itemLS" x-show="item?.discount">
                                                        <label>Discount</label>
                                                        <span>:</span>
                                                        <p>
                                                            <template x-if="item?.type=='percent'">
                                                                <span
                                                                    x-text="(item?.discount ? item.discount : 0)+'%'"></span>
                                                            </template>
                                                            <template x-if="item?.type=='khr'">
                                                                <span
                                                                    x-text="(item?.discount ? item?.discount?.toFixed(2) : 0)+'៛'"></span>
                                                            </template>
                                                        </p>
                                                    </div>
                                                    <div class="itemLS" x-show="item?.commission">
                                                        <label>Commission</label>
                                                        <span>:</span>
                                                        <p>
                                                            <template x-if="item?.commission_type=='percent'">
                                                                <span
                                                                    x-text="(item?.commission ? item.commission : 0)+'%'"></span>
                                                            </template>
                                                            <template x-if="item?.commission_type=='khr'">
                                                                <span
                                                                    x-text="(item?.commission ? item.commission.toFixed(2) : 0)+'៛'"></span>
                                                            </template>
                                                        </p>
                                                    </div>
                                                    <div class="btnActionOrderAddCartGp">
                                                        <button type="button" class="addCart" @click="addToCart(item)">
                                                            Add Cart
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </template>
                        <template x-if="dataFilter?.length <= 0 && !loading">
                            <div class="emptyDataLayout">
                                <span class="material-symbols-outlined">search</span>
                                <div class="textEmpty">
                                    <p>Data Not Found</p>
                                </div>
                            </div>
                        </template>
                        <template x-if="loading">
                            <div x-data={dataEmpty:5}>
                                <template x-for="data in dataEmpty">
                                    <div class="loadingShimmerLayout">
                                        <div class="itemLoad">
                                            <div class="imgBox"></div>
                                            <div class="textItemGp">
                                                <div class="lineBox"></div>
                                                <div class="lineBox-2"></div>
                                                <div class="lineBox-2"></div>
                                                <div class="btnActionOrderAddCartGp">
                                                    <button class="addCart"> </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="div productListingCartGp">
                    <div class="titleGp">
                        <i data-feather="align-left"></i>&nbsp;&nbsp;
                        <label>Shopping Cart </label>
                    </div>
                    <div class="productListingOrderCart">
                        <template x-if="dataCart?.length > 0">
                            <template x-for="(item,index) in dataCart">
                                <div class="listingOrderItem">
                                    <div class="imageOrderGp" data-fancybox x-bind:data-src="item.image">
                                        <img :src="item?.image"
                                            onerror="(this).src='{{ asset('images/logo/default.png') }}'">
                                    </div>
                                    <div class="textGpOrder">
                                        <h3 x-text="item?.name"></h3>
                                        <div class="priceOrderGp">
                                            <div class="priceGp">
                                                <label>Price</label>
                                                <span>:</span>
                                                <p
                                                    x-text="(item?.itemData?.price ? item.itemData?.price?.toFixed(2) : 0) + '៛'">
                                                </p>
                                            </div>
                                            {{-- <div class="discountGp" x-show="item?.itemData?.discount">
                                                <label>Discount</label>
                                                <span>:</span>
                                                <p>
                                                    <template x-if="item?.itemData?.discountType=='percent'">
                                                        <span
                                                            x-text="(item?.itemData?.discount ? item?.itemData?.discount : 0) + '%'"></span>
                                                    </template>
                                                    <template x-if="item?.itemData?.discountType=='khr'">
                                                        <span
                                                            x-text="(item?.itemData?.discount ? item?.itemData?.discount : 0) + '៛'"></span>
                                                    </template>
                                                </p>
                                            </div> --}}
                                            {{-- <div class="discountGp" x-show="item?.itemData?.commission">
                                                <label>Commission</label>
                                                <span>:</span>
                                                <p>
                                                    <template x-if="item?.itemData?.commissionType=='percent'">
                                                        <span
                                                            x-text="(item?.itemData?.commission ? item?.itemData?.commission : 0) + '%'"></span>
                                                    </template>
                                                    <template x-if="item?.itemData?.commissionType=='khr'">
                                                        <span
                                                            x-text="(item?.itemData?.commission ? item?.itemData?.commission.toFixed(2) : 0) + '៛'"></span>
                                                    </template>
                                                </p>
                                            </div> --}}
                                            <template x-if="item.product_type=='service'">
                                                <div class="discountGp">
                                                    <label>Qty</label>
                                                    <span>:</span>
                                                    <p x-text="(item?.product_qty ? item?.product_qty : 1)"></p>
                                                </div>
                                            </template>
                                        </div>
                                        <template x-if="item.product_type=='product'">
                                            <div class="qtyOrderGpSpan">
                                                <div class="qtyOrderGp" :class="item?.error ? 'red' : ''">
                                                    <label>Quantity</label>
                                                    <input type="number" min="1" step="1"
                                                        x-model="item.product_qty" x-on:input="qtyRealTimeAction(item)" />
                                                </div>
                                                <template x-if="item?.error">
                                                    <span class="spanQty">limited in stock or out of stock</span>
                                                </template>

                                            </div>
                                        </template>
                                        <div class="qtyOrderGp2">
                                            <div class="qtyOrderGp commissionBooking" x-show="item?.itemData?.commission">
                                                <label>Commission&nbsp;(<span
                                                        x-text="item?.itemData?.commissionType=='percent'?'%':'៛'"></span>)</label>
                                                <input type="number" min="1" step="1"
                                                    x-model="item.itemData.commission"
                                                    x-on:input="commissionRealTimeAction(item)" />
                                            </div>
                                        </div>
                                        <div class="qtyOrderGp2">
                                            <div class="qtyOrderGp">
                                                <label>Discount</label>
                                                <div class="discountSelect">
                                                    <select x-model="item.itemData.discountType"
                                                        :value="item?.itemData?.discountType"
                                                        @change="discountSelectOpton($event,item)">
                                                        <option value="khr">៛</option>
                                                        <option value="percent">%</option>
                                                    </select>
                                                    <input type="number" min="1" step="1"
                                                        x-model="item.itemData.discount"
                                                        x-on:input="discountRealTiemAction(item)" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="removeOrderGp" x-on:click="removeShippingCart(item,index)">
                                            <span class="material-symbols-outlined">
                                                delete
                                            </span>
                                            <label>Remove</label>
                                        </div>
                                    </div>
                                    <div class="productType" :class="item?.product_type" x-text="item?.product_type">
                                    </div>
                                </div>
                            </template>
                        </template>
                        <template x-if="dataCart?.length <= 0 || dataCart == ''">
                            <div class="emptyDataLayout">
                                <div class="image">
                                    <img src="{!! $image ?? asset('images/logo/empty.svg') !!}" alt="">
                                </div>
                                <div class="textEmpty">
                                    <p>Shopping cart data not found ...</p>
                                </div>
                            </div>
                        </template>
                        <template x-for="item in dataError?.dataCarts">
                            <div class="errorCenter">
                                <span class="error" x-text="item">Error</span>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="div paymentListTotalGp">
                    <div class="bookingShopInfoII">
                        <h3>Shop Info</h3>
                        <div class="shopInfoHeader">
                            <img :src="shopData?.image_url ? shopData?.image_url : '{{ asset('images/logo/shopLogo.png') }}'"
                                onerror="(this).src='{{ asset('images/logo/shopLogo.png') }}'">
                            <div class="infoText">
                                <h4 x-text="shopData?.name"></h4>
                                <p><i data-feather="phone"></i>&nbsp;<span
                                        x-text="shopData?.phone ? shopData.phone : '----' "></span></p>
                            </div>
                        </div>
                        {{-- <div class="shopInfoBody">
                            <h4>Shop Address</h4>
                            <p x-text="shopData?.address"></p>
                        </div> --}}
                    </div>
                    {{-- <div class="bookingShopInfo">
                        <img :src="shopData?.image_url" onerror="(this).src='{{ asset('images/logo/default.png') }}'">
                        <div class="infoText">
                            <h3 x-text="shopData?.name"></h3>
                            <p><i data-feather="phone"></i>&nbsp;<span x-text="shopData?.phone"></span></p>
                        </div>
                    </div> --}}
                    <div class="infoFormPaymentLayout">
                        <label class="label">Form Information</label>

                        <div class="itemForm">
                            <div class="selectOptionLayoutBooking">
                                <label>Customer<span>*</span></label>
                                <select name="customer_id" id="customer_id" x-model="formData.customer_id"
                                    class="SelectSale" x-init="fetchSelectCustomer()">
                                    <option value="">Select customer</option>
                                </select>
                                <template x-for="item in dataError?.customer_id">
                                    <span class="error" x-text="item">Error</span>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div class="paymentGp">
                        <div class="titleGp">
                            <i data-feather="credit-card"></i>&nbsp;&nbsp;
                            <label>Sammery Payment</label>
                        </div>
                        <div class="bodyTotalGp">
                            <div class="itemTotal">
                                <label>Sub Total</label>
                                <span>:</span>
                                <div x-text="subTotal.toFixed(2)+'៛'" x-model="subTotal"></div>
                            </div>
                            <div class="itemTotal">
                                <label>Total Commission</label>
                                <span>:</span>
                                <div x-text="commissionTotal.toFixed(2)+'៛'" x-model="commissionTotal"></div>
                            </div>
                            <div class="itemTotal">
                                <label>Total Price</label>
                                <span>:</span>
                                <div x-text="total.toFixed(2)+'៛'" x-model="total"></div>
                            </div>
                            <div class="itemTotal">
                                <label>Total Discount</label>
                                <span>:</span>
                                <div x-text="(subTotal - total).toFixed(2) +'៛'"></div>
                            </div>
                            <div class="itemTotal">
                                <label>Amount Paid</label>
                                <span>:</span>
                                <div x-text="amountPaid.toFixed(2)+'៛'" x-model="amountPaid"></div>
                            </div>
                        </div>
                        <div class="orderBtnActionGp">
                            <button type="button" :disabled="submitLoading" class="paymentSub"
                                @click="submitBooking()">
                                <div class="loading loadingSubmit" x-show="submitLoading"><span id="spinner"></span>
                                </div>
                                <i data-feather="credit-card" x-show="!submitLoading"></i>&nbsp;&nbsp;
                                Submit
                            </button>
                            <a href="{{ route('admin-booking-list') }}">
                                <button class="cancel">
                                    Cancel
                                </button>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
@section('script')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('XDatacreateorder', () => ({
                loading: false,
                dataError: null,
                member: Object(),
                formData: {
                    shop_id: null,
                    status: "confirmed",
                    customer_id: null,
                    disable: false,
                },
                btnSubmit: 'Save',
                baseImageUrl: "{{ asset('file_manager') }}",
                searchFilter: null,
                dataFilter: [],
                dataCart: Array(),
                subTotal: 0,
                commissionTotal: 0,
                total: 0,
                amountPaid: 0,
                submitLoading: false,
                bookingId: null,
                selectType: "service",
                shopData: null,
                bookingDeleteId: [],
                init() {
                    this.shopData = @json($data->shop);
                    this.dataCart = [];
                    const data = @json($data);
                    if (data) {
                        this.bookingId = data.id;
                        let customer = data?.customer ? data?.customer : null;
                        var option = "<option selected></option>";
                        var selectOptionHTML = $(option).val(customer?.id ? customer.id : null).text(
                            customer
                            ?.name ? customer.name : customer?.phone);
                        $('.SelectSale').append(selectOptionHTML).trigger('change');
                        this.formData.customer_id = customer?.id;
                        if (data?.bookingDetail?.length > 0) {

                            let total = 0;
                            data.bookingDetail.forEach(val => {
                                let commission = val?.type == "service" ? val
                                    ?.service_commission : val?.product_commission;
                                let commissionType = val?.type == "service" ? val
                                    .service_commission_type : val.product_commission_type;
                                let totalCommission = this.getCommssion(commissionType, val
                                    ?.price, commission);
                                let item = val?.type == "service" ? val.service : val.product;
                                let point = val?.type == "service" ? val?.point : val?.point;
                                let price = val?.type == "service" ? val?.price : val?.price;
                                let discount = val?.type == "service" ? val?.service_discount :
                                    val?.product_discount;
                                let discountType = val?.type == "service" ? val
                                    ?.service_discount_type : val?.product_discount_type;
                                if (discountType == "percent") {
                                    total = price - (price * (discount ? discount : 0) / 100);
                                } else if (discountType == "khr") {
                                    total = discount && discount > price ? 0 : price - (
                                        discount ? discount : 0);
                                }
                                let itemData = {
                                    price: price,
                                    point: point,
                                    discount: discount ? discount : 0,
                                    discountType: discountType,
                                    commission: commission,
                                    commissionType: commissionType,
                                    totalCommission: totalCommission,
                                    total: total
                                };
                                let BookingDetailItem = {
                                    id: val?.id,
                                    product_id: item?.id,
                                    name: item?.name ? item.name : '---',
                                    image: item?.image_url,
                                    itemData: itemData,
                                    product_qty: val?.qty ? val?.qty : 1,
                                    product_type: val?.type,
                                };
                                this.dataCart.push(BookingDetailItem);
                            });
                        }
                        this.calculatorProductPrice();
                    }
                    this.fiterProduct();
                },
                changeSelectType($event) {
                    this.selectType = $event.target.value;
                    this.searchFilter = "";
                    this.fiterProduct();
                },
                fetchSelectCustomer() {
                    $('#customer_id').select2({
                        placeholder: `Select customer...`,
                        ajax: {
                            url: '{{ route('admin-select-customer') }}',
                            dataType: 'json',
                            type: "GET",
                            quietMillis: 50,
                            data: function(param) {
                                return {
                                    search: param.term
                                };
                            },
                            processResults: function(data) {
                                return {
                                    results: $.map(data.data, function(item) {
                                        return {
                                            text: item?.name ? item?.name : item
                                                ?.phone,
                                            id: item.id
                                        }
                                    })
                                };
                            }
                        }
                    }).on('select2:open', (e) => {
                        document.querySelector('.select2-search__field').focus();
                    }).on('select2:close', async (eventClose) => {
                        const customer_id = eventClose.target.value;
                        if (!customer_id) {
                            return true;
                        }
                        this.formData.customer_id = customer_id;
                    });
                },
                submitBooking() {
                    this.dataError = [];
                    this.productValidation((valid) => {
                        if (valid.length > 0) {
                            return true;
                        } else {
                            this.$store.confirmDialog.open({
                                data: {
                                    title: "Message",
                                    message: "Are you sure to save?",
                                    btnClose: "Close",
                                    btnSave: "Yes",
                                },
                                afterClosed: (result) => {
                                    if (result) {
                                        this.submitLoading = true;
                                        const data = this.formData;
                                        data.dataCarts = this.dataCart.length ? JSON
                                            .stringify(this
                                                .dataCart) : [];
                                        data.shop = this.member ? JSON.stringify(
                                                this.shopData) :
                                            null;
                                        data.shop_id = this.shopData?.id;
                                        data.commissionTotal = this.commissionTotal;
                                        data.subTotal = this.subTotal;
                                        data.total = this.total;
                                        data.total_discount = parseFloat(this
                                                .subTotal) -
                                            parseFloat(this.total);
                                        setTimeout(() => {
                                            Axios({
                                                url: `{{ route('admin-booking-save') }}`,
                                                method: 'POST',
                                                data: {
                                                    ...data,
                                                    id: this
                                                        .bookingId,
                                                    bookingDelete: this
                                                        .bookingDeleteId,
                                                }
                                            }).then((res) => {
                                                if (res.data
                                                    .message ==
                                                    "success") {
                                                    this.submitLoading =
                                                        false;
                                                    setTimeout(
                                                        () => {
                                                            window
                                                                .location
                                                                .href =
                                                                '{{ route('admin-booking-list', 1) }}';
                                                        }, 100);
                                                }
                                            }).catch((e) => {
                                                this.dataError = e
                                                    .response?.data
                                                    .errors;
                                                this.submitLoading =
                                                    false;
                                            }).finally(() => {
                                                this.submitLoading =
                                                    false;
                                            });
                                        }, 500);
                                    }
                                }
                            });
                        }
                    });
                },
                async fiterProduct(search) {
                    this.loading = true;
                    let timer = 0;
                    clearTimeout(timer);
                    timer = setTimeout(async () => {
                        await this.fetchData(
                            `/admin/select/product?search=${search ? search :''}&shop_id=${this.shopData?.id}&type=${this.selectType}`,
                            (res) => {
                                this.dataFilter = res?.data;
                                console.log(this.dataFilter,'this.dataFilterthis.dataFilterthis.dataFilter');
                                if (this.dataFilter.length > 0) {
                                    this.dataFilter.map(itemVal => {
                                        const item = this.selectType ==
                                            "service" ? itemVal.service :
                                            itemVal.product;
                                        const dataFind = this.dataCart.find(
                                            val => val.product_id ==
                                            item?.id && val
                                            .product_type == this
                                            .selectType);
                                        if (dataFind) {
                                            itemVal.addToCart = true;
                                        }
                                    });
                                }
                                this.loading = false;
                            });
                    }, 500);
                },
                async fetchData(url, callback) {
                    await fetch(url, {
                            method: "GET",
                            headers: {
                                "Content-Type": "application/json",
                                "Accept": "application/json",
                            }
                        })
                        .then(async (res) => {
                            let data = await res.json();
                            if (data) {
                                callback(data);
                            }
                        })
                        .catch((e) => {
                        })
                        .finally(() => {});
                },
                totalDiscount(type, price, discount) {
                    let amount = 0;
                    if (type == "percent") {
                        amount = price - (price * (discount ? discount : 0) / 100);
                    } else if (type == "khr") {
                        amount = discount && discount > price ? 0 : price - (discount ? discount : 0);
                    }
                    return amount;
                },
                getCommssion(type, price, commission) {
                    let amount = parseFloat(commission);
                    if (type == "percent") {
                        amount = (price * (parseFloat(commission) ? parseFloat(commission) : 0) / 100);
                    }
                    return amount;
                },
                addToCart(item) {
                    item.addToCart = true;
                    let total = 0;
                    let totalCommission = 0;
                    let price = this.selectType == "product" ? (item?.price ? item?.price : item
                        ?.product.price) : (item?.price ? item?.price : item?.service?.price);
                    total = this.totalDiscount(item?.type, price, item?.discount);
                    totalCommission = this.getCommssion(item?.commission_type, price, item
                        ?.commission);
                    let itemData = {
                        price: item?.price ? item?.price : (this.selectType == "product" ? item
                            ?.product?.price : item?.service?.price) || 0,
                        discount: item?.discount ? item?.discount : 0,
                        point: item?.point ? item.point : 0,
                        formDate: item?.from_date ? item.from_date : null,
                        toDate: item?.to_date ? item.from_date : null,
                        discountType: item?.type ? item?.type : null,
                        commission: item?.commission ? item.commission : 0,
                        commissionType: item?.commission_type ? item.commission_type : null,
                        totalCommission: totalCommission,
                        total: total
                    };
                    let data = {
                        id: null,
                        product_id: this.selectType == "product" ? (item?.product?.id ? item
                            ?.product?.id : '---') : (item?.service?.id ? item?.service?.id :
                            '---'),
                        name: this.selectType == "product" ? (item?.product?.name ? item?.product
                            ?.name : '---') : (item?.service?.name ? item?.service?.name :
                            '---'),
                        product_type: this.selectType,
                        image: this.selectType == "product" ? (item?.product?.image_url ? item
                            ?.product?.image_url : null) : (item?.service?.image_url ? item
                            ?.service?.image_url : null),
                        itemData: itemData,
                    };
                    if (this.dataCart.length > 0) {
                        const findIndex = this.dataCart.findIndex(val => (val.product_id == data
                            .product_id && val.product_type == this.selectType));
                        if (findIndex > -1) {
                            if (this.selectType == "product") {
                                this.dataCart[findIndex].product_qty = parseInt(this.dataCart[findIndex]
                                    .product_qty) + 1;
                            } else {
                                this.dataCart[findIndex].product_qty = 1;
                            }
                        } else {
                            data.product_qty = 1;
                            this.dataCart.push(data);
                        }
                    } else {
                        data.product_qty = 1;
                        this.dataCart.push(data);
                    }
                    this.calculatorProductPrice();
                },
                discountSelectOpton($event, item) {
                    item.itemData.discount = parseFloat(item.itemData.discount);
                    item.itemData.total = this.totalDiscount(item?.itemData.discountType, item.itemData
                        .price, item?.itemData.discount);
                    this.calculatorProductPrice();
                },
                discountRealTiemAction(item) {
                    if (!item.itemData.discountType || item.itemData.discountType == null || item
                        .itemData.discountType == "null") {
                        item.itemData.discountType = "khr";
                    }
                    item.itemData.discount = parseFloat(item.itemData.discount);
                    item.itemData.total = this.totalDiscount(item?.itemData.discountType, item.itemData
                        .price, item?.itemData.discount);
                    this.calculatorProductPrice();
                },
                qtyRealTimeAction(item) {
                    if (!item.product_qty) {
                        item.product_qty = 1;
                    }
                    if (item?.product_type == "service") {
                        item.product_qty = 1;
                    }
                    this.calculatorProductPrice();
                },
                commissionRealTimeAction(item) {
                    const {
                        commissionType,
                        price,
                        commission
                    } = item.itemData;
                    let totalCommission = this.getCommssion(commissionType, price, commission);
                    item.itemData.totalCommission = totalCommission;
                    if (!commission) {
                        item.itemData.commission = 1;
                    }
                    this.calculatorProductPrice();
                },
                removeShippingCart(item, index) {
                    this.dataFilter.find(val => {
                        if (val.id == item.product_id) {
                            val.addToCart = false;
                        }
                    });
                    if (item?.id) {
                        this.bookingDeleteId.push(item.id);
                    }
                    this.dataCart.splice(index, 1);
                    this.calculatorProductPrice();
                },
                calculatorProductPrice() {
                    this.subTotal = 0;
                    this.total = 0;
                    this.commissionTotal = 0;
                    this.amountPaid = 0;
                    if (this.dataCart?.length > 0) {
                        this.dataCart.forEach((item, index) => {
                            let {
                                product_qty,
                                itemData,
                            } = item;
                            let qty = product_qty ? parseInt(product_qty) : 1;
                            let unitPrice = parseFloat(itemData?.price);
                            let price = unitPrice;
                            if (itemData.discount) {
                                const {
                                    discountType,
                                    total
                                } = itemData;
                                price = discountType == "percent" ? parseFloat(total) :
                                    parseFloat(total);
                            }
                            let totalCommission = itemData.totalCommission ? itemData
                                .totalCommission : 0;
                            let subTotal = qty * unitPrice;
                            this.subTotal += subTotal;
                            this.commissionTotal += totalCommission * qty;
                            let totalItem = qty * price;
                            this.total += totalItem;
                        });
                    }
                    this.amountPaid = this.total - this.commissionTotal;
                },
                async productValidation($cb) {
                    var error = [];
                    const data = @json($data);
                    if (this.dataCart.length) {
                        this.submitLoading = true;
                        for (const val of this.dataCart) {
                            val.error = false;
                            let findBookingDetail = data.bookingDetail.find(bkItem => bkItem.type ==
                                "product" && bkItem.product?.id == val.product_id);
                            let findBookingDetailQty = findBookingDetail?.qty ?? 0;
                            if (val.product_type == "product") {
                                let url =
                                    `/admin/select/find-shop-product?shop_id=${this.shopData?.id}&product_id=${val.product_id}`;
                                await this.fetchData(url, (res) => {
                                    if (res) {
                                        let currentStock = parseInt(res.current_stock) + parseInt(findBookingDetailQty);
                                        val.error = currentStock < parseInt(val.product_qty) ? true : false;
                                    } else {
                                        val.error = true;
                                    }
                                });
                            }
                            if (val.error) {
                                error.push(val.name);
                            }
                        }
                    }
                    $cb(error);
                    this.submitLoading = false;
                }
            }));
        });
    </script>
