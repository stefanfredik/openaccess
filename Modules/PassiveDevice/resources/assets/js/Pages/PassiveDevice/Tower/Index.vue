<script setup lang="ts">
  import { Button } from '@/components/ui/button'
  import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
  import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
  import AppLayout from '@/layouts/AppLayout.vue'
  import { Head, Link } from '@inertiajs/vue3'
  import { Eye, Pencil, Plus } from 'lucide-vue-next'
  // import { index as towerIndex, create as towerCreate, edit as towerEdit, show as towerShow, destroy as towerDestroy } from '@/routes/passive-device/tower';

  defineProps<{
    towers: {
      data: Array<any>
    }
  }>()
</script>

<template>
  <Head title="Towers" />

  <AppLayout :breadcrumbs="[{ title: 'Towers', href: route('passive-device.tower.index') }]">
    <div class="flex flex-col gap-6 p-4 md:p-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">Towers</h1>
          <p class="text-muted-foreground">Manage wireless tower/pole infrastructure.</p>
        </div>
        <Button as-child>
          <Link :href="route('passive-device.tower.create')">
            <Plus class="mr-2 h-4 w-4" />
            Add Tower
          </Link>
        </Button>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Towers</CardTitle>
          <CardDescription> List of all Towers. </CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead class="w-[100px]">Code</TableHead>
                <TableHead>Name</TableHead>
                <TableHead>Area</TableHead>
                <TableHead>Height</TableHead>
                <TableHead>Segments</TableHead>
                <TableHead class="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="tower in towers.data" :key="tower.id">
                <TableCell class="font-medium">{{ tower.code || '-' }}</TableCell>
                <TableCell>{{ tower.name }}</TableCell>
                <TableCell>{{ tower.area?.name || '-' }}</TableCell>
                <TableCell>{{ tower.height }} m</TableCell>
                <TableCell>{{ tower.segment_count || 1 }} Segments</TableCell>
                <TableCell class="text-right">
                  <div class="flex justify-end gap-2">
                    <Button variant="ghost" size="icon" as-child title="View Detail">
                      <Link :href="route('passive-device.tower.show', tower.id)">
                        <Eye class="h-4 w-4" />
                      </Link>
                    </Button>
                    <Button variant="ghost" size="icon" as-child title="Edit">
                      <Link :href="route('passive-device.tower.edit', tower.id)">
                        <Pencil class="h-4 w-4" />
                      </Link>
                    </Button>
                    <DeleteAction :href="route('passive-device.tower.destroy', tower.id)" />
                  </div>
                </TableCell>
              </TableRow>
              <TableRow v-if="towers.data.length === 0">
                <TableCell colspan="6" class="h-24 text-center"> No Towers found. </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
