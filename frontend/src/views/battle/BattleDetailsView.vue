<template>
  <div class="battle-details-page">
    <!-- Header -->
    <div class="details-header">
      <h1 class="details-title">BATTLE RESULTS</h1>
    </div>

    <!-- Battle Status Banner -->
    <div class="battle-status-banner">
      <div class="status-content" :class="{ 'victory': isPlayerWinner, 'defeat': !isPlayerWinner && !isTie, 'tie': isTie }">
        <div class="status-icon">
          <svg v-if="isPlayerWinner" width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
          </svg>
          <svg v-else-if="isTie" width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
          </svg>
          <svg v-else width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 11H7v-2h10v2z"/>
          </svg>
        </div>
        <div class="status-text">
          <div class="status-main">{{ isPlayerWinner ? 'VICTORY' : (isTie ? 'TIE GAME' : 'DEFEAT') }}</div>
          <div class="status-sub">{{ isPlayerWinner ? 'Excellent performance!' : (isTie ? 'Great match!' : 'Keep training!') }}</div>
        </div>
      </div>
    </div>

    <!-- Players Comparison -->
    <div class="battle-comparison">
      <!-- Player Card -->
      <div class="player-card" :class="{ 'winner-card': isPlayerWinner }">
        <div class="card-header">
          <div class="player-identity">
            <div class="avatar-section">
              <div class="avatar" :style="getAvatarStyle(currentPlayer)">
                <img 
                  v-if="currentPlayer.avatar && currentPlayer.avatar !== currentPlayer.name?.charAt(0)" 
                  :src="getAvatarUrl(currentPlayer)" 
                  :alt="currentPlayer.name"
                  class="avatar-image"
                />
                <span v-else class="avatar-initial">{{ currentPlayer.name?.charAt(0) || 'Y' }}</span>
              </div>
              <div class="winner-indicator" v-if="isPlayerWinner">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
              </div>
            </div>
            <div class="player-details">
              <h3 class="player-name">{{ currentPlayer.name }}</h3>
              <span class="player-country">{{ currentPlayer.flag }}</span>
            </div>
          </div>
        </div>

        <div class="performance-overview">
          <div class="score-display">
            <div class="score-label">Final Score</div>
            <div class="score-value">{{ playerFinalScore }}</div>
          </div>

          <div class="stats-grid">
            <div class="stat-box">
              <div class="stat-number">{{ playerCorrectAnswers }}/{{ totalQuestions }}</div>
              <div class="stat-label">Correct</div>
            </div>
            <div class="stat-box">
              <div class="stat-number">{{ formatTime(playerTotalTime) }}</div>
              <div class="stat-label">Total Time</div>
            </div>
            <div class="stat-box">
              <div class="stat-number">{{ formatTime(playerAverageTime) }}</div>
              <div class="stat-label">Avg Time</div>
            </div>
          </div>

          <div class="questions-performance">
            <div class="performance-title">Question Results</div>
            <div class="performance-grid">
              <div 
                v-for="(answer, index) in playerAnswers" 
                :key="index"
                class="question-result"
                :class="{ 'correct-result': answer.correct, 'incorrect-result': !answer.correct }"
              >
                <div class="question-label">Q{{ index + 1 }}</div>
                <div class="result-indicator">
                  <svg v-if="answer.correct" width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                  </svg>
                  <svg v-else width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                  </svg>
                </div>
                <div class="time-display">{{ formatTime(answer.time) }}</div>
                <div class="points-earned">{{ answer.correct ? '+100' : '0' }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- VS Separator -->
      <div class="vs-separator">
        <div class="vs-line"></div>
        <div class="vs-circle">
          <span class="vs-text">VS</span>
        </div>
        <div class="vs-line"></div>
      </div>

      <!-- Opponent Card -->
      <div class="player-card" :class="{ 'winner-card': !isPlayerWinner && !isTie }">
        <div class="card-header">
          <div class="player-identity">
            <div class="avatar-section">
              <div class="avatar" :style="getAvatarStyle(opponent)">
                <img 
                  v-if="opponent.avatar && opponent.avatar !== opponent.name?.charAt(0)" 
                  :src="getAvatarUrl(opponent)" 
                  :alt="opponent.name"
                  class="avatar-image"
                />
                <span v-else class="avatar-initial">{{ opponent.name?.charAt(0) || 'O' }}</span>
              </div>
              <div class="winner-indicator" v-if="!isPlayerWinner && !isTie">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
              </div>
            </div>
            <div class="player-details">
              <h3 class="player-name">{{ opponent.name }}</h3>
              <span class="player-country">{{ opponent.flag }}</span>
            </div>
          </div>
        </div>

        <div class="performance-overview">
          <div class="score-display">
            <div class="score-label">Final Score</div>
            <div class="score-value">{{ opponentFinalScore }}</div>
          </div>

          <div class="stats-grid">
            <div class="stat-box">
              <div class="stat-number">{{ opponentCorrectAnswers }}/{{ totalQuestions }}</div>
              <div class="stat-label">Correct</div>
            </div>
            <div class="stat-box">
              <div class="stat-number">{{ formatTime(opponentTotalTime) }}</div>
              <div class="stat-label">Total Time</div>
            </div>
            <div class="stat-box">
              <div class="stat-number">{{ formatTime(opponentAverageTime) }}</div>
              <div class="stat-label">Avg Time</div>
            </div>
          </div>

          <div class="questions-performance">
            <div class="performance-title">Question Results</div>
            <div class="performance-grid">
              <div 
                v-for="(answer, index) in opponentAnswers" 
                :key="index"
                class="question-result"
                :class="{ 'correct-result': answer.correct, 'incorrect-result': !answer.correct }"
              >
                <div class="question-label">Q{{ index + 1 }}</div>
                <div class="result-indicator">
                  <svg v-if="answer.correct" width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                  </svg>
                  <svg v-else width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                  </svg>
                </div>
                <div class="time-display">{{ formatTime(answer.time) }}</div>
                <div class="points-earned">{{ answer.correct ? '+100' : '0' }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Points Summary -->
    <div class="points-summary">
      <div class="points-card" :class="{ 'points-gain': pointsChange >= 0, 'points-loss': pointsChange < 0 }">
        <div class="points-icon">
          <svg v-if="pointsChange >= 0" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
            <path d="M7 14l5-5 5 5z"/>
          </svg>
          <svg v-else width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
            <path d="M7 10l5 5 5-5z"/>
          </svg>
        </div>
        <div class="points-info">
          <div class="points-amount">{{ pointsChange >= 0 ? '+' : '' }}{{ pointsChange }}</div>
          <div class="points-label">POINTS {{ pointsChange >= 0 ? 'GAINED' : 'LOST' }}</div>
        </div>
      </div>
    </div>

    <!-- Detailed Analysis (Collapsible) -->
    <div class="detailed-analysis">
      <div class="analysis-header" @click="toggleAnalysis">
        <h2 class="analysis-title">DETAILED ANALYSIS</h2>
        <div class="analysis-toggle">
          <svg 
            width="20" 
            height="20" 
            viewBox="0 0 24 24" 
            fill="currentColor"
            :class="{ 'rotated': showAnalysis }"
          >
            <path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"/>
          </svg>
        </div>
      </div>
      
      <div class="analysis-content" v-show="showAnalysis">
        <div class="questions-breakdown">
          <div 
            v-for="(question, index) in questionsData" 
            :key="index"
            class="question-analysis"
          >
            <div class="question-header" @click="toggleQuestion(index)">
              <div class="question-number">{{ index + 1 }}</div>
              <div class="question-text">{{ question.text }}</div>
              <div class="question-toggle">
                <svg 
                  width="16" 
                  height="16" 
                  viewBox="0 0 24 24" 
                  fill="currentColor"
                  :class="{ 'rotated': expandedQuestions.includes(index) }"
                >
                  <path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"/>
                </svg>
              </div>
            </div>
            
            <div class="question-details" v-show="expandedQuestions.includes(index)">
              <div class="answers-breakdown">
                <div class="answer-comparison">
                  <div class="player-answer">
                    <div class="answer-player-info">
                      <div class="mini-avatar" :style="getAvatarStyle(currentPlayer)">
                        <span class="mini-initial">{{ currentPlayer.name?.charAt(0) || 'Y' }}</span>
                      </div>
                      <span class="answer-player-name">Your Answer</span>
                    </div>
                    <div class="answer-content">
                      <div class="answer-value" :class="{ 'correct-answer': playerAnswers[index].correct, 'incorrect-answer': !playerAnswers[index].correct }">
                        {{ playerAnswers[index].text }}
                      </div>
                      <div class="answer-metadata">
                        <span class="answer-time">{{ formatTime(playerAnswers[index].time) }}</span>
                        <div class="answer-status" :class="{ 'status-correct': playerAnswers[index].correct, 'status-incorrect': !playerAnswers[index].correct }">
                          <svg v-if="playerAnswers[index].correct" width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                          </svg>
                          <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                          </svg>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <div class="player-answer">
                    <div class="answer-player-info">
                      <div class="mini-avatar" :style="getAvatarStyle(opponent)">
                        <span class="mini-initial">{{ opponent.name?.charAt(0) || 'O' }}</span>
                      </div>
                      <span class="answer-player-name">{{ opponent.name }}</span>
                    </div>
                    <div class="answer-content">
                      <div class="answer-value" :class="{ 'correct-answer': opponentAnswers[index].correct, 'incorrect-answer': !opponentAnswers[index].correct }">
                        {{ opponentAnswers[index].text }}
                      </div>
                      <div class="answer-metadata">
                        <span class="answer-time">{{ formatTime(opponentAnswers[index].time) }}</span>
                        <div class="answer-status" :class="{ 'status-correct': opponentAnswers[index].correct, 'status-incorrect': !opponentAnswers[index].correct }">
                          <svg v-if="opponentAnswers[index].correct" width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                          </svg>
                          <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                          </svg>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="correct-answer-reveal">
                  <div class="correct-indicator">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                      <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                    <span class="correct-label">Correct Answer</span>
                  </div>
                  <div class="correct-answer-text">{{ question.correctAnswer }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="battle-actions">
      <button class="btn-return" @click="returnToBattles">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
          <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/>
        </svg>
        <span>Return to Battles</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()

const battleId = route.params.id

// UI State
const showAnalysis = ref(false)
const expandedQuestions = ref([])

// Données par défaut
const currentPlayer = ref({
  id: 1,
  name: 'YOU',
  avatar: 'Y',
  flag: '🇨🇭'
})

const opponent = ref({
  id: 2,
  name: 'H.IBRAHIM',
  avatar: 'H',
  flag: '🇩🇪'
})

// Données de bataille par défaut
const playerAnswers = ref([
  { correct: true, text: 'Navitimer', time: 8.5 },
  { correct: true, text: '1884', time: 12.3 },
  { correct: false, text: 'ETA 2824', time: 15.7 },
  { correct: true, text: '500m', time: 6.2 },
  { correct: false, text: 'Navitimer', time: 22.1 }
])

const opponentAnswers = ref([
  { correct: true, text: 'Navitimer', time: 9.8 },
  { correct: false, text: '1905', time: 18.4 },
  { correct: false, text: 'Sellita SW200', time: 11.2 },
  { correct: true, text: '500m', time: 7.5 },
  { correct: true, text: 'Cosmonaute', time: 14.6 }
])

const questionsData = ref([
  {
    text: 'Which Breitling collection is known for its aviation heritage?',
    correctAnswer: 'Navitimer'
  },
  {
    text: 'What year was Breitling founded?',
    correctAnswer: '1884'
  },
  {
    text: 'Which movement powers the Breitling B01?',
    correctAnswer: 'In-house chronograph'
  },
  {
    text: 'What is the water resistance of the Superocean?',
    correctAnswer: '500m'
  },
  {
    text: 'Which Breitling watch was worn by astronauts?',
    correctAnswer: 'Cosmonaute'
  }
])

// UI Methods
const toggleAnalysis = () => {
  showAnalysis.value = !showAnalysis.value
}

const toggleQuestion = (index) => {
  const idx = expandedQuestions.value.indexOf(index)
  if (idx > -1) {
    expandedQuestions.value.splice(idx, 1)
  } else {
    expandedQuestions.value.push(index)
  }
}

// Data Methods
const getAvatarUrl = (user) => {
  if (!user || !user.avatar) {
    return null
  }
  
  if (typeof user.avatar === 'string' && user.avatar.length === 1) {
    return null
  }
  
  const avatarUrl = user.avatar.startsWith('http') ? user.avatar : `http://localhost:8000/${user.avatar}`
  
  return avatarUrl
}

const getCountryFlag = (posIdOrCountry) => {
  if (typeof posIdOrCountry === 'string') {
    const flagsByCode = {
      'DE': '🇩🇪',
      'FR': '🇫🇷',
      'RO': '🇷🇴',
      'PT': '🇵🇹',
      'US': '🇺🇸',
      'CH': '🇨🇭',
      'IT': '🇮🇹',
      'ES': '🇪🇸',
      'GB': '🇬🇧',
      'BE': '🇧🇪'
    }
    return flagsByCode[posIdOrCountry] || '🌍'
  }
  
  const flagMapping = {
    1: '🇨🇭', 2: '🇫🇷', 3: '🇩🇪', 4: '🇮🇹', 5: '🇪🇸', 
    6: '🇵🇹', 7: '🇷🇴', 8: '🇺🇸', 9: '🇬🇧', 10: '🇧🇪'
  }
  
  return flagMapping[posIdOrCountry] || '🇨🇭'
}

const getUserFlag = (userData) => {
  if (userData.pos && userData.pos.country_flag) {
    return userData.pos.country_flag
  }
  
  if (userData.pos_id) {
    const flagFromPosId = getCountryFlag(userData.pos_id)
    return flagFromPosId
  }
  
  return '🇨🇭'
}

const loadCurrentUserData = async () => {
  try {
    const userResponse = await fetch('http://localhost:8000/api/user', {
      credentials: 'include',
      headers: { 'Accept': 'application/json' }
    })
    
    if (!userResponse.ok) {
      throw new Error('Failed to fetch authenticated user')
    }
    
    const userData = await userResponse.json()
    
    const fullUserResponse = await fetch(`http://localhost:8000/api/v1/users/${userData.id}`, {
      credentials: 'include',
      headers: { 'Accept': 'application/json' }
    })
    
    let fullUserData = userData
    
    if (fullUserResponse.ok) {
      const fullUserResponseData = await fullUserResponse.json()
      fullUserData = fullUserResponseData.data || fullUserResponseData || userData
    }
    
    currentPlayer.value = {
      id: fullUserData.id || userData.id,
      name: fullUserData.username || userData.username || 'YOU',
      avatar: fullUserData.avatar || userData.avatar || null,
      flag: getUserFlag(fullUserData) || '🇨🇭'
    }
    
  } catch (error) {
    currentPlayer.value = {
      id: 1,
      name: 'YOU',
      avatar: null,
      flag: '🇨🇭'
    }
  }
}

onMounted(async () => {
  await loadCurrentUserData()
  
  if (battleId) {
    await loadBattleFromAPI(battleId)
  } else {
    router.push('/battle')
  }
})

const loadBattleFromAPI = async (battleId) => {
  try {
    const response = await fetch(`http://localhost:8000/api/v1/battles/${battleId}`, {
      credentials: 'include',
      headers: { 'Accept': 'application/json' }
    })
    
    if (!response.ok) {
      throw new Error(`API Error: ${response.status}`)
    }
    
    const battleDetail = await response.json()
    const battle = battleDetail.data || battleDetail
    
    if (!battle.challenger_summary || !battle.challenged_summary) {
      throw new Error('Données de bataille incomplètes')
    }
    
    const isCurrentUserChallenger = battle.challenger_id === currentPlayer.value.id
    
    if (isCurrentUserChallenger) {
      opponent.value = {
        id: battle.challenged?.id || battle.challenged_id,
        name: battle.challenged?.username || battle.challenged?.name || 'Adversaire',
        avatar: battle.challenged?.avatar || null,
        flag: battle.challenged?.pos?.country_flag || getCountryCodeSafe(battle.challenged) || '🇨🇭'
      }
      
      playerAnswers.value = (battle.challenger_summary?.answers || []).map(answer => ({
        correct: answer.correct || false,
        text: answer.selectedAnswer || answer.text || 'Pas de réponse',
        time: answer.time || 0
      }))
      
      opponentAnswers.value = (battle.challenged_summary?.answers || []).map(answer => ({
        correct: answer.correct || false,
        text: answer.selectedAnswer || answer.text || 'Pas de réponse',
        time: answer.time || 0
      }))
      
    } else {
      opponent.value = {
        id: battle.challenger?.id || battle.challenger_id,
        name: battle.challenger?.username || battle.challenger?.name || 'Adversaire',
        avatar: battle.challenger?.avatar || null,
        flag: battle.challenger?.pos?.country_flag || getCountryCodeSafe(battle.challenger) || '🇨🇭'
      }
      
      playerAnswers.value = (battle.challenged_summary?.answers || []).map(answer => ({
        correct: answer.correct || false,
        text: answer.selectedAnswer || answer.text || 'Pas de réponse',
        time: answer.time || 0
      }))
      
      opponentAnswers.value = (battle.challenger_summary?.answers || []).map(answer => ({
        correct: answer.correct || false,
        text: answer.selectedAnswer || answer.text || 'Pas de réponse',
        time: answer.time || 0
      }))
    }
    
    if (battle.challenger_summary?.questionsData?.length) {
      questionsData.value = battle.challenger_summary.questionsData
    }
    
  } catch (error) {
    alert(`Erreur lors du chargement de la bataille: ${error.message}`)
    router.push('/battle')
  }
}

const getCountryCodeSafe = (user) => {
  if (!user) return '🇨🇭'
  
  if (user.pos && user.pos.country_flag) {
    return user.pos.country_flag
  }
  
  if (user.pos_id) {
    const countryMapping = {
      1: '🇨🇭', 2: '🇫🇷', 3: '🇩🇪', 4: '🇮🇹', 5: '🇪🇸', 
      6: '🇵🇹', 7: '🇷🇴', 8: '🇺🇸', 9: '🇬🇧', 10: '🇧🇪'
    }
    return countryMapping[user.pos_id] || '🇨🇭'
  }
  
  return '🇨🇭'
}

const returnToBattles = () => {
  router.push('/battle')
}

const totalQuestions = computed(() => questionsData.value.length)

const playerCorrectAnswers = computed(() => 
  playerAnswers.value.filter(answer => answer.correct).length
)

const opponentCorrectAnswers = computed(() => 
  opponentAnswers.value.filter(answer => answer.correct).length
)

const playerTotalTime = computed(() => 
  playerAnswers.value.reduce((total, answer) => total + answer.time, 0)
)

const opponentTotalTime = computed(() => 
  opponentAnswers.value.reduce((total, answer) => total + answer.time, 0)
)

const playerAverageTime = computed(() => 
  playerTotalTime.value / totalQuestions.value
)

const opponentAverageTime = computed(() => 
  opponentTotalTime.value / totalQuestions.value
)

const playerFinalScore = computed(() => {
  const correctScore = playerCorrectAnswers.value * 100
  const timeBonus = Math.max(0, (150 - playerTotalTime.value) * 2) 
  return Math.round(correctScore + timeBonus)
})

const opponentFinalScore = computed(() => {
  const correctScore = opponentCorrectAnswers.value * 100
  const timeBonus = Math.max(0, (150 - opponentTotalTime.value) * 2)
  return Math.round(correctScore + timeBonus)
})

const isPlayerWinner = computed(() => 
  playerFinalScore.value > opponentFinalScore.value
)

const isTie = computed(() => 
  playerFinalScore.value === opponentFinalScore.value
)

const pointsChange = computed(() => {
  if (isPlayerWinner.value) return +300
  if (isTie.value) return +100
  return -70
})

const formatTime = (seconds) => {
  if (seconds < 60) {
    return `${seconds.toFixed(1)}s`
  }
  const mins = Math.floor(seconds / 60)
  const secs = (seconds % 60).toFixed(1)
  return `${mins}m ${secs}s`
}

const getAvatarStyle = (player) => {
  const gradients = [
    'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
    'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
    'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
    'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
    'linear-gradient(135deg, #fa709a 0%, #fee140 100%)'
  ]
  
  const index = player.id % gradients.length
  return {
    background: gradients[index]
  }
}

</script>

<style scoped>
.battle-details-page {
  min-height: 100vh;
  width: 100%;
  background: linear-gradient(135deg, #072C54 0%, #1e3a8a 100%);
  color: white;
  padding: 1rem;
  padding-bottom: 100px;
  box-sizing: border-box;
  overflow-y: auto;
}

/* Header */
.details-header {
  text-align: center;
  margin-bottom: 2rem;
  padding: 1rem 0;
}

.details-title {
  font-size: 2rem;
  font-weight: 800;
  color: #F7C72C;
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 1.5px;
}

/* Battle Status Banner */
.battle-status-banner {
  margin-bottom: 2rem;
  display: flex;
  justify-content: center;
}

.status-content {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.2rem 2rem;
  border-radius: 16px;
  backdrop-filter: blur(15px);
  border: 2px solid;
  transition: all 0.3s ease;
}

.status-content.victory {
  background: rgba(76, 175, 80, 0.15);
  border-color: #4CAF50;
}

.status-content.defeat {
  background: rgba(244, 67, 54, 0.15);
  border-color: #F44336;
}

.status-content.tie {
  background: rgba(247, 199, 44, 0.15);
  border-color: #F7C72C;
}

.status-icon svg {
  color: inherit;
}

.status-content.victory .status-icon {
  color: #4CAF50;
}

.status-content.defeat .status-icon {
  color: #F44336;
}

.status-content.tie .status-icon {
  color: #F7C72C;
}

.status-text {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
}

.status-main {
  font-size: 1.4rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.status-content.victory .status-main {
  color: #4CAF50;
}

.status-content.defeat .status-main {
  color: #F44336;
}

.status-content.tie .status-main {
  color: #F7C72C;
}

.status-sub {
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.8);
}

/* Battle Comparison */
.battle-comparison {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-bottom: 2rem;
}

.player-card {
  background: rgba(255, 255, 255, 0.08);
  border-radius: 20px;
  padding: 1.5rem;
  backdrop-filter: blur(15px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
}

.player-card.winner-card {
  border-color: #4CAF50;
  box-shadow: 0 0 25px rgba(76, 175, 80, 0.2);
  background: rgba(76, 175, 80, 0.08);
}

.card-header {
  margin-bottom: 1.5rem;
}

.player-identity {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.avatar-section {
  position: relative;
}

.avatar {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 1.5rem;
  border: 3px solid rgba(255, 255, 255, 0.2);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
  overflow: hidden;
  transition: all 0.3s ease;
}

.avatar-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: top;
  border-radius: 50%;
}

.avatar-initial {
  font-weight: bold;
  color: white;
  text-transform: uppercase;
}

.winner-indicator {
  position: absolute;
  top: -6px;
  right: -6px;
  background: #4CAF50;
  color: white;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid white;
  box-shadow: 0 2px 10px rgba(76, 175, 80, 0.4);
}

.player-details {
  flex: 1;
}

.player-name {
  font-size: 1.2rem;
  font-weight: 700;
  margin: 0 0 0.3rem 0;
  color: white;
}

.player-country {
  font-size: 1.1rem;
}

.performance-overview {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.score-display {
  text-align: center;
  padding: 1.2rem;
  background: rgba(0, 0, 0, 0.2);
  border-radius: 16px;
  border-top: 3px solid rgba(247, 199, 44, 0.5);
}

.score-label {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.7);
  margin-bottom: 0.5rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.score-value {
  font-size: 2.5rem;
  font-weight: 800;
  color: #F7C72C;
  text-shadow: 0 0 20px rgba(247, 199, 44, 0.3);
}

.stats-grid {
  display: flex;
  gap: 1rem;
  justify-content: space-between;
}

.stat-box {
  flex: 1;
  text-align: center;
  background: rgba(255, 255, 255, 0.05);
  padding: 1rem;
  border-radius: 12px;
}

.stat-number {
  font-size: 1rem;
  font-weight: 700;
  color: #F7C72C;
  margin-bottom: 0.3rem;
}

.stat-label {
  font-size: 0.7rem;
  color: rgba(255, 255, 255, 0.6);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.questions-performance {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.performance-title {
  font-size: 0.8rem;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.8);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.performance-grid {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.question-result {
  display: flex;
  align-items: center;
  gap: 0.8rem;
  padding: 0.8rem;
  border-radius: 10px;
  transition: all 0.3s ease;
}

.question-result.correct-result {
  background: rgba(76, 175, 80, 0.15);
  border: 1px solid rgba(76, 175, 80, 0.3);
}

.question-result.incorrect-result {
  background: rgba(244, 67, 54, 0.15);
  border: 1px solid rgba(244, 67, 54, 0.3);
}

.question-label {
  font-size: 0.7rem;
  font-weight: 700;
  color: rgba(255, 255, 255, 0.7);
  min-width: 25px;
}

.result-indicator {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.question-result.correct-result .result-indicator {
  background: #4CAF50;
}

.question-result.incorrect-result .result-indicator {
  background: #F44336;
}

.time-display {
  font-size: 0.7rem;
  color: rgba(255, 255, 255, 0.6);
  flex: 1;
  text-align: center;
}

.points-earned {
  font-size: 0.7rem;
  font-weight: 700;
  color: #F7C72C;
  min-width: 35px;
  text-align: right;
}

.vs-separator {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem 0;
  order: 2;
}

.vs-line {
  flex: 1;
  height: 2px;
  background: linear-gradient(90deg, transparent, rgba(247, 199, 44, 0.5), transparent);
}

.vs-circle {
  background: linear-gradient(135deg, #F7C72C 0%, #FFD700 100%);
  border-radius: 50%;
  width: 50px;
  height: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 1rem;
  box-shadow: 0 0 20px rgba(247, 199, 44, 0.3);
}

.vs-text {
  font-weight: 800;
  color: #072C54;
  font-size: 0.9rem;
  letter-spacing: 0.5px;
}

/* Points Summary */
.points-summary {
  text-align: center;
  margin-bottom: 2rem;
}

.points-card {
  display: inline-flex;
  align-items: center;
  gap: 1rem;
  padding: 1.2rem 2rem;
  border-radius: 16px;
  border: 2px solid;
  backdrop-filter: blur(10px);
}

.points-card.points-gain {
  background: rgba(76, 175, 80, 0.15);
  border-color: #4CAF50;
}

.points-card.points-loss {
  background: rgba(244, 67, 54, 0.15);
  border-color: #F44336;
}

.points-icon svg {
  color: inherit;
}

.points-card.points-gain .points-icon {
  color: #4CAF50;
}

.points-card.points-loss .points-icon {
  color: #F44336;
}

.points-info {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.points-amount {
  font-size: 1.8rem;
  font-weight: 800;
}

.points-card.points-gain .points-amount {
  color: #4CAF50;
}

.points-card.points-loss .points-amount {
  color: #F44336;
}

.points-label {
  font-size: 0.7rem;
  color: rgba(255, 255, 255, 0.7);
  font-weight: 600;
  letter-spacing: 1px;
  text-transform: uppercase;
}

/* Detailed Analysis (Collapsible) */
.detailed-analysis {
  margin-bottom: 2rem;
}

.analysis-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.2rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.analysis-header:hover {
  background: rgba(255, 255, 255, 0.08);
  border-color: rgba(247, 199, 44, 0.3);
}

.analysis-title {
  font-size: 1.2rem;
  font-weight: 700;
  color: #F7C72C;
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.analysis-toggle svg {
  color: #F7C72C;
  transition: transform 0.3s ease;
}

.analysis-toggle svg.rotated {
  transform: rotate(180deg);
}

.analysis-content {
  padding-top: 1rem;
}

.questions-breakdown {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.question-analysis {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  overflow: hidden;
}

.question-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.question-header:hover {
  background: rgba(255, 255, 255, 0.05);
}

.question-number {
  background: #F7C72C;
  color: #072C54;
  font-weight: 800;
  font-size: 0.8rem;
  padding: 0.5rem 0.8rem;
  border-radius: 12px;
  min-width: 40px;
  text-align: center;
  flex-shrink: 0;
}

.question-text {
  font-size: 0.9rem;
  line-height: 1.4;
  color: white;
  font-weight: 500;
  flex: 1;
}

.question-toggle svg {
  color: rgba(255, 255, 255, 0.6);
  transition: transform 0.3s ease;
}

.question-toggle svg.rotated {
  transform: rotate(180deg);
}

.question-details {
  padding: 0 1rem 1rem 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.answers-breakdown {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  padding-top: 1rem;
}

.answer-comparison {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.player-answer {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.05);
}

.answer-player-info {
  display: flex;
  align-items: center;
  gap: 0.8rem;
  min-width: 120px;
}

.mini-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8rem;
  font-weight: bold;
  color: white;
  flex-shrink: 0;
}

.mini-initial {
  text-transform: uppercase;
}

.answer-player-name {
  font-size: 0.8rem;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.8);
}

.answer-content {
  flex: 1;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}

.answer-value {
  font-size: 0.9rem;
  font-weight: 500;
  padding: 0.5rem 0.8rem;
  border-radius: 8px;
  flex: 1;
}

.answer-value.correct-answer {
  background: rgba(76, 175, 80, 0.2);
  color: #4CAF50;
  border: 1px solid rgba(76, 175, 80, 0.3);
}

.answer-value.incorrect-answer {
  background: rgba(244, 67, 54, 0.2);
  color: #F44336;
  border: 1px solid rgba(244, 67, 54, 0.3);
}

.answer-metadata {
  display: flex;
  align-items: center;
  gap: 0.8rem;
}

.answer-time {
  font-size: 0.7rem;
  color: rgba(255, 255, 255, 0.6);
  min-width: 40px;
  text-align: right;
}

.answer-status {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.answer-status.status-correct {
  background: #4CAF50;
}

.answer-status.status-incorrect {
  background: #F44336;
}

.correct-answer-reveal {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: rgba(247, 199, 44, 0.1);
  border: 1px solid rgba(247, 199, 44, 0.2);
  border-radius: 12px;
  margin-top: 0.5rem;
}

.correct-indicator {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  min-width: 120px;
}

.correct-indicator svg {
  color: #F7C72C;
}

.correct-label {
  font-size: 0.8rem;
  font-weight: 600;
  color: #F7C72C;
}

.correct-answer-text {
  font-size: 0.9rem;
  font-weight: 600;
  color: #F7C72C;
  background: rgba(247, 199, 44, 0.1);
  padding: 0.5rem 0.8rem;
  border-radius: 8px;
  border: 1px solid rgba(247, 199, 44, 0.2);
  flex: 1;
}

/* Actions */
.battle-actions {
  display: flex;
  justify-content: center;
  margin-top: 2rem;
}

.btn-return {
  display: flex;
  align-items: center;
  gap: 0.8rem;
  padding: 1rem 2rem;
  background: #F7C72C;
  color: #072C54;
  border: none;
  border-radius: 12px;
  font-weight: 700;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.btn-return:hover {
  background: #E6B625;
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(247, 199, 44, 0.3);
}

/* Responsive */
@media (min-width: 768px) {
  .battle-details-page {
    margin-left: 280px;
    width: calc(100% - 280px);
    padding: 2rem;
  }
  
  .battle-comparison {
    flex-direction: row;
    align-items: flex-start;
  }
  
  .player-card {
    flex: 1;
  }
  
  .vs-separator {
    flex-direction: column;
    width: 80px;
    height: 200px;
    order: 0;
  }
  
  .vs-line {
    width: 2px;
    height: 80px;
    background: linear-gradient(180deg, transparent, rgba(247, 199, 44, 0.5), transparent);
  }
  
  .answer-comparison {
    flex-direction: row;
    gap: 1.5rem;
  }
  
  .player-answer {
    flex: 1;
  }
}

@media (max-width: 767px) {
  .battle-details-page {
    margin-left: 0;
    width: 100%;
    padding: 1rem;
    padding-bottom: 80px;
  }
  
  .battle-comparison {
    display: flex;
    flex-direction: column;
    gap: 0;
  }
  
  .player-card:first-child {
    order: 1;
  }
  
  .vs-separator {
    order: 2;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem 0;
  }
  
  .player-card:last-child {
    order: 3;
  }
  
  .vs-line {
    flex: 1;
    height: 2px;
    background: linear-gradient(90deg, transparent, rgba(247, 199, 44, 0.5), transparent);
  }
  
  .details-title {
    font-size: 1.6rem;
  }
  
  .status-content {
    padding: 1rem 1.5rem;
  }
  
  .status-main {
    font-size: 1.2rem;
  }
  
  .avatar {
    width: 50px;
    height: 50px;
    font-size: 1.2rem;
  }
  
  .score-value {
    font-size: 2rem;
  }
  
  .points-amount {
    font-size: 1.5rem;
  }
  
  .analysis-title {
    font-size: 1rem;
  }
  
  .question-text {
    font-size: 0.8rem;
  }
}
</style>