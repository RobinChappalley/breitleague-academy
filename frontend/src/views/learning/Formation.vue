<template>
  <div class="formation-image-container">
    <div class="formation-watch-container">
    <img alt="watch aviators" src="/backgrounds/aviators-watch.png" class="lesson-watch">
    <button class="checkpoint-button"></button>
    </div>
  </div>
</template>

<script>
</script>

<style>
:root {
  --aviators-vertical: url('/backgrounds/aviators-vertical.png');
  --aviators-horizontal: url('/backgrounds/aviators-horizontal.png');
}

.formation-image-container {
  position :relative;
  height: 100vh;
  width: 100vw;
  background-position: right bottom;
  background-repeat: no-repeat;
  background-size: cover;
  display: flex;
  align-items: center;


  @media screen and (max-width: 768px){
    background-image: var(--aviators-vertical);

  }
  @media screen and (min-width: 768px){
    background-image: var(--aviators-horizontal);

  }
}

.formation-watch-container {
  position: relative;
  padding: 20px 0;
}
.lesson-watch {
  max-width: 65vw;
  max-height: 85vh;
}
.checkpoint-button {
  height: 4em;
  width: 4em;
  background-color: goldenrod;
  border-radius: 50%;
  filter:drop-shadow( 0 0 10px black);
  border: none;
  position: absolute;
  left : 30%;
}

</style>