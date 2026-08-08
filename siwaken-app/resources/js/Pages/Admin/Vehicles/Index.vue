<template>
  <AdminLayout pageTitle="Manajemen Kendaraan">
    <!-- Header & Actions -->
    <div class="flex justify-between items-center mb-8">
      <h1 class="text-headline-lg font-headline-lg text-primary">Manajemen Kendaraan</h1>
      <button class="bg-secondary text-on-secondary text-label-md font-label-md font-bold px-6 py-3 rounded-lg flex items-center gap-2 shadow-sm hover:bg-secondary-container transition-colors">
        <span class="material-symbols-outlined text-sm" data-icon="add">add</span>
        Tambah Kendaraan
      </button>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white/70 backdrop-blur-md rounded-xl p-6 mb-8 flex flex-col md:flex-row gap-4 items-center justify-between border border-white/20 shadow-sm">
      <div class="flex gap-4 w-full md:w-auto">
        <div class="relative w-full md:w-64">
          <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">search</span>
          <input class="w-full pl-10 pr-4 py-2 bg-surface rounded-lg border border-outline-variant focus:border-secondary focus:ring-2 focus:ring-secondary/20 transition-all text-body-md font-body-md outline-none" placeholder="Cari kendaraan..." type="text">
        </div>
        <select class="bg-surface border border-outline-variant rounded-lg px-4 py-2 text-body-md font-body-md focus:border-secondary focus:ring-2 focus:ring-secondary/20 outline-none w-full md:w-auto">
          <option value="">Semua Tipe</option>
          <option value="suv">SUV</option>
          <option value="sedan">Sedan</option>
          <option value="mpv">MPV</option>
        </select>
        <select class="bg-surface border border-outline-variant rounded-lg px-4 py-2 text-body-md font-body-md focus:border-secondary focus:ring-2 focus:ring-secondary/20 outline-none w-full md:w-auto">
          <option value="">Semua Status</option>
          <option value="tersedia">Tersedia</option>
          <option value="disewa">Disewa</option>
          <option value="maintenance">Maintenance</option>
        </select>
      </div>
      <button class="bg-surface border border-outline text-on-surface px-4 py-2 rounded-lg text-label-md font-label-md flex items-center gap-2 hover:bg-surface-container-low transition-colors">
        <span class="material-symbols-outlined text-sm" data-icon="filter_list">filter_list</span>
        Filter Lanjut
      </button>
    </div>

    <!-- Vehicle Table -->
    <div class="bg-surface rounded-xl shadow-sm overflow-hidden">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="border-b border-surface-variant bg-surface-container-low/50">
            <th class="py-4 px-6 text-label-md font-label-md text-on-surface-variant font-semibold">Kendaraan</th>
            <th class="py-4 px-6 text-label-md font-label-md text-on-surface-variant font-semibold">Tipe</th>
            <th class="py-4 px-6 text-label-md font-label-md text-on-surface-variant font-semibold">Nomor Plat</th>
            <th class="py-4 px-6 text-label-md font-label-md text-on-surface-variant font-semibold">Harga/Hari</th>
            <th class="py-4 px-6 text-label-md font-label-md text-on-surface-variant font-semibold">Status</th>
            <th class="py-4 px-6 text-label-md font-label-md text-on-surface-variant font-semibold text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="text-body-md font-body-md">
          <!-- Row 1 -->
          <tr class="border-b border-surface-variant hover:bg-surface-container-lowest/50 transition-colors group">
            <td class="py-4 px-6 flex items-center gap-4">
              <div class="w-16 h-12 rounded-lg bg-surface-container overflow-hidden">
                <img alt="Toyota Fortuner" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAQqo2gkVgXkTR8pr1OCjg5AliqeMCsPIjfGH7ibydZycKKA119FIljPvNnWPPF7-UqdY4ULduhl_SsLDC7QIRa54iP2i8uMMOpBFT5WJ7boDSYBRwaQ8-DnjagewPHjo71EVhxheqMwov7AfTZs4ONWeiHJUm3LXmV-L8WeUf633VTVSA53KWS-tvvGhyKs6fPFW-0-B5b7wIuHlzdx4LZqZGgDnKq0VQHQ-pKeNEWOUA2S87F14tT">
              </div>
              <span class="font-semibold text-primary">Toyota Fortuner</span>
            </td>
            <td class="py-4 px-6 text-on-surface">SUV</td>
            <td class="py-4 px-6 text-on-surface">B 1234 XYZ</td>
            <td class="py-4 px-6 text-on-surface">Rp 1.500.000</td>
            <td class="py-4 px-6">
              <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800">Tersedia</span>
            </td>
            <td class="py-4 px-6 text-right">
              <button class="text-on-surface-variant hover:text-secondary p-1 opacity-0 group-hover:opacity-100 transition-opacity"><span class="material-symbols-outlined" data-icon="edit">edit</span></button>
              <button class="text-on-surface-variant hover:text-error p-1 opacity-0 group-hover:opacity-100 transition-opacity ml-2"><span class="material-symbols-outlined" data-icon="delete">delete</span></button>
            </td>
          </tr>
          <!-- Row 2 -->
          <tr class="border-b border-surface-variant hover:bg-surface-container-lowest/50 transition-colors group">
            <td class="py-4 px-6 flex items-center gap-4">
              <div class="w-16 h-12 rounded-lg bg-surface-container overflow-hidden">
                <img alt="Honda Camry" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCl7YTixF4YJI0NEOpcHmnvqn-aDEj4bdfXbz0jg2b1nBPz8Y7Tc_eP2Pc8IPE5ZZfL7EdGu0mcTRbNa2-j6xDbKuGQgMBxhZdxVjKyVAkj4D_5DZUOC3M25-WP-AhN4JQa515EqHLMbs2z0mx_cn4b5nwuCiB8vKInyxb5n1bw67DSUwmLH0MQIRd2P5O6mUp8DkjmkzlkeO-AdnDK8Vwm9_GH0IdHHqU6hBlJXdg9JUQm-iu7wQhv">
              </div>
              <span class="font-semibold text-primary">Honda Camry</span>
            </td>
            <td class="py-4 px-6 text-on-surface">Sedan</td>
            <td class="py-4 px-6 text-on-surface">D 5678 ABC</td>
            <td class="py-4 px-6 text-on-surface">Rp 2.000.000</td>
            <td class="py-4 px-6">
              <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-100 text-blue-800">Disewa</span>
            </td>
            <td class="py-4 px-6 text-right">
              <button class="text-on-surface-variant hover:text-secondary p-1 opacity-0 group-hover:opacity-100 transition-opacity"><span class="material-symbols-outlined" data-icon="edit">edit</span></button>
              <button class="text-on-surface-variant hover:text-error p-1 opacity-0 group-hover:opacity-100 transition-opacity ml-2"><span class="material-symbols-outlined" data-icon="delete">delete</span></button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div class="py-4 px-6 border-t border-surface-variant flex justify-between items-center bg-surface-container-lowest/30">
        <span class="text-label-sm font-label-sm text-on-surface-variant">Menampilkan 1-2 dari 45 kendaraan</span>
        <div class="flex gap-2">
          <button class="p-2 border border-outline-variant rounded-md text-on-surface-variant hover:bg-surface-container-low disabled:opacity-50" disabled>
            <span class="material-symbols-outlined text-sm" data-icon="chevron_left">chevron_left</span>
          </button>
          <button class="p-2 border border-outline-variant rounded-md text-on-surface-variant hover:bg-surface-container-low">
            <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
</script>
