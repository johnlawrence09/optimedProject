<template>
    <div>
        <main class="main-container">
          <div class="invoice" id="print_Invoice">
            <div class="invoice-print">
              <header>
                <img class="logo" src="/images/optimed-logo.jpg.png" alt="optimed-logo" />
                <div class="btn">
                  <b-button class="btn-print" variant="warning" @click="print()">Print</b-button>
                </div>
                
              </header>
              
                <h1 class="header-text">PickList</h1>
                <hr/>
            
              <div class="main-header-text">
                <div class="left-text">
                  <div class="text-container">
                    <p>SHIP TO :</p>
                      <p class="sub-adress">{{ shipTo }}</p> 
                  </div>
                  <div class="customer-name">
                    <p class="number">Customer name</p>
                    <p class="number"><strong>: {{ client }}</strong></p>
                  </div>
                </div>
                <div class="right-text">
                  <div class="so-number">
                    <p class="number">SO Number</p>
                    <p class="number"><strong>: {{ referrence }}</strong></p>
                  </div>
                  <div class="order-date">
                    <p class="number">Order Date</p>
                    <p class="number"><strong>: {{ date }}</strong></p>
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
                <tr v-for="item in items " :key="item">
                  <td></td>
                  <td>{{ item.quantity }}</td>
                  <td>{{ item.unit }}</td>
                  <td>{{ item.product_name }}</td>
                  <td>{{ item.expiration_date }}</td>
                  <td>{{ item.warehouse }}</td>
                </tr>
              </table>
              <footer>
                <div class="text-item">
                  <p>Date of Print : <strong>{{ currentDate }}</strong></p>
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
            </div>
          </div>
    </main>
    </div>
        
  </template>
  
  <script>
    export default {
      data() {
        return {
          items: [],
          currentDate:'',
          client:'',
          date:'',
          referrence:'',
          shipTo:''
        }
      },

      methods: {
        getDetails() {
          let id = this.$route.params.id;
          axios.get(`print_picklist/${id}`).then((response) => {
            this.items = response.data.details;
            this.client = response.data.client;
            this.date = response.data.date;
            this.referrence = response.data.referrence;
            this.shipTo = response.data.Shipto;

            const dateNow = new Date();
            const year = dateNow.getFullYear();
            const monthIndex = dateNow.getMonth();
            const months = [
                            "January", "February", "March", "April",
                            "May", "June", "July", "August",
                            "September", "October", "November", "December"
                           ];
            const currentMonth = months[monthIndex];
            const day = dateNow.getDate();
            this.currentDate = `${currentMonth} ${day}, ${year}`;
          });
        },

        print() {
          let id = this.$route.params.id;
          window.open(`/print_picklist/${id}`, '_blank');
            // this.$htmlToPaper("print_Invoice");
        },
      },

      created() {
        this.getDetails();
      }
    }
  </script>
<style scoped>
html {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}
body {
  font-family: "Roboto", sans-serif;
  font-size: 32px;
}
.main-container {
  /* background-color: #fa5252; */

  padding: 10px;
  width: 1500px;
  margin: 10px auto ;
  font-family: "Roboto", sans-serif;
  font-size: 22px;
}



.main-header-text {
  display: grid;
  grid-template-columns: 1fr 1fr;
}

.header-text {
  text-align: center;
  text-transform: uppercase;
  font-weight: 700;
}

.text-container {
  display: flex;
  gap: 20px;
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
  margin-bottom: 30px;
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
  margin-bottom: 50px;
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
header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.btn {
  margin-left: auto;
}

.btn-print {
  width: 150px;
  font-size: 16px;
  font-weight: 600;
}


</style>