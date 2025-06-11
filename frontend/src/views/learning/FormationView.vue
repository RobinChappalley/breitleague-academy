<template>
  <div class="formation-image-container" :style="backgroundStyle">
    <div class="top-action-buttons">
      <!-- Bouton temporaire pour tester -->
      <button class="action-btn" @click="changeModule('next')">Next Module (Test)</button>
      <button class="action-btn" @click="changeModule('previous')">Previous Module (Test)</button>

      <RouterLink class="action-btn" to="/ressources">Read Ressources</RouterLink>
      <RouterLink class="action-btn" to="/missions">Missions</RouterLink>
    </div>

    <div ref="watchContainer" class="formation-watch-container">
      <!-- Image de la montre avec transition -->
      <img
          :alt="`${currentModule?.title || 'Breitling'} watch`"
          :src="currentWatchImage"
          class="lesson-watch"
          :class="{ 'watch-transitioning': isWatchTransitioning }"
          @load="updateContainerDimensions"
      >
      <!-- Bouton spécial checkpoint -->
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
          <button class="btn-start-test" @click="startCheckpointTest">
            START TEST
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {fetchProgression, fetchModules, fetchModule} from "@/services/api.js";

export default {
  name: 'FormationView',
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
        totalQuestions: 15,
        timeLimit: '20 minutes',
        passScore: 70
      },
      progression: {},
      moduleImages: {
        Onboarding:
            {
              watch: '/background/aviators-watch.png',
              background: 'var(--aviators-horizontal)'
            },
        Novelties: {
          watch: '/background/explorators-watch.png',
          background: 'var(--aviators-horizontal)'
        },
        Discovery: {
          watch: '/background/surfers-watch.png',
          background: 'var(--aviators-horizontal)'
        },
      },
      defaultImages: {
        watch: '/backgrounds/aviators-watch.png',
        background: 'var(--aviators-horizontal)'
      },
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
      const moduleId = this.currentModule?.id;
      return this.moduleImages[moduleId]?.watch || this.defaultImages.watch;
    },

    currentBackgroundImage() {
      const moduleId = this.currentModule?.id;
      return this.moduleImages[moduleId]?.background || this.defaultImages.background;
    },
    backgroundStyle() {
      return {
        backgroundImage: this.currentBackgroundImage
      };
    }


  },

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
      const container = this.$refs.watchContainer;
      if (container) {
        this.containerWidth = container.offsetWidth;
        this.containerHeight = container.offsetHeight;
      }
    },

    getButtonPosition(index) {
      if (this.containerWidth === 0) {
        return {display: 'none'};
      }

      const arcDegrees = 150;
      const startAngle = 65;
      const angleStep = arcDegrees / this.numberOfButtons;
      const currentAngle = startAngle - (index * angleStep);
      const angleRad = (currentAngle * Math.PI) / 180;

      const radiusPixels = this.containerWidth;
      const centerX = 0;
      const centerY = this.containerHeight / 2;

      const x = centerX + radiusPixels * Math.cos(angleRad);
      const y = centerY + radiusPixels * Math.sin(angleRad);

      return {
        position: 'absolute',
        left: `${x}px`,
        top: `${y}px`,
        transform: 'translate(-50%, -50%)'
      };
    },

    getLessonProgressOffset(progress) {
      const progressRatio = progress / 100;
      return this.circumference * (1 - progressRatio);
    },

    getLessonClass(status) {
      return {
        'lesson-completed': status === 'completed',
        'lesson-in-progress': status === 'in-progress',
        'lesson-not-started': status === 'not-started'
      };
    },

    // Méthodes pour le modal checkpoint
    showCheckpointModal() {
      this.isCheckpointModalVisible = true;
    },

    closeCheckpointModal() {
      this.isCheckpointModalVisible = false;
    },

    // CORRIGÉ : Redirection vers le quiz
    startCheckpointTest() {
      this.isCheckpointModalVisible = false; // Fermer le modal
      this.$router.push('/checkpoint-quiz'); // Aller au quiz
    },

    handleLessonClick(index) {
      const lesson = this.lessons[index];
      console.log(`Leçon ${index + 1} cliquée - Statut: ${lesson.status}, Progrès: ${lesson.progress}%`);

      if (lesson.status === 'not-started') {
        lesson.status = 'in-progress';
        lesson.progress = 25;
      } else if (lesson.status === 'in-progress' && lesson.progress < 100) {
        lesson.progress += 25;
        if (lesson.progress >= 100) {
          lesson.status = 'completed';
          lesson.progress = 100;
        }
      }
    },
    async loadProgression() {
      const res = await fetch('http://localhost:8000/api/user', {
        credentials: 'include',
        headers: {
          Accept: 'application/json'
        }
      })
      if (!res.ok) throw new Error('Unauthenticated user (401)')

      const connectedUser = await res.json()
      const progression = await fetchProgression(connectedUser.id)
      this.progression = progression //pas sûr que ça serve à qqch
      return progression
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

      // 3. Attendre l'animation, puis changer
      setTimeout(() => {
        this.currentModuleIndex = nextIndex;

        // Mettre à jour les lessons du nouveau module
        const newModule = this.modules[nextIndex];
        this.lessons = newModule.lessons.map((lesson, idx) => ({
          ...lesson,
          status: 'not-started',
          progress: 0,
          title: lesson.title || `Lesson ${idx + 1}`
        }));

        this.isWatchTransitioning = false;
        console.log('Module changed to:', newModule.title);
      }, 1000);
    },
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
    margin-left: 0
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
  border-color: #A9A9A9;
  color: white;
}

.lesson-in-progress {
  background-color: #4169E1;
  border-color: white;
  color: white;
}

.lesson-completed {
  background-color: goldenrod;
  border-color: #FFD700;
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
}

.action-btn {
  /* styles identiques */
  background: #F7C72C;
  color: #232323;
  font-size: 1rem;
  font-weight: bold;
  border: none;
  outline: none;
  border-radius: 16px;
  padding: 12px 18px;
  box-shadow: 0 3px 12px rgba(35, 35, 35, 0.16);
  font-family: inherit;
  cursor: pointer;
  text-align: center;
  text-decoration: none; /* Pour enlever le souligné de lien */
  display: inline-block;
  transition: background 0.2s, box-shadow 0.18s;
}

.action-btn:hover {
  background: #FFD94A;
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
    gap: 10px
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
