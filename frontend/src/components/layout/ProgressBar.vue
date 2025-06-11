<template>
  <div class="progress-bar">
    <h3>Progression</h3>
    <div>
      <p>Last lesson ID: {{ progression?.last_lesson_id }}</p>
      <p>Last checkpoint ID: {{ progression?.last_checkpoint_id }}</p>
      <p>
        Number of questions correctly answered:
        {{ progression?.idofquestionscorrectlyanswered.length }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { userService } from '@/services/api'

const progression = ref(null)

onMounted(async () => {
  try {
    const fetchCurrentUser = await fetch('http://localhost:8000/api/user', {
      credentials: 'include',
      headers: {
        Accept: 'application/json'
      }
    })
    const data = await fetchCurrentUser.json()

    const res = await userService.getUser(data.id)
    const dataUser = res.data
    console.log(dataUser)
    progression.value = dataUser.progression
  } catch (error) {
    console.error('Error fetching progression:', error)
  }
})
</script>

<style scoped>
.progress-bar {
  border: 1px solid #fff;
  padding: 10px;
  border-radius: 5px;
  color: #fff;
}
</style>
