<template>
  <div class="register-form">
    <h2>Register</h2>
    <form @submit.prevent="register">
      <div class="form-group">
        <label>First Name:</label>
        <input v-model="form.first_name" type="text" required />
      </div>
      <div class="form-group">
        <label>Last Name:</label>
        <input v-model="form.last_name" type="text" required />
      </div>
      <div class="form-group">
        <label>Email:</label>
        <input v-model="form.email" type="email" required />
      </div>
      <div class="form-group">
        <label>Password:</label>
        <input v-model="form.password" type="password" required />
      </div>
      <div class="form-group">
        <label>Confirm Password:</label>
        <input v-model="form.password_confirmation" type="password" required />
      </div>
      <div class="form-group">
        <label>Role:</label>
        <select v-model="form.role_id" required>
          <option value="">Select Role</option>
          <option v-for="role in roles" :key="role.id" :value="role.id">
            {{ role.name }}
          </option>
        </select>
      </div>
      <button type="submit" :disabled="loading">
        {{ loading ? 'Registering...' : 'Register' }}
      </button>
    </form>
    <div v-if="errors" class="errors">
      <div v-for="(error, field) in errors" :key="field">
        {{ error[0] }}
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'Register',
  data() {
    return {
      form: {
        first_name: '',
        last_name: '',
        email: '',
        password: '',
        password_confirmation: '',
        role_id: ''
      },
      roles: [],
      loading: false,
      errors: null
    }
  },
  async mounted() {
    await this.fetchRoles()
  },
  methods: {
    async fetchRoles() {
      try {
        const response = await axios.get('/api/roles')
        this.roles = response.data
      } catch (error) {
        console.error('Failed to fetch roles')
      }
    },
    async register() {
      this.loading = true
      this.errors = null
      
      try {
        const response = await axios.post('/api/register', this.form)
        localStorage.setItem('token', response.data.token)
        localStorage.setItem('user', JSON.stringify(response.data.user))
        this.$router.push('/dashboard')
      } catch (error) {
        this.errors = error.response?.data?.errors
      } finally {
        this.loading = false
      }
    }
  }
}
</script>
