<template>
  <div class="login-page">
    <div class="login-card">
      <h1>Connexion</h1>
      <form @submit.prevent="login">
        <input v-model="username" placeholder="Nom d'utilisateur" required />
        <input v-model="password" type="password" placeholder="Mot de passe" required />
        <button type="submit">Se connecter</button>
      </form>

      <p v-if="error" class="error">{{ error }}</p>

      <div v-if="currentUser">
        <p>Connecté en tant que {{ currentUser.username }}</p>
        <button @click="logout">Se déconnecter</button>
      </div>
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
      localStorage.removeItem('isLoggedIn')
      return
    }

    const data = await res.json()
    currentUser.value = data
    localStorage.setItem('isLoggedIn', 'true')

    // Si l'utilisateur est connecté → rediriger vers "/"
    router.push('/')
  } catch {
    currentUser.value = null
    localStorage.removeItem('isLoggedIn')
  }
}

const logout = async () => {
  await fetch('http://localhost:8000/logout', {
    method: 'POST',
    credentials: 'include'
  })

  currentUser.value = null
  localStorage.removeItem('isLoggedIn')
  router.push('/login') // On revient sur la page login après déconnexion
}
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background: linear-gradient(135deg, #072c54 0%, #1e3a8a 100%);
  color: white;
  padding: 1rem;
  box-sizing: border-box;
}

.login-card {
  background: rgba(255, 255, 255, 0.1);
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
  max-width: 400px;
  width: 100%;
  text-align: center;
}

.login-card h1 {
  margin-bottom: 1.5rem;
  font-size: 2rem;
  font-weight: 700;
  color: white;
  text-transform: uppercase;
}

.login-card form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.login-card input {
  padding: 0.75rem 1rem;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  background: rgba(255, 255, 255, 0.15);
  color: white;
}

.login-card input::placeholder {
  color: rgba(255, 255, 255, 0.7);
}

.login-card button {
  background: #f7c72c;
  color: #072c54;
  border: none;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  font-size: 1rem;
  transition: background 0.3s ease;
}

.login-card button:hover {
  background: #e6b625;
  transform: translateY(-1px);
}

.login-card .error {
  color: #ff6b6b;
  margin-top: 1rem;
  font-weight: 600;
}

.login-card .link-register {
  display: block;
  margin-top: 1.5rem;
  color: #f7c72c;
  text-decoration: underline;
  font-weight: 600;
}
</style>
