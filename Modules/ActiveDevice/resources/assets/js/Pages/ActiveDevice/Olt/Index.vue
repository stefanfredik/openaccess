<script setup lang="ts">
  import { Head, Link, router } from '@inertiajs/vue3'
  import { debounce } from 'lodash'
  import { Cpu, Eye, FileText, MoreVertical, Settings, Trash } from 'lucide-vue-next'
  import { computed, ref, watch } from 'vue'
  import DeviceDetailPreview from '@/../../Modules/ActiveDevice/resources/assets/js/Components/DeviceDetailPreview.vue'
  import DeviceStatusBadge from '@/../../Modules/ActiveDevice/resources/assets/js/Components/DeviceStatusBadge.vue'
  import InventoryHeader from '@/../../Modules/ActiveDevice/resources/assets/js/Components/InventoryHeader.vue'
  import DeleteAction from '@/components/DeleteAction.vue'
  import { Button } from '@/components/ui/button'
  import { Card, CardContent } from '@/components/ui/card'
  import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
  import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet'
  import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
  import AppLayout from '@/layouts/AppLayout.vue'

  const props = defineProps<{
    olts: {
      data: any[]
    }
    areas: any[]
    filters: {
      search?: string
      area_id?: string
    }
  }>()

  const selectedOltId = ref<number | null>(null)
  const isDrawerOpen = ref(false)
  const searchQuery = ref(props.filters.search || '')
  const areaId = ref(props.filters.area_id || 'all')

  const updateFilters = debounce(() => {
    router.get(
      route('active-device.olt.index'),
      {
        search: searchQuery.value,
        area_id: areaId.value,
      },
      {
        preserveState: true,
        preserveScroll: true,
        replace: true,
      },
    )
  }, 300)

  watch([searchQuery, areaId], () => {
    updateFilters()
  })

  const selectedOlt = computed(() => {
    return props.olts.data.find((o: any) => o.id === selectedOltId.value) || null
  })

  const openDrawer = (olt: any) => {
    console.log('Opening drawer for:', olt.name)
    selectedOltId.value = olt.id
    isDrawerOpen.value = true
  }

  const getPortAbbreviation = (name: string) => {
    if (!name) return 'P'
    const match = name.match(/[A-Za-z]/)
    const numMatch = name.match(/\d+/)
    return (match ? match[0].toUpperCase() : 'P') + (numMatch ? numMatch[0] : '')
  }
</script>

<template>
  <Head title="OLT Inventory" />

  <AppLayout
    :breadcrumbs="[
      { title: 'Inventory', href: '#' },
      { title: 'OLT', href: route('active-device.olt.index') },
    ]">
    <div class="flex flex-col gap-6 p-4 md:p-8">
      <!-- Header section -->
      <InventoryHeader
        title="OLT Inventory"
        description="Kelola inventori perangkat OLT dan jalur fiber."
        search-placeholder="Cari IP, Port, atau Nama..."
        add-button-text="Tambah OLT"
        :add-route="route('active-device.olt.create')"
        v-model="searchQuery"
        v-model:area-id="areaId"
        :areas="areas" />

      <!-- Table section -->
      <Card class="overflow-hidden rounded-xl border-none bg-card shadow-sm">
        <div class="flex items-center justify-between border-b border-border bg-card p-6">
          <h2 class="text-lg font-bold text-foreground">Daftar Inventori OLT</h2>
          <div class="flex space-x-2">
            <Button variant="outline" size="sm" class="h-8 text-xs">Filter</Button>
            <Button variant="outline" size="sm" class="h-8 text-xs">Export</Button>
          </div>
        </div>
        <CardContent class="p-0">
          <Table>
            <TableHeader class="bg-muted/50">
              <TableRow class="border-b border-border hover:bg-transparent">
                <TableHead class="px-6 py-4 text-xs font-semibold tracking-wider text-muted-foreground uppercase">Nama Perangkat</TableHead>
                <TableHead class="px-6 py-4 text-xs font-semibold tracking-wider text-muted-foreground uppercase">Brand / Model</TableHead>
                <TableHead class="px-6 py-4 text-xs font-semibold tracking-wider text-muted-foreground uppercase">IP Address</TableHead>
                <TableHead class="px-6 py-4 text-xs font-semibold tracking-wider text-muted-foreground uppercase">Port Aktif</TableHead>
                <TableHead class="px-6 py-4 text-xs font-semibold tracking-wider text-muted-foreground uppercase">Status</TableHead>
                <TableHead class="px-6 py-4 text-right text-xs font-semibold tracking-wider text-muted-foreground uppercase">Aksi</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow
                v-for="olt in olts.data"
                :key="olt.id"
                @click="openDrawer(olt)"
                class="group cursor-pointer border-b border-border transition-colors hover:bg-muted/50">
                <TableCell class="px-6 py-4 font-semibold text-foreground">
                  <div class="flex items-center gap-3">
                    <div
                      class="flex h-9 w-9 items-center justify-center rounded-lg border border-primary/10 bg-primary/5 text-primary shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-foreground">
                      <Cpu class="h-4.5 w-4.5" />
                    </div>
                    <div class="flex flex-col">
                      <span>{{ olt.name }}</span>
                      <span class="font-mono text-[10px] text-muted-foreground">{{ olt.code }}</span>
                    </div>
                  </div>
                </TableCell>
                <TableCell class="px-6 py-4 font-medium text-muted-foreground"> {{ olt.brand }} {{ olt.model }} </TableCell>
                <TableCell class="px-6 py-4 font-mono text-xs text-blue-500 dark:text-blue-400">
                  {{ olt.ip_address }}
                </TableCell>
                <TableCell class="px-6 py-4">
                  <div class="flex -space-x-1">
                    <template v-for="(port, idx) in olt.service_ports?.slice(0, 3)" :key="port.id">
                      <span
                        class="flex h-7 w-7 items-center justify-center rounded border-2 border-background bg-emerald-500/10 text-[9px] font-bold text-emerald-600 shadow-sm dark:text-emerald-400"
                        :title="port.name">
                        {{ getPortAbbreviation(port.name) }}
                      </span>
                    </template>
                    <span
                      v-if="olt.service_ports?.length > 3"
                      class="flex h-7 w-7 items-center justify-center rounded border-2 border-background bg-muted text-[9px] font-bold text-muted-foreground shadow-sm">
                      +{{ olt.service_ports.length - 3 }}
                    </span>
                    <span v-if="!olt.service_ports?.length" class="text-xs text-muted-foreground italic">None</span>
                  </div>
                </TableCell>
                <TableCell class="px-6 py-4">
                  <DeviceStatusBadge :status="olt.status" />
                </TableCell>
                <TableCell class="px-6 py-4 text-right" @click.stop>
                  <div class="flex items-center justify-end gap-1">
                    <Button
                      variant="ghost"
                      size="icon"
                      class="h-8 w-8 text-blue-600 opacity-60 transition-opacity hover:bg-blue-50 hover:opacity-100"
                      @click="openDrawer(olt)"
                      title="Pratinjau Cepat">
                      <Eye class="h-4 w-4" />
                    </Button>

                    <DropdownMenu>
                      <DropdownMenuTrigger as-child>
                        <Button variant="ghost" size="icon" class="h-8 w-8 p-0 opacity-60 transition-opacity hover:opacity-100">
                          <MoreVertical class="h-4 w-4" />
                        </Button>
                      </DropdownMenuTrigger>
                      <DropdownMenuContent align="end" class="w-48">
                        <DropdownMenuItem as-child class="cursor-pointer">
                          <Link :href="route('active-device.olt.show', olt.id)">
                            <FileText class="mr-2 h-4 w-4" />
                            Detail Lengkap
                          </Link>
                        </DropdownMenuItem>
                        <DropdownMenuItem as-child class="cursor-pointer">
                          <Link :href="route('active-device.olt.edit', olt.id)">
                            <Settings class="mr-2 h-4 w-4" />
                            Edit Perangkat
                          </Link>
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <div @click.stop>
                          <DeleteAction
                            :href="route('active-device.olt.destroy', olt.id)"
                            title="Hapus OLT"
                            class="h-auto w-full justify-start px-2 py-1.5 font-normal text-red-600 transition-colors hover:bg-red-50 hover:text-red-700">
                            <template #trigger>
                              <div class="flex items-center">
                                <Trash class="mr-2 h-4 w-4" />
                                Hapus Perangkat
                              </div>
                            </template>
                          </DeleteAction>
                        </div>
                      </DropdownMenuContent>
                    </DropdownMenu>
                  </div>
                </TableCell>
              </TableRow>
              <TableRow v-if="olts.data.length === 0">
                <TableCell colspan="6" class="h-32 text-center text-muted-foreground italic"> No OLTs found in inventory. </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>

      <!-- Detail Drawer (Sheet) -->
      <Sheet :open="isDrawerOpen" @update:open="isDrawerOpen = $event">
        <SheetTrigger class="hidden" />
        <SheetContent class="flex h-full flex-col border-none p-0 shadow-2xl sm:max-w-[500px]">
          <SheetHeader class="sr-only">
            <SheetTitle>Detail OLT: {{ selectedOlt?.name }}</SheetTitle>
            <SheetDescription>Koneksi, interface, dan layanan aktif pada perangkat ini.</SheetDescription>
          </SheetHeader>
          <DeviceDetailPreview v-if="selectedOlt" :device="selectedOlt" />
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
