<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Pencil } from 'lucide-vue-next';

import ConnectionManager from '@/../../Modules/ActiveDevice/resources/assets/js/Components/ConnectionManager.vue';

const props = defineProps<{
    accessPoint: any;
    availableDevices: Array<any>;
}>();
</script>

<template>
    <Head :title="`AP: ${accessPoint.name}`" />

    <AppLayout
        :breadcrumbs="[
            {
                title: 'Access Points',
                href: route('active-device.access-point.index'),
            },
            { title: accessPoint.name, href: '#' },
        ]"
    >
        <div class="flex w-full flex-col gap-6 p-4 md:p-6 lg:p-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" as-child>
                        <Link :href="route('active-device.access-point.index')">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">
                            {{ accessPoint.name }}
                        </h1>
                        <p class="text-muted-foreground">
                            {{ accessPoint.code }}
                        </p>
                    </div>
                </div>
                <Button as-child>
                    <Link
                        :href="
                            route(
                                'active-device.access-point.edit',
                                accessPoint.id,
                            )
                        "
                    >
                        <Pencil class="mr-2 h-4 w-4" />
                        Edit AP
                    </Link>
                </Button>
            </div>

            <div class="flex flex-col gap-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Details</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <dl
                            class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2 md:grid-cols-3"
                        >
                            <div>
                                <dt
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Area
                                </dt>
                                <dd class="text-sm font-semibold">
                                    {{ accessPoint.area?.name || '-' }}
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    POP / Site
                                </dt>
                                <dd class="text-sm font-semibold">
                                    {{ accessPoint.pop?.name || '-' }}
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Status
                                </dt>
                                <dd>
                                    <Badge
                                        :variant="
                                            accessPoint.is_active
                                                ? 'default'
                                                : 'destructive'
                                        "
                                    >
                                        {{
                                            accessPoint.is_active
                                                ? 'Active'
                                                : 'Inactive'
                                        }}
                                    </Badge>
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Frequency
                                </dt>
                                <dd class="text-sm font-semibold">
                                    {{ accessPoint.frequency || '-' }}
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    SSID Count
                                </dt>
                                <dd class="text-sm font-semibold">
                                    {{ accessPoint.ssid_count }}
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Brand / Model
                                </dt>
                                <dd class="text-sm font-semibold">
                                    {{ accessPoint.brand || '-' }} /
                                    {{ accessPoint.model || '-' }}
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    IP Address
                                </dt>
                                <dd class="font-mono text-sm font-semibold">
                                    {{ accessPoint.ip_address || '-' }}
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    MAC Address
                                </dt>
                                <dd class="font-mono text-sm font-semibold">
                                    {{ accessPoint.mac_address || '-' }}
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Serial Number
                                </dt>
                                <dd class="font-mono text-sm font-semibold">
                                    {{ accessPoint.serial_number || '-' }}
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Installed At
                                </dt>
                                <dd class="text-sm font-semibold">
                                    {{ accessPoint.installed_at || '-' }}
                                </dd>
                            </div>
                            <div class="sm:col-span-2 md:col-span-3">
                                <dt
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Description
                                </dt>
                                <dd class="mt-1 text-sm">
                                    {{
                                        accessPoint.description ||
                                        'No description provided.'
                                    }}
                                </dd>
                            </div>
                        </dl>
                    </CardContent>
                </Card>

                <div class="mt-4">
                    <ConnectionManager
                        :device="accessPoint"
                        device-type="Modules\ActiveDevice\Models\AccessPoint"
                        :connections="accessPoint.source_connections"
                        :incoming-connections="
                            accessPoint.destination_connections
                        "
                        :available-devices="availableDevices"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
