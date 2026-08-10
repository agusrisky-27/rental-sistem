<template>
  <RouterLink :to="to"
    class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all text-label-md font-label-md"
    :class="isActive
      ? 'bg-secondary-fixed text-secondary dark:bg-gray-800 dark:text-blue-400'
      : 'text-primary dark:text-gray-300 hover:text-secondary dark:hover:text-white hover:bg-surface-container-low dark:hover:bg-gray-700'">
    <span class="material-symbols-outlined"
      :style="isActive ? { fontVariationSettings: `'FILL' 1` } : {}">
      {{ icon }}
    </span>
    <span>{{ label }}</span>
  </RouterLink>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const props = defineProps({
  to:    { type: String, required: true },
  icon:  { type: String, required: true },
  label: { type: String, required: true },
})

const route    = useRoute()
const isActive = computed(() => route.path === props.to || route.path.startsWith(props.to + '/'))
</script>
