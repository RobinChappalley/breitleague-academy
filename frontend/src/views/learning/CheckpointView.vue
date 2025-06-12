<template>
  <div class="checkpoint-page">
    <!-- Checkpoint Start Modal - Affiché directement -->
    <div class="modal-overlay" @click="closeModal">
      <div class="checkpoint-modal" @click.stop>
        <button class="close-btn" @click="closeModal">✕</button>
        
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
const checkpointData = ref({
  title: 'Breitling Heritage & Foundations',
  description: 'Test your knowledge of Breitling\'s history, founding principles, and core values that shaped the brand into what it is today.',
  totalQuestions: 15,
  timeLimit: '10 minutes',
  passScore: 70
})

// Methods
const closeModal = () => {
  router.push('/')
}

const beginTest = () => {
  router.push('/checkpoint-quiz')
}

</script>

<style scoped>
.checkpoint-page {
  min-height: 100vh;
  width: 100%;
  padding: 0;
  margin: 0;
  position: relative;
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
@media (max-width: 767px) {
  .checkpoint-modal {
    width: 95%;
    margin: 1rem;
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

@media (max-width: 479px) {
  .modal-title {
    font-size: 1.8rem;
  }
  
  .modal-subtitle {
    font-size: 1rem;
  }
  
  .modal-text {
    font-size: 0.9rem;
  }
  
  .btn-start-test {
    padding: 1rem 2rem;
    font-size: 1rem;
  }
}
</style>