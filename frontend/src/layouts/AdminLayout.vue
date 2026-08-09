<template>
  <div class="flex min-h-screen bg-surface-container-low">
    <!-- Sidebar -->
    <AppSidebar />

    <!-- Main area -->
    <div class="ml-64 flex flex-col flex-1">
      <!-- Top Nav -->
      <AppTopNav />

      <!-- Page content -->
      <main class="mt-16 flex-1 p-margin-desktop">
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
