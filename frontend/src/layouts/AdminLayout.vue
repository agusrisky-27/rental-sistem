<template>
  <div class="flex min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 transition-colors">
    <!-- Sidebar -->
    <AppSidebar />

    <!-- Main area -->
    <div class="ml-64 flex flex-col flex-1 min-w-0">
      <!-- Top Nav -->
      <AppTopNav />

      <!-- Page content -->
      <main class="mt-16 flex-1 p-6 md:p-8">
        <RouterView />
      </main>
    </div>

    <!-- Toast notifications -->
    <ToastContainer />
  </div>
</template>

<script setup>
import AppSidebar    from '@/components/layout/AppSidebar.vue'
import AppTopNav     from '@/components/layout/AppTopNav.vue'
import ToastContainer from '@/components/ui/ToastContainer.vue'
</script>
