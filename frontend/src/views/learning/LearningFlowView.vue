<template>
  <div class="learning-flow">
    <!-- Loading State -->
    <div v-if="isLoading" class="loading-container">
      <div class="loading-spinner"></div>
      <p>Chargement...</p>
    </div>

    <!-- Theory View -->
    <TheoryView 
      v-else-if="currentView === 'theory'"
      :theory-data="currentLesson.theory"
      :current-step="currentStep"
      :total-steps="totalSteps"
      @next-step="goToQuestion"
    />
    
    <!-- Multiple Choice Quiz -->
    <QuizView 
      v-else-if="currentView === 'quiz' && currentLesson.question.type === 'multiple-choice'"
      :question-data="currentLesson.question"
      :current-step="currentStep"
      :total-steps="totalSteps"
      @next-step="nextLesson"
      @answer-selected="handleAnswer"
    />
    
    <!-- True/False Quiz -->
    <TrueFalseQuestionView 
      v-else-if="currentView === 'quiz' && currentLesson.question.type === 'true-false'"
      :question-data="currentLesson.question"
      :current-step="currentStep"
      :total-steps="totalSteps"
      @next-step="nextLesson"
      @answer-selected="handleAnswer"
    />
    
    <!-- Matching Quiz -->
    <MatchingQuestionView 
      v-else-if="currentView === 'quiz' && currentLesson.question.type === 'matching'"
      :question-data="currentLesson.question"
      :current-step="currentStep"
      :total-steps="totalSteps"
      @next-step="nextLesson"
      @answer-selected="handleAnswer"
    />
    
    <!-- Drag & Drop Quiz -->
    <DragDropQuestionView 
      v-else-if="currentView === 'quiz' && currentLesson.question.type === 'drag-drop'"
      :question-data="currentLesson.question"
      :current-step="currentStep"
      :total-steps="totalSteps"
      @next-step="nextLesson"
      @answer-selected="handleAnswer"
    />

    <!-- Lesson Completed Screen -->
    <LessonCompletedView 
      v-else-if="currentView === 'completed'"
      :lesson-number="completedLessonNumber"
      :score="lessonScore"
      :correct-answers="correctAnswersCount"
      :total-questions="totalQuestionsCount"
      @finish="handleLessonFinish"
      @next-lesson="startNextLesson"
      @back-to-menu="backToMenu"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { learningService } from '@/services/api'
import TheoryView from './TheoryView.vue'
import QuizView from './QuizView.vue'
import TrueFalseQuestionView from './TrueFalseQuestionView.vue'
import MatchingQuestionView from './MatchingQuestionView.vue'
import DragDropQuestionView from './DragDropQuestionView.vue'
import LessonCompletedView from './LessonCompletedView.vue'

// Props pour recevoir l'ID de la leçon depuis la route
const props = defineProps({
  lessonId: {
    type: [Number, String],
    default: 1
  }
})

// Reactive data
const currentView = ref('theory')
const currentStep = ref(1)
const lessonIndex = ref(0)
const isLoading = ref(true)
const lessons = ref([])

// Tracking pour la progression
const lessonAnswers = ref([])
const completedLessonNumber = ref(1)

// Computed
const currentLesson = computed(() => lessons.value[lessonIndex.value] || { theory: {}, question: {} })
const totalSteps = computed(() => lessons.value.length * 2)

const correctAnswersCount = computed(() => 
  lessonAnswers.value.filter(answer => answer.correct).length
)

const totalQuestionsCount = computed(() => lessonAnswers.value.length)

const lessonScore = computed(() => {
  if (totalQuestionsCount.value === 0) return 100
  return Math.round((correctAnswersCount.value / totalQuestionsCount.value) * 100)
})

// ⚠️ NOUVEAU : Définir l'ordre des types de questions pour alternance
const questionTypes = ['multiple-choice', 'true-false', 'matching', 'drag-drop']

// ⚠️ NOUVEAU : Fonction pour obtenir le type de question selon l'index
const getQuestionTypeForIndex = (index) => {
  return questionTypes[index % questionTypes.length]
}

// ⚠️ MODIFIÉ : Fonction pour convertir les données DB avec alternance des types
const transformDBDataToLessonFormat = (theoriesData) => {
  return theoriesData.map((theoryItem, theoryIndex) => {
    const theory = {
      title: `THÉORIE ${theoryItem.id}`,
      content: theoryItem.content.split('\n').filter(line => line.trim())
    }

    // Prendre la première question de cette théorie
    const firstQuestion = theoryItem.questions[0]
    
    // ⚠️ NOUVEAU : Déterminer le type selon l'index plutôt que selon les données DB
    const questionType = getQuestionTypeForIndex(theoryIndex)
    
    let question = {}

    if (firstQuestion) {
      // ⚠️ MODIFIÉ : Créer différents types selon l'alternance
      switch (questionType) {
        case 'multiple-choice':
          question = {
            type: 'multiple-choice',
            question: firstQuestion.content_default || generateDefaultQuestion(theoryItem, 'multiple-choice'),
            answers: firstQuestion.choices?.length > 0 
              ? firstQuestion.choices.map(choice => ({
                  text: choice.text_answer,
                  correct: choice.id === firstQuestion.correct_answer_text?.correct_choice_id
                }))
              : generateDefaultMultipleChoice(theoryItem)
          }
          break

        case 'true-false':
          question = {
            type: 'true-false',
            question: firstQuestion.content_if_TF || generateDefaultQuestion(theoryItem, 'true-false'),
            correctAnswer: firstQuestion.correct_answer_text?.true_false ? 'true' : 'false'
          }
          break

        case 'matching':
          question = {
            type: 'matching',
            instruction: firstQuestion.content_default || generateDefaultQuestion(theoryItem, 'matching'),
            pairs: firstQuestion.choices?.length >= 4 
              ? transformChoicesToPairs(firstQuestion.choices)
              : generateDefaultMatchingPairs(theoryItem)
          }
          break

        case 'drag-drop':
          question = {
            type: 'drag-drop',
            question: firstQuestion.content_if_blank || generateDefaultQuestion(theoryItem, 'drag-drop'),
            watches: generateWatchOptions(),
            correctAnswer: Math.floor(Math.random() * 4) // Random pour l'exemple
          }
          break
      }
    }

    return { theory, question }
  })
}

// ⚠️ NOUVEAU : Fonctions pour générer du contenu par défaut
const generateDefaultQuestion = (theoryItem, questionType) => {
  const baseContent = theoryItem.content.substring(0, 100) + "..."
  
  switch (questionType) {
    case 'multiple-choice':
      return `Concernant cette théorie, quelle affirmation est correcte ?`
    case 'true-false':
      return `Cette affirmation sur Breitling est-elle vraie : "${baseContent}"`
    case 'matching':
      return `Associez les éléments liés à cette théorie`
    case 'drag-drop':
      return `Quel modèle correspond le mieux à cette description ?`
    default:
      return `Question sur la théorie ${theoryItem.id}`
  }
}

const generateDefaultMultipleChoice = (theoryItem) => {
  return [
    { text: "Cette information est exacte", correct: true },
    { text: "Cette information est partiellement vraie", correct: false },
    { text: "Cette information est obsolète", correct: false },
    { text: "Cette information est incorrecte", correct: false }
  ]
}

const generateDefaultMatchingPairs = (theoryItem) => {
  return [
    { left: "Breitling", right: "Aviation" },
    { left: "Calibre", right: "Mouvement" },
    { left: "Collection", right: "Modèles" },
    { left: "Héritage", right: "Histoire" }
  ]
}

// Fonction helper pour transformer les choix en paires pour le matching
const transformChoicesToPairs = (choices) => {
  const pairs = []
  for (let i = 0; i < choices.length; i += 2) {
    if (choices[i + 1]) {
      pairs.push({
        left: choices[i].text_answer,
        right: choices[i + 1].text_answer
      })
    }
  }
  return pairs
}

// Fonction helper pour générer des options de montres
const generateWatchOptions = () => {
  return [
    { name: "Premier", image: "/images/watches/premier.jpg" },
    { name: "Navitimer", image: "/images/watches/navitimer.jpg" },
    { name: "Superocean", image: "/images/watches/superocean.jpg" },
    { name: "Avenger", image: "/images/watches/avenger.jpg" }
  ]
}

// Charger les données depuis la DB
const loadLessonData = async () => {
  try {
    isLoading.value = true
    
    // Récupérer les théories de la leçon
    const response = await learningService.getLessonTheories(props.lessonId)
    const theoriesData = response.data || []
    
    // Transformer les données en format attendu avec alternance
    lessons.value = transformDBDataToLessonFormat(theoriesData)
    
    console.log('Données chargées avec alternance:', lessons.value)
    console.log('Types de questions:', lessons.value.map(lesson => lesson.question.type))
  } catch (error) {
    console.error('Erreur lors du chargement:', error)
    // Garder les données de fallback en cas d'erreur
    lessons.value = getFallbackLessons()
  } finally {
    isLoading.value = false
  }
}

// ⚠️ MODIFIÉ : Données de fallback avec alternance
const getFallbackLessons = () => {
  const fallbackTheories = [
    {
      id: 1,
      content: "Robuste et audacieuse, l'Avenger est inspirée de l'héritage aéronautique de Breitling. Relancée en 2019, elle est ultralegible même avec des gants."
    },
    {
      id: 2, 
      content: "Le Navitimer est l'une des montres d'aviation les plus emblématiques. Créé en 1952, il dispose d'une règle à calcul circulaire."
    },
    {
      id: 3,
      content: "La collection Premier incarne l'élégance de Breitling. Elle allie sophistication et précision technique."
    },
    {
      id: 4,
      content: "La Superocean est dédiée aux sports aquatiques. Elle offre une étanchéité exceptionnelle et une lisibilité parfaite sous l'eau."
    }
  ]

  return transformDBDataToLessonFormat(fallbackTheories)
}

// Methods (inchangés)
const goToQuestion = () => {
  currentView.value = 'quiz'
  currentStep.value++
}

const nextLesson = () => {
  if (lessonIndex.value < lessons.value.length - 1) {
    lessonIndex.value++
    currentView.value = 'theory'
    currentStep.value++
  } else {
    completedLessonNumber.value = lessonIndex.value + 1
    currentView.value = 'completed'
    console.log('Toutes les leçons terminées!')
  }
}

const handleAnswer = (answerData) => {
  console.log('Answer selected:', answerData)
  
  lessonAnswers.value.push({
    questionIndex: answerData.questionIndex || lessonIndex.value,
    correct: answerData.correct || answerData.allCorrect || false,
    data: answerData
  })
}

const handleLessonFinish = () => {
  console.log('Lesson finished with score:', lessonScore.value)
}

const startNextLesson = () => {
  lessonAnswers.value = []
  
  if (lessonIndex.value < lessons.value.length - 1) {
    lessonIndex.value++
    currentView.value = 'theory'
    currentStep.value++
  } else {
    console.log('Toutes les leçons terminées!')
  }
}

const backToMenu = () => {
  console.log('Back to menu')
}

// Charger les données au montage du composant
onMounted(() => {
  loadLessonData()
})

console.log('LearningFlowView component loaded')
</script>

<style scoped>
.learning-flow {
  width: 100vw;
  min-height: 100vh;
}

.loading-container {
  width: 100vw;
  min-height: 100vh;
  background: linear-gradient(135deg, #072C54 0%, #1e3a8a 100%);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: white;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid rgba(255, 255, 255, 0.3);
  border-top: 4px solid #F7C72C;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>