<template>
  <div class="course-list">
    <div class="filters mb-4">
      <input v-model="search" placeholder="Search courses..." class="form-control mb-2">
      <select v-model="stateFilter" class="form-select">
        <option value="">All States</option>
        <option value="FL">Florida</option>
        <option value="CA">California</option>
        <option value="TX">Texas</option>
        <option value="MO">Missouri</option>
        <option value="DE">Delaware</option>
      </select>
    </div>
    
    <div v-if="loading" class="text-center">
      <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2">Loading courses...</p>
    </div>
    
    <div v-else-if="filteredCourses.length === 0" class="text-center">
      <p>No courses available.</p>
    </div>
    
    <div v-else class="row">
      <div v-for="course in filteredCourses" :key="course.id" class="col-lg-4 col-md-6 mb-4">
        <div class="card h-100">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title">{{ course.title }}</h5>
            <p class="card-text flex-grow-1">{{ course.description }}</p>
            <div class="course-details mb-3">
              <p class="mb-1"><strong>State:</strong> {{ course.state_code }}</p>
              <p class="mb-1"><strong>Duration:</strong> {{ course.total_duration || course.duration }} minutes</p>
              <p class="mb-1"><strong>Price:</strong> ${{ course.price }}</p>
            </div>
            <button @click="enrollCourse(course.id, course.table)" class="btn btn-primary mt-auto">Enroll</button>
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
      courses: [],
      search: '',
      stateFilter: '',
      loading: true
    }
  },
  computed: {
    filteredCourses() {
      return this.courses.filter(course => {
        const matchesSearch = course.title.toLowerCase().includes(this.search.toLowerCase())
        const matchesState = !this.stateFilter || course.state_code === this.stateFilter
        return matchesSearch && matchesState && course.is_active
      })
    }
  },
  methods: {
    async fetchCourses() {
      try {
        this.loading = true
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        
        const headers = {
          'Accept': 'application/json'
        }
        
        if (csrfToken) {
          headers['X-CSRF-TOKEN'] = csrfToken
        }
        
        const response = await fetch('/web/courses', {
          headers: headers,
          credentials: 'same-origin'
        })
        
        if (!response.ok) {
          if (response.status === 401) {
            window.location.href = '/login'
            return
          }
          throw new Error('Failed to fetch courses')
        }
        
        this.courses = await response.json()
      } catch (error) {
        console.error('Error fetching courses:', error)
      } finally {
        this.loading = false
      }
    },
    async enrollCourse(courseId, table) {
      try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        
        const headers = {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
        
        if (csrfToken) {
          headers['X-CSRF-TOKEN'] = csrfToken
        }
        
        const response = await fetch('/web/enrollments', {
          method: 'POST',
          headers: headers,
          credentials: 'same-origin',
          body: JSON.stringify({ course_id: courseId, table: table })
        })
        
        if (!response.ok) {
          if (response.status === 401) {
            window.location.href = '/login'
            return
          }
          const data = await response.json()
          throw new Error(data.error || 'Failed to enroll')
        }
        
        alert('Enrolled successfully!')
      } catch (error) {
        console.error('Error enrolling:', error)
        alert(error.message || 'Failed to enroll in course')
      }
    }
  },
  mounted() {
    this.fetchCourses()
  }
}
</script>
