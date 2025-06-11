<template>
  <div class="progress-bar">
    <h3 class="title">📈 Overall Training Progress</h3>

    <div v-if="progression && modules.length">
      <p>Overall Progress: {{ overallProgress }}%</p>
      <div class="progress-bar-fill">
        <div class="progress-bar-inner" :style="{ width: overallProgress + '%' }"></div>
      </div>
    </div>

    <div v-else>
      <p>Loading progression...</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { userService, fetchAllModules } from '@/services/api'
import { watch } from 'vue'

const progression = ref(null)
const modules = ref([])

onMounted(async () => {
  try {
    // 1️⃣ récupérer user
    const fetchCurrentUser = await fetch('http://localhost:8000/api/user', {
      credentials: 'include',
      headers: {
        Accept: 'application/json'
      }
    })
    const user = await fetchCurrentUser.json()

    // 2️⃣ récupérer progression
    const resUser = await userService.getUser(user.id)
    const dataUser = resUser.data
    progression.value = dataUser.progression

    // 3️⃣ récupérer modules avec ta fonction
    const resModules = await fetchAllModules()
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
watch(overallProgress, async (newValue) => {
  if (progression.value) {
    try {
      // récupérer user.id
      const fetchCurrentUser = await fetch('http://localhost:8000/api/user', {
        credentials: 'include',
        headers: {
          Accept: 'application/json'
        }
      })
      const user = await fetchCurrentUser.json()

      // récupérer user complet pour vérifier is_BS
      const resUser = await userService.getUser(user.id)
      const dataUser = resUser.data

      // nouvelle valeur de is_BS en fonction de la progression
      const shouldBeBS = parseInt(newValue) === 100

      if (dataUser.is_BS !== shouldBeBS) {
        // faire le PUT pour mettre à jour is_BS
        await fetch(`http://localhost:8000/api/v1/users/${user.id}`, {
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

        console.log(`✅ User is_BS updated to ${shouldBeBS}`)
      } else {
        console.log(`ℹ️ User is_BS already correct (${dataUser.is_BS}) → no update needed`)
      }
    } catch (error) {
      console.error('Error updating is_BS:', error)
    }
  }
})
</script>

<style scoped>
.progress-bar {
  border: 2px solid #ccc;
  border-radius: 10px;
  padding: 20px;
  margin-bottom: 20px;
  background: #1f1f1f;
  color: white;
}

.title {
  margin-bottom: 15px;
  font-size: 1.2rem;
}

.progress-bar-fill {
  background-color: #444;
  border-radius: 5px;
  height: 20px;
  width: 100%;
  overflow: hidden;
}

.progress-bar-inner {
  height: 100%;
  background-color: #4caf50;
  transition: width 0.3s ease;
}
</style>
