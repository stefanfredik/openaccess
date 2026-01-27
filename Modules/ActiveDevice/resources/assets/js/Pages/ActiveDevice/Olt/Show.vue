<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Pencil, ArrowLeft, Activity } from 'lucide-vue-next';
import ConnectionManager from '@/../../Modules/ActiveDevice/resources/assets/js/Components/ConnectionManager.vue';

const props = defineProps<{
    olt: any;
    availableDevices: Array<any>;
}>();
</script>

<template>
    <Head :title="`OLT: ${olt.name}`" />

    <AppLayout :breadcrumbs="[
        { title: 'OLT', href: '/pendataan/active-devices/olt' },
        { title: olt.name, href: '#' }
    ]">
        <div class="max-w-7xl mx-auto p-4 md:p-6 space-y-6">
            <!-- Page Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-bold tracking-tight">{{ olt.name }}</h1>
                        <Badge :variant="olt.status === 'Active' ? 'default' : (olt.status === 'Planned' ? 'secondary' : 'destructive')" class="capitalize">
                            {{ olt.status }}
                        </Badge>
                    </div>
                    <p class="text-muted-foreground flex items-center gap-2 text-sm">
                        <span class="font-mono bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded text-xs">{{ olt.code }}</span>
                        <span>&bull;</span>
                        <span>{{ olt.brand }} {{ olt.model }}</span>
                    </p>
                </div>
                <div class="flex items-center gap-2">
                     <Button variant="outline" size="sm" as-child>
                        <Link href="/pendataan/active-devices/olt">
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Kembali
                        </Link>
                    </Button>
                    <Button size="sm" as-child>
                        <Link :href="`/active-devices/olt/${olt.id}/edit`">
                            <Pencil class="mr-2 h-4 w-4" />
                            Edit OLT
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Status & Image -->
                <div class="space-y-6">
                    <Card class="overflow-hidden">
                        <div class="aspect-video bg-slate-100 dark:bg-slate-800 relative flex items-center justify-center">
                            <img v-if="olt.device_image" :src="'/storage/' + olt.device_image" alt="Device Image" class="w-full h-full object-cover" />
                            <div v-else class="text-muted-foreground flex flex-col items-center gap-2">
                                <Activity class="h-12 w-12 opacity-20" />
                                <span class="text-xs">No Image Available</span>
                            </div>
                        </div>
                        <CardContent class="p-6 grid gap-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <span class="text-xs text-muted-foreground font-medium uppercase tracking-wider">Status</span>
                                    <div class="font-medium">{{ olt.status }}</div>
                                </div>
                                <div class="space-y-1">
                                    <span class="text-xs text-muted-foreground font-medium uppercase tracking-wider">Installed</span>
                                    <div class="font-medium">{{ olt.installed_at || '-' }}</div>
                                </div>
                            </div>
                            <div class="space-y-1 pt-4 border-t">
                                <span class="text-xs text-muted-foreground font-medium uppercase tracking-wider">Service Status</span>
                                <div class="flex gap-2 flex-wrap mt-1">
                                    <Badge variant="secondary" v-for="status in olt.service_status" :key="status" class="text-xs px-2 py-0 h-5">
                                        {{ status }}
                                    </Badge>
                                    <span v-if="!olt.service_status?.length" class="text-sm text-muted-foreground">-</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader class="pb-3">
                             <CardTitle class="text-sm font-medium uppercase tracking-wider text-muted-foreground">Networking</CardTitle>
                        </CardHeader>
                        <CardContent class="grid gap-4">
                            <div class="flex items-center justify-between py-1 border-b last:border-0 border-dashed">
                                <span class="text-sm text-muted-foreground">IP Address</span>
                                <span class="text-sm font-mono font-medium text-blue-600 dark:text-blue-400">{{ olt.ip_address || '-' }}</span>
                            </div>
                            <div class="flex items-center justify-between py-1 border-b last:border-0 border-dashed">
                                <span class="text-sm text-muted-foreground">MAC Address</span>
                                <span class="text-sm font-mono">{{ olt.mac_address || '-' }}</span>
                            </div>
                            <div class="flex items-center justify-between py-1 border-b last:border-0 border-dashed">
                                <span class="text-sm text-muted-foreground">Username</span>
                                <span class="text-sm font-mono">{{ olt.username || '-' }}</span>
                            </div>
                             <div class="flex items-center justify-between py-1 border-b last:border-0 border-dashed">
                                <span class="text-sm text-muted-foreground">Password</span>
                                <span class="text-sm font-mono text-muted-foreground">********</span>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Right Column: Details & Map -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Location & Specs -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Card>
                            <CardHeader class="pb-3">
                                <CardTitle class="text-sm font-medium uppercase tracking-wider text-muted-foreground">Location</CardTitle>
                            </CardHeader>
                            <CardContent class="grid gap-4">
                                <div class="space-y-1">
                                    <span class="text-xs text-muted-foreground">Wilayah (Area)</span>
                                    <div class="font-medium">{{ olt.area?.name || '-' }}</div>
                                </div>
                                <div class="space-y-1">
                                    <span class="text-xs text-muted-foreground">POP / Site</span>
                                    <div class="font-medium">{{ olt.pop?.name || '-' }}</div>
                                </div>
                                <div class="space-y-1">
                                    <span class="text-xs text-muted-foreground">Coordinates</span>
                                    <div class="font-mono text-sm flex items-center gap-2">
                                        <span>{{ olt.latitude || '0' }}, {{ olt.longitude || '0' }}</span>
                                        <a v-if="olt.latitude && olt.longitude" 
                                           :href="`https://www.google.com/maps/search/?api=1&query=${olt.latitude},${olt.longitude}`" 
                                           target="_blank" 
                                           class="text-xs text-blue-500 hover:underline">
                                           (Google Maps)
                                        </a>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                             <CardHeader class="pb-3">
                                <CardTitle class="text-sm font-medium uppercase tracking-wider text-muted-foreground">Hardware Specification</CardTitle>
                            </CardHeader>
                            <CardContent class="grid gap-4">
                                 <div class="space-y-1">
                                    <span class="text-xs text-muted-foreground">Brand / Model</span>
                                    <div class="font-medium">{{ olt.brand }} {{ olt.model }}</div>
                                </div>
                                <div class="space-y-1">
                                    <span class="text-xs text-muted-foreground">Serial Number</span>
                                    <div class="font-mono text-sm">{{ olt.serial_number || '-' }}</div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-1">
                                        <span class="text-xs text-muted-foreground">PON Type</span>
                                        <div class="font-medium">{{ olt.pon_type || '-' }}</div>
                                    </div>
                                    <div class="space-y-1">
                                        <span class="text-xs text-muted-foreground">Purchase Year</span>
                                        <div class="font-medium">{{ olt.purchase_year || '-' }}</div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Description -->
                     <Card>
                        <CardHeader class="pb-3">
                             <CardTitle class="text-sm font-medium uppercase tracking-wider text-muted-foreground">Keterangan Tambahan</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p class="text-sm text-foreground/80 leading-relaxed">
                                {{ olt.description || 'Tidak ada keterangan tambahan.' }}
                            </p>
                        </CardContent>
                    </Card>

                    <!-- Connections -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold tracking-tight">Konektivitas</h3>
                        </div>
                        <ConnectionManager 
                            :device="olt" 
                            device-type="Modules\ActiveDevice\Models\Olt"
                            :connections="olt.source_connections"
                            :incoming-connections="olt.destination_connections"
                            :available-devices="availableDevices"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
