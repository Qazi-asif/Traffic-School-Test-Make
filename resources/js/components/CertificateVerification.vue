<template>
  <div class="certificate-verification">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-8">
          <div class="card">
            <div class="card-header text-center">
              <h3><i class="fas fa-shield-alt text-primary"></i> Florida Certificate Verification</h3>
            </div>
            <div class="card-body">
              <form @submit.prevent="verifyCertificate">
                <div class="mb-3">
                  <label class="form-label">Certificate Number</label>
                  <input v-model="certificateNumber" type="text" class="form-control" placeholder="Enter DICDS certificate number" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Verified By (Optional)</label>
                  <input v-model="verifiedBy" type="text" class="form-control" placeholder="Your name or organization">
                </div>
                <button type="submit" class="btn btn-primary w-100" :disabled="loading">
                  <i class="fas fa-search"></i> {{ loading ? 'Verifying...' : 'Verify Certificate' }}
                </button>
              </form>

              <!-- Verification Result -->
              <div v-if="verificationResult" class="mt-4">
                <div v-if="verificationResult.valid" class="alert alert-success">
                  <h5><i class="fas fa-check-circle"></i> Certificate Valid</h5>
                  <div class="row">
                    <div class="col-md-6">
                      <strong>Student Name:</strong> {{ verificationResult.certificate.student_name }}<br>
                      <strong>Course:</strong> {{ verificationResult.certificate.course_name }}<br>
                      <strong>Completion Date:</strong> {{ formatDate(verificationResult.certificate.completion_date) }}
                    </div>
                    <div class="col-md-6">
                      <strong>Certificate Number:</strong> {{ verificationResult.certificate.dicds_certificate_number }}<br>
                      <strong>Final Score:</strong> {{ verificationResult.certificate.final_exam_score }}%<br>
                      <strong>Status:</strong> <span class="badge bg-success">Valid</span>
                    </div>
                  </div>
                </div>
                <div v-else class="alert alert-danger">
                  <h5><i class="fas fa-times-circle"></i> Certificate Not Found</h5>
                  <p>The certificate number you entered could not be verified. Please check the number and try again.</p>
                </div>
              </div>

              <!-- Information -->
              <div class="mt-4">
                <h6>About Certificate Verification</h6>
                <ul class="small text-muted">
                  <li>This system verifies certificates issued by Florida-approved traffic schools</li>
                  <li>Enter the DICDS certificate number exactly as shown on the certificate</li>
                  <li>All verification attempts are logged for security purposes</li>
                  <li>For questions, contact the issuing traffic school directly</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CertificateVerification',
  data() {
    return {
      certificateNumber: '',
      verifiedBy: '',
      loading: false,
      verificationResult: null
    }
  },
  methods: {
    async verifyCertificate() {
      this.loading = true;
      this.verificationResult = null;

      try {
        const response = await fetch('/api/certificates/verify', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            certificate_number: this.certificateNumber,
            verified_by: this.verifiedBy
          })
        });

        this.verificationResult = await response.json();
      } catch (error) {
        this.verificationResult = { valid: false };
      } finally {
        this.loading = false;
      }
    },
    formatDate(date) {
      return new Date(date).toLocaleDateString();
    }
  }
}
</script>
