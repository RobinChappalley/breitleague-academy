<template>
  <nav class="navigation-menu">
    <div class="logo" v-if="isDesktop">
      <img src="/images/icones/logo.png" alt="Breitleague Academy Logo"/>
    </div>
    <ul class="nav-list">
      <li
          v-for="item in navItems"
          :key="item.name"
      >
        <RouterLink :to="item.route" class="nav-link">
        <div class="active-bar"/>
        <img :src="item.icon" :alt="item.label"/>
        <span>{{ item.label }}</span>
        </RouterLink>
      </li>
    </ul>
  </nav>
</template>

<script setup>
import {ref, onMounted, onUnmounted} from 'vue'

import learningIcon from '/images/icones/learning.svg'
import battleIcon from '/images/icones/battle.svg'
import collectionIcon from '/images/icones/collection.svg'
import cupIcon from '/images/icones/cup.svg'
import profileIcon from '/images/icones/profile.svg'


const navItems = [
  {name: 'learning', label: 'Learning', icon: learningIcon, route: '/'},
  {name: 'battle', label: 'Battle', icon: battleIcon, route: '/battle'},
  {name: 'collections', label: 'Collections', icon: collectionIcon, route: '/collection'},
  {name: 'ranking', label: 'Ranking', icon: cupIcon, route: '/ranking'},
  {name: 'profile', label: 'Profile', icon: profileIcon, route: '/profile'},
]

const activeItem = ref('learning')
const isDesktop = ref(window.innerWidth >= 768)

function setActive(itemName) {
  activeItem.value = itemName
}

function handleResize() {
  isDesktop.value = window.innerWidth >= 768
}

onMounted(() => {
  window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
})
</script>

<style scoped>.navigation-menu {
  position: fixed;
  top: 0;
  left: 0;
  display: flex;
  flex-direction: column;
  width: 280px;
  height: 100vh;
  background-color: #001638;
  padding: 20px 0;
  border-radius: 0;
  z-index: 1000;
}

@media (min-width: 1440px) {
  .navigation-menu {
    width: 300px;
  }
}

.logo {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-bottom: 60px;
}

.logo img {
  width: 160px;
}

.nav-list {
  display: flex;
  flex-direction: column;
  gap: 28px;
  padding: 0;
  margin: 0;
}

.nav-list li {
  position: relative;
  padding: 0;
  margin: 0;
  list-style: none;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 20px;
  color: #3399ff;
  font-size: 18px;
  text-decoration: none;
  transition: background-color 0.3s, color 0.3s;
}

.nav-link span {
  font-weight: bold;
}

.nav-link:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.nav-link img {
  width: 28px;
  height: 28px;
  filter: brightness(0) saturate(100%) invert(48%) sepia(59%) saturate(1326%) hue-rotate(181deg) brightness(95%) contrast(95%);
}

.nav-link.router-link-exact-active {
  color: #F7C72C;
}

.nav-link.router-link-exact-active img {
  filter: brightness(0) saturate(100%) invert(79%) sepia(79%) saturate(415%) hue-rotate(340deg) brightness(100%) contrast(102%);
}

.active-bar {
  position: absolute;
  background-color: #fcd303;
  transition: transform 0.3s ease;
}

/* Desktop (≥768px) → barre à gauche */
@media (min-width: 768px) {
  .active-bar {
    top: 0;
    left: 0;
    width: 3px;
    height: 100%;
    transform: scaleY(0);
  }
  .nav-link.router-link-exact-active .active-bar {
    transform: scaleY(1);
  }
}

/* Mobile standard (<768px) → barre fixée en bas */
@media (max-width: 767px) {
  .navigation-menu {
    position: fixed;
    bottom: 0;
    top: auto;
    left: 0;
    width: 100%;
    height: 70px;
    flex-direction: row;
    justify-content: center;
    align-items: center;
    padding: 0;
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
    z-index: 1000;
  }

  .logo {
    display: none;
  }

  .nav-list {
    flex-direction: row;
    width: 100%;
    justify-content: space-evenly;
    gap: 0;
    margin: 0;
    padding: 0;
  }

  .nav-list li {
    flex: 1;
    text-align: center;
    max-width: calc(100% / 5);
    padding: 0;
    margin: 0;
    display: flex; /* nécessaire pour le centrage vertical si besoin */
  }

  .nav-link {
    flex-direction: column;
    gap: 4px;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    font-size: 12px;
    padding: 8px 2px;
  }

  .nav-link span {
    font-weight: bold;
  }

  .nav-link img {
    width: 24px;
    height: 24px;
  }

  .active-bar {
    top: 0;
    left: 0;
    width: 100%;
    height: 1.5px;
    border-radius: 0;
    transform: scaleX(0);
  }

  .nav-link.router-link-exact-active .active-bar {
    transform: scaleX(1);
  }
}

/* Optimisation pour très petits écrans (≤350px) */
@media (max-width: 350px) {
  .navigation-menu {
    padding: 0;
    height: 65px;
  }

  .nav-list li {
    max-width: calc(100% / 5);
  }
  .nav-link {
    font-size: 9px;
    padding: 6px 1px;
    gap: 2px;
  }
  .nav-link span {
    font-weight: 600;
    line-height: 1;
  }
  .nav-link img {
    width: 20px;
    height: 20px;
  }
}

@media (max-width: 320px) {
  .navigation-menu {
    height: 60px;
  }
  .nav-link {
    font-size: 9px;
    padding: 4px 0px;
  }
  .nav-link img {
    width: 18px;
    height: 18px;
  }
  .nav-link span {
    font-weight: 600;
  }
}

.main-content {
  padding-bottom: 70px;
}

@media (max-width: 350px) {
  .main-content {
    padding-bottom: 65px;
  }
}

@media (max-width: 320px) {
  .main-content {
    padding-bottom: 60px;
  }
}
</style>