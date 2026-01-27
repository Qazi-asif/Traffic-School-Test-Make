<template>
  <div class="create-course">
    <h2>Create New Course</h2>
    <form @submit.prevent="submitCourse">
      <div class="mb-3">
        <label>Title</label>
        <input v-model="form.title" type="text" class="form-control" required>
      </div>
      <div class="mb-3">
        <label>Description</label>
        <textarea v-model="form.description" class="form-control" required></textarea>
      </div>
      <div class="mb-3">
        <label>State</label>
        <select v-model="form.state_code" class="form-select" required>
          <option value="">Select State</option>
          <option value="FL">Florida</option>
          <option value="CA">California</option>
          <option value="TX">Texas</option>
          <option value="MO">Missouri</option>
          <option value="DE">Delaware</option>
        </select>
      </div>
      <div class="mb-3">
        <label>Minimum Pass Score (%)</label>
        <input v-model.number="form.min_pass_score" type="number" class="form-control" min="0" max="100" required>
      </div>
      <div class="mb-3">
        <label>Total Duration (minutes)</label>
        <input v-model.number="form.total_duration" type="number" class="form-control" required>
      </div>
      <div class="mb-3">
        <label>Price ($)</label>
        <input v-model.number="form.price" type="number" class="form-control" required>
      </div>
      <div class="mb-3">
        <label>Certificate Template</label>
        <input v-model="form.certificate_template" type="text" class="form-control">
      </div>
      <div class="mb-3">
        <label>Active</label>
        <input v-model="form.is_active" type="checkbox">
      </div>
      <button type="submit" class="btn btn-success" :disabled="loading">
        {{ loading ? 'Creating...' : 'Create Course' }}
      </button>
    </form>
    <div v-if="success" class="alert alert-success mt-3">
      Course created successfully!
    </div>
    <div v-if="error" class="alert alert-danger mt-3">
      {{ error }}
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      form: {
        title: '',
        description: '',
        state_code: '',
        min_pass_score: 0,
        total_duration: 0,
        price: 0,
        certificate_template: '',
        is_active: true
      },
      success: false,
      error: '',
      loading: false
    }
  },
  methods: {
    async submitCourse() {
      this.loading = true
      this.success = false
      this.error = ''
      
      try {
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        
        const headers = {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
        
        // Add CSRF token if available
        if (csrfToken) {
          headers['X-CSRF-TOKEN'] = csrfToken
        }
        
        const response = await fetch('/web/courses', {
          method: 'POST',
          headers: headers,
          credentials: 'same-origin',
          body: JSON.stringify(this.form)
        })
        
        if (!response.ok) {
          if (response.status === 401) {
            window.location.href = '/login'
            return
          }
          const data = await response.json()
          throw new Error(data.message || 'Failed to create course')
        }
        
        this.success = true
        this.form = {
          title: '',
          description: '',
          state_code: '',
          min_pass_score: 0,
          total_duration: 0,
          price: 0,
          certificate_template: '',
          is_active: true
        }
      } catch (err) {
        this.error = err.message
      } finally {
        this.loading = false
      }
    }
  }
}
</script>
