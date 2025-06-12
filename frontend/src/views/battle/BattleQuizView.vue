<template>
  <div class="battle-quiz-page">
    <!-- Battle Header Info -->
    <div class="battle-header">
      <!-- UTILISATEUR AUTHENTIFIÉ À GAUCHE -->
      <div class="player-info">
        <div class="avatar" :style="getAvatarStyle(currentPlayer)">
          <img 
            v-if="currentPlayer.avatar && currentPlayer.avatar !== currentPlayer.name?.charAt(0)" 
            :src="getAvatarUrl(currentPlayer)" 
            :alt="currentPlayer.name"
            class="avatar-image"
          />
          <span v-else class="avatar-initial">{{ currentPlayer.name?.charAt(0) || 'Y' }}</span>
        </div>
        <div class="player-details">
          <h3>{{ currentPlayer.name }}</h3>
          <span class="flag">{{ currentPlayer.flag }}</span>
        </div>
      </div>
      
      <div class="vs-indicator">VS</div>
      
      <!-- ADVERSAIRE À DROITE -->
      <div class="opponent-info">
        <div class="opponent-details">
          <h3>{{ opponent.name }}</h3>
          <span class="flag">{{ opponent.flag }}</span>
        </div>
        <div class="avatar" :style="getAvatarStyle(opponent)">
          <img 
            v-if="opponent.avatar && opponent.avatar !== opponent.name?.charAt(0)" 
            :src="getAvatarUrl(opponent)" 
            :alt="opponent.name"
            class="avatar-image"
          />
          <span v-else class="avatar-initial">{{ opponent.name?.charAt(0) || 'O' }}</span>
        </div>
      </div>
    </div>

    <!-- Progress Bar -->
    <div class="progress-container">
      <div class="progress-bar">
        <div 
          class="progress-fill"
          :style="{ width: progressPercentage + '%' }"
        ></div>
      </div>
      <div class="question-counter">
        {{ currentQuestionIndex + 1 }} / {{ totalQuestions }}
      </div>
    </div>

    <!-- Timer -->
    <div class="timer-container">
      <div class="timer" :class="{ 'warning': timeLeft <= 5 }">
        {{ timeLeft }}s
      </div>
    </div>

    <!-- Question -->
    <div class="question-container" v-if="currentQuestion">
      <h2 class="question-text">{{ currentQuestion.text }}</h2>
      
      <!-- Answer Options -->
      <div class="answers-grid">
        <button
          v-for="(answer, index) in currentQuestion.answers"
          :key="index"
          class="answer-btn"
          :class="getAnswerClass(index)"
          :disabled="hasAnswered"
          @click="selectAnswer(index)"
        >
          {{ answer.text }}
        </button>
      </div>
    </div>

    <!-- Loading état -->
    <div class="loading-container" v-else>
      <div class="loading-spinner"></div>
      <p>Chargement des questions...</p>
    </div>

    <!-- Popup des points gagnés -->
    <div 
      v-if="showPointsPopup" 
      class="points-popup"
      :class="{ 'speed-bonus': pointsPopupText.includes('bonus') }"
    >
      {{ pointsPopupText }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { battleService } from '@/services/api'

const router = useRouter()

// Timer
let timerInterval = null

// Battle Data
const battleData = ref(null)
const opponent = ref({
  id: 2,
  name: 'M.OVSANNA',
  avatar: null,
  flag: '🇩🇪'
})

// MODIFIER : Initialisation par défaut plus neutre
const currentPlayer = ref({
  id: null,
  name: 'Chargement...',
  avatar: null,
  flag: '🇨🇭'
})

// Quiz State
const currentQuestionIndex = ref(0)
const totalQuestions = ref(5)
const timeLeft = ref(30)
const hasAnswered = ref(false)
const selectedAnswer = ref(null)

// Results
const playerScore = ref(0)
const opponentScore = ref(0)
const playerTime = ref(0)
const opponentTime = ref(0)
const playerAnswers = ref([])

// Questions depuis la base de données
const questions = ref([])

// Points popup
const showPointsPopup = ref(false)
const pointsPopupText = ref('')

// FONCTION AMÉLIORÉE : Récupérer l'URL de l'avatar
const getAvatarUrl = (user) => {
  console.log('🖼️ Getting avatar for user:', user)
  
  if (!user || !user.avatar) {
    console.log('❌ No avatar data for user:', user?.name)
    return null
  }
  
  // Si c'est juste une lettre (fallback), ne pas afficher d'image
  if (typeof user.avatar === 'string' && user.avatar.length === 1) {
    console.log('❌ Avatar is just initial:', user.avatar)
    return null
  }
  
  // Construire l'URL complète
  const avatarUrl = user.avatar.startsWith('http') ? user.avatar : `http://localhost:8000/${user.avatar}`
  console.log('✅ Avatar URL for', user.name, ':', avatarUrl)
  
  return avatarUrl
}

// FONCTION CORRIGÉE : Récupérer les données utilisateur actuelles
const loadCurrentUserData = async () => {
  try {
    console.log('🔄 Loading current user data...')
    
    // 1. Récupérer l'utilisateur authentifié
    const userResponse = await fetch('http://localhost:8000/api/user', {
      credentials: 'include',
      headers: { 'Accept': 'application/json' }
    })
    
    if (!userResponse.ok) {
      throw new Error('Failed to fetch authenticated user')
    }
    
    const userData = await userResponse.json()
    console.log('📋 Raw authenticated user data:', userData)
    
    // 2. Récupérer les données complètes via ton API (AVEC POS)
    const fullUserResponse = await fetch(`http://localhost:8000/api/v1/users/${userData.id}`, {
      credentials: 'include',
      headers: { 'Accept': 'application/json' }
    })
    
    let fullUserData = userData // Fallback sur les données de base
    
    if (fullUserResponse.ok) {
      const fullUserResponseData = await fullUserResponse.json()
      fullUserData = fullUserResponseData.data || fullUserResponseData || userData
      console.log('📋 Full user data from API:', fullUserData)
    } else {
      console.warn('⚠️ Could not fetch full user data, using basic auth data')
    }
    
    // 3. METTRE À JOUR currentPlayer avec les VRAIES données
    currentPlayer.value = {
      id: fullUserData.id || userData.id,
      name: fullUserData.username || userData.username || 'YOU',
      avatar: fullUserData.avatar || userData.avatar || null,
      flag: getUserFlag(fullUserData) || '🇨🇭' // UTILISER LA NOUVELLE FONCTION
    }
    
    console.log('✅ Current player loaded:', currentPlayer.value)
    console.log('🖼️ Avatar path:', currentPlayer.value.avatar)
    console.log('🚩 Flag from pos:', fullUserData.pos?.country_flag)
    
  } catch (error) {
    console.warn('⚠️ Error loading current user data:', error)
    
    // Fallback en cas d'erreur
    currentPlayer.value = {
      id: 1,
      name: 'YOU',
      avatar: null,
      flag: '🇨🇭'
    }
  }
}

// NOUVELLE FONCTION : Récupérer le drapeau depuis la relation pos
const getUserFlag = (userData) => {
  // 1. Essayer d'abord depuis pos.country_flag (la vraie source)
  if (userData.pos && userData.pos.country_flag) {
    console.log('✅ Flag from pos.country_flag:', userData.pos.country_flag)
    return userData.pos.country_flag
  }
  
  // 2. Fallback sur le mapping pos_id si pas de country_flag
  if (userData.pos_id) {
    const flagFromPosId = getCountryFlag(userData.pos_id)
    console.log('⚠️ Fallback flag from pos_id mapping:', flagFromPosId)
    return flagFromPosId
  }
  
  // 3. Fallback final
  console.log('❌ No flag found, using default')
  return '🇨🇭'
}

// GARDER LA FONCTION DE MAPPING COMME FALLBACK
const getCountryFlag = (posId) => {
  const flagMapping = {
    1: '🇨🇭', // Suisse
    2: '🇫🇷', // France
    3: '🇩🇪', // Allemagne
    4: '🇮🇹', // Italie
    5: '🇪🇸', // Espagne
    6: '🇵🇹', // Portugal
    7: '🇷🇴', // Roumanie
    8: '🇺🇸', // États-Unis
    9: '🇬🇧', // Royaume-Uni
    10: '🇧🇪' // Belgique
  }
  
  console.log('🚩 Converting pos_id to flag:', posId, '->', flagMapping[posId])
  return flagMapping[posId] || '🇨🇭'
}

// Computed
const currentQuestion = computed(() => {
  if (questions.value.length === 0) return null
  
  const question = questions.value[currentQuestionIndex.value]
  
  // UTILISER LA VRAIE STRUCTURE DE TA BASE (text_answer)
  return {
    id: question.id,
    text: question.content_default || question.content_lf_tf || question.content_lf_blank || 'Question sans contenu',
    answers: question.choices?.map(choice => ({
      text: choice.text_answer || choice.content || choice.text,
      correct: choice.is_correct || choice.correct || false
    })) || []
  }
})

const progressPercentage = computed(() => {
  if (totalQuestions.value === 0) return 0
  return (currentQuestionIndex.value / totalQuestions.value) * 100
})

// Charger les données de bataille depuis localStorage
const loadBattleData = () => {
  try {
    const savedBattle = localStorage.getItem('currentBattle')
    if (savedBattle) {
      battleData.value = JSON.parse(savedBattle)
      
      // Mettre à jour les données de l'adversaire
      if (battleData.value.opponent) {
        opponent.value = {
          id: battleData.value.opponent.id,
          name: battleData.value.opponent.name,
          avatar: battleData.value.opponent.avatar,
          flag: battleData.value.opponent.flag || '🇩🇪'
        }
        
        console.log('✅ Opponent data loaded:', opponent.value)
        console.log('🖼️ Opponent avatar:', battleData.value.opponent.avatar)
      }
      
      // Charger les questions depuis la base de données
      if (battleData.value.questions && battleData.value.questions.length > 0) {
        questions.value = battleData.value.questions
        totalQuestions.value = questions.value.length
        
        console.log('✅ Questions loaded from database:', questions.value.length, 'questions')
      } else {
        console.warn('⚠️ No questions found in battle data, using fallback')
        loadFallbackQuestions()
      }
    } else {
      console.warn('⚠️ No battle data found, using fallback')
      loadFallbackQuestions()
    }
  } catch (error) {
    console.error('❌ Error loading battle data:', error)
    loadFallbackQuestions()
  }
}

// Questions de fallback si problème avec la base
const loadFallbackQuestions = () => {
  questions.value = [
    {
      id: 1,
      content_default: 'Quelle année Breitling a-t-elle été fondée ?',
      choices: [
        { text_answer: '1884', is_correct: true },
        { text_answer: '1885', is_correct: false },
        { text_answer: '1890', is_correct: false },
        { text_answer: '1900', is_correct: false }
      ]
    },
    {
      id: 2,
      content_default: 'Qui a fondé Breitling ?',
      choices: [
        { text_answer: 'Léon Breitling', is_correct: true },
        { text_answer: 'Gaston Breitling', is_correct: false },
        { text_answer: 'Willy Breitling', is_correct: false },
        { text_answer: 'Ernest Schneider', is_correct: false }
      ]
    },
    {
      id: 3,
      content_default: 'Quel est le calibre emblématique de Breitling ?',
      choices: [
        { text_answer: 'B01', is_correct: true },
        { text_answer: 'B09', is_correct: false },
        { text_answer: 'B20', is_correct: false },
        { text_answer: 'B13', is_correct: false }
      ]
    },
    {
      id: 4,
      content_default: 'Quelle est la montre iconique de Breitling depuis 1952 ?',
      choices: [
        { text_answer: 'Navitimer', is_correct: true },
        { text_answer: 'Superocean', is_correct: false },
        { text_answer: 'Avenger', is_correct: false },
        { text_answer: 'Premier', is_correct: false }
      ]
    },
    {
      id: 5,
      content_default: 'En quelle année le premier poussoir indépendant a-t-il été créé ?',
      choices: [
        { text_answer: '1915', is_correct: true },
        { text_answer: '1920', is_correct: false },
        { text_answer: '1934', is_correct: false },
        { text_answer: '1952', is_correct: false }
      ]
    }
  ]
  
  totalQuestions.value = questions.value.length
  console.log('🔄 Using fallback questions:', questions.value.length)
}

// Methods (le reste des méthodes reste identique...)
const startTimer = () => {
  timerInterval = setInterval(() => {
    if (timeLeft.value > 0) {
      timeLeft.value--
    } else {
      selectAnswer(null)
    }
  }, 1000)
}

const stopTimer = () => {
  if (timerInterval) {
    clearInterval(timerInterval)
    timerInterval = null
  }
}

const selectAnswer = (index) => {
  if (hasAnswered.value) return
  
  selectedAnswer.value = index
  hasAnswered.value = true
  stopTimer()

  const timeTaken = 30 - timeLeft.value
  playerTime.value += timeTaken

  const opponentTime = Math.floor(Math.random() * 25) + 3
  
  let isCorrect = false
  let selectedAnswerText = 'Pas de réponse'
  let pointsEarned = 0
  
  if (index !== null && currentQuestion.value?.answers[index]) {
    isCorrect = currentQuestion.value.answers[index].correct
    selectedAnswerText = currentQuestion.value.answers[index].text
    
    if (isCorrect) {
      playerScore.value++
      
      const basePoints = 100
      let speedBonus = 0
      
      if (timeTaken < opponentTime) {
        const timeDifference = opponentTime - timeTaken
        
        if (timeDifference >= 15) {
          speedBonus = 75
        } else if (timeDifference >= 10) {
          speedBonus = 50
        } else if (timeDifference >= 5) {
          speedBonus = 30
        } else {
          speedBonus = 15
        }
      }
      
      pointsEarned = basePoints + speedBonus
      
      if (speedBonus > 0) {
        pointsPopupText.value = `+${pointsEarned} PTS!\n(+${speedBonus} bonus rapidité vs adversaire)`
      } else {
        pointsPopupText.value = `+${pointsEarned} PTS\n(Adversaire était plus rapide)`
      }
      
      showPointsPopup.value = true
      setTimeout(() => showPointsPopup.value = false, 2500)
    } else {
      pointsPopupText.value = `0 PTS\n(Mauvaise réponse)`
      showPointsPopup.value = true
      setTimeout(() => showPointsPopup.value = false, 2000)
    }
  }
  
  playerAnswers.value.push({
    questionId: currentQuestion.value?.id,
    questionText: currentQuestion.value?.text,
    selectedAnswer: selectedAnswerText,
    correct: isCorrect,
    time: timeTaken,
    opponentTime: opponentTime,
    timeLeft: timeLeft.value,
    points: pointsEarned,
    speedBonus: isCorrect ? (timeTaken < opponentTime ? true : false) : false
  })
  
  setTimeout(() => nextQuestion(), 2500)
}

const nextQuestion = () => {
  if (currentQuestionIndex.value < totalQuestions.value - 1) {
    currentQuestionIndex.value++
    timeLeft.value = 30
    hasAnswered.value = false
    selectedAnswer.value = null
    startTimer()
  } else {
    finishBattle()
  }
}

const finishBattle = async () => {
  stopTimer()
  
  const playerTotalPoints = playerAnswers.value.reduce((total, answer) => total + answer.points, 0)
  
  const opponentAnswers = playerAnswers.value.map((playerAnswer, index) => {
    const question = questions.value[index]
    const opponentTime = playerAnswer.opponentTime
    
    const isCorrect = Math.random() > 0.3
    const randomAnswer = Math.floor(Math.random() * 4)
    
    let points = 0
    if (isCorrect) {
      opponentScore.value++
      
      const basePoints = 100
      let speedBonus = 0
      
      if (opponentTime < playerAnswer.time) {
        const timeDifference = playerAnswer.time - opponentTime
        
        if (timeDifference >= 15) {
          speedBonus = 75
        } else if (timeDifference >= 10) {
          speedBonus = 50
        } else if (timeDifference >= 5) {
          speedBonus = 30
        } else {
          speedBonus = 15
        }
      }
      
      points = basePoints + speedBonus
    }
    
    opponentTime.value += opponentTime
    
    return {
      questionId: question.id,
      questionText: question.content_default,
      selectedAnswer: question.choices?.[randomAnswer]?.text_answer || question.choices?.[randomAnswer]?.text || 'Réponse mockée',
      correct: isCorrect,
      time: opponentTime,
      timeLeft: Math.max(0, 30 - opponentTime),
      points: points,
      speedBonus: isCorrect ? (opponentTime < playerAnswer.time ? true : false) : false
    }
  })
  
  const opponentTotalPoints = opponentAnswers.reduce((total, answer) => total + answer.points, 0)
  
  try {
    const matchData = {
      player1_id: currentPlayer.value.id,
      player2_id: opponent.value.id,
      player1_score: playerScore.value,
      player2_score: opponentScore.value,
      player1_time: playerTime.value,
      player2_time: opponentTime.value,
      player1_points: playerTotalPoints,
      player2_points: opponentTotalPoints,
      winner_id: playerTotalPoints > opponentTotalPoints ? currentPlayer.value.id : opponent.value.id,
      questions_data: JSON.stringify(questions.value.map(q => ({
        id: q.id,
        text: q.content_default,
        correctAnswer: q.choices?.find(c => c.is_correct)?.text_answer || q.choices?.find(c => c.is_correct)?.text
      }))),
      player1_answers: JSON.stringify(playerAnswers.value),
      player2_answers: JSON.stringify(opponentAnswers)
    }
    
    console.log('💾 Sauvegarde du match dans la base...')
    // const savedMatch = await battleService.saveMatch(matchData)
    // console.log('✅ Match sauvegardé avec ID:', savedMatch.id)
    
    const battleResults = {
      battleId: Date.now(),
      opponent: opponent.value,
      playerScore: playerScore.value,
      opponentScore: opponentScore.value,
      playerTime: playerTime.value,
      opponentTime: opponentTime.value,
      playerTotalPoints: playerTotalPoints,
      opponentTotalPoints: opponentTotalPoints,
      questionsData: questions.value.map(q => ({
        id: q.id,
        text: q.content_default || q.content_lf_tf || q.content_lf_blank,
        correctAnswer: q.choices?.find(c => c.is_correct)?.text_answer || q.choices?.find(c => c.is_correct)?.text || 'Réponse correcte'
      })),
      playerAnswers: playerAnswers.value,
      opponentAnswers: opponentAnswers
    }
    
    localStorage.setItem('lastBattleResults', JSON.stringify(battleResults))
    router.push(`/battle-details/${battleResults.battleId}`)
    
  } catch (error) {
    console.error('❌ Erreur sauvegarde match:', error)
  }
}

const getAnswerClass = (index) => {
  if (!hasAnswered.value) return ''
  
  if (selectedAnswer.value === index) {
    return currentQuestion.value?.answers[index]?.correct ? 'correct' : 'incorrect'
  }
  
  if (currentQuestion.value?.answers[index]?.correct) {
    return 'correct-answer'
  }
  
  return 'disabled'
}

const getAvatarStyle = (player) => {
  const gradients = [
    'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
    'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
    'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
    'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
    'linear-gradient(135deg, #fa709a 0%, #fee140 100%)'
  ]
  
  const index = (player.id || 0) % gradients.length
  return {
    background: gradients[index]
  }
}

// Lifecycle - ORDRE DE CHARGEMENT IMPORTANT
onMounted(async () => {
  console.log('🚀 BattleQuizView mounted')
  
  // 1. Charger les données du joueur actuel EN PREMIER
  await loadCurrentUserData()
  
  // 2. Ensuite charger les données de bataille
  loadBattleData()
  
  // 3. Démarrer le timer après un délai
  setTimeout(() => {
    if (questions.value.length > 0) {
      console.log('⏰ Starting timer...')
      startTimer()
    }
  }, 1000)
})

onUnmounted(() => {
  stopTimer()
})
</script>

<style scoped>
.battle-quiz-page {
  min-height: 100vh;
  width: 100%;
  background: linear-gradient(135deg, #072C54 0%, #1e3a8a 100%);
  color: white;
  padding: 1rem;
  padding-bottom: 100px;
  box-sizing: border-box;
}

/* BATTLE HEADER */
.battle-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

/* UTILISATEUR AUTHENTIFIÉ (GAUCHE) */
.player-info {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex: 1;
  justify-content: flex-start;
}

.player-details {
  text-align: left;
}

/* ADVERSAIRE (DROITE) */
.opponent-info {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex: 1;
  justify-content: flex-end;
}

.opponent-details {
  text-align: right;
  order: -1; /* Place le texte avant l'avatar */
}

.avatar {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 1.5rem;
  border: 3px solid #F7C72C;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
  overflow: hidden;
  flex-shrink: 0;
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
  color: white;
  text-transform: uppercase;
}

.opponent-details h3,
.player-details h3 {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 600;
}

.flag {
  font-size: 1.2rem;
}

.vs-indicator {
  font-size: 2rem;
  font-weight: 700;
  color: #F7C72C;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
  flex-shrink: 0;
}

/* PROGRESS */
.progress-container {
  margin-bottom: 2rem;
  text-align: center;
}

.progress-bar {
  width: 100%;
  height: 8px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 4px;
  overflow: hidden;
  margin-bottom: 1rem;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #F7C72C 0%, #E6B625 100%);
  border-radius: 4px;
  transition: width 0.3s ease;
}

.question-counter {
  font-size: 1.1rem;
  font-weight: 600;
  color: #F7C72C;
}

/* TIMER */
.timer-container {
  text-align: center;
  margin-bottom: 2rem;
}

.timer {
  display: inline-block;
  font-size: 3rem;
  font-weight: 700;
  color: #F7C72C;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 50%;
  width: 100px;
  height: 100px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
  transition: all 0.3s ease;
}

.timer.warning {
  color: #FF4444;
  animation: pulse 0.5s ease-in-out infinite alternate;
}

@keyframes pulse {
  from { transform: scale(1); }
  to { transform: scale(1.1); }
}

/* QUESTION */
.question-container {
  max-width: 800px;
  margin: 0 auto;
}

.question-text {
  font-size: 1.5rem;
  font-weight: 600;
  text-align: center;
  margin-bottom: 3rem;
  line-height: 1.4;
  color: white;
}

.answers-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
}

.answer-btn {
  padding: 1.5rem 2rem;
  background: rgba(255, 255, 255, 0.1);
  border: 2px solid rgba(255, 255, 255, 0.2);
  border-radius: 12px;
  color: white;
  font-size: 1.1rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  text-align: left;
}

.answer-btn:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.2);
  border-color: #F7C72C;
  transform: translateY(-2px);
}

.answer-btn:disabled {
  cursor: not-allowed;
}

/* COULEURS DES RÉPONSES AMÉLIORÉES */
.answer-btn.correct {
  background: rgba(76, 175, 80, 0.4) !important;
  border-color: #4CAF50 !important;
  color: white !important;
  box-shadow: 0 0 15px rgba(76, 175, 80, 0.5);
  animation: correctPulse 0.6s ease-out;
}

.answer-btn.incorrect {
  background: rgba(244, 67, 54, 0.4) !important;
  border-color: #F44336 !important;
  color: white !important;
  box-shadow: 0 0 15px rgba(244, 67, 54, 0.5);
  animation: incorrectShake 0.6s ease-out;
}

.answer-btn.correct-answer {
  background: rgba(76, 175, 80, 0.2) !important;
  border-color: #4CAF50 !important;
  color: #4CAF50 !important;
  animation: correctGlow 0.6s ease-out;
}

.answer-btn.disabled {
  opacity: 0.4 !important;
  background: rgba(255, 255, 255, 0.05) !important;
  border-color: rgba(255, 255, 255, 0.1) !important;
}

/* ANIMATIONS */
@keyframes correctPulse {
  0% { transform: scale(1); box-shadow: 0 0 0 rgba(76, 175, 80, 0.5); }
  50% { transform: scale(1.05); box-shadow: 0 0 20px rgba(76, 175, 80, 0.8); }
  100% { transform: scale(1); box-shadow: 0 0 15px rgba(76, 175, 80, 0.5); }
}

@keyframes incorrectShake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-10px); }
  75% { transform: translateX(10px); }
}

@keyframes correctGlow {
  0% { box-shadow: 0 0 0 rgba(76, 175, 80, 0); }
  50% { box-shadow: 0 0 15px rgba(76, 175, 80, 0.6); }
  100% { box-shadow: 0 0 10px rgba(76, 175, 80, 0.3); }
}

/* AFFICHAGE DES POINTS GAGNÉS */
.points-popup {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: rgba(247, 199, 44, 0.95);
  color: #072C54;
  padding: 1.5rem 2rem;
  border-radius: 15px;
  font-size: 1.3rem;
  font-weight: 700;
  z-index: 1000;
  animation: pointsShow 2.5s ease-out forwards;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  text-align: center;
  white-space: pre-line;
  line-height: 1.3;
  border: 3px solid #072C54;
}

.points-popup.speed-bonus {
  background: linear-gradient(135deg, #4CAF50 0%, #F7C72C 100%);
  color: white;
  border-color: #4CAF50;
  animation: speedBonusShow 2.5s ease-out forwards;
}

@keyframes speedBonusShow {
  0% { 
    opacity: 0; 
    transform: translate(-50%, -50%) scale(0.5) rotate(-10deg); 
  }
  20% { 
    opacity: 1; 
    transform: translate(-50%, -50%) scale(1.2) rotate(2deg); 
  }
  40% { 
    transform: translate(-50%, -50%) scale(1.1) rotate(-1deg); 
  }
  60% { 
    transform: translate(-50%, -50%) scale(1.05) rotate(0.5deg); 
  }
  80% { 
    opacity: 1; 
    transform: translate(-50%, -50%) scale(1) rotate(0deg); 
  }
  100% { 
    opacity: 0; 
    transform: translate(-50%, -50%) scale(0.9) rotate(0deg); 
  }
}

@keyframes pointsShow {
  0% { opacity: 0; transform: translate(-50%, -50%) scale(0.5); }
  20% { opacity: 1; transform: translate(-50%, -50%) scale(1.1); }
  80% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
  100% { opacity: 0; transform: translate(-50%, -50%) scale(0.9); }
}

/* LOADING STATE */
.loading-container {
  text-align: center;
  padding: 3rem;
}

.loading-spinner {
  width: 50px;
  height: 50px;
  border: 4px solid rgba(247, 199, 44, 0.3);
  border-top: 4px solid #F7C72C;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem auto;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* RESPONSIVE */
@media (min-width: 768px) {
  .battle-quiz-page {
    margin-left: 280px;
    width: calc(100% - 280px);
    padding: 2rem;
    padding-bottom: 2rem;
  }
  
  .battle-header {
    padding: 3rem 2rem;
  }
  
  .avatar {
    width: 80px;
    height: 80px;
    font-size: 2rem;
  }
  
  .vs-indicator {
    font-size: 3rem;
  }
  
  .question-text {
    font-size: 2rem;
    margin-bottom: 4rem;
  }
  
  .answers-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
  }
  
  .answer-btn {
    padding: 2rem;
    font-size: 1.2rem;
  }
  
  .timer {
    width: 120px;
    height: 120px;
    font-size: 3.5rem;
  }
}

@media (max-width: 767px) {
  .battle-quiz-page {
    margin-left: 0;
    width: 100%;
    padding: 1rem;
    padding-bottom: 80px;
  }
  
  .battle-header {
    padding: 1rem;
    flex-direction: column;
    gap: 1rem;
  }
  
  /* Mobile : garder la même logique mais en vertical */
  .player-info,
  .opponent-info {
    flex-direction: row;
    justify-content: center;
    width: 100%;
  }
  
  .opponent-info {
    flex-direction: row-reverse; /* Avatar à droite, texte à gauche */
  }
  
  .opponent-details {
    text-align: left; /* Réajuster l'alignement sur mobile */
    order: 0;
  }
  
  .vs-indicator {
    font-size: 1.5rem;
    order: 1;
  }
  
  .avatar {
    width: 50px;
    height: 50px;
    font-size: 1.2rem;
  }
  
  .question-text {
    font-size: 1.2rem;
    margin-bottom: 2rem;
  }
  
  .answer-btn {
    padding: 1rem;
    font-size: 1rem;
  }
  
  .timer {
    width: 80px;
    height: 80px;
    font-size: 2.5rem;
  }
}
</style>