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
        <!-- Watch Options -->
        <div class="watches-section">
          <div 
            v-for="(watch, index) in watchOptions" 
            :key="index"
            class="watch-option"
            :class="{ 
              'selected': selectedWatch === index,
              'correct': showResult && index === correctAnswer,
              'incorrect': showResult && selectedWatch === index && index !== correctAnswer
            }"
            @click="selectWatch(index)"
          >
            <div class="watch-image">
              <img :src="watch.image" :alt="watch.name" />
            </div>
          </div>
        </div>

        <!-- Question -->
        <div class="question-section">
          <p class="question-text">{{ currentQuestion.question }}</p>
        </div>

        <!-- Validation Button -->
        <div class="button-section">
          <!-- Cancel/Close Button -->
          <button 
            v-if="showResult && selectedWatch !== correctAnswer" 
            class="cancel-btn"
            @click="resetQuestion"
          >
            ✕
          </button>
          
          <!-- Next Button -->
          <button 
            class="next-btn"
            :class="{ 'disabled': selectedWatch === null && !showResult }"
            @click="handleNext"
            :disabled="selectedWatch === null && !showResult"
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
      question: "Quel modèle possède un calibre Breitling B23 ?",
      watches: [
        { name: "Premier", image: "/images/watches/premier.jpg" },
        { name: "Navitimer", image: "/images/watches/navitimer.jpg" },
        { name: "Superocean", image: "/images/watches/superocean.jpg" },
        { name: "Avenger", image: "/images/watches/avenger.jpg" }
      ],
      correctAnswer: 1 // Index de la bonne réponse
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
const selectedWatch = ref(null)
const showResult = ref(false)
const progress = computed(() => (props.currentStep / props.totalSteps) * 100)

// Watch options avec images
const watchOptions = ref(currentQuestion.value.watches)
const correctAnswer = ref(currentQuestion.value.correctAnswer)

// Emit events
const emit = defineEmits(['next-step', 'answer-selected'])

// Methods
const selectWatch = (index) => {
  if (showResult.value) return
  
  selectedWatch.value = index
  showResult.value = true
  
  emit('answer-selected', {
    questionIndex: props.currentStep - 1,
    selectedWatch: index,
    correct: index === correctAnswer.value
  })
}

const handleNext = () => {
  if (selectedWatch.value === null && !showResult.value) return
  
  emit('next-step')
}

const resetQuestion = () => {
  selectedWatch.value = null
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

/* WATCHES SECTION */
.watches-section {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
  margin-bottom: 3rem;
}

.watch-option {
  background: rgba(255, 255, 255, 0.9);
  border-radius: 12px;
  padding: 1.5rem;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 3px solid transparent;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 150px;
}

.watch-option:hover {
  background: rgba(255, 255, 255, 0.95);
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
}

.watch-option.selected {
  border-color: #F7C72C;
  background: rgba(247, 199, 44, 0.1);
}

.watch-option.correct {
  border-color: #22c55e;
  background: rgba(34, 197, 94, 0.1);
}

.watch-option.incorrect {
  border-color: #ef4444;
  background: rgba(239, 68, 68, 0.1);
}

.watch-image {
  width: 100%;
  height: 120px;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}

.watch-image img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  border-radius: 8px;
}

/* Fallback si pas d'image */
.watch-image:not(:has(img)) {
  background: #f0f0f0;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 3rem;
  color: #666;
}

.watch-image:not(:has(img))::before {
  content: "⌚";
}

/* QUESTION SECTION */
.question-section {
  margin-bottom: 3rem;
  text-align: center;
}

.question-text {
  font-size: 1.2rem;
  line-height: 1.6;
  color: white;
  margin: 0;
  padding: 0 1rem;
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
  width: 75px;
  height: 75px;
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

/* RESPONSIVE */
@media (max-width: 767px) {
  .watches-section {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .watch-option {
    padding: 1rem;
    min-height: 120px;
  }
  
  .watch-image {
    height: 100px;
  }
  
  .question-text {
    font-size: 1rem;
  }
}

@media (min-width: 768px) and (max-width: 1023px) {
  .watch-image {
    height: 140px;
  }
}
</style>