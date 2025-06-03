<template>
  <div class="profile-page">
    <!-- Profile Header -->
    <div class="profile-header">
      <div class="profile-avatar-container">
        <div class="profile-avatar">
          <div class="avatar-placeholder">
            <span>R</span>
          </div>
          <div class="edit-icon">
            <span>✏️</span>
          </div>
          <div class="breitling-badge">
            <span>B</span>
          </div>
        </div>
      </div>
      
      <h1 class="profile-name">{{ user.fullName }}</h1>
      
      <div class="profile-info">
        <div class="country-info">
          <span class="flag">{{ user.flag }}</span>
          <span class="country">{{ user.country }}</span>
        </div>
        <div class="store-info">Store : {{ user.store }}</div>
        <div class="reseller-since">{{ user.resellerSince }}</div>
      </div>
    </div>

    <!-- Desktop Layout Container -->
    <div class="content-container">
      <!-- Score Section -->
      <div class="score-section">
        <div class="score-label">SCORE</div>
        <div class="score-value">{{ user.score.toLocaleString() }} PTS</div>
      </div>

      <!-- Top 3 Watches Section -->
      <div class="watches-section">
        <div class="watches-header">
          <h2>TOP 3 WATCHES</h2>
          <button class="see-all-btn">See all</button>
        </div>
        <div class="watches-grid">
          <div 
            v-for="watch in user.topWatches" 
            :key="watch.id"
            class="watch-item"
          >
            <div class="watch-placeholder">⌚</div>
          </div>
        </div>
      </div>

      <!-- FAQ Section -->
      <div class="faq-section">
        <div class="faq-header">
          <span>FAQ</span>
          <span class="arrow">→</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

// User data
const user = ref({
  fullName: 'RAFAËL DUPUIS',
  name: 'R.DUPUIS',
  country: 'Switzerland',
  flag: '🇨🇭',
  store: 'Lausanne, St. François',
  resellerSince: 'Reseller since 2020',
  score: 41320,
  topWatches: [
    { id: 1, name: 'Premier Chrono' },
    { id: 2, name: 'Navitimer' },
    { id: 3, name: 'Superocean Heritage' }
  ]
})

console.log('ProfileView component loaded')
</script>

<style scoped>
.profile-page {
  min-height: 100vh;
  width: 100%;
  background: linear-gradient(135deg, #072C54 0%, #1e3a8a 100%);
  color: white;
  padding: 1rem;
  padding-bottom: 100px; /* Espace pour navbar mobile */
  box-sizing: border-box;
}

/* PROFILE HEADER */
.profile-header {
  text-align: center;
  margin-bottom: 2rem;
}

.profile-avatar-container {
  position: relative;
  display: inline-block;
  margin-bottom: 1.5rem;
}

.profile-avatar {
  position: relative;
  width: 120px;
  height: 120px;
  border-radius: 50%;
  background: #e0e0e0;
  overflow: hidden;
  border: 4px solid #F7C72C;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
}

.avatar-placeholder {
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 3rem;
  font-weight: bold;
  color: white;
}

.edit-icon {
  position: absolute;
  top: -5px;
  right: -5px;
  width: 35px;
  height: 35px;
  background: #F7C72C;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
  cursor: pointer;
}

.breitling-badge {
  position: absolute;
  bottom: -5px;
  left: -5px;
  width: 35px;
  height: 35px;
  background: #F7C72C;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 1.2rem;
  color: #072C54;
  border: 3px solid white;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.profile-name {
  font-size: 1.8rem;
  font-weight: 700;
  color: white;
  margin: 0 0 1rem 0;
  letter-spacing: 1px;
}

.profile-info {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 1rem;
  margin: 0 auto;
  max-width: 400px;
}

.country-info {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
}

.flag {
  font-size: 1.2rem;
}

.country {
  font-weight: 600;
}

.store-info, .reseller-since {
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.9);
  margin-bottom: 0.25rem;
}

/* CONTENT CONTAINER */
.content-container {
  width: 100%;
  max-width: none;
}

/* SCORE SECTION */
.score-section {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 1.5rem;
  text-align: center;
  margin-bottom: 2rem;
}

.score-label {
  font-size: 1rem;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.8);
  margin-bottom: 0.5rem;
}

.score-value {
  font-size: 2rem;
  font-weight: 700;
  color: white;
}

/* WATCHES SECTION */
.watches-section {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 2rem;
}

.watches-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.watches-header h2 {
  font-size: 1rem;
  font-weight: 600;
  color: white;
  margin: 0;
}

.see-all-btn {
  background: #F7C72C;
  color: #072C54;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  font-size: 0.8rem;
  transition: all 0.3s ease;
}

.see-all-btn:hover {
  background: #E6B625;
  transform: translateY(-1px);
}

.watches-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
}

.watch-item {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  padding: 1rem;
  text-align: center;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 80px;
  transition: all 0.3s ease;
}

.watch-item:hover {
  background: rgba(255, 255, 255, 0.15);
  transform: translateY(-2px);
}

.watch-placeholder {
  font-size: 2rem;
  opacity: 0.7;
}

/* FAQ SECTION */
.faq-section {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 1rem 1.5rem;
  margin-bottom: 2rem;
  cursor: pointer;
  transition: background 0.3s ease;
}

.faq-section:hover {
  background: rgba(255, 255, 255, 0.15);
}

.faq-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-weight: 600;
  color: white;
}

.arrow {
  color: #F7C72C;
  font-size: 1.2rem;
}

/* DESKTOP (768px et plus) - Alignement avec navbar */
@media (min-width: 768px) {
  .profile-page {
    margin-left: 280px; /* Marge encore plus large pour la navbar */
    width: calc(100% - 280px); /* Largeur ajustée */
    padding: 2rem;
    padding-bottom: 2rem;
  }
  
  .profile-header {
    margin-bottom: 3rem;
  }
  
  .profile-avatar {
    width: 150px;
    height: 150px;
  }
  
  .avatar-placeholder {
    font-size: 4rem;
  }
  
  .edit-icon, .breitling-badge {
    width: 45px;
    height: 45px;
    font-size: 1.2rem;
  }
  
  .breitling-badge {
    font-size: 1.5rem;
  }
  
  .profile-name {
    font-size: 2.5rem;
    margin-bottom: 1.5rem;
    letter-spacing: 2px;
  }
  
  .profile-info {
    max-width: 500px;
    padding: 1.5rem;
    font-size: 1.1rem;
  }
  
  .country-info {
    margin-bottom: 1rem;
  }
  
  .flag {
    font-size: 1.5rem;
  }
  
  .store-info, .reseller-since {
    font-size: 1rem;
    margin-bottom: 0.5rem;
  }
  
  .score-section {
    padding: 2.5rem;
    margin-bottom: 2.5rem;
  }
  
  .score-label {
    font-size: 1.2rem;
    margin-bottom: 1rem;
  }
  
  .score-value {
    font-size: 3rem;
  }
  
  .watches-section {
    padding: 2.5rem;
    margin-bottom: 2.5rem;
  }
  
  .watches-header h2 {
    font-size: 1.2rem;
  }
  
  .see-all-btn {
    padding: 0.7rem 1.3rem;
    font-size: 0.9rem;
  }
  
  .watches-grid {
    gap: 1.5rem;
  }
  
  .watch-item {
    min-height: 100px;
    padding: 1.5rem;
  }
  
  .watch-placeholder {
    font-size: 2.5rem;
  }
  
  .faq-section {
    padding: 1.5rem 2rem;
  }
  
  .faq-header {
    font-size: 1.1rem;
  }
  
  .arrow {
    font-size: 1.4rem;
  }
}

/* LARGE DESKTOP (1024px et plus) */
@media (min-width: 1024px) {
  .profile-page {
    margin-left: 280px;
    width: calc(100% - 280px);
    padding: 3rem;
  }
  
  .profile-avatar {
    width: 180px;
    height: 180px;
  }
  
  .avatar-placeholder {
    font-size: 4.5rem;
  }
  
  .edit-icon, .breitling-badge {
    width: 50px;
    height: 50px;
    font-size: 1.3rem;
  }
  
  .breitling-badge {
    font-size: 1.7rem;
  }
  
  .profile-name {
    font-size: 3rem;
  }
  
  .score-section {
    padding: 3rem;
  }
  
  .score-value {
    font-size: 3.5rem;
  }
  
  .watches-section {
    padding: 3rem;
  }
  
  .watch-item {
    min-height: 120px;
    padding: 2rem;
  }
  
  .watch-placeholder {
    font-size: 3rem;
  }
}

/* MOBILE (moins de 768px) */
@media (max-width: 767px) {
  .profile-page {
    margin-left: 0; /* Pas de marge sur mobile */
    width: 100%;
    padding-bottom: 100px; /* Navbar en bas */
  }
  
  .profile-avatar {
    width: 100px;
    height: 100px;
  }
  
  .avatar-placeholder {
    font-size: 2.5rem;
  }
  
  .edit-icon, .breitling-badge {
    width: 30px;
    height: 30px;
    font-size: 0.8rem;
  }
  
  .breitling-badge {
    font-size: 1rem;
  }
  
  .profile-name {
    font-size: 1.5rem;
  }
  
  .profile-info {
    max-width: 320px;
    padding: 0.8rem;
  }
  
  .score-section {
    padding: 1.2rem;
  }
  
  .score-value {
    font-size: 1.8rem;
  }
  
  .watches-section {
    padding: 1.2rem;
  }
  
  .watches-grid {
    gap: 0.75rem;
  }
  
  .watch-item {
    padding: 0.75rem;
    min-height: 60px;
  }
  
  .watch-placeholder {
    font-size: 1.5rem;
  }
  
  .faq-section {
    padding: 1rem 1.2rem;
  }
}
</style>