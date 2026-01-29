<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Activity } from 'lucide-vue-next';

defineProps<{
    olt: any;
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
                    <img
                        v-if="olt.device_image"
                        :src="'/storage/' + olt.device_image"
                        alt="Device Image"
                        class="h-full w-full object-cover"
                    />
                    <div
                        v-else
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
                                        olt.status === 'Active'
                                            ? 'bg-green-500'
                                            : 'bg-red-500'
                                    "
                                ></div>
                                <span class="font-medium">{{
                                    olt.status
                                }}</span>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <span
                                class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                >Installed</span
                            >
                            <div class="font-medium">
                                {{ olt.installed_at || '-' }}
                            </div>
                        </div>
                        <div class="col-span-2 space-y-1">
                            <span
                                class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                >Service Status</span
                            >
                            <div class="flex flex-wrap gap-2">
                                <Badge
                                    variant="secondary"
                                    v-for="status in olt.service_status"
                                    :key="status"
                                    class="h-5 px-2 py-0 text-xs"
                                >
                                    {{ status }}
                                </Badge>
                                <span
                                    v-if="!olt.service_status?.length"
                                    class="text-sm text-muted-foreground"
                                    >-</span
                                >
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
                            {{ olt.ip_address || '-' }}
                        </div>
                    </div>
                    <div class="space-y-1">
                        <span class="text-xs text-muted-foreground"
                            >MAC Address</span
                        >
                        <div class="font-mono text-sm">
                            {{ olt.mac_address || '-' }}
                        </div>
                    </div>
                    <div class="space-y-1">
                        <span class="text-xs text-muted-foreground"
                            >Username</span
                        >
                        <div class="font-mono text-sm">
                            {{ olt.username || '-' }}
                        </div>
                    </div>
                    <div class="space-y-1">
                        <span class="text-xs text-muted-foreground"
                            >Password</span
                        >
                        <div class="font-mono text-sm text-muted-foreground">
                            ********
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
                            {{ olt.brand }} {{ olt.model }}
                        </div>
                    </div>
                    <div class="space-y-1">
                        <span class="text-xs text-muted-foreground"
                            >Serial Number</span
                        >
                        <div class="font-mono text-sm">
                            {{ olt.serial_number || '-' }}
                        </div>
                    </div>
                    <div class="space-y-1">
                        <span class="text-xs text-muted-foreground"
                            >PON Type</span
                        >
                        <div class="font-medium">
                            {{ olt.pon_type || '-' }}
                        </div>
                    </div>
                    <div class="space-y-1">
                        <span class="text-xs text-muted-foreground"
                            >Purchase Year</span
                        >
                        <div class="font-medium">
                            {{ olt.purchase_year || '-' }}
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
                            {{ olt.area?.name || '-' }}
                        </div>
                    </div>
                    <div class="space-y-1">
                        <span class="text-xs text-muted-foreground"
                            >POP / Site</span
                        >
                        <div class="font-medium">
                            {{ olt.pop?.name || '-' }}
                        </div>
                    </div>
                    <div class="space-y-1">
                        <span class="text-xs text-muted-foreground"
                            >Coordinates</span
                        >
                        <div class="flex items-center gap-2 font-mono text-sm">
                            <span
                                >{{ olt.latitude || '0' }},
                                {{ olt.longitude || '0' }}</span
                            >
                            <a
                                v-if="olt.latitude && olt.longitude"
                                :href="`https://www.google.com/maps/search/?api=1&query=${olt.latitude},${olt.longitude}`"
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

        <!-- Section 5: Description -->
        <Card>
            <CardHeader class="pb-3">
                <CardTitle
                    class="text-sm font-bold tracking-wider text-muted-foreground uppercase"
                    >Keterangan Tambahan</CardTitle
                >
            </CardHeader>
            <CardContent>
                <p class="text-sm leading-relaxed text-foreground/80">
                    {{ olt.description || 'Tidak ada keterangan tambahan.' }}
                </p>
            </CardContent>
        </Card>
    </div>
</template>
