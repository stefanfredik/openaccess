<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

declare global {
    interface Window {
        startRelocation: (id: number, type: string, name: string) => void;
        editCable: (id: number) => void;
    }
}

import { onMounted, ref, watch } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import axios from 'axios';
import { Info, MapPin, Plus } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Switch } from '@/components/ui/switch';
import { Label } from '@/components/ui/label';
import { toast } from 'vue-sonner';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import DeviceCreateModal from './Components/DeviceCreateModal.vue';

const props = defineProps<{
    areas: Array<{ id: number; name: string; boundary: any }>;
    pops: Array<{ id: number; name: string }>;
}>();

const mapContainer = ref<HTMLElement | null>(null);
const selectedAreaId = ref<string>(localStorage.getItem('map_selected_area') || 'all');
const pendingDeviceType = ref<string | null>(null);
const showCreateModal = ref(false);
const showWarningModal = ref(false);
const showLegend = ref(localStorage.getItem('map_show_legend') !== 'false');
const relocatingDevice = ref<{ id: number; type: string; name: string } | null>(null);
const relocatingMarker = ref<L.Marker | null>(null);
const originalPosition = ref<L.LatLng | null>(null);
const currentPosition = ref<L.LatLng | null>(null);
const warningMessage = ref('');
const pendingLat = ref<number | null>(null);
const pendingLng = ref<number | null>(null);
const pendingPath = ref<Array<[number, number]>>([]);
const isDrawingCable = ref(false);
const mapData = ref<any>(null); // Store fetched GeoJSON

const deviceFilters = ref({
    infrastructure: true,
    active: true,
    passive: true,
    customer: true,
    cables: true,
});

const deviceTypes = {
    active: [
        { label: 'OLT', value: 'olt' },
        { label: 'ONT', value: 'ont' },
        { label: 'Router', value: 'router' },
        { label: 'Switch', value: 'switch' },
        { label: 'Access Point', value: 'access-point' },
    ],
    passive: [
        { label: 'ODP', value: 'odp' },
        { label: 'ODF', value: 'odf' },
        { label: 'Pole', value: 'pole' },
        { label: 'Tower', value: 'tower' },
        { label: 'Joint Box', value: 'joint-box' },
        { label: 'Jalur Kabel', value: 'cable' },
    ]
};

// Helper to create valid SVG string (using Lucide style paths)
const getIconHtml = (color: string, svgPath: string) => `
    <div style="background-color: white; border: 2px solid ${color}; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="${color}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            ${svgPath}
        </svg>
    </div>
`;

const createIcon = (color: string, svgPath: string) => L.divIcon({
    className: 'custom-map-icon',
    html: getIconHtml(color, svgPath),
    iconSize: [32, 32],
    iconAnchor: [16, 16],
    popupAnchor: [0, -18]
});

const colors = {
    blue: '#2563eb',
    green: '#16a34a',
    violet: '#7c3aed',
    red: '#dc2626',
    yellow: '#ca8a04',
    grey: '#4b5563',
    orange: '#ea580c',
    gold: '#d97706',
    black: '#000000',
};

const iconPaths = {
    pop: '<path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/>', // Building
    olt: '<rect width="20" height="8" x="2" y="2" rx="2" ry="2"/><rect width="20" height="8" x="2" y="14" rx="2" ry="2"/><line x1="6" x2="6.01" y1="6" y2="6"/><line x1="6" x2="6.01" y1="18" y2="18"/>', // Server
    ont: '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>', // Activity/Signal
    router: '<rect width="20" height="8" x="2" y="14" rx="2"/><path d="M6.01 18h.01"/><path d="M10.01 18h.01"/><path d="M15 10v4"/><path d="M17.84 7.17a4 4 0 0 0-5.66 0"/><path d="M20.66 4.34a8 8 0 0 0-11.31 0"/>', // Router
    switch: '<rect x="2" y="2" width="20" height="8" rx="2" ry="2"/><rect x="2" y="14" width="20" height="8" rx="2" ry="2"/><line x1="6" y1="6" x2="6.01" y2="6"/><line x1="6" y1="18" x2="6.01" y2="18"/>', // Switch
    access_point: '<path d="M12 20h.01"/><path d="M2 8.82a15 15 0 0 1 20 0"/><path d="M5 12.859a10 10 0 0 1 14 0"/><path d="M8.5 16.429a5 5 0 0 1 7 0"/>', // Wifi
    odp: '<path d="M3 3h18v18H3zM9 3v18M15 3v18M3 9h18M3 15h18"/>', // Grid/Box
    odf: '<rect width="18" height="18" x="3" y="3" rx="2"/><path d="M9 3v18"/><path d="M15 3v18"/><path d="M3 9h18"/><path d="M3 15h18"/>', // Rack/Frame
    pole: '<path d="M12 2v20"/><path d="M8 8h8"/><path d="M8 14h8"/>', // Pole
    tower: '<path d="M12 2 8 22h8L12 2z"/><path d="M6 18h12"/><path d="M8 12h8"/><path d="M10 6h4"/>', // Tower
    joint_box: '<path d="M3 7v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2z"/><path d="M3 7l9 6 9-6"/>', // Box
    cpe: '<path d="M15 10v4"/><path d="M17.84 7.17a4 4 0 0 0-5.66 0"/><path d="M20.66 4.34a8 8 0 0 0-11.31 0"/><rect x="2" y="14" width="20" height="8" rx="2"/>', // CPE
};

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
};

let map: L.Map | null = null;
let markersLayer: L.FeatureGroup | null = null;
let boundaryLayer: L.Layer | null = null;
let tempMarker: L.Marker | null = null;
let drawPolyline: L.Polyline | null = null;

const isPointInPolygon = (lat: number, lng: number, polygon: any[]) => {
    // If polygon is nested (e.g. [[[lat, lng], ...]]), take the first ring
    const points = Array.isArray(polygon[0][0]) ? polygon[0] : polygon;
    
    let inside = false;
    for (let i = 0, j = points.length - 1; i < points.length; j = i++) {
        const yi = points[i][0], xi = points[i][1]; // yi=lat, xi=lng
        const yj = points[j][0], xj = points[j][1]; // yj=lat, xj=lng
        
        const intersect = ((yi > lat) !== (yj > lat))
            && (lng < (xj - xi) * (lat - yi) / (yj - yi) + xi);
        if (intersect) inside = !inside;
    }
    return inside;
};



const renderMap = () => {
    if (!map || !mapData.value) return;

    // Clear existing layers
    if (markersLayer) markersLayer.clearLayers();

    const geojson = mapData.value;

    // Filter features based on deviceFilters
    const filteredFeatures = geojson.features.filter((feature: any) => {
        const type = feature.properties.type;
        
        // Map types to filter categories
        if (['pop'].includes(type)) return deviceFilters.value.infrastructure;
        if (['olt', 'ont', 'router', 'switch', 'access_point'].includes(type)) return deviceFilters.value.active;
        if (['odp', 'odf', 'pole', 'tower', 'joint_box'].includes(type)) return deviceFilters.value.passive;
        if (['cpe'].includes(type)) return deviceFilters.value.customer;
        // Check geometry type for cables, as they might be labeled 'cable' or implicitly line strings
        if (feature.geometry.type === 'LineString' || type === 'cable') return deviceFilters.value.cables;
        
        return true; // Default show
    });

    const filteredGeojson = { ...geojson, features: filteredFeatures };

    // Create markers and lines
    const geojsonLayer = L.geoJSON(filteredGeojson, {
        style: (feature) => {
            if (feature?.geometry.type === 'LineString') {
                return {
                    color: '#3b82f6',
                    weight: 6,
                    opacity: 0.8,
                    interactive: true,
                };
            }
            return {};
        },
        pointToLayer: (feature, latlng) => {
            const deviceType = feature.properties.type;
            const icon = mapIcons[deviceType] || mapIcons.pop;
            return L.marker(latlng, { icon });
        },
        onEachFeature: (feature, layer) => {
            const props = feature.properties;
            let popupContent = `
                <div class="p-2 min-w-[200px]">
                    <h3 class="font-bold text-lg mb-2">${props.name}</h3>
                    <p class="text-sm"><strong>Type:</strong> ${props.type.toUpperCase().replace('_', ' ')}</p>
                    ${props.address ? `<p class="text-sm"><strong>Address:</strong> ${props.address}</p>` : ''}
                    <p class="text-sm mb-3"><strong>Status:</strong> <span class="text-green-600">${props.status}</span></p>
            `;

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
                `;
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
                `;
            }

            popupContent += `</div>`;
            layer.bindPopup(popupContent);
            
            layer.on({
                mouseover: (e) => {
                    const l = e.target;
                    if (feature.geometry.type === 'LineString') { // Highlight Lines
                        l.setStyle({ weight: 8, color: '#2563eb' });
                        l.bringToFront();
                    }
                },
                mouseout: (e) => {
                    const l = e.target;
                    if (feature.geometry.type === 'LineString') { // Reset Lines
                        geojsonLayer.resetStyle(l);
                    }
                }
            });
        }
    });

    markersLayer?.addLayer(geojsonLayer);
};

const loadMapData = async () => {
    try {
        const url = selectedAreaId.value === 'all' 
            ? '/maps/data' 
            : `/maps/data?area_id=${selectedAreaId.value}`;
            
        const response = await axios.get(url);
        mapData.value = response.data;
        
        renderMap();

        // Handle Boundary and Zoom logic remains same but using mapData.value
        if (selectedAreaId.value !== 'all') {
             // ... existing boundary logic ...
             if (boundaryLayer) {
                map?.removeLayer(boundaryLayer);
                boundaryLayer = null;
            }
            const area = props.areas.find(a => a.id === parseInt(selectedAreaId.value));
            if (area && area.boundary) {
                boundaryLayer = L.polygon(area.boundary, {
                    color: '#3b82f6',
                    weight: 2,
                    opacity: 0.5,
                    fillOpacity: 0.2,
                    interactive: false 
                }).addTo(map!);
                boundaryLayer.bringToBack();
                
                map?.fitBounds((boundaryLayer as L.Polygon).getBounds());
            } 
        } else if (mapData.value.features && mapData.value.features.length > 0) {
             // We can't really fit bounds easily without creating a layer first, 
             // but markersLayer already has the layer now.
             if (markersLayer && markersLayer.getBounds().isValid()) {
                 map?.fitBounds(markersLayer.getBounds());
             }
        }

    } catch (error) {
        console.error('Failed to load map data:', error);
    }
};

watch(deviceFilters, () => {
    renderMap();
}, { deep: true });

const initMap = async () => {
    if (!mapContainer.value) return;

    // Load persisted view
    const savedLat = localStorage.getItem('map_center_lat');
    const savedLng = localStorage.getItem('map_center_lng');
    const savedZoom = localStorage.getItem('map_zoom');

    const defaultLat = savedLat ? parseFloat(savedLat) : -2.5489;
    const defaultLng = savedLng ? parseFloat(savedLng) : 118.0149;
    const defaultZoom = savedZoom ? parseInt(savedZoom) : 5;

    // Initialize map
    map = L.map(mapContainer.value).setView([defaultLat, defaultLng], defaultZoom); 

    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19,
    }).addTo(map!);

    // PERSISTENCE: Listen for map view changes
    map!.on('moveend', () => {
        const center = map!.getCenter();
        localStorage.setItem('map_center_lat', center.lat.toString());
        localStorage.setItem('map_center_lng', center.lng.toString());
    });

    map!.on('zoomend', () => {
        localStorage.setItem('map_zoom', map!.getZoom().toString());
    });

    map!.on('click', (e: L.LeafletMouseEvent) => {
        if (isDrawingCable.value) {
            // Add point to path
            pendingPath.value.push([e.latlng.lat, e.latlng.lng]);
            
            if (!drawPolyline) {
                drawPolyline = L.polyline(pendingPath.value, { 
                    color: '#3b82f6', 
                    weight: 4,
                    dashArray: '5, 10'
                }).addTo(map!);
            } else {
                drawPolyline.setLatLngs(pendingPath.value as L.LatLngExpression[]);
            }
            redrawEditMarkers();
            return;
        }

        if (pendingDeviceType.value && pendingDeviceType.value !== 'cable') {
            console.log('Map Clicked:', e.latlng.lat, e.latlng.lng, 'Selected Area:', selectedAreaId.value);
            
            // Check if within selected area boundary
            if (selectedAreaId.value !== 'all') {
                const area = props.areas.find(a => a.id === parseInt(selectedAreaId.value));
                if (area && area.boundary) {
                    const isInside = isPointInPolygon(e.latlng.lat, e.latlng.lng, area.boundary);
                    console.log('Boundary Check:', isInside, area.name);
                    
                    if (!isInside) {
                        warningMessage.value = `Lokasi yang anda pilih berada di luar area ${area.name}. Silahkan pilih titik di dalam area yang telah ditentukan.`;
                        showWarningModal.value = true;
                        return;
                    }
                }
            }

            pendingLat.value = e.latlng.lat;
            pendingLng.value = e.latlng.lng;
            
            // Show Pin Point
            if (tempMarker) map?.removeLayer(tempMarker);
            tempMarker = L.marker(e.latlng, {
                icon: createIcon(colors.blue, '<path d="M12 5v14M5 12h14"/>') // Plus icon for new device
            }).addTo(map!);

            showCreateModal.value = true;
        }
    });

    markersLayer = new L.FeatureGroup().addTo(map!);

    await loadMapData();
};

const saveRelocation = async () => {
    if (!relocatingDevice.value || !currentPosition.value) return;

    try {
        await axios.post('/maps/relocate', {
            id: relocatingDevice.value.id,
            type: relocatingDevice.value.type,
            lat: currentPosition.value.lat,
            lng: currentPosition.value.lng
        });
        toast.success('Posisi berhasil diperbarui');
        
        if (relocatingMarker.value) {
            relocatingMarker.value.dragging?.disable();
        }
        
        relocatingDevice.value = null;
        relocatingMarker.value = null;
        loadMapData();
    } catch (error) {
        toast.error('Gagal memperbarui posisi');
    }
};

const cancelRelocation = () => {
    if (relocatingMarker.value && originalPosition.value) {
        relocatingMarker.value.setLatLng(originalPosition.value);
        relocatingMarker.value.dragging?.disable();
    }
    relocatingDevice.value = null;
    relocatingMarker.value = null;
};

// Global function for relocation (called from popup string)
window.startRelocation = (id: number, type: string, name: string) => {
    if (!map || !markersLayer) return;

    // Find the marker in markersLayer
    let targetMarker: L.Marker | null = null;
    markersLayer.eachLayer((layer: any) => {
        if (layer instanceof L.GeoJSON) {
            layer.eachLayer((marker: any) => {
                if (marker instanceof L.Marker && marker.feature?.properties?.id === id && marker.feature?.properties?.type === type) {
                    targetMarker = marker;
                }
            });
        }
    });

    if (targetMarker) {
        relocatingDevice.value = { id, type, name };
        relocatingMarker.value = targetMarker;
        originalPosition.value = (targetMarker as L.Marker).getLatLng();
        currentPosition.value = (targetMarker as L.Marker).getLatLng();

        (targetMarker as L.Marker).dragging?.enable();
        (targetMarker as L.Marker).on('dragend', (e: any) => {
            currentPosition.value = e.target.getLatLng();
        });

        map.closePopup();
        toast.info(`Pin ${name} sekarang bisa di-drag. Geser ke lokasi baru.`, {
            position: 'top-center',
            duration: 5000
        });
    }

};
const editMarkers = ref<L.Marker[]>([]);

const redrawEditMarkers = () => {
    // Clear existing markers
    editMarkers.value.forEach(marker => map?.removeLayer(marker));
    editMarkers.value = [];

    if (!map) return;

    // Create markers for each point in pendingPath
    pendingPath.value.forEach((point, index) => {
        const marker = L.marker(point, {
            draggable: true,
            icon: L.divIcon({
                className: 'bg-transparent',
                html: `<div class="w-3 h-3 bg-white border-2 border-blue-600 rounded-full shadow-sm hover:scale-125 transition-transform"></div>`,
                iconSize: [12, 12],
                iconAnchor: [6, 6]
            })
        });

        marker.on('drag', (e: any) => {
            const newLatLng = e.target.getLatLng();
            pendingPath.value[index] = [newLatLng.lat, newLatLng.lng];
            
            // Update polyline
            if (drawPolyline) {
                drawPolyline.setLatLngs(pendingPath.value as L.LatLngExpression[]);
            }
        });
        
        // Optional: Right click to remove point
        marker.on('contextmenu', () => {
             if (pendingPath.value.length <= 2) {
                 toast.error('Jalur harus memiliki minimal 2 titik');
                 return;
             }
             pendingPath.value.splice(index, 1);
             if (drawPolyline) {
                drawPolyline.setLatLngs(pendingPath.value as L.LatLngExpression[]);
             }
             redrawEditMarkers(); // Re-index everything
        });

        marker.addTo(map!);
        editMarkers.value.push(marker);
    });
};

// Global function for editing cable path
window.editCable = (id: number) => {
    if (!map || !markersLayer) return;

    // Find the feature
    let targetFeature: any = null;
    let targetLayer: L.Polyline | null = null;

    markersLayer.eachLayer((layer: any) => {
        if (layer instanceof L.GeoJSON) {
            layer.eachLayer((l: any) => {
                if (l instanceof L.Polyline && l.feature?.properties?.id === id && l.feature?.properties?.type === 'cable') {
                    targetFeature = l.feature;
                    targetLayer = l;
                }
            });
        }
    });

    if (targetFeature && targetLayer) {
        relocatingDevice.value = {
            id: targetFeature.properties.id,
            type: 'cable',
            name: targetFeature.properties.name
        };

        const coords = targetFeature.geometry.coordinates.map((c: any) => [c[1], c[0]]);
        pendingPath.value = coords;

        isDrawingCable.value = true;
        
        if (drawPolyline) {
             map.removeLayer(drawPolyline);
        }

        drawPolyline = L.polyline(pendingPath.value, { 
            color: '#3b82f6', 
            weight: 4,
            dashArray: '5, 10'
        }).addTo(map);

        redrawEditMarkers();
        
        map.closePopup();
        toast.info(`Mengedit jalur kabel "${targetFeature.properties.name}". Geser titik putih untuk ubah jalur. Klik kanan titik untuk hapus.`, {
            position: 'top-center',
            duration: 5000
        });
    }
};

const saveEditedCable = async () => {
    if (!relocatingDevice.value || !pendingPath.value.length) return;

    try {
        await axios.post('/maps/relocate', {
            id: relocatingDevice.value.id,
            type: 'cable',
            path: pendingPath.value 
        });
        toast.success('Jalur kabel berhasil diperbarui');
        
        // Reset state
        isDrawingCable.value = false;
        pendingPath.value = [];
        if (drawPolyline) {
            map?.removeLayer(drawPolyline);
            drawPolyline = null;
        }
        editMarkers.value.forEach(marker => map?.removeLayer(marker));
        editMarkers.value = [];
        
        relocatingDevice.value = null;
        
        loadMapData();
    } catch (error) {
        toast.error('Gagal memperbarui jalur kabel');
        console.error(error);
    }
};

const finalizeCableDrawing = () => {
    if (pendingPath.value.length < 2) {
        toast.error('Gambarkan minimal 2 titik untuk jalur kabel');
        return;
    }
    
    // Set center for the modal coordinates (average or first point)
    pendingLat.value = pendingPath.value[0][0];
    pendingLng.value = pendingPath.value[0][1];
    
    editMarkers.value.forEach(marker => map?.removeLayer(marker));
    editMarkers.value = [];
    
    showCreateModal.value = true;
};

const cancelCableDrawing = () => {
    isDrawingCable.value = false;
    pendingDeviceType.value = null;
    pendingPath.value = [];
    if (drawPolyline) {
        map?.removeLayer(drawPolyline);
        drawPolyline = null;
    }
    editMarkers.value.forEach(marker => map?.removeLayer(marker));
    editMarkers.value = [];
};

onMounted(() => {
    initMap();
});

watch(pendingDeviceType, (newVal) => {
    if (newVal === 'cable') {
        isDrawingCable.value = true;
        pendingPath.value = [];
        if (drawPolyline) {
            map?.removeLayer(drawPolyline);
            drawPolyline = null;
        }
        editMarkers.value.forEach(marker => map?.removeLayer(marker));
        editMarkers.value = [];
        
        toast.info('Mode Gambar Kabel: Klik di peta untuk membuat jalur. Klik "Selesai" untuk menyimpan.', {
            duration: 5000,
            position: 'top-center'
        });
    } else {
        isDrawingCable.value = false;
    }
});

watch(showCreateModal, (val: boolean) => {
    if (!val) {
        if (pendingDeviceType.value === 'cable') {
             // If canceled modal while drawing
             // we keep it or clear it based on UX. 
             // Let's clear for now if it was successfully saved or explicitly canceled.
        }

        if (tempMarker && map) {
            map.removeLayer(tempMarker);
            tempMarker = null;
        }
    }
});

watch(selectedAreaId, (newVal) => {
    localStorage.setItem('map_selected_area', newVal);
});

watch(showLegend, (newVal) => {
    localStorage.setItem('map_show_legend', newVal.toString());
});
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Network Map', href: '/maps' }]">
        <Head title="Network Map" />
        
        <div :class="['map-page-container', { 'cursor-pin': pendingDeviceType }]">
            <div ref="mapContainer" class="absolute inset-0 z-0"></div>
            
            <!-- Map Controls Overlay (Top Right) -->
            <div class="absolute top-4 right-4 z-[1000] flex flex-col items-end gap-2">
                <!-- Legend Toggle Button -->
                <Button 
                    variant="secondary" 
                    size="sm" 
                    class="shadow-md bg-white border-blue-100 hover:bg-blue-50 text-blue-600 font-semibold gap-2 h-9"
                    @click="showLegend = !showLegend"
                >
                    <Info class="h-4 w-4" />
                    {{ showLegend ? 'Sembunyikan Legend' : 'Tampilkan Legend' }}
                </Button>

                <!-- Info Overlay -->
                <div class="bg-white rounded-lg shadow-lg p-4 space-y-4 max-h-[70vh] overflow-y-auto w-64">
                    <div class="space-y-2">
                        <h3 class="font-bold text-sm mb-1 text-gray-900">Filter by Area</h3>
                        <select 
                            v-model="selectedAreaId" 
                            @change="loadMapData"
                            class="w-full text-xs rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="all">All Areas</option>
                            <option v-for="area in areas" :key="area.id" :value="area.id">
                                {{ area.name }}
                            </option>
                        </select>
                    </div>

                    <div class="space-y-3">
                        <h3 class="font-bold text-sm text-gray-900 border-b pb-1">Filter Perangkat</h3>
                        
                        <div class="flex items-center justify-between">
                            <Label for="filter-infra" class="text-xs cursor-pointer">Infrastructure (POP)</Label>
                            <Switch id="filter-infra" :checked="deviceFilters.infrastructure" @update:checked="(v) => deviceFilters.infrastructure = v" />
                        </div>
                        <div class="flex items-center justify-between">
                            <Label for="filter-active" class="text-xs cursor-pointer">Active Devices</Label>
                            <Switch id="filter-active" :checked="deviceFilters.active" @update:checked="(v) => deviceFilters.active = v" />
                        </div>
                        <div class="flex items-center justify-between">
                            <Label for="filter-passive" class="text-xs cursor-pointer">Passive Devices</Label>
                            <Switch id="filter-passive" :checked="deviceFilters.passive" @update:checked="(v) => deviceFilters.passive = v" />
                        </div>
                        <div class="flex items-center justify-between">
                            <Label for="filter-customer" class="text-xs cursor-pointer">Customer (CPE)</Label>
                            <Switch id="filter-customer" :checked="deviceFilters.customer" @update:checked="(v) => deviceFilters.customer = v" />
                        </div>
                        <div class="flex items-center justify-between">
                            <Label for="filter-cables" class="text-xs cursor-pointer">Cables</Label>
                            <Switch id="filter-cables" :checked="deviceFilters.cables" @update:checked="(v) => deviceFilters.cables = v" />
                        </div>
                    </div>

                    <div v-show="showLegend" class="border-t pt-2 space-y-2 animate-in fade-in slide-in-from-top-2 duration-300">
                        <h3 class="font-bold text-sm mb-2 text-gray-900">Legend</h3>
                        
                        <div class="text-xs font-semibold text-gray-600 mt-2">Infrastructure</div>
                        <div class="flex items-center gap-2 text-xs">
                             <div class="w-6 h-6 rounded-full border border-blue-500 flex items-center justify-center bg-white shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" v-html="iconPaths.pop"></svg>
                            </div>
                            <span>POP</span>
                        </div>
                        
                        <div class="text-xs font-semibold text-gray-600 mt-2">Active Devices</div>
                        <div class="flex items-center gap-2 text-xs">
                            <div class="w-6 h-6 rounded-full border border-green-500 flex items-center justify-center bg-white shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" v-html="iconPaths.olt"></svg>
                            </div>
                            <span>OLT</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                            <div class="w-6 h-6 rounded-full border border-violet-500 flex items-center justify-center bg-white shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" v-html="iconPaths.ont"></svg>
                            </div>
                            <span>ONT</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                            <div class="w-6 h-6 rounded-full border border-red-500 flex items-center justify-center bg-white shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" v-html="iconPaths.router"></svg>
                            </div>
                            <span>Router</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                           <div class="w-6 h-6 rounded-full border border-yellow-500 flex items-center justify-center bg-white shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#ca8a04" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" v-html="iconPaths.switch"></svg>
                            </div>
                            <span>Switch</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                            <div class="w-6 h-6 rounded-full border border-gray-500 flex items-center justify-center bg-white shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#4b5563" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" v-html="iconPaths.access_point"></svg>
                            </div>
                            <span>Access Point</span>
                        </div>
                        
                        <div class="text-xs font-semibold text-gray-600 mt-2">Passive Devices</div>
                        <div class="flex items-center gap-2 text-xs">
                             <div class="w-6 h-6 rounded-full border border-orange-500 flex items-center justify-center bg-white shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#ea580c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" v-html="iconPaths.odp"></svg>
                            </div>
                            <span>ODP</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                            <div class="w-6 h-6 rounded-full border border-yellow-600 flex items-center justify-center bg-white shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" v-html="iconPaths.odf"></svg>
                            </div>
                            <span>ODF</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                           <div class="w-6 h-6 rounded-full border border-black flex items-center justify-center bg-white shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" v-html="iconPaths.pole"></svg>
                            </div>
                            <span>Pole</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                             <div class="w-6 h-6 rounded-full border border-red-600 flex items-center justify-center bg-white shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" v-html="iconPaths.tower"></svg>
                            </div>
                            <span>Tower</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                            <div class="w-6 h-6 rounded-full border border-gray-400 flex items-center justify-center bg-white shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#4b5563" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" v-html="iconPaths.joint_box"></svg>
                            </div>
                            <span>Joint Box</span>
                        </div>
                        
                        <div class="text-xs font-semibold text-gray-600 mt-2">Customer</div>
                        <div class="flex items-center gap-2 text-xs">
                            <div class="w-6 h-6 rounded-full border border-purple-400 flex items-center justify-center bg-white shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" v-html="iconPaths.cpe"></svg>
                            </div>
                            <span>CPE</span>
                        </div>
                        
                        <div class="text-xs font-semibold text-gray-600 mt-2">Cables</div>
                        <div class="flex items-center gap-2 text-xs">
                            <div class="w-4 h-0.5 bg-blue-600"></div>
                            <span>Active Path</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Button (Bottom Left) -->
            <div class="absolute bottom-6 left-4 z-[1000]">
                <div class="bg-white rounded-full shadow-xl p-1 border border-blue-100 hover:scale-110 transition-transform">
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="ghost" size="icon" class="h-12 w-12 rounded-full hover:bg-blue-50 hover:text-blue-600 transition-colors">
                                <MapPin class="h-8 w-8" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="start" side="top" class="w-56 mb-2">
                            <DropdownMenuLabel>Add New Device</DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuGroup>
                                <DropdownMenuLabel class="text-[10px] font-bold text-muted-foreground uppercase py-1">Active Devices</DropdownMenuLabel>
                                <DropdownMenuItem 
                                    v-for="type in deviceTypes.active" 
                                    :key="type.value"
                                    @click="pendingDeviceType = type.value"
                                    class="cursor-pointer"
                                >
                                    {{ type.label }}
                                </DropdownMenuItem>
                            </DropdownMenuGroup>
                            <DropdownMenuSeparator />
                            <DropdownMenuGroup>
                                <DropdownMenuLabel class="text-[10px] font-bold text-muted-foreground uppercase py-1">Passive Devices</DropdownMenuLabel>
                                <DropdownMenuItem 
                                    v-for="type in deviceTypes.passive" 
                                    :key="type.value"
                                    @click="pendingDeviceType = type.value"
                                    class="cursor-pointer"
                                >
                                    {{ type.label }}
                                </DropdownMenuItem>
                            </DropdownMenuGroup>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>
        </div>

        <!-- Cable Drawing Confirmation Bar -->
        <div v-if="isDrawingCable" class="absolute bottom-10 left-1/2 -translate-x-1/2 z-[2000] animate-in slide-in-from-bottom-5 duration-300">
            <div class="bg-white rounded-2xl shadow-2xl border border-blue-100 p-2 flex items-center gap-4 pr-4">
                <div class="bg-blue-600 rounded-xl p-3 text-white shadow-lg">
                    <Plus class="h-6 w-6 animate-pulse" />
                </div>
                <div>
                    <div class="text-[10px] font-bold text-blue-600 uppercase tracking-wider">Mode Gambar</div>
                    <div class="text-sm font-bold text-gray-900 leading-tight">Klik peta untuk tambah titik</div>
                </div>
                <div class="h-8 w-px bg-gray-200 mx-2"></div>
                <div class="flex gap-2">
                    <Button 
                        variant="ghost" 
                        size="sm" 
                        @click="cancelCableDrawing"
                        class="text-gray-500 hover:text-gray-700 font-semibold"
                    >
                        Batal
                    </Button>
                    <Button 
                        size="sm" 
                        @click="relocatingDevice?.type === 'cable' ? saveEditedCable() : finalizeCableDrawing()"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 rounded-lg shadow-md hover:shadow-lg transition-all"
                    >
                        {{ relocatingDevice?.type === 'cable' ? 'Simpan Perubahan' : 'Selesai Gambar' }}
                    </Button>
                </div>
            </div>
        </div>

        <!-- Relocation Confirmation Bar -->
        <div v-if="relocatingDevice" class="absolute bottom-10 left-1/2 -translate-x-1/2 z-[2000] animate-in slide-in-from-bottom-5 duration-300">
            <div class="bg-white rounded-2xl shadow-2xl border border-blue-100 p-2 flex items-center gap-2">
                <Button 
                    variant="ghost" 
                    size="sm" 
                    @click="cancelRelocation"
                    class="text-gray-500 hover:text-gray-700 font-semibold"
                >
                    Batal
                </Button>
                <Button 
                    size="sm" 
                    @click="saveRelocation"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 rounded-lg shadow-md hover:shadow-lg transition-all"
                >
                    Simpan
                </Button>
            </div>
        </div>

        <!-- Warning Popup Modal -->
        <div v-if="showWarningModal" class="fixed inset-0 z-[2000] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm animate-in fade-in duration-200">
            <div class="bg-white rounded-xl shadow-2xl max-w-sm w-full p-6 animate-in zoom-in-95 duration-200">
                <div class="flex flex-col items-center text-center space-y-4">
                    <div class="bg-amber-100 p-3 rounded-full">
                        <Plus class="h-8 w-8 text-amber-600 rotate-45" />
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">Di Luar Batas Area</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        {{ warningMessage }}
                    </p>
                    <Button 
                        @click="showWarningModal = false" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded-lg"
                    >
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
            @success="loadMapData(); pendingDeviceType = null; cancelCableDrawing()"
        />
    </AppLayout>
</template>

<style scoped>
/* Ensure map takes full height */
:deep(.leaflet-container) {
    height: 100%;
    width: 100%;
}

.cursor-pin {
    /* Sniper Crosshair SVG */
    cursor: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='32' height='32' viewBox='0 0 24 24' fill='none' stroke='%232563eb' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='12' cy='12' r='10'/%3E%3Cline x1='12' y1='8' x2='12' y2='16'/%3E%3Cline x1='8' y1='12' x2='16' y2='12'/%3E%3C/svg%3E") 16 16, crosshair !important;
}

.cursor-pin :deep(.leaflet-container),
.cursor-pin :deep(.leaflet-interactive) {
    cursor: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='32' height='32' viewBox='0 0 24 24' fill='none' stroke='%232563eb' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='12' cy='12' r='10'/%3E%3Cline x1='12' y1='8' x2='12' y2='16'/%3E%3Cline x1='8' y1='12' x2='16' y2='12'/%3E%3C/svg%3E") 16 16, crosshair !important;
}

/* Fix: Remove scrollbars and force full height for map page */
:global(html), :global(body) {
    height: 100vh !important;
    overflow: hidden !important;
}

:global(#app) {
    height: 100vh !important;
}

.map-page-container {
    height: calc(100vh - 65px); /* Adjusted for header height */
    width: 100%;
    position: relative;
    overflow: hidden;
}
</style>
