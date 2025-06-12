<template>
  <div class="learning-flow">
    <!-- Theory View -->
    <TheoryView 
      v-if="currentView === 'theory'"
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
import { ref, computed } from 'vue'
import TheoryView from './TheoryView.vue'
import QuizView from './QuizView.vue'
import TrueFalseQuestionView from './TrueFalseQuestionView.vue'
import MatchingQuestionView from './MatchingQuestionView.vue'
import DragDropQuestionView from './DragDropQuestionView.vue'
import LessonCompletedView from './LessonCompletedView.vue'

// Reactive data
const currentView = ref('theory') 
const currentStep = ref(1)
const lessonIndex = ref(0)

// Tracking pour la progression
const lessonAnswers = ref([]) 
const completedLessonNumber = ref(1)

// Sample lessons data avec différents types de questions
const lessons = ref([
  {
    theory: {
      title: "L'AVENGER",
      content: [
        "Robuste et audacieuse, l'Avenger est inspirée de l'héritage aéronautique de Breitling.",
        "Relancée en 2019, elle est ultralegible même avec des gants, et depuis 2022, elle embarque le calibre Breitling 01."
      ]
    },
    question: {
      type: "multiple-choice",
      question: "Quelle est la principale caractéristique de l'Avenger relancée en 2019 ?",
      answers: [
        { text: "Ultra-résistante", correct: false },
        { text: "Ultra-légible", correct: true },
        { text: "Ultra-mince", correct: false },
        { text: "Ultra-connectée", correct: false }
      ]
    }
  },
  {
    theory: {
      title: "LE NAVITIMER",
      content: [
        "Le Navitimer est l'icône absolue de Breitling depuis 1952.",
        "Cette montre d'aviateur emblématique combine chronographe et règle à calcul circulaire."
      ]
    },
    question: {
      type: "true-false",
      question: "Le Navitimer a été lancé en 1952.",
      correctAnswer: "true"
    }
  },
  {
    theory: {
      title: "LES COLLECTIONS",
      content: [
        "Breitling propose plusieurs collections emblématiques.",
        "Chaque collection a ses propres caractéristiques et calibres spécifiques."
      ]
    },
    question: {
      type: "matching",
      instruction: "Associez chaque montre à son calibre",
      pairs: [
        { left: "Navitimer", right: "B01" },
        { left: "Premier", right: "B09" },
        { left: "Superocean", right: "B20" },
        { left: "Avenger", right: "B13" }
      ]
    }
  },
  {
    theory: {
      title: "IDENTIFICATION",
      content: [
        "Savoir identifier les montres Breitling est essentiel.",
        "Chaque modèle a des caractéristiques visuelles distinctives."
      ]
    },
    question: {
      type: "drag-drop",
      question: "Quel modèle possède un calibre Breitling B23 ?",
      watches: [
        { name: "Premier", image: "/images/watches/premier.jpg" },
        { name: "Navitimer", image: "/images/watches/navitimer.jpg" },
        { name: "Superocean", image: "/images/watches/superocean.jpg" },
        { name: "Avenger", image: "/images/watches/avenger.jpg" }
      ],
      correctAnswer: 1
    }
  }
])

// Computed
const currentLesson = computed(() => lessons.value[lessonIndex.value])
const totalSteps = computed(() => lessons.value.length * 2)

// Computed pour le score de la leçon
const correctAnswersCount = computed(() => 
  lessonAnswers.value.filter(answer => answer.correct).length
)

const totalQuestionsCount = computed(() => lessonAnswers.value.length)

const lessonScore = computed(() => {
  if (totalQuestionsCount.value === 0) return 100
  return Math.round((correctAnswersCount.value / totalQuestionsCount.value) * 100)
})

// Methods
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

console.log('LearningFlowView component loaded')
</script>

<style scoped>
.learning-flow {
  width: 100vw;
  min-height: 100vh;
}
</style>