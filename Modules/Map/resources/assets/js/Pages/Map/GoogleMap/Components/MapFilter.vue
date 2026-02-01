<script setup lang="ts">
  import { Button } from '@/components/ui/button'
  import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
  import { Switch as UiSwitch } from '@/components/ui/switch'
  import { Layers } from 'lucide-vue-next'

  const props = defineProps<{
    showFilter: boolean
    isFilterMinimized: boolean
    selectedAreaId: string
    areas: Array<{ id: number; name: string }>
    deviceFilters: Record<string, boolean>
    availableFilters: Array<{ key: string; label: string; icon: string | null; color: string }>
    selectedAreaName: string
  }>()

  const emit = defineEmits(['update:selectedAreaId', 'update:isFilterMinimized', 'update:deviceFilters', 'loadMapData'])

  const updateSelectedAreaId = (val: string) => {
    emit('update:selectedAreaId', val)
    emit('loadMapData', true)
  }

  const toggleFilter = (key: string, val: boolean) => {
    const newFilters = { ...props.deviceFilters, [key]: val }
    emit('update:deviceFilters', newFilters)
  }
</script>

<template>
  <div
    v-if="showFilter"
    :class="[
      'absolute top-3 right-3 z-[1000] flex w-60 animate-in flex-col gap-2 rounded-lg bg-white/95 p-3 shadow-lg backdrop-blur-md transition-all slide-in-from-right-5',
      isFilterMinimized ? 'h-auto w-8' : '',
    ]">
    <!-- Header -->
    <div class="flex items-center justify-between border-b pb-2">
      <h3 v-if="!isFilterMinimized" class="font-bold text-gray-800">Filter</h3>
      <div class="flex items-center gap-1">
        <Button
          variant="ghost"
          size="icon"
          class="h-6 w-6 rounded-full hover:bg-gray-100"
          @click="emit('update:isFilterMinimized', !isFilterMinimized)"
          :title="isFilterMinimized ? 'Expand' : 'Minimize'">
          <Layers class="h-4 w-4 text-gray-500" />
        </Button>
      </div>
    </div>

    <!-- Minimized View -->
    <div v-if="isFilterMinimized" class="text-sm font-medium text-gray-600">
      {{ selectedAreaName }}
    </div>

    <!-- Expanded View -->
    <div v-else class="custom-scrollbar space-y-4 overflow-y-auto pr-2">
      <!-- Area Filter -->
      <div class="space-y-2">
        <label class="text-xs font-semibold tracking-wider text-gray-500 uppercase">Wilayah</label>
        <Select :model-value="selectedAreaId" @update:model-value="updateSelectedAreaId">
          <SelectTrigger>
            <SelectValue placeholder="Pilih Wilayah" />
          </SelectTrigger>
          <SelectContent class="z-[2001]">
            <SelectItem value="all"> Semua Wilayah </SelectItem>
            <SelectItem v-for="area in areas" :key="area.id" :value="String(area.id)">
              {{ area.name }}
            </SelectItem>
          </SelectContent>
        </Select>
      </div>

      <!-- Device Filters -->
      <div class="custom-scrollbar max-h-[50vh] space-y-3 overflow-y-auto pr-1">
        <div class="flex items-center justify-between">
          <label class="text-xs font-semibold tracking-wider text-gray-500 uppercase"> Perangkat </label>
        </div>
        <div class="mt-2 space-y-2">
          <div
            v-for="item in availableFilters"
            :key="item.key"
            class="flex cursor-pointer items-center justify-between rounded-lg border border-gray-100 p-2 transition-colors hover:bg-gray-50"
            @click="toggleFilter(item.key, !deviceFilters[item.key])">
            <div class="flex items-center gap-3">
              <div
                class="flex h-8 w-8 items-center justify-center rounded-full border-2 bg-white shadow-sm"
                :style="{ borderColor: item.color || '#ccc' }">
                <svg
                  v-if="item.icon"
                  xmlns="http://www.w3.org/2000/svg"
                  width="16"
                  height="16"
                  viewBox="0 0 24 24"
                  fill="none"
                  :stroke="item.color || '#ccc'"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  v-html="item.icon"></svg>
                <div v-else-if="item.key === 'cable'" class="h-1 w-6 -rotate-45 transform" :style="{ backgroundColor: '#3b82f6' }"></div>
                <div v-else class="h-2 w-2 rounded-full bg-gray-400"></div>
              </div>
              <span class="text-sm font-medium text-gray-700">{{ item.label }}</span>
            </div>
            <UiSwitch :checked="deviceFilters[item.key]" aria-readonly="true" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
  .custom-scrollbar::-webkit-scrollbar {
    width: 4px;
  }
  .custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
  }
</style>
