<template>
  <div class="florida-audit-viewer">
    <div class="bg-white rounded-lg shadow">
      <div class="p-6 border-b">
        <h2 class="text-xl font-semibold">Florida Audit Trail</h2>
        <div class="mt-4 flex gap-4">
          <input v-model="filters.action" placeholder="Filter by action" class="border rounded px-3 py-2">
          <label class="flex items-center">
            <input v-model="filters.floridaRequired" type="checkbox" class="mr-2">
            Florida Required Only
          </label>
          <button @click="generateReport" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            Generate Report
          </button>
        </div>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Model</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Florida Required</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="trail in trails" :key="trail.id">
              <td class="px-6 py-4 whitespace-nowrap">{{ trail.action }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ trail.user?.name || 'System' }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ trail.model_type }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span v-if="trail.florida_required" class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs">
                  Required
                </span>
                <span v-else class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs">
                  Optional
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(trail.created_at) }}
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
  name: 'FloridaAuditTrailViewer',
  data() {
    return {
      trails: [],
      filters: {
        action: '',
        floridaRequired: false
      }
    }
  },
  mounted() {
    this.loadAuditTrails()
  },
  watch: {
    filters: {
      handler() {
        this.loadAuditTrails()
      },
      deep: true
    }
  },
  methods: {
    async loadAuditTrails() {
      try {
        const params = new URLSearchParams()
        if (this.filters.action) params.append('action', this.filters.action)
        if (this.filters.floridaRequired) params.append('florida_required', '1')
        
        const response = await axios.get(`/api/florida-audit/trails?${params}`)
        this.trails = response.data.data
      } catch (error) {
        console.error('Error loading audit trails:', error)
      }
    },
    async generateReport() {
      try {
        const startDate = prompt('Start date (YYYY-MM-DD):')
        const endDate = prompt('End date (YYYY-MM-DD):')
        
        if (startDate && endDate) {
          const response = await axios.post('/api/florida-audit/generate-report', {
            start_date: startDate,
            end_date: endDate
          })
          
          // Handle report download or display
          console.log('Report generated:', response.data)
        }
      } catch (error) {
        console.error('Error generating report:', error)
      }
    },
    formatDate(date) {
      return new Date(date).toLocaleString()
    }
  }
}
</script>
