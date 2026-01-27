<template>
  <div class="course-preview copyright-protected" @contextmenu.prevent @selectstart.prevent @dragstart.prevent>
    <div class="course-header mb-4">
      <h2>{{ course.title }}</h2>
      <div class="course-info">
        <span class="badge bg-info me-2">{{ course.course_type }}</span>
        <span class="badge bg-secondary me-2">{{ course.delivery_type }}</span>
        <span class="badge bg-success">{{ course.total_duration }} minutes</span>
      </div>
    </div>

    <div class="row">
      <div class="col-md-3">
        <div class="chapters-sidebar">
          <h5>Chapters</h5>
          <div class="list-group">
            <a 
              v-for="chapter in chapters" 
              :key="chapter.id"
              href="#" 
              class="list-group-item list-group-item-action"
              :class="{ active: currentChapter?.id === chapter.id }"
              @click.prevent="selectChapter(chapter)"
            >
              <div class="d-flex justify-content-between align-items-center">
                <span>{{ chapter.title }}</span>
                <small class="text-muted">{{ chapter.duration }}m</small>
              </div>
            </a>
          </div>
        </div>
      </div>
      
      <div class="col-md-9">
        <div v-if="currentChapter" class="chapter-content">
          <div class="chapter-header d-flex justify-content-between align-items-center mb-3">
            <h4>{{ currentChapter.title }}</h4>
            <div class="timer-display" v-if="timerEnabled">
              <i class="fas fa-clock"></i>
              <span class="ms-1">{{ formatTime(chapterTimer) }}</span>
            </div>
          </div>

          <div v-if="currentChapter.video_url" class="video-container mb-4">
            <video 
              :src="currentChapter.video_url" 
              controls 
              class="w-100"
              @contextmenu.prevent
              controlslist="nodownload"
            ></video>
          </div>

          <div class="chapter-text" v-html="currentChapter.content"></div>

          <div class="chapter-navigation mt-4">
            <button 
              class="btn btn-outline-secondary me-2" 
              @click="previousChapter"
              :disabled="!canGoPrevious"
            >
              <i class="fas fa-chevron-left"></i> Previous
            </button>
            
            <button 
              class="btn btn-primary" 
              @click="nextChapter"
              :disabled="!canGoNext || !chapterCompleted"
            >
              Next <i class="fas fa-chevron-right"></i>
            </button>
          </div>

          <div v-if="timerEnabled && !chapterCompleted" class="timer-warning mt-3">
            <div class="alert alert-warning">
              <i class="fas fa-clock"></i>
              You must spend at least {{ currentChapter.required_min_time }} minutes on this chapter.
              <div class="progress mt-2">
                <div 
                  class="progress-bar" 
                  :style="{ width: timerProgress + '%' }"
                ></div>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-5">
          <i class="fas fa-book fa-3x text-muted mb-3"></i>
          <h5 class="text-muted">Select a chapter to begin</h5>
        </div>
      </div>
    </div>

    <!-- Copyright Protection Overlay -->
    <div class="copyright-overlay" v-if="showCopyrightWarning">
      <div class="alert alert-danger">
        <i class="fas fa-shield-alt"></i>
        Content is copyright protected. Unauthorized copying is prohibited.
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CoursePreview',
  props: ['courseId'],
  data() {
    return {
      course: {},
      chapters: [],
      currentChapter: null,
      chapterTimer: 0,
      timerEnabled: true,
      timerInterval: null,
      showCopyrightWarning: false
    }
  },
  computed: {
    chapterCompleted() {
      if (!this.timerEnabled || !this.currentChapter) return true;
      return this.chapterTimer >= (this.currentChapter.required_min_time * 60);
    },
    timerProgress() {
      if (!this.currentChapter) return 0;
      const required = this.currentChapter.required_min_time * 60;
      return Math.min((this.chapterTimer / required) * 100, 100);
    },
    canGoPrevious() {
      if (!this.currentChapter) return false;
      const currentIndex = this.chapters.findIndex(c => c.id === this.currentChapter.id);
      return currentIndex > 0;
    },
    canGoNext() {
      if (!this.currentChapter) return false;
      const currentIndex = this.chapters.findIndex(c => c.id === this.currentChapter.id);
      return currentIndex < this.chapters.length - 1;
    }
  },
  methods: {
    loadCourse() {
      // API call to load course
    },
    loadChapters() {
      // API call to load chapters
    },
    selectChapter(chapter) {
      this.currentChapter = chapter;
      this.resetTimer();
      this.startTimer();
    },
    previousChapter() {
      const currentIndex = this.chapters.findIndex(c => c.id === this.currentChapter.id);
      if (currentIndex > 0) {
        this.selectChapter(this.chapters[currentIndex - 1]);
      }
    },
    nextChapter() {
      const currentIndex = this.chapters.findIndex(c => c.id === this.currentChapter.id);
      if (currentIndex < this.chapters.length - 1) {
        this.selectChapter(this.chapters[currentIndex + 1]);
      }
    },
    startTimer() {
      if (this.timerEnabled) {
        this.timerInterval = setInterval(() => {
          this.chapterTimer++;
        }, 1000);
      }
    },
    resetTimer() {
      this.chapterTimer = 0;
      if (this.timerInterval) {
        clearInterval(this.timerInterval);
      }
    },
    formatTime(seconds) {
      const minutes = Math.floor(seconds / 60);
      const secs = seconds % 60;
      return `${minutes}:${secs.toString().padStart(2, '0')}`;
    },
    detectCopyAttempt() {
      this.showCopyrightWarning = true;
      setTimeout(() => {
        this.showCopyrightWarning = false;
      }, 3000);
    }
  },
  mounted() {
    this.loadCourse();
    this.loadChapters();
    
    // Copyright protection
    document.addEventListener('keydown', (e) => {
      if (e.ctrlKey && (e.key === 'c' || e.key === 'a' || e.key === 's')) {
        e.preventDefault();
        this.detectCopyAttempt();
      }
    });
  },
  beforeUnmount() {
    if (this.timerInterval) {
      clearInterval(this.timerInterval);
    }
  }
}
</script>

<style scoped>
.copyright-protected {
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}

.copyright-overlay {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 1000;
}

.timer-display {
  font-family: monospace;
  font-size: 1.2em;
  color: #007bff;
}

.chapter-content {
  min-height: 400px;
}
</style>
