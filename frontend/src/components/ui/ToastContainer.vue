<template>
  <div class="fixed top-20 right-8 z-50 flex flex-col gap-4 pointer-events-none">
    <TransitionGroup name="toast">
      <div v-for="toast in toastStore.toasts" :key="toast.id"
        class="w-80 bg-surface rounded-xl shadow-[0px_10px_30px_rgba(15,23,42,0.1)]
               flex items-start gap-3 p-4 pointer-events-auto animate-slide-in-right"
        :class="borderClass(toast.type)">

        <!-- Icon -->
        <span class="material-symbols-outlined mt-0.5 fill" :class="iconColor(toast.type)">
          {{ iconName(toast.type) }}
        </span>

        <!-- Content -->
        <div class="flex-1">
          <p class="text-label-md font-label-md font-bold text-on-surface">{{ toast.title }}</p>
          <p v-if="toast.message" class="text-label-sm font-label-sm text-on-surface-variant mt-1">
            {{ toast.message }}
          </p>
        </div>

        <!-- Dismiss -->
        <button @click="toastStore.remove(toast.id)"
          class="text-on-surface-variant hover:text-on-surface p-1 transition-colors">
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
    error:   'border-l-4 border-l-error',
    warning: 'border-l-4 border-l-amber-400',
    info:    'border-l-4 border-l-secondary',
  }[type] || 'border-l-4 border-l-secondary'
}

function iconColor(type) {
  return {
    success: 'text-emerald-500',
    error:   'text-error',
    warning: 'text-amber-400',
    info:    'text-secondary',
  }[type] || 'text-secondary'
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
