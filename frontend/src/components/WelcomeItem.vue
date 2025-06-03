<template>
  <div class="item">
    <i>
      <slot name="icon"></slot>
    </i>
    <div class="details">
      <h3>
        <slot name="heading"></slot>
      </h3>
      <slot></slot>
    </div>
  </div>
</template>

<style scoped>
.navigation-menu {
  position: fixed;
  top: 0;
  left: 0;
  display: flex;
  flex-direction: column;
  width: 280px; /* élargi de base */
  height: 100vh;
  background-color: #001638;
  padding: 20px 0;
  border-radius: 0;
  z-index: 1000;
}

@media (min-width: 1440px) {
  .navigation-menu {
    width: 300px; /* élargi encore en plein écran */
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
  transition: filter 0.3s;
}

.nav-list li.active {
  color: #F7C72C;
}

.nav-list li.active img {
  filter: brightness(0) saturate(100%) invert(86%) sepia(79%) saturate(415%) hue-rotate(8deg) brightness(100%) contrast(102%);
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

/* Mobile (<768px) → barre fixée en bas, compacte */
@media (max-width: 767px) {
  .navigation-menu {
    position: fixed;
    bottom: 0;
    top: auto;
    left: 0;
    width: 100vw;
    height: 70px;
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    padding: 0;
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
    z-index: 1000;
    flex-wrap: nowrap;
    box-sizing: border-box;
  }

  .logo {
    display: none;
  }

  .nav-list {
    display: flex;
    flex-direction: row;
    width: 100%;
    margin: 0;
    padding: 0;
    list-style: none;
  }

  .nav-list li {
    flex: 1 1 20%; /* 5 icônes = 20% chacun */
    min-width: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 2px;
    padding: 0; /* enlever les paddings horizontaux */
    font-size: 10px;
    text-align: center;
    box-sizing: border-box;
  }

  .nav-list li span {
    font-weight: bold;
  }

  .nav-list li img {
    width: 20px;
    height: 20px;
  }

  .active-bar {
    top: 0;
    left: 0;
    width: 100%;
    height: 2px;
    transform: scaleX(0);
    border-radius: 0;
  }

  .nav-list li.active .active-bar {
    transform: scaleX(1);
  }

  .main-content {
    padding-bottom: 70px;
  }
}

 </style>