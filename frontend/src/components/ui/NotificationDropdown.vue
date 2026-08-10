<template>
  <div class="relative" ref="dropdownRef">
    <button @click="toggle" class="relative p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 focus:outline-none">
      <span class="material-symbols-outlined">notifications</span>
      <span v-if="unreadCount > 0" class="absolute top-1 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>
    </button>

    <div v-if="isOpen" class="absolute right-0 mt-2 w-80 sm:w-96 origin-top-right rounded-xl bg-white dark:bg-gray-800 shadow-xl ring-1 ring-black ring-opacity-5 focus:outline-none z-50 overflow-hidden border border-gray-100 dark:border-gray-700">
      <div class="p-4 border-b dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Notifikasi</h3>
        <button v-if="unreadCount > 0" @click="markAllAsRead" class="text-xs text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 font-medium">
          Tandai semua dibaca
        </button>
      </div>

      <div class="max-h-80 overflow-y-auto">
        <div v-if="loading" class="p-4 flex justify-center">
          <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary-600"></div>
        </div>
        
        <template v-else-if="notifications.length > 0">
          <a v-for="notif in notifications" :key="notif.id" href="#" @click.prevent="markAsRead(notif.id)" class="block p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 border-b dark:border-gray-700/50 last:border-0 transition-colors" :class="{'bg-primary-50/50 dark:bg-primary-900/10': !notif.read_at}">
            <div class="flex gap-3">
              <div class="flex-shrink-0 mt-0.5">
                <span class="material-symbols-outlined text-primary-500" :class="getIconClass(notif.type)">{{ getIcon(notif.type) }}</span>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ notif.title }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">{{ notif.message }}</p>
                <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1">{{ formatTime(notif.created_at) }}</p>
              </div>
              <div v-if="!notif.read_at" class="flex-shrink-0 flex items-center">
                <div class="h-2 w-2 bg-primary-500 rounded-full"></div>
              </div>
            </div>
          </a>
        </template>

        <div v-else class="p-8 text-center">
          <span class="material-symbols-outlined text-4xl text-gray-300 dark:text-gray-600 mb-2 block">notifications_off</span>
          <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada notifikasi baru</p>
        </div>
      </div>

      <div class="p-3 text-center border-t dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
        <a href="#" class="text-sm font-medium text-primary-600 dark:text-primary-400">Lihat Semua Notifikasi</a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import axios from 'axios'

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
    // In a real app with token auth, you would use your configured api instance
    const res = await axios.get('http://localhost:8000/api/notifications', {
      headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
    })
    notifications.value = res.data.data || res.data
  } catch (error) {
    console.error('Failed to fetch notifications', error)
  } finally {
    loading.value = false
  }
}

const markAsRead = async (id) => {
  try {
    await axios.patch(`http://localhost:8000/api/notifications/${id}/read`, {}, {
      headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
    })
    const notif = notifications.value.find(n => n.id === id)
    if (notif) notif.read_at = new Date().toISOString()
  } catch (error) {
    console.error('Failed to mark as read', error)
  }
}

const markAllAsRead = async () => {
  try {
    await axios.patch(`http://localhost:8000/api/notifications/read-all`, {}, {
      headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
    })
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
    'system': 'text-gray-500',
    'user': 'text-green-500',
    'payment': 'text-yellow-500'
  }
  return classes[type] || 'text-primary-500'
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
  // fetchNotifications() // uncomment if you want to load on mount
})

onUnmounted(() => {
  document.removeEventListener('click', close)
})
</script>
