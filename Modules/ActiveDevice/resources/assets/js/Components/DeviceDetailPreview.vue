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
        Username: props.device.username,
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
        <div class="border-b bg-muted/40 p-6">
            <h2 class="text-xl font-bold text-foreground">{{ device.name }}</h2>
            <p class="text-sm text-muted-foreground italic">
                {{ deviceSubtitle }}
            </p>
        </div>

        <Tabs default-value="detail" class="flex flex-1 flex-col">
            <TabsList
                class="h-12 w-full justify-start space-x-2 rounded-none border-b bg-background px-6"
            >
                <TabsTrigger
                    value="detail"
                    class="relative flex h-12 items-center gap-2 rounded-none border-b-2 border-transparent px-2 text-xs font-bold tracking-wider text-muted-foreground uppercase hover:text-primary data-[state=active]:border-primary data-[state=active]:text-primary"
                >
                    <Info class="h-4 w-4" />
                    Info
                </TabsTrigger>
                <TabsTrigger
                    v-if="device.switch_type !== 'Unmanageable'"
                    value="services"
                    class="relative flex h-12 items-center gap-2 rounded-none border-b-2 border-transparent px-2 text-xs font-bold tracking-wider text-muted-foreground uppercase hover:text-primary data-[state=active]:border-primary data-[state=active]:text-primary"
                >
                    <ShieldCheck class="h-4 w-4" />
                    Services
                </TabsTrigger>
                <TabsTrigger
                    value="interfaces"
                    class="relative flex h-12 items-center gap-2 rounded-none border-b-2 border-transparent px-2 text-xs font-bold tracking-wider text-muted-foreground uppercase hover:text-primary data-[state=active]:border-primary data-[state=active]:text-primary"
                >
                    <Monitor class="h-4 w-4" />
                    Ports
                </TabsTrigger>
                <TabsTrigger
                    value="topology"
                    class="relative flex h-12 items-center gap-2 rounded-none border-b-2 border-transparent px-2 text-xs font-bold tracking-wider text-muted-foreground uppercase hover:text-primary data-[state=active]:border-primary data-[state=active]:text-primary"
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

                    <!-- Device Photo -->
                    <div v-if="device.photo" class="mb-6">
                        <img
                            :src="'/storage/' + device.photo"
                            alt="Device Photo"
                            class="aspect-video w-full rounded-xl border border-slate-200 object-cover shadow-sm"
                        />
                    </div>

                    <div
                        class="overflow-hidden rounded-lg border border-border bg-card shadow-sm"
                    >
                        <table class="w-full text-left text-sm">
                            <tbody class="divide-y divide-border/50">
                                <tr
                                    v-for="(val, key) in deviceDetails"
                                    :key="key"
                                    class="transition-colors hover:bg-muted/30"
                                >
                                    <td
                                        class="w-1/3 border-r border-border/50 bg-muted/20 px-4 py-3 font-medium text-muted-foreground"
                                    >
                                        {{ key }}
                                    </td>
                                    <td
                                        class="px-4 py-3 font-semibold text-foreground"
                                    >
                                        {{ val || '-' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </TabsContent>

                <!-- Services Tab -->
                <TabsContent
                    v-if="device.switch_type !== 'Unmanageable'"
                    value="services"
                    class="m-0 space-y-4 p-6"
                >
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
                                    class="flex h-10 w-10 items-center justify-center rounded border bg-muted/50 font-bold text-muted-foreground italic group-hover:border-primary/20 group-hover:bg-primary/10 group-hover:text-primary"
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
                                                ? 'border-emerald-500/50 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                                                : inf.status === 'error'
                                                  ? 'border-red-500/50 bg-red-500/10 text-red-500'
                                                  : 'border-border bg-card text-muted-foreground hover:border-muted-foreground/30',
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
                                                ? 'border-blue-500/50 bg-blue-500/10 text-blue-600 dark:text-blue-400'
                                                : inf.status === 'error'
                                                  ? 'border-red-500/50 bg-red-500/10 text-red-500'
                                                  : 'border-border bg-card text-muted-foreground hover:border-muted-foreground/30',
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
                                                ? 'border-orange-500/50 bg-orange-500/10 text-orange-600 dark:text-orange-400'
                                                : inf.status === 'error'
                                                  ? 'border-red-500/50 bg-red-500/10 text-red-500'
                                                  : 'border-border bg-card text-muted-foreground hover:border-muted-foreground/30',
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
                            class="absolute top-2 bottom-2 left-[7px] w-0.5 border-l-2 border-dashed border-border"
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
                                class="rounded-lg border border-border bg-muted/40 p-4"
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
                                        class="text-[10px] font-bold tracking-widest text-primary uppercase"
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
                                            class="text-sm font-bold text-foreground"
                                        >
                                            {{
                                                conn.peer?.name ||
                                                'Unknown Device'
                                            }}
                                        </p>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{ conn.remote_label }}:
                                            <span
                                                class="font-mono text-primary"
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
