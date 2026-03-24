<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import axios from 'axios'
import { VueDatePicker } from '@vuepic/vue-datepicker'
import { ptBR } from 'date-fns/locale'
import '@vuepic/vue-datepicker/dist/main.css'

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
  <div class="min-h-screen bg-[#FBFBFB] font-sans pb-32 text-stone-800">
    
    <nav class="bg-white border-b border-stone-100 px-8 py-5 sticky top-0 z-40 shadow-sm transition-all">
      <div class="max-w-4xl mx-auto flex justify-between items-center">
        <h1 class="font-serif text-2xl font-bold text-emerald-950 tracking-widest cursor-pointer" @click="router.push('/')">
          BARBER<span class="text-emerald-600">SYS</span>
        </h1>
        <button @click="router.push('/dashboard')" class="text-[10px] font-black tracking-[0.2em] uppercase text-stone-400 hover:text-emerald-800 transition-colors flex items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
          Voltar
        </button>
      </div>
    </nav>

    <main class="max-w-3xl mx-auto px-6 py-12">
      
      <header class="mb-12 text-center">
        <h2 class="text-4xl font-serif text-stone-900 mb-2">
          {{ isEditing ? 'Reagendar Horário' : 'Novo Agendamento' }}
        </h2>
        <p class="text-stone-500 italic">Configure sua próxima experiência conosco.</p>
      </header>

      <section class="mb-12">
        <div class="flex items-center gap-4 mb-6">
          <span class="flex items-center justify-center w-8 h-8 rounded-full bg-emerald-900 text-white font-serif font-bold text-sm shadow-md">1</span>
          <h3 class="text-sm font-black text-stone-400 uppercase tracking-[0.2em]">O Serviço</h3>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div 
            v-for="service in services" 
            :key="service.id"
            @click="selectedService = service"
            class="cursor-pointer bg-white p-5 border-2 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg flex flex-col justify-between"
            :class="selectedService?.id === service.id ? 'border-emerald-700 shadow-md' : 'border-stone-100 opacity-70 hover:opacity-100'"
          >
            <div class="flex justify-between items-start mb-4">
              <h4 class="font-bold text-lg text-emerald-950 uppercase tracking-tight">{{ service.name }}</h4>
              <span class="font-serif text-xl font-bold text-emerald-800">{{ formatMoney(service.price) }}</span>
            </div>
            <p class="text-xs font-bold text-stone-400 uppercase tracking-widest flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
              {{ service.duration_minutes }} minutos
            </p>
          </div>
        </div>
      </section>

      <section class="mb-12" :class="{ 'opacity-50 pointer-events-none grayscale transition-all duration-500': !selectedService }">
        <div class="flex items-center gap-4 mb-6">
          <span class="flex items-center justify-center w-8 h-8 rounded-full bg-emerald-900 text-white font-serif font-bold text-sm shadow-md">2</span>
          <h3 class="text-sm font-black text-stone-400 uppercase tracking-[0.2em]">O Profissional</h3>
        </div>
        
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
          <div 
            v-for="barber in barbers" 
            :key="barber.id"
            @click="selectedBarber = barber"
            class="cursor-pointer bg-white p-6 border-2 transition-all duration-300 text-center flex flex-col items-center justify-center"
            :class="selectedBarber?.id === barber.id ? 'border-emerald-700 shadow-md transform scale-105' : 'border-stone-100 hover:border-stone-300'"
          >
            <div class="w-16 h-16 bg-stone-100 rounded-full flex items-center justify-center mb-4 text-2xl font-serif font-bold text-emerald-900 shadow-inner">
              {{ barber.name.charAt(0) }}
            </div>
            <h4 class="font-bold text-sm text-stone-800 uppercase tracking-wide">{{ barber.name }}</h4>
          </div>
        </div>
      </section>

      <section class="mb-12" :class="{ 'opacity-50 pointer-events-none grayscale transition-all duration-500': !selectedBarber }">
        <div class="flex items-center gap-4 mb-6">
          <span class="flex items-center justify-center w-8 h-8 rounded-full bg-emerald-900 text-white font-serif font-bold text-sm shadow-md">3</span>
          <h3 class="text-sm font-black text-stone-400 uppercase tracking-[0.2em]">O Momento</h3>
        </div>
        
        <div class="bg-white p-8 border border-stone-100 shadow-sm relative">
          <div class="absolute top-0 left-0 w-1 h-full bg-emerald-800"></div>

          <div class="mb-8 max-w-sm">
            <label class="block text-[10px] font-black text-stone-400 uppercase tracking-[0.2em] mb-3">Selecione a Data</label>
            
            <VueDatePicker 
              v-model="selectedDate" 
              model-type="yyyy-MM-dd"
              :min-date="new Date()"
              :format-locale="ptBR"
              auto-apply
              :format="'dd/MM/yyyy'"
              teleport="body"
              input-class-name="w-full border-b-2 border-stone-200 py-2 text-lg font-serif font-bold text-emerald-950 focus:outline-none focus:border-emerald-700 transition-colors bg-transparent shadow-none rounded-none cursor-pointer"
            ></VueDatePicker>
          </div>

          <div v-if="calculating" class="py-12 text-center animate-pulse">
            <p class="font-serif italic text-stone-400 text-lg">Consultando a agenda do profissional...</p>
          </div>

          <div v-else-if="selectedDate" class="transition-all duration-500">
            <p class="block text-[10px] font-black text-stone-400 uppercase tracking-[0.2em] mb-4">Horários Disponíveis</p>
            
            <div v-if="availableSlots.length > 0" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
              <button 
                v-for="slot in availableSlots" 
                :key="slot"
                @click="selectedTime = slot"
                class="py-3 text-sm font-bold border transition-all duration-200 rounded-sm"
                :class="selectedTime === slot ? 'bg-emerald-900 text-white border-emerald-900 shadow-md scale-105' : 'bg-white text-stone-600 border-stone-200 hover:border-emerald-600 hover:text-emerald-800'"
              >
                {{ slot }}
              </button>
            </div>
            
            <div v-else class="text-center py-8 border-2 border-dashed border-stone-200 bg-stone-50">
              <p class="font-serif italic text-stone-500 text-lg">Infelizmente, a agenda está lotada para este dia.</p>
              <p class="text-xs text-stone-400 mt-2 uppercase font-bold tracking-widest">Tente selecionar outra data</p>
            </div>
          </div>
        </div>
      </section>

    </main>

    <Transition name="slide-up">
      <div v-if="selectedTime" class="fixed bottom-0 left-0 w-full bg-emerald-950 border-t-4 border-emerald-700 p-4 shadow-[0_-10px_40px_rgba(0,0,0,0.1)] z-50">
        <div class="max-w-4xl mx-auto flex justify-between items-center px-4">
          
          <div class="hidden sm:flex flex-col text-white">
            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-400 mb-1">Resumo do Agendamento</span>
            <div class="flex items-center gap-4">
              <span class="font-serif text-xl font-bold">{{ selectedService.name }}</span>
              <span class="text-emerald-500/50">|</span>
              <span class="font-bold text-sm tracking-wide">{{ selectedBarber.name }}</span>
              <span class="text-emerald-500/50">|</span>
              <span class="font-bold text-sm tracking-wide bg-emerald-900 px-3 py-1 rounded-sm">
                 {{ selectedDate.split('-').reverse().join('/') }} às {{ selectedTime }}
              </span>
            </div>
          </div>

          <div class="sm:hidden flex flex-col text-white">
             <span class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-400 mb-1">{{ selectedDate.split('-').reverse().join('/') }}</span>
             <span class="font-serif text-xl font-bold">{{ selectedTime }}</span>
          </div>

          <button 
            @click="confirmAppointment"
            :disabled="loading"
            class="bg-white text-emerald-950 hover:bg-stone-100 py-4 px-8 text-xs font-black uppercase tracking-[0.2em] transition-all active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed shadow-xl"
          >
            {{ loading ? 'Processando...' : 'Confirmar Reserva' }}
          </button>
        </div>
      </div>
    </Transition>

  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=Inter:wght@400;500;700;900&display=swap');
.font-serif { font-family: 'Playfair Display', serif; }
.font-sans { font-family: 'Inter', sans-serif; }

.slide-up-enter-active,
.slide-up-leave-active {
  transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.4s ease;
}
.slide-up-enter-from,
.slide-up-leave-to {
  transform: translateY(100%);
  opacity: 0;
}
</style>