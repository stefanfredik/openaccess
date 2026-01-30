<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    edit as areaEdit,
    index as areaIndex,
    show as areaShow,
} from '@/routes/area';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import {
    Box,
    Building2,
    Cpu,
    Map as MapIcon,
    Monitor,
    Pencil,
    Server,
} from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

const props = defineProps<{
    area: any;
    stats: {
        pops_count: number;
        servers_count: number;
        olts_count: number;
        odps_count: number;
        cpes_count: number;
    };
}>();

const provinceName = ref('-');
const regencyName = ref('-');
const districtName = ref('-');
const villageName = ref('-');
const mapContainer = ref<HTMLElement | null>(null);
let map: L.Map | null = null;

const fetchLocationNames = async () => {
    try {
        if (props.area.province_id) {
            const provRes = await axios.get(
                `https://www.emsifa.com/api-wilayah-indonesia/api/province/${props.area.province_id}.json`,
            );
            provinceName.value = provRes.data.name;
        }

        if (props.area.regency_id) {
            const regRes = await axios.get(
                `https://www.emsifa.com/api-wilayah-indonesia/api/regency/${props.area.regency_id}.json`,
            );
            regencyName.value = regRes.data.name;
        }

        if (props.area.district_id) {
            const distRes = await axios.get(
                `https://www.emsifa.com/api-wilayah-indonesia/api/district/${props.area.district_id}.json`,
            );
            districtName.value = distRes.data.name;
        }

        if (props.area.village_id) {
            const villRes = await axios.get(
                `https://www.emsifa.com/api-wilayah-indonesia/api/village/${props.area.village_id}.json`,
            );
            villageName.value = villRes.data.name;
        }
    } catch (error) {
        console.error('Error fetching location names:', error);
    }
};

const initMap = () => {
    if (!mapContainer.value || !props.area.boundary) return;

    map = L.map(mapContainer.value, {
        zoomControl: true,
        scrollWheelZoom: false,
    }).setView([0, 0], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution:
            '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    }).addTo(map);

    const boundary = L.polygon(props.area.boundary, {
        color: '#3b82f6',
        weight: 3,
        opacity: 0.8,
        fillColor: '#3b82f6',
        fillOpacity: 0.2,
    }).addTo(map);

    map.fitBounds(boundary.getBounds());
};

onMounted(() => {
    fetchLocationNames();
    setTimeout(() => {
        initMap();
    }, 100);
});
</script>

<template>
    <Head :title="area.name" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Wilayah', href: areaIndex().url },
            { title: area.name, href: areaShow({ area: area.id }).url },
        ]"
    >
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        {{ area.name }}
                    </h1>
                    <p class="text-muted-foreground">{{ area.type }}</p>
                </div>
                <Button as-child>
                    <Link :href="areaEdit({ area: area.id }).url">
                        <Pencil class="mr-2 h-4 w-4" />
                        Edit
                    </Link>
                </Button>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Map Card -->
                <Card
                    v-if="area.boundary"
                    class="overflow-hidden shadow-sm md:col-span-2"
                >
                    <CardHeader class="border-b pb-2">
                        <div class="flex items-center gap-2">
                            <MapIcon class="h-5 w-5 text-primary" />
                            <CardTitle>Batas Wilayah</CardTitle>
                        </div>
                        <CardDescription
                            >Peta batas cakupan wilayah infrastruktur
                            ini.</CardDescription
                        >
                    </CardHeader>
                    <CardContent class="p-0">
                        <div
                            ref="mapContainer"
                            class="z-0 h-[400px] w-full"
                        ></div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Info Wilayah</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-2 gap-y-3 text-sm">
                            <div class="text-muted-foreground">
                                Kode Wilayah
                            </div>
                            <div class="font-mono font-medium text-primary">
                                {{ area.code || '-' }}
                            </div>

                            <div class="text-muted-foreground">Tipe</div>
                            <div>
                                <Badge variant="secondary">{{
                                    area.type
                                }}</Badge>
                            </div>

                            <div class="text-muted-foreground">Deskripsi</div>
                            <div>{{ area.description || '-' }}</div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Lokasi Administratif</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-2 gap-y-3 text-sm">
                            <div class="text-muted-foreground">Provinsi</div>
                            <div>{{ provinceName }}</div>

                            <div class="text-muted-foreground">
                                Kota/Kabupaten
                            </div>
                            <div>{{ regencyName }}</div>

                            <div class="text-muted-foreground">Kecamatan</div>
                            <div>{{ districtName }}</div>

                            <div class="text-muted-foreground">
                                Kelurahan/Desa
                            </div>
                            <div>{{ villageName }}</div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Statistik Infrastruktur</CardTitle>
                    <CardDescription
                        >Jumlah infrastruktur yang terdaftar di wilayah
                        ini.</CardDescription
                    >
                </CardHeader>
                <CardContent>
                    <div
                        class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-5"
                    >
                        <div
                            class="flex flex-col items-center justify-center rounded-xl border bg-card p-6 transition-colors hover:bg-muted/50"
                        >
                            <Building2 class="mb-2 h-10 w-10 text-blue-500" />
                            <span class="text-3xl font-bold tracking-tight">{{
                                stats.pops_count
                            }}</span>
                            <span
                                class="mt-1 text-[10px] font-semibold tracking-wider text-muted-foreground uppercase"
                                >Point of Presence</span
                            >
                        </div>
                        <div
                            class="flex flex-col items-center justify-center rounded-xl border bg-card p-6 transition-colors hover:bg-muted/50"
                        >
                            <Server class="mb-2 h-10 w-10 text-green-500" />
                            <span class="text-3xl font-bold tracking-tight">{{
                                stats.servers_count
                            }}</span>
                            <span
                                class="mt-1 text-[10px] font-semibold tracking-wider text-muted-foreground uppercase"
                                >Server Room</span
                            >
                        </div>
                        <div
                            class="flex flex-col items-center justify-center rounded-xl border bg-card p-6 transition-colors hover:bg-muted/50"
                        >
                            <Cpu class="mb-2 h-10 w-10 text-purple-500" />
                            <span class="text-3xl font-bold tracking-tight">{{
                                stats.olts_count
                            }}</span>
                            <span
                                class="mt-1 text-[10px] font-semibold tracking-wider text-muted-foreground uppercase"
                                >Active Devices</span
                            >
                        </div>
                        <div
                            class="flex flex-col items-center justify-center rounded-xl border bg-card p-6 transition-colors hover:bg-muted/50"
                        >
                            <Box class="mb-2 h-10 w-10 text-orange-500" />
                            <span class="text-3xl font-bold tracking-tight">{{
                                stats.odps_count
                            }}</span>
                            <span
                                class="mt-1 text-[10px] font-semibold tracking-wider text-muted-foreground uppercase"
                                >Passive Devices</span
                            >
                        </div>
                        <div
                            class="flex flex-col items-center justify-center rounded-xl border bg-card p-6 transition-colors hover:bg-muted/50"
                        >
                            <Monitor class="mb-2 h-10 w-10 text-cyan-500" />
                            <span class="text-3xl font-bold tracking-tight">{{
                                stats.cpes_count
                            }}</span>
                            <span
                                class="mt-1 text-[10px] font-semibold tracking-wider text-muted-foreground uppercase"
                                >CPE / Customer</span
                            >
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
