<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Network,
    Server,
    Router as RouterIcon,
    Wifi,
    Laptop,
    Activity,
    ZoomIn,
    ZoomOut,
    Maximize,
    Save,
    List,
    Layers,
    Type,
    Lock,
    Unlock,
    Cable,
} from 'lucide-vue-next';
import { ref, computed, onMounted, watch } from 'vue';
import { toast } from 'vue-sonner';
import { update as updatePositionsRoute } from '@/routes/active-device/topology/positions';
import { store as storeConnectionRoute } from '@/routes/active-device/connections';
import { details as deviceDetailsRoute } from '@/routes/active-device/topology';
import axios from 'axios';
import ConnectionDialog from '../../Components/ActiveDevice/ConnectionDialog.vue';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';

const props = defineProps<{
    topology: Array<any>;
}>();

// --- Graph Logic ---
interface Node {
    id: string;
    uid: string; // unique string id
    name: string;
    code: string;
    type: string;
    status: string;
    ip_address: string;
    level: number;
    x: number;
    y: number;
}

interface Edge {
    id: string;
    source: string;
    target: string;
    type: string;
    source_port: string;
    dest_port: string;
}

const nodes = ref<Node[]>([]);
const edges = ref<Edge[]>([]);
const showAllLines = ref(false);
const showDeviceLabels = ref(true);
const showLinkLabels = ref(true);

const getEdgePoints = (edge: Edge) => {
    const sourceNode = nodes.value.find((n) => n.uid === edge.source);
    const targetNode = nodes.value.find((n) => n.uid === edge.target);

    if (!sourceNode || !targetNode) return null;

    const x1 = sourceNode.x + 40;
    const y1 = sourceNode.y + 30;
    const x2 = targetNode.x + 40;
    const y2 = targetNode.y + 30;

    if (!showAllLines.value) {
        return { x1, y1, x2, y2 };
    }

    const siblings = edges.value.filter(
        (e) =>
            (e.source === edge.source && e.target === edge.target) ||
            (e.source === edge.target && e.target === edge.source),
    );

    if (siblings.length <= 1) {
        return { x1, y1, x2, y2 };
    }

    const index = siblings.findIndex((e) => e.id === edge.id);
    const spacing = 12;
    const offset = (index - (siblings.length - 1) / 2) * spacing;

    // Calculate normal vector
    const dx = x2 - x1;
    const dy = y2 - y1;
    const length = Math.sqrt(dx * dx + dy * dy);

    if (length === 0) return { x1, y1, x2, y2 };

    const nx = -dy / length; // Perpendicular unit vector X
    const ny = dx / length; // Perpendicular unit vector Y

    return {
        x1: x1 + nx * offset,
        y1: y1 + ny * offset,
        x2: x2 + nx * offset,
        y2: y2 + ny * offset,
    };
};

const flattenTopology = (items: any[], level = 0, parentUid = '') => {
    items.forEach((item) => {
        const uid = item.uid;

        // Add node if not seen yet in this frontend pass
        if (!nodes.value.find((n) => n.uid === uid)) {
            nodes.value.push({
                ...item,
                level,
                x: item.x || 0,
                y: item.y || 0,
            });
        }

        // Add edge if we have a parent
        if (parentUid) {
            const edgeId = showAllLines.value
                ? `${parentUid}-${uid}-${item.source_port}-${item.destination_port}`
                : `${parentUid}-${uid}`;

            if (!edges.value.find((e) => e.id === edgeId)) {
                edges.value.push({
                    id: edgeId,
                    source: parentUid,
                    target: uid,
                    type: item.connection_type || 'Link',
                    source_port: item.source_port || '',
                    dest_port: item.destination_port || '',
                });
            }
        }

        // STOP RECURSION if this item is marked as a duplicate in the data structure
        // This avoids processing the same branch multiple times
        if (!item.is_duplicate && item.children && item.children.length > 0) {
            flattenTopology(item.children, level + 1, uid);
        }
    });
};

// Simple Layout Calculation
const calculateLayout = () => {
    const levelSpacingX = 250;
    const levelSpacingY = 150;

    // Group nodes by level
    const levels: Record<number, Node[]> = {};
    nodes.value.forEach((node) => {
        if (!levels[node.level]) levels[node.level] = [];
        levels[node.level].push(node);
    });

    Object.keys(levels).forEach((levelStr) => {
        const lv = parseInt(levelStr);
        const nodesInLevel = levels[lv];

        nodesInLevel.forEach((node, idx) => {
            // Only calculate if not already set by backend
            if (node.x === 0 && node.y === 0) {
                node.x = 100 + lv * levelSpacingX;
                node.y = 100 + idx * levelSpacingY;
            }
        });
    });
};

const draggingNode = ref<string | null>(null);
const hasChanges = ref(false);
const dragStart = ref({ x: 0, y: 0 });

const selectedNode = ref<Node | null>(null);
const nodeDetails = ref<any>(null);
const isLoadingDetails = ref(false);
const isSheetOpen = ref(false);
const isLocked = ref(false);

// Connection Manager State
const isConnecting = ref(false);
const connectionSource = ref<Node | null>(null);
const showConnectionDialog = ref(false);
const isSavingConnection = ref(false);
const connectionTarget = ref<Node | null>(null);

const handleNodeClick = async (uid: string) => {
    // Select if not dragging OR if locked (since dragging is disabled)
    if (!hasChanges.value || isLocked.value) {
        const node = nodes.value.find((n) => n.uid === uid);

        if (isConnecting.value && node) {
            if (!connectionSource.value) {
                // Step 1: Select Source
                connectionSource.value = node;
                toast.info(
                    `Source selected: ${node.name}. Now select a target.`,
                );
            } else if (connectionSource.value.uid !== uid) {
                // Step 2: Select Target
                connectionTarget.value = node;
                showConnectionDialog.value = true;
            }
            return;
        }

        // Normal details view
        if (node) {
            selectedNode.value = node;
            isSheetOpen.value = true;
            isLoadingDetails.value = true;
            nodeDetails.value = null;

            try {
                const response = await axios.get(deviceDetailsRoute().url, {
                    params: { uid: uid },
                });
                nodeDetails.value = response.data;
            } catch (error) {
                console.error('Failed to fetch details', error);
                toast.error('Failed to load device details');
            } finally {
                isLoadingDetails.value = false;
            }
        }
    }
};

const handleSaveConnection = async (data: any) => {
    isSavingConnection.value = true;
    try {
        await axios.post(storeConnectionRoute().url, data);
        toast.success('Connection created successfully');
        showConnectionDialog.value = false;
        isConnecting.value = false;
        connectionSource.value = null;
        connectionTarget.value = null;
        // Reload page to refresh topology
        window.location.reload();
    } catch (error) {
        console.error('Failed to create connection', error);
        toast.error('Failed to create connection');
    } finally {
        isSavingConnection.value = false;
    }
};

const handleNodeMouseDown = (uid: string, event: MouseEvent) => {
    if (isLocked.value) return; // Prevent drag if locked

    event.stopPropagation();
    draggingNode.value = uid;
    const node = nodes.value.find((n) => n.uid === uid);
    if (node) {
        dragStart.value = {
            x: event.clientX / zoom.value - node.x,
            y: event.clientY / zoom.value - node.y,
        };
    }
};

const handleMouseMove = (event: MouseEvent) => {
    if (draggingNode.value) {
        const node = nodes.value.find((n) => n.uid === draggingNode.value);
        if (node) {
            node.x = event.clientX / zoom.value - dragStart.value.x;
            node.y = event.clientY / zoom.value - dragStart.value.y;
            hasChanges.value = true;
        }
    }
};

const handleMouseUp = () => {
    draggingNode.value = null;
};

const saveForm = useForm({
    positions: {} as Record<string, { x: number; y: number }>,
});

const saveLayout = () => {
    const positions: Record<string, { x: number; y: number }> = {};
    nodes.value.forEach((node) => {
        positions[node.uid] = { x: node.x, y: node.y };
    });

    saveForm.positions = positions;
    saveForm.post(updatePositionsRoute().url, {
        preserveScroll: true,
        onSuccess: () => {
            hasChanges.value = false;
            toast.success('Topology layout saved successfully');
        },
    });
};

onMounted(() => {
    refreshTopology();
});

const refreshTopology = () => {
    nodes.value = [];
    edges.value = [];
    flattenTopology(props.topology);
    calculateLayout();
};

watch(showAllLines, () => {
    refreshTopology();
});

// --- UI Helpers ---
const getIcon = (type: string) => {
    if (!type) return Server;
    if (type.includes('Router')) return RouterIcon;
    if (type.includes('AdSwitch')) return Server;
    if (type.includes('Olt')) return Network;
    if (type.includes('AccessPoint')) return Wifi;
    if (type.includes('Ont') || type.includes('Cpe')) return Laptop;
    return Server;
};

const getStatusColor = (status: string) => {
    return status === 'Active' ? '#22c55e' : '#ef4444';
};

const pan = ref({ x: 0, y: 0 });
const zoom = ref(1);

const transformStyle = computed(() => ({
    transform: `translate(${pan.value.x}px, ${pan.value.y}px) scale(${zoom.value})`,
    transformOrigin: '0 0',
}));

const handleZoom = (delta: number) => {
    zoom.value = Math.min(Math.max(zoom.value + delta, 0.2), 2);
};
</script>

<template>
    <Head title="Network Topology - The Dude Style" />

    <AppLayout :breadcrumbs="[{ title: 'Topology Map', href: '#' }]">
        <div
            class="relative h-[calc(100vh-64px)] overflow-hidden bg-[#0a0b0f] select-none"
        >
            <!-- Engineering Grid Background -->
            <div class="pointer-events-none absolute inset-0 opacity-20">
                <svg
                    width="100%"
                    height="100%"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <defs>
                        <pattern
                            id="smallGrid"
                            width="10"
                            height="10"
                            patternUnits="userSpaceOnUse"
                        >
                            <path
                                d="M 10 0 L 0 0 0 10"
                                fill="none"
                                stroke="rgba(255,255,255,0.1)"
                                stroke-width="0.5"
                            />
                        </pattern>
                        <pattern
                            id="grid"
                            width="100"
                            height="100"
                            patternUnits="userSpaceOnUse"
                        >
                            <rect
                                width="100"
                                height="100"
                                fill="url(#smallGrid)"
                            />
                            <path
                                d="M 100 0 L 0 0 0 100"
                                fill="none"
                                stroke="rgba(255,255,255,0.2)"
                                stroke-width="1"
                            />
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#grid)" />
                </svg>
            </div>

            <!-- Controls -->
            <div class="absolute top-6 left-6 z-50 flex flex-col gap-4">
                <div class="flex gap-2">
                    <div
                        class="flex gap-1 rounded-lg border border-white/10 bg-black/60 p-1 shadow-2xl backdrop-blur-md"
                    >
                        <button
                            @click="handleZoom(0.1)"
                            class="rounded p-2 text-white/60 transition-colors hover:bg-white/10 hover:text-white"
                            title="Zoom In"
                        >
                            <ZoomIn class="h-5 w-5" />
                        </button>
                        <button
                            @click="handleZoom(-0.1)"
                            class="rounded p-2 text-white/60 transition-colors hover:bg-white/10 hover:text-white"
                            title="Zoom Out"
                        >
                            <ZoomOut class="h-5 w-5" />
                        </button>
                        <button
                            @click="
                                zoom = 1;
                                pan = { x: 0, y: 0 };
                            "
                            class="rounded border-l border-white/10 p-2 text-white/60 transition-colors hover:bg-white/10 hover:text-white"
                            title="Reset View"
                        >
                            <Maximize class="h-5 w-5" />
                        </button>
                    </div>

                    <div
                        class="flex gap-1 rounded-lg border border-white/10 bg-black/60 p-1 shadow-2xl backdrop-blur-md"
                    >
                        <button
                            @click="isLocked = !isLocked"
                            :class="[
                                'flex items-center gap-2 rounded p-2 text-xs font-medium transition-colors',
                                isLocked
                                    ? 'bg-red-500/80 text-white shadow-lg'
                                    : 'text-white/40 hover:bg-white/5 hover:text-white',
                            ]"
                            :title="isLocked ? 'Unlock Layout' : 'Lock Layout'"
                        >
                            <component
                                :is="isLocked ? Lock : Unlock"
                                class="h-4 w-4"
                            />
                            {{ isLocked ? 'Locked' : 'Unlocked' }}
                        </button>
                    </div>

                    <div
                        class="flex gap-1 rounded-lg border border-white/10 bg-black/60 p-1 shadow-2xl backdrop-blur-md"
                    >
                        <button
                            @click="
                                isConnecting = !isConnecting;
                                connectionSource = null;
                            "
                            :class="[
                                'flex items-center gap-2 rounded p-2 text-xs font-medium transition-colors',
                                isConnecting
                                    ? 'animate-pulse bg-orange-500 text-white shadow-lg'
                                    : 'text-white/40 hover:bg-white/5 hover:text-white',
                            ]"
                            :title="
                                isConnecting
                                    ? 'Cancel Connection'
                                    : 'Draw Connection'
                            "
                        >
                            <Cable class="h-4 w-4" />
                            {{ isConnecting ? 'Connecting...' : 'Connect' }}
                        </button>
                    </div>

                    <div
                        class="flex gap-1 rounded-lg border border-white/10 bg-black/60 p-1 shadow-2xl backdrop-blur-md"
                    >
                        <button
                            @click="showAllLines = false"
                            :class="[
                                'flex items-center gap-2 rounded p-2 text-xs font-medium transition-colors',
                                !showAllLines
                                    ? 'border border-primary/30 bg-primary/20 text-primary shadow-lg shadow-primary/10'
                                    : 'text-white/40 hover:bg-white/5 hover:text-white',
                            ]"
                            title="Consolidated View"
                        >
                            <List class="h-4 w-4" />
                            Single
                        </button>
                        <button
                            @click="showAllLines = true"
                            :class="[
                                'flex items-center gap-2 rounded p-2 text-xs font-medium transition-colors',
                                showAllLines
                                    ? 'border border-primary/30 bg-primary/20 text-primary shadow-lg shadow-primary/10'
                                    : 'text-white/40 hover:bg-white/5 hover:text-white',
                            ]"
                            title="All Connections View"
                        >
                            <Layers class="h-4 w-4" />
                            All Links
                        </button>
                    </div>

                    <div
                        class="flex gap-1 rounded-lg border border-white/10 bg-black/60 p-1 shadow-2xl backdrop-blur-md"
                    >
                        <button
                            @click="showDeviceLabels = !showDeviceLabels"
                            :class="[
                                'flex items-center gap-2 rounded p-2 text-xs font-medium transition-colors',
                                showDeviceLabels
                                    ? 'border border-blue-500/30 bg-blue-500/20 text-blue-400 shadow-lg shadow-blue-500/10'
                                    : 'text-white/40 hover:bg-white/5 hover:text-white',
                            ]"
                            title="Toggle Device Labels"
                        >
                            <Type class="h-4 w-4" />
                            Device
                        </button>
                        <button
                            @click="showLinkLabels = !showLinkLabels"
                            :class="[
                                'flex items-center gap-2 rounded p-2 text-xs font-medium transition-colors',
                                showLinkLabels
                                    ? 'border border-blue-500/30 bg-blue-500/20 text-blue-400 shadow-lg shadow-blue-500/10'
                                    : 'text-white/40 hover:bg-white/5 hover:text-white',
                            ]"
                            title="Toggle Link Labels"
                        >
                            <Activity class="h-4 w-4" />
                            Links
                        </button>
                    </div>

                    <button
                        v-if="hasChanges"
                        @click="saveLayout"
                        :disabled="saveForm.processing"
                        class="flex animate-pulse items-center gap-2 rounded-lg bg-primary/80 px-4 py-2 text-white shadow-2xl backdrop-blur-md transition-all hover:bg-primary"
                    >
                        <Save class="h-4 w-4" />
                        {{ saveForm.processing ? 'Saving...' : 'Save Layout' }}
                    </button>
                </div>

                <div
                    class="flex w-fit items-center gap-3 rounded-lg border border-white/10 bg-black/60 px-4 py-2 text-xs font-bold tracking-tighter uppercase shadow-2xl backdrop-blur-md"
                >
                    <div class="flex items-center gap-1.5 text-green-500">
                        <div
                            class="h-2 w-2 rounded-full bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.6)]"
                        ></div>
                        UP
                    </div>
                    <div class="flex items-center gap-1.5 text-red-500">
                        <div
                            class="h-2 w-2 rounded-full bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.6)]"
                        ></div>
                        DOWN
                    </div>
                </div>
            </div>

            <!-- Canvas Container -->
            <div
                class="h-full w-full overflow-auto p-[500px]"
                :class="[
                    isLocked
                        ? 'cursor-default'
                        : 'cursor-grab active:cursor-grabbing',
                ]"
                @mousedown.self=""
                @mousemove="handleMouseMove"
                @mouseup="handleMouseUp"
                @mouseleave="handleMouseUp"
            >
                <svg
                    width="2000"
                    height="2000"
                    class="overflow-visible"
                    :style="transformStyle"
                >
                    <!-- Connections (Edges) -->
                    <g v-for="edge in edges" :key="edge.id">
                        <template v-if="getEdgePoints(edge)">
                            <line
                                :x1="getEdgePoints(edge)!.x1"
                                :y1="getEdgePoints(edge)!.y1"
                                :x2="getEdgePoints(edge)!.x2"
                                :y2="getEdgePoints(edge)!.y2"
                                stroke="rgba(255,255,255,0.2)"
                                stroke-width="2"
                                stroke-dasharray="4"
                            />
                            <!-- Port Labels -->
                            <template v-if="showLinkLabels">
                                <!-- Source Port (Next to source node) -->
                                <text
                                    :x="
                                        getEdgePoints(edge)!.x1 +
                                        (getEdgePoints(edge)!.x2 -
                                            getEdgePoints(edge)!.x1) *
                                            0.15
                                    "
                                    :y="
                                        getEdgePoints(edge)!.y1 +
                                        (getEdgePoints(edge)!.y2 -
                                            getEdgePoints(edge)!.y1) *
                                            0.15 -
                                        5
                                    "
                                    fill="#22c55e"
                                    font-size="9"
                                    font-weight="bold"
                                    font-family="monospace"
                                    class="drop-shadow-sm"
                                    text-anchor="middle"
                                >
                                    {{ edge.source_port }}
                                </text>

                                <!-- Destination Port (Next to target node) -->
                                <text
                                    :x="
                                        getEdgePoints(edge)!.x1 +
                                        (getEdgePoints(edge)!.x2 -
                                            getEdgePoints(edge)!.x1) *
                                            0.85
                                    "
                                    :y="
                                        getEdgePoints(edge)!.y1 +
                                        (getEdgePoints(edge)!.y2 -
                                            getEdgePoints(edge)!.y1) *
                                            0.85 -
                                        5
                                    "
                                    fill="#3b82f6"
                                    font-size="9"
                                    font-weight="bold"
                                    font-family="monospace"
                                    class="drop-shadow-sm"
                                    text-anchor="middle"
                                >
                                    {{ edge.dest_port }}
                                </text>

                                <!-- Link Type Label (Middle) -->
                                <text
                                    :x="
                                        (getEdgePoints(edge)!.x1 +
                                            getEdgePoints(edge)!.x2) /
                                        2
                                    "
                                    :y="
                                        (getEdgePoints(edge)!.y1 +
                                            getEdgePoints(edge)!.y2) /
                                            2 -
                                        5
                                    "
                                    fill="rgba(255,255,255,0.3)"
                                    text-anchor="middle"
                                    font-size="8"
                                    font-family="monospace uppercase"
                                >
                                    {{ edge.type }}
                                </text>
                            </template>
                        </template>
                    </g>

                    <!-- Devices (Nodes) -->
                    <g
                        v-for="node in nodes"
                        :key="node.uid"
                        :class="[
                            'group',
                            isLocked || isConnecting
                                ? 'cursor-pointer'
                                : 'cursor-grab active:cursor-grabbing',
                        ]"
                        @mousedown="handleNodeMouseDown(node.uid, $event)"
                        @click="handleNodeClick(node.uid)"
                    >
                        <!-- Node Box -->
                        <rect
                            :x="node.x"
                            :y="node.y"
                            width="80"
                            height="60"
                            rx="10"
                            :fill="
                                connectionSource?.uid === node.uid
                                    ? 'rgba(249, 115, 22, 1)'
                                    : 'rgba(30, 41, 59, 1)'
                            "
                            :stroke="
                                connectionSource?.uid === node.uid
                                    ? '#fff'
                                    : 'rgba(255, 255, 255, 0.2)'
                            "
                            :stroke-width="
                                connectionSource?.uid === node.uid ? 2 : 1.5
                            "
                            class="transition-all group-hover:stroke-primary group-hover:shadow-[0_0_20px_rgba(79,70,229,0.5)]"
                            style="
                                filter: drop-shadow(
                                    0 4px 6px rgba(0, 0, 0, 0.3)
                                );
                            "
                        />

                        <!-- Status Glow -->
                        <circle
                            :cx="node.x + 80"
                            :cy="node.y"
                            r="5"
                            :fill="getStatusColor(node.status)"
                            class="animate-pulse shadow-[0_0_10px_currentColor]"
                        />

                        <!-- Icon Area -->
                        <foreignObject
                            :x="node.x + 20"
                            :y="node.y + 10"
                            width="40"
                            height="40"
                        >
                            <div
                                class="flex h-full w-full items-center justify-center text-white transition-colors group-hover:text-primary"
                            >
                                <component
                                    :is="getIcon(node.type)"
                                    class="h-6 w-6 stroke-2"
                                />
                            </div>
                        </foreignObject>

                        <!-- Labels Bundle -->
                        <g v-if="showDeviceLabels" transform="translate(0, 75)">
                            <text
                                :x="node.x + 40"
                                :y="node.y"
                                text-anchor="middle"
                                fill="#fff"
                                font-size="12"
                                font-weight="700"
                            >
                                {{ node.name }}
                            </text>
                            <text
                                :x="node.x + 40"
                                :y="node.y + 14"
                                text-anchor="middle"
                                fill="#22c55e"
                                font-size="10"
                                font-family="monospace"
                                font-weight="600"
                            >
                                {{ node.ip_address || '---.---.---.---' }}
                            </text>
                            <text
                                :x="node.x + 40"
                                :y="node.y + 26"
                                text-anchor="middle"
                                fill="rgba(255,255,255,0.4)"
                                font-size="9"
                                font-weight="600"
                                font-family="monospace uppercase tracking-tighter"
                            >
                                {{ node.code }}
                            </text>
                        </g>

                        <!-- Activity Indicator (The Dude Style) -->
                        <rect
                            :x="node.x + 10"
                            :y="node.y + 52"
                            width="60"
                            height="3"
                            rx="1.5"
                            :fill="
                                node.status === 'Active'
                                    ? 'rgba(34,197,94,0.3)'
                                    : 'rgba(239,68,68,0.3)'
                            "
                        />
                    </g>
                </svg>
            </div>

            <!-- Empty State -->
            <div
                v-if="nodes.length === 0"
                class="absolute inset-0 flex flex-col items-center justify-center text-white/40"
            >
                <div
                    class="mb-6 rounded-full border border-white/5 bg-white/5 p-8"
                >
                    <Network class="h-16 w-16 opacity-20" />
                </div>
                <h3 class="text-xl font-bold">Network Map Empty</h3>
                <p class="max-w-xs text-center text-sm opacity-60">
                    Establish connections between your active devices to
                    populate this canvas.
                </p>
            </div>

            <!-- Legend Overlay -->
            <div
                class="absolute right-6 bottom-6 z-50 space-y-2 rounded-xl border border-white/10 bg-black/60 p-4 text-[10px] text-white/50 shadow-2xl backdrop-blur-md"
            >
                <div
                    class="mb-1 font-bold tracking-widest text-white/30 uppercase"
                >
                    Visualization Info
                </div>
                <div class="flex items-center gap-2">
                    <span
                        class="h-0.5 w-3 border border-dashed border-white/20 bg-white/10"
                    ></span>
                    Link established
                </div>
                <div class="flex items-center gap-2">
                    <div
                        class="h-1.5 w-1.5 animate-pulse rounded-full bg-primary shadow-[0_0_10px_rgba(79,70,229,0.5)]"
                    ></div>
                    Real-time monitoring
                </div>
            </div>
        </div>

        <ConnectionDialog
            v-model:open="showConnectionDialog"
            :source-node="connectionSource"
            :target-node="connectionTarget"
            :processing="isSavingConnection"
            @save="handleSaveConnection"
        />

        <Sheet v-model:open="isSheetOpen">
            <SheetContent
                class="w-[400px] border-l border-white/10 bg-[#0a0b0f] p-6 text-white sm:w-[540px]"
            >
                <SheetHeader class="mb-6">
                    <SheetTitle
                        class="flex items-center gap-3 text-xl text-white"
                    >
                        <component
                            :is="getIcon(selectedNode?.type || '')"
                            class="h-6 w-6 text-blue-400"
                        />
                        {{ selectedNode?.name }}
                    </SheetTitle>
                    <SheetDescription class="text-white/60">
                        Detailed information for this active device.
                    </SheetDescription>
                </SheetHeader>

                <div v-if="selectedNode" class="space-y-6">
                    <div
                        v-if="isLoadingDetails"
                        class="flex flex-col items-center justify-center gap-3 py-12 text-white/50"
                    >
                        <Activity class="h-8 w-8 animate-spin text-primary" />
                        <span
                            class="text-xs font-medium tracking-widest uppercase"
                            >Loading Details...</span
                        >
                    </div>

                    <template v-else-if="nodeDetails">
                        <div class="space-y-1">
                            <div
                                class="text-xs font-bold tracking-widest text-white/40 uppercase"
                            >
                                Status
                            </div>
                            <div class="flex items-center gap-2">
                                <div
                                    class="h-2.5 w-2.5 rounded-full"
                                    :class="
                                        nodeDetails.status === 'Active'
                                            ? 'bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.6)]'
                                            : 'bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.6)]'
                                    "
                                ></div>
                                <span class="font-medium">{{
                                    nodeDetails.status
                                }}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-1">
                                <div
                                    class="text-xs font-bold tracking-widest text-white/40 uppercase"
                                >
                                    IP Address
                                </div>
                                <div
                                    class="font-mono font-bold tracking-wide text-blue-400"
                                >
                                    {{ nodeDetails.ip_address || 'N/A' }}
                                </div>
                            </div>
                            <div class="space-y-1">
                                <div
                                    class="text-xs font-bold tracking-widest text-white/40 uppercase"
                                >
                                    Device Type
                                </div>
                                <div class="font-medium">
                                    {{ nodeDetails.type }}
                                </div>
                            </div>
                            <div class="space-y-1">
                                <div
                                    class="text-xs font-bold tracking-widest text-white/40 uppercase"
                                >
                                    Brand
                                </div>
                                <div class="font-medium">
                                    {{ nodeDetails.brand || 'Unknown' }}
                                </div>
                            </div>
                            <div class="space-y-1">
                                <div
                                    class="text-xs font-bold tracking-widest text-white/40 uppercase"
                                >
                                    Model
                                </div>
                                <div class="font-medium">
                                    {{ nodeDetails.model || 'Unknown' }}
                                </div>
                            </div>
                            <div class="space-y-1">
                                <div
                                    class="text-xs font-bold tracking-widest text-white/40 uppercase"
                                >
                                    Area
                                </div>
                                <div class="font-medium">
                                    {{ nodeDetails.area_name || '-' }}
                                </div>
                            </div>
                            <div class="space-y-1">
                                <div
                                    class="text-xs font-bold tracking-widest text-white/40 uppercase"
                                >
                                    POP
                                </div>
                                <div class="font-medium">
                                    {{ nodeDetails.pop_name || '-' }}
                                </div>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <div
                                class="text-xs font-bold tracking-widest text-white/40 uppercase"
                            >
                                Hardware Code
                            </div>
                            <div
                                class="rounded border border-white/10 bg-white/5 p-3 font-mono text-sm select-all"
                            >
                                {{ nodeDetails.code }}
                            </div>
                        </div>

                        <div v-if="nodeDetails.description" class="space-y-1">
                            <div
                                class="text-xs font-bold tracking-widest text-white/40 uppercase"
                            >
                                Description
                            </div>
                            <div
                                class="rounded border border-white/10 bg-white/5 p-3 text-sm text-white/60"
                            >
                                {{ nodeDetails.description }}
                            </div>
                        </div>

                        <div
                            class="grid grid-cols-2 gap-4 border-t border-white/10 pt-4 text-xs text-white/40"
                        >
                            <div>
                                Serial:
                                <span class="font-mono text-white/60">{{
                                    nodeDetails.serial_number || 'N/A'
                                }}</span>
                            </div>
                            <div>
                                Ports:
                                <span class="font-mono text-white/60">{{
                                    nodeDetails.port_count || 'N/A'
                                }}</span>
                            </div>
                        </div>

                        <div class="space-y-2 border-t border-white/10 pt-4">
                            <div
                                class="text-xs font-bold tracking-widest text-white/40 uppercase"
                            >
                                Connected Ports
                            </div>
                            <div
                                v-if="
                                    nodeDetails.connections &&
                                    nodeDetails.connections.length > 0
                                "
                                class="overflow-x-auto"
                            >
                                <table class="w-full text-left text-sm">
                                    <thead>
                                        <tr
                                            class="border-b border-white/10 text-xs text-white/40"
                                        >
                                            <th class="py-2 font-medium">
                                                Port
                                            </th>
                                            <th class="py-2 font-medium">
                                                Connected To
                                            </th>
                                            <th class="py-2 font-medium">
                                                Remote Port
                                            </th>
                                            <th
                                                class="py-2 text-right font-medium"
                                            >
                                                Type
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-white/5">
                                        <tr
                                            v-for="conn in nodeDetails.connections"
                                            :key="conn.id"
                                            class="group/row"
                                        >
                                            <td
                                                class="py-2 font-mono text-blue-400"
                                            >
                                                {{ conn.local_port }}
                                            </td>
                                            <td
                                                class="py-2 font-medium text-white/80"
                                            >
                                                {{ conn.remote_device }}
                                            </td>
                                            <td
                                                class="py-2 font-mono text-white/60"
                                            >
                                                {{ conn.remote_port }}
                                            </td>
                                            <td class="py-2 text-right">
                                                <span
                                                    class="rounded border border-white/10 bg-white/5 px-1.5 py-0.5 text-[10px] tracking-tighter text-white/40 uppercase"
                                                >
                                                    {{ conn.type }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div
                                v-else
                                class="py-2 text-sm text-white/30 italic"
                            >
                                No active connections found.
                            </div>
                        </div>
                    </template>
                </div>
            </SheetContent>
        </Sheet>
    </AppLayout>
</template>

<style scoped>
svg text {
    user-select: none;
}
.group:hover rect {
    filter: drop-shadow(0 0 10px rgba(79, 70, 229, 0.2));
}
</style>
