// frontend/src/services/api.js
export const BACKEND_URL = 'http://localhost:8000'
const API_BASE_URL = `${BACKEND_URL}/api/v1`
export const SANCTUM_URL = `${BACKEND_URL}/sanctum/csrf-cookie`

export const getCurrentUser = {
  async getCurrentUserId() {
    const res = await fetch(`${BACKEND_URL}/api/user`, {
      credentials: 'include',
      headers: {
        Accept: 'application/json'
      }
    })
    if (!res.ok) {
      throw new Error(`HTTP ${res.status}`)
    }

    return res.json()
  }
}

export const userService = {
  async getUser(id) {
    const response = await fetch(`${API_BASE_URL}/users/${id}`, {
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json'
      }
    })

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`)
    }

    return response.json()
  },

  async getUserRewards(userId) {
    const response = await fetch(`${API_BASE_URL}/user-rewards?user_id=${userId}`, {
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json'
      }
    })

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`)
    }

    return response.json()
  }
}

export const fetchAllUsers = async () => {
  const res = await fetch(`${API_BASE_URL}/users`, {
    credentials: 'include',
    headers: {
      Accept: 'application/json'
    }
  })
  if (!res.ok) throw new Error('Erreur lors du chargement des utilisateurs')

  const data = await res.json()
  return data
}

export const battleService = {
  // Récupérer tous les utilisateurs disponibles pour un battle
  async getAvailableUsers() {
    // Token CSRF comme dans LoginView
    await fetch(`${SANCTUM_URL}`, {
      credentials: 'include'
    })

    const csrfToken = decodeURIComponent(
      document.cookie
        .split('; ')
        .find((row) => row.startsWith('XSRF-TOKEN='))
        ?.split('=')[1] ?? ''
    )

    const response = await fetch(`${API_BASE_URL}/users`, {
      credentials: 'include',
      headers: {
        Accept: 'application/json',
        'X-XSRF-TOKEN': csrfToken
      }
    })

    if (!response.ok) throw new Error('Erreur lors du chargement des utilisateurs')
    return await response.json()
  },

  // Récupérer toutes les questions (utilise ta route existante)
  async getQuestions() {
    await fetch(`${SANCTUM_URL}`, {
      credentials: 'include'
    })

    const csrfToken = decodeURIComponent(
      document.cookie
        .split('; ')
        .find((row) => row.startsWith('XSRF-TOKEN='))
        ?.split('=')[1] ?? ''
    )

    const response = await fetch(`${API_BASE_URL}/questions`, {
      // Utilise ta route existante
      credentials: 'include',
      headers: {
        Accept: 'application/json',
        'X-XSRF-TOKEN': csrfToken
      }
    })

    if (!response.ok) throw new Error('Erreur lors du chargement des questions')
    return await response.json()
  },

  // Récupérer tous les choix (utilise ta route existante)
  async getChoices() {
    await fetch(`${SANCTUM_URL}`, {
      credentials: 'include'
    })

    const csrfToken = decodeURIComponent(
      document.cookie
        .split('; ')
        .find((row) => row.startsWith('XSRF-TOKEN='))
        ?.split('=')[1] ?? ''
    )

    const response = await fetch(`${API_BASE_URL}/choices`, {
      // Récupérer TOUS les choix
      credentials: 'include',
      headers: {
        Accept: 'application/json',
        'X-XSRF-TOKEN': csrfToken
      }
    })

    if (!response.ok) throw new Error('Erreur lors du chargement des choix')
    return await response.json()
  }
}


export const fetchProgression = async (userid) => {
  const progression = await fetch(`${API_BASE_URL}/progression/${userid}`, {})
  const data = await progression.json()
  return data.data
}

export const fetchModule = async (moduleId) => {
  const module = await fetch(`${API_BASE_URL}/modules/${moduleId}`, {})
  const data = await module.json()
  return data.data
}

export const fetchModules = async () => {
  const modules = await fetch(`${API_BASE_URL}/modules`, {})
  const data = await modules.json()
  return data.data
}

// Ajouter ces nouvelles fonctions à la fin du fichier
export const learningService = {
  // Récupérer une théorie avec ses questions
  async getTheory(theoryId) {
    await fetch('http://localhost:8000/sanctum/csrf-cookie', {
      credentials: 'include'
    })

    const csrfToken = decodeURIComponent(
      document.cookie
        .split('; ')
        .find((row) => row.startsWith('XSRF-TOKEN='))
        ?.split('=')[1] ?? ''
    )

    const response = await fetch(`${API_BASE_URL}/theories/${theoryId}`, {
      credentials: 'include',
      headers: {
        Accept: 'application/json',
        'X-XSRF-TOKEN': csrfToken
      }
    })

    if (!response.ok) throw new Error('Erreur lors du chargement de la théorie')
    return await response.json()
  },

  // Récupérer une question avec ses choix
  async getQuestion(questionId) {
    await fetch('http://localhost:8000/sanctum/csrf-cookie', {
      credentials: 'include'
    })

    const csrfToken = decodeURIComponent(
      document.cookie
        .split('; ')
        .find((row) => row.startsWith('XSRF-TOKEN='))
        ?.split('=')[1] ?? ''
    )

    const response = await fetch(`${API_BASE_URL}/questions/${questionId}`, {
      credentials: 'include',
      headers: {
        Accept: 'application/json',
        'X-XSRF-TOKEN': csrfToken
      }
    })

    if (!response.ok) throw new Error('Erreur lors du chargement de la question')
    return await response.json()
  },

  // Récupérer toutes les théories d'une leçon
  async getLessonTheories(lessonId) {
    await fetch('http://localhost:8000/sanctum/csrf-cookie', {
      credentials: 'include'
    })

    const csrfToken = decodeURIComponent(
      document.cookie
        .split('; ')
        .find((row) => row.startsWith('XSRF-TOKEN='))
        ?.split('=')[1] ?? ''
    )

    const response = await fetch(`${API_BASE_URL}/lessons/${lessonId}/theories`, {
      credentials: 'include',
      headers: {
        Accept: 'application/json',
        'X-XSRF-TOKEN': csrfToken
      }
    })

    if (!response.ok) throw new Error('Erreur lors du chargement des théories')
    return await response.json()
  }
}


export const fetchLessons = async () => {
  const lessons = await fetch(`${API_BASE_URL}/lessons`, {})
  const data = await lessons.json()
  return data.data
}

export const fetchLesson = async (lessonId) => {
  const lesson = await fetch(`${API_BASE_URL}/lessons/${lessonId}`, {})
  const data = await lesson.json()

  }
export const fetchQuestions = async () => {
  const questions = await fetch(`${API_BASE_URL}/questions`, {})
  const data = await questions.json()
  return data.data
}

