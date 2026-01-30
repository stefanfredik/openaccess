<script setup lang="ts">
  import AppLayout from '@/layouts/AppLayout.vue'
  import { Head } from '@inertiajs/vue3'

  declare global {
    interface Window {
      startRelocation: (id: number, type: string, name: string) => void
      editCable: (id: number) => void
    }
  }

  import { Button } from '@/components/ui/button'
  import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
  } from '@/components/ui/dropdown-menu'
  import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
  import { Switch as UiSwitch } from '@/components/ui/switch'
  import axios from 'axios'
  import L from 'leaflet'
  import 'leaflet/dist/leaflet.css'
  import { Layers, Plus } from 'lucide-vue-next'
  import { computed, onMounted, reactive, ref, watch } from 'vue'
  import { toast } from 'vue-sonner'
  import DeviceCreateModal from './Components/DeviceCreateModal.vue'

  const props = defineProps<{
    areas: Array<{ id: number; name: string; boundary: any }>
    pops: Array<{ id: number; name: string }>
  }>()

  const mapContainer = ref<HTMLElement | null>(null)
  const selectedAreaId = ref<string>(localStorage.getItem('map_selected_area') || 'all')
  const pendingDeviceType = ref<string | null>(null)
  const showCreateModal = ref(false)
  const showWarningModal = ref(false)
  const showFilter = ref(true) // Default open
  const isFilterMinimized = ref(true)
  const relocatingDevice = ref<{ id: number; type: string; name: string } | null>(null)
  const relocatingMarker = ref<L.Marker | null>(null)
  const originalPosition = ref<L.LatLng | null>(null)
  const currentPosition = ref<L.LatLng | null>(null)
  const warningMessage = ref('')
  const pendingLat = ref<number | null>(null)
  const pendingLng = ref<number | null>(null)
  const pendingPath = ref<Array<[number, number]>>([])
  const isDrawingCable = ref(false)
  const mapData = ref<any>(null) // Store fetched GeoJSON

  const getSavedFilters = () => {
    // Default: Show all devices
    const defaults = {
      pop: true,
      olt: true,
      ont: true,
      router: true,
      switch: true,
      access_point: true,
      odp: true,
      odf: true,
      pole: true,
      tower: true,
      joint_box: true,
      cpe: true,
      cable: true,
    }
    // Force default state to be 'All On' every time the map loads
    return defaults
  }

  const deviceFilters = reactive<Record<string, boolean>>(getSavedFilters())

  const deviceTypes = {
    active: [
      { label: 'OLT', value: 'olt' },
      { label: 'ONT', value: 'ont' },
      { label: 'Router', value: 'router' },
      { label: 'Switch', value: 'switch' },
      { label: 'Access Point', value: 'access_point' },
      { label: 'CPE', value: 'cpe' },
    ],
    passive: [
      { label: 'ODP', value: 'odp' },
      { label: 'ODF', value: 'odf' },
      { label: 'Pole', value: 'pole' },
      { label: 'Tower', value: 'tower' },
      { label: 'Joint Box', value: 'joint_box' },
      { label: 'Cable', value: 'cable' },
    ],
  }

  const updateFilter = (type: string, value: boolean) => {
    console.log(`[DEBUG] ===== updateFilter START =====`)
    console.log(`[DEBUG] Type: ${type}, New Value: ${value}`)
    console.log(`[DEBUG] Old state:`, JSON.parse(JSON.stringify(deviceFilters)))

    // Update the filter
    deviceFilters[type] = value

    console.log(`[DEBUG] New state:`, JSON.parse(JSON.stringify(deviceFilters)))
    console.log(`[DEBUG] Calling renderMap()...`)

    // Force re-render
    renderMap()

    console.log(`[DEBUG] ===== updateFilter END =====`)
  }

  // Helper to create valid SVG string (using Lucide style paths)
  const getIconHtml = (color: string, svgPath: string) => `
    <div class="flex flex-col items-center drop-shadow-md">
        <div style="background-color: ${color}; border: 2.5px solid #fff; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; z-index: 2;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="filter: drop-shadow(0 1px 1px rgba(0,0,0,0.1));">
                ${svgPath}
            </svg>
        </div>
        <div style="width: 0; height: 0; border-left: 7px solid transparent; border-right: 7px solid transparent; border-top: 9px solid #fff; margin-top: -2px; z-index: 1;"></div>
    </div>
`

  const createIcon = (color: string, svgPath: string) =>
    L.divIcon({
      className: 'custom-map-icon',
      html: getIconHtml(color, svgPath),
      iconSize: [36, 43],
      iconAnchor: [18, 43],
      popupAnchor: [0, -43],
    })

  const colors = {
    blue: '#3b82f6',
    green: '#22c55e',
    violet: '#8b5cf6',
    red: '#ef4444',
    yellow: '#f59e0b',
    grey: '#6b7280',
    orange: '#f97316',
    gold: '#fbbf24',
    black: '#1f2937',
  }

  const iconPaths = {
    pop: '<path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/>', // Building
    olt: '<rect width="20" height="8" x="2" y="2" rx="2" ry="2"/><rect width="20" height="8" x="2" y="14" rx="2" ry="2"/><line x1="6" x2="6.01" y1="6" y2="6"/><line x1="6" x2="6.01" y1="18" y2="18"/>', // Server
    ont: '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>', // Activity/Signal
    router:
      '<rect width="20" height="8" x="2" y="14" rx="2"/><path d="M6.01 18h.01"/><path d="M10.01 18h.01"/><path d="M15 10v4"/><path d="M17.84 7.17a4 4 0 0 0-5.66 0"/><path d="M20.66 4.34a8 8 0 0 0-11.31 0"/>', // Router
    switch:
      '<rect x="2" y="2" width="20" height="8" rx="2" ry="2"/><rect x="2" y="14" width="20" height="8" rx="2" ry="2"/><line x1="6" y1="6" x2="6.01" y2="6"/><line x1="6" y1="18" x2="6.01" y2="18"/>', // Switch
    access_point:
      '<path d="M12 20h.01"/><path d="M2 8.82a15 15 0 0 1 20 0"/><path d="M5 12.859a10 10 0 0 1 14 0"/><path d="M8.5 16.429a5 5 0 0 1 7 0"/>', // Wifi
    odp: '<path d="M3 3h18v18H3zM9 3v18M15 3v18M3 9h18M3 15h18"/>', // Grid/Box
    odf: '<rect width="18" height="18" x="3" y="3" rx="2"/><path d="M9 3v18"/><path d="M15 3v18"/><path d="M3 9h18"/><path d="M3 15h18"/>', // Rack/Frame
    pole: '<path d="M12 2v20"/><path d="M8 8h8"/><path d="M8 14h8"/>', // Pole
    tower: '<path d="M12 2 8 22h8L12 2z"/><path d="M6 18h12"/><path d="M8 12h8"/><path d="M10 6h4"/>', // Tower
    joint_box: '<path d="M3 7v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2z"/><path d="M3 7l9 6 9-6"/>', // Box
    cpe: '<path d="M15 10v4"/><path d="M17.84 7.17a4 4 0 0 0-5.66 0"/><path d="M20.66 4.34a8 8 0 0 0-11.31 0"/><rect x="2" y="14" width="20" height="8" rx="2"/>', // CPE
  }

  const mapIcons = {
    pop: createIcon(colors.blue, iconPaths.pop),
    olt: createIcon(colors.green, iconPaths.olt),
    ont: createIcon(colors.violet, iconPaths.ont),
    router: createIcon(colors.red, iconPaths.router),
    switch: createIcon(colors.yellow, iconPaths.switch),
    access_point: createIcon(colors.grey, iconPaths.access_point),
    odp: createIcon(colors.orange, iconPaths.odp),
    odf: createIcon(colors.gold, iconPaths.odf),
    pole: createIcon(colors.black, iconPaths.pole),
    tower: createIcon(colors.red, iconPaths.tower),
    joint_box: createIcon(colors.grey, iconPaths.joint_box),
    cpe: createIcon(colors.violet, iconPaths.cpe),
  }

  const availableFilters = computed(() => [
    { key: 'pop', label: 'POP', icon: iconPaths.pop, color: colors.blue },
    { key: 'olt', label: 'OLT', icon: iconPaths.olt, color: colors.green },
    { key: 'ont', label: 'ONT', icon: iconPaths.ont, color: colors.violet },
    {
      key: 'router',
      label: 'Router',
      icon: iconPaths.router,
      color: colors.red,
    },
    {
      key: 'switch',
      label: 'Switch',
      icon: iconPaths.switch,
      color: colors.yellow,
    },
    {
      key: 'access_point',
      label: 'Access Point',
      icon: iconPaths.access_point,
      color: colors.grey,
    },
    { key: 'odp', label: 'ODP', icon: iconPaths.odp, color: colors.orange },
    { key: 'odf', label: 'ODF', icon: iconPaths.odf, color: colors.gold },
    { key: 'pole', label: 'Pole', icon: iconPaths.pole, color: colors.black },
    { key: 'tower', label: 'Tower', icon: iconPaths.tower, color: colors.red },
    {
      key: 'joint_box',
      label: 'Joint Box',
      icon: iconPaths.joint_box,
      color: colors.grey,
    },
    { key: 'cpe', label: 'CPE', icon: iconPaths.cpe, color: colors.violet },
    { key: 'cable', label: 'Cables', icon: null, color: '#3b82f6' },
  ])

  const selectedAreaName = computed(() => {
    if (selectedAreaId.value === 'all') return 'All Areas'
    const area = props.areas.find((a) => String(a.id) === selectedAreaId.value)
    return area ? area.name : 'Unknown Area'
  })

  let map: L.Map | null = null
  let markersLayer: L.FeatureGroup | null = null
  let boundaryLayer: L.Layer | null = null
  let tempMarker: L.Marker | null = null
  let drawPolyline: L.Polyline | null = null

  const isPointInPolygon = (lat: number, lng: number, polygon: any[]) => {
    // If polygon is nested (e.g. [[[lat, lng], ...]]), take the first ring
    const points = Array.isArray(polygon[0][0]) ? polygon[0] : polygon

    let inside = false
    for (let i = 0, j = points.length - 1; i < points.length; j = i++) {
      const yi = points[i][0],
        xi = points[i][1] // yi=lat, xi=lng
      const yj = points[j][0],
        xj = points[j][1] // yj=lat, xj=lng

      const intersect = yi > lat !== yj > lat && lng < ((xj - xi) * (lat - yi)) / (yj - yi) + xi
      if (intersect) inside = !inside
    }
    return inside
  }

  const renderMap = () => {
    if (!map || !mapData.value) {
      console.warn('Map or Data not ready')
      return
    }

    console.log('--- RenderMap Start ---')
    console.log('DeviceFilters State:', JSON.stringify(deviceFilters))

    // Clear existing layers
    if (markersLayer) {
      markersLayer.clearLayers()
      console.log('markersLayer cleared')
    }

    const geojson = mapData.value
    if (!geojson.features || !Array.isArray(geojson.features)) {
      console.warn('Invalid GeoJSON features')
      return
    }

    // Filter features based on deviceFilters
    const filteredFeatures = geojson.features.filter((feature: any) => {
      const type = (feature.properties?.type || '').toLowerCase().trim()
      const isLine = feature.geometry?.type === 'LineString'

      // Use 'cable' filter for all LineStrings
      if (isLine) {
        return deviceFilters.cable === true
      }

      // Check explicit device types for Points and others
      if (deviceFilters[type] !== undefined) {
        return deviceFilters[type] === true
      }

      // Default: If type is unknown, only show if at least one filter matches?
      // Actually, let's keep showing unknown items to avoid total disappearance if data has typos.
      return true
    })

    console.log(`Total: ${geojson.features.length}, Filtered: ${filteredFeatures.length}`)

    console.log('Filtered features count:', filteredFeatures.length)

    const filteredGeojson = { ...geojson, features: filteredFeatures }

    // Create markers and lines
    const geojsonLayer = L.geoJSON(filteredGeojson, {
      style: (feature) => {
        if (feature?.geometry.type === 'LineString') {
          return {
            color: '#3b82f6',
            weight: 6,
            opacity: 0.8,
            interactive: !pendingDeviceType.value && !isDrawingCable.value,
          }
        }
        return {}
      },
      pointToLayer: (feature, latlng) => {
        const deviceType = feature.properties.type
        const icon = mapIcons[deviceType] || mapIcons.pop
        return L.marker(latlng, {
          icon,
          interactive: !pendingDeviceType.value && !isDrawingCable.value,
        })
      },
      onEachFeature: (feature, layer) => {
        const props = feature.properties
        let popupContent = `
                <div class="p-2 min-w-[200px]">
                    <h3 class="font-bold text-lg mb-2">${props.name}</h3>
                    <p class="text-sm"><strong>Type:</strong> ${props.type.toUpperCase().replace('_', ' ')}</p>
                    ${props.address ? `<p class="text-sm"><strong>Address:</strong> ${props.address}</p>` : ''}
                    <p class="text-sm mb-3"><strong>Status:</strong> <span class="text-green-600">${props.status}</span></p>
            `

        if (feature.geometry.type === 'Point') {
          popupContent += `
                    <div class="pt-2 border-t">
                        <button
                            onclick="window.startRelocation(${props.id}, '${props.type}', '${props.name.replace(/'/g, "\\'")}')"
                            class="w-full bg-blue-50 hover:bg-blue-100 text-blue-600 text-xs font-bold py-2 px-3 rounded-md transition-colors flex items-center justify-center gap-2"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                            Geser Posisi
                        </button>
                    </div>
                `
        } else if (feature.geometry.type === 'LineString') {
          popupContent += `
                    <div class="space-y-1 mt-2">
                            <div class="text-xs text-gray-700"><strong>Code:</strong> ${props.code || '-'}</div>
                        <div class="text-xs text-gray-700"><strong>Length:</strong> ${props.length}m</div>
                        <div class="text-xs text-gray-700"><strong>Core:</strong> ${props.core_count} Core</div>
                        <div class="text-xs text-gray-700"><strong>Type:</strong> ${props.type_name}</div>
                        ${props.brand ? `<div class="text-xs text-gray-700"><strong>Brand:</strong> ${props.brand}</div>` : ''}
                        ${props.start_point ? `<div class="text-xs text-gray-700"><strong>Start:</strong> ${props.start_point}</div>` : ''}
                        ${props.end_point ? `<div class="text-xs text-gray-700"><strong>End:</strong> ${props.end_point}</div>` : ''}
                    </div>
                    <div class="pt-2 border-t mt-2">
                        <button
                            onclick="window.editCable(${props.id})"
                            class="w-full bg-blue-50 hover:bg-blue-100 text-blue-600 text-xs font-bold py-2 px-3 rounded-md transition-colors flex items-center justify-center gap-2"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                            Edit Jalur
                        </button>
                    </div>
                `
        }

        popupContent += `</div>`
        layer.bindPopup(popupContent)

        layer.on({
          mouseover: (e) => {
            const l = e.target
            if (feature.geometry.type === 'LineString') {
              // Highlight Lines
              l.setStyle({ weight: 8, color: '#2563eb' })
              l.bringToFront()
            }
          },
          mouseout: (e) => {
            const l = e.target
            if (feature.geometry.type === 'LineString') {
              // Reset Lines
              geojsonLayer.resetStyle(l)
            }
          },
        })
      },
    })

    markersLayer?.addLayer(geojsonLayer)
  }

  const loadMapData = async () => {
    try {
      const url = selectedAreaId.value === 'all' ? route('map.data') : route('map.data', { area_id: selectedAreaId.value })

      const response = await axios.get(url)
      mapData.value = response.data

      renderMap()

      // Handle Boundary and Zoom logic remains same but using mapData.value
      if (selectedAreaId.value !== 'all') {
        // ... existing boundary logic ...
        if (boundaryLayer) {
          map?.removeLayer(boundaryLayer)
          boundaryLayer = null
        }
        const area = props.areas.find((a) => a.id === parseInt(selectedAreaId.value))
        if (area && area.boundary) {
          boundaryLayer = L.polygon(area.boundary, {
            color: '#3b82f6',
            weight: 2,
            opacity: 0.5,
            fillOpacity: 0.2,
            interactive: false,
          }).addTo(map!)
          boundaryLayer.bringToBack()

          map?.fitBounds((boundaryLayer as L.Polygon).getBounds())
        }
      } else if (mapData.value.features && mapData.value.features.length > 0) {
        // We can't really fit bounds easily without creating a layer first,
        // but markersLayer already has the layer now.
        if (markersLayer && markersLayer.getBounds().isValid()) {
          map?.fitBounds(markersLayer.getBounds())
        }
      }
    } catch (error) {
      console.error('Failed to load map data:', error)
    }
  }

  // Watch deviceFilters for changes
  watch(
    deviceFilters,
    (newVal) => {
      console.log('[DEBUG] deviceFilters watcher triggered:', JSON.stringify(newVal))
      localStorage.setItem('map_device_filters', JSON.stringify(newVal))
      renderMap()
    },
    { deep: true },
  )

  const initMap = async () => {
    if (!mapContainer.value) return

    // Load persisted view
    const savedLat = localStorage.getItem('map_center_lat')
    const savedLng = localStorage.getItem('map_center_lng')
    const savedZoom = localStorage.getItem('map_zoom')

    const defaultLat = savedLat ? parseFloat(savedLat) : -2.5489
    const defaultLng = savedLng ? parseFloat(savedLng) : 118.0149
    const defaultZoom = savedZoom ? parseInt(savedZoom) : 5

    // Initialize map
    map = L.map(mapContainer.value).setView([defaultLat, defaultLng], defaultZoom)

    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
      maxZoom: 19,
    }).addTo(map!)

    // PERSISTENCE: Listen for map view changes
    map!.on('moveend', () => {
      const center = map!.getCenter()
      localStorage.setItem('map_center_lat', center.lat.toString())
      localStorage.setItem('map_center_lng', center.lng.toString())
    })

    map!.on('zoomend', () => {
      localStorage.setItem('map_zoom', map!.getZoom().toString())
    })

    map!.on('click', (e: L.LeafletMouseEvent) => {
      if (isDrawingCable.value) {
        // Add point to path
        pendingPath.value.push([e.latlng.lat, e.latlng.lng])

        if (!drawPolyline) {
          drawPolyline = L.polyline(pendingPath.value, {
            color: '#3b82f6',
            weight: 4,
            dashArray: '5, 10',
          }).addTo(map!)
        } else {
          drawPolyline.setLatLngs(pendingPath.value as L.LatLngExpression[])
        }
        redrawEditMarkers()
        return
      }

      if (pendingDeviceType.value && pendingDeviceType.value !== 'cable') {
        console.log('Map Clicked:', e.latlng.lat, e.latlng.lng, 'Selected Area:', selectedAreaId.value)

        // Check if within selected area boundary
        if (selectedAreaId.value !== 'all') {
          const area = props.areas.find((a) => a.id === parseInt(selectedAreaId.value))
          if (area && area.boundary) {
            const isInside = isPointInPolygon(e.latlng.lat, e.latlng.lng, area.boundary)
            console.log('Boundary Check:', isInside, area.name)

            if (!isInside) {
              warningMessage.value = `Lokasi yang anda pilih berada di luar area ${area.name}. Silahkan pilih titik di dalam area yang telah ditentukan.`
              showWarningModal.value = true
              return
            }
          }
        }

        pendingLat.value = e.latlng.lat
        pendingLng.value = e.latlng.lng

        // Show Pin Point
        if (tempMarker) map?.removeLayer(tempMarker)
        tempMarker = L.marker(e.latlng, {
          icon: createIcon(colors.yellow, '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>'),
        }).addTo(map!)

        showCreateModal.value = true
      }
    })

    markersLayer = new L.FeatureGroup().addTo(map!)

    await loadMapData()
  }

  const saveRelocation = async () => {
    if (!relocatingDevice.value || !currentPosition.value) return

    try {
      await axios.post(route('map.relocate'), {
        id: relocatingDevice.value.id,
        type: relocatingDevice.value.type,
        lat: currentPosition.value.lat,
        lng: currentPosition.value.lng,
      })
      toast.success('Posisi berhasil diperbarui')

      if (relocatingMarker.value) {
        relocatingMarker.value.dragging?.disable()
      }

      relocatingDevice.value = null
      relocatingMarker.value = null
      loadMapData()
    } catch (error) {
      toast.error('Gagal memperbarui posisi')
    }
  }

  const cancelRelocation = () => {
    if (relocatingMarker.value && originalPosition.value) {
      relocatingMarker.value.setLatLng(originalPosition.value)
      relocatingMarker.value.dragging?.disable()
    }
    relocatingDevice.value = null
    relocatingMarker.value = null
  }

  // Global function for relocation (called from popup string)
  window.startRelocation = (id: number, type: string, name: string) => {
    if (!map || !markersLayer) return

    // Find the marker in markersLayer
    let targetMarker: L.Marker | null = null
    markersLayer.eachLayer((layer: any) => {
      if (layer instanceof L.GeoJSON) {
        layer.eachLayer((marker: any) => {
          if (marker instanceof L.Marker && marker.feature?.properties?.id === id && marker.feature?.properties?.type === type) {
            targetMarker = marker
          }
        })
      }
    })

    if (targetMarker) {
      relocatingDevice.value = { id, type, name }
      relocatingMarker.value = targetMarker
      originalPosition.value = (targetMarker as L.Marker).getLatLng()
      currentPosition.value = (targetMarker as L.Marker).getLatLng()
      ;(targetMarker as L.Marker).dragging?.enable()
      ;(targetMarker as L.Marker).on('dragend', (e: any) => {
        currentPosition.value = e.target.getLatLng()
      })

      map.closePopup()
      toast.info(`Pin ${name} sekarang bisa di-drag. Geser ke lokasi baru.`, {
        position: 'top-center',
        duration: 5000,
      })
    }
  }
  const editMarkers = ref<L.Marker[]>([])

  const redrawEditMarkers = () => {
    // Clear existing markers
    editMarkers.value.forEach((marker) => map?.removeLayer(marker))
    editMarkers.value = []

    if (!map) return

    // Create markers for each point in pendingPath
    pendingPath.value.forEach((point, index) => {
      const marker = L.marker(point, {
        draggable: true,
        icon: L.divIcon({
          className: 'bg-transparent',
          html: `<div class="w-3 h-3 bg-white border-2 border-blue-600 rounded-full shadow-sm hover:scale-125 transition-transform"></div>`,
          iconSize: [12, 12],
          iconAnchor: [6, 6],
        }),
      })

      marker.on('drag', (e: any) => {
        const newLatLng = e.target.getLatLng()
        pendingPath.value[index] = [newLatLng.lat, newLatLng.lng]

        // Update polyline
        if (drawPolyline) {
          drawPolyline.setLatLngs(pendingPath.value as L.LatLngExpression[])
        }
      })

      // Optional: Right click to remove point
      marker.on('contextmenu', () => {
        if (pendingPath.value.length <= 2) {
          toast.error('Jalur harus memiliki minimal 2 titik')
          return
        }
        pendingPath.value.splice(index, 1)
        if (drawPolyline) {
          drawPolyline.setLatLngs(pendingPath.value as L.LatLngExpression[])
        }
        redrawEditMarkers() // Re-index everything
      })

      marker.addTo(map!)
      editMarkers.value.push(marker)
    })
  }

  // Global function for editing cable path
  window.editCable = (id: number) => {
    if (!map || !markersLayer) return

    // Find the feature
    let targetFeature: any = null
    let targetLayer: L.Polyline | null = null

    markersLayer.eachLayer((layer: any) => {
      if (layer instanceof L.GeoJSON) {
        layer.eachLayer((l: any) => {
          if (l instanceof L.Polyline && l.feature?.properties?.id === id && l.feature?.properties?.type === 'cable') {
            targetFeature = l.feature
            targetLayer = l
          }
        })
      }
    })

    if (targetFeature && targetLayer) {
      relocatingDevice.value = {
        id: targetFeature.properties.id,
        type: 'cable',
        name: targetFeature.properties.name,
      }

      const coords = targetFeature.geometry.coordinates.map((c: any) => [c[1], c[0]])
      pendingPath.value = coords

      isDrawingCable.value = true

      if (drawPolyline) {
        map.removeLayer(drawPolyline)
      }

      drawPolyline = L.polyline(pendingPath.value, {
        color: '#3b82f6',
        weight: 4,
        dashArray: '5, 10',
      }).addTo(map)

      redrawEditMarkers()

      map.closePopup()
      toast.info(`Mengedit jalur kabel "${targetFeature.properties.name}". Geser titik putih untuk ubah jalur. Klik kanan titik untuk hapus.`, {
        position: 'top-center',
        duration: 5000,
      })
    }
  }

  const saveEditedCable = async () => {
    if (!relocatingDevice.value || !pendingPath.value.length) return

    try {
      await axios.post(route('map.relocate'), {
        id: relocatingDevice.value.id,
        type: 'cable',
        path: pendingPath.value,
      })
      toast.success('Jalur kabel berhasil diperbarui')

      // Reset state
      isDrawingCable.value = false
      pendingPath.value = []
      if (drawPolyline) {
        map?.removeLayer(drawPolyline)
        drawPolyline = null
      }
      editMarkers.value.forEach((marker) => map?.removeLayer(marker))
      editMarkers.value = []

      relocatingDevice.value = null

      loadMapData()
    } catch (error) {
      toast.error('Gagal memperbarui jalur kabel')
      console.error(error)
    }
  }

  const finalizeCableDrawing = () => {
    if (pendingPath.value.length < 2) {
      toast.error('Gambarkan minimal 2 titik untuk jalur kabel')
      return
    }

    // Set center for the modal coordinates (average or first point)
    pendingLat.value = pendingPath.value[0][0]
    pendingLng.value = pendingPath.value[0][1]

    editMarkers.value.forEach((marker) => map?.removeLayer(marker))
    editMarkers.value = []

    showCreateModal.value = true
  }

  const cancelCableDrawing = () => {
    isDrawingCable.value = false
    pendingDeviceType.value = null
    pendingPath.value = []
    if (drawPolyline) {
      map?.removeLayer(drawPolyline)
      drawPolyline = null
    }
    editMarkers.value.forEach((marker) => map?.removeLayer(marker))
    editMarkers.value = []
  }

  onMounted(() => {
    initMap()
  })

  watch(pendingDeviceType, (newVal) => {
    if (newVal === 'cable') {
      isDrawingCable.value = true
      pendingPath.value = []
      if (drawPolyline) {
        map?.removeLayer(drawPolyline)
        drawPolyline = null
      }
      editMarkers.value.forEach((marker) => map?.removeLayer(marker))
      editMarkers.value = []

      toast.info('Mode Gambar Kabel: Klik di peta untuk membuat jalur. Klik "Selesai" untuk menyimpan.', {
        duration: 5000,
        position: 'top-center',
      })
    } else {
      isDrawingCable.value = false
    }
    renderMap()
  })

  watch(showCreateModal, (val) => {
    if (!val) {
      // Reset device type when modal closes
      // If we were drawing a cable or if a cable was pending, clean it up
      if (pendingDeviceType.value === 'cable' || isDrawingCable.value || (drawPolyline && !showCreateModal.value)) {
        cancelCableDrawing()
      }

      pendingDeviceType.value = null

      if (tempMarker && map) {
        map.removeLayer(tempMarker)
        tempMarker = null
      }
    }
  })

  watch(selectedAreaId, (newVal) => {
    localStorage.setItem('map_selected_area', newVal)
  })
</script>

<template>
  <AppLayout :breadcrumbs="[{ title: 'Network Map', href: route('map.index') }]">
    <Head title="Network Map" />

    <div :class="['map-page-container', { 'cursor-pin': pendingDeviceType }]">
      <div ref="mapContainer" class="absolute inset-0 z-0"></div>
      <div
        v-if="showFilter"
        :class="[
          'absolute top-3 right-3 z-[1000] flex w-60 animate-in flex-col gap-2 rounded-lg bg-white/95 p-3 shadow-lg backdrop-blur-md transition-all slide-in-from-right-5',
          isFilterMinimized ? 'h-auto w-8' : '',
        ]">
        <!-- Header -->
        <div class="flex items-center justify-between border-b pb-2">
          <h3 class="font-bold text-gray-800">Filter</h3>
          <div class="flex items-center gap-1">
            <Button
              variant="ghost"
              size="icon"
              class="h-6 w-6 rounded-full hover:bg-gray-100"
              @click="isFilterMinimized = !isFilterMinimized"
              :title="isFilterMinimized ? 'Expand' : 'Minimize'">
              <Layers class="h-4 w-4 text-gray-500" />
            </Button>
          </div>
        </div>

        <!-- Minimized View -->
        <div v-if="isFilterMinimized" class="text-sm font-medium text-gray-600">
          {{ selectedAreaName }}
        </div>

        <!-- Expanded View -->
        <div v-else class="custom-scrollbar space-y-4 overflow-y-auto pr-2">
          <!-- Area Filter -->
          <div class="space-y-2">
            <label class="text-xs font-semibold tracking-wider text-gray-500 uppercase">Wilayah</label>
            <Select v-model="selectedAreaId" @update:modelValue="loadMapData">
              <SelectTrigger>
                <SelectValue placeholder="Pilih Wilayah" />
              </SelectTrigger>
              <SelectContent class="z-[2001]">
                <SelectItem value="all"> Semua Wilayah </SelectItem>
                <SelectItem v-for="area in areas" :key="area.id" :value="String(area.id)">
                  {{ area.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Device Filters -->
          <div class="custom-scrollbar max-h-[50vh] space-y-3 overflow-y-auto pr-1">
            <div class="flex items-center justify-between">
              <label class="text-xs font-semibold tracking-wider text-gray-500 uppercase"> Perangkat </label>
            </div>
            <div class="mt-2 space-y-2">
              <div
                v-for="item in availableFilters"
                :key="item.key"
                class="flex cursor-pointer items-center justify-between rounded-lg border border-gray-100 p-2 transition-colors hover:bg-gray-50"
                @click="updateFilter(item.key, !deviceFilters[item.key])">
                <div class="flex items-center gap-3">
                  <!-- Icon Preview -->
                  <div
                    class="flex h-8 w-8 items-center justify-center rounded-full border-2 bg-white shadow-sm"
                    :style="{
                      borderColor: item.color || '#ccc',
                    }">
                    <svg
                      v-if="item.icon"
                      xmlns="http://www.w3.org/2000/svg"
                      width="16"
                      height="16"
                      viewBox="0 0 24 24"
                      fill="none"
                      :stroke="item.color || '#ccc'"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      v-html="item.icon"></svg>
                    <!-- Fallback for Cable or others without specific icon paths -->
                    <div
                      v-else-if="item.key === 'cable'"
                      class="h-1 w-6 -rotate-45 transform"
                      :style="{
                        backgroundColor: '#3b82f6',
                      }"></div>
                    <div v-else class="h-2 w-2 rounded-full bg-gray-400"></div>
                  </div>
                  <span class="text-sm font-medium text-gray-700">{{ item.label }}</span>
                </div>
                <UiSwitch :checked="deviceFilters[item.key]" aria-readonly="true" />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Add Button (Bottom Left) -->
      <div class="absolute bottom-6 left-4 z-[1000]">
        <div class="rounded-full border border-blue-100 bg-white p-1 shadow-xl transition-transform hover:scale-110">
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button variant="ghost" size="icon" class="h-12 w-12 rounded-full transition-colors hover:bg-blue-50 hover:text-blue-600">
                <Plus class="h-8 w-8" />
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="start" side="top" class="mb-2 w-56">
              <DropdownMenuLabel>Add New Device</DropdownMenuLabel>
              <DropdownMenuSeparator />
              <DropdownMenuGroup>
                <DropdownMenuLabel class="py-1 text-[10px] font-bold text-muted-foreground uppercase">Active Devices</DropdownMenuLabel>
                <DropdownMenuItem v-for="type in deviceTypes.active" :key="type.value" @click="pendingDeviceType = type.value" class="cursor-pointer">
                  {{ type.label }}
                </DropdownMenuItem>
              </DropdownMenuGroup>
              <DropdownMenuSeparator />
              <DropdownMenuGroup>
                <DropdownMenuLabel class="py-1 text-[10px] font-bold text-muted-foreground uppercase">Passive Devices</DropdownMenuLabel>
                <DropdownMenuItem
                  v-for="type in deviceTypes.passive"
                  :key="type.value"
                  @click="pendingDeviceType = type.value"
                  class="cursor-pointer">
                  {{ type.label }}
                </DropdownMenuItem>
              </DropdownMenuGroup>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
      </div>
    </div>

    <!-- Cable Drawing Confirmation Bar -->
    <div v-if="isDrawingCable" class="absolute bottom-10 left-1/2 z-[2000] -translate-x-1/2 animate-in duration-300 slide-in-from-bottom-5">
      <div
        class="flex items-center gap-4 rounded-3xl border border-white/40 bg-white/95 p-3 pr-5 shadow-[0_20px_50px_rgba(0,0,0,0.15)] backdrop-blur-xl">
        <div
          class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-[0_8px_16px_rgba(37,99,235,0.3)] transition-transform hover:scale-105 active:scale-95">
          <Plus class="h-6 w-6 font-black" stroke-width="3" />
        </div>
        <div class="flex flex-col">
          <span class="text-[10px] font-black tracking-[0.15em] text-blue-600 uppercase">
            {{ relocatingDevice?.type === 'cable' ? 'MODE EDIT KABEL' : 'MODE GAMBAR KABEL' }}
          </span>
          <span class="text-[15px] font-extrabold text-slate-800">
            {{ relocatingDevice?.type === 'cable' ? 'Geser titik untuk mengubah jalur' : 'Klik peta untuk tambah titik baru' }}
          </span>
        </div>
        <div class="mx-2 h-10 w-px bg-slate-200/60"></div>
        <div class="flex items-center gap-3">
          <Button
            variant="ghost"
            @click="cancelCableDrawing"
            class="h-11 px-6 font-bold text-slate-500 transition-colors hover:bg-slate-100/50 hover:text-slate-800">
            Batal
          </Button>
          <Button
            @click="relocatingDevice?.type === 'cable' ? saveEditedCable() : finalizeCableDrawing()"
            class="h-11 rounded-2xl bg-blue-600 px-8 font-black text-white shadow-[0_8px_20px_rgba(37,99,235,0.25)] transition-all hover:bg-blue-700 hover:shadow-[0_12px_25px_rgba(37,99,235,0.4)] active:scale-95">
            {{ relocatingDevice?.type === 'cable' ? 'Simpan Perubahan' : 'Selesai Gambar' }}
          </Button>
        </div>
      </div>
    </div>

    <!-- Relocation Confirmation Bar -->
    <div
      v-if="relocatingDevice && relocatingDevice.type !== 'cable'"
      class="absolute bottom-10 left-1/2 z-[2000] -translate-x-1/2 animate-in duration-300 slide-in-from-bottom-5">
      <div
        class="flex items-center gap-4 rounded-3xl border border-white/40 bg-white/95 p-3 pr-5 shadow-[0_20px_50px_rgba(0,0,0,0.15)] backdrop-blur-xl">
        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-orange-500 text-white shadow-[0_8px_16px_rgba(249,115,22,0.3)]">
          <MapPin class="h-6 w-6" stroke-width="3" />
        </div>
        <div class="flex flex-col">
          <span class="text-[10px] font-black tracking-[0.15em] text-orange-500 uppercase"> MODE RELOKASI </span>
          <span class="text-[15px] font-extrabold text-slate-800"> Geser pin ke lokasi baru </span>
        </div>
        <div class="mx-2 h-10 w-px bg-slate-200/60"></div>
        <div class="flex items-center gap-3">
          <Button
            variant="ghost"
            @click="cancelRelocation"
            class="h-11 px-6 font-bold text-slate-500 transition-colors hover:bg-slate-100/50 hover:text-slate-800">
            Batal
          </Button>
          <Button
            @click="saveRelocation"
            class="h-11 rounded-2xl bg-orange-500 px-8 font-black text-white shadow-[0_8px_20px_rgba(249,115,22,0.25)] transition-all hover:bg-orange-600 hover:shadow-[0_12px_25px_rgba(249,115,22,0.4)] active:scale-95">
            Simpan Perubahan
          </Button>
        </div>
      </div>
    </div>

    <!-- Warning Popup Modal -->
    <div
      v-if="showWarningModal"
      class="fixed inset-0 z-[2000] flex animate-in items-center justify-center bg-black/50 p-4 backdrop-blur-sm duration-200 fade-in">
      <div class="w-full max-w-sm animate-in rounded-xl bg-white p-6 shadow-2xl duration-200 zoom-in-95">
        <div class="flex flex-col items-center space-y-4 text-center">
          <div class="rounded-full bg-amber-100 p-3">
            <Plus class="h-8 w-8 rotate-45 text-amber-600" />
          </div>
          <h3 class="text-xl font-bold text-gray-900">Di Luar Batas Area</h3>
          <p class="text-sm leading-relaxed text-gray-600">
            {{ warningMessage }}
          </p>
          <Button @click="showWarningModal = false" class="w-full rounded-lg bg-blue-600 py-2 font-semibold text-white hover:bg-blue-700">
            Mengerti
          </Button>
        </div>
      </div>
    </div>

    <DeviceCreateModal
      v-model:open="showCreateModal"
      :device-type="pendingDeviceType"
      :lat="pendingLat"
      :lng="pendingLng"
      :path="pendingPath"
      :areas="areas"
      :pops="pops"
      :selected-area-id="selectedAreaId"
      @success="loadMapData(); pendingDeviceType = null; cancelCableDrawing()" />
  </AppLayout>
</template>

<style scoped>
  /* Ensure map takes full height */
  :deep(.leaflet-container) {
    height: 100%;
    width: 100%;
  }

  .cursor-pin {
    /* Yellow Map Pin Cursor */
    cursor:
      url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='32' height='32' viewBox='0 0 24 24' fill='%23f59e0b' stroke='%23fff' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z'/%3E%3Ccircle cx='12' cy='10' r='3' fill='%23fff'/%3E%3C/svg%3E")
        16 32,
      crosshair !important;
  }

  .cursor-pin :deep(.leaflet-container),
  .cursor-pin :deep(.leaflet-interactive) {
    cursor:
      url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='32' height='32' viewBox='0 0 24 24' fill='%23f59e0b' stroke='%23fff' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z'/%3E%3Ccircle cx='12' cy='10' r='3' fill='%23fff'/%3E%3C/svg%3E")
        16 32,
      crosshair !important;
  }

  /* Full height logic handled by map-page-container */
  :global(#app) {
    height: 100vh !important;
  }

  .map-page-container {
    height: calc(100vh - 65px); /* Adjusted for header height */
    width: 100%;
    position: relative;
    overflow: hidden;
  }

  .custom-scrollbar::-webkit-scrollbar {
    width: 4px;
  }

  .custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
  }

  .custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
  }

  .custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
  }
</style>
