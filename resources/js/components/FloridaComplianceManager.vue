<template>
  <div class="florida-compliance-manager">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold mb-4">Run Compliance Check</h3>
        <select v-model="newCheck.type" class="w-full border rounded px-3 py-2 mb-3">
          <option value="">Select Check Type</option>
          <option value="daily">Daily</option>
          <option value="weekly">Weekly</option>
          <option value="monthly">Monthly</option>
          <option value="quarterly">Quarterly</option>
          <option value="annual">Annual</option>
        </select>
        <input v-model="newCheck.name" placeholder="Check Name" class="w-full border rounded px-3 py-2 mb-3">
        <button @click="runCheck" :disabled="!newCheck.type || !newCheck.name" 
                class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 disabled:opacity-50">
          Run Check
        </button>
      </div>
      
      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold mb-4">Upcoming Due Checks</h3>
        <div v-if="upcomingChecks.length === 0" class="text-gray-500">No upcoming checks</div>
        <div v-else class="space-y-2">
          <div v-for="check in upcomingChecks" :key="check.id" 
               class="flex justify-between items-center p-2 bg-yellow-50 rounded">
            <span>{{ check.check_name }}</span>
            <span class="text-sm text-gray-600">{{ formatDate(check.next_due_date) }}</span>
          </div>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-lg shadow">
      <div class="p-6 border-b">
        <h2 class="text-xl font-semibold">Compliance Check History</h2>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Performed By</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="check in checks" :key="check.id">
              <td class="px-6 py-4 whitespace-nowrap">{{ check.check_name }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ check.check_type }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClass(check.status)" class="px-2 py-1 rounded-full text-xs">
                  {{ check.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">{{ check.performer?.name || 'System' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(check.performed_at) }}
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
  name: 'FloridaComplianceManager',
  data() {
    return {
      checks: [],
      upcomingChecks: [],
      newCheck: {
        type: '',
        name: ''
      }
    }
  },
  mounted() {
    this.loadChecks()
    this.loadUpcomingChecks()
  },
  methods: {
    async loadChecks() {
      try {
        const response = await axios.get('/api/florida-compliance/checks')
        this.checks = response.data.data
      } catch (error) {
        console.error('Error loading compliance checks:', error)
      }
    },
    async loadUpcomingChecks() {
      try {
        const response = await axios.get('/api/florida-compliance/upcoming-due')
        this.upcomingChecks = response.data
      } catch (error) {
        console.error('Error loading upcoming checks:', error)
      }
    },
    async runCheck() {
      try {
        await axios.post(`/api/florida-compliance/checks/${this.newCheck.type}/run`, {
          check_name: this.newCheck.name
        })
        
        this.newCheck = { type: '', name: '' }
        this.loadChecks()
        this.loadUpcomingChecks()
      } catch (error) {
        console.error('Error running compliance check:', error)
      }
    },
    getStatusClass(status) {
      const classes = {
        passed: 'text-white' + ' ' + 'custom-green-bg',
        failed: 'bg-red-100 text-red-800',
        warning: 'bg-yellow-100 text-yellow-800'
      }
      return classes[status] || classes.warning
    },
    formatDate(date) {
      return new Date(date).toLocaleDateString()
    }
  }
}
</script>
