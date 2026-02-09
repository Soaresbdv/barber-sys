import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/LoginView.vue') 
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('../views/RegisterView.vue') 
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: () => import('../views/DashboardView.vue'),
      beforeEnter: (to, from, next) => {
        if (!localStorage.getItem('token')) {
          next('/login')
        } else {
          next()
        }
      }
    },
    {
      path: '/appointments/new',
      name: 'new-appointment',
      component: () => import('../views/NewAppointmentView.vue'),
       beforeEnter: (to, from, next) => { // Proteção
        if (!localStorage.getItem('token')) next('/login')
        else next()
      }
    },
  ]
})

export default router