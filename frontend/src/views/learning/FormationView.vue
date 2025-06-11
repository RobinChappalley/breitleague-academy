<template>
  <div class="formation-image-container">
    <div class="top-action-buttons">
      <RouterLink class="action-btn btn-primary" to="/ressources">Read Ressources</RouterLink>
      <RouterLink class="action-btn btn-primary" to="/missions">Missions</RouterLink>
    </div>
    <div class="progress-bar">
      <ProgressBar />
    </div>

    <div ref="watchContainer" class="formation-watch-container">
      <img
        alt="watch aviators"
        src="/backgrounds/aviators-watch.png"
        class="lesson-watch"
        @load="updateContainerDimensions"
      />

      <!-- Bouton spécial checkpoint -->
      <button class="checkpoint-button special-button" @click="showCheckpointModal"></button>

      <!-- ✅ CORRIGÉ : Div au lieu de RouterLink + appel openStartModal -->
      <div
        v-for="(lesson, index) in lessons"
        :key="index"
        class="lesson-container"
        :style="getButtonPosition(index)"
        @click="openStartModal(lesson)"
      >
        <!-- Cercle de progression pour chaque leçon -->
        <svg class="lesson-progress-circle" width="50" height="50">
          <!-- Cercle de fond -->
          <circle cx="25" cy="25" r="20" fill="none" />
          <!-- Cercle de progression (seulement si en cours) -->
          <circle
            v-if="lesson.status === 'in-progress'"
            cx="25"
            cy="28"
            r="20"
            fill="none"
            stroke="white"
            stroke-width="3"
            :stroke-dasharray="circumference"
            :stroke-dashoffset="getLessonProgressOffset(lesson.progress)"
            stroke-linecap="round"
            class="progress-stroke"
            transform="rotate(-90 25 25)"
          />
        </svg>

        <!-- Bouton de la leçon -->
        <button class="checkpoint-button dynamic-button" :class="getLessonClass(lesson.status)">
          {{ index + 1 }}
        </button>
        <p class="lesson-label">
          {{ lesson.title }}
        </p>
      </div>
    </div>

    <!-- Modal Checkpoint - Affiché par-dessus -->
    <div v-if="isCheckpointModalVisible" class="modal-overlay" @click="closeCheckpointModal">
      <div class="checkpoint-modal" @click.stop>
        <button class="close-btn" @click="closeCheckpointModal">✕</button>

        <div class="modal-header">
          <h2 class="modal-title">CHECKPOINT</h2>
          <h3 class="modal-subtitle">ONBOARDING</h3>
        </div>

        <div class="modal-content">
          <p class="modal-text">
            Welcome to your checkpoint assessment. This test will evaluate your understanding of the
            material covered in this module. You'll need to answer
            {{ checkpointData.totalQuestions }} questions with a minimum score of
            {{ checkpointData.passScore }}% to pass.
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
          <button class="btn-start-test" @click="startCheckpointTest">START TEST</button>
        </div>
      </div>
    </div>

    <!-- ✅ AJOUTÉ : Le composant StartModuleModal -->
    <StartModuleModal
      :isVisible="showStartModal"
      :moduleData="selectedModule"
      @close="handleModalClose"
      @module-started="handleModuleStarted"
    />
  </div>
</template>

<script>
import { fetchProgression, fetchModules } from '@/services/api.js'
import StartModuleModal from './startModuleModal.vue'
import ProgressBar from '@/components/layout/ProgressBar.vue'

export default {
  name: 'FormationView',
  components: {
    StartModuleModal,
    ProgressBar
  },

  data() {
    return {
      lessons: [],
      containerWidth: 0,
      containerHeight: 0,
      isCheckpointModalVisible: false,
      checkpointData: {
        title: 'Breitling Heritage & Foundations',
        description: 'Test your knowledge of Breitling\'s history, founding principles, and core values that shaped the brand into what it is today.',
        totalQuestions: 22,
        timeLimit: '10 minutes',
        passScore: 70
      },
      progression: {},
      showStartModal: false,
      selectedModule: null
    }
  },

  computed: {
    numberOfButtons() {
      return this.lessons.length
    },

    // Circumférence du cercle (rayon = 20, cohérent avec le SVG)
    circumference() {
      return 2 * Math.PI * 20
    }
  },

  // ✅ CORRIGÉ : Try-catch dans mounted() pour éviter les erreurs non gérées
  async mounted() {
    try {
      console.log('🚀 Component mounting...')

      // Utilisation de nextTick pour s'assurer que le DOM est rendu
      await this.$nextTick(() => {
        this.updateContainerDimensions()
        // Garde le resize listener au cas où la fenêtre change
        window.addEventListener('resize', this.updateContainerDimensions)
      })

      console.log('📏 Container dimensions updated')

      // Charge les modules et remplit le tableau lessons dynamiquement !
      const loadedModule = await this.loadModules()
      console.log('📚 Module loaded:', loadedModule)

      // Si tu veux garder le format avec status/progress : adapte ici.
      this.lessons = loadedModule.lessons.map((lesson, idx) => ({
        ...lesson,
        status: 'not-started', // ou récupère depuis ton API ou progression
        progress: 0,
        title: lesson.title || `Lesson ${idx + 1}`
      }))

      console.log('✅ Lessons initialized:', this.lessons.length, 'lessons')
    } catch (error) {
      console.error('❌ Error during component mounting:', error)

      // En cas d'erreur, utilise des lessons par défaut
      console.log('🔄 Loading default lessons as fallback')
      this.lessons = this.getDefaultLessons()
    }
  },

  methods: {
    updateContainerDimensions() {
      const container = this.$refs.watchContainer
      if (container) {
        this.containerWidth = container.offsetWidth
        this.containerHeight = container.offsetHeight
        console.log(`📏 Container: ${this.containerWidth}x${this.containerHeight}`)
      }
    },

    // ✅ NOUVEAU : Méthode pour générer des lessons par défaut
    getDefaultLessons() {
      return [
        {
          id: 'history-lesson-1',
          title: 'Breitling Origins',
          description: 'Learn about the founding of Breitling in 1884',
          estimated_duration: '15-20 min',
          status: 'not-started',
          progress: 0
        },
        {
          id: 'history-lesson-2',
          title: 'Aviation Heritage',
          description: 'Discover our deep connection with aviation',
          estimated_duration: '15-20 min',
          status: 'not-started',
          progress: 0
        },
        {
          id: 'history-lesson-3',
          title: 'Chronograph Innovation',
          description: 'The development of precision timing instruments',
          estimated_duration: '15-20 min',
          status: 'not-started',
          progress: 0
        },
        {
          id: 'history-lesson-4',
          title: 'Iconic Timepieces',
          description: 'Legendary watches that made history',
          estimated_duration: '15-20 min',
          status: 'not-started',
          progress: 0
        },
        {
          id: 'history-lesson-5',
          title: 'Modern Legacy',
          description: 'Breitling in the contemporary era',
          estimated_duration: '15-20 min',
          status: 'not-started',
          progress: 0
        }
      ]
    },

    getButtonPosition(index) {
      if (this.containerWidth === 0) {
        return { display: 'none' }
      }

      const arcDegrees = 150
      const startAngle = 65
      const angleStep = arcDegrees / this.numberOfButtons
      const currentAngle = startAngle - index * angleStep
      const angleRad = (currentAngle * Math.PI) / 180

      const radiusPixels = this.containerWidth
      const centerX = 0
      const centerY = this.containerHeight / 2

      const x = centerX + radiusPixels * Math.cos(angleRad)
      const y = centerY + radiusPixels * Math.sin(angleRad)

      return {
        position: 'absolute',
        left: `${x}px`,
        top: `${y}px`,
        transform: 'translate(-50%, -50%)'
      }
    },

    getLessonProgressOffset(progress) {
      const progressRatio = progress / 100
      return this.circumference * (1 - progressRatio)
    },

    getLessonClass(status) {
      return {
        'lesson-completed': status === 'completed',
        'lesson-in-progress': status === 'in-progress',
        'lesson-not-started': status === 'not-started'
      }
    },

    // Méthodes pour le modal checkpoint
    showCheckpointModal() {
      this.isCheckpointModalVisible = true
    },

    closeCheckpointModal() {
      this.isCheckpointModalVisible = false
    },

    // CORRIGÉ : Redirection vers le quiz
    startCheckpointTest() {
      this.isCheckpointModalVisible = false // Fermer le modal
      this.$router.push('/checkpoint-quiz') // Aller au quiz
    },

    openStartModal(lesson) {
      this.selectedModule = {
        id: lesson.id,
        title: lesson.title,
        description: lesson.description || 'Ready to begin this training lesson?',
        estimated_duration: lesson.estimated_duration || '15-20 min',
        lessons: this.lessons
      }
      this.showStartModal = true
    },

    handleModalClose() {
      this.showStartModal = false
      this.selectedModule = null
    },

    handleModuleStarted(data) {
      console.log('Module started:', data)
      // Redirection vers la première leçon
      this.$router.push(`/LearningFlowView.vue`)
    },

    // ✅ CORRIGÉ : Gestion d'erreur complète + URLs corrigées
    async loadProgression() {
      try {
        console.log('🔄 Loading user progression...')

        // ✅ CORRIGÉ : URL avec préfixe v1
        const res = await fetch('http://localhost:8000/api/v1/users', {
          credentials: 'include',
          headers: {
            Accept: 'application/json'
          }
        })

        if (!res.ok) {
          console.warn(`❌ Users API failed with status: ${res.status}`)
          return null
        }

        const userData = await res.json()
        console.log('✅ User data loaded:', userData)

        // Gère les deux formats possibles de réponse
        const connectedUser = Array.isArray(userData) ? userData[0] : userData

        if (!connectedUser || !connectedUser.id) {
          console.warn('❌ No valid user found:', userData)
          return null
        }

        console.log(`🔄 Loading progression for user ID: ${connectedUser.id}`)
        const progression = await fetchProgression(connectedUser.id)
        console.log('✅ Progression loaded:', progression)

        this.progression = progression
        return progression
      } catch (error) {
        console.error('❌ Error loading progression:', error)
        return null
      }
    },

    async loadModules() {
      try {
        console.log('🔄 Loading modules...')

        const progression = await this.loadProgression()

        // Si pas de progression, utilise les lessons par défaut
        if (!progression || typeof progression.last_checkpoint_id === 'undefined') {
          console.warn('⚠️ No valid progression found, using default lessons')
          return {
            lessons: this.getDefaultLessons()
          }
        }

        const moduleToDisplayId = progression.last_checkpoint_id + 1
        console.log(`🔄 Loading module ID: ${moduleToDisplayId}`)

        const module = await fetchModules(moduleToDisplayId)
        console.log('✅ Module loaded:', module)

        return module
      } catch (error) {
        console.error('❌ Error loading modules:', error)
        console.log('🔄 Falling back to default lessons')

        // Retourner des lessons par défaut en cas d'erreur
        return {
          lessons: this.getDefaultLessons()
        }
      }
    }
  }
}
</script>
<style>
:root {
  --aviators-vertical: url('/backgrounds/aviators-vertical.png');
  --aviators-horizontal: url('/backgrounds/aviators-horizontal.png');
}

.formation-image-container {
  position: relative;
  height: 100vh;
  width: 100vw;
  background-position: right bottom;
  background-repeat: no-repeat;
  background-size: cover;
  display: flex;
  align-items: center;
  @media screen and (max-width: 768px) {
    background-image: var(--aviators-vertical);
  }
  @media screen and (min-width: 768px) {
    background-image: var(--aviators-horizontal);
  }
}

.formation-watch-container {
  position: relative;
  margin-left: 280px;
  @media screen and (max-width: 767px) {
    margin-left: 0;
  }
}

.lesson-watch {
  max-width: 65vw;
  max-height: 85vh;
}

.checkpoint-button {
  height: 3.5em;
  width: 3.5em;
  background-color: goldenrod;
  border-radius: 50%;
  border: none;
  position: absolute;
  cursor: pointer;
  transition: all 0.3s ease;
  top: -2%;
  left: 8%;
  filter: drop-shadow(0 0 4px #000000);
}

.special-button {
  left: 1%;
}

.lesson-container {
  position: relative; /* Changé de absolute à relative */
}

.lesson-progress-circle {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  pointer-events: none;
  z-index: 1;
}

.progress-stroke {
  transition: stroke-dashoffset 0.3s ease-in-out;
}

.lesson-not-started {
  background-color: #808080;
  border-color: #a9a9a9;
  color: white;
}

.lesson-in-progress {
  background-color: #4169e1;
  border-color: white;
  color: white;
}

.lesson-completed {
  background-color: goldenrod;
  border-color: #ffd700;
  color: white;
}

.dynamic-button {
  height: 3em;
  width: 3em;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  transition: all 0.3s ease;
  position: relative;
  z-index: -1;
}

.lesson-container:hover .dynamic-button {
  transform: scale(0.9);
}

.lesson-label {
  position: absolute;
  left: 60px;
  top: 50%;
  transform: translateY(-50%);
  color: whitesmoke;
  font-weight: bold;
}

.action-btn {
  /* styles identiques */
  background: #f7c72c;
  color: #232323;
  font-size: 1rem;
  font-weight: bold;
  border: none;
  outline: none;
  border-radius: 16px;
  padding: 12px 18px;
  box-shadow: 0 3px 12px rgba(35, 35, 35, 0.16);

  cursor: pointer;
  text-align: center;
  text-decoration: none; /* Pour enlever le souligné de lien */
  display: inline-block;
  transition: background 0.2s, box-shadow 0.18s;
}

.action-btn:hover {
  background: #ffd94a;
  box-shadow: 0 6px 24px rgba(35, 35, 35, 0.21);
}

.top-action-buttons {
  position: absolute;
  top: 40px;
  right: 30px;
  display: flex;
  justify-content: center;
  flex-direction: column;
  align-items: flex-end;
  @media screen and (width>= 768px) {
    gap: 18px;
  }
  @media screen and (max-width: 767px) {
    gap: 10px;
  }
}

/* MODAL CHECKPOINT STYLES */
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
  background: #f7c72c;
  color: #072c54;
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
  background: #e6b625;
  transform: scale(1.1);
}

.modal-header {
  background: linear-gradient(135deg, #072c54 0%, #1e3a8a 100%);
  color: white;
  padding: 2.5rem 2rem;
  text-align: center;
}

.modal-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: #f7c72c;
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
  color: #072c54;
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
  color: #072c54;
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
  background: #f7c72c;
  color: #072c54;
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
  background: #e6b625;
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(247, 199, 44, 0.4);
}

/* RESPONSIVE MODAL */
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
