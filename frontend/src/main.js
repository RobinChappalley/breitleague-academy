import './assets/base.css'

import { createApp } from 'vue'
import App from './App.vue'
import { createRouter, createWebHistory } from 'vue-router'
import { routes } from './routes'

const router = createRouter({
  history: createWebHistory(),
  routes
})
//  Ajout du guard ici
const isAuthenticated = () => {
  return localStorage.getItem('isLoggedIn') === 'true'
}

router.beforeEach(async (to, from, next) => {
  if (!isAuthenticated() && to.path !== '/login') {
    return next('/login')
  }

  if (isAuthenticated() && to.path === '/login') {
    return next('/')
  }

  return next()
})

const app = createApp(App)
app.use(router)
app.mount('#app')
