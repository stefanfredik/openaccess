<script setup lang="ts">
  import AppLayout from '@/layouts/AppLayout.vue'
  import { Head } from '@inertiajs/vue3'

  declare global {
    interface Window {
      startRelocation: (id: number, type: string, name: string) => void
      editCable: (id: number) => void
      google: any
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
  import { MarkerClusterer } from '@googlemaps/markerclusterer'
  import axios from 'axios'
  import { debounce } from 'lodash'
  import { Layers, MapPin, Plus } from 'lucide-vue-next'
  import { computed, onMounted, reactive, ref, watch } from 'vue'
  import { toast } from 'vue-sonner'
  import DeviceCreateModal from '../Components/DeviceCreateModal.vue'
  import SplicingEditor from '../../../Components/SplicingEditor.vue'

  declare global {
    interface Window {
      startRelocation: (id: number, type: string, name: string) => void
      editCable: (id: number) => void
      openSplicingEditor: (id: number, type: string) => void
      google: any
    }
  }

  const props = defineProps<{
    areas: Array<{ id: number; name: string; boundary: any }>
    pops: Array<{ id: number; name: string }>
  }>()

  // -- Interfaces --
  interface DeviceMetadata {
    id: number
    type: string
    name: string
    feature?: any
  }

  // We act as if these exist on the objects by casting, or we could extend the classes if we defined them.
  // Since AdvancedMarkerElement is loaded async, we just use Intersection Types.
  type MapMarker = google.maps.marker.AdvancedMarkerElement & { metadata?: DeviceMetadata }
  type MapPolyline = google.maps.Polyline & { metadata?: DeviceMetadata }

  // Classes (Loaded Async)
  let AdvancedMarkerElement: typeof google.maps.marker.AdvancedMarkerElement
  let PinElement: typeof google.maps.marker.PinElement
  let GoogleEvent: any // google.maps.event
  let GooglePolyline: typeof google.maps.Polyline
  let GooglePolygon: typeof google.maps.Polygon

  const mapContainer = ref<HTMLElement | null>(null)
  const selectedAreaId = ref<string>(localStorage.getItem('map_selected_area') || 'all')
  const pendingDeviceType = ref<string | null>(null)
  const showCreateModal = ref(false)
  const showWarningModal = ref(false)
  const showFilter = ref(true) // Default open
  const isFilterMinimized = ref(true)
  const relocatingDevice = ref<{ id: number; type: string; name: string; marker?: google.maps.marker.AdvancedMarkerElement } | null>(null)

  // Google Maps Objects
  let map: google.maps.Map | null = null
  let clusterer: MarkerClusterer | null = null
  let markers: MapMarker[] = []
  let polylines: MapPolyline[] = []
  let boundaryPolygons: google.maps.Polygon[] = []
  let tempMarker: google.maps.marker.AdvancedMarkerElement | null = null
  let drawPolyline: google.maps.Polyline | null = null
  let drawingManager: google.maps.drawing.DrawingManager | null = null
  let activeInfoWindow: google.maps.InfoWindow | null = null

  const warningMessage = ref('')
  const pendingLat = ref<number | null>(null)
  const pendingLng = ref<number | null>(null)
  const pendingPath = ref<Array<[number, number]>>([])
  const pendingLength = ref<number>(0)
  const isDrawingCable = ref(false)
  const mapData = ref<any>(null) // Store fetched GeoJSON
  const googleMapType = ref<'satellite' | 'roadmap'>((localStorage.getItem('map_google_type') as 'satellite' | 'roadmap') || 'roadmap')
  const showGoogleLabels = ref(false)

  // -- Filters --
  const getSavedFilters = () => ({
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
  })
  const deviceFilters = reactive<Record<string, boolean>>(getSavedFilters())

  // -- Icons & Colors --
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
    pop: '<path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/>',
    olt: '<rect width="20" height="8" x="2" y="2" rx="2" ry="2"/><rect width="20" height="8" x="2" y="14" rx="2" ry="2"/><line x1="6" x2="6.01" y1="6" y2="6"/><line x1="6" x2="6.01" y1="18" y2="18"/>',
    ont: '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>',
    router:
      '<rect width="20" height="8" x="2" y="14" rx="2"/><path d="M6.01 18h.01"/><path d="M10.01 18h.01"/><path d="M15 10v4"/><path d="M17.84 7.17a4 4 0 0 0-5.66 0"/><path d="M20.66 4.34a8 8 0 0 0-11.31 0"/>',
    switch:
      '<rect x="2" y="2" width="20" height="8" rx="2" ry="2"/><rect x="2" y="14" width="20" height="8" rx="2" ry="2"/><line x1="6" y1="6" x2="6.01" y2="6"/><line x1="6" y1="18" x2="6.01" y2="18"/>',
    access_point:
      '<path d="M12 20h.01"/><path d="M2 8.82a15 15 0 0 1 20 0"/><path d="M5 12.859a10 10 0 0 1 14 0"/><path d="M8.5 16.429a5 5 0 0 1 7 0"/>',
    odp: '<path d="M3 3h18v18H3zM9 3v18M15 3v18M3 9h18M3 15h18"/>',
    odf: '<rect width="18" height="18" x="3" y="3" rx="2"/><path d="M9 3v18"/><path d="M15 3v18"/><path d="M3 9h18"/><path d="M3 15h18"/>',
    pole: '<path d="M12 2v20"/><path d="M8 8h8"/><path d="M8 14h8"/>',
    tower: '<path d="M12 2 8 22h8L12 2z"/><path d="M6 18h12"/><path d="M8 12h8"/><path d="M10 6h4"/>',
    joint_box: '<path d="M3 7v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2z"/><path d="M3 7l9 6 9-6"/>',
    cpe: '<path d="M15 10v4"/><path d="M17.84 7.17a4 4 0 0 0-5.66 0"/><path d="M20.66 4.34a8 8 0 0 0-11.31 0"/><rect x="2" y="14" width="20" height="8" rx="2"/>',
  }

  const getIconHtml = (color: string, svgPath: string) => `
      <div class="flex flex-col items-center drop-shadow-md cursor-pointer hover:scale-110 transition-transform">
          <div style="background-color: ${color}; border: 2.5px solid #fff; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; z-index: 2;">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="filter: drop-shadow(0 1px 1px rgba(0,0,0,0.1));">
                  ${svgPath}
              </svg>
          </div>
          <div style="width: 0; height: 0; border-left: 7px solid transparent; border-right: 7px solid transparent; border-top: 9px solid #fff; margin-top: -2px; z-index: 1;"></div>
      </div>
  `

  const availableFilters = computed(() => [
    { key: 'pop', label: 'POP', icon: iconPaths.pop, color: colors.blue },
    { key: 'olt', label: 'OLT', icon: iconPaths.olt, color: colors.green },
    { key: 'ont', label: 'ONT', icon: iconPaths.ont, color: colors.violet },
    { key: 'router', label: 'Router', icon: iconPaths.router, color: colors.red },
    { key: 'switch', label: 'Switch', icon: iconPaths.switch, color: colors.yellow },
    { key: 'access_point', label: 'Access Point', icon: iconPaths.access_point, color: colors.grey },
    { key: 'odp', label: 'ODP', icon: iconPaths.odp, color: colors.orange },
    { key: 'odf', label: 'ODF', icon: iconPaths.odf, color: colors.gold },
    { key: 'pole', label: 'Pole', icon: iconPaths.pole, color: colors.black },
    { key: 'tower', label: 'Tower', icon: iconPaths.tower, color: colors.red },
    { key: 'joint_box', label: 'Joint Box', icon: iconPaths.joint_box, color: colors.grey },
    { key: 'cpe', label: 'CPE', icon: iconPaths.cpe, color: colors.violet },
    { key: 'cable', label: 'Cables', icon: null, color: '#3b82f6' },
  ])

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

  const selectedAreaName = computed(() => {
    if (selectedAreaId.value === 'all') return 'All Areas'
    const area = props.areas.find((a) => String(a.id) === selectedAreaId.value)
    return area ? area.name : 'Unknown Area'
  })

  // --- Map Initialization ---

  // Global handler for Google Maps Auth Failure
  if (typeof window !== 'undefined') {
    ;(window as any).gm_auth_failure = () => {
      toast.error('Google Maps Auth Failure: Silakan cek Billing atau API Key di Google Cloud Console.', {
        duration: 10000,
      })
    }
  }

  const initMap = () => {
    if (!mapContainer.value) return

    const apiKey = import.meta.env.VITE_GOOGLE_MAPS_API_KEY
    if (!apiKey) {
      toast.error('Google Maps API Key tidak ditemukan di .env')
      return
    }

    // Define callback first to avoid race conditions
    ;(window as any).initMapGIS = async () => {
      try {
        const markerLib = (await google.maps.importLibrary('marker')) as google.maps.MarkerLibrary
        const mapsLib = (await google.maps.importLibrary('maps')) as google.maps.MapsLibrary
        const coreLib = (await google.maps.importLibrary('core')) as any
        const drawingLib = (await google.maps.importLibrary('drawing')) as google.maps.drawing.DrawingLibrary

        GooglePolyline = mapsLib.Polyline
        GooglePolygon = mapsLib.Polygon
        AdvancedMarkerElement = markerLib.AdvancedMarkerElement
        PinElement = markerLib.PinElement
        GoogleEvent = coreLib.event

        drawingManager = new drawingLib.DrawingManager({
          drawingMode: null,
          drawingControl: true,
          drawingControlOptions: {
            position: google.maps.ControlPosition.TOP_CENTER,
            drawingModes: [google.maps.drawing.OverlayType.MARKER, google.maps.drawing.OverlayType.POLYLINE],
          },
          markerOptions: {
            draggable: true,
          },
          polylineOptions: {
            editable: true,
            geodesic: true,
            strokeColor: '#3b82f6',
            strokeOpacity: 1.0,
            strokeWeight: 4,
            icons: [{ icon: { path: 'M 0,-1 0,1', strokeOpacity: 1, scale: 2 }, offset: '0', repeat: '20px' }],
          },
        })

        const savedLat = parseFloat(localStorage.getItem('map_center_lat') || '-2.5489')
        const savedLng = parseFloat(localStorage.getItem('map_center_lng') || '118.0149')
        const savedZoom = parseInt(localStorage.getItem('map_zoom') || '5')

        map = new google.maps.Map(mapContainer.value!, {
          center: { lat: savedLat, lng: savedLng },
          zoom: savedZoom,
          mapId: 'DEMO_MAP_ID', // Required for Advanced Markers
          mapTypeControl: true,
          mapTypeControlOptions: {
            position: google.maps.ControlPosition.TOP_LEFT,
            style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR,
          },
          disableDefaultUI: false,
          streetViewControl: true,
          streetViewControlOptions: { position: google.maps.ControlPosition.RIGHT_BOTTOM },
          fullscreenControl: false,
          zoomControl: true,
          zoomControlOptions: { position: google.maps.ControlPosition.RIGHT_BOTTOM },
        })

        drawingManager.setMap(map)

        // Listen for new overlays created by the Drawing Manager
        drawingManager.addListener('overlaycomplete', (event: any) => {
          // Clear current drawing mode
          drawingManager!.setDrawingMode(null)

          if (event.type === 'marker') {
            const marker = event.overlay as google.maps.Marker
            const pos = marker.getPosition()
            if (!pos) return

            // Boundary Check
            if (selectedAreaId.value !== 'all') {
              const area = props.areas.find((a) => a.id === parseInt(selectedAreaId.value))
              if (area && area.boundary) {
                const rawPoints = Array.isArray(area.boundary[0][0]) ? area.boundary[0] : area.boundary
                const polygonPath = rawPoints.map((p: any) => ({ lat: p[0], lng: p[1] }))
                const poly = new GooglePolygon({ paths: polygonPath })

                const isInside = google.maps.geometry.poly.containsLocation(pos, poly)
                if (!isInside) {
                  marker.setMap(null) // Remove invalid marker
                  warningMessage.value = `Lokasi di luar area ${area.name}.`
                  showWarningModal.value = true
                  return
                }
              }
            }

            pendingLat.value = pos.lat()
            pendingLng.value = pos.lng()

            // Replace with custom marker logic or just show modal
            tempMarker = marker as any
            showCreateModal.value = true
          } else if (event.type === 'polyline') {
            const polyline = event.overlay as google.maps.Polyline
            const path = polyline.getPath()
            const points: [number, number][] = []
            for (let i = 0; i < path.getLength(); i++) {
              points.push([path.getAt(i).lat(), path.getAt(i).lng()])
            }

            pendingPath.value = points
            drawPolyline = polyline

            // Calculate length automatically
            if (window.google && window.google.maps && window.google.maps.geometry) {
              pendingLength.value = window.google.maps.geometry.spherical.computeLength(polyline.getPath())
            }

            finalizeCableDrawing()
          }
        })

        // Handle Toolbar interactions
        drawingManager.addListener('drawingmode_changed', () => {
          const mode = drawingManager!.getDrawingMode()
          if (mode === google.maps.drawing.OverlayType.MARKER) {
            // Default to ODP if from toolbar
            if (!pendingDeviceType.value) pendingDeviceType.value = 'odp'
          } else if (mode === google.maps.drawing.OverlayType.POLYLINE) {
            pendingDeviceType.value = 'cable'
          }
        })

        // Update map style (Initial load from preference)
        if (googleMapType.value) {
          map.setMapTypeId(googleMapType.value)
        }

        // Sync local state when map type changes natively
        google.maps.event.addListener(map, 'maptypeid_changed', () => {
          const newType = map!.getMapTypeId() as 'satellite' | 'roadmap' | 'hybrid' | 'terrain'
          if (newType === 'satellite' || newType === 'hybrid') {
            googleMapType.value = 'satellite'
            localStorage.setItem('map_google_type', 'satellite')
          } else {
            googleMapType.value = 'roadmap'
            localStorage.setItem('map_google_type', 'roadmap')
          }
        })

        // Persist View
        GoogleEvent.addListener(
          map,
          'idle',
          debounce(() => {
            if (!map) return
            const center = map.getCenter()
            const zoom = map.getZoom()
            if (center && zoom) {
              localStorage.setItem('map_center_lat', center.lat().toString())
              localStorage.setItem('map_center_lng', center.lng().toString())
              localStorage.setItem('map_zoom', zoom.toString())
              loadMapData()
            }
          }, 500),
        )

        // REMOVED manual click handler, handled by DrawingManager

        // Sync initial state if any
        if (pendingDeviceType.value) {
          if (pendingDeviceType.value === 'cable') {
            drawingManager.setDrawingMode('polyline' as any)
            isDrawingCable.value = true
          } else {
            drawingManager.setDrawingMode('marker' as any)
          }
        }

        await loadMapData()
      } catch (e) {
        console.error('Error init Google Maps', e)
        toast.error('Gagal memuat Google Maps SDK')
      }
    }

    // Check if script already exists
    if (!document.getElementById('google-maps-script')) {
      const script = document.createElement('script')
      script.id = 'google-maps-script'
      script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=geometry,places,marker,drawing&callback=initMapGIS&loading=async`
      script.async = true
      script.defer = true
      document.head.appendChild(script)
    } else if (window.google && window.google.maps) {
      ;(window as any).initMapGIS()
    }
  }

  // --- Rendering ---
  const renderMap = () => {
    if (!map || !mapData.value) return

    // 1. Clear existing
    if (clusterer) {
      clusterer.clearMarkers()
      // Note: clearing markers from clusterer removes them from map too usually,
      // but strictly we should also setMap(null) on them if we managed them.
      // MarkerClusterer manage them.
    }
    markers = []
    polylines.forEach((p) => p.setMap(null))
    polylines = []

    const geojson = mapData.value
    const markersToAdd: MapMarker[] = []

    geojson.features.forEach((feature: any) => {
      const type = (feature.properties?.type || '').toLowerCase()

      // Filter Check
      if (feature.geometry.type === 'LineString') {
        if (!deviceFilters.cable) return
      } else {
        if (deviceFilters[type] === false) return
      }

      const props = feature.properties

      if (feature.geometry.type === 'Point') {
        const latLng = { lat: feature.geometry.coordinates[1], lng: feature.geometry.coordinates[0] }

        const iconContainer = document.createElement('div')
        iconContainer.innerHTML = getIconHtml((colors as any)[type] || colors.blue, (iconPaths as any)[type] || iconPaths.pop)

        const marker = new AdvancedMarkerElement({
          position: latLng,
          content: iconContainer,
          title: props.name,
        })

        // Add Click Event (Info Window)
        marker.addListener('gmp-click', () => {
          const isSpliceable = ['odp', 'joint_box', 'odf'].includes(type)
          const splicingBtn = isSpliceable
            ? `<button onclick="window.openSplicingEditor(${props.id}, '${type}')" class="w-full mt-2 bg-purple-50 text-purple-600 rounded px-2 py-1 text-xs font-bold">Manage Splicing</button>`
            : ''

          if (activeInfoWindow) activeInfoWindow.close()

          activeInfoWindow = new google.maps.InfoWindow({
            content: `
                           <div class="p-2 min-w-[200px] text-gray-800">
                               <h3 class="font-bold text-lg mb-2">${props.name}</h3>
                               <p class="text-sm"><strong>Type:</strong> ${type.toUpperCase()}</p>
                               ${props.address ? `<p class="text-sm"><strong>Address:</strong> ${props.address}</p>` : ''}
                               <p class="text-sm mb-3"><strong>Status:</strong> <span class="text-green-600">${props.status}</span></p>
                               <div class="pt-2 border-t">
                                   <button onclick="window.startRelocation(${props.id}, '${type}', '${props.name.replace(/'/g, "\\'")}')" class="w-full bg-blue-50 text-blue-600 rounded px-2 py-1 text-xs font-bold">Geser Posisi</button>
                                   ${splicingBtn}
                                </div>
                           </div>
                        `,
          })
          activeInfoWindow.open(map, marker)
        })

        // Store ID for relocation mapping
        ;(marker as any).metadata = { id: props.id, type: type, name: props.name }

        markersToAdd.push(marker)
        markers.push(marker)
      } else if (feature.geometry.type === 'LineString') {
        const path = feature.geometry.coordinates.map((c: any) => ({ lat: c[1], lng: c[0] }))
        const poly = new GooglePolyline({
          path: path,
          geodesic: true,
          strokeColor: '#3b82f6',
          strokeOpacity: 0.8,
          strokeWeight: 6,
          map: map,
        })

        GoogleEvent.addListener(poly, 'mouseover', () => poly.setOptions({ strokeWeight: 8 }))
        GoogleEvent.addListener(poly, 'mouseout', () => poly.setOptions({ strokeWeight: 6 }))
        GoogleEvent.addListener(poly, 'click', (e: any) => {
          if (activeInfoWindow) activeInfoWindow.close()

          activeInfoWindow = new google.maps.InfoWindow({
            content: `
                           <div class="p-2 min-w-[200px] text-gray-800">
                               <h3 class="font-bold text-lg">${props.name}</h3>
                               <div class="text-xs mt-1">
                                   Length: ${props.length}m <br/>
                                   Core: ${props.core_count}
                               </div>
                               <div class="pt-2 border-t mt-2">
                                  <button onclick="window.editCable(${props.id})" class="w-full bg-blue-50 text-blue-600 rounded px-2 py-1 text-xs font-bold">Edit Jalur</button>
                               </div>
                           </div>
                        `,
            position: e.latLng,
          })
          activeInfoWindow.open(map)
        })
        // Store ID/Type
        ;(poly as any).metadata = { id: props.id, type: 'cable', name: props.name, feature: feature } // Store full feature for drawing

        polylines.push(poly)
      }
    })

    // Cluster Markers
    clusterer = new MarkerClusterer({ map, markers: markersToAdd })
  }

  const loadMapData = async (fitToArea = false) => {
    try {
      const params: any = {}
      if (selectedAreaId.value !== 'all') {
        params.area_id = selectedAreaId.value
      } else if (map) {
        const bounds = map.getBounds()
        if (bounds) {
          params.min_lat = bounds.getSouthWest().lat()
          params.max_lat = bounds.getNorthEast().lat()
          params.min_lng = bounds.getSouthWest().lng()
          params.max_lng = bounds.getNorthEast().lng()
        }
      }

      const response = await axios.get(route('map.data', params))
      mapData.value = response.data
      renderMap()

      renderBoundaries(fitToArea)
    } catch (e) {
      console.error(e)
    }
  }

  const renderBoundaries = (fitToArea: boolean) => {
    if (!map) return
    // Clear existing
    boundaryPolygons.forEach((p) => p.setMap(null))
    boundaryPolygons = []

    if (selectedAreaId.value !== 'all') {
      const area = props.areas.find((a) => String(a.id) === selectedAreaId.value)
      if (area && area.boundary) {
        const raw = Array.isArray(area.boundary[0][0]) ? area.boundary[0] : area.boundary
        const path = raw.map((p: any) => ({ lat: p[0], lng: p[1] }))
        const poly = new google.maps.Polygon({
          paths: path,
          strokeColor: '#3b82f6',
          strokeOpacity: 0.8,
          strokeWeight: 2,
          fillColor: '#3b82f6',
          fillOpacity: 0.1,
          clickable: false,
          map: map,
        })
        boundaryPolygons.push(poly)

        if (fitToArea) {
          const bounds = new google.maps.LatLngBounds()
          path.forEach((p: any) => bounds.extend(p))
          map.fitBounds(bounds)
        }
      }
    } else {
      // Render all boundaries
      props.areas.forEach((area) => {
        if (area.boundary) {
          const raw = Array.isArray(area.boundary[0][0]) ? area.boundary[0] : area.boundary
          const path = raw.map((p: any) => ({ lat: p[0], lng: p[1] }))
          const poly = new google.maps.Polygon({
            paths: path,
            strokeColor: '#3b82f6',
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: '#3b82f6',
            fillOpacity: 0.05,
            clickable: false,
            map: map,
          })
          boundaryPolygons.push(poly)
        }
      })
    }
  }

  const updateFilter = (type: string, value: boolean) => {
    deviceFilters[type] = value
    renderMap()
  }

  watch(
    deviceFilters,
    (v) => {
      localStorage.setItem('map_device_filters', JSON.stringify(v))
      renderMap()
    },
    { deep: true },
  )

  onMounted(() => {
    initMap()
  })

  // -- Interactions --
  const editMarkers = ref<google.maps.marker.AdvancedMarkerElement[]>([])

  const redrawEditMarkers = () => {
    // Native editing used
  }

  window.startRelocation = (id, type, name) => {
    if (activeInfoWindow) activeInfoWindow.close()
    const target = markers.find((m) => m.metadata?.id === id && m.metadata?.type === type)
    if (target) {
      // Hide original
      target.map = null

      // Temporary icon for relocation
      const dragIcon = document.createElement('img')
      dragIcon.src = 'http://maps.google.com/mapfiles/ms/icons/orange-dot.png'

      const dragMarker = new AdvancedMarkerElement({
        position: target.position,
        map: map,
        gmpDraggable: true,
        content: dragIcon,
        zIndex: 999,
        title: 'Geser ke posisi baru',
      })

      relocatingDevice.value = { id, type, name, marker: dragMarker }
      toast.info(`Mode Relokasi: Geser marker ${name} ke posisi baru`)
    }
  }

  window.editCable = (id) => {
    if (activeInfoWindow) activeInfoWindow.close()
    const targetPoly = polylines.find((p) => (p as any).metadata?.id === id)
    if (targetPoly) {
      const meta = (targetPoly as any).metadata
      relocatingDevice.value = { id: meta.id, type: 'cable', name: meta.name }

      const feature = meta.feature
      const coords = feature.geometry.coordinates.map((c: any) => [c[1], c[0]])
      pendingPath.value = coords
      isDrawingCable.value = true

      // Hide original
      targetPoly.setMap(null)

      drawPolyline = new GooglePolyline({
        path: coords.map((p: any) => ({ lat: p[0], lng: p[1] })),
        geodesic: true,
        strokeColor: '#3b82f6',
        strokeWeight: 4,
        editable: true,
        draggable: true,
        map: map,
      })

      const path = drawPolyline.getPath()
      const updatePendingPath = () => {
        const newPath: any[] = []
        const len = path.getLength()
        for (let i = 0; i < len; i++) {
          const p = path.getAt(i)
          newPath.push([p.lat(), p.lng()])
        }
        pendingPath.value = newPath

        // Update length for editing
        if (window.google && window.google.maps && window.google.maps.geometry) {
          pendingLength.value = window.google.maps.geometry.spherical.computeLength(drawPolyline!.getPath())
        }
      }

      GoogleEvent.addListener(path, 'insert_at', updatePendingPath)
      GoogleEvent.addListener(path, 'remove_at', updatePendingPath)
      GoogleEvent.addListener(path, 'set_at', updatePendingPath)
      GoogleEvent.addListener(drawPolyline, 'dragend', updatePendingPath)

      // Initialize path and length
      updatePendingPath()

      toast.info('Mode Edit Jalur Aktif')
    }
  }

  const cancelCableDrawing = () => {
    isDrawingCable.value = false
    pendingDeviceType.value = null
    pendingPath.value = []
    if (drawPolyline) drawPolyline.setMap(null)
    drawPolyline = null
    if (tempMarker) (tempMarker as any).setMap(null)
    tempMarker = null
    if (drawingManager) drawingManager.setDrawingMode(null)
    editMarkers.value.forEach((m) => (m.map = null))
    editMarkers.value = []

    if (relocatingDevice.value) {
      cancelRelocation()
    } else {
      loadMapData()
    }
  }

  const saveEditedCable = async () => {
    if (!relocatingDevice.value) return
    try {
      await axios.post(route('map.relocate'), {
        id: relocatingDevice.value.id,
        type: 'cable',
        path: pendingPath.value,
      })
      toast.success('Updated')
      cancelCableDrawing()
      relocatingDevice.value = null
    } catch (e) {
      toast.error('Error')
    }
  }

  const finalizeCableDrawing = () => {
    if (pendingPath.value.length < 2) return
    pendingLat.value = pendingPath.value[0][0]
    pendingLng.value = pendingPath.value[0][1]
    editMarkers.value.forEach((m) => (m.map = null))
    editMarkers.value = []
    showCreateModal.value = true
  }

  const handleCreateSuccess = () => {
    showCreateModal.value = false
    pendingDeviceType.value = null
    pendingLength.value = 0
    if (tempMarker) (tempMarker as any).setMap(null)
    tempMarker = null
    if (drawPolyline) drawPolyline.setMap(null)
    drawPolyline = null
    loadMapData()
  }

  const cancelRelocation = () => {
    if (relocatingDevice.value?.marker) {
      relocatingDevice.value.marker.map = null
    }
    relocatingDevice.value = null
    loadMapData()
  }

  const saveRelocation = async () => {
    if (!relocatingDevice.value || !relocatingDevice.value.marker) return

    // AdvancedMarkerElement uses .position, not .getPosition()
    const newPos = relocatingDevice.value.marker.position as google.maps.LatLng
    if (!newPos) return

    try {
      await axios.post(route('map.relocate'), {
        id: relocatingDevice.value.id,
        type: relocatingDevice.value.type,
        lat: typeof newPos.lat === 'function' ? newPos.lat() : (newPos as any).lat,
        lng: typeof newPos.lng === 'function' ? newPos.lng() : (newPos as any).lng,
      })
      toast.success('Lokasi berhasil diperbarui')
      cancelRelocation()
    } catch (e) {
      toast.error('Gagal memperbarui lokasi')
      console.error(e)
    }
  }

  // --- Splicing Editor Integration ---
  const showSplicingEditor = ref(false)
  const splicingEnclosureId = ref<number>(0)
  const splicingEnclosureType = ref<string>('')

  window.openSplicingEditor = (id, type) => {
    splicingEnclosureId.value = id
    splicingEnclosureType.value = type
    showSplicingEditor.value = true
  }

  watch(
    () => pendingDeviceType.value,
    (type) => {
      if (!drawingManager) return

      if (!type) {
        drawingManager.setDrawingMode(null)
        isDrawingCable.value = false
        return
      }

      if (type === 'cable') {
        drawingManager.setDrawingMode(google.maps.drawing.OverlayType.POLYLINE)
        isDrawingCable.value = true
        pendingPath.value = []
        toast.info('Silakan mulai menggambar jalur kabel di peta')
      } else {
        const label = type.toUpperCase()
        drawingManager.setDrawingMode(google.maps.drawing.OverlayType.MARKER)
        isDrawingCable.value = false
        toast.info(`Mode Tambah ${label}: Klik pada peta untuk menaruh perangkat`)
      }
    },
  )
</script>

<template>
  <AppLayout :breadcrumbs="[{ title: 'Google Map', href: route('map.google') }]">
    <Head title="Google Map" />

    <div :class="['map-page-container', { 'cursor-pin': pendingDeviceType }]">
      <div ref="mapContainer" class="absolute inset-0 z-0"></div>

      <!-- Filter Panel -->
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
            <Select v-model="selectedAreaId" @update:modelValue="() => loadMapData(true)">
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
                  <div
                    class="flex h-8 w-8 items-center justify-center rounded-full border-2 bg-white shadow-sm"
                    :style="{ borderColor: item.color || '#ccc' }">
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
                    <div v-else-if="item.key === 'cable'" class="h-1 w-6 -rotate-45 transform" :style="{ backgroundColor: '#3b82f6' }"></div>
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

      <!-- Floating Drawing Tools (Standard Professional Style) -->
      <div class="absolute bottom-10 left-4 z-[1000]">
        <!-- Add Button -->
        <DropdownMenu v-if="!pendingDeviceType && !relocatingDevice">
          <DropdownMenuTrigger as-child>
            <button
              class="flex h-12 w-12 items-center justify-center rounded-full bg-white shadow-xl ring-1 ring-black/10 transition-all hover:scale-110 hover:bg-gray-50 active:scale-95 focus:outline-none"
              title="Tambah Perangkat">
              <Plus class="h-6 w-6 text-blue-600" />
            </button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="start" side="top" class="z-[1001] mb-2 w-56">
            <DropdownMenuLabel>Pilih Perangkat</DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuGroup>
              <DropdownMenuLabel class="py-1 text-[10px] font-bold text-muted-foreground uppercase">Aktif</DropdownMenuLabel>
              <DropdownMenuItem v-for="type in deviceTypes.active" :key="type.value" @click="pendingDeviceType = type.value" class="cursor-pointer">
                <div class="flex items-center gap-2">
                  <div class="h-2 w-2 rounded-full" :style="{ backgroundColor: (colors as any)[type.value] || colors.blue }"></div>
                  {{ type.label }}
                </div>
              </DropdownMenuItem>
            </DropdownMenuGroup>
            <DropdownMenuSeparator />
            <DropdownMenuGroup>
              <DropdownMenuLabel class="py-1 text-[10px] font-bold text-muted-foreground uppercase">Pasif</DropdownMenuLabel>
              <DropdownMenuItem v-for="type in deviceTypes.passive" :key="type.value" @click="pendingDeviceType = type.value" class="cursor-pointer">
                <div class="flex items-center gap-2">
                  <div class="h-2 w-2 rounded-full" :style="{ backgroundColor: (colors as any)[type.value] || colors.blue }"></div>
                  {{ type.label }}
                </div>
              </DropdownMenuItem>
            </DropdownMenuGroup>
          </DropdownMenuContent>
        </DropdownMenu>
      </div>

      <!-- Stop/Finish Drawing Overlay (Centered at Bottom) -->
      <div v-if="pendingDeviceType || relocatingDevice" class="absolute bottom-10 left-1/2 z-[1000] -translate-x-1/2">
        <div
          class="flex items-center gap-3 rounded-full bg-white px-4 py-2 shadow-2xl ring-1 ring-black/10 animate-in fade-in zoom-in slide-in-from-bottom-4">
          <span v-if="pendingDeviceType" class="text-xs font-bold text-gray-600 uppercase tracking-tight">
            {{ pendingDeviceType === 'cable' ? 'Menggambar' : 'Menambah' }} {{ pendingDeviceType }}
          </span>
          <span v-else-if="relocatingDevice" class="text-xs font-bold text-blue-600 uppercase tracking-tight">
            Edit {{ relocatingDevice.type === 'cable' ? 'Jalur' : 'Lokasi' }}
          </span>

          <div
            v-if="(pendingDeviceType === 'cable' || (relocatingDevice && relocatingDevice.type === 'cable')) && pendingLength > 0"
            class="flex items-center gap-2">
            <div class="h-5 w-px bg-gray-200"></div>
            <span class="text-xs font-black text-blue-600">{{ pendingLength.toFixed(2) }}m</span>
          </div>
          <div class="h-5 w-px bg-gray-200"></div>

          <!-- Finalize/Save -->
          <button
            @click="
              () => {
                if (relocatingDevice) {
                  relocatingDevice.type === 'cable' ? saveEditedCable() : saveRelocation()
                } else if (pendingDeviceType === 'cable' || isDrawingCable) {
                  finalizeCableDrawing()
                }
              }
            "
            class="flex h-8 w-8 items-center justify-center rounded-full bg-green-500 text-white shadow-sm transition-all hover:bg-green-600 hover:scale-110 active:scale-95"
            :title="relocatingDevice ? 'Simpan Perubahan' : 'Selesai Gambar'">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="20"
              height="20"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="3"
              stroke-linecap="round"
              stroke-linejoin="round">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
          </button>

          <!-- Cancel -->
          <button
            @click="cancelCableDrawing"
            class="flex h-8 w-8 items-center justify-center rounded-full bg-red-50 text-red-500 transition-all hover:bg-red-500 hover:text-white hover:scale-110 active:scale-95"
            title="Batal">
            <Plus class="h-5 w-5 rotate-45" />
          </button>
        </div>
      </div>
    </div>

    <!-- Warning Popup -->
    <div v-if="showWarningModal" class="fixed inset-0 z-[2000] flex animate-in items-center justify-center bg-black/50 p-4">
      <div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-2xl">
        <div class="flex flex-col items-center space-y-4 text-center">
          <div class="rounded-full bg-amber-100 p-3"><Plus class="h-8 w-8 rotate-45 text-amber-600" /></div>
          <h3 class="text-xl font-bold text-gray-900">Di Luar Batas Area</h3>
          <p class="text-sm text-gray-600">{{ warningMessage }}</p>
          <Button @click="showWarningModal = false" class="w-full bg-blue-600">Mengerti</Button>
        </div>
      </div>
    </div>

    <DeviceCreateModal
      :open="showCreateModal"
      @update:open="showCreateModal = $event"
      :lat="pendingLat"
      :lng="pendingLng"
      :device-type="pendingDeviceType"
      :areas="areas"
      :pops="pops"
      :selected-area-id="selectedAreaId"
      :path="pendingPath"
      :initial-length="pendingLength"
      @success="handleCreateSuccess" />

    <SplicingEditor
      :is-open="showSplicingEditor"
      :enclosure-type="splicingEnclosureType"
      :enclosure-id="splicingEnclosureId"
      @close="showSplicingEditor = false" />
  </AppLayout>
</template>

<style scoped>
  .map-page-container {
    height: calc(100vh - 65px);
    width: 100%;
    position: relative;
    overflow: hidden;
  }
  .custom-scrollbar::-webkit-scrollbar {
    width: 4px;
  }
  .custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
  }
  .cursor-pin :deep(.gm-style) {
    cursor: crosshair !important;
  }
</style>
