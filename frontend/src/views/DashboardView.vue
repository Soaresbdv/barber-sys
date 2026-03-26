<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

interface Appointment {
  id: number;
  service_id: number;
  start_time: string;
  status: 'scheduled' | 'completed' | 'canceled';
  service?: {
    name: string;
  };
}

const router = useRouter()
const appointments = ref<Appointment[]>([]) 
const isLoading = ref(false)
const activeTab = ref<'upcoming' | 'history'>('upcoming') 

onMounted(async () => {
  const token = localStorage.getItem('token')
  if (!token) {
    return router.push('/login')
  }
  fetchAppointments()
})

const fetchAppointments = async () => {
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get('http://localhost:8000/api/appointments', {
      headers: { Authorization: `Bearer ${token}` }
    })
    appointments.value = response.data
  } catch (error) {
    console.error('Erro ao buscar agendamentos', error)
  }
}

const upcomingAppointments = computed(() => {
  return appointments.value.filter(apt => apt.status === 'scheduled')
})

const historyAppointments = computed(() => {
  return appointments.value.filter(apt => apt.status === 'completed' || apt.status === 'canceled')
})

const handleLogout = async () => {
  isLoading.value = true
  try {
    await axios.post('http://localhost:8000/api/logout', {}, {
      headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
    })
  } catch (error) {
    console.error('Erro ao deslogar', error)
  } finally {
    localStorage.removeItem('token')
    router.push('/login')
    isLoading.value = false
  }
}

const formatDateDirectly = (dateString: string) => {
  if (!dateString) return '';
  
  const normalizedString = dateString.replace('T', ' ').split('.')[0] as string;

  const parts = normalizedString.split(' ');
  if (parts.length < 2) return dateString;

  const datePart = parts[0] as string;
  const timePart = parts[1] as string;
  
  const [year, month, day] = datePart.split('-');
  const [hour, minute] = timePart.split(':');
  
  return `${day}/${month}/${year}, ${hour}:${minute}`;
}

const formatStatus = (status: string) => {
  const map: Record<string, string> = {
    scheduled: 'Agendado',
    completed: 'Concluído',
    canceled: 'Cancelado'
  }
  return map[status] || status
}

const editAppointment = (apt: Appointment) => {
  router.push({ path: '/appointments/new', query: { edit: apt.id.toString() } })
}

const cancelAppointment = async (id: number) => {
  if (!confirm('Deseja realmente cancelar este horário?')) return
  try {
    const token = localStorage.getItem('token')
    await axios.delete(`http://localhost:8000/api/appointments/${id}`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    
    const apt = appointments.value.find(a => a.id === id)
    if (apt) apt.status = 'canceled'
    
  } catch (error) {
    alert('Erro ao cancelar')
  }
}
</script>

<template>
  <div class="min-h-screen bg-[#FBFBFB] text-stone-800 font-sans">
    
    <nav class="bg-white border-b border-stone-100 px-8 py-5 flex justify-between items-center sticky top-0 z-50 shadow-sm">
      <div class="flex items-center gap-8">
        <h1 @click="router.push('/')" class="font-serif text-2xl font-bold text-emerald-950 tracking-widest cursor-pointer">
          BARBER<span class="text-emerald-600">SYS</span>
        </h1>
        
        <button @click="router.push('/')" class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-stone-400 hover:text-emerald-700 transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
          </svg>
          Início
        </button>
      </div>

      <button @click="handleLogout" :disabled="isLoading" class="text-[10px] font-bold tracking-[0.2em] uppercase text-red-400 hover:text-red-600 transition-colors">
        Sair
      </button>
    </nav>

    <main class="max-w-4xl mx-auto py-16 px-6">
      <header class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
          <h2 class="text-4xl font-serif text-stone-900 mb-2">Seus Cortes</h2>
          <p class="text-stone-500 italic">Acompanhe e gerencie seus horários na barbearia.</p>
        </div>
        <button @click="router.push('/appointments/new')" class="bg-emerald-900 hover:bg-emerald-800 text-white text-[10px] font-black uppercase tracking-widest px-8 py-4 transition-transform active:scale-95 shadow-md">
          Novo Agendamento
        </button>
      </header>

      <div class="flex gap-4 border-b border-stone-200 mb-8">
        <button @click="activeTab = 'upcoming'"
          class="pb-3 text-[10px] font-black uppercase tracking-widest transition-colors"
          :class="activeTab === 'upcoming' ? 'text-emerald-700 border-b-2 border-emerald-600' : 'text-stone-400 hover:text-stone-600'">
          Próximos Horários <span v-if="upcomingAppointments.length" class="ml-1 bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded-sm">{{ upcomingAppointments.length }}</span>
        </button>
        <button @click="activeTab = 'history'"
          class="pb-3 text-[10px] font-black uppercase tracking-widest transition-colors"
          :class="activeTab === 'history' ? 'text-stone-800 border-b-2 border-stone-800' : 'text-stone-400 hover:text-stone-600'">
          Histórico <span v-if="historyAppointments.length" class="ml-1 bg-stone-100 text-stone-500 px-1.5 py-0.5 rounded-sm">{{ historyAppointments.length }}</span>
        </button>
      </div>

      <div v-if="(activeTab === 'upcoming' ? upcomingAppointments : historyAppointments).length > 0" class="space-y-4">
        
        <div v-for="apt in (activeTab === 'upcoming' ? upcomingAppointments : historyAppointments)" :key="apt.id" 
          class="bg-white border p-6 flex flex-col md:flex-row md:items-center justify-between shadow-sm transition-all duration-300"
          :class="apt.status === 'scheduled' ? 'border-stone-200 hover:border-emerald-300' : 'border-stone-100 bg-stone-50/50 opacity-80'">
          
          <div class="space-y-2">
            <div class="flex items-center gap-3">
              <span class="text-[9px] font-black uppercase tracking-[0.2em] px-2 py-1 rounded-sm"
                :class="{
                  'bg-emerald-100 text-emerald-700': apt.status === 'scheduled',
                  'bg-stone-200 text-stone-600': apt.status === 'completed',
                  'bg-red-100 text-red-600': apt.status === 'canceled'
                }">
                {{ formatStatus(apt.status) }}
              </span>
            </div>
            
            <h4 class="text-xl font-serif text-stone-800 tracking-tight" :class="{'line-through text-stone-400': apt.status === 'canceled'}">
              {{ apt.service?.name || 'Serviço Profissional' }}
            </h4>
            
            <p class="text-stone-500 text-sm flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-stone-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              {{ formatDateDirectly(apt.start_time) }}
            </p>
          </div>

          <div v-if="apt.status === 'scheduled'" class="flex items-center gap-8 mt-6 md:mt-0 pt-6 md:pt-0 border-t md:border-t-0 border-stone-100">
            <button @click="editAppointment(apt)" 
              class="text-[10px] font-bold uppercase tracking-widest text-emerald-600 hover:text-emerald-800 transition-colors">
              Editar
            </button>
            <button @click="cancelAppointment(apt.id)"
              class="text-[10px] font-bold uppercase tracking-widest text-red-400 hover:text-red-600 transition-colors">
              Cancelar
            </button>
          </div>
        </div>
      </div>

      <div v-else class="py-24 text-center border-2 border-dashed border-stone-200 bg-white rounded-sm">
        <p v-if="activeTab === 'upcoming'" class="font-serif italic text-stone-400 text-lg">Nenhum agendamento futuro no momento.</p>
        <p v-if="activeTab === 'history'" class="font-serif italic text-stone-400 text-lg">Seu histórico está vazio.</p>
        <button v-if="activeTab === 'upcoming'" @click="router.push('/appointments/new')" class="mt-4 text-emerald-600 text-[10px] font-black uppercase tracking-widest hover:underline">
          Marcar um corte agora
        </button>
      </div>
    </main>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=Inter:wght@400;700;900&display=swap');
.font-serif { font-family: 'Playfair Display', serif; }
.font-sans { font-family: 'Inter', sans-serif; }
</style>