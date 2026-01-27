<template>
  <div class="state-configuration-manager">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>State Configuration Management</h2>
      <button @click="showCreateModal" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add State Configuration
      </button>
    </div>

    <!-- Configurations List -->
    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>State</th>
                <th>Submission Method</th>
                <th>Status</th>
                <th>Rules</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="config in configurations" :key="config.id">
                <td>
                  <strong>{{ config.state_code }}</strong><br>
                  <small class="text-muted">{{ config.state_name }}</small>
                </td>
                <td>
                  <span class="badge" :class="getMethodBadgeClass(config.submission_method)">
                    {{ config.submission_method.toUpperCase() }}
                  </span>
                </td>
                <td>
                  <span class="badge" :class="config.is_active ? 'bg-success' : 'bg-secondary'">
                    {{ config.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td>{{ config.compliance_rules?.length || 0 }} rules</td>
                <td>
                  <button @click="testConnection(config)" class="btn btn-sm btn-outline-info me-1">
                    <i class="fas fa-plug"></i> Test
                  </button>
                  <button @click="editConfiguration(config)" class="btn btn-sm btn-outline-primary me-1">
                    <i class="fas fa-edit"></i> Edit
                  </button>
                  <button @click="manageRules(config)" class="btn btn-sm btn-outline-secondary me-1">
                    <i class="fas fa-cogs"></i> Rules
                  </button>
                  <button @click="deleteConfiguration(config)" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-trash"></i> Delete
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal fade" id="configModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEditing ? 'Edit' : 'Create' }} State Configuration</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveConfiguration">
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">State Code</label>
                    <input v-model="form.state_code" type="text" class="form-control" maxlength="2" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">State Name</label>
                    <input v-model="form.state_name" type="text" class="form-control" required>
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Submission Method</label>
                <select v-model="form.submission_method" class="form-control" required>
                  <option value="">Select Method</option>
                  <option value="api">API</option>
                  <option value="portal">Portal</option>
                  <option value="email">Email</option>
                  <option value="manual">Manual</option>
                </select>
              </div>

              <!-- API Configuration -->
              <div v-if="form.submission_method === 'api'" class="mb-3">
                <label class="form-label">API Endpoint</label>
                <input v-model="form.api_endpoint" type="url" class="form-control" required>
                <div class="mt-2">
                  <label class="form-label">API Credentials (JSON)</label>
                  <textarea v-model="form.api_credentials" class="form-control" rows="3" 
                           placeholder='{"token": "your-api-token", "key": "your-api-key"}'></textarea>
                </div>
              </div>

              <!-- Portal Configuration -->
              <div v-if="form.submission_method === 'portal'" class="mb-3">
                <label class="form-label">Portal URL</label>
                <input v-model="form.portal_url" type="url" class="form-control" required>
                <div class="mt-2">
                  <label class="form-label">Portal Credentials (JSON)</label>
                  <textarea v-model="form.portal_credentials" class="form-control" rows="3" 
                           placeholder='{"username": "your-username", "password": "your-password"}'></textarea>
                </div>
              </div>

              <!-- Email Configuration -->
              <div v-if="form.submission_method === 'email'" class="mb-3">
                <label class="form-label">Email Recipient</label>
                <input v-model="form.email_recipient" type="email" class="form-control" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Certificate Template</label>
                <input v-model="form.certificate_template" type="text" class="form-control" required>
              </div>

              <div class="mb-3">
                <div class="form-check">
                  <input v-model="form.is_active" type="checkbox" class="form-check-input" id="isActive">
                  <label class="form-check-label" for="isActive">Active</label>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button @click="saveConfiguration" type="button" class="btn btn-primary">
              {{ isEditing ? 'Update' : 'Create' }} Configuration
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'StateConfigurationManager',
  data() {
    return {
      configurations: [],
      isEditing: false,
      form: {
        state_code: '',
        state_name: '',
        submission_method: '',
        api_endpoint: '',
        api_credentials: '',
        portal_url: '',
        portal_credentials: '',
        email_recipient: '',
        certificate_template: '',
        is_active: true
      }
    }
  },
  mounted() {
    this.loadConfigurations();
  },
  methods: {
    async loadConfigurations() {
      try {
        const response = await fetch('/api/state-configurations');
        this.configurations = await response.json();
      } catch (error) {
        console.error('Error loading configurations:', error);
      }
    },
    
    showCreateModal() {
      this.isEditing = false;
      this.resetForm();
      new bootstrap.Modal(document.getElementById('configModal')).show();
    },
    
    editConfiguration(config) {
      this.isEditing = true;
      this.form = { ...config };
      new bootstrap.Modal(document.getElementById('configModal')).show();
    },
    
    async saveConfiguration() {
      try {
        const url = this.isEditing ? `/api/state-configurations/${this.form.id}` : '/api/state-configurations';
        const method = this.isEditing ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
          method,
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(this.form)
        });
        
        if (response.ok) {
          bootstrap.Modal.getInstance(document.getElementById('configModal')).hide();
          this.loadConfigurations();
          alert('Configuration saved successfully!');
        }
      } catch (error) {
        console.error('Error saving configuration:', error);
        alert('Error saving configuration');
      }
    },
    
    async testConnection(config) {
      try {
        const response = await fetch(`/api/state-configurations/${config.state_code}/test-connection`);
        const result = await response.json();
        
        if (result.status === 'success') {
          alert('Connection test successful: ' + result.message);
        } else {
          alert('Connection test failed: ' + result.message);
        }
      } catch (error) {
        console.error('Error testing connection:', error);
        alert('Error testing connection');
      }
    },
    
    async deleteConfiguration(config) {
      if (confirm(`Are you sure you want to delete the configuration for ${config.state_name}?`)) {
        try {
          await fetch(`/api/state-configurations/${config.id}`, { method: 'DELETE' });
          this.loadConfigurations();
          alert('Configuration deleted successfully!');
        } catch (error) {
          console.error('Error deleting configuration:', error);
          alert('Error deleting configuration');
        }
      }
    },
    
    manageRules(config) {
      // Navigate to compliance rules management
      this.$router.push(`/admin/compliance-rules/${config.id}`);
    },
    
    resetForm() {
      this.form = {
        state_code: '',
        state_name: '',
        submission_method: '',
        api_endpoint: '',
        api_credentials: '',
        portal_url: '',
        portal_credentials: '',
        email_recipient: '',
        certificate_template: '',
        is_active: true
      };
    },
    
    getMethodBadgeClass(method) {
      const classes = {
        api: 'bg-primary',
        portal: 'bg-info',
        email: 'bg-warning',
        manual: 'bg-secondary'
      };
      return classes[method] || 'bg-secondary';
    }
  }
}
</script>
