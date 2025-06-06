<template>
  <div>
    <h1>Connexion</h1>
    <form @submit.prevent="login">
      <input v-model="username" placeholder="Nom d'utilisateur" required />
      <input v-model="password" type="password" placeholder="Mot de passe" required />
      <button type="submit">Se connecter</button>
    </form>

    <p v-if="error" style="color: red">{{ error }}</p>

    <div v-if="currentUser">
      <p>Connecté en tant que {{ currentUser.username }}</p>
      <button @click="logout">Se déconnecter</button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const username = ref('')
const password = ref('')
const error = ref('')
const currentUser = ref(null)
const router = useRouter()

const login = async () => {
  error.value = ''

  try {
    // Récupérer le token CSRF
    await fetch('http://localhost:8000/sanctum/csrf-cookie', {
      credentials: 'include'
    })

    // Lire le cookie XSRF-TOKEN
    const csrfTokenFromCookie = decodeURIComponent(
      document.cookie
        .split('; ')
        .find((row) => row.startsWith('XSRF-TOKEN='))
        ?.split('=')[1] ?? ''
    )

    // Envoyer le login avec le token CSRF
    const res = await fetch('http://localhost:8000/login', {
      method: 'POST',
      credentials: 'include',
      headers: {
        'Content-Type': 'application/json',
        'X-XSRF-TOKEN': csrfTokenFromCookie
      },
      body: JSON.stringify({
        username: username.value,
        password: password.value
      })
    })

    if (!res.ok) throw new Error("Nom d'utilisateur ou mot de passe invalide")

    // Récupérer l'utilisateur connecté
    await fetchUser()
  } catch (err) {
    error.value = err.message
  }
}

const fetchUser = async () => {
  try {
    const res = await fetch('http://localhost:8000/api/user', {
      credentials: 'include',
      headers: {
        Accept: 'application/json'
      }
    })

    if (!res.ok) {
      currentUser.value = null
      return
    }

    const data = await res.json()
    currentUser.value = data

    // Si l'utilisateur est connecté → rediriger vers "/"
    router.push('/')
  } catch {
    currentUser.value = null
  }
}

const logout = async () => {
  await fetch('http://localhost:8000/logout', {
    method: 'POST',
    credentials: 'include'
  })

  currentUser.value = null
  router.push('/login') // On revient sur la page login après déconnexion
}
</script>
