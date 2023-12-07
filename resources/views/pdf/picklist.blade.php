<!DOCTYPE html>
<html lang="en" style="margin: 0; padding: 0; box-sizing: border-box;">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Picklist</title>
   <style>
        th {
            font-family: 'Roboto', sans-serif;s
        },

      th:nth-child(-n + 3),
      td:nth-child(-n + 3) {
	   width: 8%;
      },

      th:first-child, td:first-child {
	/* Adjust the width of the first column here */
	   width: -5px;
      }
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
      <p style="letter-spacing: 5px; text-transform:uppercase; text-align:center; margin-bottom:32px; ">Picklist</p>
      <main>
         <div style="float: left; margin-right: 250px;">
            <p style="font-size: 12px; margin:0; font-weight: 400;"><strong style="margin-right: 30px;">Name:</strong>{{$customer_info['name']}}</p>
            <p style="font-size: 12px; margin:0; font-weight: 400;"><strong style="margin-right: 26px;">Phone:</strong>{{$customer_info['phone']}}</p>
            <p style="font-size: 12px; margin:0; font-weight: 400;"><strong style="margin-right: 16px;">Address:</strong>{{$customer_info['address']}}<</p>
            <p style="font-size: 12px; margin:0; font-weight: 400;"><strong style="margin-right: 32px;">Email:</strong>{{$customer_info['email']}}</p>
         </div>
         <div>
            <p style="font-size: 12px; margin:0; font-weight: 400;"><strong style="margin-right: 85px;">Date:</strong>{{$sale_receipt_info['date']}}</p>
            <p style="font-size: 12px; margin:0; font-weight: 400;"><strong style="margin-right: 30px;">Reference no.:</strong>{{$sale_receipt_info['number']}}</p>
            <p style="font-size: 12px; margin:0; font-weight: 400;"><strong style="margin-right: 72px;">Status:</strong>{{$sale_receipt_info['status']}}</p>
            <p style="font-size: 12px; margin:0; font-weight: 400;"><strong style="margin-right: 45px;">Warehouse:</strong>{{$sale_receipt_info['warehouse']}}</p>
         </div>
      </main>

      <table style="margin-top: 32px; font-size: 12px; border-collapse: collapse; width: 100%; ">
         <tr>
           <th style="border-right:1px solid #343a40; text-align: left; padding: 8px; font-size: 12px; background-color: #4dabf7; color: black; text-transform:uppercase" >Released</th>
           <th style="border-right:1px solid #343a40; text-align: left; padding: 8px; font-size: 12px; background-color: #4dabf7; color: black; text-transform:uppercase" >Qty</th>
           <th style="border-right:1px solid #343a40; text-align: left; padding: 8px; font-size: 12px; background-color: #4dabf7; color: black; text-transform:uppercase" >UoM</th>
           <th style="border-right:1px solid #343a40; text-align: left; padding: 8px; font-size: 12px; background-color: #4dabf7; color: black; text-transform:uppercase" >Product Name</th>
           <th style="border-right:1px solid #343a40; text-align: left; padding: 8px; font-size: 12px; background-color: #4dabf7; color: black; text-transform:uppercase" >Expiration Date</th>
           <th style="text-align: left; padding: 8px; font-size: 12px; background-color: #4dabf7; color: black; text-transform:uppercase" >Location</th>
         </tr>
         <tr>
         <tbody>
            @foreach ($details as $detail)
             <tr>
                 <td style="border: 1px solid #343a40; border-top:none; text-align: left; padding: 8px; font-size: 12px;"></td>
                 <td style="border: 1px solid #343a40; border-top:none; text-align: left; padding: 8px; font-size: 12px;">{{$detail['quantity']}}</td>
                 <td style="border: 1px solid #343a40; border-top:none; text-align: left; padding: 8px; font-size: 12px;">{{$detail['unit_purchase']}}</td>
                 <td style="border: 1px solid #343a40; border-top:none; text-align: left; padding: 8px; font-size: 12px;">{{$detail['code']}} ({{$detail['name']}})</td>
                 <td style="border: 1px solid #343a40; border-top:none; text-align: left; padding: 8px; font-size: 12px;">{{$detail['expiration']}}</td>
                 <td style="border: 1px solid #343a40; border-top:none; text-align: left; padding: 8px; font-size: 12px;">{{$detail['location']}}</td>
             </tr>
             @endforeach
         </tbody>
         </tr>
       </table>
       <footer style="margin-top: 32px; margin-left: -2px">
         <div style="float: left; margin-right: 125px;">
            <p style="font-size: 12px; color: #495057;">Prepared by:</p>
            <p style="font-size: 12px; color: #495057; margin-top: 32px;">Signature Over Printed Name</p>
         </div>
         <div style="float: left; margin-right: 125px;">
            <p style="font-size: 12px; color: #495057;">Checked by:</p>
            <p style="font-size: 12px; color: #495057; margin-top: 32px;">Signature Over Printed Name</p>
         </div>
         <div style="float: left;">
            <p style="font-size: 12px; color: #495057;">Approved by:</p>
            <p style="font-size: 12px; color: #495057; margin-top: 32px;">Signature Over Printed Name</p>
         </div>
       </footer>

   </main>

</body>
</html>
