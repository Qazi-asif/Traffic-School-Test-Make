<template>
  <div class="dicds-user-role-admin">
    <div class="admin-header">
      <h2>User Role Administration</h2>
      <p>Search and manage user accounts and roles</p>
    </div>

    <div class="search-filters">
      <div class="row">
        <div class="col-md-4">
          <label class="form-label">Filter by Status:</label>
          <select v-model="filters.status" @change="loadUsers" class="form-control">
            <option value="">All Users</option>
            <option value="active">Active</option>
            <option value="pending">Pending</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Search:</label>
          <input v-model="filters.search" @input="loadUsers" 
                 placeholder="Search by name or email" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">&nbsp;</label>
          <button @click="loadUsers" class="btn btn-primary w-100">
            <i class="fas fa-search"></i> Search
          </button>
        </div>
      </div>
    </div>

    <div class="users-table">
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Status</th>
              <th>Role</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in users" :key="user.id">
              <td>{{ user.name }}</td>
              <td>{{ user.email }}</td>
              <td>
                <span :class="getStatusClass(user.status)" class="badge">
                  {{ user.status }}
                </span>
              </td>
              <td>{{ user.role?.name || 'No Role' }}</td>
              <td>
                <div class="btn-group">
                  <button @click="updateUserStatus(user)" class="btn btn-sm btn-outline-primary">
                    Update Status
                  </button>
                  <button @click="resetPassword(user)" class="btn btn-sm btn-outline-warning">
                    Reset Password
                  </button>
                  <button @click="updateRole(user)" class="btn btn-sm btn-outline-info">
                    Update Role
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Update Status Modal -->
    <div v-if="showStatusModal" class="modal-overlay" @click="closeModals">
      <div class="modal-content" @click.stop>
        <h4>Update User Status</h4>
        <div class="form-group">
          <label>New Status:</label>
          <select v-model="selectedUser.newStatus" class="form-control">
            <option value="active">Active</option>
            <option value="pending">Pending</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        <div class="form-check">
          <input v-model="sendEmailNotification" class="form-check-input" type="checkbox">
          <label class="form-check-label">Send email notification</label>
        </div>
        <div class="modal-actions">
          <button @click="confirmStatusUpdate" class="btn btn-primary">Update</button>
          <button @click="closeModals" class="btn btn-secondary">Cancel</button>
        </div>
      </div>
    </div>

    <!-- Reset Password Modal -->
    <div v-if="showPasswordModal" class="modal-overlay" @click="closeModals">
      <div class="modal-content" @click.stop>
        <h4>Reset Password</h4>
        <p>Generate a temporary password for {{ selectedUser?.name }}?</p>
        <div v-if="tempPassword" class="alert alert-success">
          <strong>Temporary Password:</strong> {{ tempPassword }}
          <br><small>Please provide this to the user securely.</small>
        </div>
        <div class="modal-actions">
          <button @click="confirmPasswordReset" class="btn btn-warning">Generate Password</button>
          <button @click="closeModals" class="btn btn-secondary">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DicdsUserRoleAdmin',
  data() {
    return {
      users: [],
      filters: {
        status: '',
        search: ''
      },
      showStatusModal: false,
      showPasswordModal: false,
      selectedUser: null,
      sendEmailNotification: true,
      tempPassword: null
    }
  },
  mounted() {
    this.loadUsers()
  },
  methods: {
    async loadUsers() {
      try {
        const response = await axios.get('/api/dicds/user-management/users', {
          params: this.filters
        })
        this.users = response.data.data || response.data
      } catch (error) {
        console.error('Error loading users:', error)
      }
    },
    updateUserStatus(user) {
      this.selectedUser = { ...user, newStatus: user.status }
      this.showStatusModal = true
    },
    resetPassword(user) {
      this.selectedUser = user
      this.tempPassword = null
      this.showPasswordModal = true
    },
    updateRole(user) {
      // Role update functionality
      alert(`Role update for ${user.name} - Feature coming soon`)
    },
    async confirmStatusUpdate() {
      try {
        await axios.put(`/api/dicds/user-management/users/${this.selectedUser.id}/status`, {
          status: this.selectedUser.newStatus,
          send_email: this.sendEmailNotification
        })
        this.loadUsers()
        this.closeModals()
      } catch (error) {
        console.error('Error updating status:', error)
      }
    },
    async confirmPasswordReset() {
      try {
        const response = await axios.post(`/api/dicds/user-management/users/${this.selectedUser.id}/reset-password`)
        this.tempPassword = response.data.temp_password
      } catch (error) {
        console.error('Error resetting password:', error)
      }
    },
    closeModals() {
      this.showStatusModal = false
      this.showPasswordModal = false
      this.selectedUser = null
      this.tempPassword = null
    },
    getStatusClass(status) {
      const classes = {
        active: 'bg-success',
        pending: 'bg-warning',
        inactive: 'bg-secondary'
      }
      return classes[status] || 'bg-secondary'
    }
  }
}
</script>

<style scoped>
.dicds-user-role-admin {
  padding: 2rem;
}

.admin-header {
  margin-bottom: 2rem;
  text-align: center;
}

.search-filters {
  background: #f8f9fa;
  padding: 1.5rem;
  border-radius: 8px;
  margin-bottom: 2rem;
}

.users-table {
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  max-width: 500px;
  width: 90%;
}

.modal-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 1.5rem;
}

.btn-group .btn {
  margin-right: 0.25rem;
}
</style>
