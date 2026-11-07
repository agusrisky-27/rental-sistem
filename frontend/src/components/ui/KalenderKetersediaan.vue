<template>
  <div class="bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl p-4">
    <div class="flex items-center justify-between mb-3">
      <button type="button" @click="geser(-1)" title="Bulan sebelumnya"
        class="p-1.5 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
        <span class="material-symbols-outlined icon-20">chevron_left</span>
      </button>
      <span class="font-semibold text-slate-900 dark:text-white">{{ judul }}</span>
      <button type="button" @click="geser(1)" title="Bulan berikutnya"
        class="p-1.5 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
        <span class="material-symbols-outlined icon-20">chevron_right</span>
      </button>
    </div>

    <div class="grid grid-cols-7 gap-1 text-center text-xs text-slate-500 dark:text-slate-400 mb-1">
      <span v-for="h in hari" :key="h">{{ h }}</span>
    </div>
    <div class="grid grid-cols-7 gap-1">
      <span v-for="n in offset" :key="'k' + n"></span>
      <span v-for="d in hariBulan" :key="d.tgl"
        class="py-1.5 text-center text-sm rounded-md"
        :class="d.terisi
          ? 'bg-rose-100 text-rose-800 dark:bg-rose-950/70 dark:text-rose-300 font-semibold'
          : 'text-slate-700 dark:text-slate-300'">
        {{ d.n }}
      </span>
    </div>

    <div class="flex items-center gap-4 mt-3 text-xs text-slate-500 dark:text-slate-400">
      <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-rose-100 dark:bg-rose-950/70 inline-block"></span>Terisi</span>
      <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded border border-slate-300 dark:border-slate-600 inline-block"></span>Tersedia</span>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  // [{ tanggal_mulai, tanggal_akhir }]
  jadwal: { type: Array, default: () => [] },
})

const hari = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']
const bulanNama = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']

const now = new Date()
const tahun = ref(now.getFullYear())
const bulan = ref(now.getMonth())

const judul = computed(() => `${bulanNama[bulan.value]} ${tahun.value}`)
const offset = computed(() => new Date(tahun.value, bulan.value, 1).getDay())

function geser(delta) {
  const d = new Date(tahun.value, bulan.value + delta, 1)
  tahun.value = d.getFullYear()
  bulan.value = d.getMonth()
}

const pad = (n) => String(n).padStart(2, '0')

// Perbandingan string YYYY-MM-DD: terisi jika mulai <= d < akhir
const hariBulan = computed(() => {
  const jumlah = new Date(tahun.value, bulan.value + 1, 0).getDate()
  return Array.from({ length: jumlah }, (_, i) => {
    const tgl = `${tahun.value}-${pad(bulan.value + 1)}-${pad(i + 1)}`
    const terisi = props.jadwal.some(j => j.tanggal_mulai.slice(0, 10) <= tgl && tgl < j.tanggal_akhir.slice(0, 10))
    return { n: i + 1, tgl, terisi }
  })
})
</script>
