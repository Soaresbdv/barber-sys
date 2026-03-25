<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import VueApexCharts from 'vue3-apexcharts'
import type { ApexOptions } from 'apexcharts' 

const router = useRouter()
const metrics = ref<any>(null)
const expenses = ref<any[]>([])
const isLoading = ref(true)
const isSubmitting = ref(false)

const expenseForm = ref({
  description: '',
  amount: '',
  expense_date: new Date().toISOString().split('T')[0],
  category: 'geral'
})

const chartSeries = ref<any[]>([])
const chartOptions = ref<ApexOptions>({
  chart: {
    type: 'area',
    fontFamily: 'Inter, sans-serif',
    toolbar: { show: false },
    zoom: { enabled: false }
  },
  colors: ['#047857', '#ef4444'], 
  dataLabels: { enabled: false },
  stroke: { curve: 'smooth', width: 2 },
  fill: {
    type: 'gradient',
    gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 90, 100] }
  },
  xaxis: {
    categories: [],
    labels: { style: { colors: '#a8a29e' } }, 
    axisBorder: { show: false },
    axisTicks: { show: false }
  },
  yaxis: {
    labels: {
      style: { colors: '#a8a29e' },
      formatter: (value: number) => `R$ ${value.toFixed(0)}`
    }
  },
    grid: { borderColor: '#f5f5f4', strokeDashArray: 4 }, // stone-100
    legend: { position: 'top', horizontalAlign: 'right' }
})

onMounted(() => {
  fetchDashboardData()
})

const fetchDashboardData = async () => {
  isLoading.value = true
  try {
    const token = localStorage.getItem('token')
    const config = { headers: { Authorization: `Bearer ${token}` } }
    
    const [resMetrics, resExpenses] = await Promise.all([
      axios.get('http://localhost:8000/api/admin/metrics', config),
      axios.get('http://localhost:8000/api/admin/expenses', config)
    ])
    
    metrics.value = resMetrics.data
    expenses.value = resExpenses.data

    chartSeries.value = [
      { name: 'Receita (R$)', data: resMetrics.data.chart.revenue },
      { name: 'Despesas (R$)', data: resMetrics.data.chart.expenses }
    ]
    chartOptions.value = {
      ...chartOptions.value,
      xaxis: { ...chartOptions.value.xaxis, categories: resMetrics.data.chart.labels }
    }

  } catch (error) {
    console.error('Erro ao buscar dados do painel', error)
  } finally {
    isLoading.value = false
  }
}

const submitExpense = async () => {
  isSubmitting.value = true
  try {
    const token = localStorage.getItem('token')
    await axios.post('http://localhost:8000/api/admin/expenses', expenseForm.value, {
      headers: { Authorization: `Bearer ${token}` }
    })
    
    expenseForm.value.description = ''
    expenseForm.value.amount = ''
    
    await fetchDashboardData() 
    alert('Despesa registrada com sucesso!')
  } catch (error) {
    alert('Erro ao registrar despesa.')
  } finally {
    isSubmitting.value = false
  }
}

const handleLogout = () => {
  localStorage.clear()
  router.push('/login')
}

const formatMoney = (val: number) => {
  return Number(val).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })
}

const formatDate = (dateString: string) => {
  const [year, month, day] = dateString.split('-')
  return `${day}/${month}/${year}`
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
        <button @click="router.push('/admin/services')" class="text-[10px] font-bold tracking-[0.2em] uppercase text-stone-400 hover:text-emerald-400 transition-colors">
          Serviços
        </button>
        <div class="w-px h-4 bg-stone-700 mx-2"></div>
        <button @click="handleLogout" class="text-[10px] font-bold tracking-[0.2em] uppercase text-stone-400 hover:text-white transition-colors">
          Sair
        </button>
      </div>
    </nav>

    <main class="max-w-7xl mx-auto px-6 py-12" v-if="!isLoading && metrics">
      
      <header class="mb-10 flex flex-col md:flex-row md:justify-between md:items-end gap-4">
        <div>
          <h2 class="text-3xl font-serif text-stone-900 mb-1">Visão Geral Financeira</h2>
          <p class="text-stone-500 text-sm font-medium uppercase tracking-widest">Métricas de {{ metrics.current_month }}</p>
        </div>
      </header>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 border-l-4 border-emerald-600 shadow-sm rounded-sm">
          <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest mb-2">Faturamento (Realizado)</p>
          <p class="text-3xl font-serif text-emerald-950">{{ formatMoney(metrics.metrics.revenue) }}</p>
          <p class="text-xs text-stone-500 mt-2 font-medium">Agendamentos concluídos</p>
        </div>

        <div class="bg-white p-6 border-l-4 border-blue-500 shadow-sm rounded-sm">
          <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest mb-2">Previsão (Agendado)</p>
          <p class="text-3xl font-serif text-blue-950">{{ formatMoney(metrics.metrics.forecast) }}</p>
          <p class="text-xs text-stone-500 mt-2 font-medium">Projeção total: {{ formatMoney(metrics.metrics.total_projected) }}</p>
        </div>

        <div class="bg-white p-6 border-l-4 border-red-500 shadow-sm rounded-sm">
          <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest mb-2">Saídas (Despesas)</p>
          <p class="text-3xl font-serif text-red-900">{{ formatMoney(metrics.metrics.expenses) }}</p>
          <p class="text-xs text-stone-500 mt-2 font-medium">Custos operacionais do mês</p>
        </div>

        <div class="bg-stone-900 p-6 border-l-4 border-emerald-400 shadow-lg rounded-sm text-white">
          <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest mb-2">Lucro Líquido Parcial</p>
          <p class="text-3xl font-serif" :class="metrics.metrics.profit >= 0 ? 'text-emerald-400' : 'text-red-400'">
            {{ formatMoney(metrics.metrics.profit) }}
          </p>
          <p class="text-xs text-stone-400 mt-2 font-medium">Receita menos Despesas</p>
        </div>
      </div>

      <div class="bg-white p-6 border border-stone-200 shadow-sm rounded-sm mb-12 relative z-0">
        <h3 class="font-serif text-xl text-stone-900 mb-1">Evolução do Mês</h3>
        <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest mb-6">Comparativo Diário de Receitas x Despesas</p>
        
        <div class="h-[350px]">
          <VueApexCharts type="area" height="100%" :options="chartOptions" :series="chartSeries"></VueApexCharts>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-1 bg-white p-8 border border-stone-200 shadow-sm">
          <h3 class="font-serif text-xl text-stone-900 mb-6 border-b border-stone-100 pb-4">Lançar Despesa</h3>
          <form @submit.prevent="submitExpense" class="space-y-5">
            <div>
              <label class="block text-[10px] font-black text-stone-500 uppercase tracking-widest mb-1">Descrição</label>
              <input type="text" v-model="expenseForm.description" required placeholder="Ex: Conta de Luz" class="w-full border-b-2 border-stone-200 py-2 focus:outline-none focus:border-emerald-600 bg-transparent">
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-[10px] font-black text-stone-500 uppercase tracking-widest mb-1">Valor (R$)</label>
                <input type="number" step="0.01" v-model="expenseForm.amount" required placeholder="0.00" class="w-full border-b-2 border-stone-200 py-2 focus:outline-none focus:border-emerald-600 bg-transparent">
              </div>
              <div>
                <label class="block text-[10px] font-black text-stone-500 uppercase tracking-widest mb-1">Data</label>
                <input type="date" v-model="expenseForm.expense_date" required class="w-full border-b-2 border-stone-200 py-2 focus:outline-none focus:border-emerald-600 bg-transparent text-sm">
              </div>
            </div>
            <div>
              <label class="block text-[10px] font-black text-stone-500 uppercase tracking-widest mb-1">Categoria</label>
              <select v-model="expenseForm.category" class="w-full border-b-2 border-stone-200 py-2 focus:outline-none focus:border-emerald-600 bg-transparent text-sm cursor-pointer">
                <option value="fixa">Custo Fixo (Aluguel, Luz)</option>
                <option value="produtos">Produtos (Estoque)</option>
                <option value="comissao">Comissões</option>
                <option value="marketing">Marketing / Anúncios</option>
                <option value="geral">Geral / Outros</option>
              </select>
            </div>
            <button type="submit" :disabled="isSubmitting" class="w-full mt-4 bg-emerald-900 text-white text-[10px] font-black uppercase tracking-widest py-4 hover:bg-emerald-800 transition-colors shadow-md disabled:opacity-50">
              {{ isSubmitting ? 'Registrando...' : 'Salvar Despesa' }}
            </button>
          </form>
        </div>

        <div class="lg:col-span-2 bg-white p-8 border border-stone-200 shadow-sm">
          <h3 class="font-serif text-xl text-stone-900 mb-6 border-b border-stone-100 pb-4">Histórico de Saídas</h3>
          <div v-if="expenses.length > 0" class="overflow-x-auto max-h-[400px] overflow-y-auto">
            <table class="w-full text-left border-collapse">
              <thead class="sticky top-0 bg-white">
                <tr>
                  <th class="py-3 px-4 border-b border-stone-200 text-[10px] font-black text-stone-400 uppercase tracking-widest">Data</th>
                  <th class="py-3 px-4 border-b border-stone-200 text-[10px] font-black text-stone-400 uppercase tracking-widest">Descrição</th>
                  <th class="py-3 px-4 border-b border-stone-200 text-[10px] font-black text-stone-400 uppercase tracking-widest">Categoria</th>
                  <th class="py-3 px-4 border-b border-stone-200 text-[10px] font-black text-stone-400 uppercase tracking-widest text-right">Valor</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="expense in expenses" :key="expense.id" class="hover:bg-stone-50 transition-colors border-b border-stone-100">
                  <td class="py-4 px-4 text-sm font-medium text-stone-600">{{ formatDate(expense.expense_date) }}</td>
                  <td class="py-4 px-4 text-sm font-bold text-stone-800">{{ expense.description }}</td>
                  <td class="py-4 px-4 text-xs">
                    <span class="bg-stone-100 text-stone-500 px-2 py-1 rounded-sm uppercase tracking-wider font-bold text-[9px]">{{ expense.category }}</span>
                  </td>
                  <td class="py-4 px-4 text-sm font-serif font-bold text-red-600 text-right">{{ formatMoney(expense.amount) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="text-center py-12 border-2 border-dashed border-stone-100">
            <p class="font-serif italic text-stone-400">Nenhuma despesa registrada ainda.</p>
          </div>
        </div>
      </div>
    </main>
    
    <div v-else class="min-h-screen flex items-center justify-center">
      <p class="font-serif italic text-stone-400 text-xl animate-pulse">Carregando métricas financeiras...</p>
    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=Inter:wght@400;500;700;900&display=swap');
.font-serif { font-family: 'Playfair Display', serif; }
.font-sans { font-family: 'Inter', sans-serif; }

::-webkit-scrollbar { width: 6px; height: 6px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: #e7e5e4; border-radius: 4px; }
::-webkit-scrollbar-thumb:hover { background: #d6d3d1; }
</style>