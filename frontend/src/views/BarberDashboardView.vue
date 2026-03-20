<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

interface Appointment {
  id: number;
  service_id: number;
  user_id: number;
  start_time: string;
  status: 'scheduled' | 'completed' | 'canceled';
  service?: { name: string };
  user?: { name: string; phone: string };
}

interface BarberBlock {
  id: number;
  date: string;
  start_time: string;
  end_time: string;
}

const router = useRouter()
const appointments = ref<Appointment[]>([]) 
const blocks = ref<BarberBlock[]>([])
const isLoading = ref(false)
const isBlockModalOpen = ref(false)
const isSubmittingBlock = ref(false)
const blockForm = ref({ date: '', start_time: '', end_time: '' })

onMounted(() => {
  const token = localStorage.getItem('token')
  const role = localStorage.getItem('user_role')
  
  if (!token || role === 'client') {
    router.push('/')
    return
  }
  fetchAgenda()
  fetchBlocks() 
})

const fetchAgenda = async () => {
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get('http://localhost:8000/api/barber/agenda', {
      headers: { Authorization: `Bearer ${token}` }
    })
    appointments.value = response.data
  } catch (error) {
    console.error('Erro ao buscar agenda', error)
  }
}

const fetchBlocks = async () => {
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get('http://localhost:8000/api/barber/blocks', {
      headers: { Authorization: `Bearer ${token}` }
    })
    blocks.value = response.data
  } catch (error) {
    console.error('Erro ao buscar bloqueios', error)
  }
}

const submitBlock = async () => {
  isSubmittingBlock.value = true
  try {
    const token = localStorage.getItem('token')
    await axios.post('http://localhost:8000/api/barber/blocks', blockForm.value, {
      headers: { Authorization: `Bearer ${token}` }
    })
    
    blockForm.value = { date: '', start_time: '', end_time: '' }
    fetchBlocks()
    alert('Horário bloqueado com sucesso!')
  } catch (error: any) {
    alert(error.response?.data?.message || 'Erro ao criar bloqueio. Verifique os horários.')
  } finally {
    isSubmittingBlock.value = false
  }
}

const removeBlock = async (id: number) => {
  if (!confirm('Deseja remover este bloqueio e liberar a agenda?')) return
  try {
    const token = localStorage.getItem('token')
    await axios.delete(`http://localhost:8000/api/barber/blocks/${id}`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    fetchBlocks()
  } catch (error) {
    alert('Erro ao remover bloqueio.')
  }
}

const handleLogout = async () => {
  isLoading.value = true
  try {
    const token = localStorage.getItem('token')
    await axios.post('http://localhost:8000/api/logout', {}, {
      headers: { Authorization: `Bearer ${token}` }
    })
  } catch (error) {
    console.error('Erro no logout', error)
  } finally {
    localStorage.clear()
    router.push('/')
    isLoading.value = false
  }
}

const updateStatus = async (id: number, newStatus: string) => {
  const acao = newStatus === 'completed' ? 'Concluir' : 'Cancelar';
  if (!confirm(`Deseja realmente ${acao} este agendamento?`)) return
  
  try {
    const token = localStorage.getItem('token')
    await axios.patch(`http://localhost:8000/api/appointments/${id}/status`, 
      { status: newStatus },
      { headers: { Authorization: `Bearer ${token}` } }
    )
    fetchAgenda() 
  } catch (error) {
    alert('Erro ao atualizar status.')
  }
}

const formatDateDirectly = (dateString: string) => {
  if (!dateString) return { date: '', time: '' };
  const normalizedString = dateString.replace('T', ' ').split('.')[0] as string;
  const parts = normalizedString.split(' ');
  if (parts.length < 2) return { date: dateString, time: '' };
  
  const [year, month, day] = parts[0]!.split('-');
  const [hour, minute] = parts[1]!.split(':');
  return { date: `${day}/${month}/${year}`, time: `${hour}:${minute}` };
}

const formatBlockDate = (dateString: string) => {
  const [year, month, day] = dateString.split('-');
  return `${day}/${month}/${year}`;
}
</script>

<template>
  <div class="min-h-screen bg-[#FBFBFB] text-stone-800 font-sans relative">
    
    <nav class="bg-white border-b border-stone-100 px-8 py-5 flex justify-between items-center sticky top-0 z-40 shadow-sm">
      <div class="flex items-center gap-4">
        <h1 class="font-serif text-2xl font-bold text-emerald-950 tracking-widest">
          BARBER<span class="text-emerald-600">SYS</span>
        </h1>
        <span class="bg-emerald-900 text-white text-[9px] font-black uppercase tracking-widest px-2 py-1 rounded-sm ml-2">
          Profissional
        </span>
      </div>
      <button @click="handleLogout" :disabled="isLoading" class="text-[10px] font-bold tracking-[0.2em] uppercase text-red-400 hover:text-red-600 transition-colors">
        Sair
      </button>
    </nav>

    <main class="max-w-5xl mx-auto py-12 px-6">
      <header class="mb-10 border-b border-stone-200 pb-6 flex flex-col md:flex-row md:justify-between md:items-end gap-6">
        <div>
          <h2 class="text-4xl font-serif text-stone-900 mb-2">Sua Agenda</h2>
          <p class="text-stone-500 italic">Acompanhe seus clientes e gerencie os horários de hoje.</p>
        </div>
        
        <button @click="isBlockModalOpen = true" class="bg-stone-100 hover:bg-stone-200 text-emerald-950 border border-stone-300 text-[10px] font-black uppercase tracking-widest px-8 py-3 transition-colors flex items-center gap-2 shadow-sm">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
          </svg>
          Gerenciar Indisponibilidade
        </button>
      </header>

      <div v-if="appointments.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="apt in appointments" :key="apt.id" 
          class="bg-white border-l-4 p-6 shadow-sm flex flex-col justify-between transition-all"
          :class="{
            'border-emerald-600': apt.status === 'scheduled',
            'border-stone-300 opacity-60': apt.status === 'completed',
            'border-red-400 opacity-60': apt.status === 'canceled'
          }">
          
          <div class="flex justify-between items-start mb-6">
            <div>
              <span class="text-[10px] font-black uppercase tracking-widest text-stone-400 block mb-1">
                {{ formatDateDirectly(apt.start_time).date }}
              </span>
              <span class="text-3xl font-serif text-emerald-950 block leading-none">
                {{ formatDateDirectly(apt.start_time).time }}
              </span>
            </div>
            <span class="text-[9px] font-black uppercase tracking-widest px-2 py-1 rounded-sm"
              :class="{
                'bg-emerald-50 text-emerald-700': apt.status === 'scheduled',
                'bg-stone-100 text-stone-500': apt.status === 'completed',
                'bg-red-50 text-red-700': apt.status === 'canceled'
              }">
              {{ apt.status === 'scheduled' ? 'Agendado' : (apt.status === 'completed' ? 'Concluído' : 'Cancelado') }}
            </span>
          </div>

          <div class="mb-6">
            <h4 class="text-lg font-bold text-stone-800 uppercase tracking-tight">{{ apt.user?.name || 'Cliente Oculto' }}</h4>
            <p class="text-sm text-stone-500 flex items-center gap-2 mt-1">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-stone-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
              {{ apt.service?.name || 'Serviço Padrão' }}
            </p>
            <p v-if="apt.user?.phone" class="text-xs text-stone-400 mt-2 font-medium">📱 {{ apt.user.phone }}</p>
          </div>

          <div v-if="apt.status === 'scheduled'" class="flex gap-2 mt-auto pt-4 border-t border-stone-100">
            <button @click="updateStatus(apt.id, 'completed')" class="flex-1 bg-emerald-900 text-white text-[10px] font-black uppercase tracking-widest py-3 hover:bg-emerald-800 transition-colors rounded-sm shadow-md">
              Concluir
            </button>
            <button @click="updateStatus(apt.id, 'canceled')" class="flex-1 bg-white border border-stone-200 text-red-600 text-[10px] font-black uppercase tracking-widest py-3 hover:bg-red-50 transition-colors rounded-sm">
              Cancelar
            </button>
          </div>
        </div>
      </div>

      <div v-else class="py-24 text-center border-2 border-dashed border-stone-200 rounded-xl bg-white">
        <p class="font-serif italic text-stone-400 text-lg">Sua agenda está livre no momento.</p>
      </div>
    </main>

    <Teleport to="body">
      <div v-if="isBlockModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-900/40 backdrop-blur-sm p-4 font-sans">
        <div class="bg-white max-w-lg w-full p-8 shadow-2xl border-t-4 border-emerald-800 relative rounded-sm">
          
          <button @click="isBlockModalOpen = false" class="absolute top-5 right-5 text-stone-400 hover:text-red-500 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
          
          <h3 class="text-2xl font-serif text-stone-900 mb-1">Bloquear Agenda</h3>
          <p class="text-stone-500 text-sm italic mb-6">Defina seus momentos de pausa, almoço ou folga.</p>

          <form @submit.prevent="submitBlock" class="space-y-4 mb-8 bg-stone-50 p-6 border border-stone-100">
             <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <label class="block text-[10px] font-black uppercase tracking-widest text-stone-500 mb-1">Data</label>
                  <input type="date" v-model="blockForm.date" required class="w-full border border-stone-300 p-2 text-sm focus:border-emerald-600 focus:outline-none">
                </div>
                <div>
                  <label class="block text-[10px] font-black uppercase tracking-widest text-stone-500 mb-1">Início (HH:mm)</label>
                  <input type="time" v-model="blockForm.start_time" required class="w-full border border-stone-300 p-2 text-sm focus:border-emerald-600 focus:outline-none">
                </div>
                <div>
                  <label class="block text-[10px] font-black uppercase tracking-widest text-stone-500 mb-1">Fim (HH:mm)</label>
                  <input type="time" v-model="blockForm.end_time" required class="w-full border border-stone-300 p-2 text-sm focus:border-emerald-600 focus:outline-none">
                </div>
             </div>
             <button type="submit" :disabled="isSubmittingBlock" class="w-full mt-4 bg-emerald-900 text-white text-[10px] font-black uppercase tracking-widest py-3 hover:bg-emerald-800 transition-colors disabled:opacity-50">
               {{ isSubmittingBlock ? 'Salvando...' : 'Adicionar Bloqueio' }}
             </button>
          </form>

          <h4 class="text-[10px] font-black uppercase tracking-widest text-stone-400 mb-3 border-b border-stone-100 pb-2">Bloqueios Ativos (Futuros)</h4>
          
          <div v-if="blocks.length > 0" class="space-y-2 max-h-48 overflow-y-auto pr-2 custom-scrollbar">
            <div v-for="block in blocks" :key="block.id" class="flex justify-between items-center bg-white p-3 border border-stone-200 shadow-sm hover:border-emerald-200 transition-colors">
               <div>
                 <span class="block text-sm font-bold text-stone-800">{{ formatBlockDate(block.date) }}</span>
                 <span class="block text-xs text-stone-500 font-medium">
                   Das <span class="text-emerald-800">{{ block.start_time.substring(0,5) }}</span> às <span class="text-emerald-800">{{ block.end_time.substring(0,5) }}</span>
                 </span>
               </div>
               <button @click="removeBlock(block.id)" title="Liberar Horário" class="text-red-400 hover:text-red-600 p-2 transition-colors">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
               </button>
            </div>
          </div>
          
          <div v-else class="text-center py-6 border border-dashed border-stone-200 bg-stone-50">
            <p class="text-xs text-stone-400 italic font-serif">Nenhum bloqueio cadastrado para o futuro.</p>
          </div>

        </div>
      </div>
    </Teleport>

  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=Inter:wght@400;500;700;900&display=swap');
.font-serif { font-family: 'Playfair Display', serif; }
.font-sans { font-family: 'Inter', sans-serif; }

.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #f5f5f4; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #d6d3d1; border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #064e3b; }
</style>