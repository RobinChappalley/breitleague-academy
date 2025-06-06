<template>
  <div class="profile-page">
    <div v-if="isLoading" class="loading">
      <div class="loading-spinner">⏳</div>
      <p>Chargement du profil...</p>
    </div>
    
    <div v-else-if="error" class="error">
      <p>❌ {{ error }}</p>
      <button @click="loadUserProfile" class="retry-btn">Réessayer</button>
    </div>
    
    <div v-else class="profile-content">
      <!-- Profile Header -->
      <div class="profile-header">
        <div class="profile-avatar-container">
          <div class="profile-avatar">
            <div class="avatar-placeholder">
              <span>{{ getUserInitial() }}</span>  <!-- L'initiale au centre -->
            </div>
          </div>
          <!-- Badge Breitling SVG sur le bord -->
          <div v-if="user.is_BS" class="breitling-badge-avatar" title="Breitling Specialist">
            <img src="/images/icones/breitlingspecialist_badge.svg" alt="Breitling Specialist" class="badge-image-avatar">
          </div>
        </div>
        
        <div class="profile-name-container">
          <h1 class="profile-name">{{ user.username || 'Utilisateur' }}</h1>
        </div>
        
        <div class="profile-info">
          <div class="country-info">
            <span class="flag">{{ user.pos?.country_flag || '🌍' }}</span>
            <span class="country">{{ user.pos?.country || 'Non renseigné' }}</span>
          </div>
          <div class="store-info">Store : {{ user.pos?.address || 'Non renseigné' }}</div>
          <div class="reseller-since">Reseller since {{ user.signup_year || '2020' }}</div>
        </div>
      </div>

      <!-- Rest of the template remains the same -->
      <div class="content-container">
        <!-- Score Section -->
        <div class="score-section">
          <div class="score-label">SCORE</div>
          <div class="score-value">{{ (user.elo_score || 0).toLocaleString() }} PTS</div>
        </div>

        <!-- Stats Section -->
        <div class="stats-section">
          <div class="stat-item">
            <div class="stat-value">{{ user.battle_won || 0 }}</div>
            <div class="stat-label">Victoires</div>
          </div>
          <div class="stat-item">
            <div class="stat-value">{{ user.battle_lost || 0 }}</div>
            <div class="stat-label">Défaites</div>
          </div>
          <div class="stat-item">
            <div class="stat-value">{{ winRate }}%</div>
            <div class="stat-label">Win Rate</div>
          </div>
        </div>

        <!-- Top 3 Watches Section -->
        <div class="watches-section">
          <div class="watches-header">
            <h2>TOP 3 REWARDS</h2>
            <button class="see-all-btn" @click="goToCollection">See all</button>
          </div>
          <div class="watches-grid">
            <div 
              v-for="reward in topRewards" 
              :key="reward.id"
              class="watch-item"
              :title="reward.model"
            >
              <div class="watch-placeholder">⌚</div>
              <div class="watch-name">{{ reward.model?.substring(0, 15) }}...</div>
            </div>
          </div>
        </div>

        <!-- User Info Section -->
        <div class="user-info-section">
          <div class="info-item">
            <span class="info-label">Email:</span>
            <span class="info-value">{{ user.email || 'Non renseigné' }}</span>
          </div>
          <div class="info-item clickable">
            <span class="info-label">Modifier le mot de passe</span>
            <span class="info-action">→</span>
          </div>
          <div class="info-item clickable">
            <span class="info-label">Changer d'avatar</span>
            <span class="info-action">→</span>
          </div>
          <div class="info-item clickable">
            <span class="info-label">Paramètres de notification</span>
            <span class="info-action">→</span>
          </div>
          <div class="info-item clickable">
            <span class="info-label">Confidentialité</span>
            <span class="info-action">→</span>
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
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { userService } from '@/services/api'

const router = useRouter()

// Data reactive
const user = ref({})
const userRewards = ref([])
const isLoading = ref(true)
const error = ref(null)

// Computed
const winRate = computed(() => {
  const won = user.value.battle_won || 0
  const lost = user.value.battle_lost || 0
  const total = won + lost
  return total > 0 ? Math.round((won / total) * 100) : 0
})

const topRewards = computed(() => {
  return userRewards.value.slice(0, 3)
})

// Méthodes
const loadUserProfile = async () => {
  try {
    isLoading.value = true
    error.value = null
    
    console.log('🔄 Chargement utilisateur ID 1...')
    
    // Charge l'utilisateur avec l'ID 1 (rafael.dupuis dans tes données)
    const response = await userService.getUser(1)
    console.log('📦 Réponse API user:', response)
    
    // Ton UserController retourne les données avec relations
    user.value = response.data || response
    console.log('✅ User chargé:', user.value)
    
    // Charger les rewards de l'utilisateur
    await loadUserRewards(user.value.id)
    
  } catch (err) {
    error.value = `Erreur lors du chargement: ${err.message}`
    console.error('❌ Erreur API:', err)
  } finally {
    isLoading.value = false
  }
}

const loadUserRewards = async (userId) => {
  try {
    const response = await userService.getUserRewards(userId)
    console.log('📦 Réponse rewards:', response)
    
    userRewards.value = response.data || []
    console.log('✅ Rewards chargés:', userRewards.value)
    
  } catch (err) {
    console.log('⚠️ Pas de rewards trouvés:', err.message)
    userRewards.value = []
  }
}

const getUserInitial = () => {
  if (user.value.avatar) return user.value.avatar
  if (user.value.username) return user.value.username[0].toUpperCase()
  return 'U'
}

const goToCollection = () => {
  router.push('/collection')
}

// Lifecycle
onMounted(() => {
  console.log('🚀 ProfileView monté, chargement des données...')
  loadUserProfile()
})
</script>

<style scoped>
.profile-page {
  min-height: 100vh;
  width: 100%;
  background: linear-gradient(135deg, #072C54 0%, #1e3a8a 100%);
  color: white;
  padding: 1rem;
  padding-bottom: 100px;
  box-sizing: border-box;
}

.loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 50vh;
  color: #F7C72C;
}

.loading-spinner {
  font-size: 3rem;
  margin-bottom: 1rem;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error {
  text-align: center;
  color: #ff6b6b;
  padding: 2rem;
}

.retry-btn {
  background: #F7C72C;
  color: #072C54;
  border: none;
  padding: 0.8rem 1.5rem;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  margin-top: 1rem;
}

.profile-content {
  width: 100%;
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
  margin: 0 auto;
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
  text-transform: uppercase;
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

.breitling-badge-avatar {
  position: absolute;
  bottom: -10px;
  left: -15px;

}

.badge-image-avatar {
  width: 65px;
  height: 65px;
  object-fit: contain;
}

/* Cache le badge inline */
.breitling-badge-inline {
  display: none;
}

.profile-name-container {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  margin-bottom: 1rem;
}

.profile-name {
  font-size: 1.8rem;
  font-weight: 700;
  color: white;
  margin: 0;
  letter-spacing: 1px;
  text-transform: uppercase;
}

.specialist-badge-text {
  margin-bottom: 1rem;
  margin-top: 0.5rem;
}

.badge-text {
  background: linear-gradient(45deg, #F7C72C, #E6B625);
  color: #072C54;
  padding: 0.4rem 1rem;
  border-radius: 20px;
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 1px;
  text-transform: uppercase;
  border: 2px solid rgba(255, 255, 255, 0.3);
  box-shadow: 0 4px 15px rgba(247, 199, 44, 0.3);
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
  color: #F7C72C;
}

/* STATS SECTION */
.stats-section {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
  margin-bottom: 2rem;
}

.stat-item {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 1.5rem;
  text-align: center;
}

.stat-value {
  font-size: 1.8rem;
  font-weight: 700;
  color: #F7C72C;
  margin-bottom: 0.5rem;
}

.stat-label {
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.8);
  text-transform: uppercase;
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
  flex-direction: column;
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
  margin-bottom: 0.5rem;
}

.watch-name {
  font-size: 0.7rem;
  color: rgba(255, 255, 255, 0.8);
}

/* USER INFO SECTION */
.user-info-section {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 2rem;
}

.info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.8rem 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.info-item:last-child {
  border-bottom: none;
}

.info-item.clickable {
  cursor: pointer;
  transition: background 0.3s ease;
}

.info-item.clickable:hover {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 8px;
  padding-left: 1rem;
  padding-right: 1rem;
}

.info-label {
  font-weight: 600;
  color: #F7C72C;
}

.info-value {
  color: rgba(255, 255, 255, 0.9);
}

.info-action {
  color: #F7C72C;
  font-size: 1.2rem;
  transition: transform 0.3s ease;
}

.info-item.clickable:hover .info-action {
  transform: translateX(5px);
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

/* RESPONSIVE */
@media (min-width: 768px) {
  .profile-page {
    margin-left: 280px;
    width: calc(100% - 280px);
    padding: 2rem;
    padding-bottom: 2rem;
  }
  
  .profile-avatar {
    width: 150px;
    height: 150px;
  }
  
  .profile-name {
    font-size: 2.5rem;
  }
  
  .score-value {
    font-size: 3rem;
  }
}

@media (max-width: 767px) {
  .profile-page {
    margin-left: 0;
    width: 100%;
    padding: 1rem;
    padding-bottom: 80px;
  }
  
  .stats-section {
    grid-template-columns: 1fr;
    gap: 0.8rem;
  }
  
  .stat-item {
    padding: 1rem;
  }
}
</style>