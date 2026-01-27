<template>
  <div class="admin-dashboard">
    <div class="dashboard-header mb-4">
      <h2>Admin Dashboard</h2>
      <div class="dashboard-controls">
        <button @click="refreshData" class="btn btn-outline-primary">
          <i class="fas fa-sync"></i> Refresh
        </button>
      </div>
    </div>
    
    <!-- Stats Overview -->
    <div class="row mb-4">
      <div class="col-md-2">
        <div class="stat-card bg-primary text-white">
          <div class="stat-value">{{ stats.total_users }}</div>
          <div class="stat-label">Active Users</div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="stat-card bg-success text-white">
          <div class="stat-value">{{ stats.total_enrollments }}</div>
          <div class="stat-label">Monthly Enrollments</div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="stat-card bg-info text-white">
          <div class="stat-value">${{ stats.monthly_revenue }}</div>
          <div class="stat-label">Monthly Revenue</div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="stat-card bg-warning text-white">
          <div class="stat-value">{{ stats.completion_rate }}%</div>
          <div class="stat-label">Completion Rate</div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="stat-card bg-danger text-white">
          <div class="stat-value">{{ stats.pending_submissions }}</div>
          <div class="stat-label">Pending Submissions</div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="stat-card bg-secondary text-white">
          <div class="stat-value">{{ stats.total_courses }}</div>
          <div class="stat-label">Active Courses</div>
        </div>
      </div>
    </div>
    
    <!-- Charts Row -->
    <div class="row mb-4">
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">
            <h5>Enrollment Trends</h5>
          </div>
          <div class="card-body">
            <canvas id="enrollmentChart"></canvas>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">
            <h5>Revenue by State</h5>
          </div>
          <div class="card-body">
            <canvas id="revenueChart"></canvas>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Recent Activity -->
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h5>Recent Enrollments</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table">
                <thead>
                  <tr>
                    <th>Student</th>
                    <th>Course</th>
                    <th>State</th>
                    <th>Enrolled</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="enrollment in recentActivity" :key="enrollment.id">
                    <td>{{ enrollment.user.first_name }} {{ enrollment.user.last_name }}</td>
                    <td>{{ enrollment.course.title }}</td>
                    <td>{{ enrollment.course.state_code }}</td>
                    <td>{{ formatDate(enrollment.enrolled_at) }}</td>
                    <td>
                      <span :class="getStatusClass(enrollment.status)">
                        {{ enrollment.status }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
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
      stats: {
        total_users: 0,
        total_enrollments: 0,
        monthly_revenue: 0,
        completion_rate: 0,
        pending_submissions: 0,
        total_courses: 0
      },
      charts: {},
      recentActivity: []
    }
  },
  methods: {
    async loadDashboardData() {
      try {
        // Load stats
        const statsResponse = await fetch('/api/admin/dashboard/stats', {
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
          },
          credentials: 'same-origin'
        });
        
        if (statsResponse.ok) {
          this.stats = await statsResponse.json();
        }
        
        // Load charts
        const chartsResponse = await fetch('/api/admin/dashboard/charts', {
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
          },
          credentials: 'same-origin'
        });
        
        if (chartsResponse.ok) {
          this.charts = await chartsResponse.json();
          this.renderCharts();
        }
        
        // Load recent activity
        const activityResponse = await fetch('/api/admin/dashboard/recent-activity', {
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
          },
          credentials: 'same-origin'
        });
        
        if (activityResponse.ok) {
          this.recentActivity = await activityResponse.json();
        }
        
      } catch (error) {
        console.error('Error loading dashboard data:', error);
      }
    },
    
    renderCharts() {
      // Simple chart rendering - would use Chart.js in real implementation
      console.log('Charts data:', this.charts);
    },
    
    refreshData() {
      this.loadDashboardData();
    },
    
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString();
    },
    
    getStatusClass(status) {
      const classes = {
        active: 'badge bg-primary',
        completed: 'badge bg-success',
        expired: 'badge bg-warning',
        cancelled: 'badge bg-danger'
      };
      return classes[status] || 'badge bg-secondary';
    }
  },
  
  mounted() {
    this.loadDashboardData();
  }
}
</script>

<style scoped>
.stat-card {
  padding: 20px;
  border-radius: 8px;
  text-align: center;
  margin-bottom: 20px;
}

.stat-value {
  font-size: 2rem;
  font-weight: bold;
}

.stat-label {
  font-size: 0.9rem;
  opacity: 0.9;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
</style>
