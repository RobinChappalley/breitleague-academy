<script setup>
import HelloWorld from './components/HelloWorld.vue'
import TheWelcome from './components/TheWelcome.vue'
import RankingView from './views/rankings/RankingView.vue'
import NavigationMenue from './components/NavigationMenue.vue'
</script>

<template>
  <main>
    <RankingView/>
  </main>

</script>

<template>
  <div id="app-layout">
  <div class="main-content">
    <main>
    </main>
    <footer>
      <NavigationMenue />
    </footer>
  </div>
</div>

</template>


<style scoped>
header {
  line-height: 1.5;
}

.logo {
  display: block;
  margin: 0 auto 2rem;
}

@media (min-width: 1024px) {
  header {
    display: flex;
    place-items: center;
    padding-right: calc(var(--section-gap) / 2);
  }

  .logo {
    margin: 0 2rem 0 0;
  }

  header .wrapper {
    display: flex;
    place-items: flex-start;
    flex-wrap: wrap;
  }
}
</style>
