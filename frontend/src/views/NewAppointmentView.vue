<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const route = useRoute()

const services = ref<any[]>([])
const barbers = ref<any[]>([])
const availableSlots = ref<string[]>([])

const selectedService = ref<any>(null)
const selectedBarber = ref<any>(null)
const selectedDate = ref('')
const selectedTime = ref('')

const loading = ref(false)
const calculating = ref(false) 
const isEditing = ref(false)
const appointmentId = ref<string | null>(null)

onMounted(async () => {
  const token = localStorage.getItem('token')
  if (!token) return router.push('/login')

  try {
    const config = { headers: { Authorization: `Bearer ${token}` } }
    
    const [resServices, resBarbers] = await Promise.all([
      axios.get('http://localhost:8000/api/services', config),
      axios.get('http://localhost:8000/api/barbers', config)
    ])
    
    services.value = resServices.data
    barbers.value = resBarbers.data

    if (route.query.edit) {
      isEditing.value = true
      appointmentId.value = route.query.edit as string
      await loadAppointmentForEdit(appointmentId.value)
    }
  } catch (error) {
    console.error('Erro ao carregar dados iniciais', error)
  }
})

const loadAppointmentForEdit = async (id: string) => {
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get(`http://localhost:8000/api/appointments`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    
    const apt = response.data.find((a: any) => a.id == id)
    
    if (apt) {
      selectedBarber.value = barbers.value.find(b => b.id === apt.barber_id)
      selectedService.value = services.value.find(s => s.id === apt.service_id)
      
      const dateObj = new Date(apt.start_time)
      selectedDate.value = dateObj.toISOString().split('T')[0] ?? '';
      
      selectedTime.value = dateObj.getHours().toString().padStart(2, '0') + ':' + 
                          dateObj.getMinutes().toString().padStart(2, '0')
    }
  } catch (error) {
    console.error('Erro ao carregar agendamento para edição', error)
  }
}

watch([selectedBarber, selectedService, selectedDate], async () => {
  if (selectedBarber.value && selectedService.value && selectedDate.value) {
    fetchAvailability()
  } else {
    availableSlots.value = []
  }
})

const fetchAvailability = async () => {
  calculating.value = true
  if (!isEditing.value) selectedTime.value = '' 
  
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get('http://localhost:8000/api/availability', {
      headers: { Authorization: `Bearer ${token}` },
      params: {
        date: selectedDate.value,
        barber_id: selectedBarber.value.id,
        service_id: selectedService.value.id
      }
    })
    availableSlots.value = response.data.available_slots
  } catch (error) {
    console.error('Erro ao buscar horários', error)
  } finally {
    calculating.value = false
  }
}

const confirmAppointment = async () => {
  if (!selectedTime.value) return
  
  loading.value = true
  try {
    const token = localStorage.getItem('token')
    
    const payload = {
      barber_id: selectedBarber.value.id,
      service_id: selectedService.value.id,
      date: selectedDate.value, 
      time: selectedTime.value  
    }

    const config = { headers: { Authorization: `Bearer ${token}` } }

    if (isEditing.value) {
      await axios.put(`http://localhost:8000/api/appointments/${appointmentId.value}`, payload, config)
    } else {
      await axios.post('http://localhost:8000/api/appointments', payload, config)
    }

    alert('Agendamento salvo com sucesso!')
    router.push('/dashboard')
  } catch (error: any) {
    alert(error.response?.data?.message || 'Erro ao processar agendamento.')
  } finally {
    loading.value = false
  }
}

const formatMoney = (val: any) => {
  return parseFloat(val).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })
}
</script>

<template>
  <div class="min-h-screen bg-stone-50 font-sans pb-20">
    
    <nav class="bg-white border-b border-stone-200 px-6 py-4 mb-8">
      <div class="max-w-3xl mx-auto flex justify-between items-center">
        <h1 class="font-serif text-xl font-bold text-emerald-950 tracking-widest">
          NOVO<span class="text-emerald-700">AGENDAMENTO</span>.
        </h1>
        <button @click="router.push('/dashboard')" class="text-stone-500 hover:text-stone-800 font-bold text-sm uppercase">
          Voltar
        </button>
      </div>
    </nav>

    <div class="max-w-3xl mx-auto px-4 sm:px-6">
      
      <section class="mb-10">
        <h2 class="text-lg font-bold text-stone-700 uppercase tracking-wider mb-4 border-l-4 border-emerald-600 pl-3">
          1. Escolha o Serviço
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div 
            v-for="service in services" 
            :key="service.id"
            @click="selectedService = service"
            class="cursor-pointer border rounded-lg p-4 transition-all hover:shadow-md flex justify-between items-center"
            :class="selectedService?.id === service.id ? 'border-emerald-600 bg-emerald-50 ring-1 ring-emerald-600' : 'border-stone-200 bg-white'"
          >
            <div>
              <h3 class="font-bold text-emerald-950">{{ service.name }}</h3>
              <p class="text-xs text-stone-500">{{ service.duration_minutes }} min</p>
            </div>
            <span class="font-serif font-bold text-emerald-800">{{ formatMoney(service.price) }}</span>
          </div>
        </div>
      </section>

      <section class="mb-10" v-if="selectedService">
        <h2 class="text-lg font-bold text-stone-700 uppercase tracking-wider mb-4 border-l-4 border-emerald-600 pl-3">
          2. Escolha o Profissional
        </h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
          <div 
            v-for="barber in barbers" 
            :key="barber.id"
            @click="selectedBarber = barber"
            class="cursor-pointer border rounded-lg p-4 transition-all hover:shadow-md text-center"
            :class="selectedBarber?.id === barber.id ? 'border-emerald-600 bg-emerald-50 ring-1 ring-emerald-600' : 'border-stone-200 bg-white'"
          >
            <div class="w-16 h-16 mx-auto bg-stone-200 rounded-full flex items-center justify-center mb-3 text-xl font-serif font-bold text-stone-600">
              {{ barber.name.charAt(0) }}
            </div>
            <h3 class="font-bold text-sm text-emerald-950">{{ barber.name }}</h3>
          </div>
        </div>
      </section>

      <section class="mb-10" v-if="selectedBarber">
        <h2 class="text-lg font-bold text-stone-700 uppercase tracking-wider mb-4 border-l-4 border-emerald-600 pl-3">
          3. Escolha o Horário
        </h2>
        
        <div class="bg-white p-6 rounded-lg border border-stone-200 shadow-sm">
          <div class="mb-6">
            <label class="block text-sm font-bold text-stone-500 uppercase mb-2">Data Desejada</label>
            <input 
              type="date" 
              v-model="selectedDate"
              class="w-full border-stone-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 p-3 bg-stone-50 font-bold text-stone-700"
            >
          </div>

          <div v-if="calculating" class="text-center py-8 text-stone-500 italic">
            Verificando disponibilidade...
          </div>

          <div v-else-if="selectedDate">
            <div v-if="availableSlots.length > 0" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
              <button 
                v-for="slot in availableSlots" 
                :key="slot"
                @click="selectedTime = slot"
                class="py-2 px-1 rounded text-sm font-bold border transition-colors"
                :class="selectedTime === slot ? 'bg-emerald-700 text-white border-emerald-700' : 'bg-white text-stone-700 border-stone-300 hover:border-emerald-500'"
              >
                {{ slot }}
              </button>
            </div>
            <div v-else class="text-center py-4 text-amber-600 bg-amber-50 rounded border border-amber-200">
              Nenhum horário disponível para esta data. Tente outro dia.
            </div>
          </div>
        </div>
      </section>

      <div v-if="selectedTime" class="fixed bottom-0 left-0 w-full bg-white border-t border-stone-200 p-4 shadow-2xl z-50">
        <div class="max-w-3xl mx-auto flex justify-between items-center">
          <div class="hidden sm:block">
            <p class="text-xs text-stone-500 uppercase font-bold">Resumo</p>
            <p class="text-sm font-bold text-emerald-950">
              {{ selectedService.name }} com {{ selectedBarber.name }}
            </p>
            <p class="text-xs text-stone-500">
               {{ selectedDate.split('-').reverse().join('/') }} às {{ selectedTime }}
            </p>
          </div>
          <div class="sm:hidden">
             <p class="text-sm font-bold text-emerald-950">{{ selectedTime }}</p>
             <p class="text-xs font-serif">{{ formatMoney(selectedService.price) }}</p>
          </div>

          <button 
            @click="confirmAppointment"
            :disabled="loading"
            class="bg-emerald-900 hover:bg-emerald-800 text-white font-bold py-3 px-8 rounded-sm uppercase tracking-widest shadow-lg transition-transform active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed"
          >
            {{ loading ? 'Agendando...' : 'Confirmar' }}
          </button>
        </div>
      </div>

    </div>
  </div>
</template>