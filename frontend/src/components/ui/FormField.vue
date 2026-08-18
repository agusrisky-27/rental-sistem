<template>
  <div class="flex flex-col gap-1.5">
    <label class="block text-label-md font-label-md text-slate-700 dark:text-slate-300 font-medium">{{ label }}</label>
    <slot />
    <p v-if="error" class="text-label-sm text-rose-500 dark:text-rose-400 mt-1">{{ error }}</p>
  </div>
</template>

<script setup>
defineProps({
  label: { type: String, required: true },
  error: { type: String, default: '' },
})
</script>
