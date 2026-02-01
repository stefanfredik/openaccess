<script setup lang="ts">
  import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
  } from '@/components/ui/dropdown-menu'
  import { Plus } from 'lucide-vue-next'

  const props = defineProps<{
    pendingDeviceType: string | null
    relocatingDevice: any
    deviceTypes: {
      active: Array<{ label: string; value: string }>
      passive: Array<{ label: string; value: string }>
    }
    colors: Record<string, string>
  }>()

  const emit = defineEmits(['update:pendingDeviceType', 'startCableDrawing'])
</script>

<template>
  <!-- Floating Drawing Tools -->
  <div class="absolute bottom-10 left-4 z-[1000]">
    <DropdownMenu v-if="!pendingDeviceType && !relocatingDevice">
      <DropdownMenuTrigger as-child>
        <button
          class="flex h-12 w-12 items-center justify-center rounded-full bg-white shadow-xl ring-1 ring-black/10 transition-all hover:scale-110 hover:bg-gray-50 active:scale-95 focus:outline-none"
          title="Tambah Perangkat">
          <Plus class="h-6 w-6 text-blue-600" />
        </button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="start" side="top" class="z-[1001] mb-2 w-56">
        <DropdownMenuLabel>Pilih Perangkat</DropdownMenuLabel>
        <DropdownMenuSeparator />
        <DropdownMenuGroup>
          <DropdownMenuLabel class="py-1 text-[10px] font-bold text-muted-foreground uppercase">Aktif</DropdownMenuLabel>
          <DropdownMenuItem
            v-for="type in deviceTypes.active"
            :key="type.value"
            @click="emit('update:pendingDeviceType', type.value)"
            class="cursor-pointer">
            <div class="flex items-center gap-2">
              <div class="h-2 w-2 rounded-full" :style="{ backgroundColor: colors[type.value] || colors.blue }"></div>
              {{ type.label }}
            </div>
          </DropdownMenuItem>
        </DropdownMenuGroup>
        <DropdownMenuSeparator />
        <DropdownMenuGroup>
          <DropdownMenuLabel class="py-1 text-[10px] font-bold text-muted-foreground uppercase">Pasif</DropdownMenuLabel>
          <DropdownMenuItem
            v-for="type in deviceTypes.passive"
            :key="type.value"
            @click="type.value === 'cable' ? emit('startCableDrawing') : emit('update:pendingDeviceType', type.value)"
            class="cursor-pointer">
            <div class="flex items-center gap-2">
              <div class="h-2 w-2 rounded-full" :style="{ backgroundColor: colors[type.value] || colors.blue }"></div>
              {{ type.label }}
            </div>
          </DropdownMenuItem>
        </DropdownMenuGroup>
      </DropdownMenuContent>
    </DropdownMenu>
  </div>
</template>
