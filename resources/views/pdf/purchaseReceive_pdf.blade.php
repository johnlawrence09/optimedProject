<!DOCTYPE html>
<html lang="en" style="margin: 0; padding: 0; box-sizing: border-box;">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Purchase Order</title>
   <style>
        th {
            font-family: 'Roboto', sans-serif;
        },

      th:nth-child(-n + 3),
      td:nth-child(-n + 3) {
	   width: 8%;
      },

      /* th:first-child, td:first-child {
	   width: -5px;
      } */
   </style>
</head>
<body style="font-family: 'Roboto', sans-serif;">
   <main style="padding: 10px; width: 550; margin: 5px auto;">
      <header style="margin-bottom: 75px;" >
         <img style="width: 200px; margin-left:-15px;" src="images/optimed-logo.jpg" alt="">

         <p style="font-size: 12px; margin: 1px; color: #495057;">Optimedevices Medical Equipment and Supplies Trading</p>
         <p style="font-size: 12px; margin: 1px; color: #495057;">908-002-441-000</p>
         <p style="font-size: 12px; margin: 1px; color: #495057;">#632 TBC Road Angeles Heights Subd. Brgy. Bagong Bayan</p>
         <p style="font-size: 12px; margin: 1px; color: #495057;">II-A (POB.) San Pablo City, Laguna 4000</p>
         <p style="font-size: 12px; margin: 1px; color: #495057;">Mobile Nos.: 09688866081/09688572308</p>
      </header>
      <p style="letter-spacing: 5px; text-transform:uppercase; text-align:center; margin-bottom:32px; ">Purchase Receipt </p>
      <main>
         <div style="float: left; margin-right: 250px;">
            <p style="font-size: 12px; margin:0; font-weight: 400;"><strong style="margin-right: 30px;">Name:</strong>{{$purchase['supplier_name']}}</p>
            <p style="font-size: 12px; margin:0; font-weight: 400;"><strong style="margin-right: 26px;">Phone:</strong>{{$purchase['supplier_phone']}}</p>
            <p style="font-size: 12px; margin:0; font-weight: 400;"><strong style="margin-right: 16px;">Address:</strong>{{$purchase['supplier_adr']}}<</p>
            <p style="font-size: 12px; margin:0; font-weight: 400;"><strong style="margin-right: 32px;">Email:</strong>{{$purchase['supplier_email']}}</p>
         </div>
         <div>
            <p style="font-size: 12px; margin:0; font-weight: 400;"><strong style="margin-right: 85px;">Date:</strong>{{$purchase['date']}}</p>
            <p style="font-size: 12px; margin:0; font-weight: 400;"><strong style="margin-right: 30px;">Reference no.:</strong>{{$purchase['Ref']}}</p>
            <p style="font-size: 12px; margin:0; font-weight: 400;"><strong style="margin-right: 72px;">Status:</strong>{{$purchase['statut']}}</p>
            <p style="font-size: 12px; margin:0; font-weight: 400;"><strong style="margin-right: 20px;">Payment Status:</strong>{{$purchase['payment_status']}}</p>
         </div>
      </main>

      <table style="margin-top: 32px; font-size: 12px; border-collapse: collapse; width: 100%; ">
         <tr>
           <th style="border-right:1px solid #343a40; text-align: left; padding: 8px; font-size: 12px; background-color: #4dabf7; color: black; text-transform:uppercase" >PRODUCT</th>
           <th style="border-right:1px solid #343a40; text-align: left; padding: 8px; font-size: 12px; background-color: #4dabf7; color: black; text-transform:uppercase" >EXP DATE</th>
           {{-- <th style="border-right:1px solid #343a40; text-align: left; padding: 8px; font-size: 12px; background-color: #4dabf7; color: black; text-transform:uppercase" >NET UNIT COST</th> --}}
           <th style="border-right:1px solid #343a40; text-align: left; padding: 8px; font-size: 12px; background-color: #4dabf7; color: black; text-transform:uppercase" >QUANTITY</th>
           <th style="border-right:1px solid #343a40; text-align: left; padding: 8px; font-size: 12px; background-color: #4dabf7; color: black; text-transform:uppercase" >UNIT COST</th>
           <th style="border-right:1px solid #343a40; text-align: left; padding: 8px; font-size: 12px; background-color: #4dabf7; color: black; text-transform:uppercase" >DISCOUNT</th>
           <th style="border-right:1px solid #343a40; text-align: left; padding: 8px; font-size: 12px; background-color: #4dabf7; color: black; text-transform:uppercase" >TAX</th>
           <th style="text-align: left; padding: 8px; font-size: 12px; background-color: #4dabf7; color: black; text-transform:uppercase" >TOTAL</th>
         </tr>
         <tr>
         <tbody>
            @foreach ($details as $detail)
             <tr>
                 <td style="border: 1px solid #343a40; border-top:none; text-align: left; padding: 8px; font-size: 12px;"><span>{{$detail['code']}} ({{$detail['name']}})</span>
                    @if($detail['is_imei'] && $detail['imei_number'] !==null)
                       <p>IMEI/SN : {{$detail['imei_number']}}</p>
                    @endif
                </td>
                @if($detail['expiration_date'] !== null)
                <td style="border: 1px solid #343a40; border-top:none; text-align: left; padding: 8px; font-size: 12px;">{{$detail['expiration_date']}} </td>
                @else
                <td style="border: 1px solid #343a40; border-top:none; text-align: left; padding: 8px; font-size: 12px;">n/a</td>
                @endif
                 {{-- <td style="border: 1px solid #343a40; border-top:none; text-align: left; padding: 8px; font-size: 12px;">{{$detail['cost']}} </td> --}}
                 <td style="border: 1px solid #343a40; border-top:none; text-align: left; padding: 8px; font-size: 12px;">{{$detail['quantity']}}/{{$detail['unit_purchase']}}</td>
                 <td style="border: 1px solid #343a40; border-top:none; text-align: left; padding: 8px; font-size: 12px;">{{$detail['cost']}} </td>
                 <td style="border: 1px solid #343a40; border-top:none; text-align: left; padding: 8px; font-size: 12px;">{{$detail['DiscountNet']}}</td>
                 <td style="border: 1px solid #343a40; border-top:none; text-align: left; padding: 8px; font-size: 12px;">{{$detail['taxe']}}</td>
                 <td style="border: 1px solid #343a40; border-top:none; text-align: left; padding: 8px; font-size: 12px;">{{$detail['total']}}</td>
             </tr>
             @endforeach
         </tbody>
         </tr>
       </table>
       <main style="width: 550; margin: 0 10px auto auto;" >
        <table style="margin-top: 32px; font-size: 12px; border-collapse: collapse; width: 30%; float:right">
            <tr>
                <td style="border: 1px solid #343a40; text-align: left; padding: 5px; font-size: 12px;">Order Tax</td>
                <td style="border: 1px solid #343a40; text-align: left; padding: 5px; font-size: 12px;">{{$purchase['TaxNet']}} </td>
             </tr>
             <tr>
                <td style="border: 1px solid #343a40; border-top:none; text-align: left; padding: 5px; font-size: 12px;">Discount</td>
                <td style="border: 1px solid #343a40; border-top:none; text-align: left; padding: 5px; font-size: 12px;">{{$purchase['discount']}} </td>
             </tr>
             <tr>
                <td style="border: 1px solid #343a40; border-top:none; text-align: left; padding: 5px; font-size: 12px;">Shipping</td>
                <td style="border: 1px solid #343a40; border-top:none; text-align: left; padding: 5px; font-size: 12px;">{{$purchase['shipping']}} </td>
             </tr>
             <tr>
                <td style="border: 1px solid #343a40; border-top:none; text-align: left; padding: 5px; font-size: 12px;">Total</td>
                <td style="border: 1px solid #343a40; border-top:none; text-align: left; padding: 5px; font-size: 12px;">{{$symbol}} {{$purchase['GrandTotal']}} </td>
             </tr>

             <tr>
                <td style="border: 1px solid #343a40; border-top:none; text-align: left; padding: 5px; font-size: 12px;">Paid Amount</td>
                <td style="border: 1px solid #343a40; border-top:none; text-align: left; padding: 5px; font-size: 12px;">{{$symbol}} {{$purchase['paid_amount']}} </td>
             </tr>

             <tr>
                <td style="border: 1px solid #343a40; border-top:none; text-align: left; padding: 5px; font-size: 12px;">Due</td>
                <td style="border: 1px solid #343a40; border-top:none; text-align: left; padding: 5px; font-size: 12px;">{{$symbol}} {{$purchase['due']}} </td>
             </tr>
        </table>
   </main>
   </main>


</body>
</html>
