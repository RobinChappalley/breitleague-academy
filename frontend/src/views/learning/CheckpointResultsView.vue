<template>
  <div class="checkpoint-results">
    <div class="results-container">
      <!-- Title Section -->
      <div class="title-section">
        <h1 class="results-title">CHECKPOINT</h1>
        <h2 class="results-subtitle">COMPLETED</h2>
      </div>

      <!-- Score Circle -->
      <div class="score-section">
        <div class="score-circle">
          <svg class="score-ring" viewBox="0 0 200 200">
            <!-- Background circle -->
            <circle
              cx="100"
              cy="100"
              r="80"
              fill="none"
              stroke="rgba(255, 255, 255, 0.2)"
              stroke-width="8"
              class="background-circle"
            />
            <!-- Progress circle -->
            <circle
              cx="100"
              cy="100"
              r="80"
              fill="none"
              :stroke="scoreColor"
              stroke-width="8"
              stroke-linecap="round"
              :stroke-dasharray="`${circumference} ${circumference}`"
              :stroke-dashoffset="strokeOffset"
              class="progress-circle"
            />
          </svg>
          <div class="score-content">
            <div class="percentage">{{ animatedScore }}%</div>
            <div class="score-label">{{ resultStatus }}</div>
          </div>
        </div>
      </div>

      <!-- Stats Section -->
      <div class="stats-section">
        <div class="stat-item">
          <span class="stat-value">{{ correctAnswers }}</span>
          <span class="stat-label">Correct</span>
        </div>
        <div class="stat-item">
          <span class="stat-value">{{ totalQuestions - correctAnswers }}</span>
          <span class="stat-label">Incorrect</span>
        </div>
        <div class="stat-item">
          <span class="stat-value">{{ formatTime(timeUsed) }}</span>
          <span class="stat-label">Time Used</span>
        </div>
      </div>

      <!-- Result Message -->
      <div class="message-section">
        <p class="result-message">{{ resultMessage }}</p>
      </div>

      <!-- Action Buttons -->
      <div class="button-section">
        <button 
          v-if="!passed" 
          class="retry-btn" 
          @click="retryCheckpoint"
        >
          TRY AGAIN
        </button>
        <button 
          v-if="passed" 
          class="continue-btn" 
          @click="continueToNext"
        >
          CONTINUE
        </button>
        <button 
          class="back-btn" 
          @click="backToCheckpoint"
        >
          BACK TO CHECKPOINT
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()

// Data from route query
const score = ref(parseInt(route.query.score) || 0)
const passed = ref(route.query.passed === 'true' || route.query.passed === true)
const correctAnswers = ref(parseInt(route.query.correctAnswers) || 0)
const totalQuestions = ref(parseInt(route.query.totalQuestions) || 10)
const timeUsed = ref(parseInt(route.query.timeUsed) || 0)

// Animation
const animatedScore = ref(0)
const radius = 80
const circumference = 2 * Math.PI * radius

// Computed
const scoreColor = computed(() => {
  return passed.value ? '#22c55e' : '#ef4444'
})

const strokeOffset = computed(() => {
  const progress = animatedScore.value / 100
  return circumference * (1 - progress)
})

const resultStatus = computed(() => {
  return passed.value ? 'PASSED' : 'FAILED'
})

const resultMessage = computed(() => {
  if (passed.value) {
    if (score.value >= 90) {
      return 'Excellent! You have mastered this checkpoint with an outstanding score. You\'re ready for the next challenge!'
    } else if (score.value >= 80) {
      return 'Great job! You have successfully completed this checkpoint with a strong performance. Keep up the good work!'
    } else {
      return 'Congratulations! You have successfully completed this checkpoint. You can now proceed to the next module.'
    }
  } else {
    if (score.value >= 60) {
      return 'You\'re close! Just a little more practice and you\'ll pass this checkpoint. Review the material and try again.'
    } else if (score.value >= 40) {
      return 'Keep practicing! Focus on the areas where you struggled and try again when you feel more confident.'
    } else {
      return 'You need at least 70% to pass this checkpoint. Take your time to review the material thoroughly before attempting again.'
    }
  }
})

// Methods
const animateScore = () => {
  const duration = 2000
  const steps = 60
  const increment = score.value / steps
  const stepDuration = duration / steps
  
  let currentStep = 0
  const timer = setInterval(() => {
    currentStep++
    animatedScore.value = Math.min(Math.round(increment * currentStep), score.value)
    
    if (currentStep >= steps || animatedScore.value >= score.value) {
      clearInterval(timer)
      animatedScore.value = score.value
    }
  }, stepDuration)
}

const retryCheckpoint = () => {
  router.push('/checkpoint-quiz')
}

const continueToNext = () => {
  router.push('/learning')
}

const backToCheckpoint = () => {
  router.push('/checkpoint')
}

const formatTime = (seconds) => {
  const minutes = Math.floor(seconds / 60)
  const remainingSeconds = seconds % 60
  return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`
}

// Lifecycle
onMounted(() => {
  // Log pour debug
  console.log('Results data:', {
    score: score.value,
    passed: passed.value,
    correctAnswers: correctAnswers.value,
    totalQuestions: totalQuestions.value,
    timeUsed: timeUsed.value
  })
  
  setTimeout(() => {
    animateScore()
  }, 500)
})
</script>

<style scoped>
.checkpoint-results {
  width: 100vw;
  min-height: 100vh;
  background: linear-gradient(135deg, #072C54 0%, #1e3a8a 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  padding: 2rem;
}

.results-container {
  width: 100%;
  max-width: 500px;
  text-align: center;
}

/* TITLE SECTION */
.title-section {
  margin-bottom: 3rem;
}

.results-title {
  font-size: 3rem;
  font-weight: 900;
  color: #F7C72C;
  margin: 0;
  letter-spacing: 2px;
  text-transform: uppercase;
}

.results-subtitle {
  font-size: 1.5rem;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.8);
  margin: 0.5rem 0 0 0;
  text-transform: uppercase;
  letter-spacing: 1px;
}

/* SCORE SECTION */
.score-section {
  margin-bottom: 3rem;
  display: flex;
  justify-content: center;
}

.score-circle {
  position: relative;
  width: 200px;
  height: 200px;
}

.score-ring {
  width: 100%;
  height: 100%;
  transform: rotate(-90deg);
}

.background-circle {
  stroke: rgba(255, 255, 255, 0.2);
}

.progress-circle {
  transition: stroke-dashoffset 2s ease-in-out;
}

.score-content {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
}

.percentage {
  font-size: 3rem;
  font-weight: 900;
  color: white;
  line-height: 1;
  margin-bottom: 0.2rem;
}

.score-label {
  font-size: 1.2rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
}

/* STATS SECTION */
.stats-section {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
  margin-bottom: 3rem;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 15px;
  padding: 2rem;
  backdrop-filter: blur(10px);
}

.stat-item {
  text-align: center;
}

.stat-value {
  display: block;
  font-size: 2rem;
  font-weight: 700;
  color: #F7C72C;
  margin-bottom: 0.5rem;
}

.stat-label {
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.8);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* MESSAGE SECTION */
.message-section {
  margin-bottom: 3rem;
}

.result-message {
  font-size: 1.1rem;
  line-height: 1.6;
  color: rgba(255, 255, 255, 0.9);
  margin: 0;
  padding: 1.5rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

/* BUTTON SECTION */
.button-section {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.retry-btn,
.continue-btn,
.back-btn {
  padding: 1rem 2rem;
  border: none;
  border-radius: 12px;
  font-weight: 700;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.retry-btn {
  background: #ef4444;
  color: white;
}

.retry-btn:hover {
  background: #dc2626;
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
}

.continue-btn {
  background: #22c55e;
  color: white;
}

.continue-btn:hover {
  background: #16a34a;
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
}

.back-btn {
  background: rgba(255, 255, 255, 0.1);
  color: white;
  border: 2px solid rgba(255, 255, 255, 0.3);
}

.back-btn:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
}

/* RESPONSIVE - TABLET */
@media (min-width: 768px) and (max-width: 1199px) {
  .results-container {
    max-width: 600px;
  }
  
  .results-title {
    font-size: 3.5rem;
  }
  
  .results-subtitle {
    font-size: 1.8rem;
  }
  
  .score-circle {
    width: 250px;
    height: 250px;
  }
  
  .percentage {
    font-size: 3.5rem;
  }
  
  .score-label {
    font-size: 1.3rem;
  }
  
  .button-section {
    flex-direction: row;
    justify-content: center;
  }
  
  .retry-btn,
  .continue-btn,
  .back-btn {
    flex: 1;
    max-width: 200px;
  }
}

/* RESPONSIVE - DESKTOP */
@media (min-width: 1200px) {
  
  .results-container {
    max-width: 700px;
  }
  
  .results-title {
    font-size: 4rem;
    letter-spacing: 3px;
  }
  
  .results-subtitle {
    font-size: 2rem;
  }
  
  .score-circle {
    width: 300px;
    height: 300px;
  }
  
  .percentage {
    font-size: 4rem;
  }
  
  .score-label {
    font-size: 1.4rem;
  }
  
  .stats-section {
    padding: 2.5rem;
    gap: 2rem;
  }
  
  .stat-value {
    font-size: 2.5rem;
  }
  
  .stat-label {
    font-size: 1rem;
  }
  
  .result-message {
    font-size: 1.2rem;
    padding: 2rem;
  }
  
  .button-section {
    flex-direction: row;
    justify-content: center;
    max-width: 500px;
    margin: 0 auto;
  }
  
  .retry-btn,
  .continue-btn,
  .back-btn {
    flex: 1;
    font-size: 1.1rem;
  }
}

/* RESPONSIVE - MOBILE */
@media (max-width: 767px) {
  .checkpoint-results {
    margin-left: 0;
    width: 100%;
    padding: 1rem;
    padding-bottom: 80px;
  }
  
  .results-container {
    max-width: 100%;
  }
  
  .title-section {
    margin-bottom: 2rem;
  }
  
  .results-title {
    font-size: 2.2rem;
    letter-spacing: 1px;
  }
  
  .results-subtitle {
    font-size: 1.2rem;
  }
  
  .score-section {
    margin-bottom: 2rem;
  }
  
  .score-circle {
    width: 160px;
    height: 160px;
  }
  
  .percentage {
    font-size: 2.2rem;
  }
  
  .score-label {
    font-size: 1rem;
  }
  
  .stats-section {
    grid-template-columns: 1fr;
    gap: 1rem;
    padding: 1.5rem;
    margin-bottom: 2rem;
  }
  
  .stat-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    text-align: left;
  }
  
  .stat-value {
    font-size: 1.5rem;
    margin-bottom: 0;
  }
  
  .stat-label {
    font-size: 0.8rem;
  }
  
  .result-message {
    font-size: 1rem;
    padding: 1.2rem;
  }
  
  .retry-btn,
  .continue-btn,
  .back-btn {
    padding: 0.8rem 1.5rem;
    font-size: 0.9rem;
  }
}

/* RESPONSIVE - SMALL MOBILE */
@media (max-width: 480px) {
  .results-title {
    font-size: 1.8rem;
  }
  
  .results-subtitle {
    font-size: 1rem;
  }
  
  .score-circle {
    width: 140px;
    height: 140px;
  }
  
  .percentage {
    font-size: 1.8rem;
  }
  
  .score-label {
    font-size: 0.9rem;
  }
  
  .stats-section {
    padding: 1.2rem;
  }
  
  .stat-value {
    font-size: 1.3rem;
  }
}

/* Animation pour l'apparition */
.results-container {
  animation: fadeInUp 0.8s ease-out;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Animation des stats */
.stat-item {
  animation: fadeIn 0.6s ease-out;
  animation-fill-mode: both;
}

.stat-item:nth-child(1) {
  animation-delay: 0.8s;
}

.stat-item:nth-child(2) {
  animation-delay: 1s;
}

.stat-item:nth-child(3) {
  animation-delay: 1.2s;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: scale(0.9);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

/* Pulse animation pour le score quand passed */
.score-label {
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% {
    opacity: 1;
  }
  50% {
    opacity: 0.7;
  }
  100% {
    opacity: 1;
  }
}
</style>