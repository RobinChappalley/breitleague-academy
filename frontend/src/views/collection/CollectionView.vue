<template>
  <div class="collection-page">



      <div class="watch-details" v-if="selectedWatch.id">
        <div class="watch-info">
          <h1 class="title">{{ selectedWatch.model }}</h1>
          

          <div class="specs">
           <p class="spec-item ">Date : {{ selectedWatch.date }}</p>
           <p class="spec-item">Sizes : {{ selectedWatch.size }}</p>
           <p class="spec-item ">
              Colors : {{ Array.isArray(selectedWatch.colors) ? selectedWatch.colors.join(', ') : selectedWatch.colors }}
           </p>
           <p class="spec-item">Bracelets : {{ selectedWatch.bracelet }}</p>
          </div>


          <p class="description">{{ selectedWatch.description }}</p>
        </div>

        <div class="watch-image">
          <img 
          v-if="selectedWatch.photo_name"
          :src="`/images/${selectedWatch.photo_name}`"
          :alt="selectedWatch.model"
           @load="handleImageLoad"
          >
        </div>
      </div>

      <div class="watches-grid">
        <div
          v-for="watch in watches"
          :key="watch.id"
          class="watch-item"
          @click="selectWatch(watch)"
        >
          <img :src="watch.photo_name" :alt="watch.model" loading="lazy" />
          <span
            class="favorite-star"
            :class="{ 'is-favorite': watch.isFavorite }"
            @click.stop="toggleFavorite(watch)"
          >
            ★
          </span>
        </div>
      </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'

export default {
 
  setup() {
    const isLoading = ref(false)
    const error = ref(null)

    const watches = ref([])
    const selectedWatch = ref({
      id: 0,
      photo_name: '',
      model: '',
      date: '',
      size: '',
      colors: [],
      bracelet: '',
      description: ''
    })

    const selectWatch = (watch) => {
      selectedWatch.value = watch
    }

    const toggleFavorite = (watch) => {
      watch.isFavorite = !watch.isFavorite
    }

    const handleImageLoad = (e) => {
      e.target.classList.add('loaded')
    }
const fetchWatches = async () => {
  isLoading.value = true
  try {
    const response = await fetch('http://localhost:8000/api/v1/rewards')
    const result = await response.json()

    // Forcer un tableau même si le backend renvoie un objet avec "message"
   if (Array.isArray(result.data)) {
  watches.value = result.data.map(watch => ({
  ...watch,
  colors: Array.isArray(watch.colors)
    ? watch.colors
    : watch.colors?.split(',').map(c => c.trim()), // transforme "Red, Blue" en ["Red", "Blue"]
  isFavorite: false
}))

      if (watches.value.length > 0) {
        selectedWatch.value = watches.value[0]
      }
    } else {
      // On affiche rien mais on évite les erreurs fatales
      console.warn('Réponse inattendue :', result)
      watches.value = []
      selectedWatch.value = {
        id: 0,
        photo_name: '',
        model: '',
        date: '',
        size: '',
        colors: [],
        bracelet: '',
        description: ''
      }
    }

  } catch (err) {
    error.value = 'Erreur lors du chargement'
    console.error(err)
  } finally {
    isLoading.value = false
  }
}





    onMounted(() => {
      fetchWatches()
    })

    return {
      isLoading,
      error,
      watches,
      selectedWatch,
      selectWatch,
      toggleFavorite,
      handleImageLoad,
    }
  }
}
</script>
<style scoped>
 .collection-page {
  background-color: #072C54;
  color: white;
  height: 100vh;
  display: flex;
  flex-direction: column;
  box-sizing: border-box;
  padding-left: 280px;
  padding-top: 2rem; /* ✅ empêche le débordement haut */
  overflow-y: auto;  /* ✅ autorise le scroll vertical si nécessaire */
  scroll-padding-top: 2rem;
}



.watch-details {
  background-color: #072C54;
  padding: 2rem;
  display: flex;
  justify-content: space-between;
  flex: 0 0 60vh;
  box-sizing: border-box;
  overflow: auto;
}
.watch-info {
  flex: 1;
  margin-top: 2rem;         /* ✅ Ajoute une marge supérieure */
  display: flex;
  flex-direction: column;
  justify-content: flex-start; /* ✅ Assure que le titre reste en haut */
}



.title {
        /* ↑ Ajoute plus d’espace au-dessus du titre */
  margin-bottom: 3rem;     /* Espace à droite pour éviter que ça colle à l’image */
  text-transform: uppercase;
}


.specs {
  margin-bottom: 1rem;
}

.description {
  margin-top: 1rem;
  color: rgba(255,255,255,0.9);
  line-height: 1.4;
  max-width: 600px;
}

.watch-image {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: center;
}

.watch-image img {
  max-height: 100%;
  max-width: 100%;
  object-fit: contain;
}

.watches-grid {
  background-color: #0D4F97;
  flex: 1;
  padding: 2rem;
  display: grid;
  grid-template-columns: repeat(4, 1fr); /* 4 montres max */
  row-gap: 5rem; /* espace vertical entre les lignes */
  column-gap: 2rem; /* espace horizontal entre les colonnes */
  box-sizing: border-box;
  border-top-left-radius: 12px;
  border-top-right-radius: 12px;
  overflow-y: auto;
  overflow-x: hidden;
  max-height: 40vh;
}




.watch-item {
  position: relative;
  cursor: pointer;
  transition: transform 0.2s;
}

.watch-item:hover {
  transform: scale(1.05);
}

.watch-item img {
  width: 100%;
  height: auto;
  border-radius: 8px;
  background-color: white;
}

.favorite-star {
  position: absolute;
  top: 8px;
  right: 8px;
  color: #222;
  cursor: pointer;
}

.is-favorite {
  color: #FFD700;
}

.loading-state,
.error-state {
  text-align: center;
  padding: 2rem;
  color: white;
}
@media (min-width: 768px) {

  .favorite-star {
    font-size: 3rem; 
  }

}
@media (max-width: 768px) {
  .collection-page {
    padding-left: 0; /* mobile */
    padding-top: 2rem;
  }
  .watch-details {
    flex-direction: row; /* image et texte côte à côte */
    align-items: center;
    padding: 1rem;
    gap: 1rem;
    flex-wrap: wrap;
  }

  .watch-info {
    text-align: left;
    align-items: flex-start;
    flex: 1;
  }

  .title {
    text-align: left;
    margin-bottom: 1rem;
    margin-top: 2rem;
  }
  
  .description {
    position: static;
    margin-top: 1rem;
    padding: 0;
    text-align: left;
  }

  .watch-image {
    flex: 1;
    justify-content: center;
  }

  .watch-image img {
    max-width: 300px;
    height: auto;
    width: auto;
  }
}
@media (max-width: 350px) {
  .watch-details {
    flex-direction: column;
    align-items: center;
    padding: 1rem;
  }

  .watch-info {
    order: 2; /* affiche le texte APRÈS l'image */
    padding: 2rem;
    text-align: center;
    align-items: center;
  }

  .watch-image {
    order: 1; /* image avant le texte */
    margin-bottom: 1rem;
  }

  .watch-image img {
    max-width: 180px;
    height: auto;
  }

  .description {
    margin-top: 1rem;
    padding: 0 1rem;
    text-align: center;
  }

  .title {
    margin-bottom: 0.6rem;
  }
}


</style>
