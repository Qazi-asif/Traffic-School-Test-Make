<template>
  <div class="dicds-main-menu">
    <div class="menu-header">
      <h2>Florida DICDS - Main Menu</h2>
      <p>Select an option from the menu below:</p>
    </div>

    <div class="menu-sections">
      <div class="menu-section">
        <h3>{{ menu.schools?.title }}</h3>
        <div class="menu-items">
          <button v-for="item in menu.schools?.items" :key="item.id"
                  @click="navigateToItem(item.id)"
                  class="menu-item-btn">
            <div class="item-title">{{ item.title }}</div>
            <div class="item-description">{{ item.description }}</div>
          </button>
        </div>
      </div>

      <div class="menu-section">
        <h3>{{ menu.certificates?.title }}</h3>
        <div class="menu-items">
          <button v-for="item in menu.certificates?.items" :key="item.id"
                  @click="navigateToItem(item.id)"
                  class="menu-item-btn">
            <div class="item-title">{{ item.title }}</div>
            <div class="item-description">{{ item.description }}</div>
          </button>
        </div>
      </div>

      <div class="menu-section">
        <h3>{{ menu.inquiry?.title }}</h3>
        <div class="menu-items">
          <button v-for="item in menu.inquiry?.items" :key="item.id"
                  @click="navigateToItem(item.id)"
                  class="menu-item-btn">
            <div class="item-title">{{ item.title }}</div>
            <div class="item-description">{{ item.description }}</div>
          </button>
        </div>
      </div>
    </div>

    <div class="menu-footer">
      <button @click="logout" class="btn btn-outline-secondary">
        Logout
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DicdsMainMenu',
  data() {
    return {
      menu: {},
      userRole: null
    }
  },
  mounted() {
    this.loadMainMenu()
  },
  methods: {
    async loadMainMenu() {
      try {
        const response = await axios.get('/api/dicds/main-menu')
        this.menu = response.data.menu
        this.userRole = response.data.user_role
      } catch (error) {
        console.error('Error loading main menu:', error)
      }
    },
    async navigateToItem(itemId) {
      try {
        const response = await axios.post(`/api/dicds/navigation/${itemId}`)
        this.$emit('navigate', itemId)
      } catch (error) {
        console.error('Error navigating:', error)
        this.$emit('navigate', itemId)
      }
    },
    logout() {
      this.$emit('logout')
    }
  }
}
</script>

<style scoped>
.dicds-main-menu {
  max-width: 1000px;
  margin: 0 auto;
  padding: 2rem;
}

.menu-header {
  text-align: center;
  margin-bottom: 3rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #007bff;
}

.menu-sections {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
  margin-bottom: 3rem;
}

.menu-section {
  border: 1px solid #dee2e6;
  border-radius: 8px;
  padding: 1.5rem;
}

.menu-section h3 {
  color: #007bff;
  margin-bottom: 1rem;
  text-align: center;
  border-bottom: 1px solid #dee2e6;
  padding-bottom: 0.5rem;
}

.menu-items {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.menu-item-btn {
  background: #f8f9fa;
  border: 1px solid #dee2e6;
  border-radius: 4px;
  padding: 1rem;
  text-align: left;
  cursor: pointer;
  transition: all 0.2s;
  min-height: 44px;
}

.menu-item-btn:hover {
  background: #e9ecef;
  border-color: #007bff;
}

.item-title {
  font-weight: 600;
  color: #007bff;
  margin-bottom: 0.25rem;
}

.item-description {
  font-size: 0.875rem;
  color: #6c757d;
}

.menu-footer {
  text-align: center;
  padding-top: 2rem;
  border-top: 1px solid #dee2e6;
}
</style>
