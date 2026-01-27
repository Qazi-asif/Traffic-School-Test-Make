<template>
  <div class="dicds-welcome-screen">
    <div class="welcome-header">
      <h2>Florida Driver Improvement Course Data System (DICDS)</h2>
      <h3>Welcome Screen</h3>
    </div>

    <div class="system-messages" v-if="messages.length > 0">
      <div v-for="message in messages" :key="message.id" 
           :class="['message', `message-${message.message_type}`]">
        <h4>{{ message.title }}</h4>
        <div v-html="message.content"></div>
      </div>
    </div>

    <div class="welcome-content">
      <p>Welcome to the Florida DICDS system. Please review any system messages above and click Continue to proceed to the main menu.</p>
    </div>

    <div class="welcome-actions">
      <button @click="continueToMenu" class="btn btn-primary btn-lg">
        Continue
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DicdsWelcomeScreen',
  data() {
    return {
      messages: [],
      user: null
    }
  },
  mounted() {
    this.loadWelcomeData()
  },
  methods: {
    async loadWelcomeData() {
      try {
        const response = await axios.get('/api/dicds/welcome')
        this.messages = response.data.messages
        this.user = response.data.user
      } catch (error) {
        console.error('Error loading welcome data:', error)
      }
    },
    async continueToMenu() {
      try {
        const response = await axios.post('/api/dicds/welcome/continue')
        this.$emit('navigate', 'main-menu')
      } catch (error) {
        console.error('Error continuing:', error)
        this.$emit('navigate', 'main-menu')
      }
    }
  }
}
</script>

<style scoped>
.dicds-welcome-screen {
  max-width: 800px;
  margin: 0 auto;
  padding: 2rem;
  text-align: center;
}

.welcome-header {
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #007bff;
}

.system-messages {
  margin: 2rem 0;
}

.message {
  margin: 1rem 0;
  padding: 1rem;
  border-radius: 4px;
  text-align: left;
}

.message-welcome {
  background: #f4f6f0;
  border: 1px solid #516425;
}

.message-alert {
  background: #f8d7da;
  border: 1px solid #f5c6cb;
}

.message-maintenance {
  background: #fff3cd;
  border: 1px solid #ffeaa7;
}

.message-update {
  background: #d1ecf1;
  border: 1px solid #bee5eb;
}

.welcome-content {
  margin: 2rem 0;
  font-size: 1.1rem;
}

.welcome-actions {
  margin-top: 3rem;
}

.btn-lg {
  padding: 1rem 3rem;
  font-size: 1.2rem;
  min-height: 44px;
}
</style>
