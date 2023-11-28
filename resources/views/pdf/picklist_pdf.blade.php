<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="utf-8">
      <title>Purchase _{{$sale_receipt_info['number']}}</title>
      <link rel="stylesheet" href="{{asset('/css/pdf_style.css')}}" media="all" />
   </head>

   <body>
      <header class="clearfix">
         <div id="logo">
         <img src="{{asset('/images/'.$setting['logo'])}}">
         </div>
         <div id="company">
            <div><strong> Date: </strong>{{$sale_receipt_info['date']}}</div>
            <div><strong> Number: </strong> {{$sale_receipt_info['number']}}</div>
            <div><strong> Status: </strong> {{$sale_receipt_info['status']}}</div>
            <div><strong> Payment Status: </strong> {{$sale_receipt_info['payment_status']}}</div>
            <div><strong> Warehouse: </strong> {{$sale_receipt_info['warehouse']}}</div>
         </div>
         <div id="Title-heading">
             Picklist4

             {{$sale_receipt_info['number']}}
         </div>
         </div>
      </header>
      <main>
         <div id="details" class="clearfix">
            <div id="client">
               <table class="table-sm">
                  <thead>
                     <tr>
                        <th class="desc">Customer Info</th>
                     </tr>
                  </thead>
                  <tbody>
                     <tr>
                        <td>
                           <div><strong>Name:</strong> {{$customer_info['name']}}</div>
                           <div><strong>Phone:</strong> {{$customer_info['phone']}}</div>
                           <div><strong>Address:</strong>   {{$customer_info['address']}}</div>
                           <div><strong>Email:</strong>  {{$customer_info['email']}}</div>
                        </td>
                     </tr>
                  </tbody>
               </table>
            </div>
            {{-- <div id="invoice">
               <table class="table-sm">
                  <thead>
                     <tr>
                        <th class="desc">Company Info</th>
                     </tr>
                  </thead>
                  <tbody>
                     <tr>
                        <td>
                           <div id="comp">{{$setting['CompanyName']}}</div>
                           <div><strong>Address:</strong>  {{$setting['CompanyAdress']}}</div>
                           <div><strong>Phone:</strong>  {{$setting['CompanyPhone']}}</div>
                           <div><strong>Email:</strong>  {{$setting['email']}}</div>
                        </td>
                     </tr>
                  </tbody>
               </table>
            </div> --}}
         </div>
         <div id="details_inv">
            <table class="table-sm">
               <thead>
                  <tr>
                     <th>PRODUCT</th>
                     <th>QUANTITY</th>
                     <th>PICK QTY</th>
                     <th>EXPIRY</th>
                     <th>LOCATION</th>
                  </tr>
               </thead>
               <tbody>
                  @foreach ($details as $detail)
                  <tr>
                     <td>
                        <span>{{$detail['code']}} ({{$detail['name']}})</span>
                           @if($detail['is_imei'] && $detail['imei_number'] !==null)
                              <p>IMEI/SN : {{$detail['imei_number']}}</p>
                           @endif
                     </td>
                     <td>{{$detail['quantity']}}/{{$detail['unit_purchase']}}</td>
                     <td></td>
                     <td>{{$detail['expiration']}} </td>
                     <td>{{$detail['location']}} </td>
                  </tr>
                  @endforeach
               </tbody>
            </table>
         </div>
         {{-- <div id="total">
            <table>
               <tr>
                  <td>Order Tax</td>
                  <td>{{$purchase['TaxNet']}} </td>
               </tr>
               <tr>
                  <td>Discount</td>
                  <td>{{$purchase['discount']}} </td>
               </tr>
               <tr>
                  <td>Shipping</td>
                  <td>{{$purchase['shipping']}} </td>
               </tr>
               <tr>
                  <td>Total</td>
                  <td>{{$symbol}} {{$purchase['GrandTotal']}} </td>
               </tr>

               <tr>
                  <td>Paid Amount</td>
                  <td>{{$symbol}} {{$purchase['paid_amount']}} </td>
               </tr>

               <tr>
                  <td>Due</td>
                  <td>{{$symbol}} {{$purchase['due']}} </td>
               </tr>
            </table>
         </div> --}}
         <div id="signature">Signature</div>
      </main>
   </body>
</html>
