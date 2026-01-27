<template>
  <div class="password-strength-meter">
    <div class="progress mb-2" style="height: 8px;">
      <div 
        class="progress-bar" 
        :class="strengthClass"
        :style="{ width: strengthPercentage + '%' }"
      ></div>
    </div>
    <div class="strength-text">
      <small :class="strengthTextClass">{{ strengthText }}</small>
    </div>
    <div v-if="requirements.length" class="requirements mt-2">
      <small class="text-muted">Password must contain:</small>
      <ul class="list-unstyled small">
        <li v-for="req in requirements" :key="req.text" :class="req.met ? 'text-success' : 'text-danger'">
          <i :class="req.met ? 'fas fa-check' : 'fas fa-times'"></i>
          {{ req.text }}
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PasswordStrengthMeter',
  props: {
    password: {
      type: String,
      default: '',
    },
  },
  computed: {
    strength() {
      if (!this.password) return 0;
      
      let score = 0;
      
      // Length check
      if (this.password.length >= 8) score += 20;
      if (this.password.length >= 12) score += 10;
      
      // Character variety
      if (/[a-z]/.test(this.password)) score += 20;
      if (/[A-Z]/.test(this.password)) score += 20;
      if (/[0-9]/.test(this.password)) score += 15;
      if (/[^A-Za-z0-9]/.test(this.password)) score += 15;
      
      return Math.min(score, 100);
    },
    strengthPercentage() {
      return this.strength;
    },
    strengthClass() {
      if (this.strength < 30) return 'bg-danger';
      if (this.strength < 60) return 'bg-warning';
      if (this.strength < 80) return 'bg-info';
      return 'bg-success';
    },
    strengthText() {
      if (this.strength < 30) return 'Weak';
      if (this.strength < 60) return 'Fair';
      if (this.strength < 80) return 'Good';
      return 'Strong';
    },
    strengthTextClass() {
      if (this.strength < 30) return 'text-danger';
      if (this.strength < 60) return 'text-warning';
      if (this.strength < 80) return 'text-info';
      return 'text-success';
    },
    requirements() {
      return [
        {
          text: 'At least 8 characters',
          met: this.password.length >= 8,
        },
        {
          text: 'Uppercase letter (A-Z)',
          met: /[A-Z]/.test(this.password),
        },
        {
          text: 'Lowercase letter (a-z)',
          met: /[a-z]/.test(this.password),
        },
        {
          text: 'Number (0-9)',
          met: /[0-9]/.test(this.password),
        },
        {
          text: 'Special character (!@#$%^&*)',
          met: /[^A-Za-z0-9]/.test(this.password),
        },
      ];
    },
  },
};
</script>
