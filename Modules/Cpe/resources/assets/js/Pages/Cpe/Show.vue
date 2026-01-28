<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Pencil } from 'lucide-vue-next';

import ConnectionManager from '@/../../Modules/ActiveDevice/resources/assets/js/Components/ConnectionManager.vue';

const props = defineProps<{
    cpe: any;
    availableDevices: Array<any>;
}>();
</script>

<template>
    <Head :title="`CPE: ${cpe.name}`" />

    <AppLayout
        :breadcrumbs="[
            { title: 'CPEs', href: route('cpe.index') },
            { title: 'CPE Detail', href: '#' },
        ]"
    >
        <div class="flex w-full flex-col gap-6 p-4 md:p-6 lg:p-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="outline" size="icon" as-child>
                        <Link :href="route('cpe.index')">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">
                            {{ cpe.name }}
                        </h1>
                        <p class="text-muted-foreground">
                            Detail information for CPE {{ cpe.code }}
                        </p>
                    </div>
                </div>
                <Button as-child>
                    <Link :href="route('cpe.edit', cpe.id)">
                        <Pencil class="mr-2 h-4 w-4" />
                        Edit CPE
                    </Link>
                </Button>
            </div>

            <div class="flex flex-col gap-6">
                <div class="grid gap-6 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Basic Information</CardTitle>
                        </CardHeader>
                        <CardContent class="grid gap-4">
                            <div class="grid grid-cols-2 gap-1">
                                <span
                                    class="text-sm font-medium text-muted-foreground"
                                    >Code</span
                                >
                                <span>{{ cpe.code }}</span>
                            </div>
                            <div class="grid grid-cols-2 gap-1">
                                <span
                                    class="text-sm font-medium text-muted-foreground"
                                    >Type</span
                                >
                                <span>{{ cpe.type }}</span>
                            </div>
                            <div class="grid grid-cols-2 gap-1">
                                <span
                                    class="text-sm font-medium text-muted-foreground"
                                    >Status</span
                                >
                                <div>
                                    <Badge
                                        :variant="
                                            cpe.status === 'Active'
                                                ? 'default'
                                                : 'destructive'
                                        "
                                    >
                                        {{ cpe.status }}
                                    </Badge>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-1">
                                <span
                                    class="text-sm font-medium text-muted-foreground"
                                    >Area</span
                                >
                                <span>{{ cpe.area?.name || '-' }}</span>
                            </div>
                            <div class="grid grid-cols-2 gap-1">
                                <span
                                    class="text-sm font-medium text-muted-foreground"
                                    >Address</span
                                >
                                <span>{{ cpe.address }}</span>
                            </div>
                            <div class="grid grid-cols-2 gap-1">
                                <span
                                    class="text-sm font-medium text-muted-foreground"
                                    >Installed At</span
                                >
                                <span>{{ cpe.installed_at || '-' }}</span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Hardware Details</CardTitle>
                        </CardHeader>
                        <CardContent class="grid gap-4">
                            <div class="grid grid-cols-2 gap-1">
                                <span
                                    class="text-sm font-medium text-muted-foreground"
                                    >Brand</span
                                >
                                <span>{{ cpe.brand || '-' }}</span>
                            </div>
                            <div class="grid grid-cols-2 gap-1">
                                <span
                                    class="text-sm font-medium text-muted-foreground"
                                    >Model</span
                                >
                                <span>{{ cpe.model || '-' }}</span>
                            </div>
                            <div class="grid grid-cols-2 gap-1">
                                <span
                                    class="text-sm font-medium text-muted-foreground"
                                    >Serial Number</span
                                >
                                <span>{{ cpe.serial_number || '-' }}</span>
                            </div>
                            <div class="grid grid-cols-2 gap-1">
                                <span
                                    class="text-sm font-medium text-muted-foreground"
                                    >MAC Address</span
                                >
                                <span>{{ cpe.mac_address || '-' }}</span>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Description</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="whitespace-pre-wrap">
                            {{ cpe.description || 'No description provided.' }}
                        </p>
                    </CardContent>
                </Card>

                <div class="mt-4">
                    <ConnectionManager
                        :device="cpe"
                        device-type="Modules\Cpe\Models\Cpe"
                        :connections="cpe.source_connections"
                        :incoming-connections="cpe.destination_connections"
                        :available-devices="availableDevices"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
