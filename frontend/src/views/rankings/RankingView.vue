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
              :class="{ active: selectedFilter !== 'world' }"
              @click="toggleDropdown"
            >
              {{ selectedFilterLabel }}
              <i class="dropdown-icon">▼</i>
            </button>
            <div class="dropdown-menu" v-if="showDropdown">
              <a
                v-for="country in availableCountries"
                :key="country"
                href="#"
                @click="selectCountry(country)"
              >
                {{ country }}
              </a>
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
            highlight: player.highlight,
            'top-16': player.rank <= 16
          }"
          @click="openPlayerProfile(player)"
        >
          <div class="rank">{{ player.displayRank }}.</div>

          <div class="player-info">
            <div class="avatar" :style="getAvatarStyle(player)">
              <img :src="getAvatarUrl(player.avatar)" alt="avatar" class="avatar-image" />
            </div>
            <div class="player-details">
              <h3 class="player-name">{{ player.name }}</h3>
              <div class="country-flag">{{ player.flag }}</div>
            </div>
          </div>

          <div class="score">
            {{ player.score.toLocaleString('fr-CH').replace(/\u202F/g, "'") }} PTS
          </div>
        </div>

        <!-- See All Button -->
        <button class="see-all-btn" @click="seeAll" v-if="!showingAll">SEE ALL</button>
      </div>
    </div>

    <!-- Fixed User Position Footer -->
    <div class="user-position-footer">
      <div class="user-ranking-item" @click="openPlayerProfile(currentUser)">
        <div class="rank">{{ currentUser.rank }}.</div>

        <div class="player-info">
          <div class="avatar">
            <img :src="getAvatarUrl(currentUser.avatar)" alt="avatar" class="avatar-image" />
          </div>
          <div class="player-details">
            <h3 class="player-name">
              {{ currentUser.name }}
            </h3>
            <div class="country-flag">{{ currentUser.flag }}</div>
          </div>
        </div>

        <div class="score">
          {{ currentUser.score.toLocaleString('fr-CH').replace(/\u202F/g, "'") }} PTS
        </div>
      </div>
    </div>

    <!-- Player Profile Popup -->
    <div class="popup-overlay" v-if="showPlayerPopup" @click="closePlayerPopup">
      <div class="player-popup" @click.stop>
        <button class="close-btn" @click="closePlayerPopup">✕</button>

        <div class="popup-header">
          <div class="popup-avatar" :style="getAvatarUrl(selectedPlayer)">
            <img :src="getAvatarUrl(selectedPlayer?.avatar)" alt="avatar" class="avatar-image" />
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
          <div class="score-value">
            {{ selectedPlayer?.score.toLocaleString('fr-CH').replace(/\u202F/g, "'") }} PTS PTS
          </div>
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
            <h3>FAVORITES WATCHES</h3>
          </div>
          <div class="watches-grid">
            <div
              v-for="watch in selectedPlayer?.topWatches"
              :key="watch.id"
              class="watch-item"
              :title="watch.name"
            >
              <div class="watch-placeholder avatar-placeholder">
                <template v-if="watch.photo">
                  <img
                    :src="getRewardImageUrl(watch.photo)"
                    :alt="watch.name"
                    class="avatar-image"
                  />
                </template>
                <template v-else>
                  <span>{{ watch.name }}</span>
                </template>
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

const displayedPlayers = computed(() => {
  // Filtrage par pays si ce n'est pas "world"
  let filteredPlayers = rankingPlayers.value

  if (selectedFilter.value !== 'world') {
    filteredPlayers = filteredPlayers.filter(
      (player) => player.country.toLowerCase() === selectedFilter.value
    )
  }

  // Recalcule le rank pour affichage (1, 2, 3...)
  const playersWithLocalRank = filteredPlayers.map((player, index) => ({
    ...player,
    displayRank: index + 1
  }))

  // Limite à top 20 si pas "see all"
  if (!showingAll.value) {
    return playersWithLocalRank.slice(0, 20)
  }

  return playersWithLocalRank
})

const selectedFilterLabel = computed(() => {
  if (selectedFilter.value === 'world') return 'Select your country'
  return selectedFilter.value.charAt(0).toUpperCase() + selectedFilter.value.slice(1)
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

const rankingPlayers = ref([])

const loadUsersRanking = async () => {
  try {
    const res = await fetch('http://localhost:8000/api/v1/users', {
      credentials: 'include',
      headers: {
        Accept: 'application/json'
      }
    })

    if (!res.ok) throw new Error('Erreur lors du chargement des utilisateurs')

    const data = await res.json()
    console.log('✅ Utilisateurs chargés:', data)

    // Mapper les users pour les adapter à ton format Ranking
    rankingPlayers.value = data.data
      .filter((user) => user.is_BS === true)
      .map((user, index) => ({
        id: user.id,
        rank: index + 1, // 👈 on trie + on met le rang
        name: user.username.toUpperCase(),
        country: user.pos?.country || 'Unknown',
        score: user.elo_score || 0,
        avatar: user.avatar,
        flag: user.pos?.country_flag || '🌍',
        city: user.pos?.address || 'Unknown',
        since: `Reseller since ${user.signup_year}`,
        battleWin: user.battle_won || 0,
        battleLost: user.battle_lost || 0,
        topWatches: user.rewards
          ? user.rewards
              .filter((reward) => reward.pivot?.is_favourite)
              .slice(0, 3)
              .map((reward) => ({
                id: reward.id,
                name: reward.model,
                photo: reward.photo_name
              }))
          : []
      }))
      // On trie par elo_score DESC
      .sort((a, b) => b.score - a.score)
      // On attribue le rang correct après tri
      .map((player, index) => ({
        ...player,
        rank: index + 1
      }))
  } catch (err) {
    console.error('❌ Erreur API users ranking:', err.message)
  }
}

const getRewardImageUrl = (photoName) => {
  return `http://localhost:8000/${photoName}`
}

const currentUser = ref({
  id: null,
  rank: 'N/A',
  name: '',
  country: '',
  score: 0,
  avatar: '',
  flag: '',
  city: '',
  since: '',
  battleWin: 0,
  battleLost: 0,
  topWatches: []
})

const loadCurrentUser = async () => {
  try {
    // Étape 1 : Vérifier que l'utilisateur est connecté
    const res1 = await fetch('http://localhost:8000/api/user', {
      credentials: 'include',
      headers: {
        Accept: 'application/json'
      }
    })
    if (!res1.ok) throw new Error('Utilisateur non authentifié')
    const userData = await res1.json()

    const userId = userData.id
    //console.log(userId)

    // Étape 2 : Charger les vraies infos utilisateur via /v1/users/{id}
    const res2 = await fetch(`http://localhost:8000/api/v1/users/${userId}`, {
      credentials: 'include',
      headers: {
        Accept: 'application/json'
      }
    })
    if (!res2.ok) throw new Error('Erreur chargement données utilisateur')
    const fullUser = await res2.json()
    //console.log(fullUser)
    const userDataFull = fullUser.data

    // Étape 3 : Chercher le rank si présent dans le classement
    const foundPlayer = rankingPlayers.value.find((player) => player.id === userId)

    currentUser.value = {
      id: userDataFull.id,
      rank: foundPlayer ? foundPlayer.rank : 'N/A',
      name: userDataFull.username?.toUpperCase() || '',
      country: userDataFull.pos?.country || 'Unknown',
      score: userDataFull.elo_score || 0,
      avatar: userDataFull.avatar || '',
      flag: userDataFull.pos?.country_flag || '🌍',
      city: userDataFull.pos?.address || 'Unknown',
      since: `Reseller since ${userDataFull.signup_year || 'Unknown'}`,
      battleWin: userDataFull.battle_won || 0,
      battleLost: userDataFull.battle_lost || 0,
      topWatches: userDataFull.rewards
        ? userDataFull.rewards
            .filter((reward) => reward.pivot?.is_favourite)
            .slice(0, 3)
            .map((reward) => ({
              id: reward.id,
              name: reward.model,
              photo: reward.photo_name
            }))
        : []
    }
    //console.log(fullUser.data.id)

    console.log('✅ Utilisateur courant chargé :', currentUser.value)
  } catch (err) {
    console.error('❌ Erreur lors du chargement du currentUser:', err.message)
  }
}

const availableCountries = computed(() => {
  const countriesSet = new Set()

  rankingPlayers.value.forEach((player) => {
    if (player.country) {
      countriesSet.add(player.country)
    }
  })

  return Array.from(countriesSet).sort()
})

const getAvatarUrl = (avatarPath) => {
  if (avatarPath) {
    return `http://localhost:8000/${avatarPath}`
  }
  return '/images/icones/default_avatar.png' // fallback si pas d'avatar
}

onMounted(() => {
  checkScreenSize()
  window.addEventListener('resize', checkScreenSize)
  loadUsersRanking().then(() => {
    loadCurrentUser()
  })
})

console.log('RankingView component loaded')
</script>

<style scoped>
.ranking-page {
  min-height: 100vh;
  width: 100vw;
  background: linear-gradient(135deg, #072c54 0%, #1e3a8a 100%);
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
  color: #f7c72c;
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
  border-radius: 8px;
}

.filter-btn.active {
  background: #f7c72c;
  color: #072c54;
}

.dropdown {
  position: relative;
  width: 18rem;
}

.dropdown-btn {
  border-radius: 0 8px 8px 0;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  width: 100%;
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
  background: #072c54;
  border-radius: 0 0 8px 8px;
  overflow: hidden;
  z-index: 10;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
  width: 100%;
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
  cursor: pointer;
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
  border-left: 4px solid #f7c72c;
  box-shadow: 0 4px 15px rgba(247, 199, 44, 0.2);
}

.ranking-item.top-16:hover {
  background: linear-gradient(135deg, rgba(255, 215, 0, 0.25) 0%, rgba(247, 199, 44, 0.2) 100%);
  transform: translateY(-2px);
}

.rank {
  font-size: 1.2rem;
  font-weight: 700;
  color: #f7c72c;
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
.avatar img.avatar-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: top;
  border-radius: 50%;
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
  color: #f7c72c;
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
  background: #f7c72c;
  color: #072c54;
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
  background: #e6b625;
  transform: translateY(-2px);
}

/* USER POSITION FOOTER - FIXED */
.user-position-footer {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background: #072c54;
  border-top: 3px solid #f7c72c;
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
  border: 2px solid #f7c72c;
}

.user-ranking-item .rank {
  font-size: 1.2rem;
  font-weight: 700;
  color: #f7c72c;
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
  border: 2px solid #f7c72c;
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
  background: linear-gradient(135deg, #1e3a8a 0%, #072c54 100%);
  border-radius: 20px;
  padding: 2rem;
  max-width: 400px;
  width: 90%;
  position: relative;
  color: white;
  border: 2px solid #f7c72c;
}

.close-btn {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: #f7c72c;
  color: #072c54;
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
  border: 3px solid #f7c72c;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}
.popup-avatar img.avatar-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: top;
  border-radius: 50%;
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
  color: #f7c72c;
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
.popup-watches .watch-placeholder img {
  width: 100%;
  height: auto;
  object-fit: contain;
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
  background: #f7c72c;
  color: #072c54;
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
