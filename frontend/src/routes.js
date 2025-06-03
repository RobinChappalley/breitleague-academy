import FormationView from "@/views/learning/FormationView.vue";
import BattleView from "@/views/battle/BattleView.vue";
import HomeView from "@/views/HomeView.vue";
import RankingView from "@/views/rankings/RankingView.vue";
import ProfileView from "@/views/profile/ProfileView.vue";

export const routes = [
    {path: '/', component: FormationView},
    {path: '/battle', component: BattleView},
    {path: '/collection', component: HomeView},
    {path:'/ranking', component: RankingView},
    {path:'/profile', component: ProfileView},


]