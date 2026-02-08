<script setup lang="ts">
import { RouterView } from 'vue-router'
import { ref } from 'vue'

const message: string = 'Ambiente Barber-Sys Configurado!'
const status = ref<boolean>(true)
</script>

<template>
  <div class="min-h-screen bg-slate-900 flex items-center justify-center p-6">
    <div class="bg-slate-800 border border-slate-700 rounded-2xl p-10 max-w-lg w-full shadow-2xl text-center">
      
      <div class="mb-6 inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-500/20 text-emerald-400">
        <span class="text-3xl">✂️</span>
      </div>

      <h1 class="text-3xl font-bold text-white mb-2">
        {{ message }}
      </h1>
      
      <p class="text-slate-400 mb-8">
        Laravel (API) + Vue 3 + TypeScript + Tailwind v4
      </p>

      <div class="grid grid-cols-2 gap-4 text-sm">
        <div class="p-4 rounded-xl bg-slate-700/50 text-slate-300 border border-slate-600">
          <span class="block text-xs uppercase tracking-wider text-slate-500 mb-1">Status</span>
          <span class="text-emerald-400 font-bold" v-if="status">Online</span>
        </div>
        <div class="p-4 rounded-xl bg-slate-700/50 text-slate-300 border border-slate-600">
          <span class="block text-xs uppercase tracking-wider text-slate-500 mb-1">Porta</span>
          <span class="font-mono">5173</span>
        </div>
      </div>

      <RouterView class="mt-6" />
    </div>
  </div>
</template>

<style>
/* Se o Tailwind v4 estiver ok, não precisamos escrever CSS aqui */
</style>