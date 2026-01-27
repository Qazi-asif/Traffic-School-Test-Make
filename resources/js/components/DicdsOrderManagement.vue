<template>
  <div class="dicds-orders">
    <div class="alert alert-info">Component is loading...</div>
    
    <div class="d-flex justify-content-between mb-3">
      <h2>Certificate Orders</h2>
      <button class="btn btn-primary">
        <i class="fas fa-plus"></i> New Order
      </button>
    </div>

    <div v-if="loading">Loading orders...</div>
    <div v-else-if="error" class="alert alert-danger">{{ error }}</div>
    
    <div v-else class="table-responsive">
      <p v-if="orders.length === 0">No orders found. Create your first order!</p>
      <table v-else class="table table-striped">
        <thead>
          <tr>
            <th>Order ID</th>
            <th>School</th>
            <th>Course</th>
            <th>Certificate Count</th>
            <th>Total Amount</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="order in orders" :key="order.id">
            <td>#{{ order.id }}</td>
            <td>{{ order.school?.school_name || 'N/A' }}</td>
            <td>{{ order.course?.course_name || 'N/A' }}</td>
            <td>{{ order.certificate_count }}</td>
            <td>${{ order.total_amount }}</td>
            <td>
              <span :class="'badge bg-' + (order.status === 'active' ? 'success' : 'warning')">
                {{ order.status }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      orders: [],
      loading: true,
      error: null
    };
  },
  async mounted() {
    console.log('DicdsOrderManagement component mounted');
    await this.loadOrders();
  },
  methods: {
    async loadOrders() {
      try {
        console.log('Fetching orders from /web/dicds-orders');
        const response = await fetch('/web/dicds-orders');
        console.log('Response status:', response.status);
        
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        this.orders = await response.json();
        console.log('Orders loaded:', this.orders);
      } catch (error) {
        console.error('Error loading orders:', error);
        this.error = error.message;
      } finally {
        this.loading = false;
      }
    }
  }
};
</script>
