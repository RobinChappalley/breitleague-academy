<template>
  <div class="lesson-completed">
    <div class="completion-container">
      <!-- Title -->
      <div class="title-section">
        <h1 class="lesson-title">LESSON {{ lessonNumber }}</h1>
        <h2 class="completed-text">COMPLETED</h2>
      </div>

      <!-- Progress Circle -->
      <div class="progress-section">
        <div class="progress-circle">
          <svg class="progress-ring" width="200" height="200">
            <circle
              class="progress-ring-background"
              stroke="rgba(247, 199, 44, 0.2)"
              stroke-width="12"
              fill="transparent"
              r="94"
              cx="100"
              cy="100"
            />
            <circle
              class="progress-ring-progress"
              stroke="#F7C72C"
              stroke-width="12"
              fill="transparent"
              r="94"
              cx="100"
              cy="100"
              :stroke-dasharray="circumference"
              :stroke-dashoffset="strokeDashoffset"
              transform="rotate(-90 100 100)"
            />
          </svg>
          <div class="progress-text">
            <span class="percentage">{{ percentage }}%</span>
          </div>
        </div>
      </div>

      <!-- Finish Button -->
      <div class="button-section">
        <button class="finish-btn" @click="handleFinish">
          FINISH
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
const router = useRouter()

// Props
const props = defineProps({
  lessonNumber: {
    type: Number,
    default: 1
  },
  score: {
    type: Number,
    default: 100 // Pourcentage de réussite
  },
  correctAnswers: {
    type: Number,
    default: 4
  },
  totalQuestions: {
    type: Number,
    default: 4
  }
})

// Reactive data
const animatedPercentage = ref(0)
const isAnimating = ref(false)

// Computed
const percentage = computed(() => props.score)
const circumference = computed(() => 2 * Math.PI * 94) // rayon = 94
const strokeDashoffset = computed(() => {
  const progress = animatedPercentage.value / 100
  return circumference.value - (progress * circumference.value)
})

// Emit events
const emit = defineEmits(['finish', 'next-lesson', 'back-to-menu'])

// Methods
const animateProgress = () => {
  isAnimating.value = true
  const targetPercentage = percentage.value
  const duration = 2000 // 2 secondes
  const steps = 60 // 60 FPS
  const increment = targetPercentage / steps
  let currentStep = 0

  const timer = setInterval(() => {
    currentStep++
    animatedPercentage.value = Math.min(currentStep * increment, targetPercentage)
    
    if (currentStep >= steps) {
      clearInterval(timer)
      animatedPercentage.value = targetPercentage
      isAnimating.value = false
    }
  }, duration / steps)
}

const handleFinish = () => {
  emit('finish')

  emit('next-lesson')

  router.push('/')// ou 'back-to-menu' selon le contexte

}

// Lifecycle
onMounted(() => {
  
  setTimeout(() => {
    animateProgress()
  }, 500)
})
</script>

<style scoped>
.lesson-completed {
  width: 100vw;
  min-height: 100vh;
  background: linear-gradient(135deg, #072C54 0%, #1e3a8a 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  padding: 2rem;
}

.completion-container {
  width: 100%;
  max-width: 400px;
  text-align: center;
}

/* TITLE SECTION */
.title-section {
  margin-bottom: 4rem;
}

.lesson-title {
  font-size: 2.5rem;
  font-weight: 900;
  color: #F7C72C;
  margin: 0;
  letter-spacing: 2px;
  text-transform: uppercase;
}

.completed-text {
  font-size: 2rem;
  font-weight: 900;
  color: #F7C72C;
  margin: 0.5rem 0 0 0;
  letter-spacing: 2px;
  text-transform: uppercase;
}

/* PROGRESS SECTION */
.progress-section {
  margin-bottom: 4rem;
  display: flex;
  justify-content: center;
}

.progress-circle {
  position: relative;
  width: 200px;
  height: 200px;
}

.progress-ring {
  width: 100%;
  height: 100%;
  transform: rotate(-90deg);
}

.progress-ring-background {
  opacity: 0.3;
}

.progress-ring-progress {
  transition: stroke-dashoffset 0.1s ease;
  stroke-linecap: round;
}

.progress-text {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  display: flex;
  align-items: center;
  justify-content: center;
}

.percentage {
  font-size: 2.5rem;
  font-weight: 900;
  color: #F7C72C;
  letter-spacing: 1px;
}

/* BUTTON SECTION */
.button-section {
  width: 100%;
}

.finish-btn {
  background: #F7C72C;
  color: #072C54;
  border: none;
  padding: 1.2rem 4rem;
  border-radius: 12px;
  font-weight: 900;
  font-size: 1.1rem;
  cursor: pointer;
  transition: all 0.3s ease;
  text-transform: uppercase;
  letter-spacing: 2px;
  width: 100%;
  max-width: 300px;
  box-shadow: 0 4px 20px rgba(247, 199, 44, 0.3);
}

.finish-btn:hover {
  background: #E6B625;
  transform: translateY(-3px);
  box-shadow: 0 8px 30px rgba(247, 199, 44, 0.4);
}

.finish-btn:active {
  transform: translateY(-1px);
}

/* RESPONSIVE */
@media (max-width: 767px) {
  .lesson-completed {
    padding: 1rem;
  }
  
  .lesson-title {
    font-size: 2rem;
  }
  
  .completed-text {
    font-size: 1.5rem;
  }
  
  .progress-circle {
    width: 160px;
    height: 160px;
  }
  
  .progress-ring {
    width: 160px;
    height: 160px;
  }
  
  .percentage {
    font-size: 2rem;
  }
  
  .finish-btn {
    padding: 1rem 3rem;
    font-size: 1rem;
  }
}

/* Animation pour l'apparition */
.completion-container {
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
</style>