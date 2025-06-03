<template>
  <div class="formation-image-container">
    <div ref="watchContainer" class="formation-watch-container">
      <img alt="watch aviators" src="/backgrounds/aviators-watch.png" class="lesson-watch" @load="updateContainerDimensions" >

      <!-- Votre bouton existant (spécial) -->
      <button class="checkpoint-button special-button"></button>

      <!-- Boutons de checkpoint avec progression individuelle -->
      <div
          v-for="(lesson, index) in lessons"
          :key="index"
          class="lesson-container"
          :style="getButtonPosition(index)"
          @click="handleLessonClick(index)"
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
          {{lesson.title}}
        </p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FormationView',
  data() {
    return {
      lessons: [
        { id: 1, status: 'completed', progress: 100, title: 'Onboarding' },
        { id: 2, status: 'completed', progress: 100, title: 'Onboarding' },
        { id: 3, status: 'in-progress', progress: 65, title: 'Onboarding' },
        { id: 4, status: 'in-progress', progress: 30 },
        { id: 5, status: 'not-started', progress: 0 },
        { id: 6, status: 'not-started', progress: 0 },
        { id: 7, status: 'in-progress', progress: 80 },
        // ...(new Array(7)).fill({ id: 8, status: 'not-started', progress: 0 }),
      ],
      containerWidth: 0,
      containerHeight: 0
    }
  },

  computed: {
    numberOfButtons() {
      return this.lessons.length;
    },

    // Circumférence du cercle (rayon = 20, cohérent avec le SVG)
    circumference() {
      return 2 * Math.PI * 20;
    }
  },

  mounted() {
    // Utilisation de nextTick pour s'assurer que le DOM est rendu
    this.$nextTick(() => {
      this.updateContainerDimensions();
      // Garde le resize listener au cas où la fenêtre change
      window.addEventListener('resize', this.updateContainerDimensions);
    });
  },

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
        return { display: 'none' };
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
  @media screen and (max-width: 768px) {
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
  top:-2%;
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
}
</style>
