<template>
  <div class="accessibility-settings" :class="accessibilityClasses">
    <h2>Accessibility Settings</h2>
    
    <div class="setting-group">
      <label for="font-size">Font Size</label>
      <select id="font-size" v-model="preferences.font_size" @change="updatePreferences">
        <option value="small">Small</option>
        <option value="medium">Medium</option>
        <option value="large">Large</option>
        <option value="xlarge">Extra Large</option>
      </select>
    </div>

    <div class="setting-group">
      <label>
        <input 
          type="checkbox" 
          v-model="preferences.high_contrast_mode" 
          @change="updatePreferences"
        />
        High Contrast Mode
      </label>
    </div>

    <div class="setting-group">
      <label>
        <input 
          type="checkbox" 
          v-model="preferences.reduced_animations" 
          @change="updatePreferences"
        />
        Reduce Animations
      </label>
    </div>

    <div class="setting-group">
      <label>
        <input 
          type="checkbox" 
          v-model="preferences.screen_reader_optimized" 
          @change="updatePreferences"
        />
        Screen Reader Optimized
      </label>
    </div>

    <div class="setting-group">
      <label>
        <input 
          type="checkbox" 
          v-model="preferences.keyboard_navigation" 
          @change="updatePreferences"
        />
        Enhanced Keyboard Navigation
      </label>
    </div>

    <button @click="resetPreferences" class="btn btn-secondary">
      Reset to Defaults
    </button>
  </div>
</template>

<script>
export default {
  name: 'AccessibilitySettings',
  data() {
    return {
      preferences: {
        font_size: 'medium',
        high_contrast_mode: false,
        reduced_animations: false,
        screen_reader_optimized: false,
        keyboard_navigation: true,
      },
    };
  },
  computed: {
    accessibilityClasses() {
      return {
        'font-small': this.preferences.font_size === 'small',
        'font-medium': this.preferences.font_size === 'medium',
        'font-large': this.preferences.font_size === 'large',
        'font-xlarge': this.preferences.font_size === 'xlarge',
        'high-contrast': this.preferences.high_contrast_mode,
        'reduced-motion': this.preferences.reduced_animations,
        'screen-reader-optimized': this.preferences.screen_reader_optimized,
      };
    },
  },
  async mounted() {
    await this.loadPreferences();
    this.applyPreferences();
  },
  methods: {
    async loadPreferences() {
      try {
        const response = await fetch('/api/accessibility/preferences');
        this.preferences = await response.json();
      } catch (error) {
        console.error('Failed to load accessibility preferences:', error);
      }
    },
    async updatePreferences() {
      try {
        await fetch('/api/accessibility/preferences', {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          },
          body: JSON.stringify(this.preferences),
        });
        this.applyPreferences();
        this.announceChange();
      } catch (error) {
        console.error('Failed to update accessibility preferences:', error);
      }
    },
    async resetPreferences() {
      try {
        const response = await fetch('/api/accessibility/reset-preferences', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          },
        });
        this.preferences = await response.json();
        this.applyPreferences();
        this.announceChange('Settings reset to defaults');
      } catch (error) {
        console.error('Failed to reset accessibility preferences:', error);
      }
    },
    applyPreferences() {
      document.body.className = document.body.className.replace(/font-\w+|high-contrast|reduced-motion/g, '');
      document.body.classList.add(`font-${this.preferences.font_size}`);
      
      if (this.preferences.high_contrast_mode) {
        document.body.classList.add('high-contrast');
      }
      
      if (this.preferences.reduced_animations) {
        document.body.classList.add('reduced-motion');
      }
    },
    announceChange(message = 'Accessibility settings updated') {
      if (this.preferences.screen_reader_optimized) {
        const announcement = document.createElement('div');
        announcement.setAttribute('aria-live', 'polite');
        announcement.setAttribute('aria-atomic', 'true');
        announcement.className = 'sr-only';
        announcement.textContent = message;
        document.body.appendChild(announcement);
        setTimeout(() => document.body.removeChild(announcement), 1000);
      }
    },
  },
};
</script>

<style scoped>
.accessibility-settings {
  max-width: 600px;
  margin: 0 auto;
  padding: 2rem;
}

.setting-group {
  margin-bottom: 1.5rem;
}

.setting-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
}

.setting-group select,
.setting-group input[type="checkbox"] {
  margin-right: 0.5rem;
}

.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  min-height: 44px;
}

.btn-secondary {
  background-color: #6c757d;
  color: white;
}
</style>
