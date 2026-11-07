import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const token = ref(localStorage.getItem('token') || null)
  const user  = ref(JSON.parse(localStorage.getItem('user') || 'null'))

  const isLoggedIn = computed(() => !!token.value)
  const isAdmin    = computed(() => user.value?.role === 'admin')
  const homePath   = computed(() => isAdmin.value ? '/admin/dashboard' : '/akun/booking')

  function setSession(data) {
    token.value = data.token
    user.value  = data.user
    localStorage.setItem('token', token.value)
    localStorage.setItem('user', JSON.stringify(user.value))
  }

  async function login(email, password) {
    const res = await api.post('/auth/login', { email, password })
    setSession(res.data)
  }

  async function register(payload) {
    const res = await api.post('/auth/register', payload)
    setSession(res.data)
  }

  async function logout() {
    try {
      await api.post('/auth/logout')
    } catch (e) {
      // Ignore errors (token might already be invalid)
    } finally {
      token.value = null
      user.value  = null
      localStorage.removeItem('token')
      localStorage.removeItem('user')
    }
  }

  function updateUser(userData) {
    user.value = { ...user.value, ...userData }
    localStorage.setItem('user', JSON.stringify(user.value))
  }

  return { token, user, isLoggedIn, isAdmin, homePath, login, register, logout, updateUser }
})
