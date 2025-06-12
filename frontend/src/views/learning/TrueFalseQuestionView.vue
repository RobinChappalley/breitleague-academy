<template>
  <div class="quiz-page">
    <!-- Progress Bar -->
    <div class="progress-container">
      <div class="progress-bar">
        <div class="progress-fill" :style="{ width: `${progress}%` }"></div>
      </div>
    </div>

    <!-- Quiz Content -->
    <div class="quiz-content">
      <div class="quiz-container">
        <!-- Question -->
        <div class="question-section">
          <p class="question-text">{{ currentQuestion.question }}</p>
        </div>

        <!-- True/False Options -->
        <div class="answers-section">
          <div 
            class="answer-option true-false-option"
            :class="{ 
              'selected': selectedAnswer === 'true',
              'correct': showResult && currentQuestion.correctAnswer === 'true',
              'incorrect': showResult && selectedAnswer === 'true' && currentQuestion.correctAnswer !== 'true'
            }"
            @click="selectAnswer('true')"
          >
            Vrai
          </div>
          
          <div 
            class="answer-option true-false-option"
            :class="{ 
              'selected': selectedAnswer === 'false',
              'correct': showResult && currentQuestion.correctAnswer === 'false',
              'incorrect': showResult && selectedAnswer === 'false' && currentQuestion.correctAnswer !== 'false'
            }"
            @click="selectAnswer('false')"
          >
            Faux
          </div>
        </div>

        <!-- Validation Button -->
        <div class="button-section">
          <!-- Cancel/Close Button -->
          <button 
           
            class="cancel-btn"
            @click="resetQuestion"
          >
            ✕
          </button>
          
          <!-- Next Button -->
          <button 
            class="next-btn primary-btn"
            :class="{ 'disabled': selectedAnswer === null && !showResult }"
            @click="handleNext"
            :disabled="selectedAnswer === null && !showResult"
          >
            NEXT
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

// Props
const props = defineProps({
  questionData: {
    type: Object,
    default: () => ({
      question: "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor",
      correctAnswer: "true" // "true" ou "false"
    })
  },
  currentStep: {
    type: Number,
    default: 5
  },
  totalSteps: {
    type: Number,
    default: 10
  }
})

// Reactive data
const currentQuestion = ref(props.questionData)
const selectedAnswer = ref(null)
const showResult = ref(false)
const progress = computed(() => (props.currentStep / props.totalSteps) * 100)

// Emit events
const emit = defineEmits(['next-step', 'answer-selected'])

// Methods
const selectAnswer = (answer) => {
  if (showResult.value) return
  
  selectedAnswer.value = answer
  showResult.value = true
  
  emit('answer-selected', {
    questionIndex: props.currentStep - 1,
    answer: answer,
    correct: answer === currentQuestion.value.correctAnswer
  })
}

const handleNext = () => {
  if (selectedAnswer.value === null && !showResult.value) return
  
  emit('next-step')
}

const resetQuestion = () => {
  selectedAnswer.value = null
  showResult.value = false
}
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
  gap: 1.5rem;
  margin-bottom: 3rem;
}

.true-false-option {
  background: rgba(255, 255, 255, 0.9);
  color: #072C54;
  padding: 2rem;
  border-radius: 8px;
  font-weight: 600;
  font-size: 1.2rem;
  cursor: pointer;
  transition: all 0.3s ease;
  text-align: center;
  border: 3px solid transparent;
  min-height: 80px;
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
  background: #F7C72C;
  color: #072C54;
  border: none;
  width: 50px;
  height: 50px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
}



.next-btn {
  background: #F7C72C;
  color: #072C54;
  height: 50px;
  border: none;
  padding: 1rem 3rem;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
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

/* RESPONSIVE */
@media (max-width: 767px) {
  .answers-section {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .true-false-option {
    padding: 1.5rem;
    font-size: 1rem;
    min-height: 60px;
  }
}
</style>