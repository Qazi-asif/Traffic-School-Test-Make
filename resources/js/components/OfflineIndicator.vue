<template>
  <div v-if="!isOnline" class="offline-indicator" role="alert" aria-live="assertive">
    <div class="offline-content">
      <i class="fas fa-wifi-slash"></i>
      <span>You're offline. Some features may not be available.</span>
    </div>
  </div>
</template>

<script>
export default {
  name: 'OfflineIndicator',
  data() {
    return {
      isOnline: navigator.onLine,
    };
  },
  mounted() {
    window.addEventListener('online', this.handleOnline);
    window.addEventListener('offline', this.handleOffline);
  },
  beforeUnmount() {
    window.removeEventListener('online', this.handleOnline);
    window.removeEventListener('offline', this.handleOffline);
  },
  methods: {
    handleOnline() {
      this.isOnline = true;
      this.$emit('online');
    },
    handleOffline() {
      this.isOnline = false;
      this.$emit('offline');
    },
  },
};
</script>

<style scoped>
.offline-indicator {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  background: #dc3545;
  color: white;
  padding: 0.75rem;
  text-align: center;
  z-index: 1001;
  font-size: 0.9rem;
}

.offline-content {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.offline-content i {
  font-size: 1rem;
}
</style>
