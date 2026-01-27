<template>
  <div class="florida-responsive-layout" :class="layoutClasses">
    <div v-if="isMobile" class="mobile-layout">
      <slot name="mobile" :device="deviceInfo">
        <slot></slot>
      </slot>
    </div>
    <div v-else-if="isTablet" class="tablet-layout">
      <slot name="tablet" :device="deviceInfo">
        <slot></slot>
      </slot>
    </div>
    <div v-else class="desktop-layout">
      <slot name="desktop" :device="deviceInfo">
        <slot></slot>
      </slot>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FloridaResponsiveLayout',
  data() {
    return {
      screenWidth: window.innerWidth,
      deviceInfo: {}
    }
  },
  computed: {
    isMobile() {
      return this.screenWidth < 768
    },
    isTablet() {
      return this.screenWidth >= 768 && this.screenWidth < 1024
    },
    isDesktop() {
      return this.screenWidth >= 1024
    },
    layoutClasses() {
      return {
        'florida-mobile': this.isMobile,
        'florida-tablet': this.isTablet,
        'florida-desktop': this.isDesktop
      }
    }
  },
  mounted() {
    this.updateDeviceInfo()
    window.addEventListener('resize', this.handleResize)
  },
  beforeUnmount() {
    window.removeEventListener('resize', this.handleResize)
  },
  methods: {
    handleResize() {
      this.screenWidth = window.innerWidth
      this.updateDeviceInfo()
    },
    async updateDeviceInfo() {
      try {
        const response = await axios.get('/api/florida-device/info', {
          params: {
            screen_width: this.screenWidth,
            screen_height: window.innerHeight
          }
        })
        this.deviceInfo = response.data
      } catch (error) {
        console.error('Error updating device info:', error)
      }
    }
  }
}
</script>

<style scoped>
.florida-responsive-layout {
  width: 100%;
}

.florida-mobile {
  font-size: 16px;
}

.florida-tablet {
  font-size: 14px;
}

.florida-desktop {
  font-size: 14px;
}

.mobile-layout {
  padding: 1rem;
}

.tablet-layout {
  padding: 1.5rem;
}

.desktop-layout {
  padding: 2rem;
}
</style>
