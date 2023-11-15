<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap"
      rel="stylesheet"
    />
    <style>
      html {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
      }
      body {
        font-family: "Roboto", sans-serif;
      }
      .main-container {
        /* background-color: #fa5252; */

        padding: 10px;
        width: 1200px;
        margin: 20px auto;
      }

      .card {
        border-bottom: 1px solid #343a40;
      }

      .main-header-text {
        display: grid;
        grid-template-columns: 1fr 1fr;
      }

      .header-text {
        text-align: center;
        margin: 0;
        text-transform: uppercase;
      }

      .text-container {
        display: flex;
        gap: 72px;
      }

      .address {
        display: flex;
        flex-direction: column;
        gap: 8px;
      }

      .sub-adress:first-child {
        margin-top: 16px;
      }

      .sub-adress {
        display: inline-block;
        margin: 0;
        font-weight: 700;
      }

      .customer-name {
        display: flex;
        gap: 10px;
      }

      .number {
        margin: 0;
      }

      .left-text {
        display: flex;
        flex-direction: column;
        gap: 50px;
      }

      .right-text {
        display: flex;
        flex-direction: column;
        margin-top: auto;
        gap: 20px;
        margin-left: 105px;
      }

      .so-number {
        display: flex;
        gap: 14px;
        border-bottom: 1px solid #343a40;
      }

      .order-date {
        display: flex;
        gap: 20px;
        border-bottom: 1px solid #343a40;
      }

      table {
        font-family: arial, sans-serif;
        border-collapse: collapse;
        width: 100%;
        margin-top: 20px;
      }

      td,
      th {
        border: 1px solid #dddddd;
        text-align: left;
        padding: 8px;
      }

      th {
        background-color: #d0ebff;
      }

      th:nth-child(-n + 3),
      td:nth-child(-n + 3) {
        width: 8%;
      }

      .text-item {
        display: flex;
        justify-content: space-between;
      }

      .main-text-footer {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
      }

      .text-footer {
        display: flex;
        flex-direction: column;
        gap: 20px;
        margin-left: 60px;
      }
    </style>
    <title>Document</title>
  </head>
  <body>

    <main class="main-container">
      <header>
        <img class="logo" src="img/optimed-logo.jpg.png" alt="optimed-logo" />
      </header>
      <div class="card">
        <h1 class="header-text">pickList</h1>
      </div>
      <div class="main-header-text">
        <div class="left-text">
          <div class="text-container">
            @foreach ($picklist as $item)
            <p>SHIP TO :</p>
            <div class="address">
              <p class="sub-adress">Hilton Tucson East-</p>
              <p class="sub-adress">7600 E Broadway Blvd</p>
              <p class="sub-adress">AZ 85710</p>
            </div>
          </div>
          <div class="customer-name">
            <p class="number">Customer name</p>
            <p class="number"><strong>: John Lawrence Lantin</strong></p>
          </div>
        </div>
        <div class="right-text">
          <div class="so-number">
            <p class="number">SO Number</p>
            <p class="number"><strong>: {{ $item->Ref }}</strong></p>
          </div>
          <div class="order-date">
            <p class="number">Order Date</p>
            <p class="number"><strong>:</strong></p>
          </div>
        </div>
      </div>
      <table>
        <tr>
          <th>Released</th>
          <th>Qty</th>
          <th>UoM</th>
          <th>Product Name</th>
          <th>Expiration Date</th>
          <th>Location</th>
        </tr>
       
        <tr>
          <tbody>
                <td></td>
                <td>$product->quantity</td>
                <td>$product->ShortName</td>
                <td>$product->name</td>
                <td>$product->expiration_date</td>
                <td>Germany</td>
          </tbody>
        </tr>
      </table>
          @endforeach
      <footer>
        <div class="text-item">
          <p>No. of ITEMS to Prepare : <strong>1279</strong></p>
          <p>Date of Print : <strong>05 Jul 2023</strong></p>
        </div>
        <div class="main-text-footer">
          <div class="text-footer">
            <p>Prepared by:</p>
            Signature Over Printed Name
          </div>
          <div class="text-footer">
            <p>Checked by:</p>
            Signature Over Printed Name
          </div>
          <div class="text-footer">
            <p>Approved/Noted by:</p>
            Signature Over Printed Name
          </div>
        </div>
      </footer>
    </main>
  </body>
</html>
