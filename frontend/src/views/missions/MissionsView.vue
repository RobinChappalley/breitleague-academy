<template>
  <div class="missions-page">
    <div class="main-content">
      <!-- Back Arrow -->
      <button class="back-arrow" @click="goBack" aria-label="Go back">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none">
          <path
            d="M15 19l-7-7 7-7"
            stroke="#F7C72C"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
        </svg>
      </button>
      <!-- Header -->
      <div class="missions-header">
        <h2 class="missions-title">MISSIONS</h2>
      </div>

      <div v-if="successMessage" class="success-message">
        {{ successMessage }}
      </div>

      <!-- Missions List -->
      <div class="missions-container">
        <div v-for="mission in visibleMissions" :key="mission.id" class="mission-card">
          <div class="mission-content">
            <div class="mission-info">
              <h3>{{ mission.description }}</h3>
              <div class="mission-progress">
                <div class="progress-bar" :style="{ width: mission.progress + '%' }"></div>
              </div>
              <div class="progress-text text-mini">{{ mission.progress }}% Complete</div>
            </div>

            <button
              class="reward-button"
              :class="{
                claimable: mission.progress === 100,
                locked: mission.progress < 100
              }"
              :disabled="mission.progress < 100"
              @click="claimReward(mission)"
            >
              <div class="reward-watch">
                <img
                  v-if="mission.reward"
                  :src="`${BACKEND_URL}/${mission.reward.photo_name}`"
                  :alt="mission.reward.model"
                  class="watch-image"
                />
                <div v-else class="watch-icon">⌚</div>
              </div>
              <div class="reward-text btn-primary">Reward</div>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { userService, getCurrentUser, BACKEND_URL } from '@/services/api'
const router = useRouter()

const goBack = () => {
  router.back()
}

const missions = ref([])
const user = ref(null)
const isLoading = ref(false)
const error = ref(null)

const visibleMissions = computed(() => {
  return missions.value.sort((a, b) => {
    if (a.progress === 100 && b.progress !== 100) return -1
    if (a.progress !== 100 && b.progress === 100) return 1
    return b.progress - a.progress
  })
})

const successMessage = ref('')

const claimReward = async (mission) => {
  if (mission.progress === 100 && !mission.completed) {
    try {
      const resAddReward = await fetch(`${BACKEND_URL}/api/v1/user-rewards`, {
        method: 'POST',
        credentials: 'include',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json'
        },
        body: JSON.stringify({
          reward_id: mission.reward.id,
          user_id: user.value.id,
          is_favourite: false,
          acquired_at: new Date().toISOString()
        })
      })

      if (!resAddReward.ok) {
        throw new Error("Erreur lors de l'ajout du reward")
      }

      // DELETE la user-mission
      const resDeleteMission = await fetch(
        `${BACKEND_URL}/api/v1/user-missions/${mission.user_mission_id}`,
        {
          method: 'DELETE',
          credentials: 'include',
          headers: {
            Accept: 'application/json'
          }
        }
      )
      if (!resDeleteMission.ok) {
        throw new Error('Erreur lors de la suppression de la mission')
      }

      // Supprimer mission de l'UI
      missions.value = missions.value.filter((m) => m.user_mission_id !== mission.user_mission_id)

      // Afficher message de succès
      successMessage.value = `The watch "${mission.reward.model}" has been added to your collection !`

      // Effacer le message après 2 secondes
      setTimeout(() => {
        successMessage.value = ''
      }, 2500)
    } catch (error) {
      console.error(' Erreur lors de claimReward:', error)
    }
  }
}

const getAllMissions = async () => {
  try {
    isLoading.value = true
    error.value = null


    // Récupérer l'utilisateur connecté via getCurrentUserId
    const connectedUser = await getCurrentUser.getCurrentUserId()
    console.log('Utilisateur connecté:', connectedUser)

    // Charger infos complètes du user
    const response = await userService.getUser(connectedUser.id)
    user.value = response.data || response

    // Charger les missions
    const userMissions = user.value.missions || []

    // 4️⃣ Pour chaque mission, aller chercher le reward lié + ajouter user_mission_id
    const missionsWithRewards = await Promise.all(
      userMissions.map(async (mission) => {
        try {
          const rewardRes = await fetch(`${BACKEND_URL}/api/v1/rewards/${mission.reward_id}`, {
            credentials: 'include',
            headers: {
              Accept: 'application/json'
            }
          })

          if (!rewardRes.ok) {
            console.warn(`Reward ${mission.reward_id} non trouvé.`)
            return {
              ...mission,
              reward: null,
              progress: mission.pivot.is_completed === 1 ? 100 : 0,
              completed: false,
              user_mission_id: mission.pivot.id
            }
          }

          const rewardData = await rewardRes.json()

          return {
            ...mission,
            reward: rewardData.data,
            progress: mission.pivot.is_completed === 1 ? 100 : 0,
            completed: false,
            user_mission_id: mission.pivot.id
          }
        } catch (err) {
          console.error(`Erreur en récupérant reward ${mission.reward_id}:`, err)
          return {
            ...mission,
            reward: null,
            progress: mission.pivot.is_completed === 1 ? 100 : 0,
            completed: false,
            user_mission_id: mission.pivot.id
          }
        }
      })
    )

    // Stocker missions enrichies
    missions.value = missionsWithRewards
  } catch (err) {
    error.value = err
    console.error('Erreur dans getAllMissions:', err)
  } finally {
    isLoading.value = false
  }
}

onMounted(async () => {
  await getAllMissions()
})
</script>

<style scoped>
.missions-page {
  min-height: 100vh;
  width: 100%;
  background: linear-gradient(135deg, #072c54 0%, #1e3a8a 100%);
  color: white;
  padding: 1rem;
  padding-bottom: 100px;
  box-sizing: border-box;
}

/* MAIN CONTENT */
.main-content {
  padding-top: 2rem;
  width: 100%;
  max-width: 800px;
  margin: 0 auto;
}

/* HEADER */
.missions-header {
  text-align: center;
  margin-bottom: 2rem;
}

.success-message {
  background: rgba(247, 199, 44, 0.1);
  border: 1px solid #f7c72c;
  color: #f7c72c;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  text-align: center;
  font-weight: 600;
  margin-bottom: 1rem;
  transition: all 0.3s ease;
}

/* MISSIONS CONTAINER */
.missions-container {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

/* MISSION CARDS */
.mission-card {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 1.5rem;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
}

.mission-card:hover {
  background: rgba(255, 255, 255, 0.15);
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
}

.mission-content {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

/* MISSION INFO */
.mission-info {
  flex: 1;
}

.mission-progress {
  background: rgba(255, 255, 255, 0.2);
  border-radius: 10px;
  height: 8px;
  margin-bottom: 0.5rem;
  overflow: hidden;
}

.progress-bar {
  height: 100%;
  background: linear-gradient(90deg, #f7c72c 0%, #e6b625 100%);
  border-radius: 10px;
  transition: width 0.3s ease;
}

.progress-text {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.8);
  font-weight: 500;
}

/* REWARD BUTTON */
.reward-button {
  background: none;
  border: none;
  padding: 1rem 1.5rem;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.8rem;
  border-radius: 12px;
  transition: all 0.3s ease;
  min-width: 120px;
}

.reward-button:disabled {
  cursor: not-allowed;
}

/* États du bouton */
.reward-button.locked {
  background: #808080;
  opacity: 0.6;
}

.reward-button.claimable {
  background: #f7c72c;
  animation: glow 2s ease-in-out infinite alternate;
}

.reward-button.claimable:hover {
  background: #e6b625;
  transform: scale(1.05);
}

@keyframes glow {
  from {
    box-shadow: 0 0 8px #f7c72c;
  }
  to {
    box-shadow: 0 0 20px #f7c72c;
  }
}
.watch-image {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  display: block;
  border-radius: 8px;
}

.reward-watch {
  width: 70px;
  height: 70px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.watch-icon {
  font-size: 2.5rem;
  transition: all 0.3s ease;
}

.reward-button.locked .watch-icon {
  color: #555;
}

.reward-button.claimable .watch-icon {
  color: #2c3e50;
  animation: bounce 1.5s ease-in-out infinite;
}

@keyframes bounce {
  0%,
  20%,
  50%,
  80%,
  100% {
    transform: translateY(0);
  }
  40% {
    transform: translateY(-5px);
  }
  60% {
    transform: translateY(-2px);
  }
}

.reward-text {
  font-size: 0.9rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.reward-button.locked .reward-text {
  color: rgba(255, 255, 255, 0.7);
}

.reward-button.claimable .reward-text {
  color: #072c54;
}

/* DESKTOP (768px et plus) */
@media (min-width: 768px) {
  .missions-page {
    width: 100%;
    padding: 2rem;
    padding-bottom: 2rem;
  }

  .main-content {
    max-width: 900px;
    padding: 0 2rem;
  }

  .missions-header {
    margin-bottom: 3rem;
  }

  .missions-container {
    gap: 1.5rem;
  }

  .mission-card {
    padding: 2rem;
  }

  .mission-content {
    gap: 2rem;
  }

  .mission-title {
    font-size: 1.1rem;
    margin-bottom: 1.2rem;
  }

  .mission-progress {
    height: 10px;
    margin-bottom: 0.7rem;
  }

  .progress-text {
    font-size: 0.9rem;
  }

  .reward-button {
    padding: 1.2rem 1.8rem;
    min-width: 140px;
    gap: 1rem;
  }

  .reward-watch {
    width: 90px;
    height: 90px;
  }

  .watch-icon {
    font-size: 3.5rem;
  }

  .reward-text {
    font-size: 1rem;
  }
}

/* LARGE DESKTOP (1024px et plus) */
@media (min-width: 1024px) {
  .missions-page {
    width: 100%;
    padding: 3rem;
  }

  .main-content {
    max-width: 1000px;
    padding: 0 3rem;
  }

  .mission-card {
    padding: 2.5rem;
  }

  .mission-title {
    font-size: 1.2rem;
  }

  .reward-button {
    min-width: 160px;
    padding: 1.5rem 2rem;
  }

  .reward-watch {
    width: 100px;
    height: 100px;
  }

  .watch-icon {
    font-size: 4rem;
  }

  .reward-text {
    font-size: 1.1rem;
  }
}

/* MOBILE (moins de 768px) */
@media (max-width: 767px) {
  .missions-page {
    margin-left: 0;
    width: 100%;
    padding: 1rem;
    padding-bottom: 80px; /* Espace pour navbar mobile (70px + marge) */
  }


  .mission-card {
    padding: 1.2rem;
  }

  .mission-content {
    flex-direction: row;
    align-items: center;
    gap: 1rem;
    text-align: left;
  }

  .mission-info {
    flex: 1;
    order: 1;
  }

  .reward-button {
    order: 2;
    align-self: center;
    padding: 0.8rem 1.2rem;
    min-width: 100px;
    gap: 0.6rem;
    flex-shrink: 0;
  }

  .mission-title {
    font-size: 0.9rem;
    margin-bottom: 0.8rem;
  }

  .mission-progress {
    height: 6px;
  }

  .progress-text {
    font-size: 0.7rem;
  }

  .reward-watch {
    width: 60px;
    height: 60px;
  }

  .watch-icon {
    font-size: 2rem;
  }

  .reward-text {
    font-size: 0.7rem;
  }
}

/* TRÈS PETIT MOBILE (moins de 480px) */
@media (max-width: 479px) {
  .missions-page {
    padding: 0.8rem;
    padding-bottom: 100px;
  }


  .mission-card {
    padding: 1rem;
  }

  .mission-title {
    font-size: 0.8rem;
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
  padding-top: 2rem;
  border-radius: 50%;
  transition: background 0.18s, box-shadow 0.18s;
}
.back-arrow:hover {
  background: rgba(247, 199, 44, 0.1);
  box-shadow: 0 1px 4px rgba(33, 40, 80, 0.15);
}
@media (max-width: 767px) {
  .back-arrow {
    top: 1rem;
    left: 1rem;
  }
}
</style>
