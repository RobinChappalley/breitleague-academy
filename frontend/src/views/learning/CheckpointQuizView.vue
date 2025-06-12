<template>
  <div class="quiz-page">
    <!-- Loading State -->
    <div v-if="loading" class="loading-container">
      <div class="loading-spinner"></div>
      <p>Chargement des questions...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-container">
      <p>{{ error }}</p>
      <button @click="loadQuestions" class="retry-btn">Réessayer</button>
    </div>

    <!-- Quiz Content -->
    <div v-else-if="questions.length > 0">
      <!-- Progress Bar -->
      <div class="progress-container">
        <div class="progress-bar">
          <div class="progress-fill" :style="{ width: progressPercentage + '%' }"></div>
        </div>
      </div>
      <!-- Timer Centered -->
    <h1 class="timer-large">{{ formatTime(timeRemaining) }}</h1>

      <!-- Quiz Content -->
      <div class="quiz-content">
        <div class="quiz-container">
          <!-- Question -->
          <div class="question-section">
            <p class="question-text">{{ questions[currentQuestionIndex].question }}</p>
          </div>

          <!-- Answer Options -->
          <div class="answers-section">
            <div 
              v-for="(answer, index) in questions[currentQuestionIndex].answers" 
              :key="index"
              class="answer-option"
              :class="{
                'selected': selectedAnswer === index && !showResult,
                'correct': showResult && answer.correct,
                'incorrect': showResult && selectedAnswer === index && !answer.correct,
                'not-selected': showResult && selectedAnswer !== index && !answer.correct
              }"
              @click="selectAnswer(index)"
            >
              <span class="answer-text">{{ answer.text }}</span>
              <span v-if="showResult && answer.correct" class="check-icon">✓</span>
              <span v-if="showResult && selectedAnswer === index && !answer.correct" class="cross-icon">✗</span>
            </div>
          </div>

          <!-- Navigation -->
          <div class="button-section">
            <button 
              class="cancel-btn" 
              @click="exitQuiz"
              title="Exit Quiz"
            >
              ✕
            </button>
            
            <button 
              class="next-btn"
              :class="{ 'disabled': selectedAnswer === null && !showResult }"
              @click="nextQuestion"
              :disabled="selectedAnswer === null && !showResult"
            >
              {{ currentQuestion === totalQuestions ? 'FINISH' : 'NEXT' }}
            </button>
          </div>
        </div>
      </div>

      
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { battleService } from '@/services/api'

const router = useRouter()

// Quiz State
const currentQuestionIndex = ref(0)
const selectedAnswer = ref(null)
const userAnswers = ref([])
const timeRemaining = ref(600) // 20 minutes in seconds
const totalTime = ref(600)
const showResult = ref(false)
const quizTimer = ref(null)

// API State
const loading = ref(true)
const error = ref(null)
const questions = ref([])

// Configuration
const CHECKPOINT_ID = 1

// Computed Properties
const currentQuestion = computed(() => currentQuestionIndex.value + 1)
const totalQuestions = computed(() => questions.value.length)
const progressPercentage = computed(() => (currentQuestion.value / totalQuestions.value) * 100)

const correctAnswers = computed(() => {
  return userAnswers.value.filter((answer, index) => {
    return questions.value[index].answers[answer]?.correct
  }).length
})

const finalScore = computed(() => {
  return Math.round((correctAnswers.value / totalQuestions.value) * 100)
})

const testPassed = computed(() => finalScore.value >= 70)

// API Methods
const loadQuestions = async () => {
  try {
    loading.value = true
    error.value = null

    const questionsRaw = await battleService.getQuestions()
    const choicesRaw = await battleService.getChoices()

    const questionsData = questionsRaw.data || questionsRaw
    const choicesData = choicesRaw.data || choicesRaw

    const checkpointQuestions = questionsData.filter(q => q.checkpoint_id === CHECKPOINT_ID)
    const qcmQuestions = transformQuestionsToQCM(checkpointQuestions, choicesData)

    if (qcmQuestions.length === 0) {
      throw new Error('Aucune question QCM trouvée pour ce checkpoint')
    }

    questions.value = qcmQuestions
    loading.value = false

  } catch (err) {
    console.error('Erreur lors du chargement des questions:', err)
    error.value = err.message || 'Erreur lors du chargement des questions'
    loading.value = false
  }
}

const transformQuestionsToQCM = (questionsData, choicesData) => {
  const qcmQuestions = questionsData
    .map(question => {
      const questionChoices = choicesData.filter(choice => choice.question_id === question.id)
      if (questionChoices.length < 2) return null

      const correctChoiceId = question.correct_answer_text?.correct_choice_id || 
                             question.correct_choice_id ||
                             question.correct_answer_id

      const answers = questionChoices.map(choice => ({
        id: choice.id,
        text: choice.text_answer,
        correct: choice.id === correctChoiceId
      }))

      // Vérifier qu'au moins une réponse est marquée comme correcte
      const hasCorrectAnswer = answers.some(a => a.correct)
      if (!hasCorrectAnswer && answers.length > 0) {
        console.warn(`Aucune réponse correcte trouvée pour la question ${question.id}`)
        // Marquer la première réponse comme correcte par défaut (à ajuster selon votre logique)
        answers[0].correct = true
      }

      return {
        id: question.id,
        question: question.content_default,
        answers: shuffleArray(answers)
      }
    })
    .filter(question => question !== null)

  return shuffleArray(qcmQuestions)
}

// Quiz Methods
const selectAnswer = (answerIndex) => {
  if (!showResult.value) {
    selectedAnswer.value = answerIndex
  }
}

const nextQuestion = () => {
  if (selectedAnswer.value !== null && !showResult.value) {
    // 1. Enregistre la réponse
    userAnswers.value[currentQuestionIndex.value] = selectedAnswer.value

    // 2. Affiche les résultats (bonne réponse en vert)
    showResult.value = true

    // 3. Attend 2 secondes puis passe à la suite
    setTimeout(() => {
      if (currentQuestion.value === totalQuestions.value) {
        completeTest()
      } else {
        currentQuestionIndex.value++
        selectedAnswer.value = null
        showResult.value = false
      }
    }, 2000)
  }
}

const completeTest = () => {
  clearInterval(quizTimer.value)
  
  console.log('Résultats du quiz:', {
    score: finalScore.value,
    passed: testPassed.value,
    correctAnswers: correctAnswers.value,
    totalQuestions: totalQuestions.value,
    timeUsed: totalTime.value - timeRemaining.value,
    userAnswers: userAnswers.value
  })
  

  router.push({
    path: '/checkpoint-results',  
    query: {
      score: finalScore.value,
      passed: testPassed.value,
      correctAnswers: correctAnswers.value,
      totalQuestions: totalQuestions.value,
      timeUsed: totalTime.value - timeRemaining.value
    }
  })
}

const exitQuiz = () => {
  if (confirm('Êtes-vous sûr de vouloir quitter le quiz ? Votre progression sera perdue.')) {
    clearInterval(quizTimer.value)
    router.push('/checkpoint')
  }
}

const formatTime = (seconds) => {
  const minutes = Math.floor(seconds / 60)
  const remainingSeconds = seconds % 60
  return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`
}

const startTimer = () => {
  quizTimer.value = setInterval(() => {
    timeRemaining.value--
    if (timeRemaining.value <= 0) {
      completeTest()
    }
  }, 1000)
}

function shuffleArray(array) {
  const newArray = [...array]
  for (let i = newArray.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1))
    ;[newArray[i], newArray[j]] = [newArray[j], newArray[i]]
  }
  return newArray
}

// Lifecycle
onMounted(() => {
  loadQuestions().then(() => {
    if (questions.value.length > 0) {
      startTimer()
    }
  })
})

onUnmounted(() => {
  if (quizTimer.value) {
    clearInterval(quizTimer.value)
  }
})
</script>

<style scoped>
.quiz-page {
  width: 100vw;
  min-height: 100vh;
  background: linear-gradient(135deg, #072C54 0%, #1e3a8a 100%);
  color: white;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  position: relative;
}

/* LOADING STATE */
.loading-container {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 1rem;
}

.loading-spinner {
  width: 50px;
  height: 50px;
  border: 4px solid rgba(255, 255, 255, 0.3);
  border-top: 4px solid #F7C72C;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* ERROR STATE */
.error-container {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  padding: 2rem;
  text-align: center;
}

.retry-btn {
  background: #F7C72C;
  color: #072C54;
  border: none;
  padding: 0.8rem 2rem;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.retry-btn:hover {
  background: #E6B625;
  transform: translateY(-2px);
}

/* PROGRESS BAR */
.progress-container {
  width: 100%;
  padding: 1rem 2rem 0 2rem;
  flex-shrink: 0;
}

.progress-bar {
  width: 100%;
  height: 6px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 3px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: #F7C72C;
  border-radius: 3px;
  transition: width 0.3s ease;
}

/* TIMER */
.timer-large {

  text-align: center;
  color: #F7C72C;
  margin: 2rem;
}




.timer-text {
  font-size: 0.9rem;
  font-weight: 600;
  color: #F7C72C;
}

/* QUIZ CONTENT */
.quiz-content {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
}

.quiz-container {
  width: 100%;
  max-width: 600px;
}

/* QUESTION SECTION */
.question-section {
  margin-bottom: 3rem;
}

.question-text {
  font-size: 1.2rem;
  line-height: 1.6;
  color: white;
  text-align: center;
  margin: 0;
  padding: 0 1rem;
}

/* ANSWERS SECTION */
.answers-section {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-bottom: 3rem;
}

.answer-option {
  background: rgba(255, 255, 255, 0.9);
  color: #072C54;
  padding: 1.2rem;
  border-radius: 8px;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
  text-align: center;
  border: 3px solid transparent;
  min-height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}

.answer-option:hover:not(.correct):not(.incorrect) {
  background: rgba(255, 255, 255, 0.95);
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
}

.answer-option.selected {
  border-color: #F7C72C;
  background: rgba(247, 199, 44, 0.2);
  color: white;
}

/* Styles pour l'affichage des résultats */
.answer-option.correct {
  border-color: #22c55e !important;
  background: rgba(34, 197, 94, 0.9) !important;
  color: white !important;
  animation: correctPulse 0.5s ease;
  cursor: default;
}

.answer-option.incorrect {
  border-color: #ef4444 !important;
  background: rgba(239, 68, 68, 0.9) !important;
  color: white !important;
  animation: incorrectShake 0.5s ease;
  cursor: default;
}

.answer-option.not-selected {
  opacity: 0.5;
  cursor: default;
}

/* Icônes de résultat */
.check-icon, .cross-icon {
  position: absolute;
  right: 1rem;
  font-size: 1.5rem;
  font-weight: bold;
}

.check-icon {
  color: white;
}

.cross-icon {
  color: white;
}

/* Animations */
@keyframes correctPulse {
  0% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.05);
  }
  100% {
    transform: scale(1);
  }
}

@keyframes incorrectShake {
  0%, 100% {
    transform: translateX(0);
  }
  25% {
    transform: translateX(-5px);
  }
  75% {
    transform: translateX(5px);
  }
}

/* BUTTON SECTION */
.button-section {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
}

.cancel-btn {
  background: rgba(239, 68, 68, 0.8);
  color: white;
  border: none;
  width: 50px;
  height: 50px;
  border-radius: 50%;
  font-size: 1.2rem;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.cancel-btn:hover {
  background: rgba(239, 68, 68, 1);
  transform: translateY(-2px);
}

.next-btn {
  background: #F7C72C;
  color: #072C54;
  border: none;
  padding: 1rem 3rem;
  border-radius: 8px;
  font-weight: 700;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
  text-transform: uppercase;
  letter-spacing: 1px;
  flex: 1;
  max-width: 300px;
}

.next-btn:hover:not(.disabled) {
  background: #E6B625;
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
}

.next-btn.disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* RESPONSIVE - TABLET */
@media (min-width: 768px) and (max-width: 1199px) {
  .progress-container {
    padding: 1.5rem 3rem 0 3rem;
  }
  
  .quiz-content {
    padding: 3rem;
  }
  
  .quiz-container {
    max-width: 700px;
  }
  
  .question-text {
    font-size: 1.3rem;
    padding: 0 2rem;
  }
  
  .answer-option {
    padding: 1.5rem;
    font-size: 1.1rem;
    min-height: 70px;
  }
}

/* RESPONSIVE - DESKTOP */
@media (min-width: 1200px) {
  .progress-container {
    padding: 2rem 4rem 0 4rem;
  }
  
  .quiz-content {
    padding: 4rem;
  }
  
  .quiz-container {
    max-width: 800px;
  }
  
  .question-section {
    margin-bottom: 4rem;
  }
  
  .question-text {
    font-size: 1.4rem;
    line-height: 1.7;
    padding: 0 3rem;
  }
  
  .answers-section {
    gap: 1.5rem;
    margin-bottom: 4rem;
  }
  
  .answer-option {
    padding: 2rem;
    font-size: 1.2rem;
    min-height: 80px;
  }
  
  .next-btn {
    padding: 1.2rem 4rem;
    font-size: 1.1rem;
    max-width: 400px;
  }
  
  .cancel-btn {
    width: 60px;
    height: 60px;
    font-size: 1.4rem;
  }
}

/* RESPONSIVE - MOBILE */
@media (max-width: 767px) {
  .progress-container {
    padding: 1rem 1rem 0 1rem;
  }
  
  .quiz-content {
    padding: 1.5rem 1rem;
  }
  
  .question-section {
    margin-bottom: 2rem;
  }
  
  .question-text {
    font-size: 1rem;
    padding: 0 0.5rem;
  }
  
  .answers-section {
    grid-template-columns: 1fr;
    gap: 0.8rem;
    margin-bottom: 2rem;
  }
  
  .answer-option {
    padding: 1rem;
    font-size: 0.9rem;
    min-height: 50px;
  }
  
  .check-icon, .cross-icon {
    font-size: 1.2rem;
    right: 0.8rem;
  }
  
  .next-btn {
    padding: 0.8rem 2rem;
    font-size: 0.9rem;
  }
  
  .cancel-btn {
    width: 40px;
    height: 40px;
    font-size: 1rem;
  }
  

  
  .timer-text {
    font-size: 0.8rem;
  }
}
</style>