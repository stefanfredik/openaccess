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
  import type { InfrastructureArea, Pole, PaginatedData } from '@/types'

  const props = defineProps<{
    poles: PaginatedData<Pole>
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
      route('passive-device.pole.index'),
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
  <Head title="Pole Inventory" />

  <AppLayout :breadcrumbs="[{ title: 'Inventory', href: '#' }, { title: 'Pole', href: route('passive-device.pole.index') }]">
    <div class="flex flex-col gap-6 p-4 md:p-8">
      <ResourceHeader
        title="Pole Inventory"
        description="Kelola inventori tiang (pole) infrastruktur."
        search-placeholder="Cari tiang..."
        add-button-text="Tambah Tiang"
        :add-route="route('passive-device.pole.create')"
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
          <h2 class="text-lg font-bold text-foreground">Daftar Tiang</h2>
        </div>
        <CardContent class="p-0">
          <Table>
            <TableHeader class="bg-muted/50">
              <TableRow class="border-b border-border hover:bg-transparent">
                <TableHead class="px-6 py-4 text-xs font-semibold tracking-wider text-muted-foreground uppercase">Kode</TableHead>
                <TableHead class="px-6 py-4 text-xs font-semibold tracking-wider text-muted-foreground uppercase">Nama Tiang</TableHead>
                <TableHead class="px-6 py-4 text-xs font-semibold tracking-wider text-muted-foreground uppercase">Wilayah</TableHead>
                <TableHead class="px-6 py-4 text-xs font-semibold tracking-wider text-muted-foreground uppercase">Material / Kepemilikan</TableHead>
                <TableHead class="px-6 py-4 text-xs font-semibold tracking-wider text-muted-foreground uppercase">Tinggi</TableHead>
                <TableHead class="px-6 py-4 text-right text-xs font-semibold tracking-wider text-muted-foreground uppercase">Aksi</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow
                v-for="pole in poles.data"
                :key="pole.id"
                class="group border-b border-border transition-colors hover:bg-muted/50">
                <TableCell class="px-6 py-4 font-mono text-xs">
                  {{ pole.code || '-' }}
                </TableCell>
                <TableCell class="px-6 py-4 font-semibold text-foreground">
                  {{ pole.name }}
                </TableCell>
                <TableCell class="px-6 py-4 text-sm text-muted-foreground">
                  {{ pole.area?.name || '-' }}
                </TableCell>
                <TableCell class="px-6 py-4 text-sm">
                  <div class="flex flex-col">
                    <span>{{ pole.material }}</span>
                    <span class="text-xs text-muted-foreground">{{ pole.ownership }}</span>
                  </div>
                </TableCell>
                <TableCell class="px-6 py-4 text-sm"> {{ pole.height }} m </TableCell>
                <TableCell class="px-6 py-4 text-right">
                  <div class="flex justify-end gap-2">
                    <Button variant="ghost" size="icon" as-child title="Lihat Detail">
                      <Link :href="route('passive-device.pole.show', pole.id)">
                        <Eye class="h-4 w-4" />
                      </Link>
                    </Button>
                    <Button variant="ghost" size="icon" as-child title="Edit">
                      <Link :href="route('passive-device.pole.edit', pole.id)">
                        <Pencil class="h-4 w-4" />
                      </Link>
                    </Button>
                    <DeleteAction :href="route('passive-device.pole.destroy', pole.id)" />
                  </div>
                </TableCell>
              </TableRow>
              <TableRow v-if="poles.data.length === 0">
                <TableCell colspan="6" class="h-32 text-center text-muted-foreground italic"> Tidak ada data tiang ditemukan. </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>

      <Pagination :links="poles.links" />
    </div>
  </AppLayout>
</template>
