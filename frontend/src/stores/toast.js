import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useToastStore = defineStore('toast', () => {
  const toasts = ref([])

  function add(type, title, message, duration = 4000) {
    const id = Date.now()
    toasts.value.push({ id, type, title, message })
    setTimeout(() => remove(id), duration)
  }

  function remove(id) {
    toasts.value = toasts.value.filter(t => t.id !== id)
  }

  const success = (title, message) => add('success', title, message)
  const error   = (title, message) => add('error',   title, message)
  const warning = (title, message) => add('warning', title, message)
  const info    = (title, message) => add('info',    title, message)

  return { toasts, success, error, warning, info, remove }
})
