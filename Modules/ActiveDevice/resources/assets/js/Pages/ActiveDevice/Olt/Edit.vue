<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import FileUploader from '@/components/FileUploader.vue';
import { onMounted, ref, watch } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const props = defineProps<{
    olt: any;
    areas: Array<any>;
    pops: Array<any>;
}>();

const form = useForm({
    _method: 'put',
    infrastructure_area_id: props.olt.infrastructure_area_id.toString(),
    pop_id: props.olt.pop_id ? props.olt.pop_id.toString() : '',
    code: props.olt.code,
    name: props.olt.name,
    brand: props.olt.brand || '',
    model: props.olt.model || '',
    serial_number: props.olt.serial_number || '',
    mac_address: props.olt.mac_address || '',
    ip_address: props.olt.ip_address || '',
    username: props.olt.username || '',
    password: props.olt.password || '', // Usually explicit password fields are empty on edit
    service_status: props.olt.service_status || [],
    purchase_year: props.olt.purchase_year || '',
    pon_type: props.olt.pon_type || '',
    latitude: props.olt.latitude || '',
    longitude: props.olt.longitude || '',
    status: props.olt.status || 'Active',
    installed_at: props.olt.installed_at || '',
    description: props.olt.description || '',
    device_image: null as File | null,
    is_active: !!props.olt.is_active,
});

const submit = () => {
    form.post(`/pendataan/active-devices/olt/${props.olt.id}`);
};

const serviceOptions = ['Telnet', 'SSH', 'WEB'];

// Map Logic
const mapContainer = ref<HTMLElement | null>(null);
let map: L.Map | null = null;
let marker: L.Marker | null = null;
let areaLayer: L.Polygon | null = null;

const initMap = () => {
    if (!mapContainer.value) return;

    // Default: Indonesia center or existing lat/long
    const hasLocation = form.latitude && form.longitude;
    const lat = hasLocation ? parseFloat(form.latitude) : -2.5489;
    const lng = hasLocation ? parseFloat(form.longitude) : 118.0149;
    const zoom = hasLocation ? 15 : 5;

    map = L.map(mapContainer.value).setView([lat, lng], zoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Fix marker icon
    const iconRetinaUrl = 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png';
    const iconUrl = 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png';
    const shadowUrl = 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png';

    const DefaultIcon = L.icon({
        iconUrl: iconUrl,
        iconRetinaUrl: iconRetinaUrl,
        shadowUrl: shadowUrl,
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });
    L.Marker.prototype.options.icon = DefaultIcon;

    if (hasLocation) {
        addMarker(lat, lng);
    } else if (form.infrastructure_area_id) {
        // If no specific location but area is selected, try to zoom to area
        drawAreaBoundary(form.infrastructure_area_id);
    }

    map.on('click', (e) => {
        const { lat, lng } = e.latlng;
        addMarker(lat, lng);
        updateFormLocation(lat, lng);
    });
};

const drawAreaBoundary = (areaId: string | number) => {
    if (!map || !areaId) return;

    // Clear existing layer
    if (areaLayer) {
        map.removeLayer(areaLayer);
        areaLayer = null;
    }

    const area = props.areas.find(a => a.id.toString() === areaId.toString());
    
    if (area && area.boundary) {
        try {
            // Boundary is likely array of [lat, lng] or [[lat, lng]]
            // Verify structure if needed, but assuming Leaflet compatible array from backend cast
            const boundaryData = area.boundary; 
            
            areaLayer = L.polygon(boundaryData, {
                color: '#3b82f6',
                weight: 2,
                opacity: 0.6,
                fillOpacity: 0.1,
                dashArray: '5, 10'
            }).addTo(map);

            // Fit bounds only if we don't have a specific point set, OR if the user just changed the area
            // Ideally we fit bounds to give context
            const bounds = areaLayer.getBounds();
            if (bounds.isValid()) {
                map.fitBounds(bounds);
            }
        } catch (e) {
            console.error("Failed to draw area boundary", e);
        }
    }
};

const addMarker = (lat: number, lng: number) => {
    if (marker) {
        marker.setLatLng([lat, lng]);
    } else {
        if (!map) return;
        marker = L.marker([lat, lng], { draggable: true }).addTo(map);
        marker.on('dragend', (event) => {
             const position = event.target.getLatLng();
             updateFormLocation(position.lat, position.lng);
        });
    }
};

const updateFormLocation = (lat: number, lng: number) => {
    form.latitude = lat.toString();
    form.longitude = lng.toString();
};

onMounted(() => {
    initMap();
});

// Watch Area Change
watch(() => form.infrastructure_area_id, (newVal) => {
    if (newVal) {
        drawAreaBoundary(newVal);
    } else {
        if (areaLayer && map) {
            map.removeLayer(areaLayer);
            areaLayer = null;
        }
    }
});

// Watch for manual input changes to update map
watch(() => [form.latitude, form.longitude], ([newLat, newLng]) => {
     if (newLat && newLng && map) {
         const lat = parseFloat(newLat);
         const lng = parseFloat(newLng);
         
         // Only update if map view/marker is significantly different to avoid loop
         if (marker) {
             const cur = marker.getLatLng();
             if (Math.abs(cur.lat - lat) > 0.0001 || Math.abs(cur.lng - lng) > 0.0001) {
                 marker.setLatLng([lat, lng]);
                 // map.setView([lat, lng], 15); // Optional: don't always force center on type
             }
         } else {
             addMarker(lat, lng);
             map.setView([lat, lng], 15);
         }
     }
});

</script>

<template>
    <Head title="Edit OLT" />

    <AppLayout :breadcrumbs="[
        { title: 'OLT', href: '/pendataan/active-devices/olt' },
        { title: 'Edit', href: '#' }
    ]">
        <div class="max-w-4xl mx-4 p-4 md:p-4 space-y-6">
            <!-- Header -->
            <div class="space-y-1">
                <h1 class="text-2xl font-bold tracking-tight">Edit OLT</h1>
                <p class="text-muted-foreground text-sm text-foreground/60">Perbarui informasi perangkat OLT.</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Informasi Dasar -->
                <Card class="border shadow-none">
                    <CardHeader class="pb-4">
                        <CardTitle class="text-base font-semibold">Informasi Dasar</CardTitle>
                        <CardDescription class="text-xs">Identifikasi dasar dan lokasi perangkat.</CardDescription>
                    </CardHeader>
                    <CardContent class="grid gap-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-1.5">
                                <Label for="area" class="text-sm font-medium">Wilayah</Label>
                                <Select v-model="form.infrastructure_area_id">
                                    <SelectTrigger class="w-full h-11 rounded-lg border-slate-200" :class="{ 'border-destructive': form.errors.infrastructure_area_id }">
                                        <SelectValue placeholder="Pilih Wilayah" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="area in areas" :key="area.id" :value="area.id.toString()">
                                            {{ area.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <div v-if="form.errors.infrastructure_area_id" class="text-xs text-destructive mt-1">{{ form.errors.infrastructure_area_id }}</div>
                            </div>

                            <div class="space-y-1.5">
                                <Label for="pop" class="text-sm font-medium">POP</Label>
                                <Select v-model="form.pop_id">
                                    <SelectTrigger class="w-full h-11 rounded-lg border-slate-200" :class="{ 'border-destructive': form.errors.pop_id }">
                                        <SelectValue placeholder="Pilih POP" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="pop in pops" :key="pop.id" :value="pop.id.toString()">
                                            {{ pop.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <div v-if="form.errors.pop_id" class="text-xs text-destructive mt-1">{{ form.errors.pop_id }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-1.5">
                                <Label for="code" class="text-sm font-medium">Kode OLT</Label>
                                <Input id="code" v-model="form.code" placeholder="OLT-XYZ" class="h-11 rounded-lg border-slate-200" :class="{ 'border-destructive': form.errors.code }" />
                                <div v-if="form.errors.code" class="text-xs text-destructive mt-1">{{ form.errors.code }}</div>
                            </div>

                            <div class="space-y-1.5">
                                <Label for="name" class="text-sm font-medium">Hostname</Label>
                                <Input id="name" v-model="form.name" placeholder="OLT Core A" class="h-11 rounded-lg border-slate-200" :class="{ 'border-destructive': form.errors.name }" />
                                <div v-if="form.errors.name" class="text-xs text-destructive mt-1">{{ form.errors.name }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-1.5">
                                <Label for="brand" class="text-sm font-medium">Merek/Vendor</Label>
                                <Input id="brand" v-model="form.brand" class="h-11 rounded-lg border-slate-200" />
                            </div>
                            <div class="space-y-1.5">
                                <Label for="model" class="text-sm font-medium">Tipe Perangkat</Label>
                                <Input id="model" v-model="form.model" class="h-11 rounded-lg border-slate-200" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-1.5">
                                <Label for="serial_number" class="text-sm font-medium">Serial Number</Label>
                                <Input id="serial_number" v-model="form.serial_number" class="h-11 rounded-lg border-slate-200" />
                            </div>
                            <div class="space-y-1.5">
                                <Label for="mac_address" class="text-sm font-medium">MAC Address</Label>
                                <Input id="mac_address" v-model="form.mac_address" class="h-11 rounded-lg border-slate-200" />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-1.5">
                                <Label for="pon_type" class="text-sm font-medium">Tipe PON</Label>
                                <Select v-model="form.pon_type">
                                    <SelectTrigger class="w-full h-11 rounded-lg border-slate-200">
                                        <SelectValue placeholder="Pilih Tipe PON" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="GPON">GPON</SelectItem>
                                        <SelectItem value="EPON">EPON</SelectItem>
                                        <SelectItem value="XGPON">XGPON</SelectItem>
                                        <SelectItem value="Combo">Combo</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Informasi Jaringan & Autentikasi -->
                <Card class="border shadow-none">
                    <CardHeader class="pb-4">
                        <CardTitle class="text-base font-semibold">Jaringan & Autentikasi</CardTitle>
                        <CardDescription class="text-xs">Detail koneksi, IP Address dan kredensial akses.</CardDescription>
                    </CardHeader>
                    <CardContent class="grid gap-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-1.5">
                                <Label for="ip_address" class="text-sm font-medium">IP Address</Label>
                                <Input id="ip_address" v-model="form.ip_address" class="h-11 rounded-lg border-slate-200" :class="{ 'border-destructive': form.errors.ip_address }" />
                                <div v-if="form.errors.ip_address" class="text-xs text-destructive mt-1">{{ form.errors.ip_address }}</div>
                            </div>
                            <div class="space-y-1.5">
                                <Label class="text-sm font-medium">Service Status</Label>
                                <div class="flex gap-4 mt-2">
                                    <div v-for="service in serviceOptions" :key="service" class="flex items-center space-x-2">
                                        <Checkbox :id="'service-' + service" 
                                            :checked="form.service_status.includes(service)"
                                            @update:checked="(checked) => {
                                                if (checked) form.service_status.push(service);
                                                else form.service_status = form.service_status.filter(s => s !== service);
                                            }"
                                        />
                                        <Label :for="'service-' + service" class="font-normal">{{ service }}</Label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-1.5">
                                <Label for="username" class="text-sm font-medium">Username</Label>
                                <Input id="username" v-model="form.username" class="h-11 rounded-lg border-slate-200" />
                            </div>
                            <div class="space-y-1.5">
                                <Label for="password" class="text-sm font-medium">Password</Label>
                                <Input id="password" type="password" v-model="form.password" placeholder="Leave empty to keep current" class="h-11 rounded-lg border-slate-200" />
                            </div>
                        </div>
                    </CardContent>
                </Card>



                <!-- Foto & Status -->
                <Card class="border shadow-none">
                    <CardHeader class="pb-4">
                        <CardTitle class="text-base font-semibold">Foto & Status</CardTitle>
                        <CardDescription class="text-xs">Dokumentasi fisik dan status operasional perangkat.</CardDescription>
                    </CardHeader>
                    <CardContent class="grid gap-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-1.5">
                                <Label for="purchase_year" class="text-sm font-medium">Tahun Beli</Label>
                                <Input id="purchase_year" type="number" v-model="form.purchase_year" placeholder="YYYY" class="h-11 rounded-lg border-slate-200" />
                            </div>
                            <div class="space-y-1.5">
                                <Label for="installed_at" class="text-sm font-medium">Tanggal Install</Label>
                                <Input id="installed_at" type="date" v-model="form.installed_at" class="h-11 rounded-lg border-slate-200" />
                            </div>
   
                        </div>

                        <div class="space-y-2">
                            <Label>Lokasi Pemasangan (Map)</Label>
                            <div ref="mapContainer" class="h-[350px] w-full rounded-lg border border-slate-200 z-0"></div>
                            <p class="text-[11px] text-muted-foreground">Klik pada peta untuk menandai lokasi pemasangan.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-1.5">
                                <Label for="latitude" class="text-sm font-medium">Latitude</Label>
                                <Input id="latitude" v-model="form.latitude" class="h-11 rounded-lg border-slate-200" />
                            </div>
                            <div class="space-y-1.5">
                                <Label for="longitude" class="text-sm font-medium">Longitude</Label>
                                <Input id="longitude" v-model="form.longitude" class="h-11 rounded-lg border-slate-200" />
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-sm font-medium">Foto Perangkat</Label>
                            <FileUploader 
                                v-model="form.device_image" 
                                accept="image/png, image/jpeg, image/jpg"
                                :max-size="5"
                                :error="form.errors.device_image"
                                :initial-image="olt.device_image ? '/storage/' + olt.device_image : null"
                            />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-1.5">
                                <Label for="status" class="text-sm font-medium">Status</Label>
                                <Select v-model="form.status">
                                    <SelectTrigger class="w-full h-11 rounded-lg border-slate-200">
                                        <SelectValue placeholder="Pilih Status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Planned">Planned</SelectItem>
                                        <SelectItem value="Installed">Installed</SelectItem>
                                        <SelectItem value="Active">Active</SelectItem>
                                        <SelectItem value="Inactive">Inactive</SelectItem>
                                        <SelectItem value="Retired">Retired</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
   
                        </div>

                        <div class="space-y-1.5">
                            <Label for="description" class="text-sm font-medium">Keterangan</Label>
                            <Textarea id="description" v-model="form.description" class="min-h-[100px] rounded-lg border-slate-200 resize-none" />
                        </div>

                        <div class="flex items-center space-x-2">
                            <Switch id="is_active" v-model:checked="form.is_active" />
                            <Label for="is_active" class="font-normal">Status Aktif</Label>
                        </div>
                    </CardContent>
                    <CardFooter class="border-t bg-slate-50/50 p-6 flex justify-end gap-3 rounded-b-lg">
                        <Button variant="outline" as-child>
                            <Link href="/pendataan/active-devices/olt">Batal</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing" class="px-10 font-bold bg-primary hover:bg-primary/90 rounded-lg">
                            {{ form.processing ? 'Menyimpan...' : 'Update OLT' }}
                        </Button>
                    </CardFooter>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
