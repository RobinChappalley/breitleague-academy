<template>
  <div class="histoire-page">
    <button class="back-arrow" @click="goBack" aria-label="Go back">
      <svg width="36" height="36" viewBox="0 0 24 24" fill="none">
        <path d="M15 19l-7-7 7-7" stroke="#F7C72C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </button>
    <!-- Header Section with image -->
    <div class="hero-section">
      <div class="hero-image">
        <img 
          src="/images/hero/breitling-vintage-watches.jpg"
          alt="Breitling vintage watches with historical documentation"
          class="hero-img"
        />
      </div>
    </div>

    <!-- Onboarding Section -->
    <div class="onboarding-section">
      <div class="onboarding-content">
        <div class="section-header">
          <span class="section-tag">ONBOARDING</span>
          <h2 class="section-title">HISTORY</h2>
        </div>

        <div class="content-block">
          <h3 class="content-subtitle">INTRODUCTION</h3>
          <p class="content-text intro-bold">
            IT IS ESSENTIAL TO REVISIT OUR HISTORY TO EXPLAIN OUR HERITAGE AND UNDERSTAND THE BRAND'S NEW POSITIONING
          </p>
          
          <p class="content-text">
            In this presentation, you will see that while a brand's reputation is generally the result of a few decades of its history, this is not always the case.
          </p>

          <p class="content-text">
            For example, Breitling is often seen as a brand of instruments designed for aviation professionals. This is indeed the image we wanted to convey until now, since the quartz crisis. The History module is an exploration of the brand and its universes over time.
          </p>

          <p class="content-text">
            We will explore the brand's history in broad strokes to help you explain the foundations and legitimacy of our current positioning, and help you support your sales argument.
          </p>

          <h4 class="subsection-title">MUCH MORE THAN A WATCH BRAND FOR AVIATORS</h4>
          
          <p class="content-text">
            It all began with the founding of the brand in 1884 by Léon Breitling, whose goal was to develop the most efficient time measurement instruments.
          </p>

          <p class="content-text">
            This heritage has been perpetuated for more than a century until today.
          </p>
        </div>

        <!-- Second chapter -->
        <div class="content-block chapter-block">
          <h3 class="content-subtitle">A HISTORY POPULATED BY INVENTORS</h3>
          <h4 class="subsection-title">THE BREITLING FAMILY</h4>
          
          <p class="content-text">
            This section reviews the main actors who made Breitling one of the most iconic watch brands in the world. The three generations of the founding family — Léon, Gaston and Willy — were united by a common quest: the development of the world's best chronograph.
          </p>

          <!-- Timeline -->
          <div class="timeline">
            <div class="timeline-item">
              <div class="timeline-year">1884</div>
              <div class="timeline-content">
                <h5 class="timeline-title">LÉON BREITLING</h5>
                <p class="timeline-text">
                  Léon is the one who founded the brand in St. Imier in 1884. Given his success, he moved to La Chaux-de-Fonds to design instruments intended not to tell time, but to measure time: an important distinction.
                </p>
              </div>
            </div>

            <div class="timeline-item">
              <div class="timeline-year">1905</div>
              <div class="timeline-content">
                <h5 class="timeline-title">COUNTDOWN/TACHYMETER</h5>
                <p class="timeline-text">
                  In 1905, Léon presented a countdown/tachymeter with unprecedented precision, whose seconds hand completed a full rotation every four minutes, ensuring better visibility and the ability to calculate pace on the dial. Quickly adopted in sports, such as cycling or horse racing, this functionality was also appreciated by the police: the chronometer, called "Vitesse" (Speed), allowed measuring the speed of motorists and issuing the very first speeding tickets (yes, it's Breitling we have to thank for this innovation).
                </p>
              </div>
            </div>

            <div class="timeline-item">
              <div class="timeline-year">1914</div>
              <div class="timeline-content">
                <h5 class="timeline-title">GASTON BREITLING</h5>
                <p class="timeline-text">
                  Gaston, who took over the company after Léon's death, continued his father's dream and continued to innovate to develop the best time measurement instruments.
                </p>
              </div>
            </div>

            <div class="timeline-item">
              <div class="timeline-year">1915</div>
              <div class="timeline-content">
                <h5 class="timeline-title">1ST INDEPENDENT PUSHER</h5>
                <p class="timeline-text">
                  It was his pioneering spirit that logically led him to equip his watches with a pusher to isolate the functionalities. Launched in 1915, the first independent pusher on a wristwatch separated the "start-stop" and "reset" function from the crown. In 1923, he further improved his control system by adding a "reset" function on the crown, allowing users to interrupt and resume timing, but also to record several successive times.
                </p>
              </div>
            </div>

            <div class="timeline-item">
              <div class="timeline-year">1932</div>
              <div class="timeline-content">
                <h5 class="timeline-title">WILLY BREITLING</h5>
                <p class="timeline-text">
                  In 1932, at age 19, Willy Breitling took over the direction of the company, a few years after his father Gaston's death. His new perspective, sharpened by technical and commercial studies, proved crucial: Willy decided at the right time to focus his advertisements on the functionality of Breitling products but also on their elegance: this was a decisive turning point in the brand's history.
                </p>
              </div>
            </div>

            <div class="timeline-item">
              <div class="timeline-year">1934</div>
              <div class="timeline-content">
                <h5 class="timeline-title">2ND INDEPENDENT PUSHER</h5>
                <p class="timeline-text">
                  Willy was the inventor, no more no less, of the first modern chronograph, which earns him a place among the greatest names in watchmaking history. Even today, almost all chronographs produced in the world are influenced and inspired by Willy Breitling's invention, the second independent pusher at 4 o'clock to reset the chronograph.
                </p>
              </div>
            </div>
          </div>

          <div class="highlight-box">
            <h4 class="highlight-title">WHEN TO TALK ABOUT THESE HISTORICAL DATES?</h4>
            <p class="highlight-text">
              The three generations that succeeded each other at the head of Breitling are inexhaustible sources of anecdotes.
            </p>
            <p class="highlight-text">
              Talk about Léon, Gaston and especially Willy to emphasize our pioneering spirit and attest to our legitimacy, our will to innovate and our heritage.
            </p>
          </div>

          <div class="conclusion-box">
            <h4 class="conclusion-title">THE MODERN CHRONOGRAPH WAS BORN</h4>
            <p class="conclusion-text">
              The first modern chronograph was invented in 1934 by Willy Breitling, who was the grandson of the brand's founder and led the company until the 1970s. Today, more than 80 years later, his design and technology continue to influence the vast majority of chronographs produced around the world.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
const router = useRouter()

const goBack = () => {
  router.back()
}
console.log('HistoireView component loaded')
</script>

<style scoped>
.histoire-page {
  width: 100%;
  min-height: 100vh;
  background: #000000;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  /* Ajouter padding pour la navbar mobile */
  padding-bottom: 80px;
}

/* HERO SECTION */
.hero-section {
  width: 100%;
  height: 40vh; /* Réduire pour laisser place à la navbar */
  background: #000000;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.hero-image {
  width: 100%;
  height: 100%;
  position: relative;
}

.hero-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
}

/* ONBOARDING SECTION */
.onboarding-section {
  background: #072C54;
  flex: 1;
  padding: 1.5rem 1rem; /* Réduire padding mobile */
  min-height: 60vh;
}

.onboarding-content {
  width: 100%;
  max-width: none;
}

.section-header {
  margin-bottom: 1.5rem; /* Réduire margin mobile */
}

.section-tag {
  display: inline-block;
  background: rgba(255, 255, 255, 0.2);
  color: #FFFFFF;
  padding: 0.4rem 0.8rem; /* Réduire padding mobile */
  border-radius: 4px;
  font-size: 0.7rem; /* Réduire taille mobile */
  font-weight: 600;
  letter-spacing: 1px;
  margin-bottom: 0.8rem;
  text-transform: uppercase;
}

.section-title {
  color: #F7C72C;
  font-size: 2rem; /* Réduire taille mobile */
  font-weight: 700;
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  line-height: 1.1;
}

.content-block {
  background: rgba(255, 255, 255, 0.1);
  padding: 1.5rem 1rem; /* Réduire padding mobile */
  border-radius: 8px;
  backdrop-filter: blur(10px);
  margin-bottom: 1.5rem;
}

.chapter-block {
  margin-top: 2rem;
}

.content-subtitle {
  color: #F7C72C;
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 1.5rem;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.subsection-title {
  color: #F7C72C;
  font-size: 1rem;
  font-weight: 600;
  margin: 1.5rem 0 1rem 0;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.content-text {
  color: #FFFFFF;
  font-size: 0.95rem;
  line-height: 1.6;
  margin-bottom: 1.5rem;
  opacity: 0.95;
  text-align: left;
}

.intro-bold {
  font-weight: 600;
  opacity: 1;
}

.content-text:last-child {
  margin-bottom: 0;
}

/* TIMELINE */
.timeline {
  margin: 2rem 0;
  position: relative;
}

.timeline::before {
  content: '';
  position: absolute;
  left: 60px;
  top: 0;
  bottom: 0;
  width: 2px;
  background: #F7C72C;
}

.timeline-item {
  display: flex;
  margin-bottom: 2rem;
  position: relative;
}

.timeline-year {
  color: #F7C72C;
  font-size: 1.1rem;
  font-weight: 700;
  min-width: 50px;
  margin-right: 2rem;
  position: relative;
}

.timeline-year::after {
  content: '';
  position: absolute;
  right: -26px;
  top: 50%;
  transform: translateY(-50%);
  width: 8px;
  height: 8px;
  background: #F7C72C;
  border-radius: 50%;
}

.timeline-content {
  flex: 1;
}

.timeline-title {
  color: #F7C72C;
  font-size: 0.9rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.timeline-text {
  color: #FFFFFF;
  font-size: 0.9rem;
  line-height: 1.6;
  opacity: 0.9;
}

/* HIGHLIGHT BOX */
.highlight-box {
  background: rgba(247, 199, 44, 0.1);
  border-left: 4px solid #F7C72C;
  padding: 1.5rem;
  margin: 2rem 0;
  border-radius: 4px;
}

.highlight-title {
  color: #F7C72C;
  font-size: 1rem;
  font-weight: 600;
  margin-bottom: 1rem;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.highlight-text {
  color: #FFFFFF;
  font-size: 0.9rem;
  line-height: 1.6;
  margin-bottom: 1rem;
  opacity: 0.95;
}

.highlight-text:last-child {
  margin-bottom: 0;
}

/* CONCLUSION BOX */
.conclusion-box {
  background: rgba(255, 255, 255, 0.05);
  padding: 2rem;
  border-radius: 8px;
  margin-top: 2rem;
  border: 1px solid rgba(247, 199, 44, 0.3);
}

.conclusion-title {
  color: #F7C72C;
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 1rem;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.conclusion-text {
  color: #FFFFFF;
  font-size: 1rem;
  line-height: 1.7;
  opacity: 0.95;
}

/* DESKTOP (768px et plus) */
@media (min-width: 768px) {
  .histoire-page {
    width: 100%;
    padding-bottom: 0;
  }
  
  .hero-section {
    height: 50vh;
  }
  
  .onboarding-section {
    padding: 3rem 4rem;
    min-height: 50vh;
  }
  
  .section-title {
    font-size: 3rem;
    letter-spacing: 2px;
  }
  
  .content-block {
    padding: 2.5rem 3rem;
  }
}

/* LARGE DESKTOP (1024px et plus) */
@media (min-width: 1024px) {
  .histoire-page {
    width: 100% ;
  }
  
  .hero-section {
    height: 60vh;
  }
  
  .onboarding-section {
    padding: 4rem 6rem;
  }
  
  .section-title {
    font-size: 4rem;
    letter-spacing: 3px;
  }
  
  .content-block {
    padding: 3rem 4rem;
  }
}

/* MOBILE (moins de 768px) */
@media (max-width: 767px) {
  .histoire-page {
    margin-left: 0;
    width: 100%;
    padding-bottom: 80px; /* Navbar mobile */
  }
  
  .hero-section {
    height: 40vh;
  }
  
  .onboarding-section {
    padding: 1.5rem 1rem;
  }
  
  .section-title {
    font-size: 2rem;
  }
  
  .content-block {
    padding: 1.5rem 1rem;
  }
}
.back-arrow {
  position: absolute;
  top: 1.5rem;
  left: 1.5rem;
  z-index: 20;
  background: #072C54;
  border: none;
  cursor: pointer;
  padding: 0.3rem;
  border-radius: 50%;
  transition: background 0.18s, box-shadow 0.18s;
}
.back-arrow:hover {
  background: rgba(247, 199, 44, 0.1);
  box-shadow: 0 1px 4px rgba(33,40,80,0.15);
}
@media (max-width: 767px) {
  .back-arrow {
    top: 1rem;
    left: 1rem;
  }
}
</style>