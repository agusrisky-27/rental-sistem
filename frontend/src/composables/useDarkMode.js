import { ref, onMounted } from 'vue'

const isDark = ref(false)
let initialized = false

export function useDarkMode() {
  const applyTheme = () => {
    if (isDark.value) {
      document.documentElement.classList.add('dark')
      localStorage.setItem('siwaken-theme', 'dark')
    } else {
      document.documentElement.classList.remove('dark')
      localStorage.setItem('siwaken-theme', 'light')
    }
  }

  const initTheme = () => {
    if (initialized) return
    const savedTheme = localStorage.getItem('siwaken-theme')
    if (savedTheme) {
      isDark.value = savedTheme === 'dark'
    } else {
      isDark.value = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches
    }
    applyTheme()
    initialized = true
  }

  const toggleTheme = () => {
    isDark.value = !isDark.value
    applyTheme()
  }

  onMounted(() => {
    initTheme()
  })

  return {
    isDark,
    toggleTheme
  }
}
