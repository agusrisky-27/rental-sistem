<template>
  <RouterLink :to="to"
    class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition-all text-label-md font-label-md"
    :class="isActive
      ? 'bg-blue-50 text-secondary dark:bg-blue-500/15 dark:text-blue-400 font-semibold shadow-sm'
      : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/80 font-medium'">
    <span class="material-symbols-outlined transition-transform duration-200"
      :class="{ 'fill': isActive }">
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
