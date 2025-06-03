<template>
    <nav class="navigation-menu">
      <div class="logo" v-if="isDesktop">
        <img src="/images/icones/logo.png" alt="Breitleague Academy Logo" />
      </div>
      <ul class="nav-list">
        <li
          v-for="item in navItems"
          :key="item.name"
          :class="{ active: activeItem === item.name }"
          @click="setActive(item.name)"
        >
          <div class="active-bar" />
          <img :src="item.icon" :alt="item.label" />
          <span>{{ item.label }}</span>
        </li>
      </ul>
    </nav>
  </template>
  
  <script setup>
  import { ref, onMounted, onUnmounted } from 'vue'
  
  import learningIcon from '/images/icones/learning.svg'
import battleIcon from '/images/icones/battle.svg'
import collectionIcon from '/images/icones/collection.svg'
import cupIcon from '/images/icones/cup.svg'
import profileIcon from '/images/icones/profile.svg'

  
  const navItems = [
    { name: 'learning', label: 'Learning', icon: learningIcon },
    { name: 'battle', label: 'Battle', icon: battleIcon },
    { name: 'collections', label: 'Collections', icon: collectionIcon },
    { name: 'ranking', label: 'Ranking', icon: cupIcon },
    { name: 'profile', label: 'Profile', icon: profileIcon }
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
  
  <style scoped>
.navigation-menu {
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
}

.nav-list li {
  position: relative;
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 20px;
  cursor: pointer;
  color: #3399ff;
  font-size: 18px;
  transition: background-color 0.3s, color 0.3s;
}

.nav-list li span {
  font-weight: bold;
}

.nav-list li:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.nav-list li img {
  width: 28px;
  height: 28px;
  filter: brightness(0) saturate(100%) invert(48%) sepia(59%) saturate(1326%) hue-rotate(181deg) brightness(95%) contrast(95%);
}

.nav-list li.active {
  color: #F7C72C;
}

.nav-list li.active img {
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

  .nav-list li.active .active-bar {
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
    flex-direction: column;
    gap: 4px;
    padding: 8px 2px;
    font-size: 12px;
    flex: 1;
    text-align: center;
    max-width: calc(100% / 5);
  }

  .nav-list li span {
    font-weight: bold;
  }

  .nav-list li img {
    width: 24px;
    height: 24px;
  }

  .active-bar {
    top: 0;
    left: 0;
    width: 100%;
    height: 1.5px;
    transform: scaleX(0);
    border-radius: 0;
  }

  .nav-list li.active .active-bar {
    transform: scaleX(1);
  }
}

/* Optimisation pour très petits écrans (≤350px) */
@media (max-width: 350px) {
  .navigation-menu {
    padding: 0;
    height: 65px;

  }

  .nav-list {
    margin: 0;
    padding: 0;
  }

  .nav-list li {
    padding: 6px 1px;
    font-size: 9px;
    gap: 2px;
    max-width: calc(100% / 5);
  }

  .nav-list li span {
    font-weight: 600;
    line-height: 1;
  }

  .nav-list li img {
    width: 20px;
    height: 20px;
  }
}

/* Optimisation pour écrans très étroits (≤320px) */
@media (max-width: 320px) {
  .navigation-menu {
    height: 60px;
  }

  .nav-list li {
    font-size: 9px;
    padding: 4px 0px;
  }

  .nav-list li img {
    width: 18px;
    height: 18px;
  }

  .nav-list li span {
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