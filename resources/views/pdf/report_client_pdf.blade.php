<!DOCTYPE html>
<html lang="en" style="margin: 0; padding: 0; box-sizing: border-box;">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Client  : {{$client['client_name']}}</title>
   <style>
        th {
            font-family: 'Roboto', sans-serif;
        },

      th:nth-child(-n + 3),
      td:nth-child(-n + 3) {
	   width: 10%;
      },

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
      <p style="letter-spacing: 5px; text-transform:uppercase; text-align:center; margin-bottom:32px; ">CLIENT: {{$client['client_name']}}</p>
      <main>
         <div style="margin-right: 250px;">
            <p style="font-size: 12px; margin:0; font-weight: 400;"><strong style="margin-right: 70px;">Name:</strong>{{$client['client_name']}}</p>
            <p style="font-size: 12px; margin:0; font-weight: 400;"><strong style="margin-right: 65px;">Phone:</strong>{{$client['phone']}}</p>
            <p style="font-size: 12px; margin:0; font-weight: 400;"><strong style="margin-right: 37px;">Total Sales:</strong>{{$client['total_sales']}}<</p>
            <p style="font-size: 12px; margin:0; font-weight: 400;"><strong style="margin-right: 23px;">Total Amount:</strong>{{$symbol}} {{$client['total_amount']}}</p>
            <p style="font-size: 12px; margin:0; font-weight: 400;"><strong style="margin-right: 43px;">Total Paid:</strong>{{$symbol}} {{$client['total_paid']}}</p>
            <p style="font-size: 12px; margin:0; font-weight: 400;"><strong style="margin-right: 78px;">Due:</strong>{{$symbol}} {{$client['due']}}</p>
         </div>
      </main>
      <div style="margin-top: 10px; display:inline-block; ">
        <p style="color:#252626; font-size:18px;">All Sales ( Unpaid/Partial)</p>
      </div>
      <table style="font-size: 12px; border-collapse: collapse; width: 100%; ">
         <tr>
           <th style="border-right:1px solid #343a40; text-align: left; padding: 8px; font-size: 12px; background-color: #4dabf7; color: black; text-transform:uppercase" >Date</th>
           <th style="border-right:1px solid #343a40; text-align: left; padding: 8px; font-size: 12px; background-color: #4dabf7; color: black; text-transform:uppercase" >Referrence</th>
           <th style="border-right:1px solid #343a40; text-align: left; padding: 8px; font-size: 12px; background-color: #4dabf7; color: black; text-transform:uppercase" >Paid</th>
           <th style="border-right:1px solid #343a40; text-align: left; padding: 8px; font-size: 12px; background-color: #4dabf7; color: black; text-transform:uppercase" >Due</th>
           <th style="border-right:1px solid #343a40; text-align: left; padding: 8px; font-size: 12px; background-color: #4dabf7; color: black; text-transform:uppercase" >Payment Status</th>
         </tr>
         <tr>
         <tbody>
             @foreach ($sales as $sale)
             <tr>
                 <td style="border: 1px solid #343a40; border-top:none; text-align: left; padding: 8px; font-size: 12px;">{{$sale['date']}}</td>
                 <td style="border: 1px solid #343a40; border-top:none; text-align: left; padding: 8px; font-size: 12px;">{{$sale['Ref']}}</td>
                 <td style="border: 1px solid #343a40; border-top:none; text-align: left; padding: 8px; font-size: 12px;">{{$symbol}} {{$sale['paid_amount']}}</td>
                 <td style="border: 1px solid #343a40; border-top:none; text-align: left; padding: 8px; font-size: 12px;">{{$symbol}} {{$sale['due']}}</td>
                 <td style="border: 1px solid #343a40; border-top:none; text-align: left; padding: 8px; font-size: 12px;">{{$sale['payment_status']}}</td>
             </tr>
             @endforeach
         </tbody>
         </tr>
       </table>
   </main>

</body>
</html>
