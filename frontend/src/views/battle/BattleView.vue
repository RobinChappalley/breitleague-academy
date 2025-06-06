<template>
  <div class="battle-page">
    <div class="main-content">
      <!-- Header avec meilleur spacing -->
      <div class="battle-header">
        <h1 class="battle-title">BATTLES</h1>
        <h2 class="section-title">THEY CHALLENGED YOU</h2>
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
              <div class="avatar">{{ challenge.name.charAt(0) }}</div>
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
        <h2 class="section-title">YOU CHALLENGED THEM</h2>
        <div class="battle-grid">
          <div 
            v-for="challenge in outgoingChallenges" 
            :key="challenge.id"
            class="battle-card"
          >
            <div class="player-info">
              <div class="avatar">{{ challenge.name.charAt(0) }}</div>
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
        </div>

        <!-- Invite Players -->
        <div class="invite-grid">
          <div class="invite-card" @click="invitePlayer">
            <div class="invite-icon">👤</div>
            <span>Invite a Player</span>
            <button class="btn-new">New</button>
          </div>
          <div class="invite-card" @click="invitePlayer">
            <div class="invite-icon">👤</div>
            <span>Invite a Player</span>
            <button class="btn-new">New</button>
          </div>
        </div>
      </div>

      <!-- Finished Battles Section -->
      <div class="section">
        <h2 class="section-title">FINISHED BATTLES</h2>
        <div class="battle-grid">
          <div 
            v-for="battle in finishedBattles" 
            :key="battle.id"
            class="battle-card finished"
            :class="{ 'won': battle.points > 0, 'lost': battle.points < 0 }"
          >
            <div class="player-info">
              <div class="avatar">{{ battle.name.charAt(0) }}</div>
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
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// Modal state
const showInvitationModal = ref(false)
const invitedPlayerName = ref('')

// Données simulées
const incomingChallenges = ref([
  {
    id: 1,
    name: 'R.FREUENFELD',
    country: 'DE',
    timeLeft: '24h left',
    status: 'play'
  },
  {
    id: 2,
    name: 'C.NDIAYE',
    country: 'FR',
    timeLeft: '24h left',
    status: 'waiting'
  },
  {
    id: 3,
    name: 'R.KELLER',
    country: 'DE',
    timeLeft: '24h left',
    status: 'invitation'
  },
  {
    id: 4,
    name: 'L.ANEX',
    country: 'FR',
    timeLeft: '11h left',
    status: 'invitation'
  }
])

const outgoingChallenges = ref([
  {
    id: 5,
    name: 'H.OVSANNA',
    country: 'RO',
    timeLeft: '6h left',
    status: 'play'
  },
  {
    id: 6,
    name: 'S.DACOSTA',
    country: 'PT',
    timeLeft: '8h left',
    status: 'waiting'
  }
])

const finishedBattles = ref([
  {
    id: 7,
    name: 'P.DUJARDIN',
    country: 'FR',
    points: 300
  },
  {
    id: 8,
    name: 'L.ANEX',
    country: 'FR',
    points: -100
  }
])

// Methods
const acceptChallenge = (id) => {
  const challenge = incomingChallenges.value.find(c => c.id === id)
  if (challenge) {
    challenge.status = 'play'
  }
}

const declineChallenge = (id) => {
  incomingChallenges.value = incomingChallenges.value.filter(c => c.id !== id)
}

const handleAction = (challenge) => {
  if (challenge.status === 'play') {
    console.log('Starting battle with', challenge.name)
    // Navigation simple vers le quiz
    router.push('/battle-quiz')
  }
}

const viewBattle = (id) => {
  console.log('Viewing battle', id)
  // Navigation simple vers les détails
  router.push(`/battle-details/${id}`)
}

// Nouvelle méthode pour inviter un joueur
const invitePlayer = () => {
  // Simuler l'invitation d'un joueur aléatoire
  const randomPlayers = ['M.GARCIA', 'T.SMITH', 'A.MILLER', 'S.JONES', 'C.WILSON']
  const randomPlayer = randomPlayers[Math.floor(Math.random() * randomPlayers.length)]
  
  invitedPlayerName.value = randomPlayer
  showInvitationModal.value = true
  
  // Ajouter automatiquement à la liste des défis envoyés après 2 secondes
  setTimeout(() => {
    if (showInvitationModal.value) {
      const newChallenge = {
        id: Date.now(),
        name: randomPlayer,
        country: 'US',
        timeLeft: '24h left',
        status: 'waiting'
      }
      outgoingChallenges.value.unshift(newChallenge)
    }
  }, 2000)
}

const closeInvitationModal = () => {
  showInvitationModal.value = false
}

const cancelInvitation = () => {
  showInvitationModal.value = false
  // Ici on pourrait annuler l'invitation côté serveur
  console.log('Invitation cancelled for', invitedPlayerName.value)
}

console.log('BattleView component loaded')
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
.battle-header {
  text-align: center;
  margin-bottom: 2rem;
}

.battle-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: #F7C72C;
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 2px;
  text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
}

/* SECTIONS */
.section {
  margin-bottom: 3rem;
}

.section-title {
  font-size: 1.3rem;
  color: white;
  margin-bottom: 1.5rem;
  text-transform: uppercase;
  font-weight: 600;
  letter-spacing: 1px;
  text-align: center;
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
  font-weight: 600;
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
}

.invite-card:hover {
  border-color: #F7C72C;
  background: rgba(247, 199, 44, 0.05);
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
  
  .battle-title {
    font-size: 2rem;
    letter-spacing: 1px;
  }
  
  .section-title {
    font-size: 1.1rem;
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
  
  .invitation-label {
    font-size: 0.6rem;
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
  
  .battle-title {
    font-size: 2.8rem;
    letter-spacing: 2px;
  }
  
  .section {
    margin-bottom: 2.5rem;
  }
  
  .section-title {
    font-size: 1.4rem;
    text-align: left;
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
  
  .invitation-label {
    font-size: 0.65rem;
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
    padding: 0 1rem;
  }
  
  .battle-title {
    font-size: 3.5rem;
    letter-spacing: 3px;
  }
  
  .section {
    margin-bottom: 3.5rem;
  }
  
  .section-title {
    font-size: 1.8rem;
    text-align: left;
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
  
  .invitation-label {
    font-size: 0.7rem;
  }
}

/* TRÈS PETIT MOBILE (479px et moins) - AMÉLIORÉ */
@media (max-width: 479px) {
  .battle-page {
    padding: 0.5rem;
    padding-bottom: 80px;
  }
  
  .battle-title {
    font-size: 1.6rem;
    letter-spacing: 1px;
  }
  
  .section {
    margin-bottom: 2rem;
  }
  
  .section-title {
    font-size: 0.9rem;
    margin-bottom: 1rem;
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
  
  .invitation-label {
    font-size: 0.4rem;
    margin-left: 0.2rem;
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
  
  .battle-title {
    font-size: 1.4rem;
  }
  
  .section-title {
    font-size: 0.8rem;
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
  
  .invitation-label {
    font-size: 0.35rem;
  }
  
  .invite-card {
    padding: 0.6rem;
  }
  
  .invite-icon {
    font-size: 0.9rem;
  }
}
</style>