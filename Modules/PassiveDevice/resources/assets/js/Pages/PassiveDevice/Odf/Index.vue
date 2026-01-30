<script setup lang="ts">
  import { Head, Link, router } from '@inertiajs/vue3'
  import { debounce } from 'lodash'
  import { Eye, Pencil, Plus, MapPin } from 'lucide-vue-next'
  import { ref, watch } from 'vue'
  import AppLayout from '@/layouts/AppLayout.vue'
  import DeleteAction from '@/components/DeleteAction.vue'
  import Pagination from '@/components/Pagination.vue'
  import ResourceHeader from '@/components/ResourceHeader.vue'
  import { Button } from '@/components/ui/button'
  import { Card, CardContent } from '@/components/ui/card'
  import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
  import type { InfrastructureArea, Odf, PaginatedData } from '@/types'

  const props = defineProps<{
    odfs: PaginatedData<Odf>
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

  const updateFilters = debounce(() => {
    router.get(
      route('passive-device.odf.index'),
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
</script>

<template>
  <Head title="ODF Inventory" />

  <AppLayout :breadcrumbs="[{ title: 'Inventory', href: '#' }, { title: 'ODF', href: route('passive-device.odf.index') }]">
    <div class="flex flex-col gap-6 p-4 md:p-8">
      <ResourceHeader
        title="ODF Inventory"
        description="Kelola inventori Optical Distribution Frame (ODF)."
        search-placeholder="Cari ODF..."
        add-button-text="Tambah ODF"
        :add-route="route('passive-device.odf.create')"
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
        <div class="flex items-center justify-between border-b border-border bg-card p-6">
          <h2 class="text-lg font-bold text-foreground">Daftar ODF</h2>
        </div>
        <CardContent class="p-0">
          <Table>
            <TableHeader class="bg-muted/50">
              <TableRow class="border-b border-border hover:bg-transparent">
                <TableHead class="px-6 py-4 text-xs font-semibold tracking-wider text-muted-foreground uppercase">Kode</TableHead>
                <TableHead class="px-6 py-4 text-xs font-semibold tracking-wider text-muted-foreground uppercase">Nama ODF</TableHead>
                <TableHead class="px-6 py-4 text-xs font-semibold tracking-wider text-muted-foreground uppercase">Wilayah</TableHead>
                <TableHead class="px-6 py-4 text-xs font-semibold tracking-wider text-muted-foreground uppercase">Kapasitas Port</TableHead>
                <TableHead class="px-6 py-4 text-right text-xs font-semibold tracking-wider text-muted-foreground uppercase">Aksi</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow
                v-for="odf in odfs.data"
                :key="odf.id"
                class="group border-b border-border transition-colors hover:bg-muted/50">
                <TableCell class="px-6 py-4 font-mono text-xs">
                  {{ odf.code || '-' }}
                </TableCell>
                <TableCell class="px-6 py-4 font-semibold text-foreground">
                  {{ odf.name }}
                </TableCell>
                <TableCell class="px-6 py-4 text-sm text-muted-foreground">
                  {{ odf.area?.name || '-' }}
                </TableCell>
                <TableCell class="px-6 py-4 text-sm"> {{ odf.port_count }} Ports </TableCell>
                <TableCell class="px-6 py-4 text-right">
                  <div class="flex justify-end gap-2">
                    <Button variant="ghost" size="icon" as-child title="Lihat Detail">
                      <Link :href="route('passive-device.odf.show', odf.id)">
                        <Eye class="h-4 w-4" />
                      </Link>
                    </Button>
                    <Button variant="ghost" size="icon" as-child title="Edit">
                      <Link :href="route('passive-device.odf.edit', odf.id)">
                        <Pencil class="h-4 w-4" />
                      </Link>
                    </Button>
                    <DeleteAction :href="route('passive-device.odf.destroy', odf.id)" />
                  </div>
                </TableCell>
              </TableRow>
              <TableRow v-if="odfs.data.length === 0">
                <TableCell colspan="5" class="h-32 text-center text-muted-foreground italic"> Tidak ada data ODF ditemukan. </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>

      <Pagination :links="odfs.links" />
    </div>
  </AppLayout>
</template>
