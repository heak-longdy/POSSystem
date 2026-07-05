

<table id="customers">
    <tr>
        <th style="font-weight: bold;text-align:center;width:100px">Booking ID</th>
        <th style="font-weight: bold;text-align:center;width:150px">Booking Date</th>
        <th style="font-weight: bold;text-align:center;width:150px">Shop</th>
        <th style="font-weight: bold;text-align:center;width:150px">Barber</th>
        <th style="font-weight: bold;text-align:center;width:150px">Customer Name</th>
        <th style="font-weight: bold;text-align:center;width:150px">Point Receving</th>
        <th style="font-weight: bold;text-align:center;width:150px">Product's name</th>
        <th style="font-weight: bold;text-align:center;width:150px">Price of Product</th>
        <th style="font-weight: bold;text-align:center;width:150px">Product Discount</th>
        <th style="font-weight: bold;text-align:center;width:150px">Product Commision</th>
        <th style="font-weight: bold;text-align:center;width:150px">Service's name</th>
        <th style="font-weight: bold;text-align:center;width:150px">Price of Service</th>
        <th style="font-weight: bold;text-align:center;width:150px">Service  Discount</th>
        <th style="font-weight: bold;text-align:center;width:150px">Service Comission</th>
        <th style="font-weight: bold;text-align:center;width:150px">Total Price</th>
        <th style="font-weight: bold;text-align:center;width:150px">Total Discount</th>
        <th style="font-weight: bold;text-align:center;width:150px">Total Commision</th>
        <th style="font-weight: bold;text-align:center;width:150px">Amount Customer Pay To us</th>
        <th style="font-weight: bold;text-align:center;width:150px">Pay Satus</th>
        <th style="font-weight: bold;text-align:center;width:150px">Pay Date</th>
    @foreach ($bookingData as $data)
    <tr>
      <td  style="text-align:center">{{isset($data->booking->id) ? $data->booking->id : 'Null'}}</td>
      <td  style="text-align:center">{{isset($data->booking->booking_date) ? $data->booking->booking_date : 'Null'}}</td>
      <td  style="text-align:center">{{isset($data->booking->barber->name) ? $data->booking->barber->name : 'Null'}}</td>
      <td  style="text-align:center">{{isset($data->booking->shop->name) ? $data->booking->shop->name : 'Null'}}</td>
      <td  style="text-align:center">{{isset($data->booking->customer->name) ? $data->booking->customer->name : 'Null'}}</td>
      <td  style="text-align:center">{{isset($data->point) ? $data->point : 'Null'}}</td>
      <td  style="text-align:center">{{isset($data->product->name) ? $data->product->name : 'Null'}}</td>
      <td  style="text-align:center">{{isset($data->product->price) ? $data->product->price : 'Null'}}</td>
      <td  style="text-align:center">{{isset($data->product->discount) ? $data->product->discount : 'Null'}}</td>
      <td  style="text-align:center">{{isset($data->product->commission) ? $data->product->commission : 'Null'}}</td>
      <td  style="text-align:center">{{isset($data->service->name) ? $data->service->name : 'Null'}}</td>
      <td  style="text-align:center">{{isset($data->service->price) ? $data->service->price : '0$'}}$</td>
      <td  style="text-align:center">{{isset($data->service->discount) ? $data->service->discount : '0$'}}</td>
      <td  style="text-align:center">{{isset($data->service->commission) ? $data->service->commission : '0$'}}</td>
      <td  style="text-align:center">{{isset($data->service->price) ? $data->service->price : '0$'}} </td>
      <td  style="text-align:center">{{isset($data->product->commission) ? $data->product->commission : '0$'}}</td>
      <td  style="text-align:center">{{isset($data->service->discount) ? $data->service->discount : '0$'}}</td>
      <td  style="text-align:center">{{isset($data->service->price) ? $data->service->price : '0$'}}$</td>
      <td  style="text-align:center">{{isset($data->booking->booking_date) ? $data->booking->payment_status : 'Null'}}</td>
      <td  style="text-align:center">{{isset($data->booking->booking_date) ? $data->booking->payment_date : 'Null'}}</td>
    </tr>

    @endforeach
    
   
  </table>
