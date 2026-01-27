<template>
  <div v-if="showPrompt" class="pwa-prompt" role="dialog" aria-labelledby="pwa-title">
    <div class="pwa-content">
      <div class="pwa-icon">
        <i class="fas fa-mobile-alt"></i>
      </div>
      <h3 id="pwa-title">Install Traffic School App</h3>
      <p>Add this app to your home screen for quick and easy access when you're on the go.</p>
      <div class="pwa-actions">
        <button @click="installApp" class="install-btn">Install</button>
        <button @click="dismissPrompt" class="dismiss-btn">Not Now</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PWAPrompt',
  data() {
    return {
      showPrompt: false,
      deferredPrompt: null,
    };
  },
  mounted() {
    window.addEventListener('beforeinstallprompt', this.handleBeforeInstallPrompt);
    this.checkIfShouldShowPrompt();
  },
  beforeUnmount() {
    window.removeEventListener('beforeinstallprompt', this.handleBeforeInstallPrompt);
  },
  methods: {
    handleBeforeInstallPrompt(e) {
      e.preventDefault();
      this.deferredPrompt = e;
      this.showPrompt = true;
    },
    async installApp() {
      if (this.deferredPrompt) {
        this.deferredPrompt.prompt();
        const { outcome } = await this.deferredPrompt.userChoice;
        
        if (outcome === 'accepted') {
          localStorage.setItem('pwa-installed', 'true');
        }
        
        this.deferredPrompt = null;
        this.showPrompt = false;
      }
    },
    dismissPrompt() {
      this.showPrompt = false;
      localStorage.setItem('pwa-prompt-dismissed', Date.now().toString());
    },
    checkIfShouldShowPrompt() {
      const dismissed = localStorage.getItem('pwa-prompt-dismissed');
      const installed = localStorage.getItem('pwa-installed');
      
      if (installed || (dismissed && Date.now() - parseInt(dismissed) < 7 * 24 * 60 * 60 * 1000)) {
        return;
      }
      
      // Show prompt after 30 seconds if conditions are met
      setTimeout(() => {
        if (this.isMobile() && !this.isStandalone()) {
          this.showPrompt = true;
        }
      }, 30000);
    },
    isMobile() {
      return /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    },
    isStandalone() {
      return window.matchMedia('(display-mode: standalone)').matches || 
             window.navigator.standalone === true;
    },
  },
};
</script>

<style scoped>
.pwa-prompt {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background: white;
  box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.15);
  z-index: 1000;
  padding: 1rem;
  border-top: 1px solid #dee2e6;
}

.pwa-content {
  max-width: 400px;
  margin: 0 auto;
  text-align: center;
}

.pwa-icon {
  font-size: 2rem;
  color: #007bff;
  margin-bottom: 0.5rem;
}

.pwa-content h3 {
  margin: 0 0 0.5rem 0;
  font-size: 1.2rem;
  color: #333;
}

.pwa-content p {
  margin: 0 0 1rem 0;
  color: #666;
  font-size: 0.9rem;
  line-height: 1.4;
}

.pwa-actions {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
}

.install-btn, .dismiss-btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 6px;
  font-size: 0.9rem;
  cursor: pointer;
  min-height: 44px;
}

.install-btn {
  background: #007bff;
  color: white;
}

.dismiss-btn {
  background: #f8f9fa;
  color: #6c757d;
  border: 1px solid #dee2e6;
}

@media (max-width: 480px) {
  .pwa-actions {
    flex-direction: column;
  }
  
  .install-btn, .dismiss-btn {
    width: 100%;
  }
}
</style>
