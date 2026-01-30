<script setup lang="ts">
  import LocationPicker from '@/components/LocationPicker.vue'
  import { Badge } from '@/components/ui/badge'
  import { Button } from '@/components/ui/button'
  import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
  import AppLayout from '@/layouts/AppLayout.vue'
  import { index as siteIndex, show as siteShow, edit as siteEdit } from '@/routes/site'
  import { Head, Link } from '@inertiajs/vue3'
  import { Pencil } from 'lucide-vue-next'

  defineProps<{
    site: any
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
  <Head :title="site.name" />

  <AppLayout
    :breadcrumbs="[
      { title: 'Sites', href: siteIndex().url },
      { title: site.name, href: siteShow({ site: site.id }).url },
    ]">
    <div class="flex flex-col gap-6 p-4 md:p-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">{{ site.name }}</h1>
          <p class="text-muted-foreground">{{ site.type }}</p>
        </div>
        <Button as-child>
          <Link :href="siteEdit({ site: site.id }).url">
            <Pencil class="mr-2 h-4 w-4" />
            Edit Site
          </Link>
        </Button>
      </div>

      <div class="grid gap-6 md:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Details</CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="grid grid-cols-2 gap-2 text-sm">
              <div class="text-muted-foreground">Code</div>
              <div>{{ site.code || '-' }}</div>

              <div class="text-muted-foreground">Type</div>
              <div>
                <Badge variant="outline">{{ site.type }}</Badge>
              </div>

              <div class="text-muted-foreground">Status</div>
              <div>
                <Badge :variant="getUiStatus(site.status)">{{ site.status }}</Badge>
              </div>

              <div class="text-muted-foreground">Area</div>
              <div>{{ site.area?.name || '-' }}</div>

              <div class="text-muted-foreground">Description</div>
              <div>{{ site.description || '-' }}</div>

              <div class="col-span-2 mt-2 font-semibold">Power Infrastructure</div>
              <div class="text-muted-foreground">Electrical Capacity</div>
              <div>{{ site.electrical_capacity ? site.electrical_capacity + ' VA' : '-' }}</div>

              <div class="text-muted-foreground">Power Source</div>
              <div>{{ site.power_source || '-' }}</div>

              <div class="text-muted-foreground">Backup Power</div>
              <div>
                <Badge :variant="site.has_backup_power ? 'default' : 'secondary'">
                  {{ site.has_backup_power ? 'Yes' : 'No' }}
                </Badge>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Location</CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <!-- Readonly Map -->
            <div class="pointer-events-none">
              <LocationPicker :latitude="site.latitude" :longitude="site.longitude" />
            </div>
            <div class="grid grid-cols-2 gap-2 text-sm">
              <div class="text-muted-foreground">Latitude</div>
              <div>{{ site.latitude || '-' }}</div>

              <div class="text-muted-foreground">Longitude</div>
              <div>{{ site.longitude || '-' }}</div>

              <div class="text-muted-foreground">Address</div>
              <div>{{ site.address || '-' }}</div>
            </div>
          </CardContent>
        </Card>
      </div>

      <Card v-if="site.photos && site.photos.length > 0">
        <CardHeader>
          <CardTitle>Photos</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div v-for="photo in site.photos" :key="photo.id" class="aspect-square rounded-md overflow-hidden border">
              <a :href="'/storage/' + photo.path" target="_blank">
                <img :src="'/storage/' + photo.path" class="object-cover w-full h-full hover:scale-105 transition-transform duration-300" />
              </a>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
