<script setup lang="ts">
  import { Head, Link, router } from '@inertiajs/vue3'
  import { debounce } from 'lodash'
  import { Eye, FileText, MoreVertical, Network, Settings, Trash, MapPin } from 'lucide-vue-next'
  import { computed, ref, watch } from 'vue'
  import DeviceDetailPreview from '@/../../Modules/ActiveDevice/resources/assets/js/Components/DeviceDetailPreview.vue'
  import DeviceStatusBadge from '@/../../Modules/ActiveDevice/resources/assets/js/Components/DeviceStatusBadge.vue'
  import Pagination from '@/components/Pagination.vue'
  import ResourceHeader from '@/components/ResourceHeader.vue'
  import ActionMenu from '@/components/ActionMenu.vue'
  import { Button } from '@/components/ui/button'
  import { Card, CardContent } from '@/components/ui/card'
  import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet'
  import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
  import AppLayout from '@/layouts/AppLayout.vue'

  import type { AdSwitch, InfrastructureArea, PaginatedData } from '@/types'

  const props = defineProps<{
    switches: PaginatedData<AdSwitch>
    areas: InfrastructureArea[]
    filters: {
      search?: string
      area_id?: string
    }
  }>()

  const selectedSwitchId = ref<number | null>(null)
  const isDrawerOpen = ref(false)
  const searchQuery = ref(props.filters.search || '')
  const filters = ref({
    area_id: props.filters.area_id || 'all',
  })

  const updateFilters = debounce(() => {
    router.get(
      route('active-device.switch.index'),
      {
        search: searchQuery.value,
        area_id: filters.value.area_id,
      },
      {
        preserveState: true,
        preserveScroll: true,
        replace: true,
      },
    )
  }, 300)

  watch([searchQuery, filters], () => {
    updateFilters()
  }, { deep: true })

  const selectedSwitch = computed(() => {
    return props.switches.data.find((s: any) => s.id === selectedSwitchId.value) || null
  })

  const openDrawer = (networkSwitch: any) => {
    selectedSwitchId.value = networkSwitch.id
    isDrawerOpen.value = true
  }
</script>

<template>
  <Head title="Switch Inventory" />

  <AppLayout
    :breadcrumbs="[
      { title: 'Inventory', href: '#' },
      { title: 'Switch', href: route('active-device.switch.index') },
    ]">
    <div class="flex flex-col gap-6 p-4 md:p-8">
      <ResourceHeader
        title="Switch Inventory"
        description="Kelola inventori perangkat Switch dan distribusi jaringan."
        search-placeholder="Cari IP, Port, atau Nama..."
        add-button-text="Tambah Switch"
        :add-route="route('active-device.switch.create')"
        v-model="searchQuery"
        v-model:filters="filters"
        :filter-configs="[
          {
            key: 'area_id',
            label: 'Wilayah',
            placeholder: 'Wilayah',
            options: areas.map((a) => ({ label: a.name, value: a.id.toString() })),
            icon: MapPin,
          },
        ]" />

      <!-- Table section -->
      <Card class="overflow-hidden rounded-xl border-none bg-card shadow-sm">

        <CardContent class="p-0">
          <Table>
            <TableHeader class="bg-muted/50">
              <TableRow class="border-b border-border hover:bg-transparent">
                <TableHead class="px-6 py-4 text-xs font-semibold tracking-wider text-muted-foreground uppercase">Nama Perangkat</TableHead>
                <TableHead class="px-6 py-4 text-xs font-semibold tracking-wider text-muted-foreground uppercase">Brand / Model</TableHead>
                <TableHead class="px-6 py-4 text-xs font-semibold tracking-wider text-muted-foreground uppercase">Tipe Switch</TableHead>
                <TableHead class="px-6 py-4 text-xs font-semibold tracking-wider text-muted-foreground uppercase">IP Address</TableHead>
                <TableHead class="px-6 py-4 text-xs font-semibold tracking-wider text-muted-foreground uppercase">Port Count</TableHead>
                <TableHead class="px-6 py-4 text-xs font-semibold tracking-wider text-muted-foreground uppercase">Status</TableHead>
                <TableHead class="px-6 py-4 text-right text-xs font-semibold tracking-wider text-muted-foreground uppercase">Aksi</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow
                v-for="item in switches.data"
                :key="item.id"
                @click="openDrawer(item)"
                class="group cursor-pointer border-b border-border transition-colors hover:bg-muted/50">
                <TableCell class="px-6 py-4 font-semibold text-foreground">
                  <div class="flex items-center gap-3">
                    <div
                      class="flex h-9 w-9 items-center justify-center rounded-lg border border-primary/10 bg-primary/5 text-primary shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-foreground">
                      <Network class="h-4.5 w-4.5" />
                    </div>
                    <div class="flex flex-col">
                      <span>{{ item.name }}</span>
                      <span class="font-mono text-[10px] text-muted-foreground">{{ item.code }}</span>
                    </div>
                  </div>
                </TableCell>
                <TableCell class="px-6 py-4 font-medium text-muted-foreground"> {{ item.brand }} {{ item.model }} </TableCell>
                <TableCell class="px-6 py-4 text-sm font-medium text-muted-foreground">
                  {{ item.switch_type || '-' }}
                </TableCell>
                <TableCell class="px-6 py-4 font-mono text-xs text-blue-500 dark:text-blue-400">
                  {{ item.ip_address || '-' }}
                </TableCell>
                <TableCell class="px-6 py-4 text-sm text-muted-foreground"> {{ item.port_count }} Ports </TableCell>
                <TableCell class="px-6 py-4">
                  <DeviceStatusBadge :status="item.is_active ? 'Active' : 'Inactive'" />
                </TableCell>
                <TableCell class="px-6 py-4 text-right" @click.stop>
<ActionMenu
                    :detail-route="route('active-device.switch.show', item.id)"
                    :edit-route="route('active-device.switch.edit', item.id)"
                    :delete-route="route('active-device.switch.destroy', item.id)"
                    delete-message="Hapus Perangkat"
                    @preview="openDrawer(item)"
                  />
                </TableCell>
              </TableRow>
              <TableRow v-if="switches.data.length === 0">
                <TableCell colspan="7" class="h-32 text-center text-muted-foreground italic"> No Switches found in inventory. </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>

      <Pagination :links="switches.links" />

      <!-- Detail Drawer (Sheet) -->
      <Sheet :open="isDrawerOpen" @update:open="isDrawerOpen = $event">
        <SheetTrigger class="hidden" />
        <SheetContent class="flex h-full flex-col border-none p-0 shadow-2xl sm:max-w-[500px]">
          <SheetHeader class="sr-only">
            <SheetTitle>Detail Switch: {{ selectedSwitch?.name }}</SheetTitle>
            <SheetDescription>Koneksi, interface, dan layanan aktif pada perangkat ini.</SheetDescription>
          </SheetHeader>
          <DeviceDetailPreview v-if="selectedSwitch" :device="selectedSwitch" />
        </SheetContent>
      </Sheet>
    </div>
  </AppLayout>
</template>

<style scoped>
  .font-inter {
    font-family: 'Inter', sans-serif;
  }
</style>
