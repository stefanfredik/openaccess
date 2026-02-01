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

  // -- Step-by-Step Cable Drawing State Machine --
  type CableDrawStep = 'idle' | 'selectStart' | 'selectEnd' | 'editPath'
  const cableDrawStep = ref<CableDrawStep>('idle')
  const startPoint = ref<{ lat: number; lng: number } | null>(null)
  const endPoint = ref<{ lat: number; lng: number } | null>(null)
  const autoRouteMode = ref(true) // Auto-follow roads by default
  const isLoadingRoute = ref(false) // Loading state for directions API
  let startPointMarker: google.maps.marker.AdvancedMarkerElement | null = null
  let endPointMarker: google.maps.marker.AdvancedMarkerElement | null = null

  // -- Auto-Snap Cable to Devices --
  const SNAPPABLE_TYPES = ['odp', 'odf', 'joint_box', 'pole', 'olt', 'ont', 'splitter', 'slack']
  const SNAP_RADIUS_METERS = 50 // Snap radius in meters
  type SnappedDevice = { id: number; type: string; name: string; lat: number; lng: number } | null
  const snappedStartDevice = ref<SnappedDevice>(null)
  const snappedEndDevice = ref<SnappedDevice>(null)
  const nearbyDevice = ref<SnappedDevice>(null) // Currently detected nearby device
  let snapHighlightCircle: google.maps.Circle | null = null
  const waypointPolesIds = ref<number[]>([]) // Track pole IDs the cable passes through

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
            draggable: false,
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

            // Handle temporary marker interactions
            marker.addListener('click', () => {
              showCreateModal.value = true
            })

            marker.addListener('dragend', () => {
              const newPos = marker.getPosition()
              if (newPos) {
                pendingLat.value = newPos.lat()
                pendingLng.value = newPos.lng()
                showCreateModal.value = true // Re-open if dragged
              }
            })

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

      // Use the same animated target icon as placement flow
      const deviceColor = (colors as any)[type] || colors.blue
      const markerIcon = document.createElement('div')
      markerIcon.innerHTML = `
        <div class="flex flex-col items-center animate-bounce">
          <div style="background-color: ${deviceColor}; border: 3px solid #fff; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.3);">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="3"/>
              <path d="M12 2v4m0 12v4m10-10h-4M6 12H2"/>
            </svg>
          </div>
          <div style="width: 0; height: 0; border-left: 8px solid transparent; border-right: 8px solid transparent; border-top: 10px solid #fff; margin-top: -2px;"></div>
        </div>
      `

      const dragMarker = new AdvancedMarkerElement({
        position: target.position,
        map: map,
        gmpDraggable: true,
        content: markerIcon,
        zIndex: 999,
        title: 'Geser ke posisi baru',
      })

      relocatingDevice.value = { id, type, name, marker: dragMarker }
      toast.info(`Geser marker ${name} ke lokasi baru, lalu klik centang.`)
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
        draggable: false,
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

  const cancelAddingDevice = () => {
    isDrawingCable.value = false
    pendingDeviceType.value = null
    pendingPath.value = []
    if (drawPolyline) drawPolyline.setMap(null)
    drawPolyline = null
    if (tempMarker) (tempMarker as any).map = null
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
      cancelAddingDevice()
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

    // Clean up cable step drawing
    if (cableDrawStep.value !== 'idle') {
      cableDrawStep.value = 'idle'
      isDrawingCable.value = false
      startPoint.value = null
      endPoint.value = null
      if (startPointMarker) {
        startPointMarker.map = null
        startPointMarker = null
      }
      if (endPointMarker) {
        endPointMarker.map = null
        endPointMarker = null
      }
    }

    if (tempMarker) (tempMarker as any).setMap(null)
    tempMarker = null
    if (drawPolyline) drawPolyline.setMap(null)
    drawPolyline = null
    loadMapData()
  }

  const cancelRelocation = () => {
    if (relocatingDevice.value?.id) {
      // Restore original marker if possible
      const target = markers.find((m) => m.metadata?.id === relocatingDevice.value?.id && m.metadata?.type === relocatingDevice.value?.type)
      if (target) target.map = map
    }

    if (relocatingDevice.value?.marker) {
      relocatingDevice.value.marker.map = null
    }

    relocatingDevice.value = null
    // Only reload if we can't restore or to be safe, but let's try restoring first for speed
    renderMap()
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

  // -- Step-by-Step Cable Drawing Functions --
  // -- Snap Detection Functions --
  const findNearbySnappableDevice = (position: { lat: number; lng: number }): SnappedDevice => {
    if (!mapData.value?.features) return null

    let closestDevice: SnappedDevice = null
    let closestDistance = Infinity

    for (const feature of mapData.value.features) {
      const deviceType = feature.properties?.type?.toLowerCase()
      if (!SNAPPABLE_TYPES.includes(deviceType)) continue

      const coords = feature.geometry?.coordinates
      if (!coords || feature.geometry?.type !== 'Point') continue

      const deviceLat = coords[1]
      const deviceLng = coords[0]

      // Calculate distance in meters using Haversine-like approach
      const R = 6371000 // Earth radius in meters
      const dLat = ((deviceLat - position.lat) * Math.PI) / 180
      const dLng = ((deviceLng - position.lng) * Math.PI) / 180
      const a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos((position.lat * Math.PI) / 180) * Math.cos((deviceLat * Math.PI) / 180) * Math.sin(dLng / 2) * Math.sin(dLng / 2)
      const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a))
      const distance = R * c

      if (distance <= SNAP_RADIUS_METERS && distance < closestDistance) {
        closestDistance = distance
        closestDevice = {
          id: feature.properties?.id,
          type: deviceType,
          name: feature.properties?.name || feature.properties?.label || `${deviceType.toUpperCase()} #${feature.properties?.id}`,
          lat: deviceLat,
          lng: deviceLng,
        }
      }
    }

    return closestDevice
  }

  const showSnapHighlight = (device: NonNullable<SnappedDevice>) => {
    hideSnapHighlight()
    if (!map || !window.google?.maps?.Circle) return

    snapHighlightCircle = new window.google.maps.Circle({
      center: { lat: device.lat, lng: device.lng },
      radius: SNAP_RADIUS_METERS,
      strokeColor: '#22c55e',
      strokeOpacity: 0.8,
      strokeWeight: 3,
      fillColor: '#22c55e',
      fillOpacity: 0.2,
      map: map,
      zIndex: 900,
    })
  }

  const hideSnapHighlight = () => {
    if (snapHighlightCircle) {
      snapHighlightCircle.setMap(null)
      snapHighlightCircle = null
    }
    nearbyDevice.value = null
  }

  // Find poles near the path and insert them as waypoints
  const POLE_SNAP_DISTANCE = 15 // meters - distance from path to snap pole
  const insertPoleWaypoints = (path: google.maps.LatLngLiteral[]): google.maps.LatLngLiteral[] => {
    if (!mapData.value?.features || path.length < 2) return path

    // Reset waypoint poles IDs
    waypointPolesIds.value = []

    // Get all poles from map data
    const poles: { id: number; lat: number; lng: number; name: string }[] = []
    for (const feature of mapData.value.features) {
      const deviceType = feature.properties?.type?.toLowerCase()
      if (deviceType !== 'pole') continue
      const coords = feature.geometry?.coordinates
      if (!coords || feature.geometry?.type !== 'Point') continue
      poles.push({
        id: feature.properties?.id,
        lat: coords[1],
        lng: coords[0],
        name: feature.properties?.name || `Pole #${feature.properties?.id}`,
      })
    }

    if (poles.length === 0) return path

    // For each segment in path, check if any pole is close to it
    const newPath: google.maps.LatLngLiteral[] = [path[0]]
    const usedPoles = new Set<string>()
    const collectedPoleIds: number[] = []

    for (let i = 0; i < path.length - 1; i++) {
      const segStart = path[i]
      const segEnd = path[i + 1]

      // Find poles near this segment
      const nearbyPoles: { pole: (typeof poles)[0]; distance: number; position: number }[] = []

      for (const pole of poles) {
        const poleKey = `${pole.lat},${pole.lng}`
        if (usedPoles.has(poleKey)) continue

        // Calculate distance from pole to line segment
        const dist = pointToSegmentDistance(pole, segStart, segEnd)
        if (dist <= POLE_SNAP_DISTANCE) {
          // Calculate position along segment (0 = start, 1 = end)
          const position = getPositionAlongSegment(pole, segStart, segEnd)
          nearbyPoles.push({ pole, distance: dist, position })
          usedPoles.add(poleKey)
        }
      }

      // Sort by position along segment
      nearbyPoles.sort((a, b) => a.position - b.position)

      // Add poles as waypoints and collect IDs
      for (const { pole } of nearbyPoles) {
        newPath.push({ lat: pole.lat, lng: pole.lng })
        collectedPoleIds.push(pole.id)
      }

      // Add segment end (except for last segment, it's added after loop)
      if (i < path.length - 2) {
        newPath.push(segEnd)
      }
    }

    newPath.push(path[path.length - 1])

    // Store collected pole IDs
    waypointPolesIds.value = collectedPoleIds

    return newPath
  }

  // Helper: Calculate distance from point to line segment (in meters)
  const pointToSegmentDistance = (
    point: { lat: number; lng: number },
    segStart: google.maps.LatLngLiteral,
    segEnd: google.maps.LatLngLiteral,
  ): number => {
    const R = 6371000 // Earth radius in meters

    // Convert to radians
    const lat1 = (segStart.lat * Math.PI) / 180
    const lat2 = (segEnd.lat * Math.PI) / 180
    const lat3 = (point.lat * Math.PI) / 180
    const lng1 = (segStart.lng * Math.PI) / 180
    const lng2 = (segEnd.lng * Math.PI) / 180
    const lng3 = (point.lng * Math.PI) / 180

    // Cross-track distance formula (simplified for short distances)
    const dLat = lat2 - lat1
    const dLng = lng2 - lng1

    const t = Math.max(0, Math.min(1, ((lat3 - lat1) * dLat + (lng3 - lng1) * dLng) / (dLat * dLat + dLng * dLng)))

    const closestLat = lat1 + t * dLat
    const closestLng = lng1 + t * dLng

    // Haversine distance from point to closest point on segment
    const dLatP = lat3 - closestLat
    const dLngP = lng3 - closestLng
    const a = Math.sin(dLatP / 2) * Math.sin(dLatP / 2) + Math.cos(closestLat) * Math.cos(lat3) * Math.sin(dLngP / 2) * Math.sin(dLngP / 2)
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a))
    return R * c
  }

  // Helper: Get position (0-1) of pole projection along segment
  const getPositionAlongSegment = (
    point: { lat: number; lng: number },
    segStart: google.maps.LatLngLiteral,
    segEnd: google.maps.LatLngLiteral,
  ): number => {
    const dLat = segEnd.lat - segStart.lat
    const dLng = segEnd.lng - segStart.lng
    if (dLat === 0 && dLng === 0) return 0
    return Math.max(0, Math.min(1, ((point.lat - segStart.lat) * dLat + (point.lng - segStart.lng) * dLng) / (dLat * dLat + dLng * dLng)))
  }

  // Douglas-Peucker path simplification to reduce number of points
  const simplifyPath = (
    path: google.maps.LatLngLiteral[],
    tolerance: number = 0.00005, // ~5 meters in lat/lng
  ): google.maps.LatLngLiteral[] => {
    if (path.length <= 2) return path

    // Find the point with maximum distance from line between first and last
    let maxDist = 0
    let maxIndex = 0
    const first = path[0]
    const last = path[path.length - 1]

    for (let i = 1; i < path.length - 1; i++) {
      const dist = perpendicularDistance(path[i], first, last)
      if (dist > maxDist) {
        maxDist = dist
        maxIndex = i
      }
    }

    // If max distance is greater than tolerance, recursively simplify
    if (maxDist > tolerance) {
      const left = simplifyPath(path.slice(0, maxIndex + 1), tolerance)
      const right = simplifyPath(path.slice(maxIndex), tolerance)
      return [...left.slice(0, -1), ...right]
    } else {
      // All points between first and last are within tolerance, remove them
      return [first, last]
    }
  }

  // Helper: Perpendicular distance from point to line
  const perpendicularDistance = (
    point: google.maps.LatLngLiteral,
    lineStart: google.maps.LatLngLiteral,
    lineEnd: google.maps.LatLngLiteral,
  ): number => {
    const dx = lineEnd.lng - lineStart.lng
    const dy = lineEnd.lat - lineStart.lat
    const mag = Math.sqrt(dx * dx + dy * dy)
    if (mag === 0) return 0
    return Math.abs((dy * point.lng - dx * point.lat + lineEnd.lng * lineStart.lat - lineEnd.lat * lineStart.lng) / mag)
  }

  const setupMarkerSnapListener = (marker: google.maps.marker.AdvancedMarkerElement, isStartMarker: boolean = true) => {
    // During drag - show highlight when near device
    marker.addListener('drag', () => {
      const pos = marker.position as google.maps.LatLngLiteral
      const device = findNearbySnappableDevice({ lat: pos.lat, lng: pos.lng })

      if (device) {
        nearbyDevice.value = device
        showSnapHighlight(device)
      } else {
        hideSnapHighlight()
      }
    })

    // On drag end - snap to exact device position if nearby
    marker.addListener('dragend', () => {
      const markerElement = marker.content as HTMLElement
      const color = isStartMarker ? '#22c55e' : '#ef4444' // green or red

      if (nearbyDevice.value) {
        // Snap marker to exact device position
        marker.position = { lat: nearbyDevice.value.lat, lng: nearbyDevice.value.lng }

        // Store snapped device reference
        if (isStartMarker) {
          snappedStartDevice.value = nearbyDevice.value
        } else {
          snappedEndDevice.value = nearbyDevice.value
        }

        // Update marker visual to show snapped state with device name
        if (markerElement) {
          markerElement.innerHTML = `
            <div class="flex flex-col items-center">
              <div style="
                background: ${color};
                color: white;
                padding: 6px 12px;
                border-radius: 9999px;
                font-size: 11px;
                font-weight: 600;
                box-shadow: 0 0 0 3px white, 0 0 12px ${color}80;
                display: flex;
                align-items: center;
                gap: 4px;
                white-space: nowrap;
              ">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                  <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                ${nearbyDevice.value.name}
              </div>
              <div style="width: 0; height: 0; border-left: 8px solid transparent; border-right: 8px solid transparent; border-top: 10px solid ${color}; margin-top: -2px;"></div>
            </div>
          `
        }

        toast.success(`Tersnap ke ${nearbyDevice.value.name}`)
      } else {
        // Clear snapped device if not near any device
        if (isStartMarker) {
          snappedStartDevice.value = null
        } else {
          snappedEndDevice.value = null
        }

        // Reset marker visual to default
        if (markerElement) {
          const stepNum = isStartMarker ? 1 : 2
          const label = isStartMarker ? 'Titik Awal' : 'Titik Akhir'
          markerElement.innerHTML = `
            <div class="flex flex-col items-center">
              <div style="background: ${color}; color: white; padding: 4px 10px; border-radius: 9999px; font-size: 10px; font-weight: bold; box-shadow: 0 2px 6px rgba(0,0,0,0.3); display: flex; align-items: center; gap: 4px;">
                <span style="background: white; color: ${color}; width: 16px; height: 16px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 10px;">${stepNum}</span>
                ${label}
              </div>
              <div style="width: 0; height: 0; border-left: 8px solid transparent; border-right: 8px solid transparent; border-top: 10px solid ${color}; margin-top: -2px;"></div>
            </div>
          `
        }
      }
    })
  }

  const startCableDrawing = () => {
    if (!map || !AdvancedMarkerElement) return

    pendingDeviceType.value = 'cable'
    cableDrawStep.value = 'selectStart'
    isDrawingCable.value = true
    pendingPath.value = []
    startPoint.value = null
    endPoint.value = null
    snappedStartDevice.value = null
    snappedEndDevice.value = null

    // Disable DrawingManager - we handle manually
    if (drawingManager) drawingManager.setDrawingMode(null)

    // Create draggable marker at center of map
    const center = map.getCenter()
    if (!center) return

    const markerIcon = document.createElement('div')
    markerIcon.innerHTML = `
      <div class="flex flex-col items-center animate-bounce">
        <div style="background-color: #22c55e; border: 3px solid #fff; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.3);">
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="3"/>
            <path d="M12 2v4m0 12v4m10-10h-4M6 12H2"/>
          </svg>
        </div>
        <div style="width: 0; height: 0; border-left: 8px solid transparent; border-right: 8px solid transparent; border-top: 10px solid #fff; margin-top: -2px;"></div>
      </div>
    `

    startPointMarker = new AdvancedMarkerElement({
      position: { lat: center.lat(), lng: center.lng() },
      map: map,
      gmpDraggable: true,
      content: markerIcon,
      zIndex: 1000,
    })

    // Add snap detection listener
    setupMarkerSnapListener(startPointMarker)

    toast.info('Geser pin hijau ke posisi titik awal kabel (snap ke perangkat terdekat)')
  }

  const confirmStartPoint = () => {
    if (!startPointMarker || !map || !AdvancedMarkerElement) return

    let pos = startPointMarker.position as google.maps.LatLngLiteral

    // Check for nearby device if not already snapped (user may not have dragged)
    if (!snappedStartDevice.value) {
      const device = findNearbySnappableDevice(pos)
      if (device) {
        pos = { lat: device.lat, lng: device.lng }
        snappedStartDevice.value = device
        startPointMarker.position = pos
        toast.success(`Snapped ke ${device.name}`)
      }
    } else {
      // Use already snapped position
      pos = { lat: snappedStartDevice.value.lat, lng: snappedStartDevice.value.lng }
    }

    startPoint.value = { lat: pos.lat, lng: pos.lng }
    cableDrawStep.value = 'selectEnd'
    hideSnapHighlight()

    // Change start marker appearance (locked with device name if snapped)
    const lockedIcon = document.createElement('div')
    const labelText = snappedStartDevice.value ? snappedStartDevice.value.name : 'A'
    lockedIcon.innerHTML = `
      <div class="flex flex-col items-center">
        <div style="background-color: #3b82f6; border: 3px solid #fff; width: ${snappedStartDevice.value ? 'auto' : '32px'}; height: 32px; padding: ${snappedStartDevice.value ? '0 8px' : '0'}; border-radius: ${snappedStartDevice.value ? '16px' : '50%'}; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">
          <span style="color: #fff; font-weight: bold; font-size: 11px; white-space: nowrap;">${labelText}</span>
        </div>
        <div style="width: 0; height: 0; border-left: 6px solid transparent; border-right: 6px solid transparent; border-top: 8px solid #fff; margin-top: -2px;"></div>
      </div>
    `
    startPointMarker.gmpDraggable = false
    startPointMarker.content = lockedIcon

    // Create end point marker at slightly offset position
    const endIcon = document.createElement('div')
    endIcon.innerHTML = `
      <div class="flex flex-col items-center animate-bounce">
        <div style="background-color: #ef4444; border: 3px solid #fff; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.3);">
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="3"/>
            <path d="M12 2v4m0 12v4m10-10h-4M6 12H2"/>
          </svg>
        </div>
        <div style="width: 0; height: 0; border-left: 8px solid transparent; border-right: 8px solid transparent; border-top: 10px solid #fff; margin-top: -2px;"></div>
      </div>
    `

    const bounds = map.getBounds()
    const offsetLat = bounds ? (bounds.getNorthEast().lat() - bounds.getSouthWest().lat()) * 0.1 : 0.001

    endPointMarker = new AdvancedMarkerElement({
      position: { lat: pos.lat + offsetLat, lng: pos.lng },
      map: map,
      gmpDraggable: true,
      content: endIcon,
      zIndex: 1000,
    })

    // Add snap detection listener to end marker
    setupMarkerSnapListener(endPointMarker, false)

    toast.info('Geser pin merah ke posisi titik akhir kabel (snap ke perangkat terdekat)')
  }

  const confirmEndPoint = async () => {
    if (!endPointMarker || !startPoint.value || !map || !GooglePolyline) return

    let pos = endPointMarker.position as google.maps.LatLngLiteral

    // Check for nearby device if not already snapped (user may not have dragged)
    if (!snappedEndDevice.value) {
      const device = findNearbySnappableDevice(pos)
      if (device) {
        pos = { lat: device.lat, lng: device.lng }
        snappedEndDevice.value = device
        endPointMarker.position = pos
        toast.success(`Snapped ke ${device.name}`)
      }
    } else {
      // Use already snapped position
      pos = { lat: snappedEndDevice.value.lat, lng: snappedEndDevice.value.lng }
    }

    endPoint.value = { lat: pos.lat, lng: pos.lng }
    hideSnapHighlight()

    // Change end marker appearance (locked with device name if snapped)
    const lockedIcon = document.createElement('div')
    const labelText = snappedEndDevice.value ? snappedEndDevice.value.name : 'B'
    lockedIcon.innerHTML = `
      <div class="flex flex-col items-center">
        <div style="background-color: #ef4444; border: 3px solid #fff; width: ${snappedEndDevice.value ? 'auto' : '32px'}; height: 32px; padding: ${snappedEndDevice.value ? '0 8px' : '0'}; border-radius: ${snappedEndDevice.value ? '16px' : '50%'}; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">
          <span style="color: #fff; font-weight: bold; font-size: 11px; white-space: nowrap;">${labelText}</span>
        </div>
        <div style="width: 0; height: 0; border-left: 6px solid transparent; border-right: 6px solid transparent; border-top: 8px solid #fff; margin-top: -2px;"></div>
      </div>
    `
    endPointMarker.gmpDraggable = false
    endPointMarker.content = lockedIcon

    let path: google.maps.LatLngLiteral[] = []

    // Try to get route from Directions API if auto-route is enabled
    if (autoRouteMode.value) {
      isLoadingRoute.value = true
      try {
        const directionsService = new google.maps.DirectionsService()
        const result = await directionsService.route({
          origin: { lat: startPoint.value.lat, lng: startPoint.value.lng },
          destination: { lat: endPoint.value.lat, lng: endPoint.value.lng },
          travelMode: google.maps.TravelMode.DRIVING,
        })

        if (result.routes && result.routes.length > 0 && result.routes[0].overview_path) {
          const rawPath = result.routes[0].overview_path.map((p) => ({ lat: p.lat(), lng: p.lng() }))
          // Simplify path to reduce number of points for easier editing
          path = simplifyPath(rawPath, 0.0001) // tolerance ~10m
          toast.success(`Jalur dibuat dengan ${path.length} titik (dari ${rawPath.length})`)
        } else {
          // Fallback to straight line
          path = [
            { lat: startPoint.value.lat, lng: startPoint.value.lng },
            { lat: endPoint.value.lat, lng: endPoint.value.lng },
          ]
          toast.warning('Tidak dapat menemukan rute jalan, menggunakan garis lurus')
        }
      } catch (error) {
        console.error('Directions API error:', error)
        // Fallback to straight line
        path = [
          { lat: startPoint.value.lat, lng: startPoint.value.lng },
          { lat: endPoint.value.lat, lng: endPoint.value.lng },
        ]
        toast.warning('Gagal mendapatkan rute jalan, menggunakan garis lurus')
      } finally {
        isLoadingRoute.value = false
      }
    } else {
      // Manual mode: straight line
      path = [
        { lat: startPoint.value.lat, lng: startPoint.value.lng },
        { lat: endPoint.value.lat, lng: endPoint.value.lng },
      ]
    }

    // Insert pole waypoints along the path
    const originalLength = path.length
    path = insertPoleWaypoints(path)
    const polesAdded = path.length - originalLength
    if (polesAdded > 0) {
      toast.info(`Jalur kabel melewati ${polesAdded} tiang`)
    }

    cableDrawStep.value = 'editPath'

    drawPolyline = new GooglePolyline({
      path: path,
      geodesic: true,
      strokeColor: '#3b82f6',
      strokeWeight: 5,
      strokeOpacity: 1,
      editable: true,
      draggable: false,
      map: map,
    })

    // Listen to path changes to update length
    const polyPath = drawPolyline.getPath()
    const updatePathAndLength = () => {
      const newPath: [number, number][] = []
      for (let i = 0; i < polyPath.getLength(); i++) {
        const p = polyPath.getAt(i)
        newPath.push([p.lat(), p.lng()])
      }
      pendingPath.value = newPath

      if (window.google?.maps?.geometry) {
        pendingLength.value = window.google.maps.geometry.spherical.computeLength(drawPolyline!.getPath())
      }
    }

    GoogleEvent.addListener(polyPath, 'insert_at', updatePathAndLength)
    GoogleEvent.addListener(polyPath, 'remove_at', updatePathAndLength)
    GoogleEvent.addListener(polyPath, 'set_at', updatePathAndLength)

    // Initialize path and length
    updatePathAndLength()

    toast.info('Anda bisa menggeser titik-titik di jalur kabel. Klik centang jika sudah selesai.')
  }

  const cancelCableStepDrawing = () => {
    cableDrawStep.value = 'idle'
    isDrawingCable.value = false
    pendingDeviceType.value = null
    pendingPath.value = []
    pendingLength.value = 0
    startPoint.value = null
    endPoint.value = null
    snappedStartDevice.value = null
    snappedEndDevice.value = null
    hideSnapHighlight()
    waypointPolesIds.value = []

    if (startPointMarker) {
      startPointMarker.map = null
      startPointMarker = null
    }
    if (endPointMarker) {
      endPointMarker.map = null
      endPointMarker = null
    }
    if (drawPolyline) {
      drawPolyline.setMap(null)
      drawPolyline = null
    }
    if (drawingManager) drawingManager.setDrawingMode(null)

    loadMapData()
  }

  const finalizeCableStepDrawing = () => {
    if (pendingPath.value.length < 2) {
      toast.error('Jalur kabel harus memiliki minimal 2 titik')
      return
    }

    pendingLat.value = pendingPath.value[0][0]
    pendingLng.value = pendingPath.value[0][1]
    showCreateModal.value = true
  }

  const handleCableCreated = () => {
    showCreateModal.value = false
    cancelCableStepDrawing()
    toast.success('Kabel berhasil disimpan')
  }

  watch(
    () => pendingDeviceType.value,
    (type) => {
      if (!drawingManager || !map || !AdvancedMarkerElement) return

      if (!type) {
        drawingManager.setDrawingMode(null)
        isDrawingCable.value = false
        if (tempMarker) {
          tempMarker.map = null
          tempMarker = null
        }
        return
      }

      // For cable, we now use step-by-step drawing, skip DrawingManager
      if (type === 'cable') {
        // Don't use DrawingManager for cables anymore
        return
      } else {
        // Mobile-friendly placement: Place marker at center immediately
        const center = map.getCenter()
        if (!center) return

        pendingLat.value = center.lat()
        pendingLng.value = center.lng()

        // Remove old temp if exists
        if (tempMarker) tempMarker.map = null

        // Custom marker content (matching cable placement style)
        const deviceColor = (colors as any)[type] || colors.blue
        const markerIcon = document.createElement('div')
        markerIcon.innerHTML = `
          <div class="flex flex-col items-center animate-bounce">
            <div style="background-color: ${deviceColor}; border: 3px solid #fff; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.3);">
              <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="3"/>
                <path d="M12 2v4m0 12v4m10-10h-4M6 12H2"/>
              </svg>
            </div>
            <div style="width: 0; height: 0; border-left: 8px solid transparent; border-right: 8px solid transparent; border-top: 10px solid #fff; margin-top: -2px;"></div>
          </div>
        `

        tempMarker = new AdvancedMarkerElement({
          map: map,
          position: { lat: pendingLat.value, lng: pendingLng.value },
          gmpDraggable: true,
          content: markerIcon,
          title: `Letakkan ${type.toUpperCase()}`,
        })

        tempMarker.addListener('dragend', () => {
          const pos = tempMarker?.position as google.maps.LatLngLiteral
          if (pos) {
            pendingLat.value = pos.lat
            pendingLng.value = pos.lng
          }
        })

        // Disable DrawingManager default marker placement
        drawingManager.setDrawingMode(null)

        toast.info(`Geser marker ke lokasi ${type.toUpperCase()}, lalu klik centang.`)
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
              <DropdownMenuItem
                v-for="type in deviceTypes.passive"
                :key="type.value"
                @click="type.value === 'cable' ? startCableDrawing() : (pendingDeviceType = type.value)"
                class="cursor-pointer">
                <div class="flex items-center gap-2">
                  <div class="h-2 w-2 rounded-full" :style="{ backgroundColor: (colors as any)[type.value] || colors.blue }"></div>
                  {{ type.label }}
                </div>
              </DropdownMenuItem>
            </DropdownMenuGroup>
          </DropdownMenuContent>
        </DropdownMenu>
      </div>

      <!-- Step-by-Step Cable Drawing UI -->
      <div v-if="cableDrawStep !== 'idle'" class="absolute bottom-10 left-1/2 z-[1000] -translate-x-1/2">
        <div
          class="flex items-center gap-3 rounded-2xl bg-white px-5 py-3 shadow-2xl ring-1 ring-black/10 animate-in fade-in zoom-in slide-in-from-bottom-4">
          <!-- Step Indicator -->
          <div class="flex items-center gap-2">
            <div
              :class="[
                'flex h-6 w-6 items-center justify-center rounded-full text-xs font-bold',
                cableDrawStep === 'selectStart' ? 'bg-green-500 text-white' : 'bg-blue-500 text-white',
              ]">
              {{ cableDrawStep === 'selectStart' ? '1' : cableDrawStep === 'selectEnd' ? '2' : '3' }}
            </div>
            <div class="text-sm font-semibold text-gray-700">
              <span v-if="cableDrawStep === 'selectStart'">Tentukan Titik Awal</span>
              <span v-else-if="cableDrawStep === 'selectEnd'">Tentukan Titik Akhir</span>
              <span v-else-if="cableDrawStep === 'editPath'">Edit Jalur Kabel</span>
            </div>
          </div>

          <!-- Length Display -->
          <div v-if="pendingLength > 0" class="flex items-center gap-2 border-l pl-3">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="16"
              height="16"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              class="text-blue-500">
              <path d="M2 12h20M12 2l10 10-10 10" />
            </svg>
            <span class="text-sm font-black text-blue-600">{{ pendingLength.toFixed(1) }} m</span>
          </div>

          <!-- Snap Indicator (shows when near a device) -->
          <div
            v-if="nearbyDevice && (cableDrawStep === 'selectStart' || cableDrawStep === 'selectEnd')"
            class="flex items-center gap-2 border-l pl-3">
            <div class="flex items-center gap-1.5 rounded-full bg-green-100 px-3 py-1">
              <div class="h-2 w-2 animate-pulse rounded-full bg-green-500"></div>
              <span class="text-xs font-semibold text-green-700">Snap: {{ nearbyDevice.name }}</span>
            </div>
          </div>

          <!-- Auto-Route Toggle (only show before editPath step) -->
          <div v-if="cableDrawStep !== 'editPath'" class="flex items-center gap-2 border-l pl-3">
            <label class="flex cursor-pointer items-center gap-2">
              <UiSwitch :checked="autoRouteMode" @update:checked="autoRouteMode = $event" />
              <span class="text-xs font-medium text-gray-600">Ikuti Jalan</span>
            </label>
          </div>

          <!-- Loading Indicator -->
          <div v-if="isLoadingRoute" class="flex items-center gap-2 border-l pl-3">
            <svg class="h-4 w-4 animate-spin text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-xs text-gray-500">Mencari rute...</span>
          </div>

          <div class="h-6 w-px bg-gray-200"></div>

          <!-- Action Buttons -->
          <div class="flex items-center gap-2">
            <!-- Confirm Start Point -->
            <button
              v-if="cableDrawStep === 'selectStart'"
              @click="confirmStartPoint"
              class="flex h-9 items-center gap-2 rounded-full bg-green-500 px-4 text-sm font-semibold text-white shadow-sm transition-all hover:bg-green-600 hover:scale-105 active:scale-95">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="16"
                height="16"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="3"
                stroke-linecap="round"
                stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"></polyline>
              </svg>
              Konfirmasi
            </button>

            <!-- Confirm End Point -->
            <button
              v-if="cableDrawStep === 'selectEnd'"
              @click="confirmEndPoint"
              class="flex h-9 items-center gap-2 rounded-full bg-red-500 px-4 text-sm font-semibold text-white shadow-sm transition-all hover:bg-red-600 hover:scale-105 active:scale-95">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="16"
                height="16"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="3"
                stroke-linecap="round"
                stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"></polyline>
              </svg>
              Konfirmasi
            </button>

            <!-- Finish Drawing -->
            <button
              v-if="cableDrawStep === 'editPath'"
              @click="finalizeCableStepDrawing"
              class="flex h-9 items-center gap-2 rounded-full bg-blue-500 px-4 text-sm font-semibold text-white shadow-sm transition-all hover:bg-blue-600 hover:scale-105 active:scale-95">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="16"
                height="16"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="3"
                stroke-linecap="round"
                stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"></polyline>
              </svg>
              Selesai
            </button>

            <!-- Cancel -->
            <button
              @click="cancelCableStepDrawing"
              class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-gray-600 transition-all hover:bg-red-100 hover:text-red-500 hover:scale-105 active:scale-95"
              title="Batal">
              <Plus class="h-5 w-5 rotate-45" />
            </button>
          </div>
        </div>
      </div>

      <div
        v-if="((pendingDeviceType && pendingDeviceType !== 'cable') || relocatingDevice) && !showCreateModal"
        class="absolute bottom-6 left-1/2 z-[1000] w-[90%] -translate-x-1/2 sm:bottom-10 sm:w-auto">
        <div
          class="flex items-center justify-between gap-3 rounded-full bg-white px-4 py-2 shadow-2xl ring-1 ring-black/10 animate-in fade-in zoom-in slide-in-from-bottom-4 sm:justify-start">
          <span v-if="pendingDeviceType" class="truncate text-[10px] font-bold text-gray-600 uppercase tracking-tight sm:text-xs">
            Menambah {{ pendingDeviceType }}
          </span>
          <span v-else-if="relocatingDevice" class="truncate text-[10px] font-bold text-blue-600 uppercase tracking-tight sm:text-xs">
            Pindah Lokasi: {{ relocatingDevice.name }}
          </span>

          <div v-if="relocatingDevice && relocatingDevice.type === 'cable' && pendingLength > 0" class="flex items-center gap-2">
            <div class="h-5 w-px bg-gray-200"></div>
            <span class="text-[10px] font-black text-blue-600 sm:text-xs">{{ pendingLength.toFixed(2) }}m</span>
          </div>
          <div class="h-5 w-px bg-gray-200"></div>

          <div class="flex items-center gap-2">
            <!-- Confirm Placement (Show Modal) -->
            <button
              v-if="pendingDeviceType"
              @click="showCreateModal = true"
              class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-500 text-white shadow-sm transition-all hover:bg-blue-600 hover:scale-110 active:scale-95"
              title="Konfirmasi Lokasi">
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

            <!-- Finalize Relocation -->
            <button
              v-else-if="relocatingDevice"
              @click="() => (relocatingDevice.type === 'cable' ? saveEditedCable() : saveRelocation())"
              class="flex h-9 items-center gap-2 rounded-full bg-green-500 px-4 text-white shadow-sm transition-all hover:bg-green-600 hover:scale-110 active:scale-95"
              title="Simpan Perubahan">
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
              <span class="hidden text-xs font-bold sm:inline">Simpan</span>
            </button>

            <!-- Cancel -->
            <button
              @click="cancelAddingDevice"
              class="flex h-9 w-9 items-center justify-center rounded-full bg-red-50 text-red-500 transition-all hover:bg-red-500 hover:text-white hover:scale-110 active:scale-95"
              title="Batal">
              <Plus class="h-5 w-5 rotate-45" />
            </button>
          </div>
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
      :start-node="snappedStartDevice ? { id: snappedStartDevice.id, type: snappedStartDevice.type, name: snappedStartDevice.name } : null"
      :end-node="snappedEndDevice ? { id: snappedEndDevice.id, type: snappedEndDevice.type, name: snappedEndDevice.name } : null"
      :waypoint-poles="waypointPolesIds"
      @success="handleCreateSuccess"
      @cancel="cancelAddingDevice" />

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
