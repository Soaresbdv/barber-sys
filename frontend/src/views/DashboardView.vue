<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const userName = ref('')
const isLoading = ref(false)

onMounted(() => {
  if (!localStorage.getItem('token')) {
    router.push('/login')
  }
})

const handleLogout = async () => {
  isLoading.value = true
  try {
    await axios.post('http://localhost:8000/api/logout', {}, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`
      }
    })
  } catch (error) {
    console.error('Erro ao deslogar no servidor', error)
  } finally {
    localStorage.removeItem('token')
    localStorage.removeItem('user_role')
    
    alert('Você saiu do sistema com segurança.')
    router.push('/login')
    isLoading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-stone-50 font-sans">
    
    <nav class="bg-white border-b border-stone-200 px-8 py-4 flex justify-between items-center">
      <h1 class="font-serif text-2xl font-bold text-emerald-950 tracking-widest">
        BARBER<span class="text-emerald-700">SYS</span>.
      </h1>
      <button @click="handleLogout" :disabled="isLoading" class="text-red-700 hover:text-red-900 font-bold text-sm uppercase tracking-wider border border-red-200 px-4 py-2 rounded-sm hover:bg-red-50 transition-colors">
        {{ isLoading ? 'Saindo...' : 'Sair do Sistema' }}
      </button>
    </nav>

    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border-t-4 border-emerald-900 p-8">
        <h2 class="text-3xl font-serif text-stone-800 mb-4">Bem-vindo ao seu Painel</h2>
        <p class="text-stone-600 mb-8">Olá, {{ userName }}! Você está logado no sistema. (waiting)</p>
        
        <div class="border-2 border-dashed border-stone-300 rounded-lg h-64 flex items-center justify-center text-stone-400">
            (waiting)
        </div>
      </div>
    </div>
  </div>
</template>