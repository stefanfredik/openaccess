<script setup lang="ts">
  import DeviceDetailPreview from '@/../../Modules/ActiveDevice/resources/assets/js/Components/DeviceDetailPreview.vue'
  import DeviceStatusBadge from '@/../../Modules/ActiveDevice/resources/assets/js/Components/DeviceStatusBadge.vue'
  import ResourceHeader from '@/components/ResourceHeader.vue'
  import ActionMenu from '@/components/ActionMenu.vue'
  import { Button } from '@/components/ui/button'
  import { Card, CardContent } from '@/components/ui/card'
  import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet'
  import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
  import AppLayout from '@/layouts/AppLayout.vue'
  import Pagination from '@/components/Pagination.vue'
  import { Head, Link, router } from '@inertiajs/vue3'
  import { debounce } from 'lodash'
  import { Eye, FileText, Home, MoreVertical, Settings, Trash, MapPin } from 'lucide-vue-next'
  import { computed, ref, watch } from 'vue'

  import type { Cpe, InfrastructureArea, PaginatedData } from '@/types'

  const props = defineProps<{
    cpes: PaginatedData<Cpe>
    areas: InfrastructureArea[]
    filters: {
      search?: string
      area_id?: string
    }
  }>()

  const selectedCpeId = ref<number | null>(null)
  const isDrawerOpen = ref(false)
  const searchQuery = ref(props.filters.search || '')
  const filters = ref({
    area_id: props.filters.area_id || 'all',
  })

  const updateFilters = debounce(() => {
    router.get(
      route('cpe.index'),
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

  const selectedCpe = computed(() => {
    return props.cpes.data.find((c: any) => c.id === selectedCpeId.value) || null
  })

  const openDrawer = (cpe: any) => {
    selectedCpeId.value = cpe.id
    isDrawerOpen.value = true
  }
</script>

<template>
  <Head title="CPE Inventory" />

  <AppLayout
    :breadcrumbs="[
      { title: 'Inventory', href: '#' },
      { title: 'CPE', href: route('cpe.index') },
    ]">
    <div class="flex flex-col gap-6 p-4 md:p-8">
      <ResourceHeader
        title="CPE Inventory"
        description="Kelola inventori perangkat Customer Premises Equipment (CPE)."
        search-placeholder="Cari IP, Port, atau Nama..."
        add-button-text="Tambah CPE"
        :add-route="route('cpe.create')"
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
              <TableRow class="border-b hover:bg-transparent">
                <TableHead class="px-6 py-4 text-xs font-semibold tracking-wider text-muted-foreground uppercase">Nama Perangkat</TableHead>
                <TableHead class="px-6 py-4 text-xs font-semibold tracking-wider text-muted-foreground uppercase">Brand / Model</TableHead>
                <TableHead class="px-6 py-4 text-xs font-semibold tracking-wider text-muted-foreground uppercase">IP Address</TableHead>
                <TableHead class="px-6 py-4 text-xs font-semibold tracking-wider text-muted-foreground uppercase">Type</TableHead>
                <TableHead class="px-6 py-4 text-xs font-semibold tracking-wider text-muted-foreground uppercase">Status</TableHead>
                <TableHead class="px-6 py-4 text-right text-xs font-semibold tracking-wider text-muted-foreground uppercase">Aksi</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow
                v-for="item in cpes.data"
                :key="item.id"
                @click="openDrawer(item)"
                class="group cursor-pointer border-b border-border transition-colors hover:bg-muted/50">
                <TableCell class="px-6 py-4 font-semibold text-foreground">
                  <div class="flex items-center gap-3">
                    <div
                      class="flex h-9 w-9 items-center justify-center rounded-lg border border-amber-500/20 bg-amber-500/10 text-amber-500 shadow-sm">
                      <Home class="h-4.5 w-4.5" />
                    </div>
                    <div class="flex flex-col">
                      <span>{{ item.name }}</span>
                      <span class="font-mono text-[10px] text-muted-foreground">{{ item.code }}</span>
                    </div>
                  </div>
                </TableCell>
                <TableCell class="px-6 py-4 font-medium text-muted-foreground"> {{ item.brand }} {{ item.model }} </TableCell>
                <TableCell class="px-6 py-4 font-mono text-xs text-primary">
                  {{ item.ip_address || '-' }}
                </TableCell>
                <TableCell class="px-6 py-4 text-sm">
                  {{ item.type || '-' }}
                </TableCell>
                <TableCell class="px-6 py-4">
                  <DeviceStatusBadge :status="item.status" />
                </TableCell>
                <TableCell class="px-6 py-4 text-right" @click.stop>
<ActionMenu
                    :detail-route="route('cpe.show', item.id)"
                    :edit-route="route('cpe.edit', item.id)"
                    :delete-route="route('cpe.destroy', item.id)"
                    delete-message="Hapus Perangkat"
                    @preview="openDrawer(item)"
                  />
                </TableCell>
              </TableRow>
              <TableRow v-if="cpes.data.length === 0">
                <TableCell colspan="6" class="h-32 text-center text-muted-foreground italic"> No CPEs found in inventory. </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>

      <Pagination :links="cpes.links" />

      <!-- Detail Drawer (Sheet) -->
      <Sheet :open="isDrawerOpen" @update:open="isDrawerOpen = $event">
        <SheetTrigger class="hidden" />
        <SheetContent class="flex h-full flex-col border-none p-0 shadow-2xl sm:max-w-[500px]">
          <SheetHeader class="sr-only">
            <SheetTitle>Detail CPE: {{ selectedCpe?.name }}</SheetTitle>
            <SheetDescription>Koneksi, interface, dan layanan aktif pada perangkat ini.</SheetDescription>
          </SheetHeader>
          <DeviceDetailPreview v-if="selectedCpe" :device="selectedCpe" />
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
