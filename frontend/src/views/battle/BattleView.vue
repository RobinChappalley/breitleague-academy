<template>
  <div class="battle-page">
    <div class="main-content">
      <!-- Header avec meilleur spacing -->
      <div class="battle-header">
        <h1 class="battle-title">BATTLES</h1>
        <p class="section-title">THEY CHALLENGED YOU</p>
      </div>

      <!-- They Challenged You Section -->
      <div class="section">
        <div class="battle-grid">
          <div 
            v-for="challenge in incomingChallenges" 
            :key="challenge.id"
            class="battle-card"
            :class="{ 'invitation': challenge.status === 'invitation' }"
          >
            <div class="player-info">
              <div class="avatar">
                <img 
                  v-if="(challenge.user && challenge.user.avatar)" 
                  :src="getAvatarUrl(challenge.user)" 
                  :alt="challenge.name"
                  class="avatar-image"
                />
                <span v-else class="avatar-initial">{{ challenge.name.charAt(0) }}</span>
              </div>
              <div class="player-details">
                <h3 class="player-name">{{ challenge.name }}</h3>
                <div class="flag">{{ challenge.country }}</div>
              </div>
            </div>
            
            <div class="time-left">{{ challenge.timeLeft }}</div>
            
            <div class="action-buttons" v-if="challenge.status === 'invitation'">
              <button class="btn-accept" @click="acceptChallenge(challenge.id)">✓</button>
              <button class="btn-decline" @click="declineChallenge(challenge.id)">✗</button>
              <span class="invitation-label">Invitation</span>
            </div>
            <div class="action-buttons" v-else>
              <button 
                class="btn-action"
                :class="challenge.status"
                @click="handleAction(challenge)"
              >
                {{ challenge.status === 'play' ? 'Play' : 'Waiting' }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- You Challenged Them Section -->
      <div class="section">
        <div class="section-header">
          <p class="section-title">YOU CHALLENGED THEM</p>
          <div class="slots-info">
            <span class="slots-counter text-mini">{{ outgoingChallenges.length }}/5 slots used</span>
            <div class="slots-bar">
              <div 
                class="slots-fill" 
                :style="{ width: (outgoingChallenges.length / 5) * 100 + '%' }"
              ></div>
            </div>
          </div>
        </div>

        <!-- EXACTEMENT 5 CARTES : battle-cards + invite-cards -->
        <div class="battle-grid">
          <!-- Battle cards pour les slots occupés -->
          <div 
            v-for="challenge in outgoingChallenges" 
            :key="challenge.id"
            class="battle-card"
          >
            <div class="player-info">
              <div class="avatar">
                <img 
                  v-if="(challenge.user && challenge.user.avatar)" 
                  :src="getAvatarUrl(challenge.user)" 
                  :alt="challenge.name"
                  class="avatar-image"
                />
                <span v-else class="avatar-initial">{{ challenge.name.charAt(0) }}</span>
              </div>
              <div class="player-details">
                <h3 class="player-name">{{ challenge.name }}</h3>
                <div class="flag">{{ challenge.country }}</div>
              </div>
            </div>
            
            <div class="time-left">{{ challenge.timeLeft }}</div>
            
            <div class="action-buttons">
              <button 
                class="btn-action"
                :class="challenge.status"
                @click="handleAction(challenge)"
              >
                {{ challenge.status === 'play' ? 'Play' : 'Waiting' }}
              </button>
            </div>
          </div>

          <!-- Invite cards pour les slots libres (5 - nombre de battles) -->
          <div 
            v-for="n in (5 - outgoingChallenges.length)" 
            :key="'invite-' + n"
            class="invite-card" 
            @click="invitePlayer"
          >
            <div class="invite-icon">👤</div>
            <span>Invite a Player</span>
            <button class="btn-new">New</button>
          </div>
        </div>
      </div>

      <!-- Finished Battles Section -->
      <div class="section">

        <h2 class="section-title">FINISHED BATTLES</h2>
        
        <!-- SI AUCUNE BATAILLE -->
        <div v-if="finishedBattles.length === 0" class="empty-battles">
          <div class="empty-icon">🎯</div>
          <h3>Aucune bataille terminée</h3>
          <p>Jouez votre première bataille pour voir l'historique ici !</p>
        </div>
        
        <!-- SI DES BATAILLES EXISTENT -->
        <div v-else class="battle-grid">
          <div 
            v-for="battle in finishedBattles" 
            :key="battle.id"
            class="battle-card finished"
            :class="{ 'won': battle.points > 0, 'lost': battle.points < 0 }"
          >
            <div class="player-info">
              <div class="avatar">
                <img 
                  v-if="(battle.user && battle.user.avatar)" 
                  :src="getAvatarUrl(battle.user)" 
                  :alt="battle.name"
                  class="avatar-image"
                />
                <span v-else class="avatar-initial">{{ battle.name.charAt(0) }}</span>
              </div>
              <div class="player-details">
                <h3 class="player-name">{{ battle.name }}</h3>
                <div class="flag">{{ battle.country }}</div>
                <!-- Badge pour indiquer que c'est une vraie bataille -->
                <div class="real-battle-badge">Bataille jouée</div>
              </div>
            </div>
            
            <div class="points">
              {{ battle.points > 0 ? '+' : '' }}{{ battle.points }}
            </div>
            
            <div class="action-buttons">
              <button class="btn-view" @click="viewBattle(battle.id)">View</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Invitation Sent Pop-up -->
    <div class="modal-overlay" v-if="showInvitationModal" @click="closeInvitationModal">
      <div class="invitation-modal" @click.stop>
        <button class="close-btn" @click="closeInvitationModal">✕</button>
        
        <div class="modal-icon">
          <div class="icon-circle">
            <span class="check-icon">✓</span>
          </div>
        </div>
        
        <h2 class="modal-title">INVITATION SENT!</h2>
        
        <p class="modal-message">
          Your invitation has been sent to the player. They will receive a notification and can accept when they're ready.
        </p>
        
        <div class="modal-details">
          <div class="invited-player">
            <div class="player-avatar">{{ invitedPlayerName.charAt(0) }}</div>
            <span class="player-name">{{ invitedPlayerName }}</span>
          </div>
        </div>
        
        <div class="modal-actions">
          <button class="btn-ok" @click="closeInvitationModal">
            OK
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { battleService } from '@/services/api'

const router = useRouter()

// Modal state
const showInvitationModal = ref(false)
const invitedPlayerName = ref('')

// Loading state
const isLoading = ref(true)
const error = ref('')
const currentUserId = ref(null)

// Données des battles
const incomingChallenges = ref([])
const outgoingChallenges = ref([]) // CORRIGER ICI - il manquait la parenthèse fermante
const finishedBattles = ref([])
const allUsers = ref([])

// Avatar logic
const getAvatarUrl = (user) => {
  if (!user || !user.avatar) return null
  return `http://localhost:8000/${user.avatar}`
}

// NOUVELLE FONCTION : Déterminer le statut d'une bataille selon les summaries
const getBattleStatus = (battle, currentUserId) => {
  const hasChallenger = battle.challenger_summary && 
                       battle.challenger_summary.answers && 
                       battle.challenger_summary.answers.length > 0
  
  const hasChallenged = battle.challenged_summary && 
                       battle.challenged_summary.answers && 
                       battle.challenged_summary.answers.length > 0
  
  console.log(`🎮 getBattleStatus(${battle.id}):`, {
    currentUserId,
    challenger_id: battle.challenger_id,
    challenged_id: battle.challenged_id,
    hasChallenger,
    hasChallenged
  })
  
  // Bataille terminée - les deux ont joué
  if (hasChallenger && hasChallenged) {
    console.log(`   → Status: VIEW (bataille terminée)`)
    return 'view'
  }
  
  // Je suis le challenged (celui qui a été invité)
  if (currentUserId === battle.challenged_id) {
    if (!hasChallenged) {
      console.log(`   → Status: PLAY (challenged doit jouer en premier)`)
      return 'play' // Mon tour - je n'ai pas encore joué
    } else if (!hasChallenger) {
      console.log(`   → Status: WAITING (challenged a joué, attente du challenger)`)
      return 'waiting' // J'ai joué, attente du challenger
    }
  }
  
  // Je suis le challenger (celui qui a invité)
  if (currentUserId === battle.challenger_id) {
    if (!hasChallenged) {
      console.log(`   → Status: WAITING (attente que le challenged joue en premier)`)
      return 'waiting' // Attente que le challenged joue en premier
    } else if (!hasChallenger) {
      console.log(`   → Status: PLAY (challenged a joué, tour du challenger)`)
      return 'play' // Le challenged a joué, c'est mon tour
    }
  }
  
  console.log(`   → Status: WAITING (défaut)`)
  return 'waiting'
}

// Charger les vraies données depuis la base
const loadBattlesFromDB = async () => {
  try {
    isLoading.value = true
    
    // Récupérer l'utilisateur connecté
    const userResponse = await fetch('http://localhost:8000/api/user', {
      credentials: 'include',
      headers: { 'Accept': 'application/json' }
    })
    
    if (userResponse.ok) {
      const userData = await userResponse.json()
      currentUserId.value = userData.id
      console.log('👤 Current User ID:', currentUserId.value)
    }

    // Charger tous les utilisateurs disponibles
    const usersData = await battleService.getAvailableUsers()
    const loadedUsers = usersData.data || usersData || []
    allUsers.value = loadedUsers
    
    // Récupérer toutes les batailles
    const incomingBattlesFromDB = await fetch('http://localhost:8000/api/v1/battles', {
      credentials: 'include',
      headers: { 'Accept': 'application/json' }
    })

    const allBattles = await incomingBattlesFromDB.json()
    console.log('📋 TOUTES LES BATAILLES depuis API:', allBattles.data)

    // INCOMING = Batailles où JE suis le "challenged" (invitations reçues + en cours de jeu)
    incomingChallenges.value = (allBattles.data || [])
      .filter(battle => {
        const iAmChallenged = battle.challenged_id === currentUserId.value
        const battleStatus = getBattleStatus(battle, currentUserId.value)
        const notFinished = battleStatus !== 'view'
        
        console.log(`🔍 Incoming Bataille ${battle.id}:`, {
          iAmChallenged,
          battleStatus,
          notFinished,
          has_challenged_accepted: battle.has_challenged_accepted
        })
        
        return iAmChallenged && notFinished
      })
      .map(battle => {
        const battleStatus = getBattleStatus(battle, currentUserId.value)
        
        // Déterminer le statut d'affichage
        let displayStatus = 'invitation'
        if (battle.has_challenged_accepted) {
          displayStatus = battleStatus // 'play' ou 'waiting'
        }
        
        return {
          id: battle.id,
          name: battle.challenger.username,
          country: battle.challenger.pos?.country_flag || '🇨🇭',
          timeLeft: '24h left',
          status: displayStatus,
          user: battle.challenger
        }
      })

    console.log('📨 Incoming challenges (reçues):', incomingChallenges.value.length)

    // OUTGOING = Batailles où JE suis le "challenger" (invitations envoyées + en cours de jeu)
    outgoingChallenges.value = (allBattles.data || [])
      .filter(battle => {
        const iAmChallenger = battle.challenger_id === currentUserId.value
        const battleStatus = getBattleStatus(battle, currentUserId.value)
        const notFinished = battleStatus !== 'view'
        
        console.log(`🔍 Outgoing Bataille ${battle.id}:`, {
          iAmChallenger,
          battleStatus,
          notFinished,
          has_challenged_accepted: battle.has_challenged_accepted
        })
        
        return iAmChallenger && notFinished
      })
      .map(battle => {
        const battleStatus = getBattleStatus(battle, currentUserId.value)
        
        // Déterminer le statut d'affichage
        let displayStatus = 'waiting'
        if (battle.has_challenged_accepted) {
          displayStatus = battleStatus // 'play' ou 'waiting'
        }
        
        return {
          id: battle.id,
          name: battle.challenged.username,
          country: battle.challenged.pos?.country_flag || '🇨🇭',
          timeLeft: '24h left',
          status: displayStatus,
          user: battle.challenged
        }
      })

    console.log('📤 Outgoing challenges (envoyées):', outgoingChallenges.value.length)

    // FINISHED BATTLES = Batailles TERMINÉES
    await loadFinishedBattles(allBattles.data || [])

    console.log('✅ Données chargées avec logique corrigée:')
    console.log(`  - Incoming: ${incomingChallenges.value.length}`)
    console.log(`  - Outgoing: ${outgoingChallenges.value.length}`)
    console.log(`  - Finished: ${finishedBattles.value.length}`)

  } catch (err) {
    error.value = err.message
    console.error('❌ Erreur lors du chargement:', err)
    
    incomingChallenges.value = []
    outgoingChallenges.value = []
    finishedBattles.value = []
  } finally {
    isLoading.value = false
  }
}

// CORRIGER loadFinishedBattles() pour trier par date décroissante
const loadFinishedBattles = async (battles) => {
  try {
    console.log('🔄 Filtrage des batailles terminées...')
    
    // HELPER FUNCTION : même logique que dans loadBattlesFromDB
    const isBattleFinished = (battle) => {
      const hasChallenger = battle.challenger_summary && 
                           battle.challenger_summary.answers && 
                           battle.challenger_summary.answers.length > 0
      
      const hasChallenged = battle.challenged_summary && 
                           battle.challenged_summary.answers && 
                           battle.challenged_summary.answers.length > 0
      
      return hasChallenger && hasChallenged
    }
    
    // FILTRER : SEULEMENT les batailles VRAIMENT TERMINÉES
    const finishedBattlesList = battles
      .filter(battle => {
        const isFinished = isBattleFinished(battle)
        const userParticipated = battle.challenger_id === currentUserId.value || battle.challenged_id === currentUserId.value
        
        console.log(`🔍 Finished Bataille ${battle.id}:`)
        console.log(`   - isFinished: ${isFinished}`)
        console.log(`   - userParticipated: ${userParticipated}`)
        console.log(`   - created_at: ${battle.created_at}`)
        console.log(`   - updated_at: ${battle.updated_at}`)
        console.log(`   - RÉSULTAT: ${isFinished && userParticipated}`)
        
        return isFinished && userParticipated
      })
      .map(battle => {
        // Déterminer l'adversaire selon le rôle de l'utilisateur
        let opponentUser, playerWon
        
        if (battle.challenger_id === currentUserId.value) {
          // L'utilisateur est le challenger
          opponentUser = battle.challenged || {}
          playerWon = battle.has_challenger_won
          console.log(`   - JE suis challenger vs ${opponentUser.username}, j'ai ${playerWon ? 'gagné' : 'perdu'}`)
        } else {
          // L'utilisateur est le challenged
          opponentUser = battle.challenger || {}
          playerWon = !battle.has_challenger_won
          console.log(`   - JE suis challenged par ${opponentUser.username}, j'ai ${playerWon ? 'gagné' : 'perdu'}`)
        }
        
        const opponentPos = opponentUser.pos || {}
        
        // AMÉLIORATION : Utiliser updated_at (quand la bataille est terminée) plutôt que created_at
        const battleDate = battle.updated_at || battle.created_at
        const timestamp = new Date(battleDate).getTime()
        
        console.log(`   - Battle date: ${battleDate}, timestamp: ${timestamp}`)
        
        return {
          id: battle.id,
          name: opponentUser.username || opponentUser.name || `User #${opponentUser.id}`,
          country: opponentPos.country_flag || getCountryCode(opponentUser) || '🇨🇭',
          points: playerWon === true ? +300 : (playerWon === false ? -70 : +100),
          user: opponentUser,
          timestamp: timestamp,
          playerWon: playerWon,
          isDefault: false,
          battleDate: battleDate // Garder la date pour debug
        }
      })
      // CORRECTION : Tri par timestamp décroissant (plus récent en premier)
      .sort((a, b) => {
        const timestampA = a.timestamp || 0
        const timestampB = b.timestamp || 0
        
        console.log(`🔄 Tri: Bataille ${a.id} (${timestampA}) vs Bataille ${b.id} (${timestampB})`)
        
        // Décroissant : b - a (plus récent en premier)
        return timestampB - timestampA
      })
      .slice(0, 10) // Garder seulement les 10 plus récentes
    
    finishedBattles.value = finishedBattlesList
    
    console.log('✅ Batailles terminées chargées et triées:', finishedBattles.value.length)
    console.log('📅 Ordre des batailles (plus récente en premier):')
    finishedBattles.value.forEach((battle, index) => {
      console.log(`   ${index + 1}. Bataille ${battle.id} - ${battle.name} - ${battle.battleDate}`)
    })
    
  } catch (error) {
    console.error('❌ Erreur lors du chargement des batailles terminées:', error)
    finishedBattles.value = []
  }
}

// CORRIGER acceptChallenge() pour bien mettre à jour le statut
const acceptChallenge = async (battleId) => {
  try {
    console.log('✅ Acceptation de la bataille:', battleId)
    
    const response = await fetch(`http://localhost:8000/api/v1/battles/${battleId}/status`, {
      method: 'PATCH',
      credentials: 'include',
      headers: { 
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        action: 'accept',
        user_id: currentUserId.value
      })
    })
    
    if (response.ok) {
      console.log('✅ Bataille acceptée avec succès')
      // Recharger les données pour mettre à jour les listes
      await loadBattlesFromDB()
    } else {
      console.error('❌ Erreur lors de l\'acceptation:', response.status)
    }
  } catch (error) {
    console.error('❌ Erreur lors de l\'acceptation:', error)
  }
}

// CORRIGER declineChallenge() pour appeler l'API
const declineChallenge = async (battleId) => {
  try {
    console.log('❌ Refus de la bataille:', battleId)
    
    const response = await fetch(`http://localhost:8000/api/v1/battles/${battleId}/status`, {
      method: 'PATCH',
      credentials: 'include',
      headers: { 
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        action: 'decline',
        user_id: currentUserId.value
      })
    })
    
    if (response.ok) {
      console.log('✅ Bataille refusée avec succès')
      // Recharger les données pour mettre à jour les listes
      await loadBattlesFromDB()
    } else {
      console.error('❌ Erreur lors du refus:', response.status)
    }
  } catch (error) {
    console.error('❌ Erreur lors du refus:', error)
  }
}

// CORRIGER invitePlayer() pour ne PAS recharger immédiatement ET fermer le modal
const invitePlayer = async () => {
  if (outgoingChallenges.value.length >= 5) {
    alert('🚫 All slots are full!')
    return
  }
  
  const availableUsers = allUsers.value.filter(user => 
    user.id !== currentUserId.value &&
    !incomingChallenges.value.some(c => c.user?.id === user.id) &&
    !outgoingChallenges.value.some(c => c.user?.id === user.id)
  )
  
  let selectedUser = null
  
  if (availableUsers.length > 0) {
    selectedUser = availableUsers[Math.floor(Math.random() * availableUsers.length)]
    invitedPlayerName.value = selectedUser.username
    console.log('🎲 Random user selected:', selectedUser.username)
  } else {
    const randomPlayers = ['M.GARCIA', 'T.SMITH', 'A.MILLER', 'S.JONES', 'C.WILSON']
    invitedPlayerName.value = randomPlayers[Math.floor(Math.random() * randomPlayers.length)]
    console.log('🎲 Fallback to mock user:', invitedPlayerName.value)
  }
  
  try {
    console.log('📤 Envoi de l\'invitation avec questions fixes...')
    
    // NOUVEAU : Récupérer 5 questions aléatoirement pour cette bataille
    const questionsData = await battleService.getQuestions()
    const allQuestions = questionsData.data || questionsData || []
    
    if (allQuestions.length === 0) {
      throw new Error('Aucune question disponible')
    }
    
    // Sélectionner 5 questions aléatoirement
    const shuffled = allQuestions.sort(() => 0.5 - Math.random())
    const selectedQuestions = shuffled.slice(0, 5)
    const questionIds = selectedQuestions.map(q => q.id)
    
    console.log('🎲 Questions sélectionnées pour cette bataille:', questionIds)
    
    const response = await fetch('http://localhost:8000/api/v1/battles', {
      method: 'POST',
      credentials: 'include',
      headers: { 
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        challenger_id: currentUserId.value,
        challenged_id: selectedUser ? selectedUser.id : Math.floor(Math.random() * 10) + 1,
        has_challenger_accepted: true,
        has_challenged_accepted: false,
        questions_id: questionIds // NOUVEAU : Questions fixes pour cette bataille
      })
    })
    
    if (response.ok) {
      const result = await response.json()
      console.log('✅ Invitation envoyée avec questions fixes:', result)
      
      showInvitationModal.value = true
      
    } else {
      const errorText = await response.text()
      console.error('❌ Erreur lors de l\'envoi:', errorText)
      alert('Erreur lors de l\'envoi de l\'invitation')
    }
  } catch (error) {
    console.error('❌ Erreur lors de l\'invitation:', error)
    alert('Erreur lors de l\'envoi de l\'invitation')
  }
}

// CORRIGER closeInvitationModal() pour bien fermer
const closeInvitationModal = () => {
  console.log('❌ Fermeture du modal')
  showInvitationModal.value = false
  
  // Recharger les données maintenant pour voir la nouvelle invitation
  setTimeout(() => {
    loadBattlesFromDB()
  }, 500)
}

// AJOUTER/CORRIGER la fonction handleAction pour lancer le quiz
const handleAction = async (challenge) => {
  console.log('🎮 Handle action for challenge:', challenge)
  
  if (challenge.status === 'play') {
    console.log('🚀 Lancement du battle quiz...')
    
    try {
      // Récupérer les données complètes de la bataille
      const response = await fetch(`http://localhost:8000/api/v1/battles/${challenge.id}`, {
        credentials: 'include',
        headers: { 'Accept': 'application/json' }
      })
      
      if (!response.ok) {
        throw new Error(`Erreur lors du chargement: ${response.status}`)
      }
      
      const battleDetail = await response.json()
      const battle = battleDetail.data || battleDetail
      
      console.log('📋 Bataille chargée pour le quiz:', battle)
      
      // Préparer les données pour BattleQuizView
      const battleData = {
        id: battle.id,
        opponent: {
          id: challenge.user.id,
          name: challenge.user.username || challenge.name,
          avatar: challenge.user.avatar || null,
          flag: challenge.country || '🇨🇭'
        },
        questions: battle.questions_id || null,
        isFirstPlayer: !battle.challenged_summary, // Si pas de challenged_summary, je suis le premier à jouer
        existingQuestions: battle.challenged_summary?.questionsData || null // Questions déjà jouées par l'autre
      }
      
      console.log('💾 Données préparées pour le quiz:', battleData)
      
      // Sauvegarder pour BattleQuizView
      localStorage.setItem('currentBattle', JSON.stringify(battleData))
      
      // Rediriger vers le quiz
      await router.push('/battle-quiz')
      
    } catch (error) {
      console.error('❌ Erreur:', error)
      alert(`Erreur: ${error.message}`)
    }
    
  } else if (challenge.status === 'waiting') {
    console.log('⏳ En attente de l\'adversaire')
    alert('En attente que votre adversaire joue son tour')
    
  } else if (challenge.status === 'view') {
    console.log('👀 Voir les résultats')
    await router.push(`/battle-details/${challenge.id}`)
  }
}

// AJOUTER getCountryCode si elle n'existe pas
const getCountryCode = (user) => {
  if (!user) return '🇨🇭'
  
  // 1. Essayer depuis pos.country_flag
  if (user.pos && user.pos.country_flag) {
    return user.pos.country_flag
  }
  
  // 2. Fallback sur pos_id
  if (user.pos_id) {
    const countryMapping = {
      1: '🇨🇭', 2: '🇫🇷', 3: '🇩🇪', 4: '🇮🇹', 5: '🇪🇸', 
      6: '🇵🇹', 7: '🇷🇴', 8: '🇺🇸', 9: '🇬🇧', 10: '🇧🇪'
    }
    return countryMapping[user.pos_id] || '🇨🇭'
  }
  
  return '🇨🇭'
}

// CORRIGER viewBattle pour finished battles
const viewBattle = (battleId) => {
  console.log('🔍 Viewing battle:', battleId)
  router.push(`/battle-details/${battleId}`)
}

// Loading initial data
onMounted(async () => {
  console.log('🚀 BattleView mounted')
  
  // Toujours charger au montage
  await loadBattlesFromDB()
  
  // Écouter les changements de route et de focus
  window.addEventListener('focus', refreshAfterBattle)
  
  // NOUVEAU : Écouter les changements de localStorage (quand bataille terminée)
  window.addEventListener('storage', (event) => {
    if (event.key === 'battleCompleted') {
      console.log('🔄 Bataille terminée détectée, rechargement...')
      setTimeout(() => {
        loadBattlesFromDB()
      }, 1000)
    }
  })
})

onUnmounted(() => {
  window.removeEventListener('focus', refreshAfterBattle)
})

// AMÉLIORER refreshAfterBattle
const refreshAfterBattle = () => {
  console.log('🔄 Rechargement automatique après bataille terminée...')
  
  // Forcer un rechargement complet
  loadBattlesFromDB()
}
</script>

<style scoped>
.battle-page {
  min-height: 100vh;
  width: 100%;
  background: linear-gradient(135deg, #072C54 0%, #1e3a8a 100%);
  color: white;
  overflow-x: hidden;
  margin: 0;
  padding: 1rem;
  padding-bottom: 100px;
  box-sizing: border-box;
}

/* MAIN CONTENT */
.main-content {
  width: 100%;
  max-width: 800px; /* Plus étroit pour une liste */
  margin: 0 auto;
  padding: 0;
}

/* HEADER */

.battle-title {
  color: #F7C72C;
  margin-top: 1.8rem;
  margin-bottom: 1rem;
  text-transform: uppercase;
  text-align: center;
  letter-spacing: 2px;
 
}

/* SECTIONS */
.section {
  margin-bottom: 4rem;
}

.section-title {
  color: white;
  text-align: left;
}

/* HEADER SECTION - NOUVEAU */
.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.slots-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.slots-counter {
  font-size: 0.9rem;
  color: #F7C72C;
  font-weight: 600;
  white-space: nowrap;
}

.slots-bar {
  width: 100px;
  height: 6px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 3px;
  overflow: hidden;
}

.slots-fill {
  height: 100%;
  background: linear-gradient(90deg, #4CAF50 0%, #F7C72C 70%, #F44336 100%);
  transition: width 0.3s ease;
  border-radius: 3px;
}

/* LISTES - TOUJOURS VERTICALES */
.battle-grid,
.invite-grid {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  width: 100%;
}

.invite-grid {
  margin-top: 1.5rem;
}

/* BATTLE CARDS */
.battle-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: rgba(255, 255, 255, 0.1);
  padding: 1.2rem;
  border-radius: 12px;
  backdrop-filter: blur(10px);
  transition: all 0.3s ease;
  border: 1px solid rgba(255, 255, 255, 0.1);
  width: 100%;
  box-sizing: border-box;
  min-height: 80px;
  gap: 1rem;
}

.battle-card:hover {
  background: rgba(255, 255, 255, 0.15);
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
}

.battle-card.finished.won {
  background: rgba(76, 175, 80, 0.15);
  border-left: 4px solid #4CAF50;
}

.battle-card.finished.lost {
  background: rgba(244, 67, 54, 0.15);
  border-left: 4px solid #F44336;
}

.battle-card.invitation {
  border-left: 4px solid #F7C72C;
  background: rgba(247, 199, 44, 0.1);
}

/* PLAYER INFO */
.player-info {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex: 1;
  min-width: 0;
}

.avatar {
  width: 45px;
  height: 45px;
  border-radius: 50%;
  background: #F7C72C;
  color: #072C54;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 1.1rem;
  box-shadow: 0 4px 12px rgba(247, 199, 44, 0.3);
  flex-shrink: 0;
  overflow: hidden;
}

.avatar-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: top;
  border-radius: 50%;
}

.avatar-initial {
  font-weight: bold;
  color: #072C54;
  text-transform: uppercase;
}

.player-details {
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
  min-width: 0;
  flex: 1;
}

.player-name {
  font-size: 1rem;
  font-weight: 600;
  color: white;
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.flag {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.7);
  font-weight: 500;
}

/* TIME AND POINTS */
.time-left,
.points {
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.8);
  text-align: center;
  font-weight: 500;
  white-space: nowrap;
  margin: 0 1rem;
}

.points {
  font-weight: 700;
  font-size: 1rem;
}

/* ACTION BUTTONS */
.action-buttons {
  display: flex;
  align-items: center;
  gap: 0.8rem;
  flex-shrink: 0;
}

.btn-action,
.btn-view,
.btn-new {
  padding: 0.6rem 1.2rem;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-size: 0.8rem;
  white-space: nowrap;
}

.btn-action.play,
.btn-view,
.btn-new {
  background: #F7C72C;
  color: #072C54;
}

.btn-action.play:hover,
.btn-view:hover,
.btn-new:hover {
  background: #E6B625;
  transform: translateY(-1px);
}

.btn-action.waiting {
  background: rgba(255, 255, 255, 0.2);
  color: white;
  cursor: not-allowed;
}

.btn-accept,
.btn-decline {
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 50%;
  font-size: 0.9rem;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s ease;
  flex-shrink: 0;
}

.btn-accept {
  background: #4CAF50;
  color: white;
}

.btn-accept:hover {
  background: #45A049;
  transform: scale(1.1);
}

.btn-decline {
  background: #F44336;
  color: white;
}

.btn-decline:hover {
  background: #DA190B;
  transform: scale(1.1);
}

.invitation-label {
  font-size: 0.7rem;
  color: #F7C72C;
  margin-left: 0.5rem;
  white-space: nowrap;
}

/* INVITE CARDS */
.invite-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: rgba(255, 255, 255, 0.05);
  padding: 1.2rem;
  border-radius: 12px;
  border: 2px dashed rgba(255, 255, 255, 0.3);
  transition: all 0.3s ease;
  cursor: pointer;
  width: 100%;
  box-sizing: border-box;
  min-height: 80px; /* Même hauteur que battle-card */
  gap: 1rem;
}

.invite-card:hover {
  border-color: #F7C72C;
  background: rgba(247, 199, 44, 0.05);
  transform: translateY(-2px);
}

.invite-icon {
  font-size: 1.2rem;
  color: #F7C72C;
}

/* MODAL - Reste identique */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  backdrop-filter: blur(5px);
  padding: 1rem;
  box-sizing: border-box;
}

.invitation-modal {
  background: linear-gradient(135deg, #1e3a8a 0%, #072C54 100%);
  border-radius: 20px;
  padding: 2rem;
  max-width: 400px;
  width: 100%;
  position: relative;
  color: white;
  border: 2px solid #F7C72C;
  text-align: center;
  animation: modalSlideIn 0.3s ease-out;
  box-sizing: border-box;
}

/* Styles modal - identiques */
@keyframes modalSlideIn {
  from { opacity: 0; transform: translateY(-50px) scale(0.9); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}

.close-btn {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: #F7C72C;
  color: #072C54;
  border: none;
  border-radius: 50%;
  width: 30px;
  height: 30px;
  font-size: 1rem;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s ease;
}

.close-btn:hover { background: #E6B625; transform: scale(1.1); }
.modal-icon { margin-bottom: 1.5rem; }
.icon-circle {
  background: #4CAF50;
  border-radius: 50%;
  width: 80px;
  height: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
  animation: checkPulse 0.6s ease-out;
}
@keyframes checkPulse {
  0% { transform: scale(0); }
  50% { transform: scale(1.1); }
  100% { transform: scale(1); }
}
.check-icon { font-size: 2.5rem; color: white; font-weight: bold; }
.modal-title {
  font-size: 1.8rem;
  font-weight: 700;
  color: #F7C72C;
  margin: 0 0 1rem 0;
  text-transform: uppercase;
  letter-spacing: 1px;
}
.modal-message {
  font-size: 1rem;
  color: rgba(255, 255, 255, 0.9);
  margin-bottom: 2rem;
  line-height: 1.5;
}
.modal-details { margin-bottom: 2rem; }
.invited-player {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
  padding: 1rem;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 12px;
}
.player-avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: #F7C72C;
  color: #072C54;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 1.2rem;
}
.invited-player .player-name {
  font-size: 1.1rem;
  font-weight: 600;
  color: white;
}
.modal-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
}
.btn-ok {
  padding: 0.8rem 1.5rem;
  border: none;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 0.9rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  background: #F7C72C;
  color: #072C54;
}
.btn-ok:hover {
  background: #E6B625;
  transform: translateY(-2px);
}

/* FINISHED BATTLES - NOUVEAUX STYLES */
.empty-battles {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.05);
  margin-top: 1rem;
}

.empty-icon {
  font-size: 3rem;
  color: #F7C72C;
  margin-bottom: 1rem;
}

.real-battle-badge {
  display: inline-block;
  background: rgba(76, 175, 80, 0.15);
  color: #4CAF50;
  padding: 0.3rem 0.6rem;
  border-radius: 12px;
  font-size: 0.8rem;
  font-weight: 500;
  margin-top: 0.5rem;
  white-space: nowrap;
}

/* RESPONSIVE - CORRECTION POUR 768px */

/* MOBILE (767px et moins) */
@media (max-width: 768px) {
  .battle-page {
    margin-left: 0;
    width: 100%;
    padding: 1rem;
    padding-bottom: 80px;
  }
  
  .main-content {
    max-width: 100%;
  }
  
  
  
  .battle-card {
    padding: 1rem;
    min-height: 70px;
    gap: 0.8rem;
  }
  
  .avatar {
    width: 40px;
    height: 40px;
    font-size: 1rem;
  }
  
  .player-name {
    font-size: 0.9rem;
  }
  
  .flag {
    font-size: 0.7rem;
  }
  
  .time-left,
  .points {
    font-size: 0.8rem;
    margin: 0 0.5rem;
  }
  
  .btn-action,
  .btn-view,
  .btn-new {
    padding: 0.5rem 1rem;
    font-size: 0.7rem;
  }
  
  .btn-accept,
  .btn-decline {
    width: 28px;
    height: 28px;
    font-size: 0.8rem;
  }
  
}

/* PETIT TABLET (768px à 1023px) - NOUVEAU BREAKPOINT */
@media (min-width: 769px) and (max-width: 1023px) {
  .battle-page {
    margin-left: 280px;
    width: calc(100% - 280px);
    padding: 1.5rem;
    padding-bottom: 2rem;
  }
  
  .main-content {
    max-width: 700px;
    padding: 0 0.5rem;
  }
  
  
  .section {
    margin-bottom: 2.5rem;
  }
  
  
  .battle-card {
    padding: 1.3rem;
    min-height: 75px;
  }
  
  .avatar {
    width: 45px;
    height: 45px;
    font-size: 1.1rem;
  }
  
  .player-name {
    font-size: 0.95rem;
  }
  
  .flag {
    font-size: 0.75rem;
  }
  
  .time-left,
  .points {
    font-size: 0.85rem;
    margin: 0 0.8rem;
  }
  
  .btn-action,
  .btn-view,
  .btn-new {
    padding: 0.6rem 1.1rem;
    font-size: 0.75rem;
  }
  
  .btn-accept,
  .btn-decline {
    width: 30px;
    height: 30px;
    font-size: 0.85rem;
  }
  
}

/* DESKTOP (1024px et plus) */
@media (min-width: 1024px) {
  .battle-page {
    margin-left: 280px;
    width: calc(100% - 280px);
    padding: 2rem;
    padding-bottom: 2rem;
  }
  
  .main-content {
    max-width: 900px;

  }
  
  
  .section {
    margin-bottom: 3.5rem;
  }

  
  .battle-card {
    padding: 1.5rem;
    min-height: 85px;
  }
  
  .avatar {
    width: 50px;
    height: 50px;
    font-size: 1.2rem;
  }
  
  .player-name {
    font-size: 1.1rem;
  }
  
  .flag {
    font-size: 0.8rem;
  }
  
  .time-left,
  .points {
    font-size: 1rem;
    margin: 0 1rem;
  }
  
  .btn-action,
  .btn-view,
  .btn-new {
    padding: 0.7rem 1.3rem;
    font-size: 0.8rem;
  }
  
  .btn-accept,
  .btn-decline {
    width: 32px;
    height: 32px;
    font-size: 0.9rem;
  }
  
}

/* TRÈS PETIT MOBILE (479px et moins) - AMÉLIORÉ */
@media (max-width: 479px) {
  .battle-page {
    padding: 1rem;
    padding-bottom: 80px;
  }
  
  .section {
    margin-bottom: 2rem;
  }
  
  
  .battle-card {
    padding: 0.8rem;
    min-height: 60px;
    gap: 0.6rem;
    /* Garder le layout horizontal mais optimisé */
    flex-direction: row;
    align-items: center;
  }
  
  .player-info {
    gap: 0.5rem;
    flex: 1;
    min-width: 0;
  }
  
  .avatar {
    width: 30px;
    height: 30px;
    font-size: 0.8rem;
    flex-shrink: 0;
  }
  
  .player-details {
    gap: 0.1rem;
    flex: 1;
    min-width: 0;
  }
  
  .player-name {
    font-size: 0.75rem;
    line-height: 1.1;
  }
  
  .flag {
    font-size: 0.55rem;
  }
  
  .time-left,
  .points {
    font-size: 0.65rem;
    margin: 0;
    flex-shrink: 0;
    min-width: 40px;
  }
  
  .action-buttons {
    gap: 0.3rem;
    flex-shrink: 0;
  }
  
  .btn-action,
  .btn-view,
  .btn-new {
    padding: 0.3rem 0.6rem;
    font-size: 0.6rem;
    border-radius: 6px;
  }
  
  .btn-accept,
  .btn-decline {
    width: 20px;
    height: 20px;
    font-size: 0.6rem;
  }
  
  
  /* Cards d'invitation */
  .invite-card {
    padding: 0.8rem;
    gap: 0.5rem;
  }
  
  .invite-icon {
    font-size: 1rem;
  }
  
  /* Modal responsive */
  .invitation-modal {
    padding: 1.5rem;
    margin: 0.5rem;
  }
  
  .modal-title {
    font-size: 1.2rem;
  }
  
  .modal-message {
    font-size: 0.8rem;
  }
  
  .player-avatar {
    width: 40px;
    height: 40px;
    font-size: 1rem;
  }
  
  .invited-player .player-name {
    font-size: 0.9rem;
  }
  
  .btn-ok {
    padding: 0.6rem 1.2rem;
    font-size: 0.8rem;
  }
}

/* ULTRA PETIT MOBILE (380px et moins) - NOUVEAU */
@media (max-width: 380px) {
  .battle-page {
    padding: 0.4rem;
  }
  
  
  .battle-card {
    padding: 0.6rem;
    min-height: 55px;
    gap: 0.4rem;
  }
  
  .avatar {
    width: 28px;
    height: 28px;
    font-size: 0.7rem;
  }
  
  .player-name {
    font-size: 0.7rem;
  }
  
  .flag {
    font-size: 0.5rem;
  }
  
  .time-left,
  .points {
    font-size: 0.6rem;
    min-width: 35px;
  }
  
  .btn-action,
  .btn-view,
  .btn-new {
    padding: 0.25rem 0.5rem;
    font-size: 0.55rem;
  }
  
  .btn-accept,
  .btn-decline {
    width: 18px;
    height: 18px;
    font-size: 0.55rem;
  }
  
  .invite-card {
    padding: 0.6rem;
  }
  
  .invite-icon {
    font-size: 0.9rem;
  }
}
</style>