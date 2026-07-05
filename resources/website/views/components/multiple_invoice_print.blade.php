@props([
    'invoice_no'=>null,
    'order_date'=>null,
    'user'=> null,
    'phone'=>null,
    'address'=>null,
    'stores'=>(array) null,
    'details'=>(array) null,
    'delivery_fee'=>0
])
<div id="invoice-multiple" class="invoice-printer" style="font-size: 14px;">
    @foreach ($stores as $store)
        @php
            $storeData = null;
        @endphp
        <div class="container">
            <div class="header-title">
                <div class="invoice-header" style="display: flex;">
                    <img src="{{ asset('images/logo/icon.png') }}" alt="">
                    <div class="header-content" style="margin-left: 15px;">
                        <h1 style="margin: 0;">Khmerstore 24</h1>
                        <p>012345678 / 012345678</p>
                        <p>example@gmail.com</p>
                        <p>Borey Piphop Thmey, Cambodian Red Cross (Blvd 271) No. 02, Street 02, Toeuk Laak 3, Khan Tuol Kok</p>
                    </div>
                </div>
                <div class="invoice-number">
                    <p class="font-bold">#INVOICE</p>
                    <p>{{ $invoice_no }}</p>
                    <p>{{ \Carbon\Carbon::parse($order_date)->isoFormat('DD-MMMM-YYYY') }}</p>
                </div>
            </div>

            <div class="transaction">
                <div class="card-container">
                    <h2>Shipping From</h2>
                    @if ($store)
                    <p><strong>Name:</strong> {{ json_decode($store->name)->en }}</p>
                    <p><strong>Phone:</strong> {{ implode(' / ',json_decode($store->phone)) }}</p>
                    <p><strong>Address:</strong> {{ json_decode($store->address)->en }}</p>
                    @endif
                </div>
                <div class="card-container">
                    <h2>Shipping To</h2>
                    @if ($user)
                    <p><strong>Name:</strong> {{ $user->name }}</p>
                    <p><strong>Phone:</strong> {{ $phone }}</p>
                    <p><strong>Address:</strong> {{ $address }}</p>
                    @endif
                </div>
            </div>
            <table id="myTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Discount</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody id="tableList">
                    @php
                        $total_price = 0;
                    @endphp
                    @foreach ($details as $key => $item)
                        @if ($store->id == $item->store_id)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $item->service_data ? json_decode(json_decode($item->service_data)->name)->en : null }}</td>
                                <td>{{ $item->qty }}</td>
                                <td>${{ number_format($item->price, 2) }}</td>
                                <td>${{ number_format($item->discount, 2) }}</td>
                                <td>${{ number_format($item->total_price, 2) }}</td>
                            </tr>
                            @php
                                $total_price += $item->total_price;
                            @endphp
                        @endif
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td rowspan="4" colspan="4">
                        <p style="font-size: 12px; max-width: 350px;">
                            Note: <br>
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. <br>
                        </p>
                        </td>
                    </tr>
                    <tr>
                        <td class="total" style="font-weight: 600;">Subtotal:</td>
                        <td>${{ number_format($total_price, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="total" style="font-weight: 600;">Delivery Fee:</ត>
                        <td>${{ number_format($delivery_fee, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="total" style="font-weight: 600;">Total:</td>
                        <td>${{ number_format(($total_price + $delivery_fee), 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endforeach
</div>
