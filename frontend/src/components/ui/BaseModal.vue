<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="modelValue"
        class="fixed inset-0 z-50 flex items-center justify-center p-4
               bg-slate-900/60 backdrop-blur-sm"
        @click.self="$emit('update:modelValue', false)">

        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 rounded-2xl shadow-2xl
                    w-full flex flex-col animate-fade-in-up overflow-hidden transition-colors"
          :style="{ maxWidth: maxWidth, maxHeight: '90vh' }">

          <!-- Header slot -->
          <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700/70">
            <slot name="header" />
            <button @click="$emit('update:modelValue', false)"
              class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200
                     hover:bg-slate-100 dark:hover:bg-slate-700/80 transition-colors">
              <span class="material-symbols-outlined icon-20">close</span>
            </button>
          </div>

          <!-- Body -->
          <div class="px-6 py-5 overflow-y-auto flex-1 text-slate-800 dark:text-slate-200">
            <slot />
          </div>

          <!-- Footer slot -->
          <div v-if="$slots.footer"
            class="px-6 py-4 border-t border-slate-200 dark:border-slate-700/70 bg-slate-50 dark:bg-slate-900/60
                   flex justify-end gap-3 rounded-b-2xl">
            <slot name="footer" />
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
defineProps({
  modelValue: { type: Boolean, default: false },
  maxWidth:   { type: String,  default: '640px' },
})
defineEmits(['update:modelValue'])
</script>
