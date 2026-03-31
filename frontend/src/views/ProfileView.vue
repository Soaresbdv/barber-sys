<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import 'vue-cropper/dist/index.css'
import { VueCropper } from 'vue-cropper'

const router = useRouter()
const isLoading = ref(true)
const isSaving = ref(false)
const userRole = ref(localStorage.getItem('user_role') || 'client')

const form = ref({ name: '', email: '', password: '', password_confirmation: '' })
const avatarUrl = ref<string | null>(null)
const fileInput = ref<HTMLInputElement | null>(null)

const isCropModalOpen = ref(false)
const isUploadingAvatar = ref(false)
const originalImageBase64 = ref<string | null>(null)
const cropperRef = ref<any>(null)

const cropperOptions = ref({
  img: '', 
  outputSize: 1, 
  outputType: 'jpeg',
  autoCrop: true, 
  autoCropWidth: 250, 
  autoCropHeight: 250,
  fixed: true, 
  fixedNumber: [1, 1], 
  centerBox: true, 
  full: true, 
})

onMounted(async () => {
  const token = localStorage.getItem('token')
  if (!token) return router.push('/login')

  try {
    const response = await axios.get('http://localhost:8000/api/user', {
      headers: { Authorization: `Bearer ${token}` }
    })
    form.value.name = response.data.name
    form.value.email = response.data.email
    
    if (response.data.avatar) {
      avatarUrl.value = response.data.avatar.startsWith('http') 
        ? response.data.avatar 
        : `http://localhost:8000/storage/${response.data.avatar}`
    }
  } catch (error) {
    console.error('Erro ao carregar perfil', error)
  } finally {
    isLoading.value = false
  }
})

const triggerFileInput = () => { fileInput.value?.click() }

const handleFileUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (!target.files || target.files.length === 0) return

  const file = target.files[0]
  if (!file) return

  const reader = new FileReader()
  reader.onload = (e) => {
    originalImageBase64.value = e.target?.result as string
    cropperOptions.value.img = originalImageBase64.value 
    isCropModalOpen.value = true 
  }
  reader.readAsDataURL(file)
  
  target.value = ''
}

const saveCroppedImage = () => {
  if (isUploadingAvatar.value) return
  isUploadingAvatar.value = true

  cropperRef.value.getCropBlob(async (blob: Blob) => {
    
    const sizeInMB = blob.size / (1024 * 1024)
    if (sizeInMB > 5) {
      isUploadingAvatar.value = false
      return alert('A imagem recortada ficou muito pesada (mais de 5MB). Tente diminuir o zoom ou escolher outra imagem.')
    }

    const fileToUpload = new File([blob], 'avatar.jpg', { type: 'image/jpeg' })
    const formData = new FormData()
    formData.append('avatar', fileToUpload) 

    try {
      const token = localStorage.getItem('token')
      const response = await axios.post('http://localhost:8000/api/profile/avatar', formData, {
        headers: { Authorization: `Bearer ${token}` } 
      })
      
      avatarUrl.value = response.data.avatar_url 
      
      const userData = JSON.parse(localStorage.getItem('user') || '{}')
      userData.avatar = response.data.avatar_url
      localStorage.setItem('user', JSON.stringify(userData))

      isCropModalOpen.value = false 
    } catch (error: any) {
      const msgErro = error.response?.data?.message || error.message || 'Erro desconhecido';
      alert(`Servidor recusou a foto. Motivo: ${msgErro}`);
      console.error('Detalhes do Erro:', error.response);
    } finally {
      isUploadingAvatar.value = false
    }
  })
}

const saveProfile = async () => {
  if (form.value.password && form.value.password !== form.value.password_confirmation) {
    return alert('As senhas não coincidem!')
  }

  isSaving.value = true
  try {
    const token = localStorage.getItem('token')
    const payload: any = { name: form.value.name }
    if (form.value.password) payload.password = form.value.password

    await axios.put('http://localhost:8000/api/profile', payload, {
      headers: { Authorization: `Bearer ${token}` }
    })

    const userData = JSON.parse(localStorage.getItem('user') || '{}')
    userData.name = form.value.name
    localStorage.setItem('user', JSON.stringify(userData))

    alert('Perfil atualizado com sucesso!')
    form.value.password = ''
    form.value.password_confirmation = ''
  } catch (error: any) {
    alert(error.response?.data?.message || 'Erro ao salvar alterações.')
  } finally {
    isSaving.value = false
  }
}

const handleLogout = () => { localStorage.clear(); router.push('/') }
</script>

<template>
  <div class="min-h-screen bg-[#FBFBFB] text-stone-800 font-sans pb-20 relative">
    
    <nav class="bg-white border-b border-stone-100 px-8 py-5 flex justify-between items-center sticky top-0 z-40 shadow-sm">
      <h1 @click="router.push('/')" class="font-serif text-2xl font-bold text-emerald-950 tracking-widest cursor-pointer">
        BARBER<span class="text-emerald-600">SYS</span>
      </h1>
      <button @click="handleLogout" class="text-[10px] font-bold tracking-[0.2em] uppercase text-red-400 hover:text-red-600 transition-colors">
        Sair
      </button>
    </nav>

    <main class="max-w-3xl mx-auto py-12 px-6">
      <header class="mb-10 flex flex-col sm:flex-row sm:items-end justify-between gap-6 border-b border-stone-200 pb-6">
        <div>
          <button @click="userRole === 'barber' ? router.push('/barber/dashboard') : router.push('/')" class="text-[10px] font-black uppercase tracking-widest text-stone-400 hover:text-emerald-700 transition-colors mb-4 flex items-center gap-2">
            &larr; Voltar para o Painel
          </button>
          <h2 class="text-4xl font-serif text-stone-900 mb-2">Meu Perfil</h2>
          <p class="text-stone-500 italic">Central de controle da sua conta.</p>
        </div>
        
        <button @click="userRole === 'barber' ? router.push('/barber/dashboard') : router.push('/dashboard')" class="bg-stone-900 hover:bg-black text-white text-[10px] font-black uppercase tracking-widest px-8 py-4 transition-transform active:scale-95 shadow-md">
          {{ userRole === 'barber' ? 'Ver Minha Agenda' : 'Ver Meus Agendamentos' }} &rarr;
        </button>
      </header>

      <div v-if="isLoading" class="text-center py-12 animate-pulse text-stone-400 font-serif italic">Carregando informações...</div>

      <div v-else class="bg-white border border-stone-200 shadow-sm p-8 sm:p-12 rounded-sm relative z-30">
        
        <div class="flex flex-col sm:flex-row items-center gap-8 mb-12 pb-8 border-b border-stone-100">
          <div class="relative group cursor-pointer" @click="triggerFileInput" title="Clique para alterar sua foto">
            <div class="w-32 h-32 rounded-full overflow-hidden bg-stone-100 border-4 border-white shadow-lg flex items-center justify-center relative">
              <img v-if="avatarUrl" :src="avatarUrl" alt="Sua Foto" class="w-full h-full object-cover">
              <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-stone-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
              
              <div class="absolute inset-0 bg-emerald-950/70 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
              </div>
            </div>
            <input type="file" ref="fileInput" @change="handleFileUpload" accept="image/jpeg, image/png, image/webp" class="hidden">
          </div>
          <div class="text-center sm:text-left">
            <h3 class="font-bold text-stone-800 text-lg mb-1 capitalize">{{ form.name.split(' ')[0] }}</h3>
            <p v-if="userRole === 'barber'" class="text-[10px] font-black uppercase tracking-widest text-white bg-emerald-900 px-3 py-1 rounded-sm inline-block mb-4 shadow-sm">
              Mestre Barbeiro
            </p>
            <p v-else class="text-xs text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-sm inline-block font-bold mb-4">
              Cliente Fidelidade
            </p>
            <div class="flex gap-2 justify-center sm:justify-start">
              <button @click="triggerFileInput" class="text-[10px] font-black uppercase tracking-widest text-emerald-700 hover:text-emerald-900 border border-emerald-200 bg-emerald-50 px-4 py-2 hover:bg-emerald-100 transition-colors rounded-sm">
                Alterar Foto
              </button>
            </div>
          </div>
        </div>

        <form @submit.prevent="saveProfile" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
              <label class="block text-[10px] font-black uppercase tracking-widest text-stone-500 mb-2">Nome Completo</label>
              <input type="text" v-model="form.name" required class="w-full border border-stone-300 p-3 text-sm focus:border-emerald-600 focus:outline-none transition-colors rounded-sm">
            </div>
            <div class="md:col-span-2">
              <label class="block text-[10px] font-black uppercase tracking-widest text-stone-500 mb-2">E-mail (Login)</label>
              <input type="email" :value="form.email" disabled class="w-full border border-stone-200 bg-stone-100 p-3 text-sm text-stone-500 cursor-not-allowed rounded-sm" title="O e-mail não pode ser alterado por segurança.">
            </div>
            <div>
              <label class="block text-[10px] font-black uppercase tracking-widest text-stone-500 mb-2">Nova Senha (Opcional)</label>
              <input type="password" v-model="form.password" placeholder="Mínimo 6 caracteres" class="w-full border border-stone-300 p-3 text-sm focus:border-emerald-600 focus:outline-none transition-colors rounded-sm">
            </div>
            <div>
              <label class="block text-[10px] font-black uppercase tracking-widest text-stone-500 mb-2">Confirmar Nova Senha</label>
              <input type="password" v-model="form.password_confirmation" placeholder="Repita a nova senha" class="w-full border border-stone-300 p-3 text-sm focus:border-emerald-600 focus:outline-none transition-colors rounded-sm">
            </div>
          </div>
          <div class="pt-6 border-t border-stone-100 mt-8 flex justify-end">
            <button type="submit" :disabled="isSaving" class="bg-emerald-900 hover:bg-emerald-800 text-white text-[10px] font-black uppercase tracking-widest px-10 py-4 transition-colors shadow-lg shadow-emerald-900/20 disabled:opacity-50 rounded-sm">
              {{ isSaving ? 'Salvando...' : 'Atualizar Dados' }}
            </button>
          </div>
        </form>
      </div>
    </main>

    <Teleport to="body">
      <div v-if="isCropModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center bg-stone-950/80 backdrop-blur-sm p-4 font-sans">
        <div class="bg-white max-w-2xl w-full p-8 shadow-2xl border-t-4 border-emerald-800 relative rounded-sm">
          
          <button @click="isCropModalOpen = false" class="absolute top-5 right-5 text-stone-300 hover:text-red-500 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
          
          <h3 class="text-2xl font-serif text-stone-900 mb-1">Ajustar sua Foto</h3>
          <p class="text-stone-500 text-sm italic mb-8">Centralize seu rosto dentro do círculo para um perfil perfeito.</p>

          <div class="w-full h-96 bg-stone-100 mb-8 overflow-hidden relative border border-stone-200 rounded-sm">
            <vue-cropper
              ref="cropperRef"
              :img="cropperOptions.img"
              :outputSize="cropperOptions.outputSize"
              :outputType="cropperOptions.outputType"
              :autoCrop="cropperOptions.autoCrop"
              :autoCropWidth="cropperOptions.autoCropWidth"
              :autoCropHeight="cropperOptions.autoCropHeight"
              :fixed="cropperOptions.fixed"
              :fixedNumber="cropperOptions.fixedNumber"
              :centerBox="cropperOptions.centerBox"
              :full="cropperOptions.full"
            />
            
            <div class="absolute inset-0 pointer-events-none flex items-center justify-center z-10">
                <div class="w-[250px] h-[250px] rounded-full ring-[2000px] ring-black/50"></div>
            </div>
          </div>
          
          <div class="flex justify-end gap-3 pt-6 border-t border-stone-100">
             <button @click="isCropModalOpen = false" class="text-[10px] font-black uppercase tracking-widest text-stone-400 hover:text-stone-600 px-6 py-3 transition-colors">
               Cancelar
             </button>
             <button @click="saveCroppedImage" :disabled="isUploadingAvatar" class="bg-emerald-900 hover:bg-emerald-800 text-white text-[10px] font-black uppercase tracking-widest px-10 py-3 transition-colors shadow-lg disabled:opacity-50 rounded-sm">
               {{ isUploadingAvatar ? 'Processando...' : 'Confirmar Recorte' }}
             </button>
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

.vue-cropper { background-image: none !important; background-color: #f5f5f4 !important; }
</style>