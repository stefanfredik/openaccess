<script setup lang="ts">
  import { Button } from '@/components/ui/button'
  import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
  import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
  import AppLayout from '@/layouts/AppLayout.vue'
  import { Head, Link } from '@inertiajs/vue3'
  import { Eye, Pencil, Plus } from 'lucide-vue-next'
  // import { index as splitterIndex, create as splitterCreate, edit as splitterEdit, show as splitterShow, destroy as splitterDestroy } from '@/routes/passive-device/splitter';

  defineProps<{
    splitters: {
      data: Array<any>
    }
  }>()
</script>

<template>
  <Head title="Splitters" />

  <AppLayout
    :breadcrumbs="[
      {
        title: 'Splitters',
        href: route('passive-device.splitter.index'),
      },
    ]">
    <div class="flex flex-col gap-6 p-4 md:p-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">Splitters</h1>
          <p class="text-muted-foreground">Manage PLC/FBT Optical Splitters.</p>
        </div>
        <Button as-child>
          <Link :href="route('passive-device.splitter.create')">
            <Plus class="mr-2 h-4 w-4" />
            Add Splitter
          </Link>
        </Button>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Splitters</CardTitle>
          <CardDescription> List of all Splitters. </CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead class="w-[100px]">Code</TableHead>
                <TableHead>Name</TableHead>
                <TableHead>Area</TableHead>
                <TableHead>Ratio</TableHead>
                <TableHead>Loss (dB)</TableHead>
                <TableHead class="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="splitter in splitters.data" :key="splitter.id">
                <TableCell class="font-medium">{{ splitter.code || '-' }}</TableCell>
                <TableCell>{{ splitter.name }}</TableCell>
                <TableCell>{{ splitter.area?.name || '-' }}</TableCell>
                <TableCell>{{ splitter.ratio || '-' }}</TableCell>
                <TableCell>{{ splitter.loss_db || '-' }} dB</TableCell>
                <TableCell class="text-right">
                  <div class="flex justify-end gap-2">
                    <Button variant="ghost" size="icon" as-child title="View Detail">
                      <Link :href="route('passive-device.splitter.show', splitter.id)">
                        <Eye class="h-4 w-4" />
                      </Link>
                    </Button>
                    <Button variant="ghost" size="icon" as-child title="Edit">
                      <Link :href="route('passive-device.splitter.edit', splitter.id)">
                        <Pencil class="h-4 w-4" />
                      </Link>
                    </Button>
                    <DeleteAction :href="route('passive-device.splitter.destroy', splitter.id)" />
                  </div>
                </TableCell>
              </TableRow>
              <TableRow v-if="splitters.data.length === 0">
                <TableCell colspan="6" class="h-24 text-center"> No Splitters found. </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
