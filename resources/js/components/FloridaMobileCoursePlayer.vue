<template>
  <div class="florida-mobile-course-player" :class="{ 'mobile-view': isMobile }">
    <div class="course-header">
      <h3>{{ course.title }}</h3>
      <div class="progress-bar">
        <div class="progress-fill" :style="{ width: progress + '%' }"></div>
      </div>
    </div>
    
    <div class="course-content" @touchstart="handleTouchStart" @touchend="handleTouchEnd">
      <div v-if="currentChapter" class="chapter-content">
        <h4>{{ currentChapter.title }}</h4>
        <div v-html="currentChapter.content"></div>
      </div>
    </div>
    
    <div class="course-navigation">
      <button @click="previousChapter" :disabled="currentChapterIndex === 0" class="nav-btn">
        <i class="fas fa-chevron-left"></i> Previous
      </button>
      <span class="chapter-indicator">{{ currentChapterIndex + 1 }} / {{ chapters.length }}</span>
      <button @click="nextChapter" :disabled="currentChapterIndex === chapters.length - 1" class="nav-btn">
        Next <i class="fas fa-chevron-right"></i>
      </button>
    </div>
    
    <div v-if="showQuiz" class="quiz-section">
      <div class="quiz-question">
        <h5>{{ currentQuiz.question }}</h5>
        <div class="quiz-options">
          <button v-for="(option, index) in currentQuiz.options" :key="index" 
                  @click="selectAnswer(index)" 
                  :class="{ 'selected': selectedAnswer === index }"
                  class="quiz-option">
            {{ option }}
          </button>
        </div>
        <button @click="submitAnswer" :disabled="selectedAnswer === null" class="submit-btn">
          Submit Answer
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FloridaMobileCoursePlayer',
  props: {
    courseId: {
      type: [String, Number],
      required: true
    }
  },
  data() {
    return {
      course: {},
      chapters: [],
      currentChapterIndex: 0,
      progress: 0,
      showQuiz: false,
      currentQuiz: null,
      selectedAnswer: null,
      touchStartX: 0,
      touchEndX: 0,
      isMobile: window.innerWidth < 768
    }
  },
  computed: {
    currentChapter() {
      return this.chapters[this.currentChapterIndex] || null
    }
  },
  mounted() {
    this.loadCourse()
    this.trackCourseAccess()
    window.addEventListener('resize', this.handleResize)
  },
  beforeUnmount() {
    window.removeEventListener('resize', this.handleResize)
  },
  methods: {
    async loadCourse() {
      try {
        const response = await axios.get(`/api/florida-mobile/course/${this.courseId}`)
        this.course = response.data
        this.chapters = [
          { title: 'Chapter 1: Florida Traffic Laws', content: 'Content for chapter 1...' },
          { title: 'Chapter 2: Safe Driving Practices', content: 'Content for chapter 2...' },
          { title: 'Chapter 3: Defensive Driving', content: 'Content for chapter 3...' }
        ]
        this.updateProgress()
      } catch (error) {
        console.error('Error loading course:', error)
      }
    },
    async trackCourseAccess() {
      try {
        await axios.post('/api/florida-mobile/track-activity', {
          course_id: this.courseId,
          action: 'course_started',
          metrics: {
            device_type: this.isMobile ? 'mobile' : 'desktop',
            screen_width: window.innerWidth
          }
        })
      } catch (error) {
        console.error('Error tracking activity:', error)
      }
    },
    nextChapter() {
      if (this.currentChapterIndex < this.chapters.length - 1) {
        this.currentChapterIndex++
        this.updateProgress()
        this.trackActivity('chapter_completed')
      }
    },
    previousChapter() {
      if (this.currentChapterIndex > 0) {
        this.currentChapterIndex--
        this.updateProgress()
      }
    },
    updateProgress() {
      this.progress = ((this.currentChapterIndex + 1) / this.chapters.length) * 100
    },
    handleTouchStart(e) {
      this.touchStartX = e.changedTouches[0].screenX
    },
    handleTouchEnd(e) {
      this.touchEndX = e.changedTouches[0].screenX
      this.handleSwipe()
    },
    handleSwipe() {
      const swipeThreshold = 50
      const diff = this.touchStartX - this.touchEndX
      
      if (Math.abs(diff) > swipeThreshold) {
        if (diff > 0) {
          // Swipe left - next chapter
          this.nextChapter()
        } else {
          // Swipe right - previous chapter
          this.previousChapter()
        }
      }
    },
    selectAnswer(index) {
      this.selectedAnswer = index
    },
    submitAnswer() {
      this.trackActivity('quiz_answered')
      this.showQuiz = false
      this.selectedAnswer = null
    },
    async trackActivity(action) {
      try {
        await axios.post('/api/florida-mobile/track-activity', {
          course_id: this.courseId,
          action: action,
          metrics: {
            chapter_index: this.currentChapterIndex,
            progress: this.progress
          }
        })
      } catch (error) {
        console.error('Error tracking activity:', error)
      }
    },
    handleResize() {
      this.isMobile = window.innerWidth < 768
    }
  }
}
</script>

<style scoped>
.florida-mobile-course-player {
  max-width: 100%;
  margin: 0 auto;
}

.mobile-view {
  padding: 1rem;
}

.course-header {
  margin-bottom: 2rem;
}

.progress-bar {
  width: 100%;
  height: 8px;
  background: #e9ecef;
  border-radius: 4px;
  overflow: hidden;
  margin-top: 1rem;
}

.progress-fill {
  height: 100%;
  background: #007bff;
  transition: width 0.3s ease;
}

.course-navigation {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin: 2rem 0;
}

.nav-btn {
  background: #007bff;
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 4px;
  min-height: 44px;
  min-width: 44px;
}

.nav-btn:disabled {
  background: #6c757d;
}

.quiz-option {
  display: block;
  width: 100%;
  margin: 0.5rem 0;
  padding: 1rem;
  background: #f8f9fa;
  border: 2px solid #dee2e6;
  border-radius: 4px;
  min-height: 44px;
}

.quiz-option.selected {
  background: #e3f2fd;
  border-color: #007bff;
}

.submit-btn {
  background: #516425;
  color: white;
  border: none;
  padding: 0.75rem 2rem;
  border-radius: 4px;
  margin-top: 1rem;
  min-height: 44px;
}
</style>
