<template>
  <div v-if="showPrompt" class="florida-pwa-prompt">
    <div class="prompt-content">
      <div class="prompt-icon">
        <i class="fas fa-mobile-alt"></i>
      </div>
      <div class="prompt-text">
        <h6>Install Florida Traffic School</h6>
        <p>Add to your home screen for quick access to your courses</p>
      </div>
      <div class="prompt-actions">
        <button @click="installPWA" class="btn btn-primary btn-sm">Install</button>
        <button @click="dismissPrompt" class="btn btn-outline-secondary btn-sm">Later</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FloridaPWAPrompt',
  data() {
    return {
      showPrompt: false,
      deferredPrompt: null
    }
  },
  mounted() {
    this.checkPWASupport()
  },
  methods: {
    checkPWASupport() {
      window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault()
        this.deferredPrompt = e
        
        // Show prompt if not already installed and not dismissed
        if (!this.isPWAInstalled() && !this.isPromptDismissed()) {
          this.showPrompt = true
        }
      })
    },
    async installPWA() {
      if (this.deferredPrompt) {
        this.deferredPrompt.prompt()
        const { outcome } = await this.deferredPrompt.userChoice
        
        if (outcome === 'accepted') {
          console.log('PWA installed')
        }
        
        this.deferredPrompt = null
        this.showPrompt = false
      }
    },
    dismissPrompt() {
      this.showPrompt = false
      localStorage.setItem('florida-pwa-dismissed', Date.now())
    },
    isPWAInstalled() {
      return window.matchMedia('(display-mode: standalone)').matches ||
             window.navigator.standalone === true
    },
    isPromptDismissed() {
      const dismissed = localStorage.getItem('florida-pwa-dismissed')
      if (!dismissed) return false
      
      // Show again after 7 days
      const dismissedTime = parseInt(dismissed)
      const weekAgo = Date.now() - (7 * 24 * 60 * 60 * 1000)
      
      return dismissedTime > weekAgo
    }
  }
}
</script>

<style scoped>
.florida-pwa-prompt {
  position: fixed;
  bottom: 20px;
  left: 20px;
  right: 20px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  z-index: 1000;
  max-width: 400px;
  margin: 0 auto;
}

.prompt-content {
  display: flex;
  align-items: center;
  padding: 1rem;
  gap: 1rem;
}

.prompt-icon {
  font-size: 2rem;
  color: #007bff;
}

.prompt-text {
  flex: 1;
}

.prompt-text h6 {
  margin: 0 0 0.25rem 0;
  font-weight: 600;
}

.prompt-text p {
  margin: 0;
  font-size: 0.875rem;
  color: #6c757d;
}

.prompt-actions {
  display: flex;
  gap: 0.5rem;
}
</style>
