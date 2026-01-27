<template>
  <div class="florida-data-export-tool">
    <div class="bg-white p-6 rounded-lg shadow mb-6">
      <h2 class="text-xl font-semibold mb-4">Request Data Export</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <select v-model="exportType" class="border rounded px-3 py-2">
          <option value="">Select Export Type</option>
          <option value="gdpr">GDPR Request</option>
          <option value="ccpa">CCPA Request</option>
          <option value="florida_public_records">Florida Public Records</option>
          <option value="internal_audit">Internal Audit</option>
        </select>
        <button @click="requestExport" :disabled="!exportType" 
                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 disabled:opacity-50">
          Request Export
        </button>
      </div>
    </div>

    <div class="bg-white rounded-lg shadow">
      <div class="p-6 border-b">
        <h2 class="text-xl font-semibold">Export Requests</h2>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Requested</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Completed</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="exportRequest in exports" :key="exportRequest.id">
              <td class="px-6 py-4 whitespace-nowrap">{{ exportRequest.export_type }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClass(exportRequest.status)" class="px-2 py-1 rounded-full text-xs">
                  {{ exportRequest.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(exportRequest.requested_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ exportRequest.completed_at ? formatDate(exportRequest.completed_at) : '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <button v-if="exportRequest.status === 'completed'" 
                        @click="downloadExport(exportRequest.id)"
                        class="text-white px-3 py-1 rounded text-sm" 
                        style="background-color: #516425;"
                        @mouseover="$event.target.style.backgroundColor='#3d4b1c'"
                        @mouseout="$event.target.style.backgroundColor='#516425'">
                  Download
                </button>
                <button v-else @click="checkStatus(exportRequest.id)"
                        class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600">
                  Check Status
                </button>
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
  name: 'FloridaDataExportTool',
  data() {
    return {
      exports: [],
      exportType: ''
    }
  },
  mounted() {
    this.loadExports()
  },
  methods: {
    async loadExports() {
      // This would need to be implemented in the controller
      // For now, we'll use mock data
      this.exports = []
    },
    async requestExport() {
      try {
        const response = await axios.post('/api/florida-data-exports/request', {
          export_type: this.exportType
        })
        
        this.exports.unshift(response.data)
        this.exportType = ''
      } catch (error) {
        console.error('Error requesting export:', error)
      }
    },
    async checkStatus(id) {
      try {
        const response = await axios.get(`/api/florida-data-exports/status/${id}`)
        const index = this.exports.findIndex(exp => exp.id === id)
        if (index !== -1) {
          this.exports[index] = response.data
        }
      } catch (error) {
        console.error('Error checking status:', error)
      }
    },
    async downloadExport(id) {
      try {
        const response = await axios.get(`/api/florida-data-exports/download/${id}`, {
          responseType: 'blob'
        })
        
        const url = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', `export-${id}.zip`)
        document.body.appendChild(link)
        link.click()
        link.remove()
      } catch (error) {
        console.error('Error downloading export:', error)
      }
    },
    getStatusClass(status) {
      const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        processing: 'bg-blue-100 text-blue-800',
        completed: 'text-white' + ' ' + 'custom-green-bg',
        failed: 'bg-red-100 text-red-800'
      }
      return classes[status] || classes.pending
    },
    formatDate(date) {
      return new Date(date).toLocaleString()
    }
  }
}
</script>
