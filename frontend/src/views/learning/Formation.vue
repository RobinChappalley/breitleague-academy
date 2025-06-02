<template>
  <div class="image-container">
    <img alt="image aviateurs" src="/backgrounds/aviators-horizontal.png" class="desktop-image">
    <img alt="image aviateurs" src="/backgrounds/aviators-vertical.png" class="mobile-image">
  </div>
</template>

<script>
</script>

<style scoped>
.image-container {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  overflow: hidden;
}

.desktop-image,
.mobile-image {
  width: 100%;
  height: 100%;
  object-fit: cover; /* L'image couvre tout l'espace disponible */
  object-position: center; /* Centre l'image */
}

/* Afficher l'image horizontale sur desktop, cacher la verticale */
.desktop-image {
  display: block;
}

.mobile-image {
  display: none;
}

/* Sur mobile, afficher l'image verticale, cacher l'horizontale */
@media screen and (max-width: 768px) {
  .desktop-image {
    display: none;
  }
  
  .mobile-image {
    display: block;
  }
}
</style>