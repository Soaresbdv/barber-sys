<script setup lang="ts">
import { useRouter } from 'vue-router'
import axios from 'axios'
import { ref, onMounted, onUnmounted, computed } from 'vue' 

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

const activeAgendaTab = ref<'upcoming' | 'history'>('upcoming')

onMounted(() => {
  const token = localStorage.getItem('token')
  const role = localStorage.getItem('user_role')
  
  if (!token || role === 'client') {
    router.push('/')
    return
  }
  fetchAgenda()
  fetchBlocks() 
  fetchNotifications()

  pollingInterval = setInterval(fetchNotifications, 30000) 
})

onUnmounted(() => {
  if (pollingInterval) clearInterval(pollingInterval)
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

const upcomingAppointments = computed(() => {
  return appointments.value.filter(apt => apt.status === 'scheduled')
})

const historyAppointments = computed(() => {
  return appointments.value.filter(apt => apt.status === 'completed' || apt.status === 'canceled')
})

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
    
    const apt = appointments.value.find(a => a.id === id)
    if (apt) apt.status = newStatus as any
    
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

const notifications = ref<any[]>([]) 
const history = ref<any[]>([])       
const showNotifications = ref(false)
const activeTab = ref('unread')      
let pollingInterval: any = null

const fetchNotifications = async () => {
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get('http://localhost:8000/api/notifications/unread', {
      headers: { Authorization: `Bearer ${token}` }
    })
    notifications.value = response.data
  } catch (error) {
    console.error('Erro ao buscar notificações', error)
  }
}

const fetchHistory = async () => {
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get('http://localhost:8000/api/notifications', {
      headers: { Authorization: `Bearer ${token}` }
    })
    history.value = response.data
  } catch (error) {
    console.error('Erro ao buscar histórico', error)
  }
}

const toggleNotifications = async () => {
  showNotifications.value = !showNotifications.value
  if (showNotifications.value) {
    await fetchHistory() 
  }
}

const markAsRead = async (id: string) => {
  try {
    const token = localStorage.getItem('token')
    await axios.patch(`http://localhost:8000/api/notifications/${id}/read`, {}, {
      headers: { Authorization: `Bearer ${token}` }
    })

    notifications.value = notifications.value.filter(n => n.id !== id)
    
    const histItem = history.value.find(n => n.id === id)
    if (histItem) histItem.read_at = new Date().toISOString()
    
  } catch (error) {
    console.error('Erro ao marcar como lida', error)
  }
}
</script>

<template>
  <div class="min-h-screen bg-[#FBFBFB] text-stone-800 font-sans relative">
    
    <nav class="bg-white border-b border-stone-200 px-8 py-5 flex justify-between items-center sticky top-0 z-40">
      <div class="flex items-center gap-4">
        <h1 class="font-serif text-2xl font-bold text-emerald-950 tracking-widest">
          BARBER<span class="text-emerald-700">SYS</span>
        </h1>
        <span class="bg-emerald-900 text-white text-[9px] font-black uppercase tracking-widest px-2 py-1 rounded-sm ml-2">
          Profissional
        </span>
      </div>

      <div class="flex items-center gap-8 relative">        
        <RouterLink to="/profile" class="text-[10px] font-black uppercase tracking-widest text-stone-400 hover:text-emerald-700 transition-colors">
          Meu Perfil
        </RouterLink>
        <div class="relative">
          <button @click="toggleNotifications" class="text-stone-400 hover:text-emerald-600 transition-colors relative pt-1 outline-none">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" :class="{'animate-pulse text-emerald-600': notifications.length > 0}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span v-if="notifications.length > 0" class="absolute top-0 right-0 -mt-1 -mr-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[9px] font-bold text-white ring-2 ring-white shadow-sm">
              {{ notifications.length }}
            </span>
          </button>

          <div v-if="showNotifications" class="absolute right-0 mt-4 w-80 bg-white border border-stone-200 shadow-2xl rounded-sm overflow-hidden z-50 transition-all">
            <div class="bg-stone-50 border-b border-stone-100 flex">
              <button @click="activeTab = 'unread'" class="flex-1 py-3 text-[10px] font-black uppercase tracking-widest transition-colors" :class="activeTab === 'unread' ? 'text-emerald-700 border-b-2 border-emerald-600' : 'text-stone-400 hover:bg-stone-100'">
                Novas <span v-if="notifications.length > 0">({{ notifications.length }})</span>
              </button>
              <button @click="activeTab = 'history'" class="flex-1 py-3 text-[10px] font-black uppercase tracking-widest transition-colors" :class="activeTab === 'history' ? 'text-stone-800 border-b-2 border-stone-800' : 'text-stone-400 hover:bg-stone-100'">
                Histórico
              </button>
            </div>
            
            <div class="max-h-80 overflow-y-auto">
              <div v-for="notif in (activeTab === 'unread' ? notifications : history)" :key="notif.id">
                <button @click="activeTab === 'unread' ? markAsRead(notif.id) : null"
                  class="w-full text-left px-4 py-4 border-b border-stone-50 transition-colors flex gap-3 items-start group"
                  :class="[activeTab === 'unread' ? 'hover:bg-stone-50 cursor-pointer' : 'cursor-default', notif.read_at && activeTab === 'history' ? 'opacity-60 bg-stone-50/50' : '']">
                  <div class="mt-0.5 p-1.5 rounded-full transition-all" :class="notif.type === 'canceled_appointment' ? 'bg-red-100 text-red-600' : 'bg-emerald-100 text-emerald-600'">
                    <svg v-if="notif.type === 'canceled_appointment'" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" /></svg>
                  </div>
                  <div>
                    <p class="text-sm font-bold text-stone-800 leading-tight mb-1">{{ notif.data.message }}</p>
                    <p class="text-[10px] font-medium uppercase tracking-wider" :class="notif.type === 'canceled_appointment' ? 'text-red-600' : 'text-emerald-600'">
                      Horário: {{ new Date(notif.data.start_time).toLocaleString('pt-BR', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' }) }}
                    </p>
                  </div>
                </button>
              </div>
              <div v-if="(activeTab === 'unread' && notifications.length === 0)" class="px-4 py-8 text-center">
                <p class="text-xs text-stone-400 font-medium">Você está em dia!</p>
              </div>
              <div v-if="(activeTab === 'history' && history.length === 0)" class="px-4 py-8 text-center">
                <p class="text-xs text-stone-400 font-medium">Histórico vazio.</p>
              </div>
            </div>
          </div> 
        </div> 
        <button @click="handleLogout" class="text-[10px] font-bold tracking-[0.2em] uppercase text-red-400 hover:text-red-600 transition-colors">
          Sair
        </button>
      </div> 
    </nav>

    <main class="max-w-5xl mx-auto py-12 px-6">
      <header class="mb-10 flex flex-col md:flex-row md:justify-between md:items-end gap-6 border-b border-stone-200 pb-6">
        <div>
          <h2 class="text-4xl font-serif text-stone-900 mb-2">Sua Agenda</h2>
          <p class="text-stone-500 italic">Acompanhe seus clientes e gerencie os horários.</p>
        </div>
        
        <button @click="isBlockModalOpen = true" class="bg-stone-100 hover:bg-stone-200 text-emerald-950 border border-stone-300 text-[10px] font-black uppercase tracking-widest px-8 py-3 transition-colors flex items-center gap-2 shadow-sm">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
          </svg>
          Gerenciar Indisponibilidade
        </button>
      </header>

      <div class="flex gap-4 border-b border-stone-200 mb-8">
        <button @click="activeAgendaTab = 'upcoming'"
          class="pb-3 text-[10px] font-black uppercase tracking-widest transition-colors"
          :class="activeAgendaTab === 'upcoming' ? 'text-emerald-700 border-b-2 border-emerald-600' : 'text-stone-400 hover:text-stone-600'">
          Próximos Clientes <span v-if="upcomingAppointments.length" class="ml-1 bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded-sm">{{ upcomingAppointments.length }}</span>
        </button>
        <button @click="activeAgendaTab = 'history'"
          class="pb-3 text-[10px] font-black uppercase tracking-widest transition-colors"
          :class="activeAgendaTab === 'history' ? 'text-stone-800 border-b-2 border-stone-800' : 'text-stone-400 hover:text-stone-600'">
          Histórico de Atendimentos <span v-if="historyAppointments.length" class="ml-1 bg-stone-100 text-stone-500 px-1.5 py-0.5 rounded-sm">{{ historyAppointments.length }}</span>
        </button>
      </div>

      <div v-if="(activeAgendaTab === 'upcoming' ? upcomingAppointments : historyAppointments).length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="apt in (activeAgendaTab === 'upcoming' ? upcomingAppointments : historyAppointments)" :key="apt.id" 
          class="bg-white border-l-4 p-6 shadow-sm flex flex-col justify-between transition-all"
          :class="{
            'border-emerald-600 hover:border-emerald-500': apt.status === 'scheduled',
            'border-stone-300 opacity-60 bg-stone-50': apt.status === 'completed',
            'border-red-400 opacity-60 bg-red-50/30': apt.status === 'canceled'
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
                'bg-stone-200 text-stone-600': apt.status === 'completed',
                'bg-red-100 text-red-700': apt.status === 'canceled'
              }">
              {{ apt.status === 'scheduled' ? 'Agendado' : (apt.status === 'completed' ? 'Concluído' : 'Cancelado') }}
            </span>
          </div>

          <div class="mb-6">
            <h4 class="text-lg font-bold text-stone-800 uppercase tracking-tight" :class="{'line-through text-stone-400': apt.status === 'canceled'}">
              {{ apt.user?.name || 'Cliente Oculto' }}
            </h4>
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
        <p v-if="activeAgendaTab === 'upcoming'" class="font-serif italic text-stone-400 text-lg">Sua agenda está livre no momento.</p>
        <p v-if="activeAgendaTab === 'history'" class="font-serif italic text-stone-400 text-lg">Nenhum histórico de atendimento.</p>
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