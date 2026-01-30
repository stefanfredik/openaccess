<script setup lang="ts">
  import { Badge } from '@/components/ui/badge'
  import { Button } from '@/components/ui/button'
  import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
  import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
  import AppLayout from '@/layouts/AppLayout.vue'
  import { Head, Link } from '@inertiajs/vue3'
  import { Eye, Pencil, Plus } from 'lucide-vue-next'

  defineProps<{
    splicingPoints: {
      data: Array<any>
    }
  }>()
</script>

<template>
  <Head title="Splicing Points" />

  <AppLayout
    :breadcrumbs="[
      {
        title: 'Splicing Points',
        href: route('passive-device.splicing-point.index'),
      },
    ]">
    <div class="flex flex-col gap-6 p-4 md:p-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">Splicing Points</h1>
          <p class="text-muted-foreground">Manage fiber splicing points in Joint Boxes.</p>
        </div>
        <Button as-child>
          <Link :href="route('passive-device.splicing-point.create')">
            <Plus class="mr-2 h-4 w-4" />
            Add Splicing Point
          </Link>
        </Button>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Splicing Points</CardTitle>
          <CardDescription> List of all registered splicing points. </CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead class="w-[120px]">Code</TableHead>
                <TableHead>Name</TableHead>
                <TableHead>Joint Box</TableHead>
                <TableHead>Tray</TableHead>
                <TableHead>Type</TableHead>
                <TableHead>Status</TableHead>
                <TableHead class="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="item in splicingPoints.data" :key="item.id">
                <TableCell class="font-medium">{{ item.code }}</TableCell>
                <TableCell>{{ item.name }}</TableCell>
                <TableCell>{{ item.joint_box?.name || '-' }}</TableCell>
                <TableCell>{{ item.tray_number || '-' }}</TableCell>
                <TableCell>{{ item.splicing_type || '-' }}</TableCell>
                <TableCell>
                  <Badge :variant="item.status === 'Active' ? 'default' : item.status === 'Damaged' ? 'destructive' : 'secondary'">
                    {{ item.status }}
                  </Badge>
                </TableCell>
                <TableCell class="text-right">
                  <div class="flex justify-end gap-2">
                    <Button variant="ghost" size="icon" as-child title="View Detail">
                      <Link :href="route('passive-device.splicing-point.show', item.id)">
                        <Eye class="h-4 w-4" />
                      </Link>
                    </Button>
                    <Button variant="ghost" size="icon" as-child title="Edit">
                      <Link :href="route('passive-device.splicing-point.edit', item.id)">
                        <Pencil class="h-4 w-4" />
                      </Link>
                    </Button>
                  </div>
                </TableCell>
              </TableRow>
              <TableRow v-if="splicingPoints.data.length === 0">
                <TableCell colspan="7" class="h-24 text-center"> No Splicing Points found. </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
