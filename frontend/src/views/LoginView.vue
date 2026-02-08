<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import axios from 'axios'

const router = useRouter()

const email = ref('')
const password = ref('')
const loading = ref(false)
const errorMessage = ref('')

const handleLogin = async () => {
  loading.value = true
  errorMessage.value = ''
  try {
    const response = await axios.post('http://localhost:8000/api/login', {
      email: email.value,
      password: password.value
    })
    localStorage.setItem('token', response.data.access_token)
    localStorage.setItem('user_role', response.data.user.role)
    router.push('/')
  } catch (error: any) {
    if (error.response && error.response.status === 401) {
      errorMessage.value = 'Credenciais incorretas.'
    } else {
      errorMessage.value = 'Servidor indisponível no momento.'
    }
  } finally {
    loading.value = false
  }
}

const backgroundImages = [
  "https://images.unsplash.com/photo-1585747860715-2ba37e788b70?q=80&w=2074&auto=format&fit=crop", // Interior clássico escuro
  "https://images.unsplash.com/photo-1503951914875-452162b0f3f1?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D", // Barbeiro P&B
  "https://images.unsplash.com/photo-1621605815971-fbc98d665033?q=80&w=2070&auto=format&fit=crop", // Ferramentas na bancada
  "https://images.unsplash.com/photo-1605497788044-5a32c7078486?q=80&w=1974&auto=format&fit=crop", // Corte em progresso
  "https://images.unsplash.com/photo-1532710093739-9470acff878f?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"  // Detalhe pincel de barba
]

const currentImageIndex = ref(0)
let intervalId: any = null

onMounted(() => {
  backgroundImages.forEach(src => {
    const img = new Image()
    img.src = src
  })

  intervalId = setInterval(() => {
    currentImageIndex.value = (currentImageIndex.value + 1) % backgroundImages.length
  }, 4000) 
})

onUnmounted(() => {
  if (intervalId) clearInterval(intervalId)
})
</script>

<template>
  <div class="min-h-screen flex font-sans bg-stone-50">

    <div class="hidden lg:flex relative w-0 flex-1 overflow-hidden bg-emerald-950 items-center justify-center">
      
      <div class="absolute inset-0 w-full h-full bg-emerald-950">
        <img 
          v-for="(imgUrl, index) in backgroundImages" 
          :key="imgUrl"
          :src="imgUrl" 
          alt="Barbearia Clássica"
          class="absolute inset-0 h-full w-full object-cover object-center mix-blend-luminosity contrast-125 filter grayscale transition-all duration-[2000ms] ease-in-out"
          :class="index === currentImageIndex ? 'opacity-60 z-10 scale-110' : 'opacity-0 z-0 scale-100'"
        >
      </div>
      
      <div class="absolute inset-0 bg-emerald-950/70 mix-blend-multiply z-20"></div>
      <div class="absolute inset-0 bg-radial-[at_center,_var(--tw-gradient-stops)] from-transparent via-emerald-950/40 to-emerald-950/90 z-20"></div>
      
      <div class="relative z-30 p-12 text-center">
        <div class="w-20 h-1 bg-emerald-700 mx-auto mb-8 shadow-[0_0_15px_rgba(4,120,87,0.5)]"></div>
        <h2 class="font-serif text-4xl font-bold text-stone-100 tracking-wider leading-snug italic drop-shadow-2xl">
          "A excelência não é um ato,<br>é um hábito."
        </h2>
        <p class="mt-6 text-emerald-400/80 uppercase tracking-[0.3em] text-xs font-bold drop-shadow-md">BarberSys Tradition</p>
      </div>
    </div>

    <div class="flex-1 flex flex-col justify-center py-12 px-4 sm:px-6 lg:flex-none lg:px-24 xl:px-32 text-stone-900 bg-[radial-gradient(ellipse_at_top_left,_var(--tw-gradient-stops))] from-stone-50 via-stone-100 to-stone-200">
      <div class="mx-auto w-full max-w-[400px]">
        
        <div class="text-center mb-12">
           <RouterLink to="/" class="inline-block mb-6 group">
             <h2 class="font-serif text-3xl font-bold text-emerald-950 tracking-[0.2em] transition-transform group-hover:scale-105 duration-300">
              BARBER<span class="text-emerald-800">SYS</span>.
            </h2>
           </RouterLink>
          <h3 class="text-lg font-medium text-stone-600 tracking-wide">
            Acesse sua área exclusiva
          </h3>
        </div>

        <form class="space-y-10" @submit.prevent="handleLogin">
          
          <div class="relative group">
            <input id="email" name="email" type="email" required v-model="email" 
              class="peer h-10 w-full border-b-2 border-stone-300 text-stone-900 bg-transparent placeholder-transparent focus:outline-none focus:border-emerald-800 transition-colors pt-1 pb-2 text-lg font-medium"
              placeholder="seu@email.com">
            <label for="email" class="absolute left-0 -top-3.5 text-stone-500 text-sm transition-all peer-placeholder-shown:text-base peer-placeholder-shown:text-stone-400 peer-placeholder-shown:top-2 peer-focus:-top-3.5 peer-focus:text-stone-600 peer-focus:text-sm uppercase tracking-wider font-bold">
              Email
            </label>
          </div>

          <div class="relative group">
            <input id="password" name="password" type="password" required v-model="password" 
              class="peer h-10 w-full border-b-2 border-stone-300 text-stone-900 bg-transparent placeholder-transparent focus:outline-none focus:border-emerald-800 transition-colors pt-1 pb-2 text-lg font-medium font-serif tracking-widest"
              placeholder="Senha">
            <label for="password" class="absolute left-0 -top-3.5 text-stone-500 text-sm transition-all peer-placeholder-shown:text-base peer-placeholder-shown:text-stone-400 peer-placeholder-shown:top-2 peer-focus:-top-3.5 peer-focus:text-stone-600 peer-focus:text-sm uppercase tracking-wider font-bold">
              Senha
            </label>
          </div>

          <div v-if="errorMessage" class="text-red-600 text-sm font-medium text-center bg-red-50/50 py-2 px-4 border-l-2 border-red-600 animate-pulse">
            {{ errorMessage }}
          </div>

          <div>
            <button type="submit" :disabled="loading"
              class="relative w-full flex justify-center py-4 px-4 border border-transparent text-sm font-bold uppercase tracking-[0.25em] text-stone-50 bg-emerald-900 hover:bg-emerald-950 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-900 transition-all duration-300 disabled:opacity-70 disabled:cursor-not-allowed overflow-hidden group rounded-sm shadow-xl shadow-emerald-900/20 hover:shadow-emerald-900/30">
               <div class="absolute inset-0 h-full w-full scale-0 rounded-sm transition-all duration-300 group-hover:scale-100 group-hover:bg-white/10"></div>
               <span class="relative z-10" v-if="loading">Autenticando...</span>
               <span class="relative z-10" v-else>Entrar</span>
            </button>
          </div>

          <div class="text-center mt-8 text-sm text-stone-500">
            <p>Ainda não é cliente?</p>
            <RouterLink to="/register" class="mt-2 inline-block font-bold text-emerald-900 hover:text-emerald-700 uppercase tracking-widest border-b-2 border-emerald-900/30 hover:border-emerald-900 transition-all pb-1">
              Criar minha conta
            </RouterLink>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>