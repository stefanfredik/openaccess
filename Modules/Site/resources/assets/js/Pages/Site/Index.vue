<script setup lang="ts">
  import { Badge } from '@/components/ui/badge'
  import { Button } from '@/components/ui/button'
  import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
  import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
  import AppLayout from '@/layouts/AppLayout.vue'
  import { index as siteIndex, create as siteCreate, edit as siteEdit, destroy as siteDestroy } from '@/routes/site'
  import { Head, Link } from '@inertiajs/vue3'
  import { Plus, Pencil, Trash2 } from 'lucide-vue-next'

  defineProps<{
    sites: Array<any>
  }>()

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
  <Head title="Sites" />

  <AppLayout :breadcrumbs="[{ title: 'Sites', href: siteIndex().url }]">
    <div class="flex flex-col gap-6 p-4 md:p-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">Sites</h1>
          <p class="text-muted-foreground">Manage physical infrastructure sites (Towers, POPs, etc).</p>
        </div>
        <Button as-child>
          <Link :href="siteCreate().url">
            <Plus class="mr-2 h-4 w-4" />
            Add Site
          </Link>
        </Button>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Sites</CardTitle>
          <CardDescription> List of all sites. </CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead class="w-[100px]">Code</TableHead>
                <TableHead>Name</TableHead>
                <TableHead>Type</TableHead>
                <TableHead>Area</TableHead>
                <TableHead>Status</TableHead>
                <TableHead class="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="site in sites" :key="site.id">
                <TableCell class="font-medium">{{ site.code || '-' }}</TableCell>
                <TableCell>{{ site.name }}</TableCell>
                <TableCell>
                  <Badge variant="outline" class="capitalize">
                    {{ site.type }}
                  </Badge>
                </TableCell>
                <TableCell>{{ site.area?.name || '-' }}</TableCell>
                <TableCell>
                  <Badge :variant="getUiStatus(site.status)">{{ site.status }}</Badge>
                </TableCell>
                <TableCell class="text-right">
                  <div class="flex justify-end gap-2">
                    <Button variant="ghost" size="icon" as-child>
                      <Link :href="siteEdit({ site: site.id }).url">
                        <Pencil class="h-4 w-4" />
                      </Link>
                    </Button>
                    <Link
                      :href="siteDestroy({ site: site.id }).url"
                      method="delete"
                      as="button"
                      class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-10 w-10 text-destructive hover:text-destructive">
                      <Trash2 class="h-4 w-4" />
                    </Link>
                  </div>
                </TableCell>
              </TableRow>
              <TableRow v-if="sites.length === 0">
                <TableCell colspan="6" class="h-24 text-center"> No sites found. </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
