<template>
  <div class="security-dashboard">
    <div class="row mb-4">
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-body">
            <h5 class="card-title">{{ stats.total_events }}</h5>
            <p class="card-text">Total Events</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-body">
            <h5 class="card-title text-warning">{{ stats.failed_logins_today }}</h5>
            <p class="card-text">Failed Logins Today</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-body">
            <h5 class="card-title text-danger">{{ stats.high_risk_events }}</h5>
            <p class="card-text">High Risk Events</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-body">
            <h5 class="card-title text-danger">{{ stats.critical_events }}</h5>
            <p class="card-text">Critical Events</p>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h5>Recent Security Events</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>Time</th>
                <th>User</th>
                <th>Event</th>
                <th>Risk Level</th>
                <th>IP Address</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="event in recentEvents" :key="event.id">
                <td>{{ formatDate(event.created_at) }}</td>
                <td>{{ event.user?.name || 'System' }}</td>
                <td>{{ event.description }}</td>
                <td>
                  <span :class="getRiskClass(event.risk_level)">
                    {{ event.risk_level.toUpperCase() }}
                  </span>
                </td>
                <td>{{ event.ip_address }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SecurityDashboard',
  data() {
    return {
      stats: {
        total_events: 0,
        failed_logins_today: 0,
        high_risk_events: 0,
        critical_events: 0,
      },
      recentEvents: [],
    };
  },
  async mounted() {
    await this.loadDashboard();
  },
  methods: {
    async loadDashboard() {
      try {
        const response = await fetch('/api/audit/dashboard');
        const data = await response.json();
        this.stats = data.stats;
        this.recentEvents = data.recent_events;
      } catch (error) {
        console.error('Failed to load security dashboard:', error);
      }
    },
    formatDate(date) {
      return new Date(date).toLocaleString();
    },
    getRiskClass(level) {
      const classes = {
        low: 'badge bg-success',
        medium: 'badge bg-warning',
        high: 'badge bg-danger',
        critical: 'badge bg-dark',
      };
      return classes[level] || 'badge bg-secondary';
    },
  },
};
</script>
