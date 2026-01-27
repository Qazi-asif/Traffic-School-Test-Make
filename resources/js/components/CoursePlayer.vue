<template>
  <div class="course-player">
    <div class="row">
      <div class="col-md-3">
        <div class="chapter-nav">
          <h5>Chapters</h5>
          <ul class="list-group">
            <li v-for="chapter in chapters" :key="chapter.id" 
                @click="selectChapter(chapter)"
                :class="['list-group-item', {active: currentChapter?.id === chapter.id}]">
              {{ chapter.title }}
              <span v-if="isChapterCompleted(chapter.id)" class="badge bg-success">✓</span>
            </li>
          </ul>
        </div>
      </div>
      
      <div class="col-md-9">
        <div v-if="currentChapter" class="chapter-content">
          <h3>{{ currentChapter.title }}</h3>
          <div class="progress mb-3">
            <div class="progress-bar" :style="{width: progressPercentage + '%'}">
              {{ progressPercentage }}%
            </div>
          </div>
          
          <div v-if="currentChapter.video_url" class="video-container mb-3">
            <video :src="currentChapter.video_url" controls width="100%" style="max-height: 400px;"></video>
          </div>
          
          <div class="chapter-text" v-html="currentChapter.content"></div>
          
          <div class="chapter-actions mt-4">
            <button @click="completeChapter" class="btn btn-success">Complete Chapter</button>
            <button @click="startQuiz" v-if="hasQuiz" class="btn btn-warning ms-2">Take Quiz</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      enrollmentId: null,
      chapters: [],
      currentChapter: null,
      progress: [],
      progressPercentage: 0
    }
  },
  computed: {
    hasQuiz() {
      return this.currentChapter?.questions?.length > 0
    }
  },
  methods: {
    async fetchCourseData() {
      try {
        this.enrollmentId = this.$route.params.enrollmentId || new URLSearchParams(window.location.search).get('enrollmentId')
        const response = await fetch(`/api/enrollments/${this.enrollmentId}/progress`)
        const data = await response.json()
        
        if (!data.enrollment) {
          console.error('No enrollment data:', data)
          alert('Error: Enrollment not found')
          return
        }
        
        this.chapters = data.chapters || []
        this.progress = data.progress || []
        this.progressPercentage = data.enrollment?.progress_percentage || 0
        
        if (this.chapters.length > 0) {
          this.selectChapter(this.chapters[0])
        } else {
          console.warn('No chapters available for this course')
        }
      } catch (error) {
        console.error('Error loading course data:', error)
        alert('Failed to load course data')
      }
    },
    selectChapter(chapter) {
      this.currentChapter = chapter
      this.startChapter()
    },
    async startChapter() {
      await fetch(`/api/enrollments/${this.enrollmentId}/start-chapter/${this.currentChapter.id}`, {
        method: 'POST'
      })
    },
    async completeChapter() {
      await fetch(`/api/enrollments/${this.enrollmentId}/complete-chapter/${this.currentChapter.id}`, {
        method: 'POST'
      })
      this.fetchCourseData()
    },
    isChapterCompleted(chapterId) {
      return this.progress.some(p => p.chapter_id === chapterId && p.is_completed)
    },
    startQuiz() {
      this.$router.push(`/quiz/${this.enrollmentId}/${this.currentChapter.id}`)
    }
  },
  mounted() {
    this.fetchCourseData()
  }
}
</script>
