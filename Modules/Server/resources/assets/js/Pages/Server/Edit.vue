<script setup lang="ts">
  import FileUploader from '@/components/FileUploader.vue'
  import LocationPicker from '@/components/LocationPicker.vue'
  import { Button } from '@/components/ui/button'
  import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
  import { Input } from '@/components/ui/input'
  import { Label } from '@/components/ui/label'
  import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
  import { Textarea } from '@/components/ui/textarea'
  import AppLayout from '@/layouts/AppLayout.vue'
  import { Head, Link, useForm } from '@inertiajs/vue3'
  // import { index as serverIndex, edit as serverEdit, update as serverUpdate } from '@/routes/server';

  const props = defineProps<{
    server: any
    areas: Array<any>
    pops: Array<any>
  }>()

  const form = useForm({
    _method: 'put',
    name: props.server.name,
    code: props.server.code,
    function: props.server.function,
    area_id: props.server.area_id,
    pop_id: props.server.pop_id,
    building: props.server.building,
    floor: props.server.floor,
    area_location: props.server.area_location,
    latitude: props.server.latitude,
    longitude: props.server.longitude,
    description: props.server.description,
    status: props.server.status,
    photos: {
      Room: null as File | null,
      Rack: null as File | null,
      Installation: null as File | null,
      Cabling: null as File | null,
    },
  })

  const submit = () => {
    form.post(route('server.update', props.server.id), {
      onSuccess: () => form.reset('photos'),
    })
  }

  const getPhotosByCategory = (category: string) => {
    return props.server.photos?.filter((p: any) => p.category === category) || []
  }
</script>

<template>
  <Head title="Ubah Server" />

  <AppLayout
    :breadcrumbs="[
      { title: 'Server', href: route('server.index') },
      { title: 'Ubah', href: route('server.edit', server.id) },
    ]">
    <div class="mx-4 max-w-4xl space-y-6 p-4 md:p-4">
      <!-- Header -->
      <div class="space-y-1">
        <h1 class="text-2xl font-bold tracking-tight">Ubah Data Server</h1>
        <p class="text-sm text-foreground/60 text-muted-foreground">Perbarui informasi server atau perangkat core yang sudah ada.</p>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <!-- Section 1: Informasi Dasar -->
        <Card class="border shadow-none">
          <CardHeader class="pb-4">
            <CardTitle class="text-base font-semibold">Informasi Dasar</CardTitle>
            <CardDescription class="text-xs">Detail identitas dan fungsi perangkat.</CardDescription>
          </CardHeader>
          <CardContent class="space-y-5">
            <div class="space-y-1.5">
              <Label for="name" class="text-sm font-medium">Nama Perangkat</Label>
              <Input id="name" v-model="form.name" required placeholder="Contoh: CORE-ROUTER-01" class="h-11 rounded-lg border-border" />
              <div v-if="form.errors.name" class="mt-1 text-xs text-destructive">
                {{ form.errors.name }}
              </div>
            </div>

            <div class="space-y-5">
              <div class="space-y-1.5">
                <Label for="code" class="text-sm font-medium">Kode Unik</Label>
                <Input id="code" v-model="form.code" required placeholder="Contoh: SRV-001" class="h-11 rounded-lg border-border" />
                <div v-if="form.errors.code" class="mt-1 text-xs text-destructive">
                  {{ form.errors.code }}
                </div>
              </div>
              <div class="space-y-1.5">
                <Label for="function" class="text-sm font-medium">Fungsi Perangkat</Label>
                <Select v-model="form.function">
                  <SelectTrigger class="h-11 w-full rounded-lg border-border">
                    <SelectValue placeholder="Pilih Fungsi" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="Server">Server</SelectItem>
                    <SelectItem value="OLT">OLT</SelectItem>
                    <SelectItem value="Core Network">Core Network</SelectItem>
                    <SelectItem value="NOC">NOC</SelectItem>
                  </SelectContent>
                </Select>
                <div v-if="form.errors.function" class="mt-1 text-xs text-destructive">
                  {{ form.errors.function }}
                </div>
              </div>
              <div class="space-y-1.5">
                <Label for="status" class="text-sm font-medium">Status Operasional</Label>
                <Select v-model="form.status">
                  <SelectTrigger class="h-11 w-full rounded-lg border-border">
                    <SelectValue placeholder="Pilih Status" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="Active">Active</SelectItem>
                    <SelectItem value="Inactive">Inactive</SelectItem>
                    <SelectItem value="Planned">Planned</SelectItem>
                  </SelectContent>
                </Select>
                <div v-if="form.errors.status" class="mt-1 text-xs text-destructive">
                  {{ form.errors.status }}
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Section 2: Lokasi & Penempatan -->
        <Card class="border shadow-none">
          <CardHeader class="pb-4">
            <CardTitle class="text-base font-semibold">Lokasi & Penempatan</CardTitle>
            <CardDescription class="text-xs">Detail lokasi fisik perangkat ditempatkan.</CardDescription>
          </CardHeader>
          <CardContent class="space-y-5">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
              <div class="space-y-1.5">
                <Label for="area" class="text-sm font-medium">Wilayah Infrastruktur</Label>
                <Select v-model="form.area_id">
                  <SelectTrigger class="h-11 w-full rounded-lg border-border">
                    <SelectValue placeholder="Pilih Wilayah" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="area in areas" :key="area.id" :value="area.id">
                      {{ area.name }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div class="space-y-1.5">
                <Label for="pop" class="text-sm font-medium">POP (Opsional)</Label>
                <Select v-model="form.pop_id">
                  <SelectTrigger class="h-11 w-full rounded-lg border-border">
                    <SelectValue placeholder="Pilih POP (Jika ada)" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="pop in pops" :key="pop.id" :value="pop.id">
                      {{ pop.name }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-1.5">
                <Label for="building" class="text-sm font-medium">Nama Gedung</Label>
                <Input id="building" v-model="form.building" placeholder="Contoh: Gedung A" class="h-11 rounded-lg border-border" />
              </div>
              <div class="space-y-1.5">
                <Label for="floor" class="text-sm font-medium">Lantai</Label>
                <Input id="floor" v-model="form.floor" placeholder="Contoh: 2" class="h-11 rounded-lg border-border" />
              </div>
            </div>

            <div class="space-y-1.5">
              <Label for="area_location" class="text-sm font-medium">Ruangan / Keterangan Lokasi</Label>
              <Input id="area_location" v-model="form.area_location" placeholder="Contoh: Server Room 1" class="h-11 rounded-lg border-border" />
            </div>

            <div class="space-y-4 pt-2">
              <LocationPicker
                :latitude="form.latitude"
                :longitude="form.longitude"
                @update:latitude="form.latitude = $event"
                @update:longitude="form.longitude = $event" />
              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5">
                  <Label class="text-sm font-medium">Latitude</Label>
                  <Input v-model="form.latitude" placeholder="Latitude" class="h-11 rounded-lg border-border" />
                </div>
                <div class="space-y-1.5">
                  <Label class="text-sm font-medium">Longitude</Label>
                  <Input v-model="form.longitude" placeholder="Longitude" class="h-11 rounded-lg border-border" />
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Section 3: Dokumentasi -->
        <Card class="border shadow-none">
          <CardHeader class="pb-4">
            <CardTitle class="text-base font-semibold">Dokumentasi & Catatan</CardTitle>
            <CardDescription class="text-xs">Foto dokumentasi perangkat dan instalasi.</CardDescription>
          </CardHeader>
          <CardContent class="space-y-6">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
              <div v-for="category in ['Room', 'Rack', 'Installation', 'Cabling']" :key="category" class="space-y-3">
                <Label class="text-sm font-medium">{{
                  category === 'Room'
                    ? 'Ruangan / Lingkungan'
                    : category === 'Rack'
                      ? 'Rack / Kabinet'
                      : category === 'Installation'
                        ? 'Instalasi Perangkat'
                        : 'Kabel / Wiring'
                }}</Label>

                <!-- Existing Photos -->
                <div v-if="getPhotosByCategory(category).length > 0" class="mb-2 grid grid-cols-4 gap-2">
                  <div
                    v-for="photo in getPhotosByCategory(category)"
                    :key="photo.id"
                    class="group relative aspect-square overflow-hidden rounded-md border border-slate-100">
                    <img :src="'/storage/' + photo.path" class="h-full w-full object-cover transition-transform group-hover:scale-105" />
                  </div>
                </div>

                <FileUploader
                  v-model="form.photos[category as keyof typeof form.photos]"
                  accept="image/png, image/jpeg, image/jpg"
                  :max-size="5"
                  :error="(form.errors as any)[`photos.${category}`]" />
              </div>
            </div>

            <div class="space-y-1.5">
              <Label for="description" class="text-sm font-medium">Catatan Tambahan</Label>
              <Textarea
                id="description"
                v-model="form.description"
                rows="4"
                placeholder="Keterangan tambahan..."
                class="min-h-[100px] resize-none rounded-lg border-border" />
            </div>
          </CardContent>
          <CardFooter class="flex justify-end gap-3 rounded-b-lg border-t bg-muted/50 p-6">
            <Button variant="ghost" as-child class="font-medium">
              <Link :href="route('server.index')">Batal</Link>
            </Button>
            <Button type="submit" :disabled="form.processing" class="rounded-lg bg-primary px-10 font-bold hover:bg-primary/90">
              Simpan Perubahan
            </Button>
          </CardFooter>
        </Card>
      </form>
    </div>
  </AppLayout>
</template>
