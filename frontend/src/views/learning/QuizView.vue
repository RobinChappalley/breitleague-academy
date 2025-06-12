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

        <!-- Answer Options -->
        <div class="answers-section">
          <div 
            v-for="(answer, index) in currentQuestion.answers" 
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

        <!-- Validation Button -->
        <div class="button-section">
          <!-- Cancel/Close Button -->
          <button 
            v-if="showResult && !currentQuestion.answers[selectedAnswer]?.correct" 
            class="cancel-btn"
            @click="resetQuestion"
          >
            ✕
          </button>
          
          <!-- Next Button -->
          <button 
            class="next-btn"
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
      answers: [
        { text: "youhou", correct: false },
        { text: "youhou", correct: true },
        { text: "youhou", correct: false },
        { text: "youhou", correct: false }
      ]
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
const selectAnswer = (index) => {
  if (showResult.value) return
  
  selectedAnswer.value = index
  showResult.value = true
  
  emit('answer-selected', {
    questionIndex: props.currentStep - 1,
    answerIndex: index,
    correct: currentQuestion.value.answers[index].correct
  })
}

const handleNext = () => {
  if (selectedAnswer.value === null && !showResult.value) return
  
  emit('next-step')
  console.log('Going to next step')
}

const resetQuestion = () => {
  selectedAnswer.value = null
  showResult.value = false
}

console.log('QuizView component loaded')
</script>

<style scoped>
.quiz-page {
  width: 100vw;
  min-height: 100vh;
  background: #072C54;
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
}
</style>