<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

interface Appointment {
  id: number;
  user_id: number;
  barber_id: number;
  service_id: number;
  start_time: string;
  end_time: string;
  status: 'scheduled' | 'completed' | 'canceled';
  service?: {
    name: string;
  };
}

const router = useRouter()
const userName = ref('')
const isLoading = ref(false)
const appointments = ref<Appointment[]>([])

onMounted(async () => {
  const token = localStorage.getItem('token')
  if (!token) {
    router.push('/login')
    return
  }
  
  await fetchAppointments()
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

const formatStatus = (status: string) => {
  const labels: Record<string, string> = {
    scheduled: 'Agendado',
    completed: 'Concluído',
    canceled: 'Cancelado'
  }
  return labels[status] || status
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
        <p class="text-stone-600 mb-8">Olá! Aqui você pode gerenciar seus horários.</p>
        
        <div class="mt-8">
          <h3 class="text-xl font-serif text-stone-800 mb-4 border-b pb-2">Meus Agendamentos</h3>
          
          <div v-if="appointments.length > 0" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-stone-200">
              <thead class="bg-stone-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-bold text-stone-500 uppercase tracking-wider">Serviço</th>
                  <th class="px-6 py-3 text-left text-xs font-bold text-stone-500 uppercase tracking-wider">Data/Hora</th>
                  <th class="px-6 py-3 text-left text-xs font-bold text-stone-500 uppercase tracking-wider">Status</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-stone-100">
                <tr v-for="apt in appointments" :key="apt.id">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-stone-900">
                    {{ apt.service?.name || 'Serviço #' + apt.service_id }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-stone-600">
                    {{ new Date(apt.start_time).toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short' }) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="{
                      'px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border': true,
                      'bg-green-50 text-green-800 border-green-200': apt.status === 'completed',
                      'bg-blue-50 text-blue-800 border-blue-200': apt.status === 'scheduled',
                      'bg-red-50 text-red-800 border-red-200': apt.status === 'canceled'
                    }">
                      {{ formatStatus(apt.status) }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <div v-else class="text-center py-12 border-2 border-dashed border-stone-300 rounded-lg text-stone-400">
            <p>Você ainda não possui nenhum agendamento registrado.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>