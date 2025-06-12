<template>
  <div v-if="isVisible" class="modal-overlay" @click="closeModal">
    <div class="start-module-modal" @click.stop>
      <button class="close-btn" @click="closeModal">✕</button>
      
      <div class="modal-header">
        <p class="modal-title text-secondary">ONBOARDING</p>
        <h2 class="modal-subtitle">HISTORY</h2>
      </div>
      
      <div class="modal-content">
        <div class="module-description">
          <p>
            Discover the rich heritage of Breitling, from Georges Breitling's founding vision in 1884 to becoming 
            the premier aviation chronograph specialist. 
          </p>
        </div>
        
        <div class="module-summary">
          <h3>What you'll learn:</h3>
          <ul>
            <li class="text-secondary">Breitling's founding story and early innovations</li>
            <li class="text-secondary">The development of aviation chronographs</li>
            <li class="text-secondary">Key partnerships with aviation pioneers</li>
            <li class="text-secondary">Iconic timepieces and their historical significance</li>
            <li class="text-secondary">The brand's evolution and modern legacy</li>
          </ul>
        </div>
      </div>

      <div class="modal-actions">
        <button class="btn-start-learning btn-primary" @click="startModule">
          START LEARNING
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import {useRouter} from "vue-router";

const router = useRouter()
export default {
  name: 'StartModuleModal',
  props: {
    isVisible: {
      type: Boolean,
      default: false
    },
    moduleData: {
      type: Object,
      default: () => ({})
    }
  },
  
  emits: ['close', 'module-started'],
  
  methods: {
    closeModal() {
      this.$emit('close');
    },
    
    startModule() {
      this.$emit('module-started', {
        moduleId: 'history',
        moduleTitle: 'Breitling History',
        firstLessonId: 'history-lesson-1',
        totalLessons: 5
      });
      
      this.closeModal();
      const firstLessonId = this.moduleData.id
      this.$router.push('/lesson/${firstLessonId}')
    }
  }
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  backdrop-filter: blur(3px);
}

.start-module-modal {
  background: #f5f5f5;
  border-radius: 20px;
  padding: 0;
  max-width: 420px;
  width: 90%;
  max-height: 85vh; 
  position: relative;
  animation: modalSlideIn 0.3s ease-out;
  overflow: hidden;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
  display: flex; 
  flex-direction: column; 
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: translateY(-30px) scale(0.95);
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
  width: 40px;
  height: 40px;
  cursor: pointer;
  transition: all 0.3s ease;
  z-index: 10;
  display: flex;
  align-items: center;
  justify-content: center;
}

.close-btn:hover {
  background: #E6B625;
  transform: scale(1.05);
}

.modal-header {
  background: linear-gradient(135deg, #072C54 0%, #1e3a8a 100%);
  color: white;
  padding: 2.5rem 2rem 2rem 2rem; 
  text-align: left;
  flex-shrink: 0; 
}

.modal-title {
  color: white;
  margin: 0 0 0.5rem 0;
  text-transform: uppercase;
  letter-spacing: 1px;
  opacity: 0.9;
}

.modal-subtitle {
  color: white;
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 2px;
  line-height: 1;
}

.modal-content {
  padding: 1.5rem 2rem; 
  color: #333;
  overflow-y: auto; 
  flex: 1; 
  max-height: calc(85vh - 180px);
}

.module-description {
  margin-bottom: 1.5rem; 
}

.module-description p {
  color: #333;
  margin: 0;
}

.module-summary {
  background: white;
  border-radius: 12px;
  padding: 1.3rem; 
  margin-bottom: 0; 
  border-left: 4px solid #F7C72C;
}

.module-summary h3 {
  color: #072C54;
  margin: 0 0 0.8rem 0; 
}

.module-summary ul {
  margin: 0;
  padding-left: 1.2rem;
}

.module-summary li {
  color: #555;
  margin-bottom: 0.4rem; 
  line-height: 1.4;
}

.modal-actions {
  padding: 1.2rem 2rem 1.5rem 2rem; 
  background: #f5f5f5;
  flex-shrink: 0; 
  border-radius: 0 0 20px 20px; 
}

.btn-start-learning {
  background: #F7C72C;
  color: #072C54;
  border: none;
  border-radius: 15px;
  padding: 1.2rem 2rem;
  cursor: pointer;
  transition: all 0.3s ease;
  width: 100%;
  box-shadow: 0 4px 15px rgba(247, 199, 44, 0.3);
  letter-spacing: 1px;
}

.btn-start-learning:hover {
  background: #E6B625;
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(247, 199, 44, 0.4);
}

/* RESPONSIVE */
@media (max-width: 767px) {
  .start-module-modal {
    width: 95%;
    margin: 1rem;
    max-height: 90vh; 
  }
  
  .modal-header {
    padding: 2rem 1.5rem 1.5rem 1.5rem; 
  }
  
  .modal-content {
    padding: 1.2rem 1.5rem; 
    max-height: calc(90vh - 160px); 
  }
  
  .modal-actions {
    padding: 1rem 1.5rem 1.2rem 1.5rem; 
  }
}

@media (max-width: 479px) {
  .start-module-modal {
    max-height: 95vh; 
  }
  
  .modal-content {
    max-height: calc(95vh - 140px); 
  }
  
  .modal-header {
    padding: 1.8rem 1.5rem 1.2rem 1.5rem; 
  }
}
</style>