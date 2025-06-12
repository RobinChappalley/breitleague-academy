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
const currentView = ref('theory') // 'theory', 'quiz', 'completed'
const currentStep = ref(1)
const lessonIndex = ref(0)
const lessonID = 1

// Tracking pour la progression
const lessonAnswers = ref(loadAnswersFromLocalStorage()) // CORRIGÉ: Fermeture de parenthèse manquante
const completedLessonNumber = ref(1)

// Sample lessons data avec différents types de questions
const lessons = ref([{
  theory: {
    title: "THE AVENGER",
    content: [
      "Robust and bold, the Avenger is inspired by Breitling’s aeronautical legacy.",
      "Reintroduced in 2019, it is ultra-legible even with gloves, and since 2022, it features the Breitling 01 caliber."
    ]
  },
  question: {
    type: "multiple-choice",
    question: "What is the main feature of the Avenger reintroduced in 2019?",
    answers: [
      { text: "Ultra-resistant", correct: false },
      { text: "Ultra-legible", correct: true },
      { text: "Ultra-thin", correct: false },
      { text: "Ultra-connected", correct: false }
    ]
  }
},
  {
    theory: {
      title: "THE NAVITIMER",
      content: [
        "The Navitimer has been Breitling’s ultimate icon since 1952.",
        "This legendary pilot’s watch combines chronograph and circular slide rule."
      ]
    },
    question: {
      type: "true-false",
      question: "The Navitimer was launched in 1952.",
      correctAnswer: "true"
    }
  },
  {
    theory: {
      title: "THE COLLECTIONS",
      content: [
        "Breitling offers several emblematic collections.",
        "Each collection has its own specific features and calibers."
      ]
    },
    question: {
      type: "matching",
      instruction: "Match each watch to its caliber",
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
        "Knowing how to identify Breitling watches is essential.",
        "Each model has distinctive visual characteristics."
      ]
    },
    question: {
      type: "drag-drop",
      question: "Which model features a Breitling B23 caliber?",
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

function saveAnswersToLocalStorage(answers) {
  localStorage.setItem('breitling-lesson-answers', JSON.stringify(answers));
}

function loadAnswersFromLocalStorage() {
  const saved = localStorage.getItem('breitling-lesson-answers');
  return saved ? JSON.parse(saved) : [];
}


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
    // Aller à la leçon suivante
    lessonIndex.value++
    currentView.value = 'theory'
    currentStep.value++
  } else {
    // Toutes les leçons terminées, montrer l'écran de completion
    completedLessonNumber.value = lessonIndex.value + 1
    currentView.value = 'completed'
    console.log('Toutes les leçons terminées!')
  }
}

const handleAnswer = (answerData) => {
  console.log('Answer selected:', answerData)
  console.log(currentLesson.value)
  console.log(lessonIndex.value)
  
  // Ajouter la réponse au tracking de la leçon
  lessonAnswers.value.push({
    lessonId:lessonID,
    questionIndex: answerData.questionIndex || lessonIndex.value,
    correct: answerData.correct || answerData.allCorrect || false,
    data: answerData
  })
  saveAnswersToLocalStorage(lessonAnswers.value)

}

const handleLessonFinish = () => {
  console.log('Lesson finished with score:', lessonScore.value)
}

const startNextLesson = () => {
  // Reset pour une nouvelle leçon
  lessonAnswers.value = []
  
  if (lessonIndex.value < lessons.value.length - 1) {
    lessonIndex.value++
    currentView.value = 'theory'
    currentStep.value++
  } else {
    console.log('Toutes les leçons terminées!')
    // Ici tu peux rediriger vers le menu principal
  }
}

const backToMenu = () => {
  // Rediriger vers le menu principal ou la liste des leçons
  console.log('Back to menu')
  // emit('back-to-menu') si tu veux communiquer avec le parent
}

console.log('LearningFlowView component loaded')
</script>

<style scoped>
.learning-flow {
  width: 100vw;
  min-height: 100vh;
}
</style>