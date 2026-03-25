<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const barbers = ref<any[]>([])
const isLoading = ref(true)
const isSubmitting = ref(false)

const barberForm = ref({
  name: '',
  email: '',
  password: ''
})

onMounted(() => {
  fetchBarbers()
})

const fetchBarbers = async () => {
  isLoading.value = true
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get('http://localhost:8000/api/admin/barbers', {
      headers: { Authorization: `Bearer ${token}` }
    })
    barbers.value = response.data
  } catch (error) {
    console.error('Erro ao buscar profissionais', error)
  } finally {
    isLoading.value = false
  }
}

const submitBarber = async () => {
  isSubmitting.value = true
  try {
    const token = localStorage.getItem('token')
    await axios.post('http://localhost:8000/api/admin/barbers', barberForm.value, {
      headers: { Authorization: `Bearer ${token}` }
    })
    
    // Limpa o formulário após o sucesso
    barberForm.value.name = ''
    barberForm.value.email = ''
    barberForm.value.password = ''
    
    await fetchBarbers() // Recarrega a lista
    alert('Profissional cadastrado com sucesso!')
  } catch (error: any) {
    alert(error.response?.data?.message || 'Erro ao cadastrar profissional.')
  } finally {
    isSubmitting.value = false
  }
}

const deleteBarber = async (id: number, name: string) => {
  if (!confirm(`Tem certeza que deseja remover o(a) barbeiro(a) ${name} da equipe? Esta ação não pode ser desfeita.`)) {
    return
  }

  try {
    const token = localStorage.getItem('token')
    await axios.delete(`http://localhost:8000/api/admin/barbers/${id}`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    
    await fetchBarbers() // Atualiza a lista
  } catch (error: any) {
    alert(error.response?.data?.message || 'Erro ao remover profissional.')
  }
}

const handleLogout = () => {
  localStorage.clear()
  router.push('/login')
}
</script>

<template>
  <div class="min-h-screen bg-stone-100 font-sans text-stone-800 pb-20">
    
    <nav class="bg-stone-900 border-b border-stone-800 px-8 py-5 flex justify-between items-center sticky top-0 z-40 shadow-md">
      <div class="flex items-center gap-4">
        <h1 class="font-serif text-2xl font-bold text-white tracking-widest cursor-pointer" @click="router.push('/admin/dashboard')">
          BARBER<span class="text-emerald-500">SYS</span>
        </h1>
        <span class="bg-emerald-600 text-white text-[9px] font-black uppercase tracking-widest px-2 py-1 rounded-sm ml-2">
          Admin
        </span>
      </div>
      <div class="flex items-center gap-6">
        <button @click="router.push('/admin/dashboard')" class="text-[10px] font-bold tracking-[0.2em] uppercase text-stone-400 hover:text-white transition-colors">
          Dashboard
        </button>
        <button @click="handleLogout" class="text-[10px] font-bold tracking-[0.2em] uppercase text-stone-400 hover:text-white transition-colors">
          Sair
        </button>
      </div>
    </nav>

    <main class="max-w-7xl mx-auto px-6 py-12">
      
      <header class="mb-10">
        <h2 class="text-3xl font-serif text-stone-900 mb-1">Gestão de Equipe</h2>
        <p class="text-stone-500 text-sm font-medium uppercase tracking-widest">Controle de acessos e profissionais</p>
      </header>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-1 bg-white p-8 border border-stone-200 shadow-sm self-start sticky top-28">
          <h3 class="font-serif text-xl text-stone-900 mb-6 border-b border-stone-100 pb-4">Novo Profissional</h3>
          
          <form @submit.prevent="submitBarber" class="space-y-5">
            <div>
              <label class="block text-[10px] font-black text-stone-500 uppercase tracking-widest mb-1">Nome Completo</label>
              <input type="text" v-model="barberForm.name" required placeholder="Ex: João Navalha" class="w-full border-b-2 border-stone-200 py-2 focus:outline-none focus:border-emerald-600 bg-transparent">
            </div>
            
            <div>
              <label class="block text-[10px] font-black text-stone-500 uppercase tracking-widest mb-1">Email de Acesso</label>
              <input type="email" v-model="barberForm.email" required placeholder="joao@barbersys.com" class="w-full border-b-2 border-stone-200 py-2 focus:outline-none focus:border-emerald-600 bg-transparent">
            </div>

            <div>
              <label class="block text-[10px] font-black text-stone-500 uppercase tracking-widest mb-1">Senha Provisória</label>
              <input type="password" v-model="barberForm.password" required minlength="6" placeholder="Mínimo 6 caracteres" class="w-full border-b-2 border-stone-200 py-2 focus:outline-none focus:border-emerald-600 bg-transparent">
            </div>

            <button type="submit" :disabled="isSubmitting" class="w-full mt-4 bg-emerald-900 text-white text-[10px] font-black uppercase tracking-widest py-4 hover:bg-emerald-800 transition-colors shadow-md disabled:opacity-50">
              {{ isSubmitting ? 'Cadastrando...' : 'Cadastrar Barbeiro' }}
            </button>
          </form>
        </div>

        <div class="lg:col-span-2 bg-white p-8 border border-stone-200 shadow-sm">
          <h3 class="font-serif text-xl text-stone-900 mb-6 border-b border-stone-100 pb-4">Profissionais Ativos</h3>
          
          <div v-if="isLoading" class="text-center py-12">
            <p class="font-serif italic text-stone-400 animate-pulse">Carregando equipe...</p>
          </div>

          <div v-else-if="barbers.length > 0" class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr>
                  <th class="py-3 px-4 border-b border-stone-200 text-[10px] font-black text-stone-400 uppercase tracking-widest">Profissional</th>
                  <th class="py-3 px-4 border-b border-stone-200 text-[10px] font-black text-stone-400 uppercase tracking-widest">Email</th>
                  <th class="py-3 px-4 border-b border-stone-200 text-[10px] font-black text-stone-400 uppercase tracking-widest text-right">Ação</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="barber in barbers" :key="barber.id" class="hover:bg-stone-50 transition-colors border-b border-stone-100 group">
                  <td class="py-4 px-4">
                    <div class="flex items-center gap-3">
                      <div class="w-8 h-8 rounded-full bg-stone-200 flex items-center justify-center text-stone-600 font-serif font-bold text-xs">
                        {{ barber.name.charAt(0) }}
                      </div>
                      <span class="text-sm font-bold text-stone-800">{{ barber.name }}</span>
                    </div>
                  </td>
                  <td class="py-4 px-4 text-sm font-medium text-stone-500">{{ barber.email }}</td>
                  <td class="py-4 px-4 text-right">
                    <button @click="deleteBarber(barber.id, barber.name)" class="text-[10px] font-black uppercase tracking-widest text-red-400 hover:text-red-600 opacity-50 group-hover:opacity-100 transition-all">
                      Remover
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <div v-else class="text-center py-12 border-2 border-dashed border-stone-100">
            <p class="font-serif italic text-stone-400">Nenhum barbeiro cadastrado no momento.</p>
          </div>
        </div>

      </div>
    </main>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=Inter:wght@400;500;700;900&display=swap');
.font-serif { font-family: 'Playfair Display', serif; }
.font-sans { font-family: 'Inter', sans-serif; }
</style>