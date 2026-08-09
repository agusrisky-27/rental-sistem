<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="modelValue"
        class="fixed inset-0 z-50 flex items-center justify-center p-4
               bg-primary/50 backdrop-blur-sm"
        @click.self="$emit('update:modelValue', false)">

        <div class="bg-surface-container-lowest rounded-2xl shadow-[0px_10px_30px_rgba(15,23,42,0.1)]
                    w-full flex flex-col animate-fade-in-up overflow-hidden"
          :style="{ maxWidth: maxWidth, maxHeight: '90vh' }">

          <!-- Header slot -->
          <div class="flex items-center justify-between px-6 py-5 border-b border-outline-variant/30">
            <slot name="header" />
            <button @click="$emit('update:modelValue', false)"
              class="p-2 rounded-full text-on-surface-variant hover:bg-surface-container-low
                     hover:text-on-surface transition-colors">
              <span class="material-symbols-outlined">close</span>
            </button>
          </div>

          <!-- Body -->
          <div class="px-6 py-6 overflow-y-auto flex-1">
            <slot />
          </div>

          <!-- Footer slot -->
          <div v-if="$slots.footer"
            class="px-6 py-5 border-t border-outline-variant/30 bg-surface-container-low
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

<style scoped>
.modal-enter-active, .modal-leave-active { transition: opacity 0.2s ease; }
.modal-enter-from,  .modal-leave-to      { opacity: 0; }
</style>
