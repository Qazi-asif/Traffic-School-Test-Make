<template>
  <div class="status-tracker">
    <div class="status-badge" :class="order.status">{{ order.status.toUpperCase() }}</div>
    <div v-if="approval" class="approval-details">
      <p v-if="approval.approved_by_florida">
        <strong>Florida Approved:</strong> {{ approval.florida_approval_date }}
      </p>
      <p v-if="approval.florida_reference_number">
        <strong>Reference:</strong> {{ approval.florida_reference_number }}
      </p>
      <p v-if="approval.certificate_numbers_released">
        <strong>Certificates Released:</strong> {{ approval.release_date }}
      </p>
    </div>
    <button v-if="canUpdateApproval" @click="showApprovalForm = true">Update Approval</button>
    <form v-if="showApprovalForm" @submit.prevent="updateApproval">
      <input v-model="form.approved_by_florida" type="checkbox" /> Approved by Florida<br />
      <input v-model="form.florida_approval_date" type="date" placeholder="Approval Date" /><br />
      <input v-model="form.florida_reference_number" type="text" placeholder="Reference Number" /><br />
      <input v-model="form.certificate_numbers_released" type="checkbox" /> Certificates Released<br />
      <button type="submit">Update</button>
    </form>
  </div>
</template>

<script>
export default {
  props: ['order', 'canUpdateApproval'],
  data() {
    return {
      approval: null,
      showApprovalForm: false,
      form: {
        approved_by_florida: false,
        florida_approval_date: null,
        florida_reference_number: '',
        certificate_numbers_released: false
      }
    };
  },
  methods: {
    async updateApproval() {
      const response = await fetch(`/api/dicds-orders/${this.order.id}/update-approval`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(this.form)
      });
      this.approval = await response.json();
      this.showApprovalForm = false;
      this.$emit('updated');
    }
  }
};
</script>
