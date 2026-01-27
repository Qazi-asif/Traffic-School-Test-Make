<template>
  <div class="enrollment-list">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3>Enrollments Management</h3>
      <button @click="fetchEnrollments" class="btn btn-outline-primary">Refresh</button>
    </div>
    
    <div class="filters mb-3">
      <div class="row">
        <div class="col-md-3">
          <input v-model="filters.search" @input="fetchEnrollments" placeholder="Search by user or course..." class="form-control">
        </div>
        <div class="col-md-2">
          <select v-model="filters.status" @change="fetchEnrollments" class="form-select">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="completed">Completed</option>
            <option value="expired">Expired</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
        <div class="col-md-2">
          <select v-model="filters.payment_status" @change="fetchEnrollments" class="form-select">
            <option value="">All Payments</option>
            <option value="pending">Pending</option>
            <option value="paid">Paid</option>
            <option value="failed">Failed</option>
            <option value="refunded">Refunded</option>
          </select>
        </div>
      </div>
    </div>
    
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>Student</th>
            <th>Course</th>
            <th>Progress</th>
            <th>Payment</th>
            <th>Status</th>
            <th>Enrolled</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="enrollment in enrollments" :key="enrollment.id">
            <td>
              <div>
                <strong>{{ enrollment.user.first_name }} {{ enrollment.user.last_name }}</strong>
                <br>
                <small class="text-muted">{{ enrollment.user.email }}</small>
              </div>
            </td>
            <td>
              <div>
                <strong>{{ enrollment.course.title }}</strong>
                <br>
                <small class="text-muted">{{ enrollment.course.state_code }}</small>
              </div>
            </td>
            <td>
              <div class="progress" style="height: 20px;">
                <div 
                  class="progress-bar" 
                  :style="{width: enrollment.progress_percentage + '%'}"
                  :class="getProgressBarClass(enrollment.progress_percentage)"
                >
                  {{ enrollment.progress_percentage }}%
                </div>
              </div>
              <small class="text-muted">{{ Math.floor(enrollment.total_time_spent / 60) }}h spent</small>
            </td>
            <td>
              <span :class="getPaymentStatusClass(enrollment.payment_status)">
                {{ enrollment.payment_status }}
              </span>
              <br>
              <small class="text-muted">${{ enrollment.amount_paid }}</small>
            </td>
            <td>
              <span :class="getStatusClass(enrollment.status)">
                {{ enrollment.status }}
              </span>
            </td>
            <td>{{ formatDate(enrollment.enrolled_at) }}</td>
            <td>
              <div class="btn-group btn-group-sm">
                <button @click="viewEnrollment(enrollment)" class="btn btn-outline-primary">View</button>
                <button @click="updateStatus(enrollment)" class="btn btn-outline-warning">Update</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <div v-if="enrollments.length === 0" class="text-center py-4">
      <p class="text-muted">No enrollments found.</p>
    </div>
    
    <!-- Update Status Modal -->
    <div v-if="selectedEnrollment" class="modal d-block" style="background: rgba(0,0,0,0.5);">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5>Update Enrollment Status</h5>
            <button @click="selectedEnrollment = null" class="btn-close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label>Status</label>
              <select v-model="updateData.status" class="form-select">
                <option value="active">Active</option>
                <option value="completed">Completed</option>
                <option value="expired">Expired</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
            <div class="mb-3">
              <label>Payment Status</label>
              <select v-model="updateData.payment_status" class="form-select">
                <option value="pending">Pending</option>
                <option value="paid">Paid</option>
                <option value="failed">Failed</option>
                <option value="refunded">Refunded</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button @click="saveUpdate" class="btn btn-primary">Save</button>
            <button @click="selectedEnrollment = null" class="btn btn-secondary">Cancel</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      enrollments: [],
      filters: {
        search: '',
        status: '',
        payment_status: ''
      },
      selectedEnrollment: null,
      updateData: {
        status: '',
        payment_status: ''
      }
    }
  },
  methods: {
    async fetchEnrollments() {
      try {
        const params = new URLSearchParams();
        Object.keys(this.filters).forEach(key => {
          if (this.filters[key]) params.append(key, this.filters[key]);
        });
        
        const response = await fetch(`/api/enrollments?${params}`, {
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
          },
          credentials: 'same-origin'
        });
        
        if (response.ok) {
          this.enrollments = await response.json();
        }
      } catch (error) {
        console.error('Error fetching enrollments:', error);
      }
    },
    
    getProgressBarClass(percentage) {
      if (percentage >= 100) return 'bg-success';
      if (percentage >= 75) return 'bg-info';
      if (percentage >= 50) return 'bg-warning';
      return 'bg-danger';
    },
    
    getPaymentStatusClass(status) {
      const classes = {
        paid: 'badge bg-success',
        pending: 'badge bg-warning',
        failed: 'badge bg-danger',
        refunded: 'badge bg-secondary'
      };
      return classes[status] || 'badge bg-secondary';
    },
    
    getStatusClass(status) {
      const classes = {
        active: 'badge bg-primary',
        completed: 'badge bg-success',
        expired: 'badge bg-warning',
        cancelled: 'badge bg-danger'
      };
      return classes[status] || 'badge bg-secondary';
    },
    
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString();
    },
    
    viewEnrollment(enrollment) {
      // Implement view functionality
      console.log('View enrollment:', enrollment);
    },
    
    updateStatus(enrollment) {
      this.selectedEnrollment = enrollment;
      this.updateData.status = enrollment.status;
      this.updateData.payment_status = enrollment.payment_status;
    },
    
    async saveUpdate() {
      try {
        const response = await fetch(`/api/enrollments/${this.selectedEnrollment.id}`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
          },
          credentials: 'same-origin',
          body: JSON.stringify(this.updateData)
        });
        
        if (response.ok) {
          this.selectedEnrollment = null;
          this.fetchEnrollments();
        }
      } catch (error) {
        console.error('Error updating enrollment:', error);
      }
    }
  },
  
  mounted() {
    this.fetchEnrollments();
  }
}
</script>
