<!DOCTYPE html>
<html>
<head>
    <title>FinCra Webhook</title>
</head>
<body>
    {{-- <h1>FinCra Webhook Data</h1>
    <p>Event Type: {{ $eventType }}</p> --}}
    <form id="payment-form" method="POST">
      <div class="form-group">
        <label for="name"> Name</label>
        <input type="text" id="name" required />
      </div>
      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" required />
      </div>
      <div class="form-group">
        <label for="phoneNumber">Phone Number</label>
        <input type="number" id="phoneNumber" required />
      </div>
      <div class="form-group">
        <label for="amount">Amount</label>
        <input type="tel" id="amount" required />
      </div>
      <div class="form-submit">
        <button type="submit" id="submit-button"> Pay </button>
      </div>
    </form>
    
    <!-- Display other relevant data from the webhook payload -->
</body>
</html>
<script src="https://unpkg.com/@fincra-engineering/checkout@2.2.0/dist/inline.min.js"></script>
<script>
const paymentForm = document.getElementById('payment-form');
     paymentForm.addEventListener("submit", payFincra, false);
function payFincra(e) {
     e.preventDefault();
       Fincra.initialize({
         key: "pk_NjQ1Zjk0MDRiYzgxODQ3YzQwZTQ0OGEwOjoxOTg1OTI=",
         amount: parseInt(document.getElementById("amount").value),
         currency: "NGN",
         customer: {
             name: document.getElementById("name").value,
             email: document.getElementById("email").value,
             phoneNumber: document.getElementById("phoneNumber").value,
           },
        //Kindly chose the bearer of the fees
        feeBearer: "business" || "customer",
 
         onClose: function (data) {
          console.log('failed'+data);
           alert("Transaction was not completed, window closed.");
         },
         onSuccess: function (data) {
            console.log("success"+data);
           const reference = data.reference;
    alert("Payment complete! Reference: " + reference);
         },
       });
     }
</script>

