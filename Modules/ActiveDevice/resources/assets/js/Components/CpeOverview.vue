<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Activity } from 'lucide-vue-next';

defineProps<{
    cpe: any;
}>();
</script>

<template>
    <div class="space-y-6">
        <!-- Section 1: Device Image & Status -->
        <Card class="overflow-hidden">
            <div class="flex flex-col md:flex-row">
                <div
                    class="relative flex aspect-video w-full items-center justify-center bg-muted/30 md:w-1/3 lg:w-1/4"
                >
                    <div
                        class="flex flex-col items-center gap-2 text-muted-foreground"
                    >
                        <Activity class="h-12 w-12 opacity-20" />
                        <span class="text-xs">No Image Available</span>
                    </div>
                </div>
                <div class="flex-1 p-6">
                    <div class="mb-6 grid grid-cols-2 gap-6 md:grid-cols-4">
                        <div class="space-y-1">
                            <span
                                class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                >Status</span
                            >
                            <div class="flex items-center gap-2">
                                <div
                                    class="h-2.5 w-2.5 rounded-full"
                                    :class="
                                        cpe.status === 'Active'
                                            ? 'bg-green-500'
                                            : 'bg-red-500'
                                    "
                                ></div>
                                <span class="font-medium">{{
                                    cpe.status || 'Inactive'
                                }}</span>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <span
                                class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                >Installed</span
                            >
                            <div class="font-medium">
                                {{ cpe.installed_at || '-' }}
                            </div>
                        </div>
                        <div class="space-y-1">
                            <span
                                class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                >Type</span
                            >
                            <div class="font-medium">
                                {{ cpe.type || '-' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Card>

        <!-- Section 2: Networking -->
        <Card>
            <CardHeader class="pb-3">
                <CardTitle
                    class="text-sm font-bold tracking-wider text-muted-foreground uppercase"
                    >Networking</CardTitle
                >
            </CardHeader>
            <CardContent>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                    <div class="space-y-1">
                        <span class="text-xs text-muted-foreground"
                            >IP Address</span
                        >
                        <div
                            class="font-mono text-sm font-medium text-blue-600 dark:text-blue-400"
                        >
                            {{ cpe.ip_address || '-' }}
                        </div>
                    </div>
                    <div class="space-y-1">
                        <span class="text-xs text-muted-foreground"
                            >MAC Address</span
                        >
                        <div class="font-mono text-sm">
                            {{ cpe.mac_address || '-' }}
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Section 3: Hardware Specification -->
        <Card>
            <CardHeader class="pb-3">
                <CardTitle
                    class="text-sm font-bold tracking-wider text-muted-foreground uppercase"
                    >Hardware Specification</CardTitle
                >
            </CardHeader>
            <CardContent>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                    <div class="space-y-1">
                        <span class="text-xs text-muted-foreground"
                            >Brand / Model</span
                        >
                        <div class="font-medium">
                            {{ cpe.brand }} {{ cpe.model }}
                        </div>
                    </div>
                    <div class="space-y-1">
                        <span class="text-xs text-muted-foreground"
                            >Serial Number</span
                        >
                        <div class="font-mono text-sm">
                            {{ cpe.serial_number || '-' }}
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Section 4: Location -->
        <Card>
            <CardHeader class="pb-3">
                <CardTitle
                    class="text-sm font-bold tracking-wider text-muted-foreground uppercase"
                    >Location</CardTitle
                >
            </CardHeader>
            <CardContent>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <div class="space-y-1">
                        <span class="text-xs text-muted-foreground"
                            >Wilayah (Area)</span
                        >
                        <div class="font-medium">
                            {{ cpe.area?.name || '-' }}
                        </div>
                    </div>
                    <div class="space-y-1">
                        <span class="text-xs text-muted-foreground"
                            >Address</span
                        >
                        <div class="font-medium capitalize">
                            {{ cpe.address || '-' }}
                        </div>
                    </div>
                    <div class="space-y-1">
                        <span class="text-xs text-muted-foreground"
                            >Coordinates</span
                        >
                        <div class="flex items-center gap-2 font-mono text-sm">
                            <span
                                >{{ cpe.latitude || '0' }},
                                {{ cpe.longitude || '0' }}</span
                            >
                            <a
                                v-if="cpe.latitude && cpe.longitude"
                                :href="`https://www.google.com/maps/search/?api=1&query=${cpe.latitude},${cpe.longitude}`"
                                target="_blank"
                                class="text-xs text-blue-500 hover:underline"
                            >
                                (Google Maps)
                            </a>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Section 6: Uplink Info (Specific to CPE) -->
        <Card v-if="cpe.active_device">
            <CardHeader class="pb-3">
                <CardTitle
                    class="text-sm font-bold tracking-wider text-muted-foreground uppercase"
                    >Uplink Device</CardTitle
                >
            </CardHeader>
            <CardContent>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <div class="space-y-1">
                        <span class="text-xs text-muted-foreground"
                            >Name / Code</span
                        >
                        <div class="font-medium">
                            {{ cpe.active_device.name }} ({{
                                cpe.active_device.code
                            }})
                        </div>
                    </div>
                    <div class="space-y-1">
                        <span class="text-xs text-muted-foreground">Type</span>
                        <div class="font-medium">
                            {{ cpe.active_device_type.split('\\').pop() }}
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Section 5: Description -->
        <Card>
            <CardHeader class="pb-3">
                <CardTitle
                    class="text-sm font-bold tracking-wider text-muted-foreground uppercase"
                    >Keterangan Tambahan</CardTitle
                >
            </CardHeader>
            <CardContent>
                <p
                    class="text-sm leading-relaxed whitespace-pre-wrap text-foreground/80"
                >
                    {{ cpe.description || 'Tidak ada keterangan tambahan.' }}
                </p>
            </CardContent>
        </Card>
    </div>
</template>
