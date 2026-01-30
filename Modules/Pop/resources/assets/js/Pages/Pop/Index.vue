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
  import { create as popCreate, destroy as popDestroy, edit as popEdit, index as popIndex, show as popShow } from '@/routes/pop'
  import { Head, router } from '@inertiajs/vue3'
  import { debounce } from 'lodash'
  import { MapPin } from 'lucide-vue-next'
  import { ref, watch } from 'vue'
  import type { PaginatedData } from '@/types'

  const props = defineProps<{
    pops: PaginatedData<any>
    filters: any
  }>()

  const searchQuery = ref(props.filters.search || '')
  const filters = ref({
    type: props.filters.type || 'all',
  })

  const statusOptions = [
    { label: 'Active', value: 'Active' },
    { label: 'Inactive', value: 'Inactive' },
    { label: 'Planned', value: 'Planned' },
  ]

  const updateFilters = debounce(() => {
    router.get(
      popIndex().url,
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

  const getUiStatus = (status: string) => {
    switch (status) {
      case 'Active':
        return 'default'
      case 'Inactive':
        return 'destructive'
      case 'Planned':
        return 'secondary'
      default:
        return 'outline'
    }
  }
</script>

<template>
  <Head title="Data POP" />

  <AppLayout :breadcrumbs="[{ title: 'POP', href: popIndex().url }]">
    <div class="mb-10 flex flex-col gap-6 p-4 md:p-6 text-foreground">
      <ResourceHeader
        title="Data POP"
        description="Kelola infrastruktur Point of Presence Anda."
        search-placeholder="Cari POP..."
        add-button-text="Tambah POP"
        :add-route="popCreate().url"
        v-model="searchQuery"
        v-model:filters="filters"
        :filter-configs="[
          {
            key: 'type',
            label: 'Status',
            placeholder: 'Filter Status',
            options: statusOptions,
          },
        ]" />

      <Card class="overflow-hidden border-none shadow-sm">
        <CardContent class="p-0 border-t">
          <div class="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead class="w-[150px]">Kode</TableHead>
                  <TableHead class="min-w-[200px]">Nama POP</TableHead>
                  <TableHead>Area</TableHead>
                  <TableHead>Power Source</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead class="w-[150px] text-right">Aksi</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="pop in pops.data" :key="pop.id">
                  <TableCell class="font-medium whitespace-nowrap">
                    {{ pop.code || '-' }}
                  </TableCell>
                  <TableCell class="font-bold whitespace-nowrap">
                    <div class="flex items-center gap-3">
                      <div
                        class="flex h-9 w-9 items-center justify-center rounded-lg border border-red-100/50 bg-red-50 text-red-600 shadow-sm dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-400">
                        <MapPin class="h-4.5 w-4.5" />
                      </div>
                      <span>{{ pop.name }}</span>
                    </div>
                  </TableCell>
                  <TableCell class="whitespace-nowrap">{{ pop.area?.name || '-' }}</TableCell>
                  <TableCell>
                    <div class="flex flex-col">
                      <span class="font-medium">{{ pop.power_source }}</span>
                      <span class="text-xs text-muted-foreground">{{ pop.electrical_capacity }} VA</span>
                    </div>
                  </TableCell>
                  <TableCell>
                    <Badge :variant="getUiStatus(pop.status)" class="rounded-md px-3 py-0.5 shadow-sm">
                      {{ pop.status }}
                    </Badge>
                  </TableCell>
                  <TableCell class="text-right">
                    <div class="flex justify-end gap-2">
                      <ShowAction :href="popShow({ pop: pop.id }).url" title="Detail POP" />
                      <EditAction :href="popEdit({ pop: pop.id }).url" title="Ubah POP" />
                      <DeleteAction :href="popDestroy({ pop: pop.id }).url" />
                    </div>
                  </TableCell>
                </TableRow>
                <TableRow v-if="pops.data.length === 0">
                  <TableCell colspan="6" class="h-24 text-center text-muted-foreground"> Tidak ada data POP ditemukan. </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </CardContent>
      </Card>

      <Pagination :links="pops.links" />
    </div>
  </AppLayout>
</template>
