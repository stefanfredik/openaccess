<script setup lang="ts">
import ConnectionManager from '@/../../Modules/ActiveDevice/resources/assets/js/Components/ConnectionManager.vue';
import InterfaceManager from '@/../../Modules/ActiveDevice/resources/assets/js/Components/InterfaceManager.vue';
import RouterOverview from '@/../../Modules/ActiveDevice/resources/assets/js/Components/RouterOverview.vue';
import ServicePortsManager from '@/../../Modules/ActiveDevice/resources/assets/js/Components/ServicePortsManager.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Pencil } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    router: any;
    availableDevices: Array<any>;
}>();

const interfaceManagerRef = ref<InstanceType<typeof InterfaceManager> | null>(
    null,
);
const connectionManagerRef = ref<InstanceType<typeof ConnectionManager> | null>(
    null,
);
</script>

<template>
    <Head :title="`Router: ${router.name}`" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Routers', href: route('active-device.router.index') },
            { title: router.name, href: '#' },
        ]"
    >
        <div class="max-w-7xl space-y-6 p-4 md:p-6">
            <!-- Page Header -->
            <div
                class="flex flex-col justify-between gap-4 md:flex-row md:items-center"
            >
                <div class="space-y-1">
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-bold tracking-tight">
                            {{ router.name }}
                        </h1>
                        <Badge
                            :variant="
                                router.is_active ? 'default' : 'destructive'
                            "
                            class="capitalize"
                        >
                            {{ router.is_active ? 'Active' : 'Inactive' }}
                        </Badge>
                    </div>
                    <p
                        class="flex items-center gap-2 text-sm text-muted-foreground"
                    >
                        <span
                            class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-xs dark:bg-slate-800"
                            >{{ router.code }}</span
                        >
                        <span>&bull;</span>
                        <span>{{ router.brand }} {{ router.model }}</span>
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="route('active-device.router.index')">
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Kembali
                        </Link>
                    </Button>
                    <Button size="sm" as-child>
                        <Link
                            :href="
                                route('active-device.router.edit', router.id)
                            "
                        >
                            <Pencil class="mr-2 h-4 w-4" />
                            Edit Router
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Mobile View: Stacked Layout -->
            <div class="flex flex-col space-y-8 md:hidden">
                <!-- Overview -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold">Overview</h3>
                    <RouterOverview :router="router" />
                </div>

                <!-- Service Ports -->
                <ServicePortsManager
                    :device="router"
                    device-type="Modules\ActiveDevice\Models\Router"
                />

                <!-- Physical Interfaces -->
                <InterfaceManager
                    ref="interfaceManagerRef"
                    :device-id="router.id"
                    :device-type="'Modules\\ActiveDevice\\Models\\Router'"
                    :interfaces="router.interfaces || []"
                />

                <!-- Connectivity -->
                <ConnectionManager
                    ref="connectionManagerRef"
                    :device="router"
                    device-type="Modules\ActiveDevice\Models\Router"
                    :connections="router.source_connections"
                    :incoming-connections="router.destination_connections"
                    :available-devices="availableDevices"
                />
            </div>

            <!-- Desktop View: Tabs Layout -->
            <Tabs
                default-value="overview"
                class="hidden w-full flex-col gap-8 md:flex md:flex-row"
            >
                <TabsList
                    class="h-auto w-full flex-col justify-start space-y-1 bg-transparent p-0 md:w-64"
                >
                    <TabsTrigger
                        value="overview"
                        class="w-full justify-start px-4 py-2 text-left transition-all data-[state=active]:bg-primary/10 data-[state=active]:text-primary"
                    >
                        Overview
                    </TabsTrigger>
                    <TabsTrigger
                        value="services"
                        class="w-full justify-start px-4 py-2 text-left transition-all data-[state=active]:bg-primary/10 data-[state=active]:text-primary"
                    >
                        Service Ports
                    </TabsTrigger>
                    <TabsTrigger
                        value="interfaces"
                        class="w-full justify-start px-4 py-2 text-left transition-all data-[state=active]:bg-primary/10 data-[state=active]:text-primary"
                    >
                        Physical Interfaces
                    </TabsTrigger>
                    <TabsTrigger
                        value="connectivity"
                        class="w-full justify-start px-4 py-2 text-left transition-all data-[state=active]:bg-primary/10 data-[state=active]:text-primary"
                    >
                        Connectivity
                    </TabsTrigger>
                </TabsList>

                <div class="flex-1">
                    <!-- Tab 1: Overview -->
                    <TabsContent value="overview" class="mt-0 space-y-6">
                        <RouterOverview :router="router" />
                    </TabsContent>

                    <!-- Tab 2: Service Ports -->
                    <TabsContent value="services" class="mt-0 space-y-6">
                        <ServicePortsManager
                            :device="router"
                            device-type="Modules\ActiveDevice\Models\Router"
                        />
                    </TabsContent>

                    <!-- Tab 3: Physical Interfaces -->
                    <TabsContent value="interfaces" class="mt-0 space-y-6">
                        <InterfaceManager
                            ref="interfaceManagerRef"
                            :device-id="router.id"
                            :device-type="'Modules\\ActiveDevice\\Models\\Router'"
                            :interfaces="router.interfaces || []"
                        />
                    </TabsContent>

                    <!-- Tab 4: Connectivity -->
                    <TabsContent value="connectivity" class="mt-0 space-y-6">
                        <ConnectionManager
                            ref="connectionManagerRef"
                            :device="router"
                            device-type="Modules\ActiveDevice\Models\Router"
                            :connections="router.source_connections"
                            :incoming-connections="
                                router.destination_connections
                            "
                            :available-devices="availableDevices"
                        />
                    </TabsContent>
                </div>
            </Tabs>
        </div>
    </AppLayout>
</template>
