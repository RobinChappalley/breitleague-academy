<template>
  <div class="theory-page">
    <!-- Progress Bar -->
    <div class="progress-container">
      <div class="progress-bar">
        <div class="progress-fill" :style="{ width: `${progress}%` }"></div>
      </div>
    </div>

    <!-- Theory Content -->
    <div class="theory-content">
      <div class="theory-container">
        <!-- Theory Header -->
        <div class="theory-header">
          <h1 class="theory-title">THÉORIE</h1>
          <h2 class="theory-subtitle">{{ currentTheory.title }}</h2>
        </div>

        <!-- Theory Text -->
        <div class="theory-text">
          <p v-for="(paragraph, index) in currentTheory.content" :key="index" class="theory-paragraph">
            {{ paragraph }}
          </p>
        </div>

        <!-- Next Button -->
        <button class="next-btn" @click="nextStep">
          NEXT
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

// Props pour recevoir les données de la théorie
const props = defineProps({
  theoryData: {
    type: Object,
    default: () => ({
      title: "L'AVENGER",
      content: [
        "Robuste et audacieuse, l'Avenger est inspirée de l'héritage aéronautique de Breitling.",
        "Relancée en 2019, elle est ultralegible même avec des gants, et depuis 2022, elle embarque le calibre Breitling 01."
      ]
    })
  },
  currentStep: {
    type: Number,
    default: 1
  },
  totalSteps: {
    type: Number,
    default: 10
  }
})

// Reactive data
const currentTheory = ref(props.theoryData)
const progress = computed(() => (props.currentStep / props.totalSteps) * 100)

// Emit events
const emit = defineEmits(['next-step'])

// Methods
const nextStep = () => {
  emit('next-step')
  console.log('Going to next step')
}

console.log('TheoryView component loaded')
</script>

<style scoped>
.theory-page {
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

/* THEORY CONTENT */
.theory-content {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
}

.theory-container {
  width: 100%;
  max-width: 600px;
  text-align: center;
}

/* THEORY HEADER */
.theory-header {
  margin-bottom: 3rem;
}

.theory-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: #F7C72C;
  margin: 0 0 1rem 0;
  text-transform: uppercase;
  letter-spacing: 2px;
}

.theory-subtitle {
  font-size: 1.8rem;
  font-weight: 600;
  color: white;
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 1px;
}

/* THEORY TEXT */
.theory-text {
  margin-bottom: 4rem;
  text-align: left;
}

.theory-paragraph {
  font-size: 1.1rem;
  line-height: 1.7;
  color: rgba(255, 255, 255, 0.95);
  margin-bottom: 1.5rem;
  text-align: justify;
}

.theory-paragraph:last-child {
  margin-bottom: 0;
}

/* NEXT BUTTON */
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
  width: 100%;
  max-width: 300px;
}

.next-btn:hover {
  background: #E6B625;
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
}

/* RESPONSIVE - TABLET */
@media (min-width: 768px) and (max-width: 1199px) {
  .progress-container {
    padding: 1.5rem 3rem 0 3rem;
  }
  
  .theory-content {
    padding: 3rem;
  }
  
  .theory-container {
    max-width: 700px;
  }
  
  .theory-title {
    font-size: 3rem;
  }
  
  .theory-subtitle {
    font-size: 2rem;
  }
  
  .theory-paragraph {
    font-size: 1.2rem;
  }
}

/* RESPONSIVE - DESKTOP */
@media (min-width: 1200px) {
  .progress-container {
    padding: 2rem 4rem 0 4rem;
  }
  
  .theory-content {
    padding: 4rem;
  }
  
  .theory-container {
    max-width: 800px;
  }
  
  .theory-header {
    margin-bottom: 4rem;
  }
  
  .theory-title {
    font-size: 3.5rem;
    margin-bottom: 1.5rem;
  }
  
  .theory-subtitle {
    font-size: 2.2rem;
  }
  
  .theory-text {
    margin-bottom: 5rem;
  }
  
  .theory-paragraph {
    font-size: 1.3rem;
    line-height: 1.8;
    margin-bottom: 2rem;
  }
  
  .next-btn {
    padding: 1.2rem 4rem;
    font-size: 1.1rem;
    max-width: 400px;
  }
}

/* RESPONSIVE - MOBILE */
@media (max-width: 767px) {
  .progress-container {
    padding: 1rem 1rem 0 1rem;
  }
  
  .theory-content {
    padding: 1.5rem 1rem;
  }
  
  .theory-header {
    margin-bottom: 2rem;
  }
  
  .theory-title {
    font-size: 2rem;
  }
  
  .theory-subtitle {
    font-size: 1.4rem;
  }
  
  .theory-text {
    margin-bottom: 3rem;
  }
  
  .theory-paragraph {
    font-size: 1rem;
    line-height: 1.6;
    margin-bottom: 1.2rem;
  }
  
  .next-btn {
    padding: 0.8rem 2rem;
    font-size: 0.9rem;
  }
}
</style>