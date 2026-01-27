<template>
  <div class="certificate-manager">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Florida Certificate Management</h2>
      <button class="btn btn-primary" @click="showGenerateModal = true">
        <i class="fas fa-certificate"></i> Generate Certificate
      </button>
    </div>

    <div class="row mb-3">
      <div class="col-md-6">
        <input v-model="filters.search" type="text" class="form-control" placeholder="Search by name or certificate number...">
      </div>
      <div class="col-md-3">
        <select v-model="filters.status" class="form-select">
          <option value="">All Status</option>
          <option value="sent">Sent to Student</option>
          <option value="pending">Pending</option>
        </select>
      </div>
      <div class="col-md-3">
        <button class="btn btn-outline-secondary" @click="loadCertificates">
          <i class="fas fa-sync"></i> Refresh
        </button>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>Student Name</th>
            <th>DICDS Number</th>
            <th>Course</th>
            <th>Score</th>
            <th>Generated</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="certificate in certificates" :key="certificate.id">
            <td>{{ certificate.student_name }}</td>
            <td>{{ certificate.dicds_certificate_number }}</td>
            <td>{{ certificate.course_name }}</td>
            <td>{{ certificate.final_exam_score }}%</td>
            <td>{{ formatDate(certificate.generated_at) }}</td>
            <td>
              <span :class="certificate.is_sent_to_student ? 'badge bg-success' : 'badge bg-warning'">
                {{ certificate.is_sent_to_student ? 'Sent' : 'Pending' }}
              </span>
            </td>
            <td>
              <button class="btn btn-sm btn-outline-primary me-1" @click="viewCertificate(certificate)">
                <i class="fas fa-eye"></i>
              </button>
              <button class="btn btn-sm btn-outline-success me-1" @click="downloadCertificate(certificate.id)">
                <i class="fas fa-download"></i>
              </button>
              <button class="btn btn-sm btn-outline-info" @click="emailCertificate(certificate.id)" :disabled="certificate.is_sent_to_student">
                <i class="fas fa-envelope"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Generate Certificate Modal -->
    <div class="modal fade" :class="{ show: showGenerateModal }" style="display: block" v-if="showGenerateModal">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Generate Florida Certificate</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="generateCertificate">
              <div class="row">
                <div class="col-md-6">
                  <label class="form-label">Enrollment ID</label>
                  <input v-model="certificateForm.enrollment_id" type="number" class="form-control" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">DICDS Certificate Number</label>
                  <input v-model="certificateForm.dicds_certificate_number" type="text" class="form-control" required>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <label class="form-label">Final Exam Score (%)</label>
                  <input v-model="certificateForm.final_exam_score" type="number" min="0" max="100" class="form-control" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Citation Number</label>
                  <input v-model="certificateForm.citation_number" type="text" maxlength="7" class="form-control" required>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <label class="form-label">Citation County</label>
                  <input v-model="certificateForm.citation_county" type="text" class="form-control" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Traffic School Due Date</label>
                  <input v-model="certificateForm.traffic_school_due_date" type="date" class="form-control" required>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label">Court Name</label>
                <input v-model="certificateForm.court_name" type="text" class="form-control" required>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeModal">Cancel</button>
            <button type="button" class="btn btn-primary" @click="generateCertificate">Generate Certificate</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CertificateManager',
  data() {
    return {
      certificates: [],
      showGenerateModal: false,
      filters: {
        search: '',
        status: ''
      },
      certificateForm: {
        enrollment_id: '',
        dicds_certificate_number: '',
        final_exam_score: '',
        citation_number: '',
        citation_county: '',
        traffic_school_due_date: '',
        court_name: ''
      }
    }
  },
  methods: {
    loadCertificates() {
      // API call to load certificates
    },
    generateCertificate() {
      // API call to generate certificate
      this.closeModal();
    },
    viewCertificate(certificate) {
      // Show certificate details
    },
    downloadCertificate(id) {
      window.open(`/api/certificates/${id}/download`, '_blank');
    },
    emailCertificate(id) {
      // API call to email certificate
    },
    formatDate(date) {
      return new Date(date).toLocaleDateString();
    },
    closeModal() {
      this.showGenerateModal = false;
      this.certificateForm = {
        enrollment_id: '',
        dicds_certificate_number: '',
        final_exam_score: '',
        citation_number: '',
        citation_county: '',
        traffic_school_due_date: '',
        court_name: ''
      };
    }
  },
  mounted() {
    this.loadCertificates();
  }
}
</script>
