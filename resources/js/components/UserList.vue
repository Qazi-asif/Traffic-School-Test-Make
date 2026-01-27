<template>
  <div class="user-list">
    <h2>Users Management</h2>
    
    <div class="filters">
      <input v-model="search" @input="fetchUsers" placeholder="Search users..." />
      <select v-model="roleFilter" @change="fetchUsers">
        <option value="">All Roles</option>
        <option v-for="role in roles" :key="role.id" :value="role.id">
          {{ role.name }}
        </option>
      </select>
      <select v-model="statusFilter" @change="fetchUsers">
        <option value="">All Status</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
      </select>
    </div>

    <table class="users-table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Role</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="user in users.data" :key="user.id">
          <td>{{ user.first_name }} {{ user.last_name }}</td>
          <td>{{ user.email }}</td>
          <td>{{ user.role.name }}</td>
          <td>{{ user.status }}</td>
          <td>
            <button @click="editUser(user)">Edit</button>
            <button @click="deleteUser(user.id)" class="danger">Delete</button>
          </td>
        </tr>
      </tbody>
    </table>

    <div class="pagination">
      <button @click="changePage(users.current_page - 1)" :disabled="users.current_page === 1">
        Previous
      </button>
      <span>Page {{ users.current_page }} of {{ users.last_page }}</span>
      <button @click="changePage(users.current_page + 1)" :disabled="users.current_page === users.last_page">
        Next
      </button>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'UserList',
  data() {
    return {
      users: { data: [] },
      roles: [],
      search: '',
      roleFilter: '',
      statusFilter: '',
      currentPage: 1
    }
  },
  async mounted() {
    await this.fetchRoles()
    await this.fetchUsers()
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
    async fetchUsers() {
      try {
        const params = {
          page: this.currentPage,
          search: this.search,
          role_id: this.roleFilter,
          status: this.statusFilter
        }
        const response = await axios.get('/api/users', { params })
        this.users = response.data
      } catch (error) {
        console.error('Failed to fetch users')
      }
    },
    async deleteUser(userId) {
      if (confirm('Are you sure you want to delete this user?')) {
        try {
          await axios.delete(`/api/users/${userId}`)
          await this.fetchUsers()
        } catch (error) {
          console.error('Failed to delete user')
        }
      }
    },
    editUser(user) {
      this.$router.push(`/users/${user.id}/edit`)
    },
    changePage(page) {
      this.currentPage = page
      this.fetchUsers()
    }
  }
}
</script>
