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
  // import { index as odpIndex, update as odpUpdate } from '@/routes/passive-device/odp';

  const props = defineProps<{
    odp: any
    areas: Array<any>
  }>()

  const form = useForm({
    infrastructure_area_id: props.odp.infrastructure_area_id.toString(),
    code: props.odp.code,
    name: props.odp.name,
    port_count: props.odp.port_count,
    used_port_count: props.odp.used_port_count,
    splitter_type: props.odp.splitter_type || '',
    brand: props.odp.brand || '',
    model: props.odp.model || '',
    longitude: props.odp.longitude || '',
    latitude: props.odp.latitude || '',
    description: props.odp.description || '',
    is_active: !!props.odp.is_active,
    installed_at: props.odp.installed_at || '',
  })

  const submit = () => {
    form.put(route('passive-device.odps.update', props.odp.id))
  }
</script>

<template>
  <Head title="Edit ODP" />

  <AppLayout
    :breadcrumbs="[
      { title: 'ODPs', href: route('passive-device.odps.index') },
      { title: 'Edit ODP', href: '#' },
    ]">
    <div class="mx-auto flex max-w-4xl flex-col gap-6 p-4 md:p-6 lg:p-8">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">Edit ODP</h1>
          <p class="text-muted-foreground">Update Optical Distribution Point device details.</p>
        </div>
      </div>

      <form @submit.prevent="submit">
        <Card>
          <CardHeader>
            <CardTitle>ODP Information</CardTitle>
            <CardDescription> Update specifications for {{ odp.name }}. </CardDescription>
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
                  }" />
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
                }" />
              <p v-if="form.errors.name" class="text-sm text-destructive">
                {{ form.errors.name }}
              </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div class="space-y-2">
                <Label for="port_count">Port Count</Label>
                <Input
                  id="port_count"
                  type="number"
                  v-model="form.port_count"
                  :class="{
                    'border-destructive': form.errors.port_count,
                  }" />
                <p v-if="form.errors.port_count" class="text-sm text-destructive">
                  {{ form.errors.port_count }}
                </p>
              </div>
              <div class="space-y-2">
                <Label for="used_port_count">Used Port Count</Label>
                <Input
                  id="used_port_count"
                  type="number"
                  v-model="form.used_port_count"
                  :class="{
                    'border-destructive': form.errors.used_port_count,
                  }" />
                <p v-if="form.errors.used_port_count" class="text-sm text-destructive">
                  {{ form.errors.used_port_count }}
                </p>
              </div>
            </div>

            <div class="space-y-2">
              <Label for="splitter_type">Splitter Type</Label>
              <Input
                id="splitter_type"
                v-model="form.splitter_type"
                :class="{
                  'border-destructive': form.errors.splitter_type,
                }" />
              <p v-if="form.errors.splitter_type" class="text-sm text-destructive">
                {{ form.errors.splitter_type }}
              </p>
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
              <Link :href="route('passive-device.odps.index')">Cancel</Link>
            </Button>
            <Button type="submit" :disabled="form.processing">
              {{ form.processing ? 'Updating...' : 'Update ODP' }}
            </Button>
          </CardFooter>
        </Card>
      </form>
    </div>
  </AppLayout>
</template>
