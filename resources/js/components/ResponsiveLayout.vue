<template>
  <div class="responsive-layout" :class="layoutClasses">
    <component :is="currentComponent" v-bind="componentProps" />
  </div>
</template>

<script>
export default {
  name: 'ResponsiveLayout',
  props: {
    component: String,
    componentProps: Object,
  },
  data() {
    return {
      screenWidth: window.innerWidth,
      deviceType: 'desktop',
    };
  },
  computed: {
    layoutClasses() {
      return {
        'layout-mobile': this.deviceType === 'mobile',
        'layout-tablet': this.deviceType === 'tablet',
        'layout-desktop': this.deviceType === 'desktop',
        'touch-friendly': this.isTouchDevice,
      };
    },
    currentComponent() {
      const componentMap = {
        mobile: {
          'CoursePlayer': 'MobileCoursePlayer',
          'Dashboard': 'MobileDashboard',
          'Quiz': 'MobileQuiz',
        },
        tablet: {
          'CoursePlayer': 'TabletCoursePlayer',
        },
      };
      
      return componentMap[this.deviceType]?.[this.component] || this.component;
    },
    isTouchDevice() {
      return 'ontouchstart' in window || navigator.maxTouchPoints > 0;
    },
  },
  mounted() {
    this.detectDevice();
    window.addEventListener('resize', this.handleResize);
  },
  beforeUnmount() {
    window.removeEventListener('resize', this.handleResize);
  },
  methods: {
    detectDevice() {
      this.screenWidth = window.innerWidth;
      
      if (this.screenWidth < 768) {
        this.deviceType = 'mobile';
      } else if (this.screenWidth < 1024) {
        this.deviceType = 'tablet';
      } else {
        this.deviceType = 'desktop';
      }
    },
    handleResize() {
      this.detectDevice();
    },
  },
};
</script>

<style scoped>
.responsive-layout {
  width: 100%;
  min-height: 100vh;
}

.layout-mobile {
  padding: 0.5rem;
}

.layout-tablet {
  padding: 1rem;
}

.layout-desktop {
  padding: 1.5rem;
}

.touch-friendly button,
.touch-friendly .btn {
  min-height: 44px;
  min-width: 44px;
  padding: 12px 16px;
}

@media (max-width: 576px) {
  .responsive-layout {
    font-size: 14px;
  }
}

@media (min-width: 577px) and (max-width: 768px) {
  .responsive-layout {
    font-size: 15px;
  }
}

@media (min-width: 769px) {
  .responsive-layout {
    font-size: 16px;
  }
}
</style>
