<script setup lang="ts">
  import { Button } from '@/components/ui/button'
  import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
  import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
  import AppLayout from '@/layouts/AppLayout.vue'
  import { Head, Link } from '@inertiajs/vue3'
  import { Eye, Pencil, Plus } from 'lucide-vue-next'
  // import { index as odpIndex, create as odpCreate, edit as odpEdit, show as odpShow, destroy as odpDestroy } from '@/routes/passive-device/odp';

  defineProps<{
    odps: {
      data: Array<any>
    }
  }>()
</script>

<template>
  <Head title="ODPs" />

  <AppLayout :breadcrumbs="[{ title: 'ODPs', href: route('passive-device.odps.index') }]">
    <div class="flex flex-col gap-6 p-4 md:p-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">ODPs</h1>
          <p class="text-muted-foreground">Manage Optical Distribution Point (ODP) assets.</p>
        </div>
        <Button as-child>
          <Link :href="route('passive-device.odps.create')">
            <Plus class="mr-2 h-4 w-4" />
            Add ODP
          </Link>
        </Button>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>ODPs</CardTitle>
          <CardDescription> List of all ODPs. </CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead class="w-[100px]">Code</TableHead>
                <TableHead>Name</TableHead>
                <TableHead>Area</TableHead>
                <TableHead>Splitter Type</TableHead>
                <TableHead>Ports (Used/Total)</TableHead>
                <TableHead class="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="odp in odps.data" :key="odp.id">
                <TableCell class="font-medium">{{ odp.code || '-' }}</TableCell>
                <TableCell>{{ odp.name }}</TableCell>
                <TableCell>{{ odp.area?.name || '-' }}</TableCell>
                <TableCell>{{ odp.splitter_type || '-' }}</TableCell>
                <TableCell>{{ odp.used_port_count }} / {{ odp.port_count }}</TableCell>
                <TableCell class="text-right">
                  <div class="flex justify-end gap-2">
                    <Button variant="ghost" size="icon" as-child title="View Detail">
                      <Link :href="route('passive-device.odps.show', odp.id)">
                        <Eye class="h-4 w-4" />
                      </Link>
                    </Button>
                    <Button variant="ghost" size="icon" as-child title="Edit">
                      <Link :href="route('passive-device.odps.edit', odp.id)">
                        <Pencil class="h-4 w-4" />
                      </Link>
                    </Button>
                    <DeleteAction :href="route('passive-device.odps.destroy', odp.id)" />
                  </div>
                </TableCell>
              </TableRow>
              <TableRow v-if="odps.data.length === 0">
                <TableCell colspan="6" class="h-24 text-center"> No ODPs found. </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
