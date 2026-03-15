<script setup lang="ts">
import { ref, onMounted } from 'vue'
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
  router.push({ path: '/appointments/new', query: { edit: apt.id } })
}

const cancelAppointment = async (id: number) => {
  if (!confirm('Deseja cancelar?')) return
  try {
    const token = localStorage.getItem('token')
    await axios.delete(`http://localhost:8000/api/appointments/${id}`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    fetchAppointments() 
  } catch (error) {
    alert('Erro ao cancelar')
  }
}
</script>

<template>
  <div class="min-h-screen bg-stone-50 font-sans">
    <nav class="bg-white border-b border-stone-200 px-8 py-4 flex justify-between items-center">
      <h1 class="font-serif text-2xl font-bold text-emerald-950 tracking-widest">
        BARBER<span class="text-emerald-700">SYS</span>.
      </h1>
      <button @click="handleLogout" :disabled="isLoading" class="text-red-700 font-bold text-sm border border-red-200 px-4 py-2 rounded-sm hover:bg-red-50">
        SAIR DO SISTEMA
      </button>
    </nav>

    <div class="max-w-7xl mx-auto py-12 px-4">
      <div class="bg-white shadow-xl rounded-lg border-t-4 border-emerald-900 p-8">
        <h2 class="text-3xl font-serif text-stone-800 mb-2">Bem-vindo ao seu Painel</h2>
        <p class="text-stone-600 mb-8">Olá! Aqui você pode gerenciar seus horários.</p>
        
        <h3 class="text-xl font-serif text-stone-800 mb-4 border-b pb-2">Meus Agendamentos</h3>
        
        <div v-if="appointments.length > 0" class="overflow-x-auto">
          <table class="min-w-full divide-y divide-stone-200">
            <thead class="bg-stone-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-bold text-stone-500 uppercase">Serviço</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-stone-500 uppercase">Data/Hora</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-stone-500 uppercase">Status</th>
                <th class="px-6 py-3 text-right text-xs font-bold text-stone-500 uppercase">Ações</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-stone-100">
              <tr v-for="apt in appointments" :key="apt.id">
                <td class="px-6 py-4 text-sm font-medium text-stone-900">
                  {{ apt.service?.name || 'Serviço #' + apt.service_id }}
                </td>
                <td class="px-6 py-4 text-sm text-stone-600">
                  {{ formatDateDirectly(apt.start_time) }}
                </td>
                <td class="px-6 py-4">
                  <span :class="{
                    'px-3 py-1 text-xs font-semibold rounded-full border': true,
                    'bg-blue-50 text-blue-800 border-blue-200': apt.status === 'scheduled',
                    'bg-red-50 text-red-800 border-red-200': apt.status === 'canceled'
                  }">
                    {{ formatStatus(apt.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right text-sm font-medium space-x-3">
                  <button v-if="apt.status === 'scheduled'" @click="editAppointment(apt)" class="text-emerald-600 hover:underline">Editar</button>
                  <button v-if="apt.status === 'scheduled'" @click="cancelAppointment(apt.id)" class="text-red-600 hover:underline">Cancelar</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="text-center py-12 border-2 border-dashed border-stone-300 text-stone-400 rounded-lg">
          Nenhum agendamento encontrado.
        </div>
      </div>
    </div>
  </div>
</template>