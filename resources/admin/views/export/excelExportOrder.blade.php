

<table id="customers">
    <tr>
        <th style="font-weight: bold;text-align:center;width:100px">Order ID</th>
        <th style="font-weight: bold;text-align:center;width:150px">Order Date</th>
        <th style="font-weight: bold;text-align:center;width:150px">Shop</th>
        <th style="font-weight: bold;text-align:center;width:150px">Barber</th>
        <th style="font-weight: bold;text-align:center;width:150px">Customer Name</th>
        <th style="font-weight: bold;text-align:center;width:150px">Point Receiving</th>
        <th style="font-weight: bold;text-align:center;width:150px">Product's name</th>
        <th style="font-weight: bold;text-align:center;width:150px">Price of Product</th>
        <th style="font-weight: bold;text-align:center;width:150px">Product Discount</th>
        <th style="font-weight: bold;text-align:center;width:150px">Product Commission</th>
        <th style="font-weight: bold;text-align:center;width:150px">Service's name</th>
        <th style="font-weight: bold;text-align:center;width:150px">Price of Service</th>
        <th style="font-weight: bold;text-align:center;width:150px">Service Discount</th>
        <th style="font-weight: bold;text-align:center;width:150px">Service Commission</th>
        <th style="font-weight: bold;text-align:center;width:150px">Total Price</th>
        <th style="font-weight: bold;text-align:center;width:150px">Total Discount</th>
        <th style="font-weight: bold;text-align:center;width:150px">Total Commission</th>
        <th style="font-weight: bold;text-align:center;width:150px">Amount Customer Pay To us</th>
        <th style="font-weight: bold;text-align:center;width:150px">Pay Status</th>
        <th style="font-weight: bold;text-align:center;width:150px">Pay Date</th>
    </tr>
    @php
        $exportItems = $orderData ?? [];
    @endphp
    @foreach ($exportItems as $data)
    @php
        $order = $data->order ?? null;
    @endphp
    <tr>
      <td style="text-align:center">{{ $order?->invoice_number ?: ($order?->id ?: 'Null') }}</td>
      <td style="text-align:center">{{ $order?->order_date ? \Carbon\Carbon::parse($order->order_date)->format('Y-m-d H:i') : 'Null' }}</td>
      <td style="text-align:center">{{ $order?->shop?->name ?: 'Null' }}</td>
      <td style="text-align:center">{{ $order?->barber?->name ?: 'Null' }}</td>
      <td style="text-align:center">{{ $order?->customer?->name ?: ($order?->customer?->phone ?: 'Null') }}</td>
      <td style="text-align:center">{{ $data->point ?? 'Null' }}</td>
      <td style="text-align:center">{{ $data->product?->name ?: 'Null' }}</td>
      <td style="text-align:center">{{ $data->product?->price ? '$' . number_format($data->product->price, 2) : 'Null' }}</td>
      <td style="text-align:center">{{ $data->product_discount ? '$' . number_format($data->product_discount, 2) : 'Null' }}</td>
      <td style="text-align:center">{{ $data->product_commission ? '$' . number_format($data->product_commission, 2) : 'Null' }}</td>
      <td style="text-align:center">{{ $data->service?->name ?: 'Null' }}</td>
      <td style="text-align:center">{{ $data->service?->price ? '$' . number_format($data->service->price, 2) : '0$' }}</td>
      <td style="text-align:center">{{ $data->service_discount ? '$' . number_format($data->service_discount, 2) : '0$' }}</td>
      <td style="text-align:center">{{ $data->service_commission ? '$' . number_format($data->service_commission, 2) : '0$' }}</td>
      <td style="text-align:center">{{ $order?->total_price ? '$' . number_format($order->total_price, 2) : '0$' }}</td>
      <td style="text-align:center">{{ $order?->total_discount ? '$' . number_format($order->total_discount, 2) : '0$' }}</td>
      <td style="text-align:center">{{ $order?->total_commission ? '$' . number_format($order->total_commission, 2) : '0$' }}</td>
      <td style="text-align:center">{{ $order?->paid_amount ? '$' . number_format($order->paid_amount, 2) : '0$' }}</td>
      <td style="text-align:center">{{ $order?->payment_status ?: 'Null' }}</td>
      <td style="text-align:center">{{ $order?->payment_date ? \Carbon\Carbon::parse($order->payment_date)->format('Y-m-d H:i') : 'Null' }}</td>
    </tr>
    @endforeach
</table>
