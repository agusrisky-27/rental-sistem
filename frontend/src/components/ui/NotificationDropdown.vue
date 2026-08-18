<template>
  <div class="relative" ref="dropdownRef">
    <button @click="toggle" class="relative p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 focus:outline-none rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
      <span class="material-symbols-outlined">notifications</span>
      <span v-if="unreadCount > 0" class="absolute top-1 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-rose-500 text-[10px] font-bold text-white shadow-sm">
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>
    </button>

    <div v-if="isOpen" class="absolute right-0 mt-2 w-80 sm:w-96 origin-top-right rounded-xl bg-white dark:bg-slate-800 shadow-2xl ring-1 ring-black/5 focus:outline-none z-50 overflow-hidden border border-slate-200 dark:border-slate-700 animate-fade-in-up">
      <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-900/60">
        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Notifikasi</h3>
        <button v-if="unreadCount > 0" @click="markAllAsRead" class="text-xs text-secondary hover:text-secondary-container dark:text-blue-400 dark:hover:text-blue-300 font-semibold transition-colors">
          Tandai semua dibaca
        </button>
      </div>

      <div class="max-h-80 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700/60">
        <div v-if="loading" class="p-6 flex justify-center">
          <div class="animate-spin rounded-full h-6 w-6 border-2 border-secondary border-t-transparent"></div>
        </div>
        
        <template v-else-if="notifications.length > 0">
          <a v-for="notif in notifications" :key="notif.id" href="#" @click.prevent="markAsRead(notif.id)"
             class="block p-4 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors"
             :class="{'bg-blue-50/50 dark:bg-blue-950/20': !notif.read_at}">
            <div class="flex gap-3">
              <div class="flex-shrink-0 mt-0.5">
                <span class="material-symbols-outlined" :class="getIconClass(notif.type)">{{ getIcon(notif.type) }}</span>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-slate-900 dark:text-slate-100 truncate">{{ notif.title }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-2">{{ notif.message }}</p>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">{{ formatTime(notif.created_at) }}</p>
              </div>
              <div v-if="!notif.read_at" class="flex-shrink-0 flex items-center">
                <div class="h-2 w-2 bg-secondary dark:bg-blue-400 rounded-full"></div>
              </div>
            </div>
          </a>
        </template>

        <div v-else class="p-8 text-center">
          <span class="material-symbols-outlined text-4xl text-slate-300 dark:text-slate-600 mb-2 block">notifications_off</span>
          <p class="text-sm text-slate-500 dark:text-slate-400">Tidak ada notifikasi baru</p>
        </div>
      </div>

      <div class="p-3 text-center border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/60 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
        <a href="#" class="text-sm font-semibold text-secondary dark:text-blue-400">Lihat Semua Notifikasi</a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import api from '@/services/api'

const isOpen = ref(false)
const loading = ref(false)
const notifications = ref([])
const dropdownRef = ref(null)

const unreadCount = computed(() => notifications.value.filter(n => !n.read_at).length)

const toggle = () => {
  isOpen.value = !isOpen.value
  if (isOpen.value && notifications.value.length === 0) {
    fetchNotifications()
  }
}

const close = (e) => {
  if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
    isOpen.value = false
  }
}

const fetchNotifications = async () => {
  loading.value = true
  try {
    const res = await api.get('/notifications')
    notifications.value = res.data.data || res.data
  } catch (error) {
    console.error('Failed to fetch notifications', error)
  } finally {
    loading.value = false
  }
}

const markAsRead = async (id) => {
  try {
    await api.patch(`/notifications/${id}/read`)
    const notif = notifications.value.find(n => n.id === id)
    if (notif) notif.read_at = new Date().toISOString()
  } catch (error) {
    console.error('Failed to mark as read', error)
  }
}

const markAllAsRead = async () => {
  try {
    await api.patch(`/notifications/read-all`)
    notifications.value.forEach(n => n.read_at = new Date().toISOString())
  } catch (error) {
    console.error('Failed to mark all as read', error)
  }
}

const getIcon = (type) => {
  const icons = {
    'transaction': 'receipt_long',
    'system': 'settings',
    'user': 'person',
    'payment': 'payments'
  }
  return icons[type] || 'notifications'
}

const getIconClass = (type) => {
  const classes = {
    'transaction': 'text-blue-500',
    'system': 'text-slate-400',
    'user': 'text-emerald-500',
    'payment': 'text-amber-500'
  }
  return classes[type] || 'text-secondary dark:text-blue-400'
}

const formatTime = (dateStr) => {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  const now = new Date()
  const diffInSeconds = Math.floor((now - date) / 1000)
  
  if (diffInSeconds < 60) return 'Baru saja'
  if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} menit lalu`
  if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} jam lalu`
  return `${Math.floor(diffInSeconds / 86400)} hari lalu`
}

onMounted(() => {
  document.addEventListener('click', close)
})

onUnmounted(() => {
  document.removeEventListener('click', close)
})
</script>
