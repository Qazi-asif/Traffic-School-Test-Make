<template>
  <div class="inventory-snapshot">
    <h2>Certificate Inventory</h2>
    <table>
      <thead>
        <tr>
          <th>School</th>
          <th>Course Type</th>
          <th>Available Certificates</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in inventory" :key="item.id">
          <td>{{ item.school_name }}</td>
          <td>{{ item.course_type }}</td>
          <td>{{ item.available_count }}</td>
          <td>
            <span v-if="item.available_count < 10" class="low-inventory">Low</span>
            <span v-else class="normal-inventory">Normal</span>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
export default {
  data() {
    return {
      inventory: []
    };
  },
  async mounted() {
    await this.loadInventory();
  },
  methods: {
    async loadInventory() {
      const response = await fetch('/api/certificate-inventory');
      this.inventory = await response.json();
    }
  }
};
</script>
