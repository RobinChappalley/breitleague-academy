<script setup>
import NavigationMenue from './components/NavigationMenue.vue'
import { computed } from 'vue'
import {useRoute} from "vue-router";
const route = useRoute()

const showNavbar = computed(() => {
  // Par défaut affichée, sauf si meta.hideNavbar == true
  return !route.meta.hideNavBar
})

</script>




<template>
  <div id="app-layout">
    <router-view></router-view>

    <div class="main-content">
      <main>

      </main>

      <footer>
        <NavigationMenue v-if="showNavbar" />
      </footer>
    </div>
  </div>
</template>

<style scoped>

</style>
