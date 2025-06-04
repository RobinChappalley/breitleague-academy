<template>
  <div class="checkpoint-page">
    <div class="main-content">
      <!-- Header -->
      <div class="checkpoint-header">
        <button class="back-btn" @click="goBack">
          <span class="back-icon">←</span>
        </button>
        <h1 class="checkpoint-title">CHECKPOINT</h1>
        <div class="progress-indicator">
          <span class="progress-text">{{ currentModule }} / {{ totalModules }}</span>
        </div>
      </div>

      <!-- Checkpoint Info -->
      <div class="checkpoint-info">
        <div class="checkpoint-card">
          <div class="checkpoint-icon">
            <span class="icon">🎯</span>
          </div>
          <h2 class="checkpoint-name">{{ checkpointData.title }}</h2>
          <p class="checkpoint-description">{{ checkpointData.description }}</p>
          
          <div class="checkpoint-stats">
            <div class="stat-item">
              <span class="stat-label">Questions</span>
              <span class="stat-value">{{ checkpointData.totalQuestions }}</span>
            </div>
            <div class="stat-item">
              <span class="stat-label">Time Limit</span>
              <span class="stat-value">{{ checkpointData.timeLimit }}</span>
            </div>
            <div class="stat-item">
              <span class="stat-label">Pass Score</span>
              <span class="stat-value">{{ checkpointData.passScore }}%</span>
            </div>
          </div>

          <div class="checkpoint-actions">
            <button 
              class="btn-start-checkpoint" 
              @click="startCheckpoint"
            >
              START CHECKPOINT
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Checkpoint Start Modal -->
    <div class="modal-overlay" v-if="showStartModal" @click="closeStartModal">
      <div class="checkpoint-modal" @click.stop>
        <button class="close-btn" @click="closeStartModal">✕</button>
        
        <div class="modal-header">
          <h2 class="modal-title">CHECKPOINT</h2>
          <h3 class="modal-subtitle">ONBOARDING</h3>
        </div>
        
        <div class="modal-content">
          <p class="modal-text">
            Welcome to your checkpoint assessment. This test will evaluate your understanding 
            of the material covered in this module. You'll need to answer {{ checkpointData.totalQuestions }} 
            questions with a minimum score of {{ checkpointData.passScore }}% to pass.
          </p>
          
          <div class="modal-rules">
            <h4>Test Rules:</h4>
            <ul>
              <li>Time limit: {{ checkpointData.timeLimit }}</li>
              <li>{{ checkpointData.totalQuestions }} multiple choice questions</li>
              <li>Minimum {{ checkpointData.passScore }}% required to pass</li>
              <li>You can retake the test if needed</li>
            </ul>
          </div>
        </div>
        
        <div class="modal-actions">
          <button class="btn-start-test" @click="beginTest">
            START TEST
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// Data
const currentModule = ref(1)
const totalModules = ref(5)
const showStartModal = ref(false)

const checkpointData = ref({
  title: 'Breitling Heritage & Foundations',
  description: 'Test your knowledge of Breitling\'s history, founding principles, and core values that shaped the brand into what it is today.',
  totalQuestions: 15,
  timeLimit: '20 minutes',
  passScore: 70
})

// Methods
const goBack = () => {
  router.push('/learning')
}

const startCheckpoint = () => {
  showStartModal.value = true
}

const closeStartModal = () => {
  showStartModal.value = false
}

const beginTest = () => {
  showStartModal.value = false
  router.push('/checkpoint-quiz')
}

console.log('CheckpointView component loaded')
</script>

<style scoped>
.checkpoint-page {
  min-height: 100vh;
  width: 100%;
  background: linear-gradient(135deg, #072C54 0%, #1e3a8a 100%);
  color: white;
  padding: 1rem;
  padding-bottom: 100px;
  box-sizing: border-box;
}

/* HEADER */
.checkpoint-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 2rem;
  padding: 1rem 0;
}

.back-btn {
  background: rgba(255, 255, 255, 0.1);
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 12px;
  color: white;
  padding: 0.8rem 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 1.2rem;
}

.back-btn:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: translateX(-2px);
}

.checkpoint-title {
  font-size: 2rem;
  font-weight: 700;
  color: #F7C72C;
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 2px;
  text-align: center;
  flex: 1;
}

.progress-indicator {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 20px;
  padding: 0.5rem 1rem;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.progress-text {
  font-size: 0.9rem;
  font-weight: 600;
  color: #F7C72C;
}

/* CHECKPOINT INFO */
.checkpoint-info {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 60vh;
}

.checkpoint-card {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 20px;
  padding: 3rem;
  backdrop-filter: blur(10px);
  border: 2px solid rgba(255, 255, 255, 0.1);
  text-align: center;
  max-width: 600px;
  width: 100%;
}

.checkpoint-icon {
  margin-bottom: 2rem;
}

.icon {
  font-size: 5rem;
  display: inline-block;
  padding: 1.5rem;
  background: #F7C72C;
  border-radius: 50%;
  width: 120px;
  height: 120px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
  box-shadow: 0 8px 25px rgba(247, 199, 44, 0.3);
}

.checkpoint-name {
  font-size: 2.2rem;
  font-weight: 700;
  color: white;
  margin: 0 0 1.5rem 0;
  line-height: 1.2;
}

.checkpoint-description {
  font-size: 1.1rem;
  color: rgba(255, 255, 255, 0.8);
  line-height: 1.6;
  margin-bottom: 3rem;
}

.checkpoint-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1.5rem;
  margin-bottom: 3rem;
}

.stat-item {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 15px;
  padding: 1.5rem;
  text-align: center;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.stat-label {
  display: block;
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.7);
  margin-bottom: 0.8rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stat-value {
  display: block;
  font-size: 1.4rem;
  font-weight: 700;
  color: #F7C72C;
}

.checkpoint-actions {
  margin-top: 2rem;
}

.btn-start-checkpoint {
  background: #F7C72C;
  color: #072C54;
  border: none;
  border-radius: 15px;
  padding: 1.2rem 3rem;
  font-size: 1.2rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 6px 20px rgba(247, 199, 44, 0.3);
}

.btn-start-checkpoint:hover {
  background: #E6B625;
  transform: translateY(-3px);
  box-shadow: 0 8px 25px rgba(247, 199, 44, 0.4);
}

/* MODAL */
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

.checkpoint-modal {
  background: white;
  border-radius: 20px;
  padding: 0;
  max-width: 500px;
  width: 90%;
  position: relative;
  animation: modalSlideIn 0.3s ease-out;
  overflow: hidden;
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: translateY(-50px) scale(0.9);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.close-btn {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: #F7C72C;
  color: #072C54;
  border: none;
  border-radius: 50%;
  width: 35px;
  height: 35px;
  font-size: 1.1rem;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s ease;
  z-index: 10;
}

.close-btn:hover {
  background: #E6B625;
  transform: scale(1.1);
}

.modal-header {
  background: linear-gradient(135deg, #072C54 0%, #1e3a8a 100%);
  color: white;
  padding: 2.5rem 2rem;
  text-align: center;
}

.modal-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: #F7C72C;
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 2px;
}

.modal-subtitle {
  font-size: 1.2rem;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.8);
  margin: 0.5rem 0 0 0;
  text-transform: uppercase;
}

.modal-content {
  padding: 2.5rem 2rem;
  color: #072C54;
}

.modal-text {
  font-size: 1rem;
  line-height: 1.6;
  margin-bottom: 2rem;
  color: #333;
}

.modal-rules {
  background: #f5f5f5;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 2rem;
}

.modal-rules h4 {
  margin: 0 0 1rem 0;
  color: #072C54;
  font-weight: 600;
}

.modal-rules ul {
  margin: 0;
  padding-left: 1.5rem;
}

.modal-rules li {
  margin-bottom: 0.5rem;
  color: #555;
}

.modal-actions {
  padding: 0 2rem 2.5rem 2rem;
  text-align: center;
}

.btn-start-test {
  background: #F7C72C;
  color: #072C54;
  border: none;
  border-radius: 15px;
  padding: 1.2rem 3rem;
  font-size: 1.1rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  cursor: pointer;
  transition: all 0.3s ease;
  width: 100%;
  box-shadow: 0 4px 15px rgba(247, 199, 44, 0.3);
}

.btn-start-test:hover {
  background: #E6B625;
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(247, 199, 44, 0.4);
}

/* RESPONSIVE */
@media (min-width: 768px) {
  .checkpoint-page {
    margin-left: 280px;
    width: calc(100% - 280px);
    padding: 2rem;
    padding-bottom: 2rem;
  }
  
  .checkpoint-title {
    font-size: 2.5rem;
  }
  
  .checkpoint-card {
    padding: 4rem;
  }
  
  .icon {
    width: 140px;
    height: 140px;
    font-size: 5.5rem;
  }
  
  .checkpoint-name {
    font-size: 2.5rem;
  }
  
  .checkpoint-description {
    font-size: 1.2rem;
  }
  
  .stat-item {
    padding: 2rem;
  }
  
  .stat-value {
    font-size: 1.6rem;
  }
  
  .btn-start-checkpoint {
    font-size: 1.3rem;
    padding: 1.4rem 4rem;
  }
}

@media (max-width: 767px) {
  .checkpoint-page {
    margin-left: 0;
    width: 100%;
    padding: 1rem;
    padding-bottom: 80px;
  }
  
  .checkpoint-header {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }
  
  .checkpoint-title {
    font-size: 1.8rem;
  }
  
  .checkpoint-info {
    min-height: auto;
    margin-top: 2rem;
  }
  
  .checkpoint-card {
    padding: 2rem;
  }
  
  .icon {
    width: 100px;
    height: 100px;
    font-size: 4rem;
  }
  
  .checkpoint-name {
    font-size: 1.8rem;
  }
  
  .checkpoint-description {
    font-size: 1rem;
  }
  
  .checkpoint-stats {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .stat-item {
    padding: 1.2rem;
  }
  
  .btn-start-checkpoint {
    font-size: 1rem;
    padding: 1rem 2rem;
  }
}

@media (max-width: 479px) {
  .checkpoint-card {
    padding: 1.5rem;
  }
  
  .checkpoint-name {
    font-size: 1.5rem;
  }
  
  .icon {
    width: 80px;
    height: 80px;
    font-size: 3.5rem;
  }
  
  .modal-content {
    padding: 2rem 1.5rem;
  }
  
  .modal-header {
    padding: 2rem 1.5rem;
  }
  
  .modal-title {
    font-size: 2rem;
  }
}
</style>