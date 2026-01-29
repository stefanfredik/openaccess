<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
    ArrowRightLeft,
    CheckCircle2,
    Info,
    LockKeyhole,
    Monitor,
    Network,
    PencilLine,
    ShieldCheck,
    Zap,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import InterfaceManager from './InterfaceManager.vue';

const props = defineProps<{
    device: any;
}>();

// Ensure arrays are valid
const activeServicePorts = computed(
    () => props.device.service_ports || props.device.servicePorts || [],
);
const sourceConnections = computed(
    () =>
        props.device.source_connections || props.device.sourceConnections || [],
);
const destinationConnections = computed(
    () =>
        props.device.destination_connections ||
        props.device.destinationConnections ||
        [],
);
const activeInterfaces = computed(() => props.device.interfaces || []);
const isInterfaceManaging = ref(false);

const ethernetInterfaces = computed(() => {
    return activeInterfaces.value.filter(
        (i: any) =>
            (i.type === 'Ethernet' || i.type === 'Gigabit') &&
            !i.name.toUpperCase().includes('PON'),
    );
});

const opticalInterfaces = computed(() => {
    return activeInterfaces.value.filter(
        (i: any) =>
            (i.type === 'Fiber' || i.type === 'Optical') &&
            !i.name.toUpperCase().includes('PON'),
    );
});

const ponInterfaces = computed(() => {
    return activeInterfaces.value.filter(
        (i: any) => i.type === 'PON' || i.name.toUpperCase().includes('PON'),
    );
});

const allConnections = computed(() => {
    const outgoing = sourceConnections.value.map((c: any) => ({
        ...c,
        role: 'Source', // This device is the source
        local_label: 'Local (Source)',
        remote_label: 'Remote (Dest)',
        peer: c.destination,
        peer_port_display: c.destination_interface?.name ?? c.destination_port,
        local_port_display: c.source_interface?.name ?? c.source_port,
        direction_icon: 'arrow-right',
    }));

    const incoming = destinationConnections.value.map((c: any) => ({
        ...c,
        role: 'Destination', // This device is the destination
        local_label: 'Local (Dest)',
        remote_label: 'Remote (Source)',
        peer: c.source,
        peer_port_display: c.source_interface?.name ?? c.source_port,
        local_port_display: c.destination_interface?.name ?? c.destination_port,
        direction_icon: 'arrow-left',
    }));

    return [...outgoing, ...incoming];
});

const deviceType = computed(() => {
    // Determine type based on properties or defaults
    if (props.device.pon_type) return 'Modules\\ActiveDevice\\Models\\Olt';
    if (props.device.onu_type) return 'Modules\\ActiveDevice\\Models\\Ont';
    if (props.device.switch_type)
        return 'Modules\\ActiveDevice\\Models\\AdSwitch';
    if (props.device.type && props.device.brand) {
        // Generic check for AP/CPE if we can't be sure, but usually namespaces help
        if (
            props.device.type.includes('AP') ||
            props.device.type.includes('Access')
        )
            return 'Modules\\ActiveDevice\\Models\\AccessPoint';
    }
    // Check for CPE specific fields or breadcrumbs context if possible,
    // but here we rely on the object structure
    if (
        props.device.model &&
        !props.device.pon_type &&
        !props.device.switch_type
    ) {
        // Fallback or specific markers
        if (props.device.type === 'CPE') return 'Modules\\Cpe\\Models\\Cpe';
    }

    return 'Modules\\ActiveDevice\\Models\\Router';
});

const deviceSubtitle = computed(() => {
    let type = 'Device';
    if (props.device.pon_type) type = 'OLT';
    else if (props.device.onu_type) type = 'ONT';
    else if (props.device.switch_type) type = 'Switch';
    else if (props.device.type === 'CPE') type = 'CPE';
    else if (props.device.type?.includes('Access')) type = 'Access Point';
    else if (props.device.ip_address) type = 'Router';

    return `${type} - ${props.device.brand || ''} ${props.device.model || ''}`;
});

// Dynamic details based on device type
const deviceDetails = computed(() => {
    const details: Record<string, any> = {
        'Serial Number': props.device.serial_number,
        'MAC Address': props.device.mac_address,
        'IP Address': props.device.ip_address,
        'Area / Wilayah': props.device.area?.name,
        'POP Location': props.device.pop?.name,
        Status:
            props.device.status ||
            (props.device.is_active ? 'Active' : 'Inactive'),
        Description: props.device.description,
    };

    // Specific fields
    if (props.device.pon_type) {
        details['PON Type'] = props.device.pon_type;
    }
    if (props.device.onu_type) {
        details['ONT Type'] = props.device.onu_type;
    }
    if (props.device.switch_type) {
        details['Switch Type'] = props.device.switch_type;
    }
    if (props.device.port_count) {
        details['Total Ports'] = props.device.port_count;
    }
    if (props.device.purchase_year) {
        details['Tahun Perolehan'] = props.device.purchase_year;
    }
    if (props.device.installed_at) {
        details['Tanggal Instalasi'] = props.device.installed_at;
    }

    return details;
});
</script>

<template>
    <div class="flex h-full flex-col">
        <div class="border-b bg-slate-50 p-6">
            <h2 class="text-xl font-bold text-slate-900">{{ device.name }}</h2>
            <p class="text-sm text-slate-500 italic">
                {{ deviceSubtitle }}
            </p>
        </div>

        <Tabs default-value="detail" class="flex flex-1 flex-col">
            <TabsList
                class="h-12 w-full justify-start space-x-2 rounded-none border-b bg-white px-6"
            >
                <TabsTrigger
                    value="detail"
                    class="relative flex h-12 items-center gap-2 rounded-none border-b-2 border-transparent px-2 text-xs font-bold tracking-wider text-slate-400 uppercase hover:text-blue-600 data-[state=active]:border-blue-600 data-[state=active]:text-blue-600"
                >
                    <Info class="h-4 w-4" />
                    Info
                </TabsTrigger>
                <TabsTrigger
                    value="services"
                    class="relative flex h-12 items-center gap-2 rounded-none border-b-2 border-transparent px-2 text-xs font-bold tracking-wider text-slate-400 uppercase hover:text-blue-600 data-[state=active]:border-blue-600 data-[state=active]:text-blue-600"
                >
                    <ShieldCheck class="h-4 w-4" />
                    Services
                </TabsTrigger>
                <TabsTrigger
                    value="interfaces"
                    class="relative flex h-12 items-center gap-2 rounded-none border-b-2 border-transparent px-2 text-xs font-bold tracking-wider text-slate-400 uppercase hover:text-blue-600 data-[state=active]:border-blue-600 data-[state=active]:text-blue-600"
                >
                    <Monitor class="h-4 w-4" />
                    Ports
                </TabsTrigger>
                <TabsTrigger
                    value="topology"
                    class="relative flex h-12 items-center gap-2 rounded-none border-b-2 border-transparent px-2 text-xs font-bold tracking-wider text-slate-400 uppercase hover:text-blue-600 data-[state=active]:border-blue-600 data-[state=active]:text-blue-600"
                >
                    <Network class="h-4 w-4" />
                    Links
                </TabsTrigger>
            </TabsList>

            <div class="flex-1 overflow-y-auto">
                <!-- Details Tab -->
                <TabsContent value="detail" class="m-0 p-6">
                    <h3
                        class="mb-4 text-xs font-bold tracking-widest text-gray-400 uppercase"
                    >
                        Device Information
                    </h3>
                    <div
                        class="overflow-hidden rounded-lg border border-slate-100 bg-white shadow-sm"
                    >
                        <table class="w-full text-left text-sm">
                            <tbody class="divide-y divide-gray-50">
                                <tr
                                    v-for="(val, key) in deviceDetails"
                                    :key="key"
                                    class="transition-colors hover:bg-slate-50/50"
                                >
                                    <td
                                        class="w-1/3 border-r border-slate-50/50 bg-slate-50/30 px-4 py-3 font-medium text-slate-500"
                                    >
                                        {{ key }}
                                    </td>
                                    <td
                                        class="px-4 py-3 font-semibold text-slate-700"
                                    >
                                        {{ val || '-' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </TabsContent>

                <!-- Services Tab -->
                <TabsContent value="services" class="m-0 space-y-4 p-6">
                    <h3
                        class="text-xs font-bold tracking-widest text-gray-400 uppercase"
                    >
                        Active Services
                    </h3>
                    <div class="grid grid-cols-1 gap-3">
                        <div
                            v-for="port in activeServicePorts"
                            :key="port.id"
                            class="group flex items-center justify-between rounded-lg border p-3 transition hover:border-blue-200"
                        >
                            <div class="flex items-center space-x-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded border bg-slate-50 font-bold text-slate-600 italic group-hover:border-blue-100 group-hover:bg-blue-50 group-hover:text-blue-600"
                                >
                                    {{ port.port }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold">
                                        {{ port.name }}
                                    </p>
                                    <p
                                        :class="[
                                            'text-xs',
                                            port.status === 'Active'
                                                ? 'text-green-500'
                                                : 'text-gray-400',
                                        ]"
                                    >
                                        Status:
                                        {{
                                            port.status === 'Active'
                                                ? 'Running'
                                                : 'Closed'
                                        }}
                                    </p>
                                </div>
                            </div>
                            <CheckCircle2
                                v-if="port.status === 'Active'"
                                class="h-5 w-5 text-green-500"
                            />
                            <LockKeyhole v-else class="h-5 w-5 text-gray-300" />
                        </div>
                        <div
                            v-if="activeServicePorts.length === 0"
                            class="py-8 text-center text-sm text-muted-foreground italic"
                        >
                            No active services configured.
                        </div>
                    </div>
                </TabsContent>

                <!-- Interfaces Tab -->
                <TabsContent value="interfaces" class="m-0 p-6">
                    <h3
                        class="mb-4 text-xs font-bold tracking-widest text-gray-400 uppercase"
                    >
                        Physical Ports
                    </h3>

                    <div v-if="!isInterfaceManaging">
                        <div class="mb-4 flex justify-end">
                            <Button
                                variant="outline"
                                size="sm"
                                @click="isInterfaceManaging = true"
                                class="h-8"
                            >
                                <PencilLine class="mr-2 h-4 w-4" />
                                Manage Ports
                            </Button>
                        </div>

                        <div
                            v-if="activeInterfaces.length > 0"
                            class="space-y-6"
                        >
                            <!-- Ethernet Ports -->
                            <div v-if="ethernetInterfaces.length > 0">
                                <h4
                                    class="mb-3 text-xs font-bold tracking-widest text-slate-500 uppercase"
                                >
                                    Ethernet (RJ45)
                                </h4>
                                <div
                                    class="grid grid-cols-4 gap-2 sm:grid-cols-5"
                                >
                                    <div
                                        v-for="inf in ethernetInterfaces"
                                        :key="inf.id"
                                        :class="[
                                            'relative flex aspect-square flex-col items-center justify-center rounded-lg border-2 p-2 transition',
                                            inf.status === 'up'
                                                ? 'border-green-500 bg-green-50 text-green-700'
                                                : inf.status === 'error'
                                                  ? 'border-red-200 bg-red-50 text-red-500'
                                                  : 'border-slate-200 bg-white text-slate-400 hover:border-slate-300',
                                        ]"
                                        :title="
                                            inf.name +
                                            ' - ' +
                                            (inf.status === 'up'
                                                ? 'Connected'
                                                : 'Idle')
                                        "
                                    >
                                        <!-- Status Dot -->
                                        <div
                                            v-if="inf.status === 'up'"
                                            class="absolute top-1.5 right-1.5 h-2 w-2 rounded-full bg-green-500"
                                        ></div>
                                        <Monitor
                                            class="mb-1 h-5 w-5 opacity-80"
                                        />
                                        <span
                                            class="text-[10px] font-bold tracking-tighter uppercase"
                                            >{{ inf.name }}</span
                                        >
                                    </div>
                                </div>
                            </div>

                            <!-- Optical Ports -->
                            <div v-if="opticalInterfaces.length > 0">
                                <h4
                                    class="mb-3 text-xs font-bold tracking-widest text-slate-500 uppercase"
                                >
                                    Optical (SFP/SFP+)
                                </h4>
                                <div
                                    class="grid grid-cols-4 gap-2 sm:grid-cols-5"
                                >
                                    <div
                                        v-for="inf in opticalInterfaces"
                                        :key="inf.id"
                                        :class="[
                                            'relative flex aspect-square flex-col items-center justify-center rounded-lg border-2 p-2 transition',
                                            inf.status === 'up'
                                                ? 'border-blue-500 bg-blue-50 text-blue-700'
                                                : inf.status === 'error'
                                                  ? 'border-red-200 bg-red-50 text-red-500'
                                                  : 'border-slate-200 bg-white text-slate-400 hover:border-slate-300',
                                        ]"
                                        :title="
                                            inf.name +
                                            ' - ' +
                                            (inf.status === 'up'
                                                ? 'Connected'
                                                : 'Idle')
                                        "
                                    >
                                        <!-- Status Dot -->
                                        <div
                                            v-if="inf.status === 'up'"
                                            class="absolute top-1.5 right-1.5 h-2 w-2 rounded-full bg-blue-500"
                                        ></div>
                                        <div
                                            v-else-if="inf.status === 'error'"
                                            class="absolute top-1.5 right-1.5 h-2 w-2 rounded-full bg-red-500"
                                        ></div>

                                        <Zap class="mb-1 h-5 w-5 opacity-80" />
                                        <span
                                            class="text-[10px] font-bold tracking-tighter uppercase"
                                            >{{ inf.name }}</span
                                        >
                                    </div>
                                </div>
                            </div>

                            <!-- PON Ports -->
                            <div v-if="ponInterfaces.length > 0">
                                <h4
                                    class="mb-3 text-xs font-bold tracking-widest text-slate-500 uppercase"
                                >
                                    PON Ports
                                </h4>
                                <div
                                    class="grid grid-cols-4 gap-2 sm:grid-cols-5"
                                >
                                    <div
                                        v-for="inf in ponInterfaces"
                                        :key="inf.id"
                                        :class="[
                                            'relative flex aspect-square flex-col items-center justify-center rounded-lg border-2 p-2 transition',
                                            inf.status === 'up'
                                                ? 'border-orange-500 bg-orange-50 text-orange-700'
                                                : inf.status === 'error'
                                                  ? 'border-red-200 bg-red-50 text-red-500'
                                                  : 'border-slate-200 bg-white text-slate-400 hover:border-slate-300',
                                        ]"
                                        :title="
                                            inf.name +
                                            ' - ' +
                                            (inf.status === 'up'
                                                ? 'Connected'
                                                : 'Idle')
                                        "
                                    >
                                        <!-- Status Dot -->
                                        <div
                                            v-if="inf.status === 'up'"
                                            class="absolute top-1.5 right-1.5 h-2 w-2 rounded-full bg-orange-500"
                                        ></div>
                                        <div
                                            v-else-if="inf.status === 'error'"
                                            class="absolute top-1.5 right-1.5 h-2 w-2 rounded-full bg-red-500"
                                        ></div>

                                        <Zap class="mb-1 h-5 w-5 opacity-80" />
                                        <span
                                            class="text-[10px] font-bold tracking-tighter uppercase"
                                            >{{ inf.name }}</span
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            v-else
                            class="py-8 text-center text-sm text-muted-foreground italic"
                        >
                            No physical interfaces found.
                        </div>
                    </div>

                    <div v-else>
                        <div class="mb-4">
                            <Button
                                variant="ghost"
                                size="sm"
                                @click="isInterfaceManaging = false"
                                class="h-8"
                            >
                                ← Back to Visualization
                            </Button>
                        </div>
                        <InterfaceManager
                            :device-id="device.id"
                            :device-type="deviceType"
                            :interfaces="activeInterfaces"
                        />
                    </div>
                </TabsContent>

                <!-- Topology/Links Tab -->
                <TabsContent value="topology" class="m-0 p-6">
                    <h3
                        class="mb-6 text-xs font-bold tracking-widest text-gray-400 uppercase"
                    >
                        Active Connections
                    </h3>

                    <div class="relative space-y-6">
                        <div
                            class="absolute top-2 bottom-2 left-[7px] w-0.5 border-l-2 border-dashed border-blue-100"
                        ></div>

                        <div
                            v-for="conn in allConnections"
                            :key="conn.id"
                            class="relative pl-8"
                        >
                            <div
                                :class="[
                                    'absolute top-1 left-0 h-4 w-4 rounded-full border-2 border-white shadow-sm',
                                    conn.role === 'Source'
                                        ? 'bg-blue-600'
                                        : 'bg-green-600',
                                ]"
                            ></div>
                            <div
                                class="rounded-lg border border-blue-100 bg-blue-50 p-4"
                            >
                                <div
                                    class="mb-2 flex items-center justify-between"
                                >
                                    <span
                                        :class="[
                                            'rounded px-2 py-0.5 text-[10px] font-bold tracking-tight text-white uppercase',
                                            conn.role === 'Source'
                                                ? 'bg-blue-600'
                                                : 'bg-green-600',
                                        ]"
                                    >
                                        {{ conn.local_label }}:
                                        {{ conn.local_port_display }}
                                    </span>
                                    <span
                                        class="text-[10px] font-bold tracking-widest text-blue-600 uppercase"
                                    >
                                        {{ conn.connection_type }}
                                    </span>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <ArrowRightLeft
                                        class="h-5 w-5 text-blue-400"
                                    />
                                    <div>
                                        <p
                                            class="text-sm font-bold text-slate-800"
                                        >
                                            {{
                                                conn.peer?.name ||
                                                'Unknown Device'
                                            }}
                                        </p>
                                        <p class="text-xs text-slate-500">
                                            {{ conn.remote_label }}:
                                            <span
                                                class="font-mono text-blue-600"
                                            >
                                                {{ conn.peer_port_display }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="allConnections.length === 0"
                            class="py-8 text-center text-sm text-muted-foreground italic"
                        >
                            No active connections.
                        </div>

                        <!-- Note: Adding connections usually happens on the details page, so we just show status here -->
                        <div class="mt-4 text-center">
                            <p class="text-xs text-muted-foreground">
                                To add or manage connections, visit the full
                                device details page.
                            </p>
                        </div>
                    </div>
                </TabsContent>
            </div>
        </Tabs>
    </div>
</template>
