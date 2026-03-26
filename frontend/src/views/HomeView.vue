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
  localStorage.removeItem('user_role')
  isLoggedIn.value = false
  userName.value = null
  router.push('/')
}

const featuredServices = [
  { id: 1, name: 'Corte Clássico Executivo', price: 'R$ 45', desc: 'Tesoura ou máquina, com acabamento impecável, lavagem e finalização com pomada premium.' },
  { id: 2, name: 'Barboterapia Completa', price: 'R$ 35', desc: 'Toalha quente, massagem facial, espumas clássicas e navalha. Um verdadeiro ritual de relaxamento.' },
  { id: 3, name: 'Combo: O Cavalheiro', price: 'R$ 75', desc: 'Corte executivo + Barboterapia. Saia pronto para qualquer reunião ou evento especial.' }
]
</script>

<template>
  <div class="min-h-screen bg-stone-50 text-stone-800 font-sans selection:bg-emerald-800 selection:text-white scroll-smooth">
    
    <nav class="fixed w-full z-50 bg-stone-50/80 backdrop-blur-lg border-b border-stone-200/50 transition-all duration-300">
      <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="flex items-center justify-between h-24">
          <div class="flex-shrink-0 font-serif font-bold text-3xl tracking-widest text-emerald-950 cursor-pointer hover:opacity-80 transition-opacity" @click="router.push('/')">
            BARBER<span class="text-emerald-600">SYS</span><span class="text-stone-300">.</span>
          </div>
          
          <div class="hidden md:flex items-center space-x-12">
            <a href="#home" class="text-stone-500 hover:text-emerald-800 transition-colors text-[10px] font-black tracking-[0.2em] uppercase relative group">
              Início
              <span class="absolute -bottom-2 left-0 w-0 h-0.5 bg-emerald-700 transition-all group-hover:w-full"></span>
            </a>
            <a href="#services" class="text-stone-500 hover:text-emerald-800 transition-colors text-[10px] font-black tracking-[0.2em] uppercase relative group">
              Serviços
              <span class="absolute -bottom-2 left-0 w-0 h-0.5 bg-emerald-700 transition-all group-hover:w-full"></span>
            </a>
          </div>

          <div class="flex items-center gap-4">
            <RouterLink v-if="!isLoggedIn" to="/login" class="bg-emerald-900 hover:bg-emerald-800 text-white font-bold py-3 px-8 rounded-sm transition-transform active:scale-95 shadow-lg shadow-emerald-900/20 uppercase tracking-[0.2em] text-[10px]">
              Área do Cliente
            </RouterLink>

            <div v-else class="flex items-center gap-6">
              <div class="hidden sm:block text-right">
                <p class="text-[9px] font-black uppercase tracking-widest text-emerald-600 leading-none mb-1">Olá,</p>
                <p class="text-sm font-serif font-bold text-emerald-950 capitalize tracking-tight">
                  {{ userName }}
                </p>
              </div>
              
              <div class="flex items-center gap-5 border-l border-stone-200 pl-6">
                <RouterLink to="/profile" class="text-[9px] font-black tracking-[0.2em] uppercase text-emerald-900 border border-emerald-900 hover:bg-emerald-900 hover:text-white transition-colors py-2.5 px-6 rounded-sm">
                  Meu Perfil
                </RouterLink>
                
                <button @click="handleLogout" class="text-[9px] font-black tracking-[0.2em] uppercase text-stone-400 hover:text-red-500 transition-colors flex items-center gap-1.5">
                  Sair
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <section id="home" class="relative min-h-screen flex items-center pt-24">
      <div class="absolute inset-0 z-0">
        <img class="w-full h-full object-cover object-center" :src="bgImage" alt="Barbearia Clássica">
        <div class="absolute inset-0 bg-gradient-to-r from-stone-50 via-stone-50/90 to-transparent"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-stone-50 via-transparent to-black/20"></div>
      </div>

      <div class="relative max-w-7xl mx-auto px-6 lg:px-8 z-10 w-full">
        <div class="lg:w-7/12">
          
          <div class="inline-flex items-center gap-3 px-4 py-2 bg-emerald-50 border border-emerald-100 mb-8 rounded-sm">
            <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
            <span class="text-[9px] font-black uppercase tracking-[0.2em] text-emerald-800">Agendas Abertas para Hoje</span>
          </div>

          <h1 class="font-serif text-5xl sm:text-7xl lg:text-[5.5rem] font-bold text-stone-900 leading-[1.05] tracking-tight mb-6">
            A arte do <br>
            <span class="italic text-emerald-800 font-light">corte clássico.</span>
          </h1>
          
          <p class="text-lg sm:text-xl text-stone-600 max-w-md mb-12 leading-relaxed font-medium">
            Resgatamos a tradição com a conveniência que o homem moderno exige. Pontualidade, técnica e ambiente exclusivo.
          </p>
          
          <div class="flex flex-col sm:flex-row gap-5">
              <RouterLink to="/appointments/new" class="flex justify-center items-center px-10 py-5 bg-emerald-900 hover:bg-emerald-800 transition-all shadow-2xl shadow-emerald-900/30 text-[10px] font-black tracking-[0.2em] uppercase text-white rounded-sm active:scale-95">
                Agendar Horário
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
              </RouterLink>
              <a href="#services" class="flex justify-center items-center px-10 py-5 border border-stone-300 bg-white/50 backdrop-blur-sm text-[10px] font-black tracking-[0.2em] uppercase text-stone-700 hover:bg-white transition-all rounded-sm">
                Nossos Serviços
              </a>
          </div>
        </div>
      </div>
    </section>

    <section id="services" class="py-32 bg-stone-50 relative">
      <div class="max-w-7xl mx-auto px-6 lg:px-8">
        
        <div class="text-center max-w-2xl mx-auto mb-20">
          <span class="block text-emerald-700 font-black tracking-[0.3em] uppercase text-[10px] mb-4">O que fazemos de melhor</span>
          <h2 class="font-serif text-4xl sm:text-5xl font-bold text-stone-900">Serviços Premium</h2>
          <div class="w-24 h-1 bg-emerald-800 mx-auto mt-8 opacity-20"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div v-for="service in featuredServices" :key="service.id" 
               class="bg-white p-10 border border-stone-200 shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-500 group relative overflow-hidden rounded-sm">
            
            <div class="absolute top-0 left-0 w-full h-1 bg-stone-100 group-hover:bg-emerald-700 transition-colors duration-500"></div>
            
            <h3 class="font-serif text-2xl font-bold text-stone-900 mb-2 group-hover:text-emerald-900 transition-colors">{{ service.name }}</h3>
            <p class="text-3xl font-light text-stone-400 mb-6">{{ service.price }}</p>
            <p class="text-stone-500 text-sm leading-relaxed mb-8">{{ service.desc }}</p>
            
            <RouterLink to="/appointments/new" class="inline-flex items-center text-[10px] font-black uppercase tracking-[0.2em] text-emerald-700 group-hover:text-emerald-500 transition-colors">
              Marcar este <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 ml-2 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
            </RouterLink>
          </div>
        </div>

        <div class="mt-20 text-center">
          <RouterLink to="/appointments/new" class="inline-block border-b-2 border-stone-300 pb-1 text-xs font-black uppercase tracking-[0.2em] text-stone-500 hover:text-stone-900 hover:border-stone-900 transition-colors">
            Ver lista completa no painel
          </RouterLink>
        </div>
      </div>
    </section>

    <footer class="bg-stone-950 pt-20 pb-10 border-t-4 border-emerald-900">
      <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12 mb-16">
          
          <div>
            <div class="font-serif font-bold text-3xl tracking-widest text-white mb-6">
              BARBER<span class="text-emerald-600">SYS</span><span class="text-stone-600">.</span>
            </div>
            <p class="text-stone-400 text-sm leading-relaxed max-w-xs">
              A combinação perfeita entre a barbearia tradicional e a tecnologia moderna. Agende seu horário sem complicações.
            </p>
          </div>

          <div>
            <h4 class="text-white text-[10px] font-black uppercase tracking-[0.2em] mb-6">Funcionamento</h4>
            <ul class="space-y-3 text-stone-400 text-sm">
              <li class="flex justify-between border-b border-stone-800 pb-2"><span>Seg - Sex</span> <span>09:00 - 19:00</span></li>
              <li class="flex justify-between border-b border-stone-800 pb-2"><span>Sábado</span> <span>09:00 - 15:00</span></li>
              <li class="flex justify-between border-b border-stone-800 pb-2 text-stone-600"><span>Domingo</span> <span>Fechado</span></li>
            </ul>
          </div>

          <div>
            <h4 class="text-white text-[10px] font-black uppercase tracking-[0.2em] mb-6">Endereço</h4>
            <p class="text-stone-400 text-sm leading-relaxed mb-4">
              Av. Clássica dos Cavalheiros, 1920<br>
              Centro Histórico - CWB<br>
              Paraná, Brasil
            </p>
          </div>
        </div>

        <div class="border-t border-stone-800 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
          <p class="text-stone-600 text-xs font-medium">
            &copy; 2026 BarberSys. Todos os direitos reservados.
          </p>
          <div class="flex gap-6">
            <a href="#" class="text-stone-600 hover:text-stone-300 transition-colors">Instagram</a>
            <a href="#" class="text-stone-600 hover:text-stone-300 transition-colors">WhatsApp</a>
          </div>
        </div>
      </div>
    </footer>

  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,300;0,700;1,400&family=Inter:wght@400;500;700;900&display=swap');
html { scroll-behavior: smooth; }
.font-serif { font-family: 'Playfair Display', serif; }
.font-sans { font-family: 'Inter', sans-serif; }
</style>