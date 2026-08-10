<template>
  <div class="w-full">
    <!-- Table Skeleton -->
    <div v-if="type === 'table'" class="animate-pulse">
      <div class="h-10 bg-gray-200 dark:bg-gray-700 rounded-t-lg mb-2"></div>
      <div v-for="i in rows" :key="i" class="flex gap-4 mb-3 border-b dark:border-gray-700 pb-3">
        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/4"></div>
        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/4"></div>
        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/4"></div>
        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/4"></div>
      </div>
    </div>

    <!-- Card Skeleton -->
    <div v-else-if="type === 'card'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 animate-pulse">
      <div v-for="i in rows" :key="i" class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="h-10 w-10 bg-gray-200 dark:bg-gray-700 rounded-full mb-4"></div>
        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2 mb-2"></div>
        <div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
      </div>
    </div>

    <!-- Form Skeleton -->
    <div v-else-if="type === 'form'" class="animate-pulse space-y-4 max-w-md">
      <div v-for="i in rows" :key="i">
        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/4 mb-2"></div>
        <div class="h-10 bg-gray-200 dark:bg-gray-700 rounded w-full"></div>
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
