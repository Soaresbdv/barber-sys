import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/', name: 'home', component: HomeView },
    { path: '/login', name: 'login', component: () => import('../views/LoginView.vue') },
    { path: '/register', name: 'register', component: () => import('../views/RegisterView.vue') },
    { path: '/profile', name: 'profile', component: () => import('../views/ProfileView.vue') },
    
    // Rota do barbeiro 
    { 
      path: '/barber/dashboard', 
      name: 'barber-dashboard', 
      component: () => import('../views/BarberDashboardView.vue'),
      beforeEnter: (to, from, next) => {
        const token = localStorage.getItem('token');
        const role = localStorage.getItem('user_role');
        
        if (!token) {
          next('/login'); 
        } else if (role !== 'barber' && role !== 'admin') {
          next('/dashboard'); 
        } else {
          next(); 
        }
      }
    },

    // Rota do Cliente
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

    // Rota de Novo Agendamento
    {
      path: '/appointments/new',
      name: 'new-appointment',
      component: () => import('../views/NewAppointmentView.vue'),
      beforeEnter: (to, from, next) => { 
        if (!localStorage.getItem('token')) {
          next('/login')
        } else {
          next()
        }
      }
    },

    // Rota do Admin
    { 
      path: '/admin/dashboard', 
      name: 'admin-dashboard', 
      component: () => import('../views/AdminDashboardView.vue'),
      beforeEnter: (to, from, next) => {
        const token = localStorage.getItem('token');
        const role = localStorage.getItem('user_role');
        
        if (!token) {
          next('/login');
        } else if (role !== 'admin') {
          next('/dashboard'); 
        } else {
          next();
        }
      }
    },
    // Rota de Gestão de Barbeiros (Admin)
    { 
      path: '/admin/barbers', 
      name: 'admin-barbers', 
      component: () => import('../views/AdminBarbersView.vue'),
      beforeEnter: (to, from, next) => {
        const token = localStorage.getItem('token');
        const role = localStorage.getItem('user_role');
        
        if (!token) {
          next('/login');
        } else if (role !== 'admin') {
          next('/dashboard'); 
        } else {
          next();
        }
      }
    },

    // Rota da Gestão de Serviços 
    { 
      path: '/admin/services', 
      name: 'admin-services', 
      component: () => import('../views/AdminServicesView.vue'),
      beforeEnter: (to, from, next) => {
        const token = localStorage.getItem('token');
        const role = localStorage.getItem('user_role');
        
        if (!token) {
          next('/login');
        } else if (role !== 'admin') {
          next('/dashboard'); 
        } else {
          next();
        }
      }
    },
  ]
})

export default router