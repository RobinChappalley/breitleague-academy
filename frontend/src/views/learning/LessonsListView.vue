<template>
  <div class="lessons-page">
    <button class="back-arrow" @click="goBack" aria-label="Go back">
      <svg width="36" height="36" viewBox="0 0 24 24" fill="none">
        <path d="M15 19l-7-7 7-7" stroke="#F7C72C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </button>
    
    <!-- Loading state -->
    <div v-if="loading" class="lessons-content">
      <div class="loading">Chargement des leçons...</div>
    </div>

    <!-- Main Content -->
    <div v-else class="lessons-content">
      <div class="lessons-container">
        <h1 class="section-title">LESSONS RESSOURCES</h1>
        
        <!-- Dynamic Modules and Lessons -->
        <div v-for="module in modules" :key="module.id" class="section">
          <h2 class="section-subtitle">{{ module.title.toUpperCase() }}</h2>
          
          <div class="lessons-grid">
            <div 
              v-for="lesson in module.lessons" 
              :key="lesson.id"
              class="lesson-item" 
              @click="openLesson(lesson.id)"
            >
              <p>{{ lesson.title }}</p>
              <button class="read-btn btn-primary">Read</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { fetchModules } from '../../services/api.js'

const router = useRouter()
const modules = ref([])
const loading = ref(true)

const goBack = () => {
  router.back()
}

const loadModulesAndLessons = async () => {
  try {
    loading.value = true
    const data = await fetchModules()
    modules.value = data
  } catch (error) {
    console.error('Erreur lors du chargement des modules:', error)
  } finally {
    loading.value = false
  }
}

const openLesson = (lessonId) => {
  router.push(`/ressources/history`)
}

onMounted(() => {
  loadModulesAndLessons()
})

</script>

<style scoped>
.lessons-page {
  min-height: 100vh;
  width: 100vw;
  background: linear-gradient(135deg, #072C54 0%, #1e3a8a 100%);
  color: white;
  padding: 0;
  margin: 0;
}
p {
  color: #072C54;
}
/* MAIN CONTENT */
.lessons-content {
  padding: 3rem;
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: flex-start;
}

.lessons-container {
  width: 100%;
  max-width: 800px;
  margin: 0 auto;
}

.section-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: #F7C72C;
  margin: 0 0 3rem 0;
  text-transform: uppercase;
  letter-spacing: 2px;
  text-align: center;
}

/* SECTIONS */
.section {
  margin-bottom: 4rem;
}

.section-subtitle {
  font-size: 1.5rem;
  font-weight: 600;
  color: white;
  margin: 0 0 2rem 0;
  text-transform: uppercase;
  letter-spacing: 1px;
}

/* LESSONS GRID */
.lessons-grid {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  width: 100%;
}

.lesson-item {
  background: rgba(255, 255, 255, 0.9);
  border-radius: 12px;
  padding: 1.5rem 2rem;
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 2px solid transparent;
  width: 100%;
}

.lesson-item:hover {
  background: rgba(255, 255, 255, 0.95);
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
}


.read-btn {
  background: #F7C72C;
  color: #072C54;
  border: none;
  padding: 0.7rem 1.5rem;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.3s ease;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  width: 200px
}

.read-btn:hover {
  background: #E6B625;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

/* DESKTOP (768px et plus) */
@media (min-width: 768px) {
  .lessons-page {
    width: 100% ;
    padding: 0;
  }
  
  .lessons-content {
    padding: 2.5rem 2rem;
  }
  
  .section-title {
    font-size: 2.5rem;
  }
}

/* LARGE DESKTOP (1024px et plus) */
@media (min-width: 1024px) {
  .lessons-page {
    width: 100% ;
  }
  
  .lessons-content {
    padding: 3.5rem 4rem;
  }
  
  .section-title {
    font-size: 3rem;
  }
}

/* RESPONSIVE - LARGE DESKTOP */
@media (min-width: 1400px) {
  .lessons-content {
    padding: 4rem 6rem;
  }
  
  .lessons-container {
    max-width: 900px;
  }
  
  .section-title {
    font-size: 3rem;
  }
  
  .lesson-item {
    padding: 2rem 2.5rem;
  }
  
  .lesson-name {
    font-size: 1.3rem;
  }
  
  .read-btn {
    padding: 0.8rem 2rem;
    font-size: 1rem;
  }
}

/* RESPONSIVE - DESKTOP */
@media (min-width: 1200px) and (max-width: 1399px) {
  .lessons-content {
    padding: 3.5rem 4rem;
  }
  
  .section-title {
    font-size: 2.8rem;
  }
  
  .lesson-item {
    padding: 1.8rem 2.2rem;
  }
  
  .lesson-name {
    font-size: 1.2rem;
  }
}

/* RESPONSIVE - TABLET */
@media (min-width: 768px) and (max-width: 1199px) {
  .lessons-content {
    padding: 2.5rem 2rem;
  }
  
  .section-title {
    font-size: 2.2rem;
  }
  
  .lesson-item {
    padding: 1.3rem 1.8rem;
  }
  
  .lesson-name {
    font-size: 1rem;
  }
  
  .read-btn {
    padding: 0.6rem 1.2rem;
    font-size: 0.85rem;
  }
}

/* RESPONSIVE - MOBILE */
@media (max-width: 767px) {
  .lessons-page {
    margin-left: 0;
    width: 100%;
    padding-bottom: 80px; 
  }
  
  .lessons-content {
    padding: 1.5rem 1rem;
  }
  
  .section-title {
    font-size: 1.8rem;
    margin-bottom: 2rem;
  }
  
  .section {
    margin-bottom: 2.5rem;
  }
  
  .section-subtitle {
    font-size: 1.2rem;
    margin-bottom: 1.5rem;
  }
  
  .lesson-item {
    padding: 1rem 1.2rem;
    gap: 1rem;
  }
  
  .lesson-name {
    font-size: 0.9rem;
  }
  
  .read-btn {
    padding: 0.5rem 1rem;
    font-size: 0.8rem;
    width: 100px;
  }
}
.back-arrow {
  position: absolute;
  top: 1.5rem;
  left: 1.5rem;
  z-index: 20;
  background: none;
  border: none;
  cursor: pointer;
  padding: 0.3rem;
  border-radius: 50%;
  transition: background 0.18s, box-shadow 0.18s;
}
.back-arrow:hover {
  background: rgba(247, 199, 44, 0.1);
  box-shadow: 0 1px 4px rgba(33,40,80,0.15);
}

.loading {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 50vh;
  font-size: 1.2rem;
  color: #F7C72C;
}
</style>