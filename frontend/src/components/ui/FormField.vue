<template>
  <div class="flex flex-col gap-1.5">
    <label class="block text-label-md font-label-md text-on-surface">{{ label }}</label>
    <slot />
    <p v-if="error" class="text-label-sm text-error">{{ error }}</p>
  </div>
</template>

<script setup>
defineProps({
  label: { type: String, required: true },
  error: { type: String, default: '' },
})
</script>
