<template>
  <div class="florida-course-manager">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Florida Course Management</h2>
      <button class="btn btn-primary" @click="showCreateModal = true">
        <i class="fas fa-plus"></i> Create Course
      </button>
    </div>

    <div class="row mb-3">
      <div class="col-md-4">
        <select v-model="filters.course_type" class="form-select">
          <option value="">All Course Types</option>
          <option value="BDI">BDI</option>
          <option value="ADI">ADI</option>
          <option value="TLSAE">TLSAE</option>
        </select>
      </div>
      <div class="col-md-4">
        <select v-model="filters.delivery_type" class="form-select">
          <option value="">All Delivery Types</option>
          <option value="internet">Internet</option>
          <option value="in_person">In Person</option>
          <option value="cd_rom">CD ROM</option>
          <option value="video">Video</option>
          <option value="dvd">DVD</option>
        </select>
      </div>
      <div class="col-md-4">
        <input v-model="filters.search" type="text" class="form-control" placeholder="Search courses...">
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>Title</th>
            <th>Type</th>
            <th>Delivery</th>
            <th>Duration</th>
            <th>Price</th>
            <th>DICDS ID</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="course in courses" :key="course.id">
            <td>{{ course.title }}</td>
            <td><span class="badge bg-info">{{ course.course_type }}</span></td>
            <td>{{ course.delivery_type }}</td>
            <td>{{ course.total_duration }} min</td>
            <td>${{ course.price }}</td>
            <td>{{ course.dicds_course_id }}</td>
            <td>
              <span :class="course.is_active ? 'badge bg-success' : 'badge bg-danger'">
                {{ course.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td>
              <button class="btn btn-sm btn-outline-primary me-1" @click="editCourse(course)">
                <i class="fas fa-edit"></i>
              </button>
              <button class="btn btn-sm btn-outline-success me-1" @click="manageChapters(course)">
                <i class="fas fa-book"></i>
              </button>
              <button class="btn btn-sm btn-outline-danger" @click="deleteCourse(course)">
                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal fade" :class="{ show: showCreateModal }" style="display: block" v-if="showCreateModal">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ editingCourse ? 'Edit' : 'Create' }} Florida Course</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveCourse">
              <div class="row">
                <div class="col-md-6">
                  <label class="form-label">Course Type</label>
                  <select v-model="form.course_type" class="form-select" required>
                    <option value="BDI">BDI</option>
                    <option value="ADI">ADI</option>
                    <option value="TLSAE">TLSAE</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Delivery Type</label>
                  <select v-model="form.delivery_type" class="form-select" required>
                    <option value="internet">Internet</option>
                    <option value="in_person">In Person</option>
                    <option value="cd_rom">CD ROM</option>
                    <option value="video">Video</option>
                    <option value="dvd">DVD</option>
                  </select>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label">Title</label>
                <input v-model="form.title" type="text" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea v-model="form.description" class="form-control" rows="3"></textarea>
              </div>
              <div class="row">
                <div class="col-md-4">
                  <label class="form-label">Duration (minutes)</label>
                  <input v-model="form.total_duration" type="number" class="form-control" min="240" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Min Pass Score (%)</label>
                  <input v-model="form.min_pass_score" type="number" class="form-control" min="0" max="100" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Price ($)</label>
                  <input v-model="form.price" type="number" step="0.01" class="form-control" required>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label">DICDS Course ID</label>
                <input v-model="form.dicds_course_id" type="text" class="form-control" required>
              </div>
              <div class="form-check">
                <input v-model="form.is_active" type="checkbox" class="form-check-input" id="isActive">
                <label class="form-check-label" for="isActive">Active</label>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeModal">Cancel</button>
            <button type="button" class="btn btn-primary" @click="saveCourse">Save</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FloridaCourseManager',
  data() {
    return {
      courses: [],
      showCreateModal: false,
      editingCourse: null,
      filters: {
        course_type: '',
        delivery_type: '',
        search: ''
      },
      form: {
        course_type: 'BDI',
        delivery_type: 'internet',
        title: '',
        description: '',
        total_duration: 240,
        min_pass_score: 80,
        price: 0,
        dicds_course_id: '',
        is_active: true
      }
    }
  },
  methods: {
    loadCourses() {
      // API call to load courses
    },
    editCourse(course) {
      this.editingCourse = course;
      this.form = { ...course };
      this.showCreateModal = true;
    },
    manageChapters(course) {
      this.$router.push(`/admin/florida-courses/${course.id}/chapters`);
    },
    saveCourse() {
      // API call to save course
      this.closeModal();
    },
    deleteCourse(course) {
      if (confirm('Are you sure?')) {
        // API call to delete course
      }
    },
    closeModal() {
      this.showCreateModal = false;
      this.editingCourse = null;
      this.form = {
        course_type: 'BDI',
        delivery_type: 'internet',
        title: '',
        description: '',
        total_duration: 240,
        min_pass_score: 80,
        price: 0,
        dicds_course_id: '',
        is_active: true
      };
    }
  },
  mounted() {
    this.loadCourses();
  }
}
</script>
