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

      <!-- ADVERSAIRE -->
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
    
    let fullUserData = userData 
    if (fullUserResponse.ok) {
      const fullUserResponseData = await fullUserResponse.json()
      fullUserData = fullUserResponseData.data || fullUserResponseData || userData
      console.log('📋 Full user data from API:', fullUserData)
    } else {
      console.warn('⚠️ Could not fetch full user data, using basic auth data')
    }
    
    currentPlayer.value = {
      id: fullUserData.id || userData.id,
      name: fullUserData.username || userData.username || 'YOU',
      avatar: fullUserData.avatar || userData.avatar || null,
      flag: getUserFlag(fullUserData) || '🇨🇭' 
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


const getUserFlag = (userData) => {

  if (userData.pos && userData.pos.country_flag) {
    console.log('✅ Flag from pos.country_flag:', userData.pos.country_flag)
    return userData.pos.country_flag
  }
  

  if (userData.pos_id) {
    const flagFromPosId = getCountryFlag(userData.pos_id)
    console.log('⚠️ Fallback flag from pos_id mapping:', flagFromPosId)
    return flagFromPosId
  }
  
  console.log('❌ No flag found, using default')
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
  
  console.log('🚩 Converting pos_id to flag:', posId, '->', flagMapping[posId])
  return flagMapping[posId] || '🇨🇭'
}


const currentQuestion = computed(() => {
  if (questions.value.length === 0) {
    console.log('❌ Aucune question disponible')
    return null
  }
  
  const question = questions.value[currentQuestionIndex.value]
  console.log('🎯 Question actuelle BRUTE:', question)
  

  const result = {
    id: question.id,
    text: question.content_default || question.content_if_TF || question.content_if_blank || 'Question sans contenu',
    answers: formattedAnswers
  }
  
  console.log('✅ Question FINALE pour affichage:', result)
  console.log('🔍 === RÉSUMÉ DES RÉPONSES ===')
  result.answers.forEach((answer, index) => {
    console.log(`   ${index}: "${answer.text}" = ${answer.correct ? '✅ CORRECT' : '❌ incorrect'}`)
  })
  
  return result
})

// CORRIGER formatQuestions() - UTILISER correct_answer_text au lieu de correct_choice_id
const formatQuestions = async (questionsList) => {
  try {
    console.log('🔧 === FORMATAGE QUESTIONS AVEC correct_answer_text ===')
    console.log('📋 Questions reçues:', questionsList)
    
    questions.value = questionsList.map((q, index) => {
      console.log(`\n📝 === QUESTION ${index + 1} ===`)
      console.log(`🆔 ID: ${q.id}`)
      console.log(`📝 Contenu: ${q.content_default}`)
      console.log(`🎯 correct_answer_text: ${q.correct_answer_text}`)
      console.log(`🎯 correct_choice_id: ${q.correct_choice_id}`)
      
      const choices = q.choices || []
      console.log(`📋 Choix reçus (${choices.length}):`, choices)
      
      if (choices.length === 0) {
        console.warn(`⚠️ Aucun choix pour question ${q.id}`)
        return {
          id: q.id,
          content_default: q.content_default,
          choices: []
        }
      }
      
      // UTILISER correct_answer_text (format JSON string ou array)
      let correctAnswerTexts = []
      
      if (q.correct_answer_text) {
        try {
          // Si c'est un string JSON, le parser
          if (typeof q.correct_answer_text === 'string') {
            correctAnswerTexts = JSON.parse(q.correct_answer_text)
          } else if (Array.isArray(q.correct_answer_text)) {
            correctAnswerTexts = q.correct_answer_text
          } else {
            correctAnswerTexts = [q.correct_answer_text]
          }
        } catch (e) {
          console.warn('⚠️ Erreur parsing correct_answer_text, fallback sur string direct')
          correctAnswerTexts = [q.correct_answer_text]
        }
      } else {
        console.warn('⚠️ Pas de correct_answer_text, essai avec correct_choice_id')
        // Fallback sur correct_choice_id si pas de correct_answer_text
        const correctChoice = choices.find(c => parseInt(c.id) === parseInt(q.correct_choice_id))
        if (correctChoice) {
          correctAnswerTexts = [correctChoice.text_answer]
        } else {
          console.error('❌ Aucune méthode pour identifier la bonne réponse !')
          correctAnswerTexts = []
        }
      }
      
      console.log(`✅ Réponses correctes identifiées:`, correctAnswerTexts)
      
      const formattedChoices = choices.map((choice, choiceIndex) => {
        // COMPARAISON avec correct_answer_text
        const isCorrect = correctAnswerTexts.includes(choice.text_answer)
        
        console.log(`\n📝 Choix ${choiceIndex}:`)
        console.log(`   - ID: ${choice.id}`)
        console.log(`   - Texte: "${choice.text_answer}"`)
        console.log(`   - Comparaison avec correct_answer_text: ${isCorrect}`)
        console.log(`   - Résultat: ${isCorrect ? '✅ CORRECT' : '❌ incorrect'}`)
        
        return {
          id: choice.id,
          text_answer: choice.text_answer,
          is_correct: isCorrect // BOOLEAN STRICT basé sur correct_answer_text
        }
      })
      
      // VÉRIFICATION : une seule réponse correcte
      const correctChoices = formattedChoices.filter(c => c.is_correct)
      console.log(`\n🔍 Vérification question ${index + 1}:`)
      console.log(`   - Choix corrects trouvés: ${correctChoices.length}`)
      
      if (correctChoices.length === 0) {
        console.error(`❌ AUCUNE réponse correcte pour question ${q.id} !`)
        console.error('❌ correct_answer_text:', correctAnswerTexts)
        console.error('❌ Choix disponibles:', choices.map(c => c.text_answer))
        
        // FALLBACK : marquer le premier choix comme correct pour éviter le crash
        if (formattedChoices.length > 0) {
          formattedChoices[0].is_correct = true
          console.warn('⚠️ FALLBACK: Premier choix marqué comme correct')
        }
      } else if (correctChoices.length > 1) {
        console.error(`❌ PLUSIEURS réponses correctes pour question ${q.id} !`)
      } else {
        console.log(`✅ Une seule réponse correcte: "${correctChoices[0].text_answer}"`)
      }
      
      // Mélanger les choix
      const shuffledChoices = formattedChoices.sort(() => 0.5 - Math.random())
      
      return {
        id: q.id,
        content_default: q.content_default,
        choices: shuffledChoices,
        debug_correct_answer_text: correctAnswerTexts,
        debug_original_correct_choice_id: q.correct_choice_id
      }
    })
    
    totalQuestions.value = questions.value.length
    console.log(`\n✅ === FORMATAGE TERMINÉ ===`)
    console.log(`📊 Total questions formatées: ${questions.value.length}`)
    
    // VÉRIFICATION FINALE
    questions.value.forEach((q, index) => {
      const correctChoices = q.choices.filter(c => c.is_correct)
      console.log(`🔍 Question ${index + 1}: ${correctChoices.length} réponse(s) correcte(s)`)
      if (correctChoices.length === 1) {
        console.log(`✅ Bonne réponse: "${correctChoices[0].text_answer}"`)
      }
    })
    
  } catch (error) {
    console.error('❌ Erreur formatage:', error)
    loadFallbackQuestions()
  }
}

// Computed
const progressPercentage = computed(() => {
  if (totalQuestions.value === 0) return 0
  return (currentQuestionIndex.value / totalQuestions.value) * 100
})


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
        
        console.log('✅ Opponent data loaded:', opponent.value)
        console.log('🖼️ Opponent avatar:', battleData.value.opponent.avatar)
      }
    }
    
    // TOUJOURS charger les questions depuis l'API (pas depuis localStorage)
    await loadQuestionsFromAPI()
    
  } catch (error) {
    console.error('❌ Error loading battle data:', error)
    await loadQuestionsFromAPI() // Fallback sur l'API
  }
}

// AJOUTER la fonction loadFallbackQuestions qui manque
const loadFallbackQuestions = () => {
  console.log('🔄 Chargement des questions de fallback...')
  
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
  console.log('✅ Questions de fallback chargées:', questions.value.length)
}

// AJOUTER la fonction loadSpecificQuestions qui manque aussi
const loadSpecificQuestions = async (questionIds) => {
  try {
    console.log('🎯 Chargement des questions spécifiques:', questionIds)
    
    if (!Array.isArray(questionIds) || questionIds.length === 0) {
      throw new Error('IDs de questions invalides')
    }
    
    // Récupérer toutes les questions depuis l'API
    const questionsData = await battleService.getQuestions()
    console.log('📋 Toutes les questions depuis API:', questionsData)
    
    const allQuestions = questionsData.data || questionsData || []
    console.log('📊 Questions disponibles dans l\'API:', allQuestions.length)
    
    if (allQuestions.length === 0) {
      throw new Error('Aucune question disponible dans l\'API')
    }
    
    // Filtrer les questions par leurs IDs dans l'ordre donné
    const specificQuestions = questionIds.map(id => {
      const found = allQuestions.find(q => q.id === id)
      console.log(`🔍 Recherche question ID ${id}:`, found ? 'TROUVÉE' : 'NON TROUVÉE')
      return found
    }).filter(Boolean) // Enlever les undefined
    
    console.log(`🎯 Questions spécifiques trouvées: ${specificQuestions.length}/${questionIds.length}`)
    
    if (specificQuestions.length === 0) {
      throw new Error('Aucune question spécifique trouvée')
    }
    
    // Formater les questions trouvées
    await formatQuestions(specificQuestions)
    
  } catch (error) {
    console.error('❌ Erreur lors du chargement des questions spécifiques:', error)
    console.warn('⚠️ Fallback sur questions aléatoires')
    
    // Fallback : charger des questions aléatoirement
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
      console.error('❌ Erreur fallback:', fallbackError)
      loadFallbackQuestions()
    }
  }
}

// CORRIGER loadQuestionsFromAPI() avec une gestion d'erreur plus robuste
const loadQuestionsFromAPI = async () => {
  try {
    console.log('🔄 Chargement des questions pour cette bataille...')
    console.log('🎯 battleData:', battleData.value)
    
    // Vérifier si on a des questions fixes pour cette bataille (IDs)
    if (battleData.value?.questions && Array.isArray(battleData.value.questions) && battleData.value.questions.length > 0) {
      console.log('🎯 Utilisation des questions fixes de la bataille:', battleData.value.questions)
      await loadSpecificQuestions(battleData.value.questions)
      return
    }
    
    // Vérifier si l'autre joueur a déjà joué (questions complètes dans son summary)
    if (battleData.value?.existingQuestions && Array.isArray(battleData.value.existingQuestions) && battleData.value.existingQuestions.length > 0) {
      console.log('🔄 Utilisation des questions déjà jouées par l\'adversaire')
      
      // CORRIGER : Reformater les questions existantes avec la même structure
      questions.value = battleData.value.existingQuestions.map((q, index) => ({
        id: q.id || index + 1,
        content_default: q.text || `Question ${index + 1}`,
        choices: [
          { text_answer: q.correctAnswer, is_correct: true },
          { text_answer: 'Réponse B', is_correct: false },
          { text_answer: 'Réponse C', is_correct: false },
          { text_answer: 'Réponse D', is_correct: false }
        ]
      }))
      
      totalQuestions.value = questions.value.length
      console.log('✅ Questions formatées depuis l\'adversaire:', questions.value.length)
      return
    }
    
    // Fallback : charger 5 questions aléatoirement depuis l'API
    console.log('🎲 Chargement de 5 questions aléatoirement depuis l\'API...')
    const questionsData = await battleService.getQuestions()
    console.log('📋 Réponse API questions:', questionsData)
    
    const allQuestions = questionsData.data || questionsData || []
    console.log('📊 Questions disponibles:', allQuestions.length)
    
    if (allQuestions.length === 0) {
      console.warn('⚠️ Aucune question API, utilisation du fallback')
      loadFallbackQuestions()
      return
    }
    
    // Sélectionner 5 questions aléatoirement
    const shuffled = allQuestions.sort(() => 0.5 - Math.random())
    const selectedQuestions = shuffled.slice(0, 5)
    
    console.log('🎯 Questions sélectionnées:', selectedQuestions.length)
    await formatQuestions(selectedQuestions)
    
  } catch (error) {
    console.error('❌ Erreur lors du chargement des questions depuis API:', error)
    console.warn('⚠️ Fallback sur questions par défaut')
    loadFallbackQuestions()
  }
}

// Methods (le reste des méthodes reste identique...)
const startTimer = () => {
  console.log('⏰ Démarrage du timer...')
  
  // Nettoyer l'ancien timer si il existe
  if (timerInterval) {
    clearInterval(timerInterval)
    timerInterval = null
  }
  
  timerInterval = setInterval(() => {
    if (timeLeft.value > 0) {
      timeLeft.value--
    } else {
      console.log('⏰ Temps écoulé ! Sélection automatique de null')
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

// CORRIGER selectAnswer() - Déclarer speedBonus au début
const selectAnswer = (index) => {
  if (hasAnswered.value) {
    console.log('⚠️ Réponse déjà donnée, ignoré')
    return
  }
  
  console.log('\n🎯 === DÉBUT SÉLECTION RÉPONSE ===')
  console.log(`🎯 Index cliqué: ${index}`)
  
  // VÉRIFICATIONS DE BASE
  if (!currentQuestion.value) {
    console.error('❌ currentQuestion.value est null/undefined')
    return
  }
  
  if (!currentQuestion.value.answers) {
    console.error('❌ currentQuestion.value.answers est null/undefined')
    return
  }
  
  if (index !== null && index >= currentQuestion.value.answers.length) {
    console.error(`❌ Index ${index} invalide (max: ${currentQuestion.value.answers.length - 1})`)
    return
  }
  
  // Arrêter le timer et marquer comme répondu
  selectedAnswer.value = index
  hasAnswered.value = true
  stopTimer()
  
  console.log(`✅ hasAnswered défini à: ${hasAnswered.value}`)
  console.log(`✅ selectedAnswer défini à: ${selectedAnswer.value}`)

  const timeTaken = 30 - timeLeft.value
  playerTime.value += timeTaken
  
  // DÉCLARER TOUTES LES VARIABLES AU DÉBUT
  let pointsEarned = 0
  let selectedAnswerText = 'Temps écoulé'
  let isCorrect = false
  let speedBonus = 0 // ✅ DÉCLARER ICI POUR ÉVITER L'ERREUR
  
  // Gérer le cas où l'utilisateur a répondu (index !== null)
  if (index !== null) {
    const selectedAnswerObj = currentQuestion.value.answers[index]
    isCorrect = selectedAnswerObj.correct === true
    selectedAnswerText = selectedAnswerObj.text
    
    console.log(`🔍 Réponse sélectionnée: "${selectedAnswerText}"`)
    console.log(`🔍 Est correcte: ${isCorrect}`)
  } else {
    console.log('⏰ Temps écoulé - aucune réponse sélectionnée')
  }
  
  if (isCorrect) {
    console.log('🎉 === BONNE RÉPONSE ===')
    playerScore.value++
    
    const basePoints = 100
    
    // CALCUL DU BONUS DE RAPIDITÉ
    if (timeTaken <= 5) {
      speedBonus = 100 // ⚡ Super rapide
    } else if (timeTaken <= 10) {
      speedBonus = 75  // 🔥 Très rapide
    } else if (timeTaken <= 15) {
      speedBonus = 50  // ⚡ Rapide
    } else if (timeTaken <= 20) {
      speedBonus = 25  // 👍 Correct
    } else {
      speedBonus = 0   // 😐 Lent
    }
    
    pointsEarned = basePoints + speedBonus
    
    // MESSAGES DE BONUS
    if (speedBonus >= 75) {
      pointsPopupText.value = `🔥 +${pointsEarned} PTS!\n(+${speedBonus} bonus rapidité)`
    } else if (speedBonus >= 25) {
      pointsPopupText.value = `⚡ +${pointsEarned} PTS!\n(+${speedBonus} bonus rapidité)`
    } else if (speedBonus > 0) {
      pointsPopupText.value = `👍 +${pointsEarned} PTS!\n(+${speedBonus} bonus rapidité)`
    } else {
      pointsPopupText.value = `✅ +${pointsEarned} PTS\n(Bonne réponse !)`
    }
    
    showPointsPopup.value = true
    setTimeout(() => showPointsPopup.value = false, 2500)
  } else {
    console.log('💥 === MAUVAISE RÉPONSE OU TEMPS ÉCOULÉ ===')
    
    // speedBonus reste à 0 pour les mauvaises réponses
    pointsEarned = 0
    
    if (index === null) {
      pointsPopupText.value = `⏰ 0 PTS\n(Temps écoulé !)`
    } else if (timeTaken <= 5) {
      pointsPopupText.value = `💨 0 PTS\n(Trop rapide, mauvaise réponse !)`
    } else if (timeTaken >= 25) {
      pointsPopupText.value = `🐌 0 PTS\n(Temps presque écoulé...)`
    } else {
      pointsPopupText.value = `❌ 0 PTS\n(Mauvaise réponse)`
    }
    
    showPointsPopup.value = true
    setTimeout(() => showPointsPopup.value = false, 2000)
  }
  
  // Sauvegarder la réponse (maintenant speedBonus est toujours défini)
  playerAnswers.value.push({
    questionId: currentQuestion.value?.id,
    questionText: currentQuestion.value?.text,
    selectedAnswer: selectedAnswerText,
    correct: isCorrect,
    time: timeTaken,
    timeLeft: timeLeft.value,
    points: pointsEarned,
    speedCategory: getSpeedCategory(timeTaken),
    speedBonus: speedBonus // ✅ MAINTENANT TOUJOURS DÉFINI
  })
  
  console.log('📊 Réponse sauvegardée:', playerAnswers.value[playerAnswers.value.length - 1])
  
  // Passage à la question suivante
  console.log('⏳ Programmation du passage à la question suivante dans 2.5s...')
  
  setTimeout(() => {
    console.log('🔄 Exécution du passage à la question suivante')
    try {
      nextQuestion()
    } catch (error) {
      console.error('❌ Erreur dans nextQuestion():', error)
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
  
  console.log('🎯 === FIN SÉLECTION RÉPONSE ===\n')
}

// NOUVELLE FONCTION : Catégoriser la rapidité
const getSpeedCategory = (timeTaken) => {
  if (timeTaken <= 5) return 'lightning' // ⚡ Éclair
  if (timeTaken <= 10) return 'fast' // 🔥 Rapide
  if (timeTaken <= 15) return 'good' // ⚡ Bien
  if (timeTaken <= 20) return 'average' // 👍 Moyen
  if (timeTaken <= 25) return 'slow' // 😐 Lent
  return 'very_slow' // 🐌 Très lent
}

const nextQuestion = () => {
  console.log('\n🔄 === PASSAGE À LA QUESTION SUIVANTE ===')
  console.log(`📊 Index actuel: ${currentQuestionIndex.value}`)
  console.log(`📊 Total questions: ${totalQuestions.value}`)
  console.log(`📊 Questions restantes: ${totalQuestions.value - currentQuestionIndex.value - 1}`)
  
  if (currentQuestionIndex.value < totalQuestions.value - 1) {
    console.log('➡️ Passage à la question suivante...')
    
    // Réinitialiser l'état
    currentQuestionIndex.value++
    timeLeft.value = 30
    hasAnswered.value = false
    selectedAnswer.value = null
    
    console.log(`✅ Nouvelle question index: ${currentQuestionIndex.value}`)
    console.log(`✅ Timer réinitialisé: ${timeLeft.value}s`)
    console.log(`✅ hasAnswered réinitialisé: ${hasAnswered.value}`)
    
    // Vérifier que la prochaine question existe
    const nextQ = questions.value[currentQuestionIndex.value]
    if (nextQ) {
      console.log(`✅ Prochaine question trouvée: "${nextQ.content_default}"`)
      startTimer()
    } else {
      console.error(`❌ Question ${currentQuestionIndex.value} introuvable !`)
      console.error('❌ Questions disponibles:', questions.value.length)
      finishBattle()
    }
  } else {
    console.log('🏁 Dernière question terminée, fin de bataille')
    finishBattle()
  }
}

const finishBattle = async () => {
  stopTimer()
  
  const playerTotalPoints = playerAnswers.value.reduce((total, answer) => total + answer.points, 0)
  const playerAverageTime = playerAnswers.value.length > 0 
    ? playerTime.value / playerAnswers.value.length 
    : 0
  
  // STATISTIQUES PERSONNELLES (pas de comparaison adversaire)
  const perfectAnswers = playerAnswers.value.filter(a => a.correct && a.time <= 10).length
  const goodAnswers = playerAnswers.value.filter(a => a.correct && a.time <= 20).length
  
  console.log('📊 === STATISTIQUES FINALES ===')
  console.log(`✅ Score: ${playerScore.value}/${totalQuestions.value}`)
  console.log(`⚡ Réponses parfaites (≤10s): ${perfectAnswers}`)
  console.log(`👍 Bonnes réponses (≤20s): ${goodAnswers}`)
  console.log(`📈 Points totaux: ${playerTotalPoints}`)
  console.log(`⏱️ Temps moyen: ${playerAverageTime.toFixed(1)}s`)
  
  try {
    const existingBattleId = battleData.value?.id
    console.log('🆔 Mise à jour de la bataille:', existingBattleId)
    
    if (!existingBattleId) {
      throw new Error('ID de bataille manquant')
    }

    await fetch('http://localhost:8000/sanctum/csrf-cookie', {
      credentials: 'include'
    })

    // Déterminer si je suis challenger ou challenged
    const iAmChallenger = battleData.value?.isFirstPlayer === false
    
    // Préparer les données selon mon rôle
    let updateData = {}
    
    if (iAmChallenger) {
      // Je suis le challenger, j'ajoute challenger_summary
      updateData = {
        has_challenger_won: null,
        challenger_summary: {
          score: playerScore.value,
          totalPoints: playerTotalPoints,
          totalTime: playerTime.value,
          averageTime: playerAverageTime,
          perfectAnswers: perfectAnswers, // NOUVEAU
          goodAnswers: goodAnswers, // NOUVEAU
          answers: playerAnswers.value.map(answer => ({
            questionId: answer.questionId,
            selectedAnswer: answer.selectedAnswer,
            correct: answer.correct,
            time: answer.time,
            points: answer.points,
            speedCategory: answer.speedCategory // NOUVEAU
          })),
          questionsData: questions.value.map(q => ({
            id: q.id,
            text: q.content_default,
            correctAnswer: q.choices?.find(c => c.is_correct)?.text_answer || 'Réponse correcte'
          }))
        }
      }
    } else {
      // Je suis le challenged, j'ajoute challenged_summary
      updateData = {
        has_challenger_won: null,
        challenged_summary: {
          score: playerScore.value,
          totalPoints: playerTotalPoints,
          totalTime: playerTime.value,
          averageTime: playerAverageTime,
          perfectAnswers: perfectAnswers, // NOUVEAU
          goodAnswers: goodAnswers, // NOUVEAU
          answers: playerAnswers.value.map(answer => ({
            questionId: answer.questionId,
            selectedAnswer: answer.selectedAnswer,
            correct: answer.correct,
            time: answer.time,
            points: answer.points,
            speedCategory: answer.speedCategory // NOUVEAU
          })),
          questionsData: questions.value.map(q => ({
            id: q.id,
            text: q.content_default,
            correctAnswer: q.choices?.find(c => c.is_correct)?.text_answer || 'Réponse correcte'
          }))
        }
      }
    }
    
    console.log('💾 Données de mise à jour:', updateData)

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
    
    console.log('✅ Tour terminé:', battle)
    
    // VÉRIFIER si la bataille est maintenant terminée
    const bothPlayersFinished = battle.challenger_summary && battle.challenged_summary
    
    if (bothPlayersFinished) {
      console.log('🏁 Bataille terminée ! Redirection vers BattleDetails')
      await router.push(`/battle-details/${existingBattleId}`)
    } else {
      console.log('⏳ En attente de l\'autre joueur, retour à Battle')
      await router.push('/battle')
    }
    
  } catch (error) {
    console.error('❌ Erreur lors de la sauvegarde:', error)
    alert(`Erreur: ${error.message}`)
    router.push('/battle')
  }
}

// CORRIGER getAnswerClass() avec debug
const getAnswerClass = (index) => {
  if (!hasAnswered.value) return ''
  
  const selectedAnswerObj = currentQuestion.value?.answers[index]
  const isSelectedAnswer = selectedAnswer.value === index
  const isCorrectAnswer = selectedAnswerObj?.correct === true
  
  console.log(`🎨 Style pour réponse ${index}:`, {
    isSelected: isSelectedAnswer,
    isCorrect: isCorrectAnswer,
    answerObj: selectedAnswerObj,
    correctProperty: selectedAnswerObj?.correct,
    correctType: typeof selectedAnswerObj?.correct
  })
  
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

// Lifecycle - ORDRE DE CHARGEMENT MODIFIÉ
onMounted(async () => {
  console.log('🚀 BattleQuizView mounted')
  
  // 1. Charger les données du joueur actuel EN PREMIER
  await loadCurrentUserData()
  
  // 2. Ensuite charger les données de bataille (qui va charger les questions depuis l'API)
  await loadBattleData()
  
  // 3. Démarrer le timer après un délai
  setTimeout(() => {
    if (questions.value.length > 0) {
      console.log('⏰ Starting timer...')
      startTimer()
    } else {
      console.error('❌ Aucune question disponible pour démarrer le timer')
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