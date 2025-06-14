import LoginView from '@/views/connection/LoginView.vue'
import FormationView from '@/views/learning/FormationView.vue'
import BattleView from '@/views/battle/BattleView.vue'
import BattleQuizView from '@/views/battle/BattleQuizView.vue'
import BattleDetailsView from '@/views/battle/BattleDetailsView.vue'
import HomeView from '@/views/HomeView.vue'
import RankingView from '@/views/rankings/RankingView.vue'
import ProfileView from '@/views/profile/ProfileView.vue'
import CollectionView from '@/views/collection/CollectionView.vue'
import MissionsView from '@/views/missions/MissionsView.vue'
import LessonsListView from '@/views/learning/LessonsListView.vue'
import CheckpointView from '@/views/learning/CheckpointView.vue'
import CheckpointQuizView from '@/views/learning/CheckpointQuizView.vue'
import CheckpointResultsView from '@/views/learning/CheckpointResultsView.vue'
import HistoireView from '@/views/learning/HistoireView.vue'
import LearningFlowView from "@/views/learning/LearningFlowView.vue";


export const routes = [
  { path: '/login', component: LoginView, meta: { hideNavBar: true } },
  { path: '/', component: FormationView, meta: { hideNavBar: false } },
  { path: '/battle', component: BattleView, meta: { hideNavBar: false } },
  { path: '/battle-quiz', component: BattleQuizView, meta: { hideNavBar: true } },
  { path: '/battle-details/:id?', component: BattleDetailsView, meta: { hideNavBar: false } },
  { path: '/collection', component: CollectionView, meta: { hideNavBar: false } },
  { path: '/ranking', component: RankingView, meta: { hideNavBar: false } },
  { path: '/profile', component: ProfileView, meta: { hideNavBar: false } },
  { path: '/ressources', component: LessonsListView, meta: { hideNavBar: true } },
  { path: '/missions', component: MissionsView, meta: { hideNavBar: true } },
  { path: '/checkpoint', component: CheckpointView, meta: { hideNavBar: false } },
  { path: '/checkpoint-quiz', component: CheckpointQuizView, meta: { hideNavBar: true } },
  {
    path: '/checkpoint-results',
    name: 'CheckpointResults',
    component: CheckpointResultsView,
    meta: { hideNavBar: true }
  },
  { path: '/ressources/history', component: HistoireView, meta: { hideNavBar: true } },
  { path: '/lesson/:lessonId', component: LearningFlowView, meta:{hideNavBar:true}, props: true },
  {
    path: '/:pathMatch(.*)*',
    redirect: (to) => {
      if (localStorage.getItem('isLoggedIn') === 'true') {
        return '/'
      } else {
        return '/login'
      }
    }
  }

]
