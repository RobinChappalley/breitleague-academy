<template>
  <div class="collection-page">



      <div class="watch-details" v-if="selectedWatch.id">
        <div class="watch-info">
          <h1 class="title">{{ selectedWatch.model }}</h1>
          

          <div class="specs">
            <div class="spec-item">Date : {{ selectedWatch.date }}</div>
            <div class="spec-item">Sizes : {{ selectedWatch.size }}</div>
            <div class="spec-item">Colors : {{ selectedWatch.colors.join(', ') }}</div>
            <div class="spec-item">Bracelets : {{ selectedWatch.bracelet }}</div>
          </div>

          <p class="description">{{ selectedWatch.description }}</p>
        </div>

        <div class="watch-image">
          <img
            v-if="selectedWatch.photo_name"
            src='/images/hero/breitling-vintage-watches.jpg'
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



   const fetchWatches = () => {
  isLoading.value = true
  try {
    watches.value = [
      {
        "id": 1,
        "photo_name": "/images/hero/breitling-vintage-watches.jpg",
        "model": "Top Time Limited Edition",
        "date": "2020",
        "size": "41 mm",
        "colors": ["Silver Opalin", "Bronze"],
        "bracelet": "22/20mm",
        "description": "The Breitling Top Time Limited Edition is a modern reinterpretation..."
      },
      
    ]

    // 👉 Sélectionne automatiquement la première montre
    if (watches.value.length > 0) {
      selectedWatch.value = watches.value[0]
    }

  } catch (err) {
    error.value = 'Failed to load watches'
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
  overflow: hidden;
  padding-left: 280px; /* desktop */
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
  padding-right: 2rem;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.title {
  font-size: 2.5rem;
  color: white;
  margin-bottom: 3rem;
  text-transform: uppercase;
  font-weight: 700;
}

.specs {
  margin-bottom: 1rem;
  font-size: 1rem;
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
  flex: 0 0 40vh;
  padding: 2rem;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  gap: 1rem;
  box-sizing: border-box;
  border-top-left-radius: 12px;
  border-top-right-radius: 12px;
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
  font-size: 1.4rem;
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

@media (max-width: 768px) {
  .collection-page {
    padding-left: 0; /* mobile */
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
    font-size: 1.6rem;
    text-align: center;
    margin-bottom: 1rem;
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
    padding: 0;
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
    font-size: 0.9rem;
    padding: 0 1rem;
    text-align: center;
  }

  .title {
    font-size: 1.4rem;
    margin-bottom: 0.6rem;
  }
}


</style>
