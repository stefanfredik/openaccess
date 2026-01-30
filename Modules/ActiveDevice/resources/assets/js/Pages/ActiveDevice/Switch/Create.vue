<script setup lang="ts">
  import { Head, Link, useForm } from '@inertiajs/vue3'
  import { onMounted } from 'vue'
  import FileUploader from '@/components/FileUploader.vue'
  import MapLocationPicker from '@/components/MapLocationPicker.vue'
  import { Button } from '@/components/ui/button'
  import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
  import { Input } from '@/components/ui/input'
  import { Label } from '@/components/ui/label'
  import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
  import { Switch } from '@/components/ui/switch'
  import { Textarea } from '@/components/ui/textarea'
  import AppLayout from '@/layouts/AppLayout.vue'

  defineProps<{
    areas: Array<any>
    pops: Array<any>
  }>()

  const form = useForm({
    infrastructure_area_id: '',
    pop_id: '',
    code: '',
    name: '',
    brand: '',
    model: '',
    serial_number: '',
    mac_address: '',
    ip_address: '',
    port_count: 0,
    switch_type: '', // Access, Aggregation, Core
    is_active: true,
    installed_at: '',
    latitude: '',
    longitude: '',
    description: '',
    username: '',
    password: '',
    purchase_year: '',
    photo: null,
  })

  const submit = () => {
    form.post(route('active-device.switch.store'))
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
  <Head title="Add Switch" />

  <AppLayout
    :breadcrumbs="[
      { title: 'Switche', href: route('active-device.switch.index') },
      { title: 'Tambah Switch', href: '#' },
    ]">
    <div class="flex max-w-4xl flex-col gap-6 p-4 md:p-6 lg:p-8">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">Tambah Switch</h1>
        </div>
      </div>

      <form @submit.prevent="submit">
        <Card>
          <CardHeader>
            <CardTitle>Switch Information</CardTitle>
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
                <Label for="pop">POP (Site)</Label>
                <Select v-model="form.pop_id">
                  <SelectTrigger
                    :class="{
                      'border-destructive': form.errors.pop_id,
                    }">
                    <SelectValue placeholder="Select POP" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="pop in pops" :key="pop.id" :value="pop.id.toString()">
                      {{ pop.name }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                <p v-if="form.errors.pop_id" class="text-sm text-destructive">
                  {{ form.errors.pop_id }}
                </p>
              </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div class="space-y-2">
                <Label for="code">Code</Label>
                <Input
                  id="code"
                  v-model="form.code"
                  :class="{
                    'border-destructive': form.errors.code,
                  }"
                  placeholder="SW-XYZ" />
                <p v-if="form.errors.code" class="text-sm text-destructive">
                  {{ form.errors.code }}
                </p>
              </div>

              <div class="space-y-2">
                <Label for="name">Name</Label>
                <Input
                  id="name"
                  v-model="form.name"
                  :class="{
                    'border-destructive': form.errors.name,
                  }"
                  placeholder="Switch Access A" />
                <p v-if="form.errors.name" class="text-sm text-destructive">
                  {{ form.errors.name }}
                </p>
              </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div class="space-y-2">
                <Label for="switch_type">Switch Type</Label>
                <Select v-model="form.switch_type">
                  <SelectTrigger>
                    <SelectValue placeholder="Select Type" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="Unmanageable">Unmanageable</SelectItem>
                    <SelectItem value="Access">Manageable (Access)</SelectItem>
                    <SelectItem value="Aggregation">Manageable (Aggregation)</SelectItem>
                    <SelectItem value="Core">Manageable (Core)</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div class="space-y-2">
                <Label for="port_count">Port Count</Label>
                <Input id="port_count" type="number" v-model="form.port_count" />
              </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div class="space-y-2">
                <Label for="brand">Brand</Label>
                <Input id="brand" v-model="form.brand" />
              </div>
              <div class="space-y-2">
                <Label for="model">Model</Label>
                <Input id="model" v-model="form.model" />
              </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div class="space-y-2">
                <Label for="serial_number">Serial Number</Label>
                <Input id="serial_number" v-model="form.serial_number" />
              </div>
              <div class="space-y-2">
                <Label for="mac_address">MAC Address</Label>
                <Input id="mac_address" v-model="form.mac_address" />
              </div>
            </div>

            <MapLocationPicker
              v-model:latitude="form.latitude"
              v-model:longitude="form.longitude"
              :area-id="form.infrastructure_area_id"
              :areas="areas" />

            <div v-if="form.switch_type !== 'Unmanageable'" class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div class="space-y-2">
                <Label for="username">Username</Label>
                <Input id="username" v-model="form.username" placeholder="admin" />
              </div>
              <div class="space-y-2">
                <Label for="password">Password</Label>
                <Input id="password" type="password" v-model="form.password" placeholder="••••••••" />
              </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div v-if="form.switch_type !== 'Unmanageable'" class="space-y-2">
                <Label for="ip_address">IP Address</Label>
                <Input
                  id="ip_address"
                  v-model="form.ip_address"
                  :class="{
                    'border-destructive': form.errors.ip_address,
                  }"
                  placeholder="192.168.1.1" />
                <p v-if="form.errors.ip_address" class="text-sm text-destructive">
                  {{ form.errors.ip_address }}
                </p>
              </div>
              <div class="space-y-2">
                <Label for="installed_at">Installed At</Label>
                <Input id="installed_at" type="date" v-model="form.installed_at" />
              </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div class="space-y-2">
                <Label for="purchase_year">Purchase Year</Label>
                <Input id="purchase_year" type="number" v-model="form.purchase_year" placeholder="YYYY" :min="1900" :max="new Date().getFullYear()" />
              </div>
              <div class="space-y-1.5">
                <Label class="text-sm font-medium">Device Photo</Label>
                <FileUploader
                  v-model="form.photo"
                  accept="image/png, image/jpeg, image/jpg"
                  :max-size="2"
                  description="Max 2MB. Supports PNG, JPG."
                  :error="form.errors.photo" />
              </div>
            </div>

            <div class="space-y-2">
              <Label for="description">Description</Label>
              <Textarea id="description" v-model="form.description" />
            </div>

            <div class="flex items-center space-x-2">
              <Switch id="is_active" v-model:checked="form.is_active" />
              <Label for="is_active">Active Status</Label>
            </div>
          </CardContent>
          <CardFooter class="mt-6 flex justify-end gap-2 border-t p-6">
            <Button variant="outline" as-child>
              <Link :href="route('active-device.switch.index')">Cancel</Link>
            </Button>
            <Button type="submit" :disabled="form.processing">
              {{ form.processing ? 'Saving...' : 'Save Switch' }}
            </Button>
          </CardFooter>
        </Card>
      </form>
    </div>
  </AppLayout>
</template>
