<template>
  <div class="w-full">
    <!-- Table Skeleton -->
    <div v-if="type === 'table'" class="animate-pulse space-y-3">
      <div class="h-10 bg-slate-100 dark:bg-slate-800 rounded-lg mb-4"></div>
      <div v-for="i in rows" :key="i" class="flex gap-4 border-b border-slate-100 dark:border-slate-800/80 pb-3 items-center">
        <div class="h-4 bg-slate-200 dark:bg-slate-700/70 rounded w-1/4"></div>
        <div class="h-4 bg-slate-200 dark:bg-slate-700/70 rounded w-1/4"></div>
        <div class="h-4 bg-slate-200 dark:bg-slate-700/70 rounded w-1/4"></div>
        <div class="h-4 bg-slate-200 dark:bg-slate-700/70 rounded w-1/4"></div>
      </div>
    </div>

    <!-- Card Skeleton -->
    <div v-else-if="type === 'card'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 animate-pulse">
      <div v-for="i in rows" :key="i" class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700/80">
        <div class="h-10 w-10 bg-slate-200 dark:bg-slate-700 rounded-full mb-4"></div>
        <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded w-1/2 mb-2"></div>
        <div class="h-6 bg-slate-200 dark:bg-slate-700 rounded w-3/4"></div>
      </div>
    </div>

    <!-- Form Skeleton -->
    <div v-else-if="type === 'form'" class="animate-pulse space-y-4 max-w-md">
      <div v-for="i in rows" :key="i">
        <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded w-1/4 mb-2"></div>
        <div class="h-10 bg-slate-200 dark:bg-slate-700 rounded-lg w-full"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  type: {
    type: String,
    default: 'table', // table, card, form
    validator: (value) => ['table', 'card', 'form'].includes(value)
  },
  rows: {
    type: Number,
    default: 4
  }
})
</script>
