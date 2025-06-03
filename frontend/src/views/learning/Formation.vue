<template>
  <div class="formation-image-container">
    <div ref="watchContainer" class="formation-watch-container" >
      <img alt="watch aviators" src="/backgrounds/aviators-watch.png" class="lesson-watch">

      <!-- Votre bouton existant (spécial) -->
      <button class="checkpoint-button special-button"></button>

      <!-- Boutons de checkpoint générés dynamiquement -->
      <button
          v-for="(checkpoint, index) in checkpoints"
          :key="index"
          class="checkpoint-button dynamic-button"
          :style="getButtonPosition(index)"
          @click="handleCheckpointClick(index)"
      >
        {{ index + 1 }}
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FormationView',
  data() {
    return {
      checkpoints: [
        { id: 1, completed: false },
        { id: 2, completed: true },
        { id: 3, completed: false },
        { id: 4, completed: false },
        ...(new Array(10)).fill({ id: 5, completed: false }),
      ],
      containerWidth: 0, // Ajouter cette propriété
      containerHeight: 0
    }
  },
  computed: {
    numberOfButtons() {
      return this.checkpoints.length;
    }
  },
  mounted() {
    // Récupérer les dimensions une fois le composant monté
    this.updateContainerDimensions();

    // Optionnel : écouter le redimensionnement de la fenêtre
    window.addEventListener('resize', this.updateContainerDimensions);
  },
  beforeUnmount() {
    // Nettoyer l'event listener
    window.removeEventListener('resize', this.updateContainerDimensions);
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
      // Vérifier que les dimensions sont disponibles
      if (this.containerWidth === 0) {
        return { display: 'none' }; // Cacher temporairement
      }

      // Configuration de l'arc
      const arcDegrees = 140;
      const startAngle = -50

      // Calcul de l'angle pour chaque bouton
      const angleStep = arcDegrees / (this.numberOfButtons);
      const currentAngle = startAngle + (index * angleStep);
      const angleRad = (currentAngle * Math.PI) / 180;

      // Utiliser les dimensions stockées
      const radiusPixels = this.containerWidth;

      // Centre du conteneur
      const centerX = 0
      const centerY = this.containerHeight/2;

      // Calcul des coordonnées du cercle parfait basé sur la largeur
      const x = centerX + radiusPixels * Math.cos(angleRad);
      const y = centerY + radiusPixels * Math.sin(angleRad);

      return {
        position: 'absolute',
        left: `${x}px`,
        top: `${y}px`,
        transform: 'translate(-50%, -50%)'
      };
    },

    handleCheckpointClick(index) {
      console.log(`Checkpoint ${index + 1} clicked`);
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
}

.lesson-watch {
  max-width: 65vw;
  max-height: 85vh;
}

/* Styles communs pour tous les boutons */
.checkpoint-button {
  height: 4em;
  width: 4em;
  background-color: goldenrod;
  border-radius: 50%;
  filter: drop-shadow(0 0 10px black);
  border: none;
  position: absolute;
  cursor: pointer;
  transition: all 0.3s ease;
}

/* Votre bouton spécial existant */
.special-button {
  left: 4%;
  /* Ajoutez des styles spécifiques si nécessaire */
}

/* Boutons dynamiques de checkpoint */
.dynamic-button {
  height: 3em;
  width: 3em;
  border: 2px solid white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  color: white;

  &:hover {
    transform: translate(-50%, -50%) scale(2);
    background-color: #DAA520;
  }
}
</style>
