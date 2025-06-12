<template>
  <div class="quiz-page">
    <!-- Progress Bar -->
    <div class="progress-container">
      <div class="progress-bar">
        <div class="progress-fill" :style="{ width: `${progress}%` }"></div>
      </div>
    </div>

    <!-- Quiz Content -->
    <div class="quiz-content">
      <div class="quiz-container">
        <!-- Instructions -->
        <div class="question-section">
          <p class="question-text">{{ currentQuestion.instruction }}</p>
        </div>

        <!-- Matching Grid - 2 colonnes -->
        <div class="matching-grid" ref="matchingGrid">
          <!-- Colonne de gauche -->
          <div class="left-column">
            <div 
              v-for="(leftItem, index) in leftItems" 
              :key="'left-' + index"
              class="match-item left-item"
              :class="{ 
                'selected': selectedLeftIndex === index,
                'matched': leftItem.matched,
                'correct': leftItem.matched && leftItem.correct,
                'incorrect': leftItem.matched && !leftItem.correct,
                'can-unmatch': leftItem.matched && !leftItem.correct
              }"
              @click="selectLeft(index)"
            >
              {{ leftItem.text }}
              <!-- Indicateur de paire correcte -->
              <div v-if="leftItem.matched && leftItem.correct" class="match-indicator correct-indicator">
                ✓
              </div>
              <!-- Indicateur de paire incorrecte -->
              <div v-if="leftItem.matched && !leftItem.correct" class="match-indicator incorrect-indicator">
                ✗
              </div>
              <!-- Indicateur de paire en cours -->
              <div v-if="leftItem.matched && !showResult && !leftItem.correct && !leftItem.correct" class="match-indicator">
                {{ leftItem.matchId + 1 }}
              </div>
            </div>
          </div>

          <!-- Colonne de droite -->
          <div class="right-column">
            <div 
              v-for="(rightItem, index) in rightItems" 
              :key="'right-' + index"
              class="match-item right-item"
              :class="{ 
                'selected': selectedRightIndex === index,
                'matched': rightItem.matched,
                'correct': rightItem.matched && rightItem.correct,
                'incorrect': rightItem.matched && !rightItem.correct,
                'can-unmatch': rightItem.matched && !rightItem.correct
              }"
              @click="selectRight(index)"
            >
              {{ rightItem.text }}
              <!-- Indicateur de paire correcte -->
              <div v-if="rightItem.matched && rightItem.correct" class="match-indicator correct-indicator">
                ✓
              </div>
              <!-- Indicateur de paire incorrecte -->
              <div v-if="rightItem.matched && !rightItem.correct" class="match-indicator incorrect-indicator">
                ✗
              </div>
              <!-- Indicateur de paire en cours -->
              <div v-if="rightItem.matched && !showResult && !rightItem.correct && !rightItem.correct" class="match-indicator">
                {{ rightItem.matchId + 1 }}
              </div>
            </div>
          </div>
        </div>

        <!-- Validation Button -->
        <div class="button-section">
          <button
            class="cancel-btn"
            @click="resetQuestion"
          >
            ✕
          </button>
          <!-- Next Button - Toujours visible -->
          <button 
            class="next-btn"
            :class="{ 'disabled': !allCorrect }"
            @click="handleNext"
            :disabled="!allCorrect"
          >
            NEXT
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'

// Props
const props = defineProps({
  questionData: {
    type: Object,
    default: () => ({
      instruction: "Associez chaque montre à son calibre",
      pairs: [
        { left: "Navitimer", right: "B01" },
        { left: "Premier", right: "B09" },
        { left: "Superocean", right: "B20" },
        { left: "Avenger", right: "B13" }
      ]
    })
  },
  currentStep: {
    type: Number,
    default: 5
  },
  totalSteps: {
    type: Number,
    default: 10
  }
})

// Reactive data
const currentQuestion = ref(props.questionData)
const showResult = ref(false)
const selectedLeftIndex = ref(null)
const selectedRightIndex = ref(null)
const matches = ref([]) // Stocke les paires validées: [{ leftIndex: 0, rightIndex: 2, matchId: 0 }]
const matchingGrid = ref(null)
const router = useRouter()
const progress = computed(() => (props.currentStep / props.totalSteps) * 100)

// Préparer les items avec mélange de la colonne droite
const leftItems = ref(
  currentQuestion.value.pairs.map((pair, index) => ({
    text: pair.left,
    originalIndex: index,
    matched: false,
    correct: false,
    matchId: null
  }))
)

const rightItems = ref(
  [...currentQuestion.value.pairs]
    .sort(() => Math.random() - 0.5) // Mélanger
    .map((pair, index) => ({
      text: pair.right,
      originalIndex: currentQuestion.value.pairs.findIndex(p => p.right === pair.right),
      matched: false,
      correct: false,
      matchId: null
    }))
)

// Computed
const allMatched = computed(() => 
  matches.value.length === leftItems.value.length
)

// Computed - Garder seulement allCorrect
const allCorrect = computed(() => 
  matches.value.length === leftItems.value.length && 
  matches.value.every(match => match.correct)
)
const resetQuestion = () => {
 if (confirm('Êtes-vous sûr de vouloir quitter le quiz ? Votre progression sera perdue.')) {
    router.push('/')
  }
}

const correctPairsCount = computed(() => 
  matches.value.filter(match => {
    const leftOriginal = leftItems.value[match.leftIndex].originalIndex
    const rightOriginal = rightItems.value[match.rightIndex].originalIndex
    return leftOriginal === rightOriginal
  }).length
)

const totalPairs = computed(() => matches.value.length)

// Emit events
const emit = defineEmits(['next-step', 'answer-selected'])

// Methods
const createMatch = () => {
  const leftIndex = selectedLeftIndex.value
  const rightIndex = selectedRightIndex.value
  const matchId = matches.value.length // ID unique pour chaque paire
  
  // Vérifier immédiatement si la paire est correcte
  const leftOriginal = leftItems.value[leftIndex].originalIndex
  const rightOriginal = rightItems.value[rightIndex].originalIndex
  const isCorrect = leftOriginal === rightOriginal
  
  // Marquer comme appariés avec l'ID de match et le statut correct/incorrect
  leftItems.value[leftIndex].matched = true
  leftItems.value[leftIndex].matchId = matchId
  leftItems.value[leftIndex].correct = isCorrect
  rightItems.value[rightIndex].matched = true
  rightItems.value[rightIndex].matchId = matchId
  rightItems.value[rightIndex].correct = isCorrect
  
  // Ajouter la paire aux matches
  matches.value.push({ leftIndex, rightIndex, matchId, correct: isCorrect })
  
  // Reset selections
  selectedLeftIndex.value = null
  selectedRightIndex.value = null
  
  // Enlever complètement le déclenchement automatique
}

// Supprimer complètement la méthode checkAnswers

// Nouvelle méthode pour permettre de défaire une paire incorrecte
const selectLeft = (index) => {
  if (showResult.value) return
  
  // Si l'item est déjà apparié et incorrect, permettre de le désapparier
  if (leftItems.value[index].matched && !leftItems.value[index].correct) {
    unmatchPair(index, 'left')
    return
  }
  
  // Si l'item est déjà apparié et correct, ne rien faire
  if (leftItems.value[index].matched) return
  
  selectedLeftIndex.value = selectedLeftIndex.value === index ? null : index
  
  // Si on a une sélection de chaque côté, créer la paire
  if (selectedLeftIndex.value !== null && selectedRightIndex.value !== null) {
    createMatch()
  }
}

const selectRight = (index) => {
  if (showResult.value) return
  
  // Si l'item est déjà apparié et incorrect, permettre de le désapparier
  if (rightItems.value[index].matched && !rightItems.value[index].correct) {
    unmatchPair(index, 'right')
    return
  }
  
  // Si l'item est déjà apparié et correct, ne rien faire
  if (rightItems.value[index].matched) return
  
  selectedRightIndex.value = selectedRightIndex.value === index ? null : index
  
  // Si on a une sélection de chaque côté, créer la paire
  if (selectedLeftIndex.value !== null && selectedRightIndex.value !== null) {
    createMatch()
  }
}

// Nouvelle méthode pour défaire une paire incorrecte
const unmatchPair = (index, side) => {
  let matchToRemove
  
  if (side === 'left') {
    const matchId = leftItems.value[index].matchId
    matchToRemove = matches.value.find(match => match.matchId === matchId)
    
    // Réinitialiser les items
    leftItems.value[index].matched = false
    leftItems.value[index].correct = false
    leftItems.value[index].matchId = null
    
    // Trouver et réinitialiser l'item de droite correspondant
    const rightIndex = matchToRemove.rightIndex
    rightItems.value[rightIndex].matched = false
    rightItems.value[rightIndex].correct = false
    rightItems.value[rightIndex].matchId = null
  } else {
    const matchId = rightItems.value[index].matchId
    matchToRemove = matches.value.find(match => match.matchId === matchId)
    
    // Réinitialiser les items
    rightItems.value[index].matched = false
    rightItems.value[index].correct = false
    rightItems.value[index].matchId = null
    
    // Trouver et réinitialiser l'item de gauche correspondant
    const leftIndex = matchToRemove.leftIndex
    leftItems.value[leftIndex].matched = false
    leftItems.value[leftIndex].correct = false
    leftItems.value[leftIndex].matchId = null
  }
  
  // Supprimer la paire des matches
  const matchIndex = matches.value.findIndex(match => match.matchId === matchToRemove.matchId)
  if (matchIndex !== -1) {
    matches.value.splice(matchIndex, 1)
  }
}

const handleNext = () => {
  if (!allCorrect.value) return
  
  // Valider et passer à l'étape suivante directement
  emit('answer-selected', {
    questionIndex: props.currentStep - 1,
    matches: matches.value.map(match => ({
      left: leftItems.value[match.leftIndex].text,
      right: rightItems.value[match.rightIndex].text,
      correct: match.correct
    })),
    allCorrect: allCorrect.value,
    correct: allCorrect.value
  })
  
  emit('next-step')
}
</script>

<style scoped>
.quiz-page {
  width: 100vw;
  min-height: 100vh;
  background: linear-gradient(135deg, #072C54 0%, #1e3a8a 100%);
  color: white;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
}

/* PROGRESS BAR */
.progress-container {
  width: 100%;
  padding: 1rem 2rem 0 2rem;
  flex-shrink: 0;
}

.progress-bar {
  width: 100%;
  height: 6px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 3px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: #F7C72C;
  border-radius: 3px;
  transition: width 0.3s ease;
}

/* QUIZ CONTENT */
.quiz-content {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
}

.quiz-container {
  width: 100%;
  max-width: 700px;
}

/* QUESTION SECTION */
.question-section {
  margin-bottom: 2rem;
}

.question-text {
  font-size: 1.2rem;
  line-height: 1.6;
  color: white;
  text-align: center;
  margin: 0;
  padding: 0 1rem;
}

/* MATCHING GRID */
.matching-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
  margin-bottom: 2rem;
}

.left-column,
.right-column {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.match-item {
  background: rgba(255, 255, 255, 0.9);
  color: #072C54;
  padding: 1.2rem;
  border-radius: 8px;
  font-weight: 600;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 3px solid transparent;
  user-select: none;
  position: relative;
}

.match-item:hover:not(.matched) {
  background: rgba(255, 255, 255, 0.95);
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.match-item.selected {
  border-color: #F7C72C;
  background: rgba(247, 199, 44, 0.9);
  color: #072C54;
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(247, 199, 44, 0.4);
}

.match-item.matched:not(.correct):not(.incorrect) {
  cursor: default;
  background: rgba(255, 255, 255, 0.7);
  border-color: #ccc;
}

.match-item.correct {
  border-color: #22c55e ;
  background: rgba(34, 197, 94, 0.8) ;
  color: white;
}

.match-item.incorrect {
  border-color: #ef4444 ;
  background: rgba(239, 68, 68, 0.6) ;
  color: white;
  cursor: pointer ;
  position: relative;
  border-style: dashed ; /* Bordure en pointillés pour montrer que c'est modifiable */
}

.match-item.incorrect:hover {
  background: rgba(239, 68, 68, 0.8) ;
  transform: translateY(-2px) scale(1.02);
  box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
}

.match-item.can-unmatch {
  cursor: pointer;
  animation: gentle-pulse 3s infinite;
}

@keyframes gentle-pulse {
  0%, 100% { 
    transform: scale(1);
    box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4);
  }
  50% { 
    transform: scale(1.02);
    box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
  }
}

/* INDICATEURS DE PAIRE */
.match-indicator {
  position: absolute;
  top: -8px;
  right: -8px;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8rem;
  font-weight: 700;
  border: 2px solid white;
}

.match-indicator:not(.correct-indicator):not(.incorrect-indicator) {
  background: #F7C72C;
  color: #072C54;
}

.correct-indicator {
  background: #22c55e;
  color: white;
  font-size: 1rem;
}

.incorrect-indicator {
  background: #ef4444;
  color: white;
  font-size: 1rem;
  /* Enlever l'animation de rotation */
}

/* Enlever complètement @keyframes rotate */

/* BUTTONS */
.button-section {
  display: flex;
  justify-content: center;
  gap: 1rem;
}

.cancel-btn {
  background: #F7C72C;
  color: #072C54;
  border: none;
  width: 50px;
  height: 50px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
}


.check-btn,
.next-btn {
 background: #F7C72C;
  color: #072C54;
  border: none;
  padding: 1rem 3rem;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  text-transform: uppercase;
  letter-spacing: 1px;
  flex: 1;
  max-width: 300px;
  height: 50px;
}


.next-btn:hover:not(.disabled) {
  background: #E6B625;
  transform: translateY(-2px);
}

.next-btn.disabled {
  background: rgba(255, 255, 255, 0.3);
  color: rgba(255, 255, 255, 0.6);
  cursor: not-allowed;
  opacity: 0.5;
}

/* RESPONSIVE */
@media (max-width: 767px) {
  .matching-grid {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }
  
  .match-item {
    padding: 1rem;
    font-size: 0.9rem;
  }
  
  .question-text {
    font-size: 1rem;
  }
}
</style>