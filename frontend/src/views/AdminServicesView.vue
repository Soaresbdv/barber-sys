<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const services = ref<any[]>([])
const isLoading = ref(true)
const isSubmitting = ref(false)

const isEditing = ref(false)
const editingId = ref<number | null>(null)

const serviceForm = ref({
  name: '',
  price: '',
  duration_minutes: ''
})

onMounted(() => {
  fetchServices()
})

const fetchServices = async () => {
  isLoading.value = true
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get('http://localhost:8000/api/admin/services', {
      headers: { Authorization: `Bearer ${token}` }
    })
    services.value = response.data
  } catch (error) {
    console.error('Erro ao buscar serviços', error)
  } finally {
    isLoading.value = false
  }
}

const submitService = async () => {
  isSubmitting.value = true
  try {
    const token = localStorage.getItem('token')
    const config = { headers: { Authorization: `Bearer ${token}` } }

    if (isEditing.value && editingId.value) {
      await axios.put(`http://localhost:8000/api/admin/services/${editingId.value}`, serviceForm.value, config)
      alert('Serviço atualizado com sucesso!')
    } else {
      await axios.post('http://localhost:8000/api/admin/services', serviceForm.value, config)
      alert('Serviço criado com sucesso!')
    }
    
    resetForm()
    await fetchServices()
  } catch (error: any) {
    alert(error.response?.data?.message || 'Erro ao processar serviço.')
  } finally {
    isSubmitting.value = false
  }
}

const editService = (service: any) => {
  isEditing.value = true
  editingId.value = service.id
  serviceForm.value = {
    name: service.name,
    price: service.price,
    duration_minutes: service.duration_minutes
  }
}

const deleteService = async (id: number, name: string) => {
  if (!confirm(`Tem certeza que deseja remover o serviço "${name}"? Ele deixará de aparecer para os clientes.`)) {
    return
  }

  try {
    const token = localStorage.getItem('token')
    await axios.delete(`http://localhost:8000/api/admin/services/${id}`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    
    await fetchServices()
  } catch (error: any) {
    alert(error.response?.data?.message || 'Erro ao remover serviço.')
  }
}

const resetForm = () => {
  isEditing.value = false
  editingId.value = null
  serviceForm.value = { name: '', price: '', duration_minutes: '' }
}

const handleLogout = () => {
  localStorage.clear()
  router.push('/login')
}

const formatMoney = (val: number) => {
  return Number(val).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })
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
        <button @click="router.push('/admin/dashboard')" class="text-[10px] font-bold tracking-[0.2em] uppercase text-stone-400 hover:text-emerald-400 transition-colors">
          Dashboard
        </button>
        <button @click="router.push('/admin/barbers')" class="text-[10px] font-bold tracking-[0.2em] uppercase text-stone-400 hover:text-emerald-400 transition-colors">
          Equipe
        </button>
        <button class="text-[10px] font-bold tracking-[0.2em] uppercase text-emerald-400">
          Serviços
        </button>
        <div class="w-px h-4 bg-stone-700 mx-2"></div>
        <button @click="handleLogout" class="text-[10px] font-bold tracking-[0.2em] uppercase text-stone-400 hover:text-white transition-colors">
          Sair
        </button>
      </div>
    </nav>

    <main class="max-w-7xl mx-auto px-6 py-12">
      
      <header class="mb-10">
        <h2 class="text-3xl font-serif text-stone-900 mb-1">Catálogo de Serviços</h2>
        <p class="text-stone-500 text-sm font-medium uppercase tracking-widest">Gestão de cortes, combos e preços</p>
      </header>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-1 bg-white p-8 border border-stone-200 shadow-sm self-start sticky top-28 transition-all duration-300" :class="{ 'ring-2 ring-emerald-600': isEditing }">
          <div class="flex justify-between items-center mb-6 border-b border-stone-100 pb-4">
            <h3 class="font-serif text-xl text-stone-900">
              {{ isEditing ? 'Editar Serviço' : 'Novo Serviço' }}
            </h3>
            <button v-if="isEditing" @click="resetForm" class="text-[10px] font-bold uppercase tracking-widest text-stone-400 hover:text-stone-600">
              Cancelar Edição
            </button>
          </div>
          
          <form @submit.prevent="submitService" class="space-y-5">
            <div>
              <label class="block text-[10px] font-black text-stone-500 uppercase tracking-widest mb-1">Nome do Serviço</label>
              <input type="text" v-model="serviceForm.name" required placeholder="Ex: Corte Degradê" class="w-full border-b-2 border-stone-200 py-2 focus:outline-none focus:border-emerald-600 bg-transparent font-medium">
            </div>
            
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-[10px] font-black text-stone-500 uppercase tracking-widest mb-1">Preço (R$)</label>
                <input type="number" step="0.01" v-model="serviceForm.price" required placeholder="0.00" class="w-full border-b-2 border-stone-200 py-2 focus:outline-none focus:border-emerald-600 bg-transparent font-serif font-bold text-emerald-900">
              </div>
              <div>
                <label class="block text-[10px] font-black text-stone-500 uppercase tracking-widest mb-1">Duração (Min)</label>
                <input type="number" v-model="serviceForm.duration_minutes" required placeholder="Ex: 45" class="w-full border-b-2 border-stone-200 py-2 focus:outline-none focus:border-emerald-600 bg-transparent">
              </div>
            </div>

            <button type="submit" :disabled="isSubmitting" class="w-full mt-4 text-white text-[10px] font-black uppercase tracking-widest py-4 transition-colors shadow-md disabled:opacity-50" :class="isEditing ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-emerald-900 hover:bg-emerald-800'">
              {{ isSubmitting ? 'Salvando...' : (isEditing ? 'Atualizar Serviço' : 'Cadastrar Serviço') }}
            </button>
          </form>
        </div>

        <div class="lg:col-span-2 bg-white p-8 border border-stone-200 shadow-sm">
          <h3 class="font-serif text-xl text-stone-900 mb-6 border-b border-stone-100 pb-4">Serviços Ativos</h3>
          
          <div v-if="isLoading" class="text-center py-12">
            <p class="font-serif italic text-stone-400 animate-pulse">Carregando cardápio...</p>
          </div>

          <div v-else-if="services.length > 0" class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr>
                  <th class="py-3 px-4 border-b border-stone-200 text-[10px] font-black text-stone-400 uppercase tracking-widest">Serviço</th>
                  <th class="py-3 px-4 border-b border-stone-200 text-[10px] font-black text-stone-400 uppercase tracking-widest">Duração</th>
                  <th class="py-3 px-4 border-b border-stone-200 text-[10px] font-black text-stone-400 uppercase tracking-widest">Preço</th>
                  <th class="py-3 px-4 border-b border-stone-200 text-[10px] font-black text-stone-400 uppercase tracking-widest text-right">Ações</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="service in services" :key="service.id" class="hover:bg-stone-50 transition-colors border-b border-stone-100 group" :class="{ 'bg-emerald-50': editingId === service.id }">
                  <td class="py-4 px-4 font-bold text-stone-800">{{ service.name }}</td>
                  <td class="py-4 px-4 text-sm font-medium text-stone-500">{{ service.duration_minutes }} min</td>
                  <td class="py-4 px-4 font-serif font-bold text-emerald-800">{{ formatMoney(service.price) }}</td>
                  <td class="py-4 px-4 text-right space-x-4">
                    <button @click="editService(service)" class="text-[10px] font-black uppercase tracking-widest text-emerald-600 hover:text-emerald-800 transition-all">
                      Editar
                    </button>
                    <button @click="deleteService(service.id, service.name)" class="text-[10px] font-black uppercase tracking-widest text-red-400 hover:text-red-600 transition-all">
                      Remover
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <div v-else class="text-center py-12 border-2 border-dashed border-stone-100">
            <p class="font-serif italic text-stone-400">Nenhum serviço cadastrado no momento.</p>
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