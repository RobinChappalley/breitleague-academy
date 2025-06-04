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
  }
}

const viewBattle = (id) => {
  console.log('Viewing battle', id)
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
  padding-bottom: 100px; /* Espace pour navbar mobile */
  box-sizing: border-box;
}

/* MAIN CONTENT */
.main-content {
  width: 100%;
  max-width: 1200px;
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

.battle-subtitle {
  font-size: 1.1rem;
  color: rgba(255, 255, 255, 0.9);
  margin: 1rem 0 0 0;
  text-transform: uppercase;
  font-weight: 500;
  letter-spacing: 1px;
}

/* SECTIONS */
.section {
  margin-bottom: 2.5rem;
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

/* GRIDS - Format liste pour mobile */
.battle-grid {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.invite-grid {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-top: 1.5rem;
}

/* BATTLE CARDS */
.battle-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: rgba(255, 255, 255, 0.1);
  padding: 1.5rem;
  border-radius: 12px;
  backdrop-filter: blur(10px);
  transition: all 0.3s ease;
  border: 1px solid rgba(255, 255, 255, 0.1);
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
}

.player-details {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
}

.player-name {
  font-size: 1rem;
  font-weight: 600;
  color: white;
  margin: 0;
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
  min-width: 70px;
  text-align: center;
  font-weight: 500;
}

.points {
  font-weight: 700;
  font-size: 1rem;
}

/* ACTION BUTTONS */
.action-buttons {
  display: flex;
  align-items: center;
  gap: 0.5rem;
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
  margin-left: 0.3rem;
}

/* INVITE CARDS */
.invite-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: rgba(255, 255, 255, 0.05);
  padding: 1.5rem;
  border-radius: 12px;
  border: 2px dashed rgba(255, 255, 255, 0.3);
  transition: all 0.3s ease;
}

.invite-card:hover {
  border-color: #F7C72C;
  background: rgba(247, 199, 44, 0.05);
}

.invite-icon {
  font-size: 1.3rem;
  color: #F7C72C;
}

/* INVITATION MODAL */
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
}

.invitation-modal {
  background: linear-gradient(135deg, #1e3a8a 0%, #072C54 100%);
  border-radius: 20px;
  padding: 2rem;
  max-width: 400px;
  width: 90%;
  position: relative;
  color: white;
  border: 2px solid #F7C72C;
  text-align: center;
  animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: translateY(-50px) scale(0.9);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
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

.close-btn:hover {
  background: #E6B625;
  transform: scale(1.1);
}

.modal-icon {
  margin-bottom: 1.5rem;
}

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
  0% {
    transform: scale(0);
  }
  50% {
    transform: scale(1.1);
  }
  100% {
    transform: scale(1);
  }
}

.check-icon {
  font-size: 2.5rem;
  color: white;
  font-weight: bold;
}

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

.modal-details {
  margin-bottom: 2rem;
}

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

.waiting-indicator {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.8rem;
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.9rem;
}

.spinner {
  width: 20px;
  height: 20px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  border-top-color: #F7C72C;
  animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.modal-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
}

.btn-cancel,
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
}

.btn-cancel {
  background: rgba(244, 67, 54, 0.2);
  color: #F44336;
  border: 2px solid #F44336;
}

.btn-cancel:hover {
  background: rgba(244, 67, 54, 0.3);
  transform: translateY(-2px);
}

.btn-ok {
  background: #F7C72C;
  color: #072C54;
}

.btn-ok:hover {
  background: #E6B625;
  transform: translateY(-2px);
}

/* DESKTOP (768px et plus) */
@media (min-width: 768px) {
  .battle-page {
    margin-left: 280px; /* Aligné avec navbar desktop */
    width: calc(100% - 280px);
    padding: 2rem;
    padding-bottom: 2rem;
  }
  
  .main-content {
    max-width: 1000px;
    padding: 0 2rem;
  }
  
  .battle-title {
    font-size: 3.5rem;
    letter-spacing: 3px;
    margin-bottom: 1rem;
  }
  
  .battle-header {
    margin-bottom: 3rem;
  }
  
  .section {
    margin-bottom: 3rem;
  }
  
  .section-title {
    font-size: 1.8rem;
    margin-bottom: 2rem;
  }
  
  .battle-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
    gap: 1.5rem;
  }
  
  .invite-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
  }
  
  .battle-card {
    padding: 2rem;
  }
  
  .avatar {
    width: 55px;
    height: 55px;
    font-size: 1.3rem;
  }
  
  .player-name {
    font-size: 1.1rem;
  }
  
  .flag {
    font-size: 0.9rem;
  }
  
  .time-left,
  .points {
    font-size: 1rem;
    min-width: 80px;
  }
  
  .btn-action,
  .btn-view,
  .btn-new {
    padding: 0.8rem 1.5rem;
    font-size: 0.9rem;
  }
  
  .btn-accept,
  .btn-decline {
    width: 35px;
    height: 35px;
    font-size: 1rem;
  }
  
  .invitation-label {
    font-size: 0.8rem;
  }
}

/* LARGE DESKTOP (1024px et plus) */
@media (min-width: 1024px) {
  .battle-page {
    margin-left: 280px;
    width: calc(100% - 280px);
    padding: 3rem;
  }
  
  .main-content {
    max-width: 1200px;
    padding: 0 3rem;
  }
  
  .battle-title {
    font-size: 4rem;
  }
  
  .section {
    margin-bottom: 4rem;
  }
  
  .section-title {
    font-size: 2rem;
  }
  
  .battle-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 2rem;
  }
  
  .invite-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 2rem;
  }
  
  .battle-card {
    padding: 2.5rem;
  }
  
  .avatar {
    width: 60px;
    height: 60px;
    font-size: 1.4rem;
  }
  
  .player-name {
    font-size: 1.2rem;
  }
  
  .time-left,
  .points {
    font-size: 1.1rem;
  }
  
  .btn-action,
  .btn-view,
  .btn-new {
    padding: 1rem 2rem;
    font-size: 1rem;
  }
}

/* MOBILE (moins de 768px) */
@media (max-width: 767px) {
  .battle-page {
    margin-left: 0;
    width: 100%;
    padding: 1rem;
    padding-bottom: 80px; /* Navbar mobile */
  }
  
  .battle-title {
    font-size: 2rem;
    letter-spacing: 1px;
  }
  
  .section-title {
    font-size: 1.1rem;
  }
  
  .battle-card {
    padding: 1.2rem;
    flex-wrap: wrap;
    gap: 1rem;
  }
  
  .player-info {
    gap: 0.8rem;
    min-width: 200px;
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
    min-width: 60px;
  }
  
  .action-buttons {
    flex-direction: column;
    gap: 0.3rem;
    align-items: flex-end;
    min-width: 80px;
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
  
  .invite-card {
    padding: 1.2rem;
  }
}

/* TRÈS PETIT MOBILE (moins de 480px) */
@media (max-width: 479px) {
  .battle-page {
    padding: 0.8rem;
    padding-bottom: 80px;
  }
  
  .battle-title {
    font-size: 1.8rem;
  }
  
  .section-title {
    font-size: 1rem;
  }
  
  .battle-card {
    padding: 1rem;
  }
  
  .player-info {
    min-width: 150px;
  }
  
  .avatar {
    width: 35px;
    height: 35px;
    font-size: 0.9rem;
  }
  
  .player-name {
    font-size: 0.8rem;
  }
  
  .time-left,
  .points {
    font-size: 0.7rem;
  }
  
  .btn-action,
  .btn-view,
  .btn-new {
    padding: 0.4rem 0.8rem;
    font-size: 0.6rem;
  }
}
</style>