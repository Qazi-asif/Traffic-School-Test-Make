<template>
  <div class="user-profile">
    <h2>User Profile</h2>
    <div v-if="user" class="profile-details">
      <div class="profile-field">
        <label>Name:</label>
        <span>{{ user.first_name }} {{ user.last_name }}</span>
      </div>
      <div class="profile-field">
        <label>Email:</label>
        <span>{{ user.email }}</span>
      </div>
      <div class="profile-field">
        <label>Role:</label>
        <span>{{ user.role.name }}</span>
      </div>
      <div class="profile-field">
        <label>Phone:</label>
        <span>{{ user.phone || 'Not provided' }}</span>
      </div>
      <div class="profile-field">
        <label>Address:</label>
        <span>{{ user.address || 'Not provided' }}</span>
      </div>
      <div class="profile-field">
        <label>Driver License:</label>
        <span>{{ user.driver_license || 'Not provided' }}</span>
      </div>
      <div class="profile-field">
        <label>Status:</label>
        <span class="status" :class="user.status">{{ user.status }}</span>
      </div>
      <div class="profile-field">
        <label>Member Since:</label>
        <span>{{ formatDate(user.created_at) }}</span>
      </div>
    </div>
    <div class="profile-actions">
      <button @click="editProfile">Edit Profile</button>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'UserProfile',
  props: ['userId'],
  data() {
    return {
      user: null
    }
  },
  async mounted() {
    await this.fetchUser()
  },
  methods: {
    async fetchUser() {
      try {
        const response = await axios.get(`/api/users/${this.userId}`)
        this.user = response.data
      } catch (error) {
        console.error('Failed to fetch user')
      }
    },
    editProfile() {
      this.$router.push(`/users/${this.userId}/edit`)
    },
    formatDate(date) {
      return new Date(date).toLocaleDateString()
    }
  }
}
</script>

<style scoped>
.profile-field {
  margin-bottom: 1rem;
}
.profile-field label {
  font-weight: bold;
  margin-right: 0.5rem;
}
.status.active {
  color: green;
}
.status.inactive {
  color: red;
}
</style>
