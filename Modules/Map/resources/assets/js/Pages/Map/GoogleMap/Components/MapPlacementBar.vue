<script setup lang="ts">
  import { Plus } from 'lucide-vue-next'

  const props = defineProps<{
    pendingDeviceType: string | null
    relocatingDevice: any
    showCreateModal: boolean
    pendingLength: number
  }>()

  const emit = defineEmits(['update:showCreateModal', 'saveEditedCable', 'saveRelocation', 'cancelAddingDevice'])
</script>

<template>
  <div
    v-if="((pendingDeviceType && pendingDeviceType !== 'cable') || relocatingDevice) && !showCreateModal"
    class="absolute bottom-6 left-1/2 z-[1000] w-[90%] -translate-x-1/2 sm:bottom-10 sm:w-auto">
    <div
      class="flex items-center justify-between gap-3 rounded-full bg-white px-4 py-2 shadow-2xl ring-1 ring-black/10 animate-in fade-in zoom-in slide-in-from-bottom-4 sm:justify-start">
      <span v-if="pendingDeviceType" class="truncate text-[10px] font-bold text-gray-600 uppercase tracking-tight sm:text-xs">
        Menambah {{ pendingDeviceType }}
      </span>
      <span v-else-if="relocatingDevice" class="truncate text-[10px] font-bold text-blue-600 uppercase tracking-tight sm:text-xs">
        Pindah Lokasi: {{ relocatingDevice.name }}
      </span>

      <div v-if="relocatingDevice && relocatingDevice.type === 'cable' && pendingLength > 0" class="flex items-center gap-2">
        <div class="h-5 w-px bg-gray-200"></div>
        <span class="text-[10px] font-black text-blue-600 sm:text-xs">{{ pendingLength.toFixed(2) }}m</span>
      </div>
      <div class="h-5 w-px bg-gray-200"></div>

      <div class="flex items-center gap-2">
        <!-- Confirm Placement -->
        <button
          v-if="pendingDeviceType"
          @click="emit('update:showCreateModal', true)"
          class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-500 text-white shadow-sm transition-all hover:bg-blue-600 hover:scale-110 active:scale-95"
          title="Konfirmasi Lokasi">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="20"
            height="20"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="3"
            stroke-linecap="round"
            stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"></polyline>
          </svg>
        </button>

        <!-- Finalize Relocation -->
        <button
          v-else-if="relocatingDevice"
          @click="emit(relocatingDevice.type === 'cable' ? 'saveEditedCable' : 'saveRelocation')"
          class="flex h-9 items-center gap-2 rounded-full bg-green-500 px-4 text-white shadow-sm transition-all hover:bg-green-600 hover:scale-110 active:scale-95"
          title="Simpan Perubahan">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="20"
            height="20"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="3"
            stroke-linecap="round"
            stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"></polyline>
          </svg>
          <span class="hidden text-xs font-bold sm:inline">Simpan</span>
        </button>

        <!-- Cancel -->
        <button
          @click="emit('cancelAddingDevice')"
          class="flex h-9 w-9 items-center justify-center rounded-full bg-red-50 text-red-500 transition-all hover:bg-red-500 hover:text-white hover:scale-110 active:scale-95"
          title="Batal">
          <Plus class="h-5 w-5 rotate-45" />
        </button>
      </div>
    </div>
  </div>
</template>
