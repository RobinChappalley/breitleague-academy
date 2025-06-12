<template>
  <div class="collection-page">
    <div v-if="isLoading" class="loading-state">Loading...</div>

    <div v-else-if="error" class="error-state text-secondary">❌ {{ error }}</div>

    <div v-else>
      <div class="watch-details" v-if="selectedWatch.id">
        <div class="watch-info">
          <h2 class="title">{{ selectedWatch.model }}</h2>
          <div class="specs">
            <p class="spec-item">Date: {{ selectedWatch.date }}</p>
            <p class="spec-item">Sizes: {{ selectedWatch.size }}</p>
            <p class="spec-item">
              Colors:
              {{
                Array.isArray(selectedWatch.colors)
                  ? selectedWatch.colors.join(', ')
                  : selectedWatch.colors
              }}
            </p>
            <p class="spec-item">Bracelets: {{ selectedWatch.bracelet }}</p>
          </div>
          <p class="description">{{ selectedWatch.description }}</p>
        </div>

        <div class="watch-image">
          <img
            v-if="selectedWatch.photo_name"
            :src="`${backendUrl.replace(/\/$/, '')}/${selectedWatch.photo_name.replace(
              /^\/\//,
              ''
            )}`"
            :alt="selectedWatch.model"
            @load="handleImageLoad"
          />
        </div>
      </div>

      <div class="watches-grid" v-if="watches.length">
        <div
          v-for="watch in watches"
          :key="watch.id"
          class="watch-item"
          @click="selectWatch(watch)"
        >
          <img
            :src="`${backendUrl}/${watch.photo_name}`.replace(/([^:]\/)(\/)+/g, '$1')"
            :alt="watch.model"
          />
          <span
            class="favorite-star"
            :class="{ 'is-favorite': watch.isFavorite }"
            @click.stop="toggleFavorite(watch)"
          >
            ★
          </span>
        </div>
      </div>

      <div v-else class="loading-state text-secondary">
        You haven't earned any rewards yet, so no watches are available.
      </div>
    </div>
  </div>
</template>

<script setup>
import { userService } from '@/services/api'
import { ref, onMounted } from 'vue'

const backendUrl = 'http://localhost:8000'
const watches = ref([])
const selectedWatch = ref({})
const favoriteIds = ref([])
const isLoading = ref(true)
const error = ref(null)

const errorWatches = ref(null)
const errorFavorites = ref(null)

const fetchWatches = async () => {
  try {
    errorWatches.value = null

    const fetchCurrentUser = await fetch('http://localhost:8000/api/user', {
      credentials: 'include',
      headers: {
        Accept: 'application/json'
      }
    })

    const data = await fetchCurrentUser.json()

    const res = await userService.getUser(data.id)
    const dataUser = res.data

    watches.value = dataUser.rewards.map((reward) => ({
      ...reward,
      userRewardId: reward.pivot?.id,
      colors: Array.isArray(reward.colors)
        ? reward.colors
        : reward.colors?.split(',').map((c) => c.trim()) || [],
      isFavorite: reward.pivot?.is_favourite === 1
    }))

    if (watches.value.length > 0) {
      selectedWatch.value = watches.value[0]
    }
  } catch (err) {
    errorWatches.value = 'Error loading watches'
    console.error(err)
  }
}

const fetchFavorites = async () => {
  try {
    errorFavorites.value = null

    const fetchCurrentUser = await fetch('http://localhost:8000/api/user', {
      credentials: 'include',
      headers: {
        Accept: 'application/json'
      }
    })

    const data = await fetchCurrentUser.json()

    const res = await userService.getUser(data.id)
    const dataUser = res.data

    favoriteIds.value = Array.isArray(dataUser.rewards)
      ? dataUser.rewards
          .filter((reward) => reward.pivot?.is_favourite === 1)
          .map((reward) => reward.id)
      : []

    watches.value.forEach((watch) => {
      watch.isFavorite = favoriteIds.value.includes(watch.id)
    })
  } catch (err) {
    // Ici attention : ne pas dire "You haven't earned rewards" par défaut !
    // On distingue "aucun favori" (normal) et "erreur réseau / auth" (anormal)
    errorFavorites.value = 'Error loading favorites'
    favoriteIds.value = []
    console.warn('Missing or empty auth:', err.message)
  }
}

const toggleFavorite = async (watch) => {
  const isCurrentlyFavorite = watch.isFavorite
  const maxFavoritesReached = favoriteIds.value.length >= 3

  if (!isCurrentlyFavorite && maxFavoritesReached) {
    alert('You can only have a maximum of 3 favorite watches.')
    return
  }

  try {
    // On définit bodyData ici
    const bodyData = {
      is_favourite: !isCurrentlyFavorite ? 1 : 0
      // Si besoin, ajoute user_id ici :
      // user_id: currentUserId.value
    }

    //On utilise bodyData dans le fetch
    const response = await fetch(`${backendUrl}/api/v1/user-rewards/${watch.userRewardId}`, {
      method: 'PUT',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(bodyData)
    })

    // On affiche le résultat
    const result = await response.json()
    console.log('PUT result', result)

    if (response.ok) {
      // 4️⃣ On met à jour l'état local
      watch.isFavorite = !isCurrentlyFavorite

      if (isCurrentlyFavorite) {
        favoriteIds.value = favoriteIds.value.filter((id) => id !== watch.id)
      } else {
        favoriteIds.value = [...favoriteIds.value, watch.id]
      }
    } else {
      console.error('Favorites API error', response.status, result)
    }
  } catch (error) {
    console.error('Favorites network error', error)
  }
}

const selectWatch = (watch) => {
  selectedWatch.value = watch
}

const handleImageLoad = (e) => {
  e.target.classList.add('loaded')
}

onMounted(async () => {
  await fetchWatches()
  await fetchFavorites()
  isLoading.value = false
})
</script>

<style scoped>
.collection-page {
  background-color: #0d4f97;
  color: white;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  box-sizing: border-box;
  padding-left: 280px;
  overflow-y: auto; /* autorise le scroll */
}

.watch-details {
  background-color: #072c54;
  padding-left: 1.5rem;
  padding-right: 1.5rem;
  padding-bottom: 15rem;
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  flex: 0 0 auto;
  box-sizing: border-box;
  flex-wrap: wrap; /* autorise wrap */
}

.watch-info {
  flex: 1;
  margin-top: 2rem;
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
}

.title {
  margin-bottom: 3rem;
  text-transform: uppercase;
}

.specs {
  margin-bottom: 1rem;
}

.description {
  margin-top: 1rem;
  color: rgba(255, 255, 255, 0.9);
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
  max-height: 350px;
  max-width: 350px;
  object-fit: contain;
}

.watches-grid {
  background-color: #0d4f97;
  padding: 2rem;
  display: grid;
  flex-grow: 1;
  grid-template-columns: repeat(auto-fit, minmax(170px, auto));
  justify-content: start;
  row-gap: 2rem;
  column-gap: 15rem;
  box-sizing: border-box;
  border-top-left-radius: 12px;
  border-top-right-radius: 12px;
  overflow-y: auto;
  height: auto;
  min-height: 0px;
}

.watch-item {
  position: relative;
  cursor: pointer;
  transition: transform 0.2s;
}

.watch-item img {
  width: clamp(100px, 20vw, 150px);
  height: auto;
  object-fit: contain;
}

.favorite-star {
  position: absolute;
  top: 8px;
  right: 8px;
  color: #222;
  cursor: pointer;
  font-size: 1.5rem;
}

.is-favorite {
  color: #ffd700;
}

.no-watches {
  text-align: center;
  color: white;
  font-style: italic;
  font-size: 1rem;
  grid-column: 1 / -1;
}
.error-state {
  text-align: center;
  color: #ff6b6b;
  padding: 2rem;
}
@media (min-width: 768px) {
  .favorite-star {
    font-size: 3rem;
  }
}

@media (max-width: 768px) {
  .collection-page {
    padding-left: 0;
    padding-top: 1rem;
  }
  .watches-grid {
    column-gap: 2rem; /* réduit sur petits écrans */
    padding: 1rem; /* réduit un peu les bords */
  }
  .watch-details {
    flex-direction: row;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
  }
  .watch-info {
    text-align: left;
    align-items: flex-start;
    flex: 1;
  }
  .title {
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
    max-width: 180 px;
  }
  .watch-image img {
    max-width: 300px;
    height: auto;
  }
}

@media (max-width: 350px) {
  .watch-details {
    flex-direction: column;
    align-items: center;
    padding: 1rem;
  }
  .watches-grid {
    row-gap: 0.5rem;
    column-gap: 1rem;
    padding: 0.5rem;
  }
  .watch-info {
    order: 2;
    padding: 1rem;
    text-align: center;
    align-items: center;
  }
  .watch-image {
    order: 1;
    margin-bottom: 1rem;
  }
  .watch-image img {
    max-width: auto;
    height: 120px;
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
