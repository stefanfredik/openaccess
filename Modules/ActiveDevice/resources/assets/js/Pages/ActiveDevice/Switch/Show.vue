<script setup lang="ts">
import ConnectionManager from '@/../../Modules/ActiveDevice/resources/assets/js/Components/ConnectionManager.vue';
import InterfaceManager from '@/../../Modules/ActiveDevice/resources/assets/js/Components/InterfaceManager.vue';
import ServicePortsManager from '@/../../Modules/ActiveDevice/resources/assets/js/Components/ServicePortsManager.vue';
import SwitchOverview from '@/../../Modules/ActiveDevice/resources/assets/js/Components/SwitchOverview.vue';
import { Button } from '@/components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Pencil } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    networkSwitch: any;
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
    <Head :title="`Switch: ${networkSwitch.name}`" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Switches', href: route('active-device.switch.index') },
            { title: networkSwitch.name, href: '#' },
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
                            {{ networkSwitch.name }}
                        </h1>
                    </div>
                    <p
                        class="flex items-center gap-2 text-sm text-muted-foreground"
                    >
                        <span
                            class="rounded bg-muted px-1.5 py-0.5 font-mono text-[10px] uppercase"
                            >{{ networkSwitch.code }}</span
                        >
                        <span>&bull;</span>
                        <span
                            >{{ networkSwitch.brand }}
                            {{ networkSwitch.model }}</span
                        >
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="route('active-device.switch.index')">
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Back to List
                        </Link>
                    </Button>
                    <Button size="sm" as-child>
                        <Link
                            :href="
                                route(
                                    'active-device.switch.edit',
                                    networkSwitch.id,
                                )
                            "
                        >
                            <Pencil class="mr-2 h-4 w-4" />
                            Edit Switch
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Mobile View: Stacked Layout -->
            <div class="flex flex-col space-y-8 md:hidden">
                <!-- Overview -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold">Overview</h3>
                    <SwitchOverview :network-switch="networkSwitch" />
                </div>

                <!-- Service Ports -->
                <ServicePortsManager
                    :device="networkSwitch"
                    device-type="Modules\ActiveDevice\Models\AdSwitch"
                />

                <!-- Physical Interfaces -->
                <InterfaceManager
                    ref="interfaceManagerRef"
                    :device-id="networkSwitch.id"
                    device-type="Modules\ActiveDevice\Models\AdSwitch"
                    :interfaces="networkSwitch.interfaces || []"
                />

                <!-- Connectivity -->
                <ConnectionManager
                    ref="connectionManagerRef"
                    :device="networkSwitch"
                    device-type="Modules\ActiveDevice\Models\AdSwitch"
                    :connections="networkSwitch.source_connections || []"
                    :incoming-connections="
                        networkSwitch.destination_connections || []
                    "
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
                        <SwitchOverview :network-switch="networkSwitch" />
                    </TabsContent>

                    <!-- Tab 2: Service Ports -->
                    <TabsContent value="services" class="mt-0 space-y-6">
                        <ServicePortsManager
                            :device="networkSwitch"
                            device-type="Modules\ActiveDevice\Models\AdSwitch"
                        />
                    </TabsContent>

                    <!-- Tab 3: Physical Interfaces -->
                    <TabsContent value="interfaces" class="mt-0 space-y-6">
                        <InterfaceManager
                            ref="interfaceManagerRef"
                            :device-id="networkSwitch.id"
                            device-type="Modules\ActiveDevice\Models\AdSwitch"
                            :interfaces="networkSwitch.interfaces || []"
                        />
                    </TabsContent>

                    <!-- Tab 4: Connectivity -->
                    <TabsContent value="connectivity" class="mt-0 space-y-6">
                        <ConnectionManager
                            ref="connectionManagerRef"
                            :device="networkSwitch"
                            device-type="Modules\ActiveDevice\Models\AdSwitch"
                            :connections="networkSwitch.source_connections"
                            :incoming-connections="
                                networkSwitch.destination_connections
                            "
                            :available-devices="availableDevices"
                        />
                    </TabsContent>
                </div>
            </Tabs>
        </div>
    </AppLayout>
</template>
