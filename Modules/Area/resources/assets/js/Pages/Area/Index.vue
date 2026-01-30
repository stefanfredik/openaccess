<script setup lang="ts">
  import DeleteAction from '@/components/DeleteAction.vue'
  import EditAction from '@/components/EditAction.vue'
  import ResourceHeader from '@/components/ResourceHeader.vue'
  import ShowAction from '@/components/ShowAction.vue'
  import { Badge } from '@/components/ui/badge'
  import { Card, CardContent } from '@/components/ui/card'
  import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
  import AppLayout from '@/layouts/AppLayout.vue'
  import Pagination from '@/components/Pagination.vue'
  import { create as areaCreate, destroy as areaDestroy, edit as areaEdit, index as areaIndex, show as areaShow } from '@/routes/area'
  import { Head, router } from '@inertiajs/vue3'
  import { debounce } from 'lodash'
  import { MapPin } from 'lucide-vue-next'
  import { ref, watch } from 'vue'
  import type { PaginatedData } from '@/types'

  const props = defineProps<{
    areas: PaginatedData<any>
    filters: any
  }>()

  const searchQuery = ref(props.filters.search || '')
  const filters = ref({
    type: props.filters.type || 'all',
  })

  const typeOptions = [
    { label: 'Region', value: 'region' },
    { label: 'Area', value: 'area' },
    { label: 'Sub-Area', value: 'subarea' },
    { label: 'POP Location', value: 'pop_location' },
  ]

  const updateFilters = debounce(() => {
    router.get(
      areaIndex().url,
      {
        search: searchQuery.value,
        type: filters.value.type === 'all' ? undefined : filters.value.type,
      },
      {
        preserveState: true,
        replace: true,
      },
    )
  }, 300)

  watch([searchQuery, filters], () => {
    updateFilters()
  }, { deep: true })
</script>

<template>
  <Head title="Wilayah Jaringan" />

  <AppLayout :breadcrumbs="[{ title: 'Wilayah', href: areaIndex().url }]">
    <div class="mb-10 flex flex-col gap-6 p-4 md:p-6 text-foreground">
      <ResourceHeader
        title="Wilayah"
        description="Wilayah Infrastruktur"
        search-placeholder="Cari wilayah..."
        add-button-text="Tambah Wilayah"
        :add-route="areaCreate().url"
        v-model="searchQuery"
        v-model:filters="filters"
        :filter-configs="[
          {
            key: 'type',
            label: 'Tipe',
            placeholder: 'Filter Tipe',
            options: typeOptions,
          },
        ]" />

      <Card class="overflow-hidden border-none shadow-sm">
        <CardContent class="p-0 border-t">
          <div class="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead class="w-[150px]">Kode</TableHead>
                  <TableHead class="min-w-[200px]">Nama Wilayah</TableHead>
                  <TableHead class="w-[150px]">Tipe</TableHead>
                  <TableHead>Keterangan</TableHead>
                  <TableHead class="w-[150px] text-right">Aksi</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="area in areas.data" :key="area.id">
                  <TableCell class="font-medium whitespace-nowrap">
                    <div class="flex items-center gap-2">
                      <MapPin class="h-4 w-4 text-primary/70" />
                      {{ area.code || '-' }}
                    </div>
                  </TableCell>
                  <TableCell class="font-bold whitespace-nowrap">{{ area.name }}</TableCell>
                  <TableCell>
                    <Badge variant="outline" class="rounded-md px-3 py-0.5 whitespace-nowrap capitalize">
                      {{ area.type.replace('_', ' ') }}
                    </Badge>
                  </TableCell>
                  <TableCell class="max-w-[300px] truncate text-muted-foreground">{{ area.description || '-' }}</TableCell>
                  <TableCell class="text-right">
                    <div class="flex justify-end gap-2">
                      <ShowAction :href="areaShow({ area: area.id }).url" title="Detail Wilayah" />
                      <EditAction :href="areaEdit({ area: area.id }).url" title="Ubah Wilayah" />
                      <DeleteAction
                        :href="
                          areaDestroy({
                            area: area.id,
                          }).url
                        " />
                    </div>
                  </TableCell>
                </TableRow>
                <TableRow v-if="areas.data.length === 0">
                  <TableCell colspan="5" class="h-24 text-center"> No areas found. </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </CardContent>
      </Card>

      <Pagination :links="areas.links" />
    </div>
  </AppLayout>
</template>
