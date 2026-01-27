<template>
  <div class="chapter-builder">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Chapter Builder - {{ course.title }}</h2>
      <button class="btn btn-primary" @click="showCreateModal = true">
        <i class="fas fa-plus"></i> Add Chapter
      </button>
    </div>

    <div class="row">
      <div class="col-md-8">
        <div class="chapters-list">
          <div v-for="chapter in chapters" :key="chapter.id" class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h5>{{ chapter.title }}</h5>
              <div>
                <span class="badge bg-info me-2">{{ chapter.duration }} min</span>
                <button class="btn btn-sm btn-outline-primary me-1" @click="editChapter(chapter)">
                  <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-outline-success me-1" @click="manageQuestions(chapter)">
                  <i class="fas fa-question-circle"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" @click="deleteChapter(chapter)">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <small class="text-muted">Duration: {{ chapter.duration }} minutes</small><br>
                  <small class="text-muted">Min Time: {{ chapter.required_min_time }} minutes</small>
                </div>
                <div class="col-md-6">
                  <small class="text-muted">Order: {{ chapter.order_index }}</small><br>
                  <small :class="chapter.is_active ? 'text-success' : 'text-danger'">
                    {{ chapter.is_active ? 'Active' : 'Inactive' }}
                  </small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-md-4">
        <div class="card">
          <div class="card-header">
            <h5>Timer Settings</h5>
          </div>
          <div class="card-body">
            <div class="form-check mb-3">
              <input v-model="timerSettings.enabled" type="checkbox" class="form-check-input" id="timerEnabled">
              <label class="form-check-label" for="timerEnabled">Enable Chapter Timers</label>
            </div>
            <div class="form-check mb-3">
              <input v-model="timerSettings.enforce_min_time" type="checkbox" class="form-check-input" id="enforceMinTime">
              <label class="form-check-label" for="enforceMinTime">Enforce Minimum Time</label>
            </div>
            <button class="btn btn-success btn-sm" @click="saveTimerSettings">Save Settings</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Chapter Modal -->
    <div class="modal fade" :class="{ show: showCreateModal }" style="display: block" v-if="showCreateModal">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ editingChapter ? 'Edit' : 'Create' }} Chapter</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveChapter">
              <div class="row">
                <div class="col-md-6">
                  <label class="form-label">Title</label>
                  <input v-model="chapterForm.title" type="text" class="form-control" required>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Duration (minutes)</label>
                  <input v-model="chapterForm.duration" type="number" class="form-control" min="1" required>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Min Time (minutes)</label>
                  <input v-model="chapterForm.required_min_time" type="number" class="form-control" min="0" required>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label">Video URL (optional)</label>
                <input v-model="chapterForm.video_url" type="url" class="form-control">
              </div>
              <div class="mb-3">
                <label class="form-label">Content</label>
                <div class="content-editor">
                  <div class="editor-toolbar mb-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary me-1" @click="formatText('bold')">
                      <i class="fas fa-bold"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary me-1" @click="formatText('italic')">
                      <i class="fas fa-italic"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary me-1" @click="formatText('underline')">
                      <i class="fas fa-underline"></i>
                    </button>
                  </div>
                  <textarea v-model="chapterForm.content" class="form-control" rows="10" required></textarea>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <label class="form-label">Order Index</label>
                  <input v-model="chapterForm.order_index" type="number" class="form-control" min="1" required>
                </div>
                <div class="col-md-6">
                  <div class="form-check mt-4">
                    <input v-model="chapterForm.is_active" type="checkbox" class="form-check-input" id="chapterActive">
                    <label class="form-check-label" for="chapterActive">Active</label>
                  </div>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeModal">Cancel</button>
            <button type="button" class="btn btn-primary" @click="saveChapter">Save Chapter</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ChapterBuilder',
  props: ['courseId'],
  data() {
    return {
      course: {},
      chapters: [],
      showCreateModal: false,
      editingChapter: null,
      timerSettings: {
        enabled: true,
        enforce_min_time: true
      },
      chapterForm: {
        title: '',
        content: '',
        video_url: '',
        duration: 30,
        required_min_time: 15,
        order_index: 1,
        is_active: true
      }
    }
  },
  methods: {
    loadCourse() {
      // API call to load course
    },
    loadChapters() {
      // API call to load chapters
    },
    editChapter(chapter) {
      this.editingChapter = chapter;
      this.chapterForm = { ...chapter };
      this.showCreateModal = true;
    },
    manageQuestions(chapter) {
      this.$router.push(`/admin/chapters/${chapter.id}/questions`);
    },
    saveChapter() {
      // API call to save chapter
      this.closeModal();
    },
    deleteChapter(chapter) {
      if (confirm('Are you sure?')) {
        // API call to delete chapter
      }
    },
    saveTimerSettings() {
      // API call to save timer settings
    },
    formatText(command) {
      document.execCommand(command, false, null);
    },
    closeModal() {
      this.showCreateModal = false;
      this.editingChapter = null;
      this.chapterForm = {
        title: '',
        content: '',
        video_url: '',
        duration: 30,
        required_min_time: 15,
        order_index: 1,
        is_active: true
      };
    }
  },
  mounted() {
    this.loadCourse();
    this.loadChapters();
  }
}
</script>
