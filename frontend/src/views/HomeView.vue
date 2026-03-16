<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { RouterLink, useRouter } from 'vue-router'

const router = useRouter()
const bgImage = "https://images.unsplash.com/photo-1585747860715-2ba37e788b70?q=80&w=2074&auto=format&fit=crop"

const isLoggedIn = ref(false)
const userName = ref<string | null>(null)

onMounted(() => {
  const token = localStorage.getItem('token')
  const userJson = localStorage.getItem('user')
  
  if (token) {
    isLoggedIn.value = true
    if (userJson) {
      try {
        const userData = JSON.parse(userJson)
        userName.value = userData.name ? userData.name.split(' ')[0] : 'Cliente'
      } catch (e) {
        console.error("Erro ao processar dados do usuário", e)
        userName.value = 'Cliente'
      }
    }
  }
})

const handleLogout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  isLoggedIn.value = false
  userName.value = null
  router.push('/')
}
</script>

<template>
  <div class="min-h-screen bg-stone-50 text-stone-800 font-sans selection:bg-emerald-800 selection:text-white">
    
    <nav class="fixed w-full z-50 bg-stone-50/90 backdrop-blur-md border-b border-stone-200 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20">
          <div class="flex-shrink-0 font-serif font-bold text-2xl tracking-widest text-emerald-950 cursor-pointer" @click="router.push('/')">
            BARBER<span class="text-emerald-700">SYS</span>.
          </div>
          
          <div class="hidden md:block">
            <div class="ml-10 flex items-baseline space-x-8">
              <a href="#home" class="text-stone-600 hover:text-emerald-800 transition-colors px-3 py-2 rounded-md text-[10px] font-black tracking-widest uppercase">Início</a>
              <a href="#services" class="text-stone-600 hover:text-emerald-800 transition-colors px-3 py-2 rounded-md text-[10px] font-black tracking-widest uppercase">Serviços</a>
            </div>
          </div>

          <div class="flex items-center gap-4">
            <RouterLink v-if="!isLoggedIn" to="/login" class="bg-emerald-900 hover:bg-emerald-800 text-stone-50 font-bold py-2.5 px-6 rounded-sm transition-all shadow-lg shadow-emerald-900/20 uppercase tracking-wider text-[10px] border border-emerald-950">
              Área do Cliente
            </RouterLink>

            <div v-else class="flex items-center gap-4 md:gap-6">
              <div class="hidden sm:block text-right border-r border-stone-200 pr-4 md:pr-6">
                <p class="text-[9px] font-black uppercase tracking-widest text-emerald-700 leading-none mb-1">Bem-vindo</p>
                <p class="text-sm font-serif italic font-bold text-emerald-950 uppercase tracking-tighter">
                  {{ userName }}
                </p>
              </div>
              
              <div class="flex items-center gap-2">
                <RouterLink to="/dashboard" class="bg-stone-800 hover:bg-black text-white font-bold py-2.5 px-5 rounded-sm transition-all shadow-md uppercase tracking-widest text-[10px]">
                  Painel
                </RouterLink>
                <button @click="handleLogout" title="Sair" class="p-2 text-stone-400 hover:text-red-600 transition-colors">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <div id="home" class="relative pt-20 lg:pt-0 overflow-hidden min-h-screen flex items-center">
      <div class="absolute inset-0 z-0">
        <img class="w-full h-full object-cover opacity-90" :src="bgImage" alt="Barbearia Clássica">
        <div class="absolute inset-0 bg-gradient-to-r from-stone-50 via-stone-50/95 to-stone-50/20 sm:via-stone-50/70"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-stone-50 via-transparent to-transparent"></div>
      </div>

      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 z-10 w-full mt-10 sm:mt-0">
        <div class="lg:w-1/2">
          <div class="border-l-4 border-emerald-800 pl-6 mb-6">
            <span class="block text-emerald-800 font-bold tracking-[0.4em] uppercase text-xs mb-2">Desde 2026</span>
            <h1 class="font-serif text-5xl sm:text-6xl md:text-7xl font-bold text-emerald-950 leading-[1.1]">
              A arte do <br>
              <span class="italic text-emerald-700">corte clássico.</span>
            </h1>
          </div>
          <p class="mt-6 text-lg text-stone-700 max-w-lg mb-10 leading-relaxed font-medium italic">
            Resgatamos a tradição da barbearia com a conveniência que o homem moderno exige. Pontualidade, técnica e ambiente exclusivo.
          </p>
          <div class="flex flex-col sm:flex-row gap-4">
              <RouterLink to="/appointments/new" class="flex justify-center items-center px-10 py-4 rounded-sm text-sm font-black tracking-[0.2em] uppercase text-white bg-emerald-900 hover:bg-emerald-800 transition-all shadow-2xl shadow-emerald-900/40">
                Agendar Agora
              </RouterLink>
              <button class="flex justify-center items-center px-10 py-4 border-2 border-stone-800 rounded-sm text-sm font-black tracking-[0.2em] uppercase text-stone-800 hover:bg-stone-800 hover:text-white transition-all">
                Serviços
              </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=Inter:wght@400;700;900&display=swap');
.font-serif { font-family: 'Playfair Display', serif; }
.font-sans { font-family: 'Inter', sans-serif; }
</style>