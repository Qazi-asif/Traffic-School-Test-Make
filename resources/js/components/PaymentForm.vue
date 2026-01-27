<template>
  <div class="payment-form">
    <div class="card">
      <div class="card-header">
        <h5>Complete Your Purchase</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-8">
            <form @submit.prevent="processPayment">
              <div class="mb-3">
                <label>Billing Name</label>
                <input v-model="billingInfo.name" type="text" class="form-control" required>
              </div>
              
              <div class="mb-3">
                <label>Billing Email</label>
                <input v-model="billingInfo.email" type="email" class="form-control" required>
              </div>
              
              <div class="mb-3">
                <label>Payment Method</label>
                <div class="form-check">
                  <input v-model="paymentMethod" value="card" type="radio" class="form-check-input" id="card">
                  <label for="card" class="form-check-label">Credit/Debit Card</label>
                </div>
                <div class="form-check">
                  <input v-model="paymentMethod" value="paypal" type="radio" class="form-check-input" id="paypal">
                  <label for="paypal" class="form-check-label">PayPal</label>
                </div>
              </div>
              
              <div v-if="paymentMethod === 'card'" class="mb-3">
                <label>Card Information</label>
                <div class="card-element p-3 border rounded">
                  <!-- Stripe Elements would be mounted here -->
                  <input type="text" placeholder="1234 5678 9012 3456" class="form-control mb-2">
                  <div class="row">
                    <div class="col-6">
                      <input type="text" placeholder="MM/YY" class="form-control">
                    </div>
                    <div class="col-6">
                      <input type="text" placeholder="CVC" class="form-control">
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="mb-3">
                <h6>Billing Address</h6>
                <div class="row">
                  <div class="col-md-6">
                    <input v-model="billingInfo.address.street" type="text" placeholder="Street Address" class="form-control mb-2">
                  </div>
                  <div class="col-md-6">
                    <input v-model="billingInfo.address.city" type="text" placeholder="City" class="form-control mb-2">
                  </div>
                  <div class="col-md-6">
                    <input v-model="billingInfo.address.state" type="text" placeholder="State" class="form-control mb-2">
                  </div>
                  <div class="col-md-6">
                    <input v-model="billingInfo.address.zip" type="text" placeholder="ZIP Code" class="form-control mb-2">
                  </div>
                </div>
              </div>
              
              <button type="submit" class="btn btn-primary btn-lg w-100" :disabled="processing">
                <span v-if="processing">Processing...</span>
                <span v-else>Pay ${{ coursePrice }}</span>
              </button>
            </form>
          </div>
          
          <div class="col-md-4">
            <div class="order-summary">
              <h6>Order Summary</h6>
              <div class="d-flex justify-content-between">
                <span>{{ courseName }}</span>
                <span>${{ coursePrice }}</span>
              </div>
              <hr>
              <div class="d-flex justify-content-between">
                <span>Tax</span>
                <span>$0.00</span>
              </div>
              <hr>
              <div class="d-flex justify-content-between fw-bold">
                <span>Total</span>
                <span>${{ coursePrice }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: ['courseId', 'courseName', 'coursePrice'],
  data() {
    return {
      paymentMethod: 'card',
      processing: false,
      billingInfo: {
        name: '',
        email: '',
        address: {
          street: '',
          city: '',
          state: '',
          zip: ''
        }
      }
    }
  },
  methods: {
    async processPayment() {
      this.processing = true;
      
      try {
        // Create payment intent
        const intentResponse = await fetch('/api/payments/create-intent', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
          },
          credentials: 'same-origin',
          body: JSON.stringify({
            course_id: this.courseId,
            payment_method: this.paymentMethod,
            billing_name: this.billingInfo.name,
            billing_email: this.billingInfo.email
          })
        });
        
        if (!intentResponse.ok) {
          throw new Error('Failed to create payment intent');
        }
        
        const intent = await intentResponse.json();
        
        // Simulate payment processing
        await new Promise(resolve => setTimeout(resolve, 2000));
        
        // Confirm payment
        const confirmResponse = await fetch('/api/payments/confirm', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
          },
          credentials: 'same-origin',
          body: JSON.stringify({
            intent_id: intent.intent_id,
            course_id: this.courseId,
            billing_name: this.billingInfo.name,
            billing_email: this.billingInfo.email,
            billing_address: this.billingInfo.address
          })
        });
        
        if (confirmResponse.ok) {
          const result = await confirmResponse.json();
          this.$emit('payment-success', result);
          alert('Payment successful! You are now enrolled in the course.');
        } else {
          throw new Error('Payment failed');
        }
        
      } catch (error) {
        console.error('Payment error:', error);
        alert('Payment failed. Please try again.');
      } finally {
        this.processing = false;
      }
    }
  }
}
</script>

<style scoped>
.order-summary {
  background: #f8f9fa;
  padding: 20px;
  border-radius: 8px;
}

.card-element {
  background: white;
}
</style>
