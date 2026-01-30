<script setup lang="ts">
  import { Button } from '@/components/ui/button'
  import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
  import { Input } from '@/components/ui/input'
  import { Label } from '@/components/ui/label'
  import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
  import { Switch } from '@/components/ui/switch'
  import { Textarea } from '@/components/ui/textarea'
  import AppLayout from '@/layouts/AppLayout.vue'
  import { Head, Link, useForm } from '@inertiajs/vue3'
  // import { index as jointBoxIndex, store as jointBoxStore } from '@/routes/passive-device/joint-box';
  import { onMounted } from 'vue'

  defineProps<{
    areas: Array<any>
  }>()

  const form = useForm({
    infrastructure_area_id: '',
    code: '',
    name: '',
    core_capacity: 0,
    input_count: 0,
    output_count: 0,
    brand: '',
    model: '',
    longitude: '',
    latitude: '',
    description: '',
    is_active: true,
    installed_at: '',
  })

  const submit = () => {
    form.post(route('passive-device.joint-box.store'))
  }

  onMounted(() => {
    const params = new URLSearchParams(window.location.search)
    const lat = params.get('lat')
    const lng = params.get('lng')
    const areaId = params.get('area_id')

    if (lat) form.latitude = lat
    if (lng) form.longitude = lng
    if (areaId) form.infrastructure_area_id = areaId
  })
</script>

<template>
  <Head title="Add Joint Box" />

  <AppLayout
    :breadcrumbs="[
      {
        title: 'Joint Boxes',
        href: route('passive-device.joint-box.index'),
      },
      { title: 'Add Joint Box', href: '#' },
    ]">
    <div class="mx-auto flex max-w-4xl flex-col gap-6 p-4 md:p-6 lg:p-8">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">Add Joint Box</h1>
          <p class="text-muted-foreground">Register a new Splice Enclosure asset.</p>
        </div>
      </div>

      <form @submit.prevent="submit">
        <Card>
          <CardHeader>
            <CardTitle>Joint Box Information</CardTitle>
            <CardDescription> Capacity and technical details of the splice enclosure. </CardDescription>
          </CardHeader>
          <CardContent class="grid gap-6">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div class="space-y-2">
                <Label for="area">Infrastructure Area</Label>
                <Select v-model="form.infrastructure_area_id">
                  <SelectTrigger
                    :class="{
                      'border-destructive': form.errors.infrastructure_area_id,
                    }">
                    <SelectValue placeholder="Select Area" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="area in areas" :key="area.id" :value="area.id.toString()">
                      {{ area.name }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                <p v-if="form.errors.infrastructure_area_id" class="text-sm text-destructive">
                  {{ form.errors.infrastructure_area_id }}
                </p>
              </div>

              <div class="space-y-2">
                <Label for="code">Code</Label>
                <Input
                  id="code"
                  v-model="form.code"
                  :class="{
                    'border-destructive': form.errors.code,
                  }"
                  placeholder="JB-XYZ" />
                <p v-if="form.errors.code" class="text-sm text-destructive">
                  {{ form.errors.code }}
                </p>
              </div>
            </div>

            <div class="space-y-2">
              <Label for="name">Name</Label>
              <Input
                id="name"
                v-model="form.name"
                :class="{
                  'border-destructive': form.errors.name,
                }"
                placeholder="Joint Box Intersection" />
              <p v-if="form.errors.name" class="text-sm text-destructive">
                {{ form.errors.name }}
              </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
              <div class="space-y-2">
                <Label for="core_capacity">Core Capacity</Label>
                <Input
                  id="core_capacity"
                  type="number"
                  v-model="form.core_capacity"
                  :class="{
                    'border-destructive': form.errors.core_capacity,
                  }" />
                <p v-if="form.errors.core_capacity" class="text-sm text-destructive">
                  {{ form.errors.core_capacity }}
                </p>
              </div>
              <div class="space-y-2">
                <Label for="input_count">Input Count</Label>
                <Input
                  id="input_count"
                  type="number"
                  v-model="form.input_count"
                  :class="{
                    'border-destructive': form.errors.input_count,
                  }" />
                <p v-if="form.errors.input_count" class="text-sm text-destructive">
                  {{ form.errors.input_count }}
                </p>
              </div>
              <div class="space-y-2">
                <Label for="output_count">Output Count</Label>
                <Input
                  id="output_count"
                  type="number"
                  v-model="form.output_count"
                  :class="{
                    'border-destructive': form.errors.output_count,
                  }" />
                <p v-if="form.errors.output_count" class="text-sm text-destructive">
                  {{ form.errors.output_count }}
                </p>
              </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div class="space-y-2">
                <Label for="brand">Brand</Label>
                <Input
                  id="brand"
                  v-model="form.brand"
                  :class="{
                    'border-destructive': form.errors.brand,
                  }" />
                <p v-if="form.errors.brand" class="text-sm text-destructive">
                  {{ form.errors.brand }}
                </p>
              </div>
              <div class="space-y-2">
                <Label for="model">Model</Label>
                <Input
                  id="model"
                  v-model="form.model"
                  :class="{
                    'border-destructive': form.errors.model,
                  }" />
                <p v-if="form.errors.model" class="text-sm text-destructive">
                  {{ form.errors.model }}
                </p>
              </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div class="space-y-2">
                <Label for="longitude">Longitude</Label>
                <Input
                  id="longitude"
                  v-model="form.longitude"
                  :class="{
                    'border-destructive': form.errors.longitude,
                  }" />
                <p v-if="form.errors.longitude" class="text-sm text-destructive">
                  {{ form.errors.longitude }}
                </p>
              </div>
              <div class="space-y-2">
                <Label for="latitude">Latitude</Label>
                <Input
                  id="latitude"
                  v-model="form.latitude"
                  :class="{
                    'border-destructive': form.errors.latitude,
                  }" />
                <p v-if="form.errors.latitude" class="text-sm text-destructive">
                  {{ form.errors.latitude }}
                </p>
              </div>
            </div>

            <div class="space-y-2">
              <Label for="installed_at">Installed At</Label>
              <Input
                id="installed_at"
                type="date"
                v-model="form.installed_at"
                :class="{
                  'border-destructive': form.errors.installed_at,
                }" />
              <p v-if="form.errors.installed_at" class="text-sm text-destructive">
                {{ form.errors.installed_at }}
              </p>
            </div>

            <div class="space-y-2">
              <Label for="description">Description</Label>
              <Textarea
                id="description"
                v-model="form.description"
                :class="{
                  'border-destructive': form.errors.description,
                }" />
              <p v-if="form.errors.description" class="text-sm text-destructive">
                {{ form.errors.description }}
              </p>
            </div>

            <div class="flex items-center space-x-2">
              <Switch id="is_active" v-model:checked="form.is_active" />
              <Label for="is_active">Active Status</Label>
            </div>
          </CardContent>
          <CardFooter class="mt-6 flex justify-end gap-2 border-t p-6">
            <Button variant="outline" as-child>
              <Link :href="route('passive-device.joint-box.index')">Cancel</Link>
            </Button>
            <Button type="submit" :disabled="form.processing">
              {{ form.processing ? 'Saving...' : 'Save Joint Box' }}
            </Button>
          </CardFooter>
        </Card>
      </form>
    </div>
  </AppLayout>
</template>
