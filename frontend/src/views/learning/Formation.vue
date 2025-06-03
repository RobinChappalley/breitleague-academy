<template>

  <div class="formation-image-container">
    <img alt="watch aviators" src="/backgrounds/aviators-watch.png" class="lesson-watch">
  </div>
</template>

<script>
</script>

<style>
:root {
  --aviators-vertical: url('/backgrounds/aviators-vertical.png');
  --aviators-horizontal: url('/backgrounds/aviators-horizontal.png');
  --aviators-watch: url('/backgrounds/aviators-watch.png');
}

.formation-image-container {
  height: 100vh;
  width: 100vw;
  background-position: right bottom;
  background-repeat: no-repeat;
  background-size: cover;

  @media screen and (max-width: 768px){
    background-image: var(--aviators-vertical);

  }
  @media screen and (min-width: 768px){
    background-image: var(--aviators-horizontal);

  }
}
.lesson-watch {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  max-width: 65vw;
  max-height: 85vh;
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