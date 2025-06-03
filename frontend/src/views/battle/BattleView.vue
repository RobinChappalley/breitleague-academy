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
          <div class="invite-card">
            <div class="invite-icon">👤</div>
            <span>Invite a Player</span>
            <button class="btn-new">New</button>
          </div>
          <div class="invite-card">
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
  </div>
</template>

<script setup>
import { ref } from 'vue'

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

console.log('BattleView component loaded')
</script>

<style scoped>
.battle-page {
  min-height: 100vh;
  width: 100vw;
  background: linear-gradient(135deg, #072C54 0%, #1e3a8a 100%);
  color: white;
  overflow-x: hidden;
  margin: 0;
  padding: 0;
}

/* MAIN CONTENT */
.main-content {
  width: 100%;
  padding: 2rem 4rem; /* Plus de padding sur les côtés */
  box-sizing: border-box;
}

/* HEADER */
.battle-header {
  text-align: center;
  margin-bottom: 3rem;
}

.battle-title {
  font-size: 3.5rem;
  font-weight: 700;
  color: #F7C72C;
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 3px;
  text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
}

.battle-subtitle {
  font-size: 1.3rem;
  color: rgba(255, 255, 255, 0.9);
  margin: 1rem 0 0 0;
  text-transform: uppercase;
  font-weight: 500;
  letter-spacing: 1px;
}

/* SECTIONS */
.section {
  margin-bottom: 4rem;
}

.section-title {
  font-size: 1.8rem;
  color: white;
  margin-bottom: 2rem;
  text-transform: uppercase;
  font-weight: 600;
  letter-spacing: 1px;
  text-align: center;
}

/* GRIDS */
.battle-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 1.5rem;
}

.invite-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.5rem;
  margin-top: 2rem;
}

/* BATTLE CARDS */
.battle-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: rgba(255, 255, 255, 0.1);
  padding: 1.5rem;
  border-radius: 15px;
  backdrop-filter: blur(10px);
  transition: all 0.3s ease;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.battle-card:hover {
  background: rgba(255, 255, 255, 0.15);
  transform: translateY(-3px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
}

.battle-card.finished.won {
  background: rgba(76, 175, 80, 0.15);
  border-left: 5px solid #4CAF50;
}

.battle-card.finished.lost {
  background: rgba(244, 67, 54, 0.15);
  border-left: 5px solid #F44336;
}

.battle-card.invitation {
  border-left: 5px solid #F7C72C;
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
  box-shadow: 0 4px 12px rgba(247, 199, 44, 0.3);
}

.player-details {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
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
  min-width: 80px;
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
  gap: 0.75rem;
}

.btn-action,
.btn-view,
.btn-new {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-size: 0.9rem;
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
  width: 35px;
  height: 35px;
  border: none;
  border-radius: 50%;
  font-size: 1rem;
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
  font-size: 0.8rem;
  color: #F7C72C;
  font-weight: 600;
  margin-left: 0.5rem;
}

/* INVITE CARDS */
.invite-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: rgba(255, 255, 255, 0.05);
  padding: 1.5rem;
  border-radius: 15px;
  border: 2px dashed rgba(255, 255, 255, 0.3);
  transition: all 0.3s ease;
}

.invite-card:hover {
  border-color: #F7C72C;
  background: rgba(247, 199, 44, 0.05);
}

.invite-icon {
  font-size: 1.5rem;
  color: #F7C72C;
}

/* DESKTOP OPTIMIZATION */
@media (min-width: 1200px) {
  .main-content {
    padding: 3rem 8rem; /* Encore plus de padding pour très large écran */
  }
  
  .battle-title {
    font-size: 4rem;
  }
  
  .battle-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 2rem;
  }
  
  .battle-card {
    padding: 2rem;
  }
  
  .avatar {
    width: 60px;
    height: 60px;
    font-size: 1.4rem;
  }
}

/* MOBILE */
@media (max-width: 768px) {
  .main-content {
    padding: 1rem;
  }
  
  .battle-title {
    font-size: 2rem;
  }
  
  .battle-grid {
    grid-template-columns: 1fr;
  }
  
  .battle-card {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }
  
  .player-info {
    justify-content: center;
  }
  
  .action-buttons {
    justify-content: center;
  }
}
</style>