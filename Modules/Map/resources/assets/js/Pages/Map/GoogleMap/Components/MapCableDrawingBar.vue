<script setup lang="ts">
  import { Switch as UiSwitch } from '@/components/ui/switch'
  import { Plus } from 'lucide-vue-next'

  const props = defineProps<{
    cableDrawStep: string
    pendingLength: number
    nearbyDevice: any
    autoRouteMode: boolean
    isLoadingRoute: boolean
  }>()

  const emit = defineEmits(['confirmStartPoint', 'confirmEndPoint', 'finalizeCableStepDrawing', 'cancelCableStepDrawing', 'update:autoRouteMode'])
</script>

<template>
  <div v-if="cableDrawStep !== 'idle'" class="absolute bottom-10 left-1/2 z-[1000] -translate-x-1/2">
    <div
      class="flex items-center gap-3 rounded-2xl bg-white px-5 py-3 shadow-2xl ring-1 ring-black/10 animate-in fade-in zoom-in slide-in-from-bottom-4">
      <!-- Step Indicator -->
      <div class="flex items-center gap-2">
        <div
          :class="[
            'flex h-6 w-6 items-center justify-center rounded-full text-xs font-bold',
            cableDrawStep === 'selectStart' ? 'bg-green-500 text-white' : 'bg-blue-500 text-white',
          ]">
          {{ cableDrawStep === 'selectStart' ? '1' : cableDrawStep === 'selectEnd' ? '2' : '3' }}
        </div>
        <div class="text-sm font-semibold text-gray-700">
          <span v-if="cableDrawStep === 'selectStart'">Tentukan Titik Awal</span>
          <span v-else-if="cableDrawStep === 'selectEnd'">Tentukan Titik Akhir</span>
          <span v-else-if="cableDrawStep === 'editPath'">Edit Jalur Kabel</span>
        </div>
      </div>

      <!-- Length Display -->
      <div v-if="pendingLength > 0" class="flex items-center gap-2 border-l pl-3">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="16"
          height="16"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          class="text-blue-500">
          <path d="M2 12h20M12 2l10 10-10 10" />
        </svg>
        <span class="text-sm font-black text-blue-600">{{ pendingLength.toFixed(1) }} m</span>
      </div>

      <!-- Snap Indicator -->
      <div v-if="nearbyDevice && (cableDrawStep === 'selectStart' || cableDrawStep === 'selectEnd')" class="flex items-center gap-2 border-l pl-3">
        <div class="flex items-center gap-1.5 rounded-full bg-green-100 px-3 py-1">
          <div class="h-2 w-2 animate-pulse rounded-full bg-green-500"></div>
          <span class="text-xs font-semibold text-green-700">Snap: {{ nearbyDevice.name }}</span>
        </div>
      </div>

      <!-- Auto-Route Toggle -->
      <div v-if="cableDrawStep !== 'editPath'" class="flex items-center gap-2 border-l pl-3">
        <label class="flex cursor-pointer items-center gap-2">
          <UiSwitch :checked="autoRouteMode" @update:checked="emit('update:autoRouteMode', $event)" />
          <span class="text-xs font-medium text-gray-600">Ikuti Jalan</span>
        </label>
      </div>

      <!-- Loading Indicator -->
      <div v-if="isLoadingRoute" class="flex items-center gap-2 border-l pl-3">
        <svg class="h-4 w-4 animate-spin text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path
            class="opacity-75"
            fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="text-xs text-gray-500">Mencari rute...</span>
      </div>

      <div class="h-6 w-px bg-gray-200"></div>

      <!-- Action Buttons -->
      <div class="flex items-center gap-2">
        <button
          v-if="cableDrawStep === 'selectStart'"
          @click="emit('confirmStartPoint')"
          class="flex h-9 items-center gap-2 rounded-full bg-green-500 px-4 text-sm font-semibold text-white shadow-sm transition-all hover:bg-green-600 hover:scale-105 active:scale-95">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="16"
            height="16"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="3"
            stroke-linecap="round"
            stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"></polyline>
          </svg>
          Konfirmasi
        </button>

        <button
          v-if="cableDrawStep === 'selectEnd'"
          @click="emit('confirmEndPoint')"
          class="flex h-9 items-center gap-2 rounded-full bg-red-500 px-4 text-sm font-semibold text-white shadow-sm transition-all hover:bg-red-600 hover:scale-105 active:scale-95">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="16"
            height="16"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="3"
            stroke-linecap="round"
            stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"></polyline>
          </svg>
          Konfirmasi
        </button>

        <button
          v-if="cableDrawStep === 'editPath'"
          @click="emit('finalizeCableStepDrawing')"
          class="flex h-9 items-center gap-2 rounded-full bg-blue-500 px-4 text-sm font-semibold text-white shadow-sm transition-all hover:bg-blue-600 hover:scale-105 active:scale-95">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="16"
            height="16"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="3"
            stroke-linecap="round"
            stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"></polyline>
          </svg>
          Selesai
        </button>

        <button
          @click="emit('cancelCableStepDrawing')"
          class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-gray-600 transition-all hover:bg-red-100 hover:text-red-500 hover:scale-105 active:scale-95"
          title="Batal">
          <Plus class="h-5 w-5 rotate-45" />
        </button>
      </div>
    </div>
  </div>
</template>
