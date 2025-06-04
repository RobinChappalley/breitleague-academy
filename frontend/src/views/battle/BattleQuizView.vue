<template>
  <div class="battle-quiz-page">
    <!-- Battle Header Info -->
    <div class="battle-header">
      <div class="opponent-info">
        <div class="avatar" :style="getAvatarStyle(opponent)">
          {{ opponent.avatar }}
        </div>
        <div class="opponent-details">
          <h3>{{ opponent.name }}</h3>
          <span class="flag">{{ opponent.flag }}</span>
        </div>
      </div>
      <div class="vs-indicator">VS</div>
      <div class="player-info">
        <div class="avatar" :style="getAvatarStyle(currentPlayer)">
          {{ currentPlayer.avatar }}
        </div>
        <div class="player-details">
          <h3>{{ currentPlayer.name }}</h3>
          <span class="flag">{{ currentPlayer.flag }}</span>
        </div>
      </div>
    </div>

    <!-- Progress Bar -->
    <div class="progress-container">
      <div class="progress-bar">
        <div 
          class="progress-fill"
          :style="{ width: progressPercentage + '%' }"
        ></div>
      </div>
      <div class="question-counter">
        {{ currentQuestionIndex + 1 }} / {{ totalQuestions }}
      </div>
    </div>

    <!-- Timer -->
    <div class="timer-container">
      <div class="timer" :class="{ 'warning': timeLeft <= 5 }">
        {{ timeLeft }}s
      </div>
    </div>

    <!-- Question -->
    <div class="question-container">
      <h2 class="question-text">{{ currentQuestion.text }}</h2>
      
      <!-- Answer Options -->
      <div class="answers-grid">
        <button
          v-for="(answer, index) in currentQuestion.answers"
          :key="index"
          class="answer-btn"
          :class="getAnswerClass(index)"
          :disabled="hasAnswered"
          @click="selectAnswer(index)"
        >
          {{ answer.text }}
        </button>
      </div>
    </div>

    <!-- Battle Complete Modal -->
    <div class="modal-overlay" v-if="showResults" @click="closeResults">
      <div class="results-modal" @click.stop>
        <button class="close-btn" @click="closeResults">✕</button>
        
        <div class="results-header">
          <h2>Battle Complete!</h2>
        </div>

        <div class="results-comparison">
          <div class="player-result">
            <div class="avatar" :style="getAvatarStyle(currentPlayer)">
              {{ currentPlayer.avatar }}
            </div>
            <h3>{{ currentPlayer.name }}</h3>
            <div class="score">{{ playerScore }}/{{ totalQuestions }}</div>
            <div class="time">{{ playerTime }}s</div>
          </div>

          <div class="vs">VS</div>

          <div class="opponent-result">
            <div class="avatar" :style="getAvatarStyle(opponent)">
              {{ opponent.avatar }}
            </div>
            <h3>{{ opponent.name }}</h3>
            <div class="score">{{ opponentScore }}/{{ totalQuestions }}</div>
            <div class="time">{{ opponentTime }}s</div>
          </div>
        </div>

        <div class="winner-announcement" :class="winnerClass">
          {{ winnerText }}
        </div>

        <div class="points-earned">
          <div class="points-label">Points Earned</div>
          <div class="points-value">+{{ pointsEarned }}</div>
        </div>

        <div class="results-actions">
          <button class="btn-rematch" @click="requestRematch">
            Rematch
          </button>
          <button class="btn-return" @click="returnToBattles">
            Return to Battles
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// Battle Data
const opponent = ref({
  id: 2,
  name: 'M.OVSANNA',
  avatar: 'M',
  flag: '🇩🇪'
})

const currentPlayer = ref({
  id: 1,
  name: 'R.DUFUIS',
  avatar: 'R',
  flag: '🇨🇭'
})

// Quiz State
const currentQuestionIndex = ref(0)
const totalQuestions = ref(5)
const timeLeft = ref(30)
const hasAnswered = ref(false)
const selectedAnswer = ref(null)
const showResults = ref(false)

// Results
const playerScore = ref(0)
const opponentScore = ref(0)
const playerTime = ref(0)
const opponentTime = ref(0)
const pointsEarned = ref(0)

// Timer
let timer = null

// Mock Questions
const questions = ref([
  {
    text: "Which Breitling collection is known for its aviation heritage?",
    answers: [
      { text: "Navitimer", correct: true },
      { text: "Superocean", correct: false },
      { text: "Premier", correct: false },
      { text: "Chronomat", correct: false }
    ]
  },
  {
    text: "What year was Breitling founded?",
    answers: [
      { text: "1884", correct: true },
      { text: "1905", correct: false },
      { text: "1892", correct: false },
      { text: "1876", correct: false }
    ]
  },
  {
    text: "Which movement powers the Breitling B01?",
    answers: [
      { text: "In-house chronograph", correct: true },
      { text: "ETA 2824", correct: false },
      { text: "Sellita SW200", correct: false },
      { text: "Valjoux 7750", correct: false }
    ]
  },
  {
    text: "What is the water resistance of the Superocean?",
    answers: [
      { text: "200m", correct: false },
      { text: "300m", correct: false },
      { text: "500m", correct: true },
      { text: "1000m", correct: false }
    ]
  },
  {
    text: "Which Breitling watch was worn by astronauts?",
    answers: [
      { text: "Cosmonaute", correct: true },
      { text: "Navitimer", correct: false },
      { text: "Chronomat", correct: false },
      { text: "Premier", correct: false }
    ]
  }
])

// Computed
const currentQuestion = computed(() => questions.value[currentQuestionIndex.value])
const progressPercentage = computed(() => (currentQuestionIndex.value / totalQuestions.value) * 100)

const winnerClass = computed(() => {
  if (playerScore.value > opponentScore.value) return 'winner'
  if (playerScore.value < opponentScore.value) return 'loser'
  return 'tie'
})

const winnerText = computed(() => {
  if (playerScore.value > opponentScore.value) return 'You Win! 🎉'
  if (playerScore.value < opponentScore.value) return 'You Lose 😔'
  return 'It\'s a Tie! 🤝'
})

// Methods
const startTimer = () => {
  timeLeft.value = 30
  timer = setInterval(() => {
    timeLeft.value--
    if (timeLeft.value <= 0) {
      autoAnswer()
    }
  }, 1000)
}

const stopTimer = () => {
  if (timer) {
    clearInterval(timer)
    timer = null
  }
}

const selectAnswer = (index) => {
  if (hasAnswered.value) return
  
  selectedAnswer.value = index
  hasAnswered.value = true
  stopTimer()

  // Add to score if correct
  if (currentQuestion.value.answers[index].correct) {
    playerScore.value++
  }

  // Add time bonus
  const timeBonus = Math.max(0, timeLeft.value)
  playerTime.value += (30 - timeLeft.value)

  // Wait 2 seconds then go to next question
  setTimeout(() => {
    nextQuestion()
  }, 2000)
}

const autoAnswer = () => {
  if (hasAnswered.value) return
  
  hasAnswered.value = true
  stopTimer()
  playerTime.value += 30

  setTimeout(() => {
    nextQuestion()
  }, 1000)
}

const nextQuestion = () => {
  if (currentQuestionIndex.value < totalQuestions.value - 1) {
    currentQuestionIndex.value++
    hasAnswered.value = false
    selectedAnswer.value = null
    startTimer()
  } else {
    finishBattle()
  }
}

const finishBattle = () => {
  // Simulate opponent results
  opponentScore.value = Math.floor(Math.random() * 6) // 0-5
  opponentTime.value = Math.floor(Math.random() * 100) + 50 // 50-150s

  // Calculate points earned
  if (playerScore.value > opponentScore.value) {
    pointsEarned.value = 100
  } else if (playerScore.value === opponentScore.value) {
    pointsEarned.value = 50
  } else {
    pointsEarned.value = 25
  }

  showResults.value = true
}

const getAnswerClass = (index) => {
  if (!hasAnswered.value) return ''
  
  if (selectedAnswer.value === index) {
    return currentQuestion.value.answers[index].correct ? 'correct' : 'incorrect'
  }
  
  if (currentQuestion.value.answers[index].correct) {
    return 'correct-answer'
  }
  
  return 'disabled'
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

const closeResults = () => {
  showResults.value = false
  router.push('/battles')
}

const requestRematch = () => {
  // Reset quiz state for rematch
  currentQuestionIndex.value = 0
  playerScore.value = 0
  playerTime.value = 0
  hasAnswered.value = false
  selectedAnswer.value = null
  showResults.value = false
  
  // Shuffle questions for variety
  questions.value = [...questions.value].sort(() => Math.random() - 0.5)
  
  startTimer()
}

const returnToBattles = () => {
  router.push('/battles')
}

// Lifecycle
onMounted(() => {
  startTimer()
})

onUnmounted(() => {
  stopTimer()
})
</script>

<style scoped>
.battle-quiz-page {
  min-height: 100vh;
  width: 100%;
  background: linear-gradient(135deg, #072C54 0%, #1e3a8a 100%);
  color: white;
  padding: 1rem;
  padding-bottom: 100px;
  box-sizing: border-box;
}

/* BATTLE HEADER */
.battle-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 2rem 1rem;
  margin-bottom: 2rem;
}

.opponent-info,
.player-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.avatar {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 1.5rem;
  border: 3px solid #F7C72C;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}

.opponent-details h3,
.player-details h3 {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 600;
}

.flag {
  font-size: 1.2rem;
}

.vs-indicator {
  font-size: 2rem;
  font-weight: 700;
  color: #F7C72C;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

/* PROGRESS */
.progress-container {
  margin-bottom: 2rem;
  text-align: center;
}

.progress-bar {
  width: 100%;
  height: 8px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 4px;
  overflow: hidden;
  margin-bottom: 1rem;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #F7C72C 0%, #E6B625 100%);
  border-radius: 4px;
  transition: width 0.3s ease;
}

.question-counter {
  font-size: 1.1rem;
  font-weight: 600;
  color: #F7C72C;
}

/* TIMER */
.timer-container {
  text-align: center;
  margin-bottom: 2rem;
}

.timer {
  display: inline-block;
  font-size: 3rem;
  font-weight: 700;
  color: #F7C72C;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 50%;
  width: 100px;
  height: 100px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
  transition: all 0.3s ease;
}

.timer.warning {
  color: #FF4444;
  animation: pulse 0.5s ease-in-out infinite alternate;
}

@keyframes pulse {
  from { transform: scale(1); }
  to { transform: scale(1.1); }
}

/* QUESTION */
.question-container {
  max-width: 800px;
  margin: 0 auto;
}

.question-text {
  font-size: 1.5rem;
  font-weight: 600;
  text-align: center;
  margin-bottom: 3rem;
  line-height: 1.4;
  color: white;
}

.answers-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
}

.answer-btn {
  padding: 1.5rem 2rem;
  background: rgba(255, 255, 255, 0.1);
  border: 2px solid rgba(255, 255, 255, 0.2);
  border-radius: 12px;
  color: white;
  font-size: 1.1rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  text-align: left;
}

.answer-btn:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.2);
  border-color: #F7C72C;
  transform: translateY(-2px);
}

.answer-btn:disabled {
  cursor: not-allowed;
}

.answer-btn.correct {
  background: rgba(76, 175, 80, 0.3);
  border-color: #4CAF50;
  color: white;
}

.answer-btn.incorrect {
  background: rgba(244, 67, 54, 0.3);
  border-color: #F44336;
  color: white;
}

.answer-btn.correct-answer {
  background: rgba(76, 175, 80, 0.2);
  border-color: #4CAF50;
}

.answer-btn.disabled {
  opacity: 0.5;
}

/* RESULTS MODAL */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  backdrop-filter: blur(5px);
}

.results-modal {
  background: linear-gradient(135deg, #1e3a8a 0%, #072C54 100%);
  border-radius: 20px;
  padding: 2rem;
  max-width: 500px;
  width: 90%;
  position: relative;
  color: white;
  border: 2px solid #F7C72C;
  text-align: center;
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
}

.results-header h2 {
  margin: 0 0 2rem 0;
  font-size: 2rem;
  color: #F7C72C;
}

.results-comparison {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 2rem;
}

.player-result,
.opponent-result {
  text-align: center;
  flex: 1;
}

.player-result .avatar,
.opponent-result .avatar {
  width: 60px;
  height: 60px;
  font-size: 1.5rem;
  margin: 0 auto 1rem auto;
}

.player-result h3,
.opponent-result h3 {
  margin: 0 0 0.5rem 0;
  font-size: 1.1rem;
}

.score {
  font-size: 2rem;
  font-weight: 700;
  color: #F7C72C;
  margin-bottom: 0.5rem;
}

.time {
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.7);
}

.vs {
  font-size: 1.5rem;
  font-weight: 700;
  color: #F7C72C;
  margin: 0 1rem;
}

.winner-announcement {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 2rem;
  padding: 1rem;
  border-radius: 12px;
}

.winner-announcement.winner {
  background: rgba(76, 175, 80, 0.2);
  color: #4CAF50;
}

.winner-announcement.loser {
  background: rgba(244, 67, 54, 0.2);
  color: #F44336;
}

.winner-announcement.tie {
  background: rgba(255, 193, 7, 0.2);
  color: #FFC107;
}

.points-earned {
  margin-bottom: 2rem;
}

.points-label {
  font-size: 1rem;
  color: rgba(255, 255, 255, 0.7);
  margin-bottom: 0.5rem;
}

.points-value {
  font-size: 2.5rem;
  font-weight: 700;
  color: #F7C72C;
}

.results-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
}

.btn-rematch,
.btn-return {
  padding: 1rem 2rem;
  border: none;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 1rem;
}

.btn-rematch {
  background: #F7C72C;
  color: #072C54;
}

.btn-rematch:hover {
  background: #E6B625;
  transform: translateY(-2px);
}

.btn-return {
  background: rgba(255, 255, 255, 0.1);
  color: white;
  border: 2px solid rgba(255, 255, 255, 0.3);
}

.btn-return:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: translateY(-2px);
}

/* RESPONSIVE */
@media (min-width: 768px) {
  .battle-quiz-page {
    margin-left: 280px;
    width: calc(100% - 280px);
    padding: 2rem;
    padding-bottom: 2rem;
  }
  
  .battle-header {
    padding: 3rem 2rem;
  }
  
  .avatar {
    width: 80px;
    height: 80px;
    font-size: 2rem;
  }
  
  .vs-indicator {
    font-size: 3rem;
  }
  
  .question-text {
    font-size: 2rem;
    margin-bottom: 4rem;
  }
  
  .answers-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
  }
  
  .answer-btn {
    padding: 2rem;
    font-size: 1.2rem;
  }
  
  .timer {
    width: 120px;
    height: 120px;
    font-size: 3.5rem;
  }
}

@media (max-width: 767px) {
  .battle-quiz-page {
    margin-left: 0;
    width: 100%;
    padding: 1rem;
    padding-bottom: 80px;
  }
  
  .battle-header {
    padding: 1rem;
  }
  
  .avatar {
    width: 50px;
    height: 50px;
    font-size: 1.2rem;
  }
  
  .vs-indicator {
    font-size: 1.5rem;
  }
  
  .question-text {
    font-size: 1.2rem;
    margin-bottom: 2rem;
  }
  
  .answer-btn {
    padding: 1rem;
    font-size: 1rem;
  }
  
  .timer {
    width: 80px;
    height: 80px;
    font-size: 2.5rem;
  }
  
  .results-comparison {
    flex-direction: column;
    gap: 1rem;
  }
  
  .vs {
    order: 2;
  }
  
  .results-actions {
    flex-direction: column;
  }
}
</style>