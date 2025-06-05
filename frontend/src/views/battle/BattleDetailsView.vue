<template>
  <div class="battle-details-page">
    <!-- Header -->
    <div class="details-header">
      <button class="close-btn" @click="closeBattleDetails">✕</button>
      <h1 class="details-title">BATTLE DETAILS</h1>
    </div>

    <!-- Players Comparison -->
    <div class="players-comparison">
      <div class="player-section" :class="{ 'winner': isPlayerWinner }">
        <div class="player-info">
          <div class="avatar" :style="getAvatarStyle(currentPlayer)">
            {{ currentPlayer.avatar }}
          </div>
          <h3 class="player-name">{{ currentPlayer.name }}</h3>
          <span class="flag">{{ currentPlayer.flag }}</span>
        </div>
        
        <div class="battle-result">
          <div class="result-badge" v-if="isPlayerWinner">
            <span class="result-text">You won!</span>
          </div>
        </div>

        <!-- Player Stats -->
        <div class="player-stats">
          <div class="stat-row">
            <span class="stat-label">Correct Answers:</span>
            <span class="stat-value">{{ playerCorrectAnswers }}/{{ totalQuestions }}</span>
          </div>
          <div class="stat-row">
            <span class="stat-label">Total Time:</span>
            <span class="stat-value">{{ formatTime(playerTotalTime) }}</span>
          </div>
          <div class="stat-row">
            <span class="stat-label">Average Time:</span>
            <span class="stat-value">{{ formatTime(playerAverageTime) }}</span>
          </div>
        </div>

        <!-- Player Answers -->
        <div class="answers-list">
          <div 
            v-for="(answer, index) in playerAnswers" 
            :key="index"
            class="answer-indicator"
            :class="{ 
              'correct': answer.correct, 
              'incorrect': !answer.correct 
            }"
          >
            <div class="question-number">Q{{ index + 1 }}</div>
            <div class="check-mark" v-if="answer.correct">✓</div>
            <div class="x-mark" v-else>✕</div>
            <div class="answer-time">{{ formatTime(answer.time) }}</div>
            <span class="points">{{ answer.correct ? '+100' : '+0' }}</span>
          </div>
        </div>

        <div class="player-score">
          <div class="score-label">Final Score:</div>
          <div class="score-value">{{ playerFinalScore }}</div>
        </div>
      </div>

      <!-- VS Divider -->
      <div class="vs-divider">
        <span class="vs-text">VS</span>
      </div>

      <div class="player-section" :class="{ 'winner': !isPlayerWinner && !isTie }">
        <div class="player-info">
          <div class="avatar" :style="getAvatarStyle(opponent)">
            {{ opponent.avatar }}
          </div>
          <h3 class="player-name">{{ opponent.name }}</h3>
          <span class="flag">{{ opponent.flag }}</span>
        </div>

        <div class="battle-result">
          <div class="result-badge" v-if="!isPlayerWinner && !isTie">
            <span class="result-text">Winner!</span>
          </div>
        </div>

        <!-- Opponent Stats -->
        <div class="player-stats">
          <div class="stat-row">
            <span class="stat-label">Correct Answers:</span>
            <span class="stat-value">{{ opponentCorrectAnswers }}/{{ totalQuestions }}</span>
          </div>
          <div class="stat-row">
            <span class="stat-label">Total Time:</span>
            <span class="stat-value">{{ formatTime(opponentTotalTime) }}</span>
          </div>
          <div class="stat-row">
            <span class="stat-label">Average Time:</span>
            <span class="stat-value">{{ formatTime(opponentAverageTime) }}</span>
          </div>
        </div>

        <!-- Opponent Answers -->
        <div class="answers-list">
          <div 
            v-for="(answer, index) in opponentAnswers" 
            :key="index"
            class="answer-indicator"
            :class="{ 
              'correct': answer.correct, 
              'incorrect': !answer.correct 
            }"
          >
            <div class="question-number">Q{{ index + 1 }}</div>
            <div class="check-mark" v-if="answer.correct">✓</div>
            <div class="x-mark" v-else>✕</div>
            <div class="answer-time">{{ formatTime(answer.time) }}</div>
            <span class="points">{{ answer.correct ? '+100' : '+0' }}</span>
          </div>
        </div>

        <div class="player-score">
          <div class="score-label">Final Score:</div>
          <div class="score-value">{{ opponentFinalScore }}</div>
        </div>
      </div>
    </div>

    <!-- Points Summary -->
    <div class="points-summary">
      <div class="points-box" :class="{ 'positive': pointsChange >= 0, 'negative': pointsChange < 0 }">
        <div class="points-change">{{ pointsChange >= 0 ? '+' : '' }}{{ pointsChange }}PTS</div>
      </div>
    </div>

    <!-- Questions Review -->
    <div class="questions-review">
      <h2 class="review-title">DETAILED ANSWERS</h2>
      
      <div 
        v-for="(question, index) in questionsData" 
        :key="index"
        class="question-review"
      >
        <h3 class="question-title">QUESTION {{ index + 1 }}</h3>
        <p class="question-text">{{ question.text }}</p>
        
        <div class="answers-comparison">
          <div class="comparison-row">
            <div class="player-answer-section">
              <span class="answer-label">Your answer:</span>
              <div class="answer-details">
                <span 
                  class="answer-text" 
                  :class="{ 
                    'correct': playerAnswers[index].correct, 
                    'incorrect': !playerAnswers[index].correct 
                  }"
                >
                  {{ playerAnswers[index].text }}
                </span>
                <span class="answer-time-detail">
                  {{ formatTime(playerAnswers[index].time) }}
                </span>
              </div>
            </div>
            
            <div class="opponent-answer-section">
              <span class="answer-label">{{ opponent.name }}:</span>
              <div class="answer-details">
                <span 
                  class="answer-text" 
                  :class="{ 
                    'correct': opponentAnswers[index].correct, 
                    'incorrect': !opponentAnswers[index].correct 
                  }"
                >
                  {{ opponentAnswers[index].text }}
                </span>
                <span class="answer-time-detail">
                  {{ formatTime(opponentAnswers[index].time) }}
                </span>
              </div>
            </div>
          </div>
          
          <div class="correct-answer-section">
            <span class="answer-label">Correct answer:</span>
            <span class="answer-text correct">{{ question.correctAnswer }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="battle-actions">
      <button class="btn-return" @click="returnToBattles">
        Return to Battles
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

// Charger les données depuis localStorage si disponibles
onMounted(() => {
  const savedResults = localStorage.getItem('lastBattleResults')
  if (savedResults) {
    const results = JSON.parse(savedResults)
    
    // Mettre à jour avec les vraies données si elles existent
    if (results.battleId === parseInt(battleId)) {
      opponent.value = results.opponent
      if (results.playerAnswers?.length) {
        playerAnswers.value = results.playerAnswers
      }
      if (results.opponentAnswers?.length) {
        opponentAnswers.value = results.opponentAnswers
      }
      if (results.questionsData?.length) {
        questionsData.value = results.questionsData
      }
    }
  }
})

// Fonction helper pour obtenir le drapeau du pays
function getCountryFlag(country) {
  const flags = {
    'DE': '🇩🇪',
    'FR': '🇫🇷',
    'RO': '🇷🇴',
    'PT': '🇵🇹',
    'US': '🇺🇸',
    'CH': '🇨🇭'
  }
  return flags[country] || '🌍'
}

// Computed Properties
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
  const timeBonus = Math.max(0, (150 - playerTotalTime.value) * 2) // Bonus pour rapidité
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

// Methods
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

const closeBattleDetails = () => {
  // Nettoyer localStorage
  localStorage.removeItem('lastBattleResults')
  router.push('/battle')
}

const returnToBattles = () => {
  // Nettoyer localStorage
  localStorage.removeItem('lastBattleResults')
  router.push('/battle')
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

/* HEADER */
.details-header {
  position: relative;
  text-align: center;
  margin-bottom: 1rem;
  padding: 0.8rem 0;
}

.close-btn {
  position: absolute;
  top: 0;
  right: 0;
  background: #F7C72C;
  color: #072C54;
  border: none;
  border-radius: 8px;
  width: 32px;
  height: 32px;
  font-size: 0.9rem;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.close-btn:hover {
  background: #E6B625;
  transform: scale(1.05);
}

.details-title {
  font-size: 1.6rem;
  font-weight: 700;
  color: #F7C72C;
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 1px;
  text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
}

/* PLAYERS COMPARISON */
.players-comparison {
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
  margin-bottom: 1.5rem;
}

.vs-divider {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0.8rem 0;
  position: relative;
  order: 2;
}

.vs-divider::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 10%;
  right: 10%;
  height: 2px;
  background: linear-gradient(to right, transparent, #F7C72C, transparent);
  transform: translateY(-50%);
}

.vs-text {
  background: #072C54;
  padding: 0.4rem 0.8rem;
  border: 2px solid #F7C72C;
  border-radius: 20px;
  font-size: 1rem;
  font-weight: 700;
  color: #F7C72C;
  z-index: 1;
  position: relative;
}

.player-section {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 1rem;
  backdrop-filter: blur(10px);
  border: 2px solid rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
}

.player-section:first-child {
  order: 1;
}

.player-section:last-child {
  order: 3;
}

.player-section.winner {
  border-color: #4CAF50;
  background: rgba(76, 175, 80, 0.1);
  box-shadow: 0 0 15px rgba(76, 175, 80, 0.2);
}

.player-info {
  text-align: center;
  margin-bottom: 0.8rem;
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
  margin: 0 auto 0.6rem auto;
  border: 3px solid #F7C72C;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.player-name {
  margin: 0 0 0.3rem 0;
  font-size: 0.9rem;
  font-weight: 600;
  color: white;
}

.flag {
  font-size: 1rem;
}

.battle-result {
  height: 35px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 0.8rem;
}

.result-badge {
  background: #4CAF50;
  color: white;
  padding: 0.4rem 0.8rem;
  border-radius: 15px;
  font-weight: 600;
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  box-shadow: 0 3px 10px rgba(76, 175, 80, 0.3);
}

/* PLAYER STATS */
.player-stats {
  margin-bottom: 1rem;
  padding: 0.8rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 10px;
}

.stat-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.4rem;
  font-size: 0.8rem;
}

.stat-row:last-child {
  margin-bottom: 0;
}

.stat-label {
  color: rgba(255, 255, 255, 0.8);
  font-weight: 500;
}

.stat-value {
  font-weight: 600;
  color: #F7C72C;
}

/* ANSWERS LIST */
.answers-list {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  margin-bottom: 1rem;
}

.answer-indicator {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.6rem;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.7rem;
}

.answer-indicator.correct {
  background: rgba(76, 175, 80, 0.2);
  border: 1px solid #4CAF50;
}

.answer-indicator.incorrect {
  background: rgba(244, 67, 54, 0.2);
  border: 1px solid #F44336;
}

.question-number {
  font-size: 0.6rem;
  font-weight: 700;
  color: rgba(255, 255, 255, 0.7);
  min-width: 22px;
}

.check-mark, .x-mark {
  border-radius: 50%;
  width: 18px;
  height: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 0.7rem;
  color: white;
}

.check-mark {
  background: #4CAF50;
}

.x-mark {
  background: #F44336;
}

.answer-time {
  font-size: 0.6rem;
  color: rgba(255, 255, 255, 0.6);
  flex: 1;
  text-align: center;
}

.points {
  font-size: 0.7rem;
  font-weight: 700;
  color: #F7C72C;
  min-width: 32px;
  text-align: right;
}

/* PLAYER SCORE */
.player-score {
  border-top: 2px solid rgba(255, 255, 255, 0.2);
  padding-top: 0.8rem;
  text-align: center;
}

.score-label {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.7);
  margin-bottom: 0.3rem;
}

.score-value {
  font-size: 1.8rem;
  font-weight: 700;
  color: #F7C72C;
}

/* POINTS SUMMARY */
.points-summary {
  text-align: center;
  margin-bottom: 1.5rem;
}

.points-box {
  display: inline-block;
  padding: 0.8rem 1.6rem;
  border-radius: 12px;
  font-size: 1.6rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  border: 2px solid;
}

.points-box.positive {
  background: rgba(76, 175, 80, 0.2);
  border-color: #4CAF50;
  color: #4CAF50;
}

.points-box.negative {
  background: rgba(244, 67, 54, 0.2);
  border-color: #F44336;
  color: #F44336;
}

/* QUESTIONS REVIEW */
.questions-review {
  margin-bottom: 1.5rem;
}

.review-title {
  font-size: 1.3rem;
  font-weight: 700;
  color: #F7C72C;
  text-align: center;
  margin-bottom: 1rem;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.question-review {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 1rem;
  margin-bottom: 1rem;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.question-title {
  font-size: 0.9rem;
  font-weight: 700;
  color: #F7C72C;
  margin: 0 0 0.6rem 0;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.question-text {
  font-size: 0.9rem;
  line-height: 1.4;
  margin-bottom: 1rem;
  color: white;
}

.answers-comparison {
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
}

.comparison-row {
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
}

.player-answer-section,
.opponent-answer-section,
.correct-answer-section {
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
}

.answer-label {
  font-size: 0.7rem;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.8);
}

.answer-details {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 0.4rem;
}

.answer-text {
  font-size: 0.8rem;
  font-weight: 600;
  padding: 0.3rem 0.6rem;
  border-radius: 6px;
  flex: 1;
}

.answer-text.correct {
  background: rgba(76, 175, 80, 0.2);
  color: #4CAF50;
  border: 1px solid #4CAF50;
}

.answer-text.incorrect {
  background: rgba(244, 67, 54, 0.2);
  color: #F44336;
  border: 1px solid #F44336;
}

.answer-time-detail {
  font-size: 0.6rem;
  color: rgba(255, 255, 255, 0.6);
  min-width: 35px;
  text-align: right;
}

/* BATTLE ACTIONS */
.battle-actions {
  display: flex;
  justify-content: center;
  margin-top: 1.5rem;
}

.btn-return {
  padding: 0.9rem 1.5rem;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 0.9rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  background: #F7C72C;
  color: #072C54;
}

.btn-return:hover {
  background: #E6B625;
  transform: translateY(-2px);
}

/* RESPONSIVE SMALL TABLET (576px et plus) */
@media (min-width: 576px) {
  .battle-details-page {
    padding: 1.2rem;
    padding-bottom: 100px;
  }
  
  .details-title {
    font-size: 1.8rem;
  }
  
  .close-btn {
    width: 35px;
    height: 35px;
    font-size: 1rem;
  }
  
  .player-section {
    padding: 1.2rem;
  }
  
  .avatar {
    width: 55px;
    height: 55px;
    font-size: 1.3rem;
  }
  
  .score-value {
    font-size: 2rem;
  }
  
  .points-box {
    font-size: 1.8rem;
    padding: 1rem 2rem;
  }
  
  .question-review {
    padding: 1.2rem;
  }
}

/* RESPONSIVE TABLET (768px et plus) */
@media (min-width: 768px) {
  .battle-details-page {
    margin-left: 280px;
    width: calc(100% - 280px);
    padding: 1.5rem;
    padding-bottom: 2rem;
  }
  
  .details-title {
    font-size: 2.2rem;
    letter-spacing: 2px;
  }
  
  .close-btn {
    width: 38px;
    height: 38px;
    font-size: 1.1rem;
  }
  
  .players-comparison {
    flex-direction: row;
    gap: 1.5rem;
    align-items: flex-start;
  }
  
  .player-section {
    flex: 1;
    padding: 1.5rem;
  }
  
  .vs-divider {
    flex-direction: column;
    justify-content: center;
    width: 60px;
    height: auto;
    order: 2;
    margin: 1.5rem 0;
    padding: 1rem 0;
  }
  
  .vs-divider::before {
    width: 2px;
    height: 180px;
    left: 50%;
    top: 0;
    bottom: 0;
    background: linear-gradient(to bottom, transparent, #F7C72C, transparent);
    transform: translateX(-50%);
  }
  
  .vs-text {
    font-size: 1.3rem;
    padding: 0.5rem 1rem;
  }
  
  .avatar {
    width: 70px;
    height: 70px;
    font-size: 1.7rem;
  }
  
  .player-name {
    font-size: 1.1rem;
  }
  
  .battle-result {
    height: 45px;
  }
  
  .result-badge {
    padding: 0.6rem 1.2rem;
    font-size: 0.9rem;
  }
  
  .stat-row {
    font-size: 0.9rem;
  }
  
  .answer-indicator {
    padding: 0.8rem;
    font-size: 0.8rem;
    gap: 0.6rem;
  }
  
  .check-mark, .x-mark {
    width: 22px;
    height: 22px;
    font-size: 0.8rem;
  }
  
  .question-number {
    font-size: 0.7rem;
    min-width: 25px;
  }
  
  .answer-time {
    font-size: 0.7rem;
  }
  
  .points {
    font-size: 0.8rem;
    min-width: 35px;
  }
  
  .score-value {
    font-size: 2.3rem;
  }
  
  .points-box {
    font-size: 2.2rem;
    padding: 1.1rem 2.2rem;
  }
  
  .question-review {
    padding: 1.5rem;
  }
  
  .comparison-row {
    flex-direction: row;
    gap: 1.5rem;
  }
  
  .player-answer-section,
  .opponent-answer-section {
    flex: 1;
  }
  
  .battle-actions {
    justify-content: center;
    max-width: 300px;
    margin: 2rem auto 0 auto;
  }
  
  .btn-return {
    font-size: 1rem;
    padding: 1rem 2rem;
  }
}

/* RESPONSIVE LARGE DESKTOP (1024px et plus) */
@media (min-width: 1024px) {
  .battle-details-page {
    margin-left: 280px;
    width: calc(100% - 280px);
    padding: 2.5rem;
  }
  
  .details-title {
    font-size: 2.8rem;
    letter-spacing: 3px;
  }
  
  .close-btn {
    width: 42px;
    height: 42px;
    font-size: 1.2rem;
  }
  
  .players-comparison {
    gap: 2.5rem;
    max-width: 1100px;
    margin: 0 auto 2.5rem auto;
  }
  
  .player-section {
    padding: 2rem;
  }
  
  .vs-divider {
    width: 80px;
    margin: 2rem 0;
  }
  
  .vs-divider::before {
    height: 220px;
  }
  
  .vs-text {
    font-size: 1.5rem;
  }
  
  .avatar {
    width: 85px;
    height: 85px;
    font-size: 2rem;
  }
  
  .player-name {
    font-size: 1.3rem;
  }
  
  .score-value {
    font-size: 2.8rem;
  }
  
  .points-box {
    font-size: 2.8rem;
    padding: 1.3rem 2.8rem;
  }
  
  .questions-review {
    max-width: 900px;
    margin: 0 auto 2.5rem auto;
  }
  
  .review-title {
    font-size: 1.8rem;
  }
  
  .question-review {
    padding: 2rem;
  }
  
  .question-title {
    font-size: 1.1rem;
  }
  
  .question-text {
    font-size: 1rem;
  }
}

/* RESPONSIVE TRÈS LARGE DESKTOP (1200px et plus) */
@media (min-width: 1200px) {
  .players-comparison {
    max-width: 1200px;
    gap: 3rem;
  }
  
  .player-section {
    padding: 2.5rem;
  }
  
  .avatar {
    width: 90px;
    height: 90px;
    font-size: 2.2rem;
  }
  
  .score-value {
    font-size: 3rem;
  }
  
  .points-box {
    font-size: 3rem;
    padding: 1.5rem 3rem;
  }
  
  .questions-review {
    max-width: 1000px;
  }
  
  .question-review {
    padding: 2.5rem;
  }
}

/* RESPONSIVE MOBILE (767px et moins) */
@media (max-width: 767px) {
  .battle-details-page {
    margin-left: 0;
    width: 100%;
    padding: 0.8rem;
    padding-bottom: 80px;
  }
  
  .details-header {
    margin-bottom: 0.8rem;
  }
  
  .details-title {
    font-size: 1.4rem;
  }
  
  .close-btn {
    width: 28px;
    height: 28px;
    font-size: 0.8rem;
  }
  
  .players-comparison {
    gap: 0.6rem;
    margin-bottom: 1.2rem;
  }
  
  .vs-divider {
    padding: 0.6rem 0;
  }
  
  .vs-text {
    font-size: 0.9rem;
    padding: 0.3rem 0.6rem;
  }
  
  .player-section {
    padding: 0.8rem;
  }
  
  .avatar {
    width: 45px;
    height: 45px;
    font-size: 1.1rem;
  }
  
  .player-name {
    font-size: 0.8rem;
  }
  
  .battle-result {
    height: 30px;
  }
  
  .result-badge {
    padding: 0.3rem 0.6rem;
    font-size: 0.6rem;
  }
  
  .player-stats {
    padding: 0.6rem;
  }
  
  .stat-row {
    font-size: 0.7rem;
    margin-bottom: 0.3rem;
  }
  
  .answer-indicator {
    padding: 0.5rem;
    font-size: 0.6rem;
    gap: 0.3rem;
  }
  
  .check-mark, .x-mark {
    width: 16px;
    height: 16px;
    font-size: 0.6rem;
  }
  
  .question-number {
    font-size: 0.5rem;
    min-width: 18px;
  }
  
  .answer-time {
    font-size: 0.5rem;
  }
  
  .points {
    font-size: 0.6rem;
    min-width: 28px;
  }
  
  .score-value {
    font-size: 1.4rem;
  }
  
  .points-box {
    font-size: 1.3rem;
    padding: 0.6rem 1.2rem;
  }
  
  .review-title {
    font-size: 1.1rem;
  }
  
  .question-review {
    padding: 0.8rem;
  }
  
  .question-title {
    font-size: 0.8rem;
  }
  
  .question-text {
    font-size: 0.8rem;
  }
  
  .answer-text {
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
  }
  
  .answer-time-detail {
    font-size: 0.5rem;
    min-width: 28px;
  }
  
  .btn-rematch,
  .btn-return {
    padding: 0.7rem 1.2rem;
    font-size: 0.8rem;
  }
}

/* RESPONSIVE TRÈS PETIT MOBILE (480px et moins) */
@media (max-width: 479px) {
  .battle-details-page {
    padding: 0.6rem;
    padding-bottom: 80px;
  }
  
  .details-title {
    font-size: 1.2rem;
    letter-spacing: 0.5px;
  }
  
  .close-btn {
    width: 26px;
    height: 26px;
    font-size: 0.7rem;
  }
  
  .player-section {
    padding: 0.6rem;
  }
  
  .avatar {
    width: 40px;
    height: 40px;
    font-size: 1rem;
  }
  
  .points-box {
    font-size: 1.1rem;
    padding: 0.5rem 1rem;
  }
  
  .answer-indicator {
    padding: 0.4rem;
  }
  
  .question-review {
    padding: 0.6rem;
  }
  
  .btn-rematch,
  .btn-return {
    padding: 0.6rem 1rem;
    font-size: 0.7rem;
  }
}

/* RESPONSIVE EXTRA PETIT MOBILE (360px et moins) */
@media (max-width: 360px) {
  .battle-details-page {
    padding: 0.5rem;
  }
  
  .details-title {
    font-size: 1.1rem;
  }
  
  .close-btn {
    width: 24px;
    height: 24px;
    font-size: 0.6rem;
  }
  
  .vs-text {
    font-size: 0.8rem;
  }
  
  .avatar {
    width: 38px;
    height: 38px;
    font-size: 0.9rem;
  }
  
  .score-value {
    font-size: 1.2rem;
  }
  
  .points-box {
    font-size: 1rem;
    padding: 0.4rem 0.8rem;
  }
}
</style>