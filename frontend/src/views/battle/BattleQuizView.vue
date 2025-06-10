<template>
  <div class="battle-quiz-page">
    <!-- Battle Header Info -->
    <div class="battle-header">
      <div class="opponent-info">
        <div class="avatar" :style="getAvatarStyle(opponent)">
          {{ opponent.avatar }}
        </div>
        <div class="opponent-details">
          <h3>{{ opponent.name }}</h3>
          <span class="flag">{{ opponent.flag }}</span>
        </div>
      </div>
      <div class="vs-indicator">VS</div>
      <div class="player-info">
        <div class="avatar" :style="getAvatarStyle(currentPlayer)">
          {{ currentPlayer.avatar }}
        </div>
        <div class="player-details">
          <h3>{{ currentPlayer.name }}</h3>
          <span class="flag">{{ currentPlayer.flag }}</span>
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
  avatar: 'M',
  flag: '🇩🇪'
})

const currentPlayer = ref({
  id: 1,
  name: 'R.DUFUIS',
  avatar: 'R',
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

// Computed
const currentQuestion = computed(() => {
  if (questions.value.length === 0) return null
  
  const question = questions.value[currentQuestionIndex.value]
  
  // UTILISER LA VRAIE STRUCTURE DE TA BASE (text_answer)
  return {
    id: question.id,
    text: question.content_default || question.content_lf_tf || question.content_lf_blank || 'Question sans contenu',
    answers: question.choices?.map(choice => ({
      text: choice.text_answer || choice.content || choice.text, // CORRIGER : utiliser text_answer
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
          avatar: battleData.value.opponent.avatar || battleData.value.opponent.name.charAt(0),
          flag: battleData.value.opponent.flag
        }
      }
      
      // Charger les questions depuis la base de données
      if (battleData.value.questions && battleData.value.questions.length > 0) {
        questions.value = battleData.value.questions
        totalQuestions.value = questions.value.length
        
        console.log('✅ Questions loaded from database:', questions.value.length, 'questions')
        console.log('📋 First question:', questions.value[0])
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

// Methods
const startTimer = () => {
  timerInterval = setInterval(() => {
    if (timeLeft.value > 0) {
      timeLeft.value--
    } else {
      selectAnswer(null) // Temps écoulé
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

  // Calculer le temps pris pour répondre
  const timeTaken = 30 - timeLeft.value
  playerTime.value += timeTaken

  // GÉNÉRER LE TEMPS DE L'ADVERSAIRE pour cette question
  const opponentTime = Math.floor(Math.random() * 25) + 3 // Entre 3 et 28 secondes
  
  // Vérifier si la réponse est correcte
  let isCorrect = false
  let selectedAnswerText = 'Pas de réponse'
  let pointsEarned = 0
  
  if (index !== null && currentQuestion.value?.answers[index]) {
    isCorrect = currentQuestion.value.answers[index].correct
    selectedAnswerText = currentQuestion.value.answers[index].text
    
    if (isCorrect) {
      playerScore.value++
      
      // SYSTÈME DE POINTS AVEC BONUS DE RAPIDITÉ
      const basePoints = 100
      let speedBonus = 0
      
      // BONUS SEULEMENT SI TU ES PLUS RAPIDE QUE L'ADVERSAIRE
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
      
      // AFFICHER LE POPUP DES POINTS
      if (speedBonus > 0) {
        pointsPopupText.value = `+${pointsEarned} PTS!\n(+${speedBonus} bonus rapidité vs adversaire)`
      } else {
        pointsPopupText.value = `+${pointsEarned} PTS\n(Adversaire était plus rapide)`
      }
      
      showPointsPopup.value = true
      
      setTimeout(() => {
        showPointsPopup.value = false
      }, 2500)
      
      console.log(`🎯 Bonne réponse !`)
      console.log(`⏱️ Ton temps: ${timeTaken}s | Adversaire: ${opponentTime}s`)
      console.log(`🏆 ${basePoints} points + ${speedBonus} bonus = ${pointsEarned} points`)
    } else {
      pointsPopupText.value = `0 PTS\n(Mauvaise réponse)`
      showPointsPopup.value = true
      
      setTimeout(() => {
        showPointsPopup.value = false
      }, 2000)
      
      console.log('❌ Mauvaise réponse, 0 points')
      console.log(`⏱️ Ton temps: ${timeTaken}s | Adversaire: ${opponentTime}s`)
    }
  }
  
  // Sauvegarder la réponse du joueur avec les temps
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
  
  console.log('📝 Answer recorded:', {
    question: currentQuestion.value?.text,
    answer: selectedAnswerText,
    correct: isCorrect,
    playerTime: timeTaken,
    opponentTime: opponentTime,
    fasterThanOpponent: timeTaken < opponentTime,
    points: pointsEarned
  })
  
  // Attendre 2.5 secondes pour voir les couleurs et le popup
  setTimeout(() => {
    nextQuestion()
  }, 2500)
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
  
  // Calculer le score total du joueur avec les points de rapidité
  const playerTotalPoints = playerAnswers.value.reduce((total, answer) => total + answer.points, 0)
  
  // Générer des réponses pour l'adversaire EN UTILISANT LES TEMPS DÉJÀ GÉNÉRÉS
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
  
  // SAUVEGARDER LE MATCH DANS LA BASE DE DONNÉES
  try {
    const matchData = {
      player1_id: 1, // ID du joueur connecté
      player2_id: opponent.value.id,
      player1_score: playerScore.value,
      player2_score: opponentScore.value,
      player1_time: playerTime.value,
      player2_time: opponentTime.value,
      player1_points: playerTotalPoints,
      player2_points: opponentTotalPoints,
      winner_id: playerTotalPoints > opponentTotalPoints ? 1 : opponent.value.id,
      questions_data: JSON.stringify(questions.value.map(q => ({
        id: q.id,
        text: q.content_default,
        correctAnswer: q.choices?.find(c => c.is_correct)?.text_answer || q.choices?.find(c => c.is_correct)?.text
      }))),
      player1_answers: JSON.stringify(playerAnswers.value),
      player2_answers: JSON.stringify(opponentAnswers)
    }
    
    console.log('💾 Sauvegarde du match dans la base...')
    const savedMatch = await battleService.saveMatch(matchData)
    console.log('✅ Match sauvegardé avec ID:', savedMatch.id)
    
    // Sauvegarder les résultats pour BattleDetailsView
    const battleResults = {
      battleId: savedMatch.id,
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
    router.push(`/battle-details/${savedMatch.id}`)
    
  } catch (error) {
    console.error('❌ Erreur sauvegarde match:', error)
    
    // Fallback
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
  
  const index = player.id % gradients.length
  return {
    background: gradients[index]
  }
}

// Lifecycle
onMounted(() => {
  loadBattleData()
  
  setTimeout(() => {
    if (questions.value.length > 0) {
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
  padding: 2rem 1rem;
  margin-bottom: 2rem;
}

.opponent-info,
.player-info {
  display: flex;
  align-items: center;
  gap: 1rem;
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
  }
  
  .avatar {
    width: 50px;
    height: 50px;
    font-size: 1.2rem;
  }
  
  .vs-indicator {
    font-size: 1.5rem;
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