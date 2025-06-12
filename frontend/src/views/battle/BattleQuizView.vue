<template>
  <div class="battle-quiz-page">
    <!-- Battle Header Info -->
    <div class="battle-header">
      <!-- UTILISATEUR AUTHENTIFIÉ -->

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
        <div class="progress-fill" :style="{ width: progressPercentage + '%' }"></div>
      </div>
      <div class="question-counter">{{ currentQuestionIndex + 1 }} / {{ totalQuestions }}</div>
    </div>

    <!-- Timer -->
    <div class="timer-container">
      <div class="timer" :class="{ warning: timeLeft <= 5 }">{{ timeLeft }}s</div>
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

// Initialisation par défaut plus neutre
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

// Récupérer l'URL de l'avatar
const getAvatarUrl = (user) => {
  if (!user || !user.avatar) {
    return null
  }

  // Si c'est juste une lettre (fallback), ne pas afficher d'image
  if (typeof user.avatar === 'string' && user.avatar.length === 1) {
    return null
  }

  // Construire l'URL complète
  const avatarUrl = user.avatar.startsWith('http') ? user.avatar : `http://localhost:8000/${user.avatar}`
  return avatarUrl
}

// Récupérer les données utilisateur actuelles
const loadCurrentUserData = async () => {
  try {

    // 1. Récupérer l'utilisateur authentifié
    const userResponse = await fetch('http://localhost:8000/api/user', {
      credentials: 'include',
      headers: { Accept: 'application/json' }
    })

    if (!userResponse.ok) {
      throw new Error('Failed to fetch authenticated user')
    }

    const userData = await userResponse.json()

    // 2. Récupérer les données complètes via API (AVEC POS)
    const fullUserResponse = await fetch(`http://localhost:8000/api/v1/users/${userData.id}`, {
      credentials: 'include',
      headers: { Accept: 'application/json' }
    })
    
    let fullUserData = userData
    if (fullUserResponse.ok) {
      const fullUserResponseData = await fullUserResponse.json()
      fullUserData = fullUserResponseData.data || fullUserResponseData || userData
    }


    currentPlayer.value = {
      id: fullUserData.id || userData.id,
      name: fullUserData.username || userData.username || 'YOU',
      avatar: fullUserData.avatar || userData.avatar || null,
      flag: getUserFlag(fullUserData) || '🇨🇭'
    }

    
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

const getUserFlag = (userData) => {
  if (userData.pos && userData.pos.country_flag) {
    return userData.pos.country_flag
  }


  if (userData.pos_id) {
    const flagFromPosId = getCountryFlag(userData.pos_id)
    return flagFromPosId
  }


  return '🇨🇭'
}

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


  return flagMapping[posId] || '🇨🇭'
}

const currentQuestion = computed(() => {
  if (questions.value.length === 0) {return null
}
  
  const question = questions.value[currentQuestionIndex.value]
  
  const formattedAnswers = question.choices?.map((choice, index) => ({
    text: choice.text_answer,
    correct: choice.is_correct
  })) || []

  const result = {
    id: question.id,
    text: question.content_default || question.content_if_TF || question.content_if_blank || 'Question sans contenu',
    answers: formattedAnswers
  }

  return result
})

const formatQuestions = async (questionsList) => {
  try {
    questions.value = questionsList.map((q, index) => {
      const choices = q.choices || []

      if (choices.length === 0) {
        console.warn(`Aucun choix pour question ${q.id}`)
        return {
          id: q.id,
          content_default: q.content_default,
          choices: []
        }
      }

      let correctAnswerTexts = []

      if (q.correct_answer_text) {
        try {
          if (typeof q.correct_answer_text === 'string') {
            correctAnswerTexts = JSON.parse(q.correct_answer_text)
          } else if (Array.isArray(q.correct_answer_text)) {
            correctAnswerTexts = q.correct_answer_text
          } else {
            correctAnswerTexts = [q.correct_answer_text]
          }
        } catch (e) {
          console.warn(' Erreur parsing correct_answer_text, fallback sur string direct')
          correctAnswerTexts = [q.correct_answer_text]
        }
      } else {
        console.warn(' Pas de correct_answer_text, essai avec correct_choice_id')
        const correctChoice = choices.find(c => parseInt(c.id) === parseInt(q.correct_choice_id))
        if (correctChoice) {
          correctAnswerTexts = [correctChoice.text_answer]
        } else {
          console.error(' Aucune méthode pour identifier la bonne réponse !')
          correctAnswerTexts = []
        }
      }

      const formattedChoices = choices.map((choice, choiceIndex) => {
        const isCorrect = correctAnswerTexts.includes(choice.text_answer)

        return {
          id: choice.id,
          text_answer: choice.text_answer,
          is_correct: isCorrect
        }
      })

      const correctChoices = formattedChoices.filter(c => c.is_correct)

      if (correctChoices.length === 0) {
        console.error(` AUCUNE réponse correcte pour question ${q.id} !`)
        console.error(' correct_answer_text:', correctAnswerTexts)
        console.error(' Choix disponibles:', choices.map(c => c.text_answer))

        if (formattedChoices.length > 0) {
          formattedChoices[0].is_correct = true
          console.warn(' FALLBACK: Premier choix marqué comme correct')
        }
      } else if (correctChoices.length > 1) {
        console.error(` PLUSIEURS réponses correctes pour question ${q.id} !`)
      }

      // Mélanger les choix
      const shuffledChoices = formattedChoices.sort(() => 0.5 - Math.random())

      return {
        id: q.id,
        content_default: q.content_default,
        choices: shuffledChoices
      }
    })

    totalQuestions.value = questions.value.length

  } catch (error) {
    console.error(' Erreur formatage:', error)
    loadFallbackQuestions()
  }
}

const loadBattleData = async () => {
  try {
    const savedBattle = localStorage.getItem('currentBattle')
    if (savedBattle) {
      battleData.value = JSON.parse(savedBattle)


      if (battleData.value.opponent) {
        opponent.value = {
          id: battleData.value.opponent.id,
          name: battleData.value.opponent.name,
          avatar: battleData.value.opponent.avatar,
          flag: battleData.value.opponent.flag || '🇩🇪'
        }
      }
    }

    await loadQuestionsFromAPI()

  } catch (error) {
    console.error(' Error loading battle data:', error)
    await loadQuestionsFromAPI()
  }
}

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
}

const loadSpecificQuestions = async (questionIds) => {
  try {
    if (!Array.isArray(questionIds) || questionIds.length === 0) {
      throw new Error('IDs de questions invalides')
    }

    const questionsData = await battleService.getQuestions()
    const allQuestions = questionsData.data || questionsData || []

    if (allQuestions.length === 0) {
      throw new Error('Aucune question disponible dans l\'API')
    }

    const specificQuestions = questionIds.map(id => {
      const found = allQuestions.find(q => q.id === id)
      return found
    }).filter(Boolean)

    if (specificQuestions.length === 0) {
      throw new Error('Aucune question spécifique trouvée')
    }

    await formatQuestions(specificQuestions)

  } catch (error) {
    console.error(' Erreur lors du chargement des questions spécifiques:', error)
    console.warn('Fallback sur questions aléatoires')

    try {
      const questionsData = await battleService.getQuestions()
      const allQuestions = questionsData.data || questionsData || []

      if (allQuestions.length > 0) {
        const shuffled = allQuestions.sort(() => 0.5 - Math.random())
        const selectedQuestions = shuffled.slice(0, 5)
        await formatQuestions(selectedQuestions)
      } else {
        throw new Error('Aucune question disponible')
      }
    } catch (fallbackError) {
      console.error(' Erreur fallback:', fallbackError)
      loadFallbackQuestions()
    }
  }
}

const loadQuestionsFromAPI = async () => {
  try {
    const questionsData = await battleService.getQuestions()
    const allQuestions = questionsData.data || questionsData || []

    if (allQuestions.length === 0) {
      console.warn('⚠️ Aucune question API, utilisation du fallback')
      loadFallbackQuestions()
      return
    }

    const superShuffleArray = (array) => {
      const shuffled = [...array]

      for (let i = shuffled.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1))
        ;[shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]]
      }

      const seed = Date.now() % 1000
      for (let i = shuffled.length - 1; i > 0; i--) {
        const randomFactor1 = Math.random()
        const randomFactor2 = Math.sin(seed + i)
        const randomFactor3 = (new Date().getMilliseconds()) / 1000
        const combinedRandom = Math.abs(randomFactor1 + randomFactor2 + randomFactor3) % 1
        const j = Math.floor(combinedRandom * (i + 1))
        ;[shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]]
      }

      const chunkSize = Math.max(1, Math.floor(shuffled.length / 10))
      for (let start = 0; start < shuffled.length; start += chunkSize) {
        const end = Math.min(start + chunkSize, shuffled.length)
        const chunk = shuffled.slice(start, end)

        const rotateBy = Math.floor(Math.random() * chunk.length)
        const rotatedChunk = [...chunk.slice(rotateBy), ...chunk.slice(0, rotateBy)]

        for (let i = 0; i < rotatedChunk.length; i++) {
          shuffled[start + i] = rotatedChunk[i]
        }
      }

      return shuffled
    }

    const superShuffled = superShuffleArray(allQuestions)

    let selectedQuestions = []

    if (superShuffled.length >= 20) {
      const step = Math.floor(superShuffled.length / 10)
      const usedIndices = new Set()

      while (selectedQuestions.length < 5 && usedIndices.size < superShuffled.length) {
        const sectionStart = (selectedQuestions.length * step) % (superShuffled.length - step)
        const randomInSection = Math.floor(Math.random() * step)
        const candidateIndex = sectionStart + randomInSection

        if (!usedIndices.has(candidateIndex) && candidateIndex < superShuffled.length) {
          selectedQuestions.push(superShuffled[candidateIndex])
          usedIndices.add(candidateIndex)
        }
      }

      while (selectedQuestions.length < 5) {
        const randomIndex = Math.floor(Math.random() * superShuffled.length)
        if (!usedIndices.has(randomIndex)) {
          selectedQuestions.push(superShuffled[randomIndex])
          usedIndices.add(randomIndex)
        }
      }
    } else if (superShuffled.length >= 10) {
      const indices = []
      const spacing = Math.floor(superShuffled.length / 5)

      for (let i = 0; i < 5; i++) {
        const baseIndex = i * spacing
        const randomOffset = Math.floor(Math.random() * Math.min(spacing, superShuffled.length - baseIndex))
        indices.push((baseIndex + randomOffset) % superShuffled.length)
      }

      const uniqueIndices = [...new Set(indices)]
      selectedQuestions = uniqueIndices.slice(0, 5).map(index => superShuffled[index])

      while (selectedQuestions.length < 5 && selectedQuestions.length < superShuffled.length) {
        const randomIndex = Math.floor(Math.random() * superShuffled.length)
        const candidate = superShuffled[randomIndex]
        if (!selectedQuestions.some(q => q.id === candidate.id)) {
          selectedQuestions.push(candidate)
        }
      }
    } else {
      selectedQuestions = superShuffled.slice(0, Math.min(5, superShuffled.length))
    }

    await formatQuestions(selectedQuestions)

  } catch (error) {
    console.error('Erreur lors du chargement des questions depuis API:', error)
    console.warn('Fallback sur questions par défaut')
    loadFallbackQuestions()
  }
}

const startTimer = () => {
  if (timerInterval) {
    clearInterval(timerInterval)
    timerInterval = null
  }

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
  if (hasAnswered.value) {
    return
  }

  // VÉRIFICATIONS DE BASE
  if (!currentQuestion.value) {
    console.error(' currentQuestion.value est null/undefined')
    return
  }

  if (!currentQuestion.value.answers) {
    console.error('currentQuestion.value.answers est null/undefined')
    return
  }

  if (index !== null && index >= currentQuestion.value.answers.length) {
    console.error(`Index ${index} invalide (max: ${currentQuestion.value.answers.length - 1})`)
    return
  }
  
  selectedAnswer.value = index
  hasAnswered.value = true
  stopTimer()

  const timeTaken = 30 - timeLeft.value
  playerTime.value += timeTaken

  let pointsEarned = 0
  let selectedAnswerText = 'Temps écoulé'
  let isCorrect = false
  let speedBonus = 0

  if (index !== null) {
    const selectedAnswerObj = currentQuestion.value.answers[index]
    isCorrect = selectedAnswerObj.correct === true
    selectedAnswerText = selectedAnswerObj.text
  }

  if (isCorrect) {
    playerScore.value++

    const basePoints = 100

    // CALCUL DU BONUS DE RAPIDITÉ
    if (timeTaken <= 5) {
      speedBonus = 100
    } else if (timeTaken <= 10) {
      speedBonus = 75
    } else if (timeTaken <= 15) {
      speedBonus = 50
    } else if (timeTaken <= 20) {
      speedBonus = 25
    } else {
      speedBonus = 0
    }

    pointsEarned = basePoints + speedBonus

    // MESSAGES DE BONUS
    if (speedBonus >= 75) {
      pointsPopupText.value = ` +${pointsEarned} PTS!\n(+${speedBonus} bonus rapidité)`
    } else if (speedBonus >= 25) {
      pointsPopupText.value = ` +${pointsEarned} PTS!\n(+${speedBonus} bonus rapidité)`
    } else if (speedBonus > 0) {
      pointsPopupText.value = `+${pointsEarned} PTS!\n(+${speedBonus} bonus rapidité)`
    } else {
      pointsPopupText.value = ` +${pointsEarned} PTS\n(Bonne réponse !)`
    }

    showPointsPopup.value = true
    setTimeout(() => showPointsPopup.value = false, 2500)
  } else {
    // speedBonus reste à 0 pour les mauvaises réponses
    pointsEarned = 0

    if (index === null) {
      pointsPopupText.value = `0 PTS\n(Temps écoulé !)`
    } else if (timeTaken <= 5) {
      pointsPopupText.value = ` 0 PTS\n(Trop rapide, mauvaise réponse !)`
    } else if (timeTaken >= 25) {
      pointsPopupText.value = ` 0 PTS\n(Temps presque écoulé...)`
    } else {
      pointsPopupText.value = ` 0 PTS\n(Mauvaise réponse)`
    }

    showPointsPopup.value = true
    setTimeout(() => (showPointsPopup.value = false), 2000)
  }
// Sauvegarder la réponse
  playerAnswers.value.push({
    questionId: currentQuestion.value?.id,
    questionText: currentQuestion.value?.text,
    selectedAnswer: selectedAnswerText,
    correct: isCorrect,
    time: timeTaken,
    timeLeft: timeLeft.value,
    points: pointsEarned,
    speedCategory: getSpeedCategory(timeTaken),
    speedBonus: speedBonus
  })
// Passage à la question suivante
  setTimeout(() => {
    try {
      nextQuestion()
    } catch (error) {
      console.error(' Erreur dans nextQuestion():', error)
      // Fallback en cas d'erreur
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
  }, 2500)
}

const getSpeedCategory = (timeTaken) => {
  if (timeTaken <= 5) return 'lightning'
  if (timeTaken <= 10) return 'fast'
  if (timeTaken <= 15) return 'good'
  if (timeTaken <= 20) return 'average'
  if (timeTaken <= 25) return 'slow'
  return 'very_slow'
}

const nextQuestion = () => {
  if (currentQuestionIndex.value < totalQuestions.value - 1) {
    currentQuestionIndex.value++
    timeLeft.value = 30
    hasAnswered.value = false
    selectedAnswer.value = null

    const nextQ = questions.value[currentQuestionIndex.value]
    if (nextQ) {
      startTimer()
    } else {
      console.error(` Question ${currentQuestionIndex.value} introuvable !`)
      finishBattle()
    }
  } else {
    finishBattle()
  }
}

const finishBattle = async () => {
  stopTimer()

  const playerTotalPoints = playerAnswers.value.reduce((total, answer) => total + answer.points, 0)

  const playerAverageTime = playerAnswers.value.length > 0
    ? playerTime.value / playerAnswers.value.length
    : 0


  const perfectAnswers = playerAnswers.value.filter(a => a.correct && a.time <= 10).length

      const goodAnswers = playerAnswers.value.filter(a => a.correct && a.time <= 20).length
  try {
    const existingBattleId = battleData.value?.id

    if (!existingBattleId) {
      throw new Error('ID de bataille manquant')
    }

    await fetch('http://localhost:8000/sanctum/csrf-cookie', {
      credentials: 'include'
    })

    const iAmChallenger = battleData.value?.isFirstPlayer === false

    let updateData = {}

    if (iAmChallenger) {
      updateData = {
        has_challenger_won: null,
        challenger_summary: {
          score: playerScore.value,
          totalPoints: playerTotalPoints,
          totalTime: playerTime.value,
          averageTime: playerAverageTime,
          perfectAnswers: perfectAnswers,
          goodAnswers: goodAnswers,
          answers: playerAnswers.value.map(answer => ({
            questionId: answer.questionId,
            selectedAnswer: answer.selectedAnswer,
            correct: answer.correct,
            time: answer.time,
            points: answer.points,
            speedCategory: answer.speedCategory
          })),
          questionsData: questions.value.map(q => ({
            id: q.id,
            text: q.content_default,
            correctAnswer: q.choices?.find(c => c.is_correct)?.text_answer || 'Réponse correcte'
          }))
        }
      }
    } else {
      updateData = {
        has_challenger_won: null,
        challenged_summary: {
          score: playerScore.value,
          totalPoints: playerTotalPoints,
          totalTime: playerTime.value,
          averageTime: playerAverageTime,
          perfectAnswers: perfectAnswers,
          goodAnswers: goodAnswers,
          answers: playerAnswers.value.map(answer => ({
            questionId: answer.questionId,
            selectedAnswer: answer.selectedAnswer,
            correct: answer.correct,
            time: answer.time,
            points: answer.points,
            speedCategory: answer.speedCategory
          })),
          questionsData: questions.value.map(q => ({
            id: q.id,
            text: q.content_default,
            correctAnswer: q.choices?.find(c => c.is_correct)?.text_answer || 'Réponse correcte'
          }))
        }
      }
    }

    const csrfToken = decodeURIComponent(
      document.cookie
        .split('; ')
        .find((row) => row.startsWith('XSRF-TOKEN='))
        ?.split('=')[1] ?? ''
    )

    const response = await fetch(`http://localhost:8000/api/v1/battles/${existingBattleId}`, {
      method: 'PUT',
      credentials: 'include',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-XSRF-TOKEN': csrfToken
      },
      body: JSON.stringify(updateData)
    })

    if (!response.ok) {
      const errorText = await response.text()
      throw new Error(`API Error: ${response.status} - ${errorText}`)
    }

    const updatedBattle = await response.json()
    const battle = updatedBattle.data || updatedBattle

    const bothPlayersFinished = battle.challenger_summary && battle.challenged_summary

    if (bothPlayersFinished) {
      await router.push(`/battle-details/${existingBattleId}`)
    } else {
      await router.push('/battle')
    }
    
  } catch (error) {
    console.error('Erreur lors de la sauvegarde:', error)
    alert(`Erreur: ${error.message}`)
    router.push('/battle')
  }
}

const getAnswerClass = (index) => {
  if (!hasAnswered.value) return ''
  
  const selectedAnswerObj = currentQuestion.value?.answers[index]
  const isSelectedAnswer = selectedAnswer.value === index
  const isCorrectAnswer = selectedAnswerObj?.correct === true

  if (isSelectedAnswer) {
    return isCorrectAnswer ? 'correct' : 'incorrect'
  }
  
  if (isCorrectAnswer) {
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

onMounted(async () => {
  await loadCurrentUserData()
  
  await loadBattleData()

  setTimeout(() => {
    if (questions.value.length > 0) {
      startTimer()
    } else {
      console.error(' Aucune question disponible pour démarrer le timer')
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
  background: linear-gradient(135deg, #072c54 0%, #1e3a8a 100%);
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

/* UTILISATEUR AUTHENTIFIÉ  */
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

/* ADVERSAIRE  */
.opponent-info {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex: 1;
  justify-content: flex-end;
}

.opponent-details {
  text-align: right;
  order: -1;
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
  border: 3px solid #f7c72c;
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
  color: #f7c72c;
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
  background: linear-gradient(90deg, #f7c72c 0%, #e6b625 100%);
  border-radius: 4px;
  transition: width 0.3s ease;
}

.question-counter {
  font-size: 1.1rem;
  font-weight: 600;
  color: #f7c72c;
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
  color: #f7c72c;
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
  color: #ff4444;
  animation: pulse 0.5s ease-in-out infinite alternate;
}

@keyframes pulse {
  from {
    transform: scale(1);
  }
  to {
    transform: scale(1.1);
  }
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
  border-color: #f7c72c;
  transform: translateY(-2px);
}

.answer-btn:disabled {
  cursor: not-allowed;
}

/* COULEURS DES RÉPONSES AMÉLIORÉES */
.answer-btn.correct {
  background: rgba(76, 175, 80, 0.4) !important;
  border-color: #4caf50 !important;
  color: white !important;
  box-shadow: 0 0 15px rgba(76, 175, 80, 0.5);
  animation: correctPulse 0.6s ease-out;
}

.answer-btn.incorrect {
  background: rgba(244, 67, 54, 0.4) !important;
  border-color: #f44336 !important;
  color: white !important;
  box-shadow: 0 0 15px rgba(244, 67, 54, 0.5);
  animation: incorrectShake 0.6s ease-out;
}

.answer-btn.correct-answer {
  background: rgba(76, 175, 80, 0.2) !important;
  border-color: #4caf50 !important;
  color: #4caf50 !important;
  animation: correctGlow 0.6s ease-out;
}

.answer-btn.disabled {
  opacity: 0.4 !important;
  background: rgba(255, 255, 255, 0.05) !important;
  border-color: rgba(255, 255, 255, 0.1) !important;
}

/* ANIMATIONS */
@keyframes correctPulse {
  0% {
    transform: scale(1);
    box-shadow: 0 0 0 rgba(76, 175, 80, 0.5);
  }
  50% {
    transform: scale(1.05);
    box-shadow: 0 0 20px rgba(76, 175, 80, 0.8);
  }
  100% {
    transform: scale(1);
    box-shadow: 0 0 15px rgba(76, 175, 80, 0.5);
  }
}

@keyframes incorrectShake {
  0%,
  100% {
    transform: translateX(0);
  }
  25% {
    transform: translateX(-10px);
  }
  75% {
    transform: translateX(10px);
  }
}

@keyframes correctGlow {
  0% {
    box-shadow: 0 0 0 rgba(76, 175, 80, 0);
  }
  50% {
    box-shadow: 0 0 15px rgba(76, 175, 80, 0.6);
  }
  100% {
    box-shadow: 0 0 10px rgba(76, 175, 80, 0.3);
  }
}

/* AFFICHAGE DES POINTS GAGNÉS */
.points-popup {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: rgba(247, 199, 44, 0.95);
  color: #072c54;
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
  border: 3px solid #072c54;
}

.points-popup.speed-bonus {
  background: linear-gradient(135deg, #4caf50 0%, #f7c72c 100%);
  color: white;
  border-color: #4caf50;
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
  0% {
    opacity: 0;
    transform: translate(-50%, -50%) scale(0.5);
  }
  20% {
    opacity: 1;
    transform: translate(-50%, -50%) scale(1.1);
  }
  80% {
    opacity: 1;
    transform: translate(-50%, -50%) scale(1);
  }
  100% {
    opacity: 0;
    transform: translate(-50%, -50%) scale(0.9);
  }
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
  border-top: 4px solid #f7c72c;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem auto;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
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
    flex-direction: row-reverse;
  }

  .opponent-details {
    text-align: left;
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
