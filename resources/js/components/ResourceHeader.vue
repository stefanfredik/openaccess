<script setup lang="ts">
  import { Button } from '@/components/ui/button'
  import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
  import { Link } from '@inertiajs/vue3'
  import { Filter, Plus, Search, X } from 'lucide-vue-next'

  interface FilterOption {
    label: string
    value: string
  }

  interface FilterConfig {
    key: string
    label?: string
    placeholder?: string
    options: FilterOption[]
    icon?: any
  }

  const props = defineProps<{
    title: string
    description?: string
    searchPlaceholder?: string
    addButtonText?: string
    addRoute?: string
    // Search model
    modelValue?: string
    // Dynamic filters
    filters?: Record<string, any>
    filterConfigs?: FilterConfig[]
  }>()

  const emit = defineEmits(['update:modelValue', 'update:filters', 'clear'])

  const updateFilter = (key: string, value: string) => {
    const newFilters = { ...props.filters, [key]: value }
    emit('update:filters', newFilters)
  }

  const clearAll = () => {
    emit('update:modelValue', '')
    const clearedFilters = {} as any
    if (props.filterConfigs) {
      props.filterConfigs.forEach((f) => (clearedFilters[f.key] = 'all'))
    }
    emit('update:filters', clearedFilters)
    emit('clear')
  }

  const hasActiveFilters = () => {
    if (props.modelValue) return true
    if (!props.filters) return false
    return Object.values(props.filters).some((v) => v !== 'all' && v !== undefined && v !== '')
  }
</script>

<template>
  <div class="flex flex-col gap-6">
    <!-- Top Row: Title & Main Action -->
    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
      <div class="space-y-1">
        <h1 class="text-2xl font-bold tracking-tight text-foreground md:text-3xl">
          {{ title }}
        </h1>
        <p v-if="description" class="text-sm font-medium text-muted-foreground">
          {{ description }}
        </p>
      </div>

      <div class="flex items-center gap-2">
        <slot name="top-actions"></slot>
        <Button v-if="addButtonText && addRoute" as-child class="h-10 rounded-lg px-4 font-semibold shadow-sm transition-all hover:opacity-90">
          <Link :href="addRoute">
            <Plus class="mr-2 h-5 w-5" />
            {{ addButtonText }}
          </Link>
        </Button>
      </div>
    </div>

    <!-- Bottom Row: Filters & Search -->
    <div class="flex flex-col gap-3 md:flex-row md:items-center">
      <!-- Search -->
      <div class="relative flex-1 max-w-md">
        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/70" />
        <input
          type="text"
          :value="modelValue"
          @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
          :placeholder="searchPlaceholder || 'Cari data...'"
          class="h-10 w-full rounded-lg border border-border bg-background py-2 pr-10 pl-10 text-sm transition-all focus:border-primary/50 focus:ring-2 focus:ring-primary/20 focus:outline-none" />
        <button
          v-if="modelValue"
          @click="$emit('update:modelValue', '')"
          class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground">
          <X class="h-3.5 w-3.5" />
        </button>
      </div>

      <!-- Dynamic Filters -->
      <div v-if="filterConfigs && filterConfigs.length > 0" class="flex flex-wrap items-center gap-2">
        <div v-for="config in filterConfigs" :key="config.key" class="min-w-[140px]">
          <Select :model-value="String(filters?.[config.key] || 'all')" @update:model-value="(val) => updateFilter(config.key, val)">
            <SelectTrigger class="h-10 border-border/60 bg-background/50 text-xs shadow-none hover:bg-muted/50">
              <div class="flex items-center gap-2">
                <component :is="config.icon || Filter" class="h-3.5 w-3.5 text-muted-foreground/70" />
                <SelectValue :placeholder="config.placeholder || 'Pilih...'" />
              </div>
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">Semua {{ config.label || '' }}</SelectItem>
              <SelectItem v-for="opt in config.options" :key="opt.value" :value="opt.value">
                {{ opt.label }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <!-- Extra Filter Slot -->
        <slot name="extra-filters"></slot>

        <!-- Reset Button -->
        <Button v-if="hasActiveFilters()" variant="ghost" size="sm" @click="clearAll" class="h-10 px-3 text-xs font-bold text-destructive hover:bg-destructive/5">
          Reset
        </Button>
      </div>
    </div>
  </div>
</template>
