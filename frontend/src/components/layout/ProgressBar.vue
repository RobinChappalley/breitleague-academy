<template>
  <div class="bs-progress-container">
    <p class="bs-progress-title">Specialist Progression</p>
    <template v-if="isDataReady">
      <div class="bs-progress-bar">
        <div class="bs-progress-bar-inner" :style="{ width: overallProgress + '%' }"></div>
      </div>
      <p class="bs-progress-percent">{{ overallProgress }}%</p>
    </template>
    <template v-else>
      <div class="loading">
        <div class="loading-spinner">⏳</div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { userService, fetchModules, getCurrentUser } from '@/services/api'
import { watch } from 'vue'

const progression = ref(null)
const modules = ref([])

onMounted(async () => {
  try {
    // récupérer user
    const user = await getCurrentUser.getCurrentUserId()
    console.log('Utilisateur connecté:', user)

    //récupérer progression
    const resUser = await userService.getUser(user.id)
    const dataUser = resUser.data
    progression.value = dataUser.progression

    //récupérer modules avec ta fonction
    const resModules = await fetchModules()
    modules.value = resModules
  } catch (error) {
    console.error('Error fetching data:', error)
  }
})

// Calculs dynamiques
const totalQuestions = computed(() => {
  return modules.value.reduce(
    (sum, module) =>
      sum +
      module.lessons.reduce(
        (lSum, lesson) =>
          lSum + lesson.theories.reduce((tSum, theory) => tSum + theory.questions.length, 0),
        0
      ),
    0
  )
})

const overallProgress = computed(() => {
  if (!progression.value || totalQuestions.value === 0) return 0
  return Math.min(
    (progression.value.idofquestionscorrectlyanswered.length / totalQuestions.value) * 100,
    100
  ).toFixed(0)
})

const isDataReady = computed(() => {
  return progression.value !== null && modules.value.length > 0
})
watch(overallProgress, async (newValue) => {
  if (progression.value) {
    try {
      const user = await getCurrentUser.getCurrentUserId()
      console.log('Utilisateur connecté:', user)

      // récupérer user complet pour vérifier is_BS
      const resUser = await userService.getUser(user.id)
      const dataUser = resUser.data

      // nouvelle valeur de is_BS en fonction de la progression
      const shouldBeBS = parseInt(newValue) === 100

      if (dataUser.is_BS !== shouldBeBS) {
        // faire le PUT pour mettre à jour is_BS
        await fetch(await userService.getUser(user.id), {
          method: 'PUT',
          credentials: 'include',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json'
          },
          body: JSON.stringify({
            is_BS: shouldBeBS
          })
        })

        console.log(`User is_BS updated to ${shouldBeBS}`)
      } else {
        console.log(`User is_BS already correct (${dataUser.is_BS}) → no update needed`)
      }
    } catch (error) {
      console.error('Error updating is_BS:', error)
    }
  }
})
</script>

<style scoped>
.bs-progress-floating {
  position: fixed;
  z-index: 9999; /* pour que ça passe au-dessus de tout */
  top: 20px; /* par défaut (desktop) */
  left: 50%;
  transform: translateX(-50%);
  width: auto;
}

.bs-progress-container {
  position: absolute;
  z-index: 9999;
  top: 40px;
  left: 60%;
  transform: translateX(-50%);
  background: rgba(7, 44, 84, 0.8);
  padding: 0.5rem 1rem;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
  text-align: center;
  color: #f7c72c;
  min-width: 15vw;
}

.bs-progress-title {
  font-size: 0.8rem;
  font-weight: 600;
  margin-bottom: 0.25rem;
  color: #f7c72c;
}

.bs-progress-bar {
  background-color: rgba(255, 255, 255, 0.15);
  border-radius: 10px;
  height: 8px;
  width: 100%;
  overflow: hidden;
  margin: 0.25rem 0;
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.bs-progress-bar-inner {
  height: 100%;
  background: linear-gradient(90deg, #f7c72c, #e6b625);
  transition: width 0.5s ease;
}

.bs-progress-percent {
  font-size: 0.75rem;
  color: #f7c72c;
  margin-top: 0.15rem;
  font-weight: 500;
}

.loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 0.5rem;
  color: rgba(255, 255, 255, 0.8);
}

.loading-spinner {
  font-size: 2rem;
  margin-bottom: 0.5rem;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

/* Responsive - mobile (< 768px) */
@media (max-width: 768px) {
  .bs-progress-container {
    left: 30px;
    transform: none;
    width: auto;
    max-width: 180px;
    padding: 0.4rem 0.6rem;
    border-radius: 8px;
    min-width: unset;
  }

  .bs-progress-title {
    font-size: 0.75rem;
  }

  .bs-progress-bar {
    height: 6px;
    margin: 0.25rem 0;
  }

  .bs-progress-percent {
    font-size: 0.7rem;
  }
}
</style>
