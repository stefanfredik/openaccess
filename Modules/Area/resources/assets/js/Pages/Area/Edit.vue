<script setup lang="ts">
  import { Button } from '@/components/ui/button'
  import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
  import { Input } from '@/components/ui/input'
  import { Label } from '@/components/ui/label'
  import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
  import { Textarea } from '@/components/ui/textarea'
  import AppLayout from '@/layouts/AppLayout.vue'
  import { edit as areaEdit, index as areaIndex, update as areaUpdate } from '@/routes/area'
  import { Head, Link, useForm } from '@inertiajs/vue3'
  import axios from 'axios'
  import L from 'leaflet'
  import 'leaflet-draw'
  import 'leaflet-draw/dist/leaflet.draw.css'
  import 'leaflet/dist/leaflet.css'
  import { Maximize2 } from 'lucide-vue-next'
  import { nextTick, onMounted, ref, watch } from 'vue'

  const props = defineProps<{
    area: any
  }>()

  const form = useForm({
    name: props.area.name,
    code: props.area.code,
    type: props.area.type,
    province_id: props.area.province_id,
    regency_id: props.area.regency_id,
    district_id: props.area.district_id,
    village_id: props.area.village_id,
    description: props.area.description,
    boundary: props.area.boundary || null,
  })

  const mapContainer = ref<HTMLElement | null>(null)
  let map: L.Map | null = null
  let drawnItems: L.FeatureGroup | null = null

  // Fullscreen functionality
  const isFullscreen = ref(false)

  const toggleFullscreen = () => {
    const mapElement = mapContainer.value
    if (!mapElement) return

    if (!document.fullscreenElement) {
      mapElement
        .requestFullscreen()
        .then(() => {
          isFullscreen.value = true
          setTimeout(() => {
            map?.invalidateSize()
          }, 100)
        })
        .catch((err) => {
          console.error(`Error attempting to enable fullscreen: ${err.message}`)
        })
    } else {
      if (document.exitFullscreen) {
        document.exitFullscreen().then(() => {
          isFullscreen.value = false
        })
      }
    }
  }

  document.addEventListener('fullscreenchange', () => {
    if (!document.fullscreenElement) {
      isFullscreen.value = false
      setTimeout(() => {
        map?.invalidateSize()
      }, 100)
      if (mapContainer.value) {
        mapContainer.value.classList.remove('fullscreen-map')
      }
    } else {
      isFullscreen.value = true
      if (mapContainer.value) {
        mapContainer.value.classList.add('fullscreen-map')
      }
    }
  })

  const provinces = ref<Array<any>>([])
  const regencies = ref<Array<any>>([])
  const districts = ref<Array<any>>([])
  const villages = ref<Array<any>>([])

  const fetchProvinces = async () => {
    try {
      const response = await axios.get('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json')
      provinces.value = response.data
    } catch (error) {
      console.error('Error fetching provinces:', error)
    }
  }

  const fetchRegencies = async (id: string) => {
    try {
      const response = await axios.get(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${id}.json`)
      regencies.value = response.data
    } catch (error) {
      console.error('Error fetching regencies:', error)
    }
  }

  const fetchDistricts = async (id: string) => {
    try {
      const response = await axios.get(`https://www.emsifa.com/api-wilayah-indonesia/api/districts/${id}.json`)
      districts.value = response.data
    } catch (error) {
      console.error('Error fetching districts:', error)
    }
  }

  const fetchVillages = async (id: string) => {
    try {
      const response = await axios.get(`https://www.emsifa.com/api-wilayah-indonesia/api/villages/${id}.json`)
      villages.value = response.data
    } catch (error) {
      console.error('Error fetching villages:', error)
    }
  }

  const initMap = () => {
    if (!mapContainer.value) return

    map = L.map(mapContainer.value).setView([-2.5489, 118.0149], 5)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map)

    drawnItems = new L.FeatureGroup()
    map.addLayer(drawnItems)

    const drawControl = new L.Control.Draw({
      edit: {
        featureGroup: drawnItems,
      },
      draw: {
        polygon: {},
        polyline: false,
        rectangle: false,
        circle: false,
        marker: false,
        circlemarker: false,
      },
    })
    map.addControl(drawControl)

    // Initial boundary if exists
    if (form.boundary && Array.isArray(form.boundary) && form.boundary.length > 0) {
      // Ensure boundary is in the correct format [lat, lng]
      const polygon = L.polygon(form.boundary as L.LatLngExpression[])
      drawnItems.addLayer(polygon)
      map.fitBounds(polygon.getBounds())
    }

    map.on(L.Draw.Event.CREATED, (event: any) => {
      const layer = event.layer
      drawnItems?.clearLayers()
      drawnItems?.addLayer(layer)
      updateFormBoundary()
    })

    map.on(L.Draw.Event.EDITED, () => {
      updateFormBoundary()
    })

    map.on(L.Draw.Event.DELETED, () => {
      form.boundary = null
    })
  }

  const updateFormBoundary = () => {
    if (!drawnItems) return
    const layers = drawnItems.getLayers()
    if (layers.length > 0) {
      const polygon = layers[0] as L.Polygon
      const latlngs = polygon.getLatLngs()
      const coords = Array.isArray(latlngs[0]) ? latlngs[0] : latlngs
      form.boundary = (coords as L.LatLng[]).map((ll) => [ll.lat, ll.lng])
    } else {
      form.boundary = null
    }
  }

  onMounted(async () => {
    await fetchProvinces()
    if (form.province_id) await fetchRegencies(form.province_id)
    if (form.regency_id) await fetchDistricts(form.regency_id)
    if (form.district_id) await fetchVillages(form.district_id)

    await nextTick()
    initMap()
  })

  // Watchers for cascading dropdowns
  watch(
    () => form.province_id,
    async (newVal, oldVal) => {
      if (newVal === oldVal) return
      regencies.value = []
      districts.value = []
      villages.value = []
      form.regency_id = null
      form.district_id = null
      form.village_id = null

      if (newVal) {
        await fetchRegencies(newVal)
      }
    },
  )

  watch(
    () => form.regency_id,
    async (newVal, oldVal) => {
      if (newVal === oldVal) return
      districts.value = []
      villages.value = []
      form.district_id = null
      form.village_id = null

      if (newVal) {
        await fetchDistricts(newVal)
      }
    },
  )

  watch(
    () => form.district_id,
    async (newVal, oldVal) => {
      if (newVal === oldVal) return
      villages.value = []
      form.village_id = null

      if (newVal) {
        await fetchVillages(newVal)
      }
    },
  )

  const submit = () => {
    form.put(areaUpdate({ area: props.area.id }).url)
  }
</script>

<template>
  <Head title="Ubah Wilayah" />

  <AppLayout
    :breadcrumbs="[
      { title: 'Wilayah', href: areaIndex().url },
      { title: 'Ubah', href: areaEdit({ area: area.id }).url },
    ]">
    <div class="mx-4 max-w-4xl space-y-6 p-4 md:p-4">
      <!-- Header -->
      <div class="space-y-1">
        <h1 class="text-2xl font-bold tracking-tight">Ubah Wilayah</h1>
        <p class="text-sm text-foreground/60 text-muted-foreground">Perbarui data wilayah infrastruktur dibawah ini.</p>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <!-- Section 1: Identitas -->
        <Card class="border shadow-none">
          <CardHeader class="pb-4">
            <CardTitle class="text-base font-semibold">Informasi Wilayah</CardTitle>
            <CardDescription class="text-xs">Detail nama dan tipe klasifikasi wilayah.</CardDescription>
          </CardHeader>
          <CardContent class="space-y-5">
            <div class="space-y-1.5">
              <Label for="name" class="text-sm font-medium">Nama Wilayah</Label>
              <Input id="name" v-model="form.name" placeholder="Masukkan nama wilayah" required class="h-11 rounded-lg border-border" />
              <div v-if="form.errors.name" class="mt-1 text-xs text-destructive">
                {{ form.errors.name }}
              </div>
            </div>

            <div class="space-y-5">
              <div class="space-y-1.5">
                <Label for="code" class="text-sm font-medium">Kode Wilayah</Label>
                <Input id="code" v-model="form.code" placeholder="Masukkan kode wilayah" class="h-11 rounded-lg border-border" />
                <div v-if="form.errors.code" class="mt-1 text-xs text-destructive">
                  {{ form.errors.code }}
                </div>
              </div>
              <div class="space-y-1.5">
                <Label for="type" class="text-sm font-medium">Tipe Wilayah</Label>
                <Select v-model="form.type">
                  <SelectTrigger class="h-11 w-full rounded-lg border-border">
                    <SelectValue placeholder="Pilih tipe wilayah" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="region">Region</SelectItem>
                    <SelectItem value="area">Area</SelectItem>
                    <SelectItem value="subarea">Subarea</SelectItem>
                    <SelectItem value="pop_location">POP Location</SelectItem>
                  </SelectContent>
                </Select>
                <div v-if="form.errors.type" class="mt-1 text-xs text-destructive">
                  {{ form.errors.type }}
                </div>
              </div>
            </div>

            <div class="space-y-1.5">
              <Label for="description" class="text-sm font-medium">Keterangan</Label>
              <Textarea
                id="description"
                v-model="form.description"
                placeholder="Masukkan keterangan tambahan jika ada"
                class="min-h-[100px] resize-none rounded-lg border-border" />
              <div v-if="form.errors.description" class="mt-1 text-xs text-destructive">
                {{ form.errors.description }}
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Section 2: Lokasi -->
        <Card class="border shadow-none">
          <CardHeader class="pb-4">
            <CardTitle class="text-base font-semibold">Lokasi Administratif</CardTitle>
            <CardDescription class="text-xs">Data wilayah berdasarkan administrasi nasional.</CardDescription>
          </CardHeader>
          <CardContent class="space-y-5">
            <div class="space-y-1.5">
              <Label class="text-sm font-medium">Provinsi</Label>
              <Select v-model="form.province_id">
                <SelectTrigger class="h-11 w-full rounded-lg border-border">
                  <SelectValue placeholder="Pilih provinsi" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="prov in provinces" :key="prov.id" :value="prov.id">
                    {{ prov.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div class="space-y-1.5">
              <Label class="text-sm font-medium">Kota / Kabupaten</Label>
              <Select v-model="form.regency_id" :disabled="!form.province_id">
                <SelectTrigger class="h-11 w-full rounded-lg border-border">
                  <SelectValue placeholder="Pilih kota/kabupaten" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="reg in regencies" :key="reg.id" :value="reg.id">
                    {{ reg.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div class="space-y-1.5">
              <Label class="text-sm font-medium">Kecamatan</Label>
              <Select v-model="form.district_id" :disabled="!form.regency_id">
                <SelectTrigger class="h-11 w-full rounded-lg border-border">
                  <SelectValue placeholder="Pilih kecamatan" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="dist in districts" :key="dist.id" :value="dist.id">
                    {{ dist.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div class="space-y-1.5">
              <Label class="text-sm font-medium">Kelurahan / Desa</Label>
              <Select v-model="form.village_id" :disabled="!form.district_id">
                <SelectTrigger class="h-11 w-full rounded-lg border-border">
                  <SelectValue placeholder="Pilih kelurahan/desa" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="vill in villages" :key="vill.id" :value="vill.id">
                    {{ vill.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>
          </CardContent>
        </Card>

        <!-- Section 3: Map -->
        <Card class="border shadow-none">
          <CardHeader class="pb-4">
            <CardTitle class="text-base font-semibold">Batas Wilayah (Map)</CardTitle>
            <CardDescription class="text-xs">Gambarkan area cakupan pada peta dibawah ini.</CardDescription>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="relative h-[450px] w-full overflow-hidden rounded-lg border border-border">
              <div ref="mapContainer" class="absolute inset-0 z-0"></div>
              <Button
                type="button"
                @click="toggleFullscreen"
                variant="outline"
                size="icon"
                class="absolute top-3 right-3 z-[1000] border-border bg-background shadow-sm">
                <Maximize2 class="h-4 w-4" />
              </Button>
            </div>
            <div class="flex items-center justify-between text-[11px] tracking-tight text-muted-foreground uppercase">
              <span>Gunakan ikon Polygon di kiri atas peta untuk mulai menggambar.</span>
              <span v-if="form.boundary" class="font-bold text-emerald-600">Batas wilayah telah ditentukan</span>
            </div>
          </CardContent>
          <CardFooter class="flex justify-end gap-3 rounded-b-lg border-t bg-muted/50 p-6">
            <Button variant="ghost" as-child class="font-medium">
              <Link :href="areaIndex().url">Batal</Link>
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

<style>
  :deep(.leaflet-control-zoom),
  :deep(.leaflet-draw-section) {
    z-index: 1001 !important;
  }
  .fullscreen-map .map-controls {
    z-index: 1002 !important;
  }
  :deep(.fullscreen-map) {
    z-index: 9999 !important;
  }
</style>
