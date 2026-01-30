<script setup lang="ts">
  import { Head, Link, router } from '@inertiajs/vue3'
  import { debounce } from 'lodash'
  import { Eye, FileText, MoreVertical, Router as RouterIcon, Settings, Trash, MapPin } from 'lucide-vue-next'
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

  import type { InfrastructureArea, PaginatedData, Router } from '@/types'

  const props = defineProps<{
    routers: PaginatedData<Router>
    areas: InfrastructureArea[]
    filters: {
      search?: string
      area_id?: string
    }
  }>()

  const searchQuery = ref(props.filters.search || '')
  const filters = ref({
    area_id: props.filters.area_id || 'all',
  })
  const selectedRouterId = ref<number | null>(null)
  const isDrawerOpen = ref(false)

  const updateFilters = debounce(() => {
    router.get(
      route('active-device.router.index'),
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

  const selectedRouter = computed(() => {
    return props.routers.data.find((r: any) => r.id === selectedRouterId.value) || null
  })

  const openDrawer = (router: any) => {
    selectedRouterId.value = router.id
    isDrawerOpen.value = true
  }
</script>

<template>
  <Head title="Routers" />

  <AppLayout
    :breadcrumbs="[
      { title: 'Inventory', href: '#' },
      { title: 'Router', href: route('active-device.router.index') },
    ]">
    <div class="flex flex-col gap-6 p-4 md:p-8">
      <ResourceHeader
        title="Router Inventory"
        description="Kelola perangkat Router."
        search-placeholder="Cari router..."
        add-button-text="Tambah Router"
        :add-route="route('active-device.router.create')"
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

      <Card class="overflow-hidden rounded-xl border-none bg-card shadow-sm">

        <CardContent class="p-0">
          <Table>
            <TableHeader class="bg-muted/50">
              <TableRow class="border-b border-border hover:bg-transparent">
                <TableHead class="px-6 py-4 text-xs font-semibold tracking-wider text-muted-foreground uppercase">Nama Perangkat</TableHead>
                <TableHead class="px-6 py-4 text-xs font-semibold tracking-wider text-muted-foreground uppercase">Area / POP</TableHead>
                <TableHead class="px-6 py-4 text-xs font-semibold tracking-wider text-muted-foreground uppercase">Ports</TableHead>
                <TableHead class="px-6 py-4 text-xs font-semibold tracking-wider text-muted-foreground uppercase">IP Address</TableHead>
                <TableHead class="px-6 py-4 text-xs font-semibold tracking-wider text-muted-foreground uppercase">Status</TableHead>
                <TableHead class="px-6 py-4 text-right text-xs font-semibold tracking-wider text-muted-foreground uppercase">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow
                v-for="item in routers.data"
                :key="item.id"
                @click="openDrawer(item)"
                class="group cursor-pointer border-b border-border transition-colors hover:bg-muted/50">
                <TableCell class="px-6 py-4 font-semibold text-foreground">
                  <div class="flex items-center gap-3">
                    <div
                      class="flex h-9 w-9 items-center justify-center rounded-lg border border-primary/10 bg-primary/5 text-primary shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-foreground">
                      <RouterIcon class="h-4.5 w-4.5" />
                    </div>
                    <div class="flex flex-col">
                      <span>{{ item.name }}</span>
                      <span class="font-mono text-[10px] text-muted-foreground">
                        {{ item.brand }}
                        {{ item.model }}
                        <span v-if="item.code">({{ item.code }})</span>
                      </span>
                    </div>
                  </div>
                </TableCell>
                <TableCell class="px-6 py-4 text-sm text-muted-foreground">
                  <div class="flex flex-col">
                    <span>{{ item.area?.name || '-' }}</span>
                    <span class="text-[10px] text-muted-foreground/80">{{ item.pop?.name || '-' }}</span>
                  </div>
                </TableCell>
                <TableCell class="px-6 py-4 text-sm text-muted-foreground"> {{ item.port_count }} Ports </TableCell>
                <TableCell class="px-6 py-4 font-mono text-xs text-blue-500 dark:text-blue-400">{{ item.ip_address || '-' }}</TableCell>
                <TableCell class="px-6 py-4">
                  <DeviceStatusBadge :status="item.is_active ? 'Active' : 'Inactive'" />
                </TableCell>
                <TableCell class="px-6 py-4 text-right" @click.stop>
<ActionMenu
                    :detail-route="route('active-device.router.show', item.id)"
                    :edit-route="route('active-device.router.edit', item.id)"
                    :delete-route="route('active-device.router.destroy', item.id)"
                    delete-message="Hapus Router"
                    @preview="openDrawer(item)"
                  />
                </TableCell>
              </TableRow>
              <TableRow v-if="routers.data.length === 0">
                <TableCell colspan="6" class="h-32 text-center text-muted-foreground italic"> No Routers found in inventory. </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>

      <Pagination :links="routers.links" />

      <!-- Detail Drawer (Sheet) -->
      <Sheet :open="isDrawerOpen" @update:open="isDrawerOpen = $event">
        <SheetTrigger class="hidden" />
        <SheetContent class="flex h-full flex-col border-none p-0 shadow-2xl sm:max-w-[500px]">
          <SheetHeader class="sr-only">
            <SheetTitle>Detail Router: {{ selectedRouter?.name }}</SheetTitle>
            <SheetDescription>Koneksi, interface, dan layanan aktif pada perangkat ini.</SheetDescription>
          </SheetHeader>
          <DeviceDetailPreview v-if="selectedRouter" :device="selectedRouter" />
        </SheetContent>
      </Sheet>
    </div>
  </AppLayout>
</template>
