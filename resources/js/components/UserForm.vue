<template>
  <div class="user-form">
    <h2>{{ isEdit ? 'Edit User' : 'Create User' }}</h2>
    <form @submit.prevent="saveUser">
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
        <input v-model="form.password" type="password" :required="!isEdit" />
      </div>
      <div class="form-group">
        <label>Phone:</label>
        <input v-model="form.phone" type="text" />
      </div>
      <div class="form-group">
        <label>Address:</label>
        <textarea v-model="form.address"></textarea>
      </div>
      <div class="form-group">
        <label>Driver License:</label>
        <input v-model="form.driver_license" type="text" />
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
      <div class="form-group">
        <label>Status:</label>
        <select v-model="form.status">
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
      </div>
      <button type="submit" :disabled="loading">
        {{ loading ? 'Saving...' : 'Save' }}
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
  name: 'UserForm',
  props: ['userId'],
  data() {
    return {
      form: {
        first_name: '',
        last_name: '',
        email: '',
        password: '',
        phone: '',
        address: '',
        driver_license: '',
        role_id: '',
        status: 'active'
      },
      roles: [],
      loading: false,
      errors: null
    }
  },
  computed: {
    isEdit() {
      return !!this.userId
    }
  },
  async mounted() {
    await this.fetchRoles()
    if (this.isEdit) {
      await this.fetchUser()
    }
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
    async fetchUser() {
      try {
        const response = await axios.get(`/api/users/${this.userId}`)
        this.form = { ...response.data, password: '' }
      } catch (error) {
        console.error('Failed to fetch user')
      }
    },
    async saveUser() {
      this.loading = true
      this.errors = null
      
      try {
        if (this.isEdit) {
          await axios.put(`/api/users/${this.userId}`, this.form)
        } else {
          await axios.post('/api/users', this.form)
        }
        this.$router.push('/users')
      } catch (error) {
        this.errors = error.response?.data?.errors
      } finally {
        this.loading = false
      }
    }
  }
}
</script>
