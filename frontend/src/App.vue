<template>
  <RouterView />
</template>

<script setup>
import { RouterView } from 'vue-router'
import { useDarkMode } from '@/composables/useDarkMode'

// Initialize theme on app mount
useDarkMode()
</script>
