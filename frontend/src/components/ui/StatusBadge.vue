<template>
  <span class="inline-flex items-center px-2.5 py-1 rounded-full text-label-sm font-label-sm font-bold uppercase tracking-wider"
    :class="badgeClass">
    {{ label }}
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: { type: String, required: true },
})

const map = {
  tersedia:            { label: 'Tersedia',   cls: 'bg-emerald-100 text-emerald-800' },
  disewa:              { label: 'Disewa',     cls: 'bg-blue-100    text-blue-800'    },
  maintenance:         { label: 'Maintenance',cls: 'bg-amber-100   text-amber-800'   },
  aktif:               { label: 'Aktif',      cls: 'bg-secondary-fixed text-on-secondary-fixed' },
  selesai:             { label: 'Selesai',    cls: 'bg-emerald-100 text-emerald-800' },
  pending:             { label: 'Pending',    cls: 'bg-amber-100   text-amber-800'   },
  dibatalkan:          { label: 'Dibatalkan', cls: 'bg-red-100     text-red-800'     },
  menunggu:            { label: 'Menunggu',   cls: 'bg-amber-100   text-amber-800'   },
  berhasil:            { label: 'Berhasil',   cls: 'bg-emerald-100 text-emerald-800' },
  lunas:               { label: 'Lunas',      cls: 'bg-emerald-100 text-emerald-800' },
  terlambat:           { label: 'Terlambat',  cls: 'bg-red-100     text-red-800'     },
  'hari ini':          { label: 'Hari Ini',   cls: 'bg-blue-100    text-blue-800'    },
  'menunggu verifikasi':{ label: 'Menunggu Verifikasi', cls: 'bg-amber-100 text-amber-800' },
  gold:                { label: 'Gold',       cls: 'bg-tertiary-fixed text-on-tertiary-fixed-variant' },
  silver:              { label: 'Silver',     cls: 'bg-surface-variant text-on-surface-variant' },
  basic:               { label: 'Basic',      cls: 'bg-primary-fixed   text-on-primary-fixed-variant' },
}

const resolved  = computed(() => map[props.status?.toLowerCase()] || { label: props.status, cls: 'bg-surface-variant text-on-surface-variant' })
const label      = computed(() => resolved.value.label)
const badgeClass = computed(() => resolved.value.cls)
</script>
