<script setup lang="ts">
  import { Button } from '@/components/ui/button'
  import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
  import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
  import AppLayout from '@/layouts/AppLayout.vue'
  import { Head, Link } from '@inertiajs/vue3'
  import { Eye, Pencil, Plus } from 'lucide-vue-next'
  // import { index as cableIndex, create as cableCreate, edit as cableEdit, show as cableShow, destroy as cableDestroy } from '@/routes/passive-device/cable';

  defineProps<{
    cables: {
      data: Array<any>
    }
  }>()
</script>

<template>
  <Head title="Cables" />

  <AppLayout :breadcrumbs="[{ title: 'Cables', href: route('passive-device.cable.index') }]">
    <div class="flex flex-col gap-6 p-4 md:p-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">Cables</h1>
          <p class="text-muted-foreground">Manage Fiber Optic Cable assets.</p>
        </div>
        <Button as-child>
          <Link :href="route('passive-device.cable.create')">
            <Plus class="mr-2 h-4 w-4" />
            Add Cable
          </Link>
        </Button>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Cables</CardTitle>
          <CardDescription> List of all Cables. </CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead class="w-[100px]">Code</TableHead>
                <TableHead>Name</TableHead>
                <TableHead>Area</TableHead>
                <TableHead>Type / Cores</TableHead>
                <TableHead>Length</TableHead>
                <TableHead class="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="cable in cables.data" :key="cable.id">
                <TableCell class="font-medium">{{ cable.code || '-' }}</TableCell>
                <TableCell>{{ cable.name }}</TableCell>
                <TableCell>{{ cable.area?.name || '-' }}</TableCell>
                <TableCell>
                  <div class="flex flex-col">
                    <span>{{ cable.type }}</span>
                    <span class="text-xs text-muted-foreground">{{ cable.core_count }} Cores</span>
                  </div>
                </TableCell>
                <TableCell>{{ cable.length }} m</TableCell>
                <TableCell class="text-right">
                  <div class="flex justify-end gap-2">
                    <Button variant="ghost" size="icon" as-child title="View Detail">
                      <Link :href="route('passive-device.cable.show', cable.id)">
                        <Eye class="h-4 w-4" />
                      </Link>
                    </Button>
                    <Button variant="ghost" size="icon" as-child title="Edit">
                      <Link :href="route('passive-device.cable.edit', cable.id)">
                        <Pencil class="h-4 w-4" />
                      </Link>
                    </Button>
                    <DeleteAction :href="route('passive-device.cable.destroy', cable.id)" />
                  </div>
                </TableCell>
              </TableRow>
              <TableRow v-if="cables.data.length === 0">
                <TableCell colspan="6" class="h-24 text-center"> No Cables found. </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
