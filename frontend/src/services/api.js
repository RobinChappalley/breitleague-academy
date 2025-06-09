// frontend/src/services/api.js
const API_BASE_URL = 'http://localhost:8000/api/v1'

export const userService = {
  async getUser(id) {
    const response = await fetch(`${API_BASE_URL}/users/${id}`, {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
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
        'Accept': 'application/json'
      }
    })
    
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`)
    }
    
    return response.json()
  }
}

export const battleService = {
  // Récupérer tous les utilisateurs disponibles pour un battle
  async getAvailableUsers() {
    // Token CSRF comme dans LoginView
    await fetch('http://localhost:8000/sanctum/csrf-cookie', {
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
        'Accept': 'application/json',
        'X-XSRF-TOKEN': csrfToken
      }
    })
    
    if (!response.ok) throw new Error('Erreur lors du chargement des utilisateurs')
    return await response.json()
  }
}