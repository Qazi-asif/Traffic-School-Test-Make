<template>
  <div class="my-enrollments">
    <h2>My Course Enrollments</h2>
    <div v-if="enrollments.length === 0" class="text-center py-4">
      <p>No enrollments found. <a href="/courses">Browse courses</a> to get started.</p>
    </div>
    <div v-else class="row">
      <div v-for="enrollment in enrollments" :key="enrollment.id" class="col-md-6 mb-3">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">{{ enrollment.course.title }}</h5>
            <p class="card-text">{{ enrollment.course.description }}</p>
            <div class="progress mb-2">
              <div class="progress-bar" :style="{width: enrollment.progress_percentage + '%'}">
                {{ enrollment.progress_percentage }}%
              </div>
            </div>
            <p><strong>Status:</strong> {{ enrollment.status }}</p>
            <p><strong>Payment:</strong> {{ enrollment.payment_status }}</p>
            <div class="d-flex gap-2">
              <a :href="`/course-player?enrollmentId=${enrollment.id}`" class="btn btn-primary">Continue Course</a>
              <a :href="`/booklets/order/${enrollment.id}`" class="btn btn-outline-secondary" title="Order Course Booklet">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-book" viewBox="0 0 16 16">
                  <path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811V2.828zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492V2.687zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783z"/>
                </svg>
                Booklet
              </a>
            </div>
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
      enrollments: []
    }
  },
  methods: {
    async fetchEnrollments() {
      try {
        const response = await fetch('/web/my-enrollments', {
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
          },
          credentials: 'same-origin'
        })
        
        if (!response.ok) {
          if (response.status === 401) {
            window.location.href = '/login'
            return
          }
          throw new Error('Failed to fetch enrollments')
        }
        
        this.enrollments = await response.json()
      } catch (error) {
        console.error('Error fetching enrollments:', error)
      }
    }
  },
  mounted() {
    this.fetchEnrollments()
  }
}
</script>
