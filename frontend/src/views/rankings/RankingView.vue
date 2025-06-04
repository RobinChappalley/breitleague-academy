<template>
  <div class="ranking-page">
    <!-- Main Content -->
    <div class="main-content">
      <!-- Header -->
      <div class="ranking-header">
        <h1 class="ranking-title">RANKING</h1>
        
        <!-- Filters -->
        <div class="filters">
          <button 
            class="filter-btn"
            :class="{ active: selectedFilter === 'world' }"
            @click="selectedFilter = 'world'"
          >
            World
          </button>
          <div class="dropdown">
            <button 
              class="filter-btn dropdown-btn"
              :class="{ active: selectedFilter === 'switzerland' }"
              @click="toggleDropdown"
            >
              Switzerland
              <i class="dropdown-icon">▼</i>
            </button>
            <div class="dropdown-menu" v-if="showDropdown">
              <a href="#" @click="selectCountry('Switzerland')">Switzerland</a>
              <a href="#" @click="selectCountry('France')">France</a>
              <a href="#" @click="selectCountry('Germany')">Germany</a>
              <a href="#" @click="selectCountry('Italy')">Italy</a>
            </div>
          </div>
        </div>
      </div>

      <!-- Ranking List -->
      <div class="ranking-list">
        <div 
          v-for="(player, index) in displayedPlayers" 
          :key="player.id"
          class="ranking-item"
          :class="{ 
            'highlight': player.highlight,
            'top-16': player.rank <= 16
          }"
        >
          <div class="rank">{{ player.rank }}.</div>
          
          <div class="player-info">
            <div class="avatar" :style="getAvatarStyle(player)">
              {{ player.avatar }}
            </div>
            <div class="player-details">
              <h3 class="player-name" @click="openPlayerProfile(player)">{{ player.name }}</h3>
              <div class="country-flag">{{ player.flag }}</div>
            </div>
          </div>
          
          <div class="score">{{ player.score.toLocaleString() }}</div>
        </div>

        <!-- See All Button -->
        <button class="see-all-btn" @click="seeAll" v-if="!showingAll">
          SEE ALL
        </button>
      </div>
    </div>

    <!-- Fixed User Position Footer -->
    <div class="user-position-footer">
      <div class="user-ranking-item">
        <div class="rank">{{ currentUser.rank }}.</div>
        
        <div class="player-info">
          <div class="avatar" :style="getAvatarStyle(currentUser)">
            {{ currentUser.avatar }}
          </div>
          <div class="player-details">
            <h3 class="player-name" @click="openPlayerProfile(currentUser)">{{ currentUser.name }}</h3>
            <div class="country-flag">{{ currentUser.flag }}</div>
          </div>
        </div>
        
        <div class="score">{{ currentUser.score.toLocaleString() }}</div>
      </div>
    </div>

    <!-- Player Profile Popup -->
    <div class="popup-overlay" v-if="showPlayerPopup" @click="closePlayerPopup">
      <div class="player-popup" @click.stop>
        <button class="close-btn" @click="closePlayerPopup">✕</button>
        
        <div class="popup-header">
          <div class="popup-avatar" :style="getAvatarStyle(selectedPlayer)">
            {{ selectedPlayer?.avatar }}
          </div>
          <div class="popup-player-info">
            <h2 class="popup-player-name">{{ selectedPlayer?.name }}</h2>
            <div class="popup-location">
              <span class="popup-flag">{{ selectedPlayer?.flag }}</span>
              <span>{{ selectedPlayer?.city }}</span>
            </div>
            <div class="popup-since">{{ selectedPlayer?.since }}</div>
          </div>
        </div>

        <div class="popup-score">
          <div class="score-value">{{ selectedPlayer?.score.toLocaleString() }} PTS</div>
        </div>

        <div class="popup-stats">
          <div class="stat-item">
            <span class="stat-label">BATTLE WIN</span>
            <span class="stat-value">{{ selectedPlayer?.battleWin }}</span>
          </div>
          <div class="stat-item">
            <span class="stat-label">BATTLE LOST</span>
            <span class="stat-value">{{ selectedPlayer?.battleLost }}</span>
          </div>
        </div>

        <div class="popup-watches">
          <div class="watches-header">
            <h3>TOP 3 WATCHES</h3>
            <button class="see-all-watches-btn">See all</button>
          </div>
          <div class="watches-grid">
            <div 
              v-for="watch in selectedPlayer?.topWatches" 
              :key="watch.id"
              class="watch-item"
            >
              <div class="watch-placeholder">
                ⌚
              </div>
              <div class="watch-name">{{ watch.name }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

// Data
const selectedFilter = ref('world')
const showDropdown = ref(false)
const isDesktop = ref(false)
const showingAll = ref(false)
const showPlayerPopup = ref(false)
const selectedPlayer = ref(null)

// Mock data avec plus de joueurs et infos détaillées
const allPlayers = ref([
  { 
    id: 1, rank: 1, name: 'J.DEGIRMENCI', country: 'South Korea', score: 94532, avatar: 'J', flag: '🇰🇷', 
    city: 'Seoul, Gangnam District', since: 'Reseller since 2018', battleWin: 145, battleLost: 23,
    topWatches: [
      { id: 1, name: 'Premier B01' },
      { id: 2, name: 'Navitimer' },
      { id: 3, name: 'Superocean' }
    ]
  },
  { 
    id: 2, rank: 2, name: 'M.OVSANNA', country: 'Germany', score: 87530, avatar: 'M', flag: '🇩🇪',
    city: 'Berlin, Mitte', since: 'Reseller since 2017', battleWin: 132, battleLost: 41,
    topWatches: [
      { id: 1, name: 'Premier B01' },
      { id: 2, name: 'Navitimer' },
      { id: 3, name: 'Superocean' }
    ]
  },
  { 
    id: 3, rank: 3, name: 'S.DACOSTA', country: 'Spain', score: 84320, avatar: 'S', flag: '🇪🇸',
    city: 'Madrid, Centro', since: 'Reseller since 2019', battleWin: 128, battleLost: 35,
    topWatches: [
      { id: 1, name: 'Premier B01' },
      { id: 2, name: 'Navitimer' },
      { id: 3, name: 'Superocean' }
    ]
  },
  { 
    id: 4, rank: 4, name: 'R.FREUENFELD', country: 'Germany', score: 83234, avatar: 'R', flag: '🇩🇪',
    city: 'Munich, Maxvorstadt', since: 'Reseller since 2020', battleWin: 119, battleLost: 29,
    topWatches: [
      { id: 1, name: 'Premier B01' },
      { id: 2, name: 'Navitimer' },
      { id: 3, name: 'Superocean' }
    ]
  },
  { 
    id: 5, rank: 5, name: 'L.ANEX', country: 'France', score: 82342, avatar: 'L', flag: '🇫🇷',
    city: 'Paris, 1er Arrondissement', since: 'Reseller since 2016', battleWin: 115, battleLost: 33,
    topWatches: [
      { id: 1, name: 'Premier B01' },
      { id: 2, name: 'Navitimer' },
      { id: 3, name: 'Superocean' }
    ]
  },
  { 
    id: 6, rank: 6, name: 'P.DUJARDIN', country: 'France', score: 82234, avatar: 'P', flag: '🇫🇷',
    city: 'Lyon, Presqu\'île', since: 'Reseller since 2018', battleWin: 112, battleLost: 28,
    topWatches: [
      { id: 1, name: 'Premier B01' },
      { id: 2, name: 'Navitimer' },
      { id: 3, name: 'Superocean' }
    ]
  },
  // Continuer pour les rangs 7-20
  { id: 7, rank: 7, name: 'R.BAUMGARTNER', country: 'Switzerland', score: 81832, avatar: 'R', flag: '🇨🇭' },
  { id: 8, rank: 8, name: 'V.MICHOU', country: 'Canada', score: 80222, avatar: 'V', flag: '🇨🇦' },
  { id: 9, rank: 9, name: 'L.BENSAID', country: 'Spain', score: 80160, avatar: 'L', flag: '🇪🇸' },
  { 
    id: 10, rank: 10, name: 'R.KELLER', country: 'Germany', score: 79883, avatar: 'R', flag: '🇩🇪',
    city: 'Berlin, Alexander Platz', since: 'Reseller since 2016', battleWin: 128, battleLost: 73,
    topWatches: [
      { id: 1, name: 'Premier B01' },
      { id: 2, name: 'Navitimer' },
      { id: 3, name: 'Superocean' }
    ]
  },
  { id: 11, rank: 11, name: 'R.ANGER', country: 'Switzerland', score: 79034, avatar: 'R', flag: '🇨🇭' },
  { id: 12, rank: 12, name: 'A.MOREL', country: 'France', score: 78850, avatar: 'A', flag: '🇫🇷' },
  { id: 13, rank: 13, name: 'Y.TAKEDA', country: 'Japan', score: 77600, avatar: 'Y', flag: '🇯🇵' },
  { id: 14, rank: 14, name: 'C.NDIAYE', country: 'France', score: 77502, avatar: 'C', flag: '🇫🇷' },
  { id: 15, rank: 15, name: 'M.ROJAS', country: 'Canada', score: 76307, avatar: 'M', flag: '🇨🇦' },
  { id: 16, rank: 16, name: 'K.HADDAD', country: 'Morocco', score: 75998, avatar: 'K', flag: '🇲🇦' },
  // Les autres ne sont plus en top 16
  { id: 17, rank: 17, name: 'S.FRAISOU', country: 'South Korea', score: 70532, avatar: 'S', flag: '🇰🇷', highlight: true },
  { id: 18, rank: 18, name: 'F.MORANGO', country: 'Spain', score: 69530, avatar: 'F', flag: '🇪🇸', highlight: true },
  { id: 19, rank: 19, name: 'C.TENERA', country: 'Spain', score: 67320, avatar: 'C', flag: '🇪🇸', highlight: true },
  { id: 20, rank: 20, name: 'H.IBRAHIM', country: 'Germany', score: 42234, avatar: 'H', flag: '🇩🇪', highlight: true }
])

// Utilisateur actuel (séparé)
const currentUser = ref({
  id: 70, 
  rank: 70, 
  name: 'R.DUFUIS', 
  country: 'Switzerland', 
  score: 41320, 
  avatar: 'R', 
  flag: '🇨🇭',
  city: 'Zurich, Bahnhofstrasse', 
  since: 'Reseller since 2021', 
  battleWin: 45, 
  battleLost: 28,
  topWatches: [
    { id: 1, name: 'Premier B01' },
    { id: 2, name: 'Navitimer' },
    { id: 3, name: 'Superocean' }
  ]
})

const displayedPlayers = computed(() => {
  if (showingAll.value) {
    return allPlayers.value
  }
  return allPlayers.value.slice(0, 20)
})

// Methods
const checkScreenSize = () => {
  isDesktop.value = window.innerWidth >= 768
}

const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value
}

const selectCountry = (country) => {
  selectedFilter.value = country.toLowerCase()
  showDropdown.value = false
}

const seeAll = () => {
  showingAll.value = true
}

const openPlayerProfile = (player) => {
  selectedPlayer.value = player
  showPlayerPopup.value = true
}

const closePlayerPopup = () => {
  showPlayerPopup.value = false
  selectedPlayer.value = null
}

// Generate random gradient for each avatar
const getAvatarStyle = (player) => {
  if (!player) return {}
  
  const gradients = [
    'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
    'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
    'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
    'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
    'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
    'linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)',
    'linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%)',
    'linear-gradient(135deg, #ff8a80 0%, #ff5722 100%)'
  ]
  
  const index = player.id % gradients.length
  return {
    background: gradients[index]
  }
}

onMounted(() => {
  checkScreenSize()
  window.addEventListener('resize', checkScreenSize)
})

console.log('RankingView component loaded')
</script>

<style scoped>
.ranking-page {
  min-height: 100vh;
  width: 100vw;
  background: linear-gradient(135deg, #072C54 0%, #1e3a8a 100%);
  color: white;
  overflow-x: hidden;
}

/* MAIN CONTENT */
.main-content {
  padding: 2rem;
  padding-bottom: 120px;
  max-width: 800px;
  margin: 0 auto;
}

/* HEADER */
.ranking-header {
  margin-bottom: 2rem;
  text-align: center;
}

.ranking-title {
  font-size: 3rem;
  font-weight: 700;
  color: #F7C72C;
  margin: 0 0 2rem 0;
  text-transform: uppercase;
  letter-spacing: 2px;
}

.filters {
  display: flex;
  gap: 0;
  justify-content: center;
  margin-bottom: 2rem;
}

.filter-btn {
  padding: 1rem 2rem;
  border: none;
  background: rgba(255, 255, 255, 0.1);
  color: white;
  cursor: pointer;
  transition: all 0.3s ease;
  font-weight: 600;
  text-transform: uppercase;
}

.filter-btn:first-child {
  border-radius: 8px 0 0 8px;
}

.filter-btn.active {
  background: #F7C72C;
  color: #072C54;
}

.dropdown {
  position: relative;
}

.dropdown-btn {
  border-radius: 0 8px 8px 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.dropdown-icon {
  font-size: 0.8rem;
  transition: transform 0.3s ease;
}

.dropdown-menu {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: #072C54;
  border-radius: 0 0 8px 8px;
  overflow: hidden;
  z-index: 10;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}

.dropdown-menu a {
  display: block;
  padding: 1rem;
  color: white;
  text-decoration: none;
  transition: background 0.3s ease;
}

.dropdown-menu a:hover {
  background: rgba(247, 199, 44, 0.1);
}

/* RANKING LIST */
.ranking-list {
  width: 100%;
}

.ranking-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.5rem;
  background: rgba(255, 255, 255, 0.1);
  margin-bottom: 1rem;
  border-radius: 12px;
  transition: all 0.3s ease;
}

.ranking-item:hover {
  background: rgba(255, 255, 255, 0.15);
}

.ranking-item.highlight {
  background: rgba(135, 206, 250, 0.2);
}

/* TOP 16 STYLING */
.ranking-item.top-16 {
  background: linear-gradient(135deg, rgba(255, 215, 0, 0.15) 0%, rgba(247, 199, 44, 0.1) 100%);
  border-left: 4px solid #F7C72C;
  box-shadow: 0 4px 15px rgba(247, 199, 44, 0.2);
}

.ranking-item.top-16:hover {
  background: linear-gradient(135deg, rgba(255, 215, 0, 0.25) 0%, rgba(247, 199, 44, 0.2) 100%);
  transform: translateY(-2px);
}

.rank {
  font-size: 1.2rem;
  font-weight: 700;
  color: #F7C72C;
  min-width: 40px;
}

.player-info {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex: 1;
}

.avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 1.2rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
  border: 2px solid rgba(255, 255, 255, 0.2);
}

.player-details {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.player-name {
  font-size: 1rem;
  font-weight: 600;
  color: white;
  margin: 0;
  cursor: pointer;
  transition: color 0.3s ease;
}

.player-name:hover {
  color: #F7C72C;
}

.country-flag {
  font-size: 1.2rem;
}

.score {
  font-size: 1.2rem;
  font-weight: 700;
  color: white;
}

/* SEE ALL BUTTON */
.see-all-btn {
  width: 100%;
  padding: 1rem;
  background: #F7C72C;
  color: #072C54;
  border: none;
  border-radius: 12px;
  font-weight: 700;
  font-size: 1rem;
  text-transform: uppercase;
  cursor: pointer;
  transition: all 0.3s ease;
  margin: 2rem 0;
}

.see-all-btn:hover {
  background: #E6B625;
  transform: translateY(-2px);
}

/* USER POSITION FOOTER - FIXED */
.user-position-footer {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background: #072C54;
  border-top: 3px solid #F7C72C;
  padding: 1rem 2rem;
  z-index: 1000;
  box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.3);
}

.user-ranking-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  max-width: 800px;
  margin: 0 auto;
  background: rgba(247, 199, 44, 0.15);
  padding: 1rem 1.5rem;
  border-radius: 12px;
  border: 2px solid #F7C72C;
}

.user-ranking-item .rank {
  font-size: 1.2rem;
  font-weight: 700;
  color: #F7C72C;
  min-width: 40px;
}

.user-ranking-item .player-info {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex: 1;
}

.user-ranking-item .avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 1.2rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
  border: 2px solid #F7C72C;
}

.user-ranking-item .player-details {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.user-ranking-item .player-name {
  font-size: 1rem;
  font-weight: 600;
  color: white;
  margin: 0;
  cursor: pointer;
}

.user-ranking-item .country-flag {
  font-size: 1.2rem;
}

.user-ranking-item .score {
  font-size: 1.2rem;
  font-weight: 700;
  color: white;
}

/* POPUP STYLES */
.popup-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  backdrop-filter: blur(5px);
}

.player-popup {
  background: linear-gradient(135deg, #1e3a8a 0%, #072C54 100%);
  border-radius: 20px;
  padding: 2rem;
  max-width: 400px;
  width: 90%;
  position: relative;
  color: white;
  border: 2px solid #F7C72C;
}

.close-btn {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: #F7C72C;
  color: #072C54;
  border: none;
  border-radius: 50%;
  width: 30px;
  height: 30px;
  font-size: 1rem;
  font-weight: bold;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.popup-header {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
}

.popup-avatar {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 2rem;
  border: 3px solid #F7C72C;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}

.popup-player-info {
  flex: 1;
}

.popup-player-name {
  font-size: 1.5rem;
  font-weight: 700;
  color: white;
  margin: 0 0 0.5rem 0;
}

.popup-location {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
}

.popup-flag {
  font-size: 1.2rem;
}

.popup-since {
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.9rem;
}

.popup-score {
  text-align: center;
  margin-bottom: 2rem;
}

.score-value {
  background: rgba(255, 255, 255, 0.1);
  padding: 1rem;
  border-radius: 12px;
  font-size: 1.5rem;
  font-weight: 700;
  color: white;
}

.popup-stats {
  display: flex;
  justify-content: space-between;
  margin-bottom: 2rem;
}

.stat-item {
  text-align: center;
}

.stat-label {
  display: block;
  color: #F7C72C;
  font-size: 0.9rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.stat-value {
  display: block;
  font-size: 2rem;
  font-weight: 700;
  color: white;
}

.popup-watches {
  border-top: 1px solid rgba(255, 255, 255, 0.2);
  padding-top: 2rem;
}

.watches-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.watches-header h3 {
  color: white;
  font-size: 1rem;
  font-weight: 600;
  margin: 0;
}

.see-all-watches-btn {
  background: #F7C72C;
  color: #072C54;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  font-size: 0.8rem;
}

.watches-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
}

.watch-item {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  padding: 1rem 0.5rem;
  text-align: center;
}

.watch-placeholder {
  font-size: 2rem;
  margin-bottom: 0.5rem;
  opacity: 0.7;
}

.watch-name {
  font-size: 0.7rem;
  color: rgba(255, 255, 255, 0.8);
  font-weight: 500;
}

/* RESPONSIVE */
@media (min-width: 768px) {
  .ranking-page {
    margin-left: 280px;
    width: calc(100% - 280px);
    padding-bottom: 0; /* Pas de padding bottom sur desktop */
  }
  
  .main-content {
    padding-bottom: 120px; /* Espace pour le footer utilisateur */
  }
  
  .user-position-footer {
    left: 280px; /* Aligné avec la navbar desktop */
    padding: 1.5rem 2rem;
  }
  
  .user-ranking-item {
    max-width: 900px;
    padding: 1.2rem 2rem;
  }
  
  .user-ranking-item .rank {
    font-size: 1.4rem;
    min-width: 50px;
  }
  
  .user-ranking-item .avatar {
    width: 60px;
    height: 60px;
    font-size: 1.4rem;
  }
  
  .user-ranking-item .player-name {
    font-size: 1.1rem;
  }
  
  .user-ranking-item .score {
    font-size: 1.4rem;
  }
  
  .ranking-title {
    font-size: 3.5rem;
  }
}

@media (min-width: 1024px) {
  .ranking-page {
    margin-left: 280px;
    width: calc(100% - 280px);
    padding: 3rem;
    padding-bottom: 0;
  }
  
  .main-content {
    padding-bottom: 140px; /* Plus d'espace sur large desktop */
  }
  
  .user-position-footer {
    left: 280px;
    padding: 2rem 3rem;
  }
  
  .user-ranking-item {
    max-width: 1000px;
    padding: 1.5rem 2.5rem;
  }
  
  .user-ranking-item .rank {
    font-size: 1.5rem;
  }
  
  .user-ranking-item .avatar {
    width: 70px;
    height: 70px;
    font-size: 1.6rem;
  }
  
  .user-ranking-item .player-name {
    font-size: 1.2rem;
  }
  
  .user-ranking-item .score {
    font-size: 1.5rem;
  }
  
  .ranking-title {
    font-size: 4rem;
  }
}

/* MOBILE (moins de 768px) */
@media (max-width: 767px) {
  .ranking-page {
    margin-left: 0;
    width: 100%;
    padding: 1rem;
    padding-bottom: 0;
  }
  
  .main-content {
    padding: 1rem;
    padding-bottom: 180px; /* Plus d'espace : footer fixe (100px) + navbar mobile (70px) + marge (10px) */
  }
  
  .user-position-footer {
    left: 0; /* Pleine largeur sur mobile */
    padding: 0.8rem 1rem;
    bottom: 70px; /* Au-dessus de la navbar mobile */
  }
  
  .see-all-btn {
    margin: 2rem 0 3rem 0; /* Plus de marge en bas */
  }
  
  .user-ranking-item {
    padding: 0.8rem 1rem;
    gap: 0.8rem;
  }
  
  .user-ranking-item .rank {
    font-size: 1rem;
    min-width: 35px;
  }
  
  .user-ranking-item .avatar {
    width: 40px;
    height: 40px;
    font-size: 1rem;
  }
  
  .user-ranking-item .player-name {
    font-size: 0.9rem;
  }
  
  .user-ranking-item .score {
    font-size: 1rem;
  }
  
  .ranking-title {
    font-size: 2rem;
  }
  
  .ranking-item {
    padding: 0.8rem 1rem;
    gap: 0.8rem;
  }
  
  .ranking-item .rank {
    font-size: 1rem;
    min-width: 35px;
  }
  
  .ranking-item .avatar {
    width: 40px;
    height: 40px;
    font-size: 1rem;
  }
  
  .ranking-item .player-name {
    font-size: 0.9rem;
  }
  
  .ranking-item .score {
    font-size: 1rem;
  }
}

/* TRÈS PETIT MOBILE (moins de 480px) */
@media (max-width: 479px) {
  .main-content {
    padding: 1rem;
    padding-bottom: 190px; /* Encore plus d'espace sur très petit mobile */
  }
  
  .user-position-footer {
    padding: 0.6rem 0.8rem;
  }
  
  .user-ranking-item {
    padding: 0.6rem 0.8rem;
  }
  
  .user-ranking-item .rank {
    font-size: 0.9rem;
    min-width: 30px;
  }
  
  .user-ranking-item .avatar {
    width: 35px;
    height: 35px;
    font-size: 0.9rem;
  }
  
  .user-ranking-item .player-name {
    font-size: 0.8rem;
  }
  
  .user-ranking-item .score {
    font-size: 0.9rem;
  }
}
</style>