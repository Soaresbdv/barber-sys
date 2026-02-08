<script setup lang="ts">
import { RouterLink } from 'vue-router'

const bgImage = "https://images.unsplash.com/photo-1585747860715-2ba37e788b70?q=80&w=2074&auto=format&fit=crop"
</script>

<template>
  <div class="min-h-screen bg-stone-50 text-stone-800 font-sans selection:bg-emerald-800 selection:text-white">
    
    <nav class="fixed w-full z-50 bg-stone-50/90 backdrop-blur-md border-b border-stone-200 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20">
          <div class="flex-shrink-0 font-serif font-bold text-2xl tracking-widest text-emerald-950">
            BARBER<span class="text-emerald-700">SYS</span>.
          </div>
          <div class="hidden md:block">
            <div class="ml-10 flex items-baseline space-x-8">
              <a href="#home" class="text-stone-600 hover:text-emerald-800 transition-colors px-3 py-2 rounded-md text-sm font-bold tracking-wider uppercase">Início</a>
              <a href="#services" class="text-stone-600 hover:text-emerald-800 transition-colors px-3 py-2 rounded-md text-sm font-bold tracking-wider uppercase">Serviços</a>
            </div>
          </div>
          <div>
            <RouterLink to="/login" class="bg-emerald-900 hover:bg-emerald-800 text-stone-50 font-bold py-2 px-6 rounded-sm transition-all shadow-lg shadow-emerald-900/20 uppercase tracking-wider text-xs border border-emerald-950">
              Área do Cliente
            </RouterLink>
          </div>
        </div>
      </div>
    </nav>

    <div id="home" class="relative pt-20 lg:pt-0 overflow-hidden min-h-screen flex items-center">
      
      <div class="absolute inset-0 z-0">
        <img class="w-full h-full object-cover opacity-90" :src="bgImage" alt="Barbearia Clássica">
        <div class="absolute inset-0 bg-gradient-to-r from-stone-50 via-stone-50/90 to-stone-50/20 sm:via-stone-50/60"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-stone-50 via-transparent to-transparent"></div>
      </div>

      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 z-10 w-full mt-10 sm:mt-0">
        <div class="lg:w-1/2">
          <div class="border-l-4 border-emerald-800 pl-6 mb-6">
            <span class="block text-emerald-800 font-bold tracking-[0.2em] uppercase text-sm mb-2">Desde 2026</span>
            <h1 class="font-serif text-5xl sm:text-6xl md:text-7xl font-bold text-emerald-950 leading-[1.1]">
              A arte do <br>
              <span class="italic text-emerald-700">corte clássico.</span>
            </h1>
          </div>
          
          <p class="mt-6 text-xl text-stone-700 max-w-lg mb-10 leading-relaxed font-medium">
            Resgatamos a tradição da barbearia com a conveniência que o homem moderno exige. Pontualidade, técnica e ambiente exclusivo.
          </p>
          
          <div class="flex flex-col sm:flex-row gap-4">
            <RouterLink to="/register" class="flex justify-center items-center px-8 py-4 rounded-sm text-lg font-bold tracking-widest uppercase text-white bg-emerald-900 hover:bg-emerald-800 transition-all shadow-xl shadow-emerald-900/20">
              Agendar Agora
            </RouterLink>
            
            <button class="flex justify-center items-center px-8 py-4 border-2 border-stone-800 rounded-sm text-lg font-bold tracking-widest uppercase text-stone-800 hover:bg-stone-800 hover:text-white transition-all">
              Serviços
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="py-24 bg-stone-100 border-t border-stone-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-20">
          <h2 class="text-emerald-800 font-bold tracking-widest uppercase text-xs mb-3">Nossos Valores</h2>
          <div class="w-24 h-1 bg-emerald-800 mx-auto mb-6"></div>
          <p class="text-3xl leading-8 font-serif font-bold text-stone-800 sm:text-4xl">
            Por que a BarberSys?
          </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
          <div class="bg-white p-10 rounded-sm shadow-xl shadow-stone-200/50 border-b-4 border-emerald-800 hover:-translate-y-2 transition-transform duration-300">
            <div class="w-14 h-14 bg-stone-100 rounded-full flex items-center justify-center mb-6 text-emerald-900">
               <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h3 class="text-xl font-serif font-bold text-stone-900 mb-3">Horário Garantido</h3>
            <p class="text-stone-600 leading-relaxed">Respeitamos rigorosamente seu agendamento. Sem filas, sem espera.</p>
          </div>
           <div class="bg-white p-10 rounded-sm shadow-xl shadow-stone-200/50 border-b-4 border-emerald-800 hover:-translate-y-2 transition-transform duration-300">
            <div class="w-14 h-14 bg-stone-100 rounded-full flex items-center justify-center mb-6 text-emerald-900">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
            </div>
            <h3 class="text-xl font-serif font-bold text-stone-900 mb-3">Barbeiros Experts</h3>
            <p class="text-stone-600 leading-relaxed">Equipe selecionada com mestres na tesoura e na navalha clássica.</p>
          </div>
           <div class="bg-white p-10 rounded-sm shadow-xl shadow-stone-200/50 border-b-4 border-emerald-800 hover:-translate-y-2 transition-transform duration-300">
            <div class="w-14 h-14 bg-stone-100 rounded-full flex items-center justify-center mb-6 text-emerald-900">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
            </div>
            <h3 class="text-xl font-serif font-bold text-stone-900 mb-3">Ambiente Premium</h3>
            <p class="text-stone-600 leading-relaxed">Wi-fi, café expresso e toalha quente. Uma experiência completa.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>