<template>
  <div class="formation-image-container" :style="backgroundStyle">
    <div class="top-action-buttons">
    <div class="progress-bar">
      <ProgressBar />
    </div>
      <!-- Bouton temporaire pour tester -->
      <button class="action-btn" @click="changeModule('next')">Next Module (Test)</button>
      <button class="action-btn" @click="changeModule('previous')">Previous Module (Test)</button>

      <RouterLink class="action-btn" to="/ressources">Read Ressources</RouterLink>
      <RouterLink class="action-btn" to="/missions">Missions</RouterLink>
    </div>

    <div ref="watchContainer" class="formation-watch-container">

      <!-- Image de la montre avec transition -->
      <transition name="watch-slide"  mode="out-in" @after-enter="onWatchTransitionEnd">
        <img
            v-if="currentWatchImage"
            :key="currentWatchImage"
            :alt="`${currentModule?.title || 'Breitling'} watch`"
            :src="currentWatchImage"
            class="lesson-watch"
            @load="updateContainerDimensions"
        >
      </transition>
      <!-- Bouton spécial checkpoint -->
      <transition name="fade">
        <div v-if="showLessonPoints">
          <button
              class="checkpoint-button special-button"
              @click="showCheckpointModal"
          ></button>

          <!-- Boutons de checkpoint avec progression individuelle -->
          <RouterLink
              v-for="(lesson,index) in lessons"
              :key="index"
              :to="`/lesson/${lesson.id}`"
              class="lesson-container"
              :style="getButtonPosition(index)"

          >
            <!-- Cercle de progression pour chaque leçon -->
            <svg class="lesson-progress-circle" width="50" height="50">
              <!-- Cercle de fond -->
              <circle
                  cx="25"
                  cy="25"
                  r="20"
                  fill="none"
              />
              <!-- Cercle de progression (seulement si en cours) -->
              <!--I don't why it works with 28. but that's it! !-->
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
            <button
                class="checkpoint-button dynamic-button"
                :class="getLessonClass(lesson.status)"
            >
              {{ index + 1 }}
            </button>
            <p class="lesson-label">
              {{ lesson.title }}
            </p>
          </RouterLink>
        </div>
      </transition>
=======
        </div>

    <div ref="watchContainer" class="formation-watch-container">
     
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


import { fetchProgression, fetchModule, fetchModules } from '@/services/api.js'
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
      modules: [],
      currentModuleIndex: 0,
      isWatchTransitioning: false,
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
      moduleImages: {
        Onboarding:
            {
              watch: '/backgrounds/aviators-watch.png',
              backgroundMobile: 'backgrounds/aviators-vertical.png',
              backgroundDesktop: 'backgrounds/aviators-horizontal.png'
            },
        Novelties: {
          watch: '/backgrounds/explorators-watch.png',
          backgroundMobile: 'backgrounds/explorators-vertical.png',
          backgroundDesktop: 'backgrounds/explorators-horizontal.png',
        },
        Discovery: {
          watch: '/backgrounds/surfers-watch.png',
          backgroundMobile: 'backgrounds/surfers-vertical.png',
          backgroundDesktop: 'backgrounds/surfers-horizontal.png'
        },
      },
      defaultImages: {
        watch: '/backgrounds/aviators-watch.png',
        backgroundMobile: 'backgrounds/aviators-vertical.png',
        backgroundDesktop: 'backgrounds/aviators-horizontal.png'
      },
      showLessonPoints: true,

      showStartModal: false,
      selectedModule: null
    }
  },

  computed: {
    currentModule() {
      if (!this.modules.length) return null;
      return this.modules[this.currentModuleIndex];
    },
    numberOfButtons() {
      return this.currentModule.lessons?.length
    },

    // Circumférence du cercle (rayon = 20, cohérent avec le SVG)
    circumference() {

      return 2 * Math.PI * 20;
    },
    currentWatchImage() {
      const moduleId = this.currentModule?.title;
      return this.moduleImages[moduleId]?.watch || this.defaultImages.watch;
    },

    currentBackgroundImage() {
      const moduleTitle = this.currentModule?.title;
      const moduleData = this.moduleImages[moduleTitle];

      if (!moduleData) return this.defaultImages.background;

      // Choix responsive
      return window.innerWidth >= 768
          ? moduleData.backgroundDesktop
          : moduleData.backgroundMobile;
    },
    backgroundStyle() {
      return {
        backgroundImage: `url(${this.currentBackgroundImage})`,
        backgroundPosition: 'right bottom',
        backgroundRepeat: 'no-repeat',
        backgroundSize: 'cover'
      };
    }
  },

  // ✅ CORRIGÉ : Try-catch dans mounted() pour éviter les erreurs non gérées
  async mounted() {
    await this.$nextTick(() => {
      this.updateContainerDimensions();
      window.addEventListener('resize', this.updateContainerDimensions);
    });

    // 1. Charger tous les modules (simple)
    await this.loadAllModules();

    // 2. Charger le module spécifique avec ses lessons
    const loadedModule = await this.loadModule();

    // 3. Trouver l'index du module actuel dans la liste
    const moduleIndex = this.modules.findIndex(m => m.id === loadedModule.id);
    if (moduleIndex !== -1) {
      this.currentModuleIndex = moduleIndex;
      // Remplacer le module simple par le module complet avec lessons
      this.modules[moduleIndex] = loadedModule;
    }

    // 4. Mapper les lessons avec status/progress
    this.lessons = loadedModule.lessons.map((lesson, idx) => ({
      ...lesson,
      status: 'not-started',
      progress: 0,
      title: lesson.title || `Lesson ${idx + 1}`
    }));
  }
  ,

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

      //-30 pour que le texte reste dans l'écran sur mobile
      const radiusPixels = this.containerWidth - 30;
      const centerX = 0;
      const centerY = this.containerHeight / 2;


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

    async loadProgression() {
      const res = await fetch('http://localhost:8000/api/user', {
        credentials: 'include',
        headers: {
          Accept: 'application/json'

    handleModuleStarted(data) {
      console.log('Module started:', data)
      // Redirection vers la première leçon
      this.$router.push(`/LearningFlowView.vue`)
    },


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


    async loadModule() {
      const progression = await this.loadProgression()
      const moduleToDisplayId = progression.last_checkpoint_id + 1
      const module = await fetchModule(moduleToDisplayId)
      return module
    },

    async loadAllModules() {
      this.modules = await fetchModules()
    },

    async changeModule(direction = 'next') {
      if (this.isWatchTransitioning) return;
      console.log(this.showLessonPoints);

      this.showLessonPoints = false;
      console.log(this.showLessonPoints);

      console.log('Changing module:', direction);

      // 1. Calculer le prochain index
      let nextIndex;
      if (direction === 'next') {
        nextIndex = (this.currentModuleIndex + 1) % this.modules.length;
      } else {
        nextIndex = this.currentModuleIndex === 0 ? this.modules.length - 1 : this.currentModuleIndex - 1;
      }

      console.log('Current index:', this.currentModuleIndex, 'Next index:', nextIndex);

      // 2. Démarrer la transition
      this.isWatchTransitioning = true;

      // 3. Charger le module complet s'il n'a pas ses lessons
      let newModule = this.modules[nextIndex];
      if (!newModule.lessons || newModule.lessons.length === 0) {
        try {
          newModule = await fetchModule(newModule.id);
          this.modules[nextIndex] = newModule; // Remplacer dans la liste
        } catch (error) {
          console.error('Erreur lors du chargement du module:', error);
          this.isWatchTransitioning = false;
          return;
        }
      }

      // 4. Attendre l'animation, puis changer
      setTimeout(() => {
        this.currentModuleIndex = nextIndex;

        // Mettre à jour les lessons du nouveau module
        this.lessons = newModule.lessons.map((lesson, idx) => ({
          ...lesson,
          status: 'not-started',
          progress: 0,
          title: lesson.title || `Lesson ${idx + 1}`
        }));

        this.isWatchTransitioning = false;
        console.log('Module changed to:', newModule.title);
      }, 400);
    },

    onWatchTransitionEnd() {
      // Affiche les points dès que la transition sortie de la montre est finie
      this.showLessonPoints = true;
    },
  }
}

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
  --explorators-vertical: url('/backgrounds/explorators-vertical.png');
  --explorators-horizontal: url('/backgrounds/explorators-horizontal.png');
  --surfers-horizontal: url('/backgrounds/surfers-horizontal.png');
  --surfers-vertical: url('/backgrounds/surfers-vertical.png');
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
  transition: background-image 1s ease-in-out;
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
  transform-origin: 0 50%; /* Point de rotation : gauche-centre */
  transition: transform 1s ease-in-out, opacity 1s ease-in-out;
}

.lesson-watch.watch-transitioning {
  transform: rotate(180deg);
  opacity: 0.7;
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
  position: relative;
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
  min-width: 150px;
  max-width: 10vw;
  text-align: left;
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
  @media screen and (width >= 768px) {
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

/* Animation pour la montre qui sort : rotation autour du centre + disparition */
.watch-slide-leave-active {
  animation: watch-spin-out 0.3s forwards cubic-bezier(.13, -0.33, .7, 1.2);
  z-index: 2;
}

@keyframes watch-spin-out {
  100% {
    opacity: 0.8;
    transform: rotate(180deg)
  }
}

/* Animation pour la montre qui entre : descend du haut vers son centre */
.watch-slide-enter-active {
  animation: watch-slide-down-in 0.3s forwards cubic-bezier(.13, -0.33, .7, 1.2);
  z-index: 3;
}

@keyframes watch-slide-down-in {

  0% {
    opacity: 0.8;
    transform: rotate(-150deg);
  }

}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

.fade-leave-from, .fade-enter-to {
  opacity: 1;
}

</style>
