<template>
  <div class="receipt-generator">
    <button @click="generateReceipt" :disabled="loading">Generate Receipt</button>
    <div v-if="receipt" class="receipt-preview">
      <h3>Order Receipt</h3>
      <p><strong>Receipt Number:</strong> {{ receipt.receipt_number }}</p>
      <p><strong>School:</strong> {{ receipt.receipt_data.school_name }}</p>
      <p><strong>Course Type:</strong> {{ receipt.receipt_data.course_type }}</p>
      <p><strong>Certificate Count:</strong> {{ receipt.receipt_data.certificate_count }}</p>
      <p><strong>Unit Price:</strong> ${{ receipt.receipt_data.unit_price }}</p>
      <p><strong>Total Amount:</strong> ${{ receipt.receipt_data.total_amount }}</p>
      <hr />
      <p><strong>Mail to:</strong><br />{{ receipt.receipt_data.florida_mailing_address }}</p>
      <button @click="printReceipt">Print Receipt</button>
    </div>
  </div>
</template>

<script>
export default {
  props: ['orderId'],
  data() {
    return {
      receipt: null,
      loading: false
    };
  },
  methods: {
    async generateReceipt() {
      this.loading = true;
      try {
        const response = await fetch(`/api/dicds-orders/${this.orderId}/generate-receipt`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' }
        });
        this.receipt = await response.json();
      } finally {
        this.loading = false;
      }
    },
    async printReceipt() {
      window.print();
      await fetch(`/api/dicds-orders/${this.orderId}/mark-printed`, { method: 'POST' });
    }
  }
};
</script>
