<template>
  <div class="battle-page">
    <div class="main-content">
      <div class="battle-header">
        <h1 class="battle-title">BATTLES</h1>
        <p class="section-title">THEY CHALLENGED YOU</p>
      </div>

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

        <div class="battle-grid">
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

      <div class="section">
        <h2 class="section-title">FINISHED BATTLES</h2>
        
        <div v-if="finishedBattles.length === 0" class="empty-battles">
          <div class="empty-icon">🎯</div>
          <h3>Aucune bataille terminée</h3>
          <p>Jouez votre première bataille pour voir l'historique ici !</p>
        </div>
        
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
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { battleService } from '@/services/api'

const router = useRouter()

const showInvitationModal = ref(false)
const invitedPlayerName = ref('')

const isLoading = ref(true)
const error = ref('')
const currentUserId = ref(null)

const incomingChallenges = ref([])
const outgoingChallenges = ref([])
const finishedBattles = ref([])
const allUsers = ref([])

const getAvatarUrl = (user) => {
  if (!user || !user.avatar) return null
  return `http://localhost:8000/${user.avatar}`
}

const getBattleStatus = (battle, currentUserId) => {
  const hasChallenger = battle.challenger_summary && 
                       battle.challenger_summary.answers && 
                       battle.challenger_summary.answers.length > 0
  
  const hasChallenged = battle.challenged_summary && 
                       battle.challenged_summary.answers && 
                       battle.challenged_summary.answers.length > 0
  
  if (hasChallenger && hasChallenged) {
    return 'view'
  }
  
  if (currentUserId === battle.challenged_id) {
    if (!hasChallenged) {
      return 'play'
    } else if (!hasChallenger) {
      return 'waiting'
    }
  }
  
  if (currentUserId === battle.challenger_id) {
    if (!hasChallenged) {
      return 'waiting'
    } else if (!hasChallenger) {
      return 'play'
    }
  }
  
  return 'waiting'
}

const loadBattlesFromDB = async () => {
  try {
    isLoading.value = true
    
    const userResponse = await fetch('http://localhost:8000/api/user', {
      credentials: 'include',
      headers: { 'Accept': 'application/json' }
    })
    
    if (userResponse.ok) {
      const userData = await userResponse.json()
      currentUserId.value = userData.id
    }

    const usersData = await battleService.getAvailableUsers()
    const loadedUsers = usersData.data || usersData || []
    allUsers.value = loadedUsers
    
    const incomingBattlesFromDB = await fetch('http://localhost:8000/api/v1/battles', {
      credentials: 'include',
      headers: { 'Accept': 'application/json' }
    })

    const allBattles = await incomingBattlesFromDB.json()

    incomingChallenges.value = (allBattles.data || [])
      .filter(battle => {
        const iAmChallenged = battle.challenged_id === currentUserId.value
        const battleStatus = getBattleStatus(battle, currentUserId.value)
        const notFinished = battleStatus !== 'view'
        
        return iAmChallenged && notFinished
      })
      .map(battle => {
        const battleStatus = getBattleStatus(battle, currentUserId.value)
        
        let displayStatus = 'invitation'
        if (battle.has_challenged_accepted) {
          displayStatus = battleStatus
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

    outgoingChallenges.value = (allBattles.data || [])
      .filter(battle => {
        const iAmChallenger = battle.challenger_id === currentUserId.value
        const battleStatus = getBattleStatus(battle, currentUserId.value)
        const notFinished = battleStatus !== 'view'
        
        return iAmChallenger && notFinished
      })
      .map(battle => {
        const battleStatus = getBattleStatus(battle, currentUserId.value)
        
        let displayStatus = 'waiting'
        if (battle.has_challenged_accepted) {
          displayStatus = battleStatus
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

    await loadFinishedBattles(allBattles.data || [])

  } catch (err) {
    error.value = err.message
    
    incomingChallenges.value = []
    outgoingChallenges.value = []
    finishedBattles.value = []
  } finally {
    isLoading.value = false
  }
}

const loadFinishedBattles = async (battles) => {
  try {
    const isBattleFinished = (battle) => {
      const hasChallenger = battle.challenger_summary && 
                           battle.challenger_summary.answers && 
                           battle.challenger_summary.answers.length > 0
      
      const hasChallenged = battle.challenged_summary && 
                           battle.challenged_summary.answers && 
                           battle.challenged_summary.answers.length > 0
      
      return hasChallenger && hasChallenged
    }
    
    const finishedBattlesList = battles
      .filter(battle => {
        const isFinished = isBattleFinished(battle)
        const userParticipated = battle.challenger_id === currentUserId.value || battle.challenged_id === currentUserId.value
        
        return isFinished && userParticipated
      })
      .map(battle => {
        let opponentUser, playerWon
        
        if (battle.challenger_id === currentUserId.value) {
          opponentUser = battle.challenged || {}
          playerWon = battle.has_challenger_won
        } else {
          opponentUser = battle.challenger || {}
          playerWon = !battle.has_challenger_won
        }
        
        const opponentPos = opponentUser.pos || {}
        
        const battleDate = battle.updated_at || battle.created_at
        const timestamp = new Date(battleDate).getTime()
        
        return {
          id: battle.id,
          name: opponentUser.username || opponentUser.name || `User #${opponentUser.id}`,
          country: opponentPos.country_flag || getCountryCode(opponentUser) || '🇨🇭',
          points: playerWon === true ? +300 : (playerWon === false ? -70 : +100),
          user: opponentUser,
          timestamp: timestamp,
          playerWon: playerWon,
          isDefault: false,
          battleDate: battleDate
        }
      })
      .sort((a, b) => {
        const timestampA = a.timestamp || 0
        const timestampB = b.timestamp || 0
        
        return timestampB - timestampA
      })
      .slice(0, 10)
    
    finishedBattles.value = finishedBattlesList
    
  } catch (error) {
    finishedBattles.value = []
  }
}

const acceptChallenge = async (battleId) => {
  try {
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
      await loadBattlesFromDB()
    }
  } catch (error) {
    console.error('❌ Erreur lors de l\'acceptation:', error)
  }
}

const declineChallenge = async (battleId) => {
  try {
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
      await loadBattlesFromDB()
    }
  } catch (error) {
    console.error('❌ Erreur lors du refus:', error)
  }
}

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
  } else {
    const randomPlayers = ['M.GARCIA', 'T.SMITH', 'A.MILLER', 'S.JONES', 'C.WILSON']
    invitedPlayerName.value = randomPlayers[Math.floor(Math.random() * randomPlayers.length)]
  }
  
  try {
    const questionsData = await battleService.getQuestions()
    const allQuestions = questionsData.data || questionsData || []
    
    if (allQuestions.length === 0) {
      throw new Error('Aucune question disponible')
    }
    
    const shuffled = allQuestions.sort(() => 0.5 - Math.random())
    const selectedQuestions = shuffled.slice(0, 5)
    const questionIds = selectedQuestions.map(q => q.id)
    
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
        questions_id: questionIds
      })
    })
    
    if (response.ok) {
      const result = await response.json()
      showInvitationModal.value = true
    } else {
      const errorText = await response.text()
      alert('Erreur lors de l\'envoi de l\'invitation')
    }
  } catch (error) {
    alert('Erreur lors de l\'envoi de l\'invitation')
  }
}

const closeInvitationModal = () => {
  showInvitationModal.value = false
  
  setTimeout(() => {
    loadBattlesFromDB()
  }, 500)
}

const handleAction = async (challenge) => {
  if (challenge.status === 'play') {
    try {
      const response = await fetch(`http://localhost:8000/api/v1/battles/${challenge.id}`, {
        credentials: 'include',
        headers: { 'Accept': 'application/json' }
      })
      
      if (!response.ok) {
        throw new Error(`Erreur lors du chargement: ${response.status}`)
      }
      
      const battleDetail = await response.json()
      const battle = battleDetail.data || battleDetail
      
      const battleData = {
        id: battle.id,
        opponent: {
          id: challenge.user.id,
          name: challenge.user.username || challenge.name,
          avatar: challenge.user.avatar || null,
          flag: challenge.country || '🇨🇭'
        },
        questions: battle.questions_id || null,
        isFirstPlayer: !battle.challenged_summary,
        existingQuestions: battle.challenged_summary?.questionsData || null
      }
      
      localStorage.setItem('currentBattle', JSON.stringify(battleData))
      
      await router.push('/battle-quiz')
      
    } catch (error) {
      console.error('❌ Erreur:', error)
      alert(`Erreur: ${error.message}`)
    }
    
  } else if (challenge.status === 'waiting') {
    alert('En attente que votre adversaire joue son tour')
    
  } else if (challenge.status === 'view') {
    await router.push(`/battle-details/${challenge.id}`)
  }
}

const getCountryCode = (user) => {
  if (!user) return '🇨🇭'
  
  if (user.pos && user.pos.country_flag) {
    return user.pos.country_flag
  }
  
  if (user.pos_id) {
    const countryMapping = {
      1: '🇨🇭', 2: '🇫🇷', 3: '🇩🇪', 4: '🇮🇹', 5: '🇪🇸', 
      6: '🇵🇹', 7: '🇷🇴', 8: '🇺🇸', 9: '🇬🇧', 10: '🇧🇪'
    }
    return countryMapping[user.pos_id] || '🇨🇭'
  }
  
  return '🇨🇭'
}

const viewBattle = (battleId) => {
  router.push(`/battle-details/${battleId}`)
}

onMounted(async () => {
  await loadBattlesFromDB()
  
  window.addEventListener('focus', refreshAfterBattle)
  
  window.addEventListener('storage', (event) => {
    if (event.key === 'battleCompleted') {
      setTimeout(() => {
        loadBattlesFromDB()
      }, 1000)
    }
  })
})

onUnmounted(() => {
  window.removeEventListener('focus', refreshAfterBattle)
})

const refreshAfterBattle = () => {
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

.main-content {
  width: 100%;
  max-width: 800px;
  margin: 0 auto;
  padding: 0;
}

.battle-title {
  color: #F7C72C;
  margin-top: 1.8rem;
  margin-bottom: 1rem;
  text-transform: uppercase;
  text-align: center;
  letter-spacing: 2px;
}

.section {
  margin-bottom: 4rem;
}

.section-title {
  color: white;
  text-align: left;
}

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
  min-height: 80px;
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
</style>