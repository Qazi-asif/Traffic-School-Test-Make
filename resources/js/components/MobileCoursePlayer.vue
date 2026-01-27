<template>
  <div class="mobile-course-player" @touchstart="handleTouchStart" @touchend="handleTouchEnd">
    <div class="course-header">
      <button @click="goBack" class="back-btn" aria-label="Go back">
        <i class="fas fa-arrow-left"></i>
      </button>
      <h1>{{ course.title }}</h1>
      <button @click="toggleFullscreen" class="fullscreen-btn" aria-label="Toggle fullscreen">
        <i class="fas fa-expand"></i>
      </button>
    </div>

    <div class="progress-container">
      <div class="progress-bar">
        <div class="progress-fill" :style="{ width: progressPercentage + '%' }"></div>
      </div>
      <span class="progress-text">{{ Math.round(progressPercentage) }}% Complete</span>
    </div>

    <div class="content-area" ref="contentArea">
      <div v-if="currentContent.type === 'video'" class="video-container">
        <video 
          ref="videoPlayer"
          :src="currentContent.url"
          controls
          playsinline
          @ended="markContentComplete"
        ></video>
      </div>
      
      <div v-else-if="currentContent.type === 'text'" class="text-content">
        <div v-html="currentContent.content"></div>
      </div>
      
      <div v-else-if="currentContent.type === 'quiz'" class="quiz-container">
        <h3>{{ currentContent.question }}</h3>
        <div class="quiz-options">
          <button 
            v-for="(option, index) in currentContent.options"
            :key="index"
            @click="selectAnswer(index)"
            :class="{ 'selected': selectedAnswer === index }"
            class="quiz-option"
          >
            {{ option }}
          </button>
        </div>
        <button @click="submitAnswer" :disabled="selectedAnswer === null" class="submit-btn">
          Submit Answer
        </button>
      </div>
    </div>

    <div class="navigation-controls">
      <button @click="previousContent" :disabled="currentIndex === 0" class="nav-btn">
        <i class="fas fa-chevron-left"></i> Previous
      </button>
      <span class="content-counter">{{ currentIndex + 1 }} / {{ course.contents.length }}</span>
      <button @click="nextContent" :disabled="currentIndex === course.contents.length - 1" class="nav-btn">
        Next <i class="fas fa-chevron-right"></i>
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'MobileCoursePlayer',
  props: {
    course: Object,
  },
  data() {
    return {
      currentIndex: 0,
      progressPercentage: 0,
      selectedAnswer: null,
      touchStartX: 0,
      touchEndX: 0,
    };
  },
  computed: {
    currentContent() {
      return this.course.contents[this.currentIndex] || {};
    },
  },
  methods: {
    goBack() {
      this.$router.go(-1);
    },
    toggleFullscreen() {
      if (this.$refs.contentArea.requestFullscreen) {
        this.$refs.contentArea.requestFullscreen();
      }
    },
    previousContent() {
      if (this.currentIndex > 0) {
        this.currentIndex--;
        this.updateProgress();
      }
    },
    nextContent() {
      if (this.currentIndex < this.course.contents.length - 1) {
        this.currentIndex++;
        this.updateProgress();
      }
    },
    updateProgress() {
      this.progressPercentage = ((this.currentIndex + 1) / this.course.contents.length) * 100;
    },
    markContentComplete() {
      // Mark current content as completed
      this.nextContent();
    },
    selectAnswer(index) {
      this.selectedAnswer = index;
    },
    submitAnswer() {
      // Handle quiz answer submission
      this.nextContent();
    },
    handleTouchStart(e) {
      this.touchStartX = e.changedTouches[0].screenX;
    },
    handleTouchEnd(e) {
      this.touchEndX = e.changedTouches[0].screenX;
      this.handleSwipe();
    },
    handleSwipe() {
      const swipeThreshold = 50;
      const diff = this.touchStartX - this.touchEndX;
      
      if (Math.abs(diff) > swipeThreshold) {
        if (diff > 0) {
          // Swipe left - next content
          this.nextContent();
        } else {
          // Swipe right - previous content
          this.previousContent();
        }
      }
    },
  },
  mounted() {
    this.updateProgress();
  },
};
</script>

<style scoped>
.mobile-course-player {
  display: flex;
  flex-direction: column;
  height: 100vh;
  background: #f8f9fa;
}

.course-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem;
  background: #007bff;
  color: white;
}

.back-btn, .fullscreen-btn {
  background: none;
  border: none;
  color: white;
  font-size: 1.2rem;
  padding: 0.5rem;
  min-height: 44px;
  min-width: 44px;
}

.progress-container {
  padding: 1rem;
  background: white;
  border-bottom: 1px solid #dee2e6;
}

.progress-bar {
  width: 100%;
  height: 8px;
  background: #e9ecef;
  border-radius: 4px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: #516425;
  transition: width 0.3s ease;
}

.progress-text {
  display: block;
  text-align: center;
  margin-top: 0.5rem;
  font-size: 0.9rem;
  color: #6c757d;
}

.content-area {
  flex: 1;
  padding: 1rem;
  overflow-y: auto;
}

.video-container video {
  width: 100%;
  height: auto;
  border-radius: 8px;
}

.text-content {
  line-height: 1.6;
  font-size: 1.1rem;
}

.quiz-container h3 {
  margin-bottom: 1rem;
  font-size: 1.2rem;
}

.quiz-options {
  margin-bottom: 1rem;
}

.quiz-option {
  display: block;
  width: 100%;
  padding: 1rem;
  margin-bottom: 0.5rem;
  background: white;
  border: 2px solid #dee2e6;
  border-radius: 8px;
  text-align: left;
  cursor: pointer;
  min-height: 44px;
}

.quiz-option.selected {
  border-color: #007bff;
  background: #e3f2fd;
}

.submit-btn {
  width: 100%;
  padding: 1rem;
  background: #007bff;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 1.1rem;
  min-height: 44px;
}

.navigation-controls {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem;
  background: white;
  border-top: 1px solid #dee2e6;
}

.nav-btn {
  padding: 0.75rem 1rem;
  background: #007bff;
  color: white;
  border: none;
  border-radius: 6px;
  min-height: 44px;
}

.nav-btn:disabled {
  background: #6c757d;
  cursor: not-allowed;
}

.content-counter {
  font-weight: 500;
  color: #6c757d;
}
</style>
