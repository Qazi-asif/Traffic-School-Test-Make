<template>
  <div v-if="show" class="modal">
    <div class="modal-content">
      <h2>Amend Order Certificate Count</h2>
      <form @submit.prevent="submitAmendment">
        <div class="form-group">
          <label>Current Certificate Count: {{ order.certificate_count }}</label>
        </div>
        <div class="form-group">
          <label>New Certificate Count</label>
          <input v-model.number="form.amended_certificate_count" type="number" min="1" required />
        </div>
        <div class="form-group">
          <label>Amendment Reason (min 10 characters)</label>
          <textarea v-model="form.amendment_reason" minlength="10" required></textarea>
        </div>
        <div class="form-actions">
          <button type="submit" :disabled="loading">Submit Amendment</button>
          <button type="button" @click="$emit('close')">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  props: ['show', 'order'],
  emits: ['close', 'amended'],
  data() {
    return {
      form: {
        amended_certificate_count: null,
        amendment_reason: ''
      },
      loading: false
    };
  },
  methods: {
    async submitAmendment() {
      this.loading = true;
      try {
        const response = await fetch(`/api/dicds-orders/${this.order.id}/amend`, {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(this.form)
        });
        if (response.ok) {
          this.$emit('amended');
          this.$emit('close');
        }
      } finally {
        this.loading = false;
      }
    }
  }
};
</script>
