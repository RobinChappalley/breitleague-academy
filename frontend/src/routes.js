import FormationView from "@/views/learning/FormationView.vue";
import BattleView from "@/views/battle/BattleView.vue";
import HomeView from "@/views/HomeView.vue";
import RankingView from "@/views/rankings/RankingView.vue";
import ProfileView from "@/views/profile/ProfileView.vue";
import MissionsView from "@/views/missions/MissionsView.vue";
import LessonsListView from "@/views/learning/LessonsListView.vue";

export const routes = [
    {path: '/', component: FormationView, meta: {hideNavBar: false}},
    {path: '/battle', component: BattleView, meta:{hideNavBar: false}},
    {path: '/collection', component: HomeView, meta: {hideNavBar: false}},
    {path:'/ranking', component: RankingView, meta: {hideNavBar: false}},
    {path:'/profile', component: ProfileView, meta: {hideNavBar: false}},
    {path:'/ressources', component: LessonsListView, meta: {hideNavBar: false}},
    {path:'/missions', component: MissionsView, meta: {hideNavBar: true}}
]