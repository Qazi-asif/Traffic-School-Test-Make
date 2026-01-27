<template>
  <div class="progress-tracker">
    <div class="progress-header mb-3">
      <h4>Course Progress</h4>
      <div class="progress-stats">
        <span class="badge bg-primary">{{ progressPercentage }}% Complete</span>
        <span class="badge bg-info ms-2">{{ timeSpentHours }} hours spent</span>
      </div>
    </div>
    
    <div class="overall-progress mb-4">
      <div class="progress" style="height: 20px;">
        <div 
          class="progress-bar" 
          :style="{width: progressPercentage + '%'}"
          :class="progressBarClass"
        >
          {{ progressPercentage }}%
        </div>
      </div>
    </div>
    
    <div class="chapters-progress">
      <h5>Chapter Progress</h5>
      <div v-for="chapter in chaptersProgress" :key="chapter.id" class="chapter-item mb-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <span class="chapter-title">{{ chapter.title }}</span>
          <div class="chapter-status">
            <i v-if="chapter.is_completed" class="fas fa-check-circle text-success"></i>
            <i v-else-if="chapter.started_at" class="fas fa-play-circle text-warning"></i>
            <i v-else class="fas fa-circle text-muted"></i>
          </div>
        </div>
        
        <div class="chapter-details">
          <small class="text-muted">
            <span v-if="chapter.time_spent">Time spent: {{ Math.floor(chapter.time_spent / 60) }}h {{ chapter.time_spent % 60 }}m</span>
            <span v-if="chapter.last_accessed_at" class="ms-2">Last accessed: {{ formatDate(chapter.last_accessed_at) }}</span>
          </small>
        </div>
        
        <div class="progress mt-1" style="height: 8px;">
          <div 
            class="progress-bar bg-info" 
            :style="{width: getChapterProgress(chapter) + '%'}"
          ></div>
        </div>
      </div>
    </div>
    
    <div v-if="quizAttempts.length > 0" class="quiz-attempts mt-4">
      <h5>Quiz Results</h5>
      <div class="table-responsive">
        <table class="table table-sm">
          <thead>
            <tr>
              <th>Chapter</th>
              <th>Score</th>
              <th>Status</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="attempt in quizAttempts" :key="attempt.id">
              <td>{{ attempt.chapter ? attempt.chapter.title : 'Final Exam' }}</td>
              <td>{{ attempt.score }}%</td>
              <td>
                <span :class="attempt.passed ? 'badge bg-success' : 'badge bg-danger'">
                  {{ attempt.passed ? 'Passed' : 'Failed' }}
                </span>
              </td>
              <td>{{ formatDate(attempt.attempted_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: ['enrollmentId'],
  data() {
    return {
      enrollment: null,
      chaptersProgress: [],
      quizAttempts: []
    }
  },
  computed: {
    progressPercentage() {
      return this.enrollment ? this.enrollment.progress_percentage : 0;
    },
    timeSpentHours() {
      return this.enrollment ? (this.enrollment.total_time_spent / 60).toFixed(1) : 0;
    },
    progressBarClass() {
      const percentage = this.progressPercentage;
      if (percentage >= 100) return 'bg-success';
      if (percentage >= 75) return 'bg-info';
      if (percentage >= 50) return 'bg-warning';
      return 'bg-danger';
    }
  },
  methods: {
    async fetchProgress() {
      try {
        const response = await fetch(`/api/enrollments/${this.enrollmentId}/progress`, {
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
          },
          credentials: 'same-origin'
        });
        
        if (response.ok) {
          const data = await response.json();
          this.enrollment = data.enrollment;
          this.chaptersProgress = data.chapters_progress;
          this.quizAttempts = data.quiz_attempts;
        }
      } catch (error) {
        console.error('Error fetching progress:', error);
      }
    },
    
    getChapterProgress(chapter) {
      if (chapter.is_completed) return 100;
      if (chapter.started_at) return 50;
      return 0;
    },
    
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString();
    }
  },
  
  mounted() {
    this.fetchProgress();
  }
}
</script>

<style scoped>
.chapter-item {
  border: 1px solid #e9ecef;
  border-radius: 0.375rem;
  padding: 0.75rem;
}

.chapter-title {
  font-weight: 500;
}

.chapter-status i {
  font-size: 1.2em;
}
</style>
