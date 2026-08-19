<template>
  <span class="inline-flex items-center px-2.5 py-1 rounded-full text-label-sm font-label-sm font-bold uppercase tracking-wider transition-colors"
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
  tersedia:             { label: 'Tersedia',            cls: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300 dark:border dark:border-emerald-800/60' },
  disewa:               { label: 'Disewa',              cls: 'bg-blue-100 text-blue-800 dark:bg-blue-950/70 dark:text-blue-300 dark:border dark:border-blue-800/60' },
  maintenance:          { label: 'Maintenance',         cls: 'bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300 dark:border dark:border-amber-800/60' },
  aktif:                { label: 'Aktif',               cls: 'bg-blue-100 text-blue-800 dark:bg-blue-950/70 dark:text-blue-300 dark:border dark:border-blue-800/60' },
  selesai:              { label: 'Selesai',             cls: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300 dark:border dark:border-emerald-800/60' },
  pending:              { label: 'Pending',             cls: 'bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300 dark:border dark:border-amber-800/60' },
  dibatalkan:           { label: 'Dibatalkan',          cls: 'bg-rose-100 text-rose-800 dark:bg-rose-950/70 dark:text-rose-300 dark:border dark:border-rose-800/60' },
  menunggu:             { label: 'Menunggu',            cls: 'bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300 dark:border dark:border-amber-800/60' },
  berhasil:             { label: 'Berhasil',            cls: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300 dark:border dark:border-emerald-800/60' },
  lunas:                { label: 'Lunas',               cls: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300 dark:border dark:border-emerald-800/60' },
  terlambat:            { label: 'Terlambat',           cls: 'bg-rose-100 text-rose-800 dark:bg-rose-950/70 dark:text-rose-300 dark:border dark:border-rose-800/60' },
  'tepat waktu':        { label: 'Tepat Waktu',         cls: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300 dark:border dark:border-emerald-800/60' },
  ditolak:              { label: 'Ditolak',             cls: 'bg-rose-100 text-rose-800 dark:bg-rose-950/70 dark:text-rose-300 dark:border dark:border-rose-800/60' },
  'hari ini':           { label: 'Hari Ini',            cls: 'bg-sky-100 text-sky-800 dark:bg-sky-950/70 dark:text-sky-300 dark:border dark:border-sky-800/60' },
  'menunggu verifikasi':{ label: 'Menunggu Verifikasi', cls: 'bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300 dark:border dark:border-amber-800/60' },
  gold:                 { label: 'Gold',                cls: 'bg-amber-100 text-amber-900 dark:bg-amber-950/80 dark:text-amber-300 dark:border dark:border-amber-700/60' },
  silver:               { label: 'Silver',              cls: 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200 dark:border dark:border-slate-700' },
  basic:                { label: 'Basic',               cls: 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 dark:border dark:border-blue-800/50' },
}

const resolved  = computed(() => map[props.status?.toLowerCase()] || { label: props.status, cls: 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200 dark:border dark:border-slate-700' })
const label      = computed(() => resolved.value.label)
const badgeClass = computed(() => resolved.value.cls)
</script>
