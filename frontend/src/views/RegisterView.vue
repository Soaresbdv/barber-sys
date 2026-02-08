<script setup lang="ts">
import { ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const name = ref('')
const email = ref('')
const phone = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const loading = ref(false)
const errorMessage = ref('')

const handleRegister = async () => {
  if (password.value !== passwordConfirmation.value) {
    errorMessage.value = 'As senhas não coincidem.'
    return
  }

  loading.value = true
  errorMessage.value = ''

  try {
    const response = await axios.post('http://localhost:8000/api/register', {
      name: name.value,
      email: email.value,
      phone: phone.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value
    })

    localStorage.setItem('token', response.data.access_token)
    localStorage.setItem('user_role', response.data.user.role) 
    alert('Conta criada com sucesso!')
    
    setTimeout(() => {
        router.push('/')
    }, 500)
    
  } catch (error: any) {
    console.error(error)
    
    if (error.response && error.response.data && error.response.data.errors) {
      const errors = error.response.data.errors
      const firstErrorKey = Object.keys(errors)[0]
      
      if (firstErrorKey && Array.isArray(errors[firstErrorKey])) {
        errorMessage.value = errors[firstErrorKey][0]
      } else {
         errorMessage.value = 'Erro nos dados fornecidos.'
      }
    } else {
      errorMessage.value = 'Erro ao criar conta. Tente novamente.'
    }
  } finally {
    loading.value = false
  }
}

const sideImage = "https://images.unsplash.com/photo-1605497788044-5a32c7078486?q=80&w=1974&auto=format&fit=crop"
</script>

<template>
  <div class="min-h-screen flex font-sans bg-stone-50">

    <div class="flex-1 flex flex-col justify-center py-12 px-4 sm:px-6 lg:flex-none lg:px-24 xl:px-32 text-stone-900 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-stone-50 via-stone-100 to-stone-200">
      <div class="mx-auto w-full max-w-[450px]">
        
        <div class="text-center mb-10">
           <RouterLink to="/" class="inline-block mb-4 group">
             <h2 class="font-serif text-3xl font-bold text-emerald-950 tracking-[0.2em] transition-transform group-hover:scale-105 duration-300">
              BARBER<span class="text-emerald-800">SYS</span>.
            </h2>
           </RouterLink>
          <h3 class="text-xl font-medium text-stone-600 tracking-wide font-serif italic">
            Comece sua jornada de estilo.
          </h3>
        </div>

        <form class="space-y-8" @submit.prevent="handleRegister">
          
          <div class="relative group">
            <input id="name" type="text" required v-model="name" 
              class="peer h-10 w-full border-b-2 border-stone-300 text-stone-900 bg-transparent placeholder-transparent focus:outline-none focus:border-emerald-800 transition-colors pt-1 pb-2 text-lg font-medium"
              placeholder="Nome Completo">
            <label for="name" class="absolute left-0 -top-3.5 text-stone-500 text-sm transition-all peer-placeholder-shown:text-base peer-placeholder-shown:text-stone-400 peer-placeholder-shown:top-2 peer-focus:-top-3.5 peer-focus:text-stone-600 peer-focus:text-sm uppercase tracking-wider font-bold">
              Nome Completo
            </label>
          </div>

          <div class="relative group">
            <input id="email" type="email" required v-model="email" 
              class="peer h-10 w-full border-b-2 border-stone-300 text-stone-900 bg-transparent placeholder-transparent focus:outline-none focus:border-emerald-800 transition-colors pt-1 pb-2 text-lg font-medium"
              placeholder="Email">
            <label for="email" class="absolute left-0 -top-3.5 text-stone-500 text-sm transition-all peer-placeholder-shown:text-base peer-placeholder-shown:text-stone-400 peer-placeholder-shown:top-2 peer-focus:-top-3.5 peer-focus:text-stone-600 peer-focus:text-sm uppercase tracking-wider font-bold">
              Email
            </label>
          </div>

          <div class="relative group">
            <input id="phone" type="tel" required v-model="phone" 
              class="peer h-10 w-full border-b-2 border-stone-300 text-stone-900 bg-transparent placeholder-transparent focus:outline-none focus:border-emerald-800 transition-colors pt-1 pb-2 text-lg font-medium"
              placeholder="(XX) XXXXX-XXXX">
            <label for="phone" class="absolute left-0 -top-3.5 text-stone-500 text-sm transition-all peer-placeholder-shown:text-base peer-placeholder-shown:text-stone-400 peer-placeholder-shown:top-2 peer-focus:-top-3.5 peer-focus:text-stone-600 peer-focus:text-sm uppercase tracking-wider font-bold">
              Celular / WhatsApp
            </label>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="relative group">
                <input id="password" type="password" required v-model="password" 
                class="peer h-10 w-full border-b-2 border-stone-300 text-stone-900 bg-transparent placeholder-transparent focus:outline-none focus:border-emerald-800 transition-colors pt-1 pb-2 text-lg font-medium font-serif tracking-widest"
                placeholder="Senha">
                <label for="password" class="absolute left-0 -top-3.5 text-stone-500 text-sm transition-all peer-placeholder-shown:text-base peer-placeholder-shown:text-stone-400 peer-placeholder-shown:top-2 peer-focus:-top-3.5 peer-focus:text-stone-600 peer-focus:text-sm uppercase tracking-wider font-bold">
                Senha
                </label>
            </div>

            <div class="relative group">
                <input id="password_confirmation" type="password" required v-model="passwordConfirmation" 
                class="peer h-10 w-full border-b-2 border-stone-300 text-stone-900 bg-transparent placeholder-transparent focus:outline-none focus:border-emerald-800 transition-colors pt-1 pb-2 text-lg font-medium font-serif tracking-widest"
                placeholder="Confirmar">
                <label for="password_confirmation" class="absolute left-0 -top-3.5 text-stone-500 text-sm transition-all peer-placeholder-shown:text-base peer-placeholder-shown:text-stone-400 peer-placeholder-shown:top-2 peer-focus:-top-3.5 peer-focus:text-stone-600 peer-focus:text-sm uppercase tracking-wider font-bold">
                Confirmar
                </label>
            </div>
          </div>

          <div v-if="errorMessage" class="text-red-600 text-sm font-medium text-center bg-red-50/50 py-2 px-4 border-l-2 border-red-600 animate-pulse">
            {{ errorMessage }}
          </div>

          <div class="pt-4">
            <button type="submit" :disabled="loading"
              class="relative w-full flex justify-center py-4 px-4 border border-transparent text-sm font-bold uppercase tracking-[0.25em] text-stone-50 bg-emerald-900 hover:bg-emerald-950 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-900 transition-all duration-300 disabled:opacity-70 disabled:cursor-not-allowed overflow-hidden group rounded-sm shadow-xl shadow-emerald-900/20 hover:shadow-emerald-900/30">
               <div class="absolute inset-0 h-full w-full scale-0 rounded-sm transition-all duration-300 group-hover:scale-100 group-hover:bg-white/10"></div>
               <span class="relative z-10" v-if="loading">Criando conta...</span>
               <span class="relative z-10" v-else>CADASTRAR</span>
            </button>
          </div>

           <div class="text-center mt-6 text-sm text-stone-500">
            <p>Já possui cadastro?</p>
            <RouterLink to="/login" class="mt-2 inline-block font-bold text-emerald-900 hover:text-emerald-700 uppercase tracking-widest border-b-2 border-emerald-900/30 hover:border-emerald-900 transition-all pb-1">
              Fazer Login
            </RouterLink>
          </div>
        </form>
      </div>
    </div>

    <div class="hidden lg:flex relative w-0 flex-1 overflow-hidden bg-emerald-950 items-center justify-center">
      <img class="absolute inset-0 h-full w-full object-cover object-center opacity-60 mix-blend-luminosity contrast-125 filter grayscale hover:scale-105 transition-transform duration-[3s]" :src="sideImage" alt="Cuidado Masculino">
      
      <div class="absolute inset-0 bg-emerald-950/60 mix-blend-multiply"></div>
      <div class="absolute inset-0 bg-gradient-to-r from-stone-200/20 to-transparent"></div>
      <div class="absolute inset-0 bg-radial-[at_center,_var(--tw-gradient-stops)] from-transparent via-emerald-950/20 to-emerald-950/80"></div>
      
      <div class="relative z-20 p-16 text-right max-w-lg ml-auto">
        <h2 class="font-serif text-5xl font-bold text-stone-100 tracking-wide leading-tight italic drop-shadow-xl mb-6">
          "Seja notado.<br>Seja lembrado."
        </h2>
        <div class="w-full h-[1px] bg-emerald-500/50 mb-4"></div>
        <p class="text-emerald-200/80 font-sans text-lg font-light leading-relaxed">
            Junte-se ao clube exclusivo BarberSys e tenha acesso aos melhores profissionais da região.
        </p>
      </div>
    </div>

  </div>
</template>