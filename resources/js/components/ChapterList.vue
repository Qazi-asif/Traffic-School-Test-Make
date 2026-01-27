<template>
  <div class="chapter-list">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3>Course Chapters</h3>
      <button @click="showAddForm = true" class="btn btn-primary">Add Chapter</button>
    </div>
    
    <div v-if="showAddForm" class="card mb-3">
      <div class="card-body">
        <h5>Add New Chapter</h5>
        <form @submit.prevent="addChapter">
          <div class="mb-3">
            <label>Title</label>
            <input v-model="newChapter.title" type="text" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Content</label>
            <textarea v-model="newChapter.content" class="form-control" rows="4" required></textarea>
          </div>
          <div class="mb-3">
            <label>Duration (minutes)</label>
            <input v-model.number="newChapter.duration" type="number" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Video URL</label>
            <input v-model="newChapter.video_url" type="url" class="form-control">
          </div>
          <button type="submit" class="btn btn-success me-2">Add Chapter</button>
          <button type="button" @click="showAddForm = false" class="btn btn-secondary">Cancel</button>
        </form>
      </div>
    </div>
    
    <div class="chapters">
      <div v-for="(chapter, index) in chapters" :key="chapter.id" class="card mb-2">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h6>{{ index + 1 }}. {{ chapter.title }}</h6>
              <small class="text-muted">{{ chapter.duration }} minutes</small>
            </div>
            <div>
              <button @click="editChapter(chapter)" class="btn btn-sm btn-outline-primary me-2">Edit</button>
              <button @click="deleteChapter(chapter.id)" class="btn btn-sm btn-outline-danger">Delete</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: ['courseId'],
  data() {
    return {
      chapters: [],
      showAddForm: false,
      newChapter: {
        title: '',
        content: '',
        duration: 0,
        video_url: '',
        order_index: 1
      }
    }
  },
  methods: {
    async fetchChapters() {
      try {
        const response = await fetch(`/api/courses/${this.courseId}/chapters`, {
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
          },
          credentials: 'same-origin'
        });
        
        if (response.ok) {
          this.chapters = await response.json();
        }
      } catch (error) {
        console.error('Error fetching chapters:', error);
      }
    },
    
    async addChapter() {
      try {
        this.newChapter.order_index = this.chapters.length + 1;
        
        const response = await fetch(`/api/courses/${this.courseId}/chapters`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
          },
          credentials: 'same-origin',
          body: JSON.stringify(this.newChapter)
        });
        
        if (response.ok) {
          this.fetchChapters();
          this.showAddForm = false;
          this.newChapter = { title: '', content: '', duration: 0, video_url: '', order_index: 1 };
        }
      } catch (error) {
        console.error('Error adding chapter:', error);
      }
    },
    
    async deleteChapter(chapterId) {
      if (confirm('Are you sure you want to delete this chapter?')) {
        try {
          const response = await fetch(`/api/chapters/${chapterId}`, {
            method: 'DELETE',
            headers: {
              'Accept': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            },
            credentials: 'same-origin'
          });
          
          if (response.ok) {
            this.fetchChapters();
          }
        } catch (error) {
          console.error('Error deleting chapter:', error);
        }
      }
    },
    
    editChapter(chapter) {
      // Implement edit functionality
      console.log('Edit chapter:', chapter);
    }
  },
  
  mounted() {
    this.fetchChapters();
  }
}
</script>
