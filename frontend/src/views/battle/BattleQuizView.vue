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

    <!-- Question - avec protection contre les questions non chargées -->
    <div class="question-container" v-if="currentQuestion">
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

    <!-- Loading état -->
    <div class="loading-container" v-else>
      <div class="loading-spinner"></div>
      <p>Chargement des questions...</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// Timer
let timerInterval = null

// Battle Data - récupérées depuis localStorage
const battleData = ref(null)
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

// Results
const playerScore = ref(0)
const opponentScore = ref(0)
const playerTime = ref(0)
const opponentTime = ref(0)
const playerAnswers = ref([]) // CORRIGER ICI - il manquait la parenthèse fermante

// Questions depuis la base de données
const questions = ref([])

// Computed
const currentQuestion = computed(() => {
  if (questions.value.length === 0) return null
  
  const question = questions.value[currentQuestionIndex.value]
  
  // Adapter la structure de ta base de données
  return {
    id: question.id,
    text: question.content_default || question.content_lf_tf || question.content_lf_blank || 'Question sans contenu',
    answers: question.choices?.map(choice => ({
      text: choice.content || choice.text,
      correct: choice.is_correct || choice.correct || false
    })) || []
  }
})

const progressPercentage = computed(() => {
  if (totalQuestions.value === 0) return 0
  return (currentQuestionIndex.value / totalQuestions.value) * 100
})

// Charger les données de bataille depuis localStorage
const loadBattleData = () => {
  try {
    const savedBattle = localStorage.getItem('currentBattle')
    if (savedBattle) {
      battleData.value = JSON.parse(savedBattle)
      
      // Mettre à jour les données de l'adversaire
      if (battleData.value.opponent) {
        opponent.value = {
          id: battleData.value.opponent.id,
          name: battleData.value.opponent.name,
          avatar: battleData.value.opponent.avatar || battleData.value.opponent.name.charAt(0),
          flag: battleData.value.opponent.flag
        }
      }
      
      // Charger les questions depuis la base de données
      if (battleData.value.questions && battleData.value.questions.length > 0) {
        questions.value = battleData.value.questions
        totalQuestions.value = questions.value.length
        
        console.log('✅ Questions loaded from database:', questions.value.length, 'questions')
        console.log('📋 First question:', questions.value[0])
      } else {
        console.warn('⚠️ No questions found in battle data, using fallback')
        loadFallbackQuestions()
      }
    } else {
      console.warn('⚠️ No battle data found, using fallback')
      loadFallbackQuestions()
    }
  } catch (error) {
    console.error('❌ Error loading battle data:', error)
    loadFallbackQuestions()
  }
}

// Questions de fallback si problème avec la base
const loadFallbackQuestions = () => {
  questions.value = [
    {
      id: 1,
      content_default: 'Quelle année Breitling a-t-elle été fondée ?',
      choices: [
        { content: '1884', is_correct: true },
        { content: '1885', is_correct: false },
        { content: '1890', is_correct: false },
        { content: '1900', is_correct: false }
      ]
    },
    {
      id: 2,
      content_default: 'Qui a fondé Breitling ?',
      choices: [
        { content: 'Léon Breitling', is_correct: true },
        { content: 'Gaston Breitling', is_correct: false },
        { content: 'Willy Breitling', is_correct: false },
        { content: 'Ernest Schneider', is_correct: false }
      ]
    },
    {
      id: 3,
      content_default: 'Quel est le calibre emblématique de Breitling ?',
      choices: [
        { content: 'B01', is_correct: true },
        { content: 'B09', is_correct: false },
        { content: 'B20', is_correct: false },
        { content: 'B13', is_correct: false }
      ]
    },
    {
      id: 4,
      content_default: 'Quelle est la montre iconique de Breitling depuis 1952 ?',
      choices: [
        { content: 'Navitimer', is_correct: true },
        { content: 'Superocean', is_correct: false },
        { content: 'Avenger', is_correct: false },
        { content: 'Premier', is_correct: false }
      ]
    },
    {
      id: 5,
      content_default: 'En quelle année le premier poussoir indépendant a-t-il été créé ?',
      choices: [
        { content: '1915', is_correct: true },
        { content: '1920', is_correct: false },
        { content: '1934', is_correct: false },
        { content: '1952', is_correct: false }
      ]
    }
  ]
  
  totalQuestions.value = questions.value.length
  console.log('🔄 Using fallback questions:', questions.value.length)
}

// Methods
const startTimer = () => {
  timerInterval = setInterval(() => {
    if (timeLeft.value > 0) {
      timeLeft.value--
    } else {
      // Temps écoulé, passer à la question suivante
      selectAnswer(null) // Aucune réponse sélectionnée
    }
  }, 1000)
}

const stopTimer = () => {
  if (timerInterval) {
    clearInterval(timerInterval)
    timerInterval = null
  }
}

const selectAnswer = (index) => {
  if (hasAnswered.value) return
  
  selectedAnswer.value = index
  hasAnswered.value = true
  stopTimer()

  // Calculer le temps pris pour répondre
  const timeTaken = 30 - timeLeft.value
  playerTime.value += timeTaken

  // Vérifier si la réponse est correcte
  let isCorrect = false
  let selectedAnswerText = 'Pas de réponse'
  
  if (index !== null && currentQuestion.value?.answers[index]) {
    isCorrect = currentQuestion.value.answers[index].correct
    selectedAnswerText = currentQuestion.value.answers[index].text
    
    if (isCorrect) {
      playerScore.value++
    }
  }
  
  // Sauvegarder la réponse du joueur
  playerAnswers.value.push({
    questionId: currentQuestion.value?.id,
    questionText: currentQuestion.value?.text,
    selectedAnswer: selectedAnswerText,
    correct: isCorrect,
    time: timeTaken,
    timeLeft: timeLeft.value
  })
  
  console.log('📝 Answer recorded:', {
    question: currentQuestion.value?.text,
    answer: selectedAnswerText,
    correct: isCorrect,
    time: timeTaken
  })
  
  // Attendre un peu avant de passer à la question suivante
  setTimeout(() => {
    nextQuestion()
  }, 1500)
}

const nextQuestion = () => {
  if (currentQuestionIndex.value < totalQuestions.value - 1) {
    currentQuestionIndex.value++
    timeLeft.value = 30
    hasAnswered.value = false
    selectedAnswer.value = null
    startTimer()
  } else {
    // Quiz terminé
    finishBattle()
  }
}

const finishBattle = () => {
  stopTimer()
  
  // Générer des réponses pour l'adversaire (mockées)
  const opponentAnswers = questions.value.map((question, index) => {
    const randomAnswer = Math.floor(Math.random() * 4)
    const isCorrect = Math.random() > 0.3 // 70% de chance de réussir
    const timeTaken = Math.floor(Math.random() * 20) + 5 // Entre 5 et 25 secondes
    
    if (isCorrect) opponentScore.value++
    opponentTime.value += timeTaken
    
    return {
      questionId: question.id,
      questionText: question.content_default,
      selectedAnswer: question.choices?.[randomAnswer]?.content || 'Réponse mockée',
      correct: isCorrect,
      time: timeTaken,
      timeLeft: Math.max(0, 30 - timeTaken)
    }
  })
  
  // Sauvegarder les résultats pour BattleDetailsView
  const battleResults = {
    battleId: battleData.value?.battleId || Date.now(),
    opponent: opponent.value,
    playerScore: playerScore.value,
    opponentScore: opponentScore.value,
    playerTime: playerTime.value,
    opponentTime: opponentTime.value,
    questionsData: questions.value.map(q => ({
      id: q.id,
      text: q.content_default || q.content_lf_tf || q.content_lf_blank,
      correctAnswer: q.choices?.find(c => c.is_correct)?.content || 'Réponse correcte'
    })),
    playerAnswers: playerAnswers.value,
    opponentAnswers: opponentAnswers
  }
  
  localStorage.setItem('lastBattleResults', JSON.stringify(battleResults))
  
  console.log('🏁 Battle finished:', {
    playerScore: playerScore.value,
    opponentScore: opponentScore.value,
    playerTime: playerTime.value,
    opponentTime: opponentTime.value
  })
  
  // Rediriger vers les détails
  router.push(`/battle-details/${battleResults.battleId}`)
}

const getAnswerClass = (index) => {
  if (!hasAnswered.value) return ''
  
  if (selectedAnswer.value === index) {
    return currentQuestion.value?.answers[index]?.correct ? 'correct' : 'incorrect'
  }
  
  if (currentQuestion.value?.answers[index]?.correct) {
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

// Lifecycle
onMounted(() => {
  loadBattleData()
  
  // Démarrer le timer seulement si on a des questions
  setTimeout(() => {
    if (questions.value.length > 0) {
      startTimer()
    }
  }, 1000) // Laisser 1 seconde pour que l'utilisateur voie la question
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

/* LOADING STATE */
.loading-container {
  text-align: center;
  padding: 3rem;
}

.loading-spinner {
  width: 50px;
  height: 50px;
  border: 4px solid rgba(247, 199, 44, 0.3);
  border-top: 4px solid #F7C72C;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem auto;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
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