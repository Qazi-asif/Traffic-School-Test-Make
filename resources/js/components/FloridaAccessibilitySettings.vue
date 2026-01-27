<template>
  <div class="florida-accessibility-settings">
    <div class="card">
      <div class="card-header">
        <h5>Florida Accessibility Settings</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Font Size</label>
              <select v-model="preferences.font_size" class="form-control" @change="updatePreferences">
                <option value="small">Small</option>
                <option value="medium">Medium</option>
                <option value="large">Large</option>
                <option value="xlarge">Extra Large</option>
              </select>
            </div>
            
            <div class="form-check mb-3">
              <input v-model="preferences.high_contrast_mode" class="form-check-input" type="checkbox" @change="updatePreferences">
              <label class="form-check-label">High Contrast Mode</label>
            </div>
            
            <div class="form-check mb-3">
              <input v-model="preferences.reduced_animations" class="form-check-input" type="checkbox" @change="updatePreferences">
              <label class="form-check-label">Reduce Animations</label>
            </div>
          </div>
          
          <div class="col-md-6">
            <div class="form-check mb-3">
              <input v-model="preferences.screen_reader_optimized" class="form-check-input" type="checkbox" @change="updatePreferences">
              <label class="form-check-label">Screen Reader Optimized</label>
            </div>
            
            <div class="form-check mb-3">
              <input v-model="preferences.keyboard_navigation" class="form-check-input" type="checkbox" @change="updatePreferences">
              <label class="form-check-label">Keyboard Navigation</label>
            </div>
            
            <div class="form-check mb-3">
              <input v-model="preferences.mobile_optimized" class="form-check-input" type="checkbox" @change="updatePreferences">
              <label class="form-check-label">Mobile Optimized</label>
            </div>
          </div>
        </div>
        
        <div class="mt-3">
          <button @click="resetPreferences" class="btn btn-outline-secondary">Reset to Defaults</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FloridaAccessibilitySettings',
  data() {
    return {
      preferences: {
        font_size: 'medium',
        high_contrast_mode: false,
        reduced_animations: false,
        screen_reader_optimized: false,
        keyboard_navigation: true,
        mobile_optimized: true
      }
    }
  },
  mounted() {
    this.loadPreferences()
  },
  methods: {
    async loadPreferences() {
      try {
        const response = await axios.get('/api/florida-accessibility/preferences')
        this.preferences = response.data
        this.applyPreferences()
      } catch (error) {
        console.error('Error loading preferences:', error)
      }
    },
    async updatePreferences() {
      try {
        await axios.put('/api/florida-accessibility/preferences', this.preferences)
        this.applyPreferences()
      } catch (error) {
        console.error('Error updating preferences:', error)
      }
    },
    async resetPreferences() {
      try {
        await axios.post('/api/florida-accessibility/reset-preferences')
        this.loadPreferences()
      } catch (error) {
        console.error('Error resetting preferences:', error)
      }
    },
    applyPreferences() {
      document.body.className = document.body.className.replace(/font-\w+/g, '')
      document.body.classList.add(`font-${this.preferences.font_size}`)
      
      if (this.preferences.high_contrast_mode) {
        document.body.classList.add('high-contrast')
      } else {
        document.body.classList.remove('high-contrast')
      }
      
      if (this.preferences.reduced_animations) {
        document.body.classList.add('reduced-motion')
      } else {
        document.body.classList.remove('reduced-motion')
      }
    }
  }
}
</script>
