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

const router = useRouter()
const appointments = ref<Appointment[]>([]) 
const isLoading = ref(false)

onMounted(() => {
  const token = localStorage.getItem('token')
  const role = localStorage.getItem('user_role')
  
  if (!token || role === 'client') {
    router.push('/')
    return
  }
  fetchAgenda()
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
</script>

<template>
  <div class="min-h-screen bg-[#FBFBFB] text-stone-800 font-sans">
    <nav class="bg-white border-b border-stone-100 px-8 py-5 flex justify-between items-center sticky top-0 z-50 shadow-sm">
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

    <main class="max-w-4xl mx-auto py-12 px-6">
      <header class="mb-10 border-b border-stone-200 pb-6">
        <h2 class="text-4xl font-serif text-stone-900 mb-2">Sua Agenda</h2>
        <p class="text-stone-500 italic">Acompanhe seus clientes e gerencie os horários de hoje.</p>
      </header>

      <div v-if="appointments.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
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
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=Inter:wght@400;700;900&display=swap');
.font-serif { font-family: 'Playfair Display', serif; }
.font-sans { font-family: 'Inter', sans-serif; }
</style>