<script setup lang="ts">
  import L from '@/utils/leaflet'
  import { onMounted, ref, watch } from 'vue'
  import 'leaflet/dist/leaflet.css'

  const props = defineProps<{
    latitude?: string | number
    longitude?: string | number
  }>()

  const emit = defineEmits(['update:latitude', 'update:longitude'])

  const mapContainer = ref<HTMLElement | null>(null)
  const map = ref<L.Map | null>(null)
  const marker = ref<L.Marker | null>(null)

  // Default center (Jakarta)
  const defaultLat = -6.2088
  const defaultLng = 106.8456

  onMounted(() => {
    if (!mapContainer.value) return

    const lat = props.latitude ? Number(props.latitude) : defaultLat
    const lng = props.longitude ? Number(props.longitude) : defaultLng

    map.value = L.map(mapContainer.value).setView([lat, lng], 13)

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map.value)

    // Initial marker
    if (props.latitude && props.longitude) {
      setMarker(lat, lng)
    }

    map.value.on('click', (e) => {
      setMarker(e.latlng.lat, e.latlng.lng)
      emit('update:latitude', e.latlng.lat)
      emit('update:longitude', e.latlng.lng)
    })
  })

  const setMarker = (lat: number, lng: number) => {
    if (marker.value) {
      marker.value.setLatLng([lat, lng])
    } else if (map.value) {
      marker.value = L.marker([lat, lng], { draggable: true }).addTo(map.value)

      marker.value.on('dragend', (e) => {
        const pos = e.target.getLatLng()
        emit('update:latitude', pos.lat)
        emit('update:longitude', pos.lng)
      })
    }

    // Auto-pan if off-screen? Optional.
  }

  // Watch for external prop changes
  watch(
    () => [props.latitude, props.longitude],
    ([newLat, newLng]) => {
      if (newLat && newLng) {
        const lat = Number(newLat)
        const lng = Number(newLng)
        // Only update if map is ready and position differs significantly (to avoid loops)
        if (marker.value) {
          const cur = marker.value.getLatLng()
          if (Math.abs(cur.lat - lat) > 0.0001 || Math.abs(cur.lng - lng) > 0.0001) {
            setMarker(lat, lng)
            map.value?.panTo([lat, lng])
          }
        } else {
          setMarker(lat, lng)
        }
      }
    },
  )
</script>

<template>
  <div class="space-y-2">
    <div ref="mapContainer" class="h-[300px] w-full rounded-md border shadow-sm z-0"></div>
    <p class="text-xs text-muted-foreground">Click map or drag marker to set location.</p>
  </div>
</template>

<style>
  /* Fix leaflet z-index issues if any */
  .leaflet-pane {
    z-index: 10 !important;
  }
  .leaflet-top,
  .leaflet-bottom {
    z-index: 20 !important;
  }
</style>
