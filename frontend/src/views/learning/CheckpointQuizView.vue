<template>
  <div class="quiz-page">
    <!-- Progress Bar -->
    <div class="progress-container">
      <div class="progress-bar">
        <div class="progress-fill" :style="{ width: progressPercentage + '%' }"></div>
      </div>
    </div>

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
              'selected': selectedAnswer === index,
              'correct': showResult && answer.correct,
              'incorrect': showResult && selectedAnswer === index && !answer.correct
            }"
            @click="selectAnswer(index)"
          >
            {{ answer.text }}
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

    <!-- Timer Display -->
    <div class="timer-display">
      <span class="timer-icon">⏱️</span>
      <span class="timer-text">{{ formatTime(timeRemaining) }}</span>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// Quiz State
const currentQuestionIndex = ref(0)
const selectedAnswer = ref(null)
const userAnswers = ref([])
const timeRemaining = ref(1200) // 20 minutes in seconds
const totalTime = ref(1200)
const showResult = ref(false)
const quizTimer = ref(null)

// Quiz Data
const questions = ref([
  {
    question: "When was Breitling founded and by whom?",
    answers: [
      { text: "1884 by Léon Breitling", correct: true },
      { text: "1905 by Gaston Breitling", correct: false },
      { text: "1892 by Georges Breitling", correct: false },
      { text: "1878 by Henri Breitling", correct: false }
    ]
  },
  {
    question: "What was Breitling's primary focus when the company was first established?",
    answers: [
      { text: "Luxury dress watches", correct: false },
      { text: "Chronographs and precision timing instruments", correct: true },
      { text: "Marine chronometers", correct: false },
      { text: "Pocket watches for the general public", correct: false }
    ]
  },
  {
    question: "Which Breitling watch was the first wrist-worn chronograph?",
    answers: [
      { text: "Navitimer", correct: false },
      { text: "Premier", correct: false },
      { text: "Montbrillant", correct: true },
      { text: "Chronomat", correct: false }
    ]
  },
  {
    question: "What makes the Breitling Navitimer distinctive?",
    answers: [
      { text: "Its diving capabilities", correct: false },
      { text: "The integrated slide rule bezel", correct: true },
      { text: "The GMT function", correct: false },
      { text: "The perpetual calendar", correct: false }
    ]
  },
  {
    question: "Which professional group was primarily targeted by early Breitling chronographs?",
    answers: [
      { text: "Racing drivers", correct: false },
      { text: "Aviation professionals", correct: true },
      { text: "Military officers", correct: false },
      { text: "Scientists and researchers", correct: false }
    ]
  },
  {
    question: "What does the Breitling Emergency watch feature?",
    answers: [
      { text: "GPS navigation", correct: false },
      { text: "Emergency locator transmitter", correct: true },
      { text: "Satellite communication", correct: false },
      { text: "Weather forecast", correct: false }
    ]
  },
  {
    question: "In which year was the Navitimer first launched?",
    answers: [
      { text: "1950", correct: false },
      { text: "1952", correct: true },
      { text: "1954", correct: false },
      { text: "1956", correct: false }
    ]
  },
  {
    question: "What is the signature feature of Breitling's Superocean collection?",
    answers: [
      { text: "Chronograph function", correct: false },
      { text: "Diving capabilities", correct: true },
      { text: "GMT complication", correct: false },
      { text: "Skeleton dial", correct: false }
    ]
  },
  {
    question: "Which caliber is most commonly associated with modern Breitling Navitimer watches?",
    answers: [
      { text: "B01", correct: true },
      { text: "B09", correct: false },
      { text: "B20", correct: false },
      { text: "B13", correct: false }
    ]
  },
  {
    question: "What is the minimum passing score for this checkpoint?",
    answers: [
      { text: "60%", correct: false },
      { text: "65%", correct: false },
      { text: "70%", correct: true },
      { text: "75%", correct: false }
    ]
  }
])

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

// Methods
const selectAnswer = (answerIndex) => {
  if (!showResult.value) {
    selectedAnswer.value = answerIndex
    showResult.value = true
    
    // Auto-advance after showing result
    setTimeout(() => {
      nextQuestion()
    }, 1500)
  }
}

const nextQuestion = () => {
  if (selectedAnswer.value !== null || showResult.value) {
    // Save answer
    userAnswers.value[currentQuestionIndex.value] = selectedAnswer.value
    
    if (currentQuestion.value === totalQuestions.value) {
      // Finish test - redirect directly to results page
      completeTest()
    } else {
      // Next question
      currentQuestionIndex.value++
      selectedAnswer.value = null
      showResult.value = false
    }
  }
}

const completeTest = () => {
  clearInterval(quizTimer.value)
  
  // Redirect directly to CheckpointResults page
  router.push({
    name: 'CheckpointResults',
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
  if (confirm('Are you sure you want to exit the quiz? Your progress will be lost.')) {
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

// Lifecycle
onMounted(() => {
  startTimer()
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
.timer-display {
  position: fixed;
  top: 1rem;
  right: 1rem;
  background: rgba(0, 0, 0, 0.7);
  border-radius: 20px;
  padding: 0.5rem 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  z-index: 100;
}

.timer-icon {
  font-size: 1rem;
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
}

.answer-option:hover {
  background: rgba(255, 255, 255, 0.95);
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
}

.answer-option.selected {
  border-color: #F7C72C;
  background: rgba(247, 199, 44, 0.2);
  color: white;
}

.answer-option.correct {
  border-color: #22c55e;
  background: rgba(34, 197, 94, 0.2);
  color: white;
}

.answer-option.incorrect {
  border-color: #ef4444;
  background: rgba(239, 68, 68, 0.2);
  color: white;
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
  
  .next-btn {
    padding: 0.8rem 2rem;
    font-size: 0.9rem;
  }
  
  .cancel-btn {
    width: 40px;
    height: 40px;
    font-size: 1rem;
  }
  
  .timer-display {
    top: 0.5rem;
    right: 0.5rem;
    padding: 0.4rem 0.8rem;
  }
  
  .timer-text {
    font-size: 0.8rem;
  }
}
</style>