<template>
  <div class="fixed top-20 right-8 z-50 flex flex-col gap-3 pointer-events-none">
    <TransitionGroup name="toast">
      <div v-for="toast in toastStore.toasts" :key="toast.id"
        class="w-84 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700
               flex items-start gap-3 p-4 pointer-events-auto animate-slide-in-right transition-colors"
        :class="borderClass(toast.type)">

        <!-- Icon -->
        <span class="material-symbols-outlined mt-0.5 fill text-xl" :class="iconColor(toast.type)">
          {{ iconName(toast.type) }}
        </span>

        <!-- Content -->
        <div class="flex-1 min-w-0">
          <p class="text-label-md font-bold text-slate-900 dark:text-white">{{ toast.title }}</p>
          <p v-if="toast.message" class="text-xs text-slate-600 dark:text-slate-300 mt-0.5 leading-relaxed">
            {{ toast.message }}
          </p>
        </div>

        <!-- Dismiss -->
        <button @click="toastStore.remove(toast.id)"
          class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
          <span class="material-symbols-outlined" style="font-size:18px">close</span>
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup>
import { useToastStore } from '@/stores/toast'
const toastStore = useToastStore()

function borderClass(type) {
  return {
    success: 'border-l-4 border-l-emerald-500',
    error:   'border-l-4 border-l-rose-500',
    warning: 'border-l-4 border-l-amber-500',
    info:    'border-l-4 border-l-secondary dark:border-l-blue-400',
  }[type] || 'border-l-4 border-l-secondary dark:border-l-blue-400'
}

function iconColor(type) {
  return {
    success: 'text-emerald-500',
    error:   'text-rose-500',
    warning: 'text-amber-500',
    info:    'text-secondary dark:text-blue-400',
  }[type] || 'text-secondary dark:text-blue-400'
}

function iconName(type) {
  return {
    success: 'check_circle',
    error:   'error',
    warning: 'warning',
    info:    'info',
  }[type] || 'info'
}
</script>

<style scoped>
.toast-enter-active { transition: all 0.3s ease-out; }
.toast-leave-active { transition: all 0.2s ease-in; }
.toast-enter-from   { opacity: 0; transform: translateX(100%); }
.toast-leave-to     { opacity: 0; transform: translateX(100%); }
</style>
