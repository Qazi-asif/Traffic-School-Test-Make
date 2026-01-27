<template>
  <div class="florida-security-dashboard">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold mb-2">Security Events (24h)</h3>
        <p class="text-3xl font-bold text-blue-600">{{ stats.events24h }}</p>
      </div>
      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold mb-2">Failed Logins</h3>
        <p class="text-3xl font-bold text-red-600">{{ stats.failedLogins }}</p>
      </div>
      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold mb-2">High Risk Events</h3>
        <p class="text-3xl font-bold text-orange-600">{{ stats.highRisk }}</p>
      </div>
    </div>

    <div class="bg-white rounded-lg shadow">
      <div class="p-6 border-b">
        <h2 class="text-xl font-semibold">Recent Security Events</h2>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Event</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Risk Level</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="log in logs" :key="log.id">
              <td class="px-6 py-4 whitespace-nowrap">{{ log.event_type }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ log.user?.name || 'System' }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getRiskClass(log.risk_level)" class="px-2 py-1 rounded-full text-xs">
                  {{ log.risk_level }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(log.created_at) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FloridaSecurityDashboard',
  data() {
    return {
      logs: [],
      stats: {
        events24h: 0,
        failedLogins: 0,
        highRisk: 0
      }
    }
  },
  mounted() {
    this.loadSecurityLogs()
  },
  methods: {
    async loadSecurityLogs() {
      try {
        const response = await axios.get('/api/florida-security/logs')
        this.logs = response.data.data
        this.calculateStats()
      } catch (error) {
        console.error('Error loading security logs:', error)
      }
    },
    calculateStats() {
      const now = new Date()
      const yesterday = new Date(now.getTime() - 24 * 60 * 60 * 1000)
      
      this.stats.events24h = this.logs.filter(log => 
        new Date(log.created_at) > yesterday
      ).length
      
      this.stats.failedLogins = this.logs.filter(log => 
        log.event_type === 'failed_login'
      ).length
      
      this.stats.highRisk = this.logs.filter(log => 
        log.risk_level === 'high' || log.risk_level === 'critical'
      ).length
    },
    getRiskClass(level) {
      const classes = {
        low: 'bg-green-100 text-green-800' + ' ' + 'custom-green-bg custom-green-text',
        medium: 'bg-yellow-100 text-yellow-800',
        high: 'bg-orange-100 text-orange-800',
        critical: 'bg-red-100 text-red-800'
      }
      return classes[level] || classes.low
    },
    formatDate(date) {
      return new Date(date).toLocaleString()
    }
  }
}
</script>
