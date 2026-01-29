<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
    ArrowRightLeft,
    CheckCircle2,
    LockKeyhole,
    Monitor,
    PencilLine,
    PlusCircle,
    Zap,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import InterfaceManager from './InterfaceManager.vue';

const props = defineProps<{
    olt: any;
}>();

console.log('Rendering OltDetailPreview for:', props.olt?.name);

const activeServicePorts = computed(() => props.olt.service_ports || []);
const sourceConnections = computed(() => props.olt.source_connections || []);
const activeInterfaces = computed(() => props.olt.interfaces || []);
const isInterfaceManaging = ref(false);

// Mock interfaces for visualization ONLY IF real data is empty
const displayInterfaces = computed(() => {
    if (activeInterfaces.value.length > 0) return activeInterfaces.value;
    return [
        { name: 'GE1', status: 'up', type: 'Gigabit' },
        { name: 'GE2', status: 'up', type: 'Gigabit' },
        { name: 'GE3', status: 'idle', type: 'Gigabit' },
        { name: 'GE4', status: 'idle', type: 'Gigabit' },
        { name: 'SFP1', status: 'up', type: 'Fiber' },
        { name: 'SFP2', status: 'error', type: 'Fiber' },
    ];
});

const deviceDetails = computed(() => ({
    'Serial Number': props.olt.serial_number,
    'MAC Address': props.olt.mac_address,
    'IP Address': props.olt.ip_address,
    'PON Type': props.olt.pon_type,
    Wilayah: props.olt.area?.name,
    'Lokasi POP': props.olt.pop?.name,
    'Tahun Perolehan': props.olt.purchase_year,
    'Tanggal Instalasi': props.olt.installed_at,
    'Status Operasional': props.olt.status,
    Deskripsi: props.olt.description,
}));
</script>

<template>
    <div class="flex h-full flex-col">
        <div class="border-b bg-slate-50 p-6">
            <h2 class="text-xl font-bold text-slate-900">{{ olt.name }}</h2>
            <p class="text-sm text-slate-500 italic">
                OLT - {{ olt.brand }} {{ olt.model }}
            </p>
        </div>

        <Tabs default-value="services" class="flex flex-1 flex-col">
            <TabsList
                class="h-12 w-full justify-start rounded-none border-b bg-white px-6"
            >
                <TabsTrigger
                    value="services"
                    class="relative h-12 rounded-none hover:text-blue-600 data-[state=active]:after:absolute data-[state=active]:after:bottom-0 data-[state=active]:after:h-0.5 data-[state=active]:after:w-full data-[state=active]:after:bg-blue-600"
                    >Services</TabsTrigger
                >
                <TabsTrigger
                    value="interfaces"
                    class="relative h-12 rounded-none hover:text-blue-600 data-[state=active]:after:absolute data-[state=active]:after:bottom-0 data-[state=active]:after:h-0.5 data-[state=active]:after:w-full data-[state=active]:after:bg-blue-600"
                    >Interfaces</TabsTrigger
                >
                <TabsTrigger
                    value="topology"
                    class="relative h-12 rounded-none hover:text-blue-600 data-[state=active]:after:absolute data-[state=active]:after:bottom-0 data-[state=active]:after:h-0.5 data-[state=active]:after:w-full data-[state=active]:after:bg-blue-600"
                    >Topology</TabsTrigger
                >
                <TabsTrigger
                    value="detail"
                    class="relative h-12 rounded-none hover:text-blue-600 data-[state=active]:after:absolute data-[state=active]:after:bottom-0 data-[state=active]:after:h-0.5 data-[state=active]:after:w-full data-[state=active]:after:bg-blue-600"
                    >Detail Info</TabsTrigger
                >
            </TabsList>

            <div class="flex-1 overflow-y-auto">
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
                            No active services found.
                        </div>
                    </div>
                </TabsContent>

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
                                Kelola Port
                            </Button>
                        </div>
                        <div class="grid grid-cols-4 gap-2 sm:grid-cols-6">
                            <div
                                v-for="inf in displayInterfaces"
                                :key="inf.name"
                                :class="[
                                    'flex cursor-help flex-col items-center justify-center rounded border p-2 transition',
                                    inf.status === 'up'
                                        ? inf.type === 'Fiber'
                                            ? 'bg-blue-500 text-white'
                                            : 'bg-green-500 text-white'
                                        : inf.status === 'error'
                                          ? 'border-red-200 bg-red-100 text-red-400'
                                          : 'bg-gray-100 text-gray-400',
                                ]"
                                :title="
                                    inf.status === 'up'
                                        ? 'Connected'
                                        : inf.status === 'error'
                                          ? 'Error / Down'
                                          : 'Idle'
                                "
                            >
                                <Zap
                                    v-if="inf.type === 'Fiber'"
                                    class="mb-1 h-4 w-4"
                                />
                                <Monitor v-else class="mb-1 h-4 w-4" />
                                <span
                                    class="text-[10px] font-bold tracking-tighter uppercase"
                                    >{{ inf.name }}</span
                                >
                            </div>
                        </div>

                        <div class="mt-8 space-y-2">
                            <div
                                class="flex items-center space-x-2 text-xs text-gray-500"
                            >
                                <span
                                    class="h-3 w-3 rounded bg-green-500"
                                ></span>
                                <span>Up / Connected (Copper)</span>
                            </div>
                            <div
                                class="flex items-center space-x-2 text-xs text-gray-500"
                            >
                                <span
                                    class="h-3 w-3 rounded bg-blue-500"
                                ></span>
                                <span>Up / Connected (Fiber)</span>
                            </div>
                            <div
                                class="flex items-center space-x-2 text-xs text-gray-500"
                            >
                                <span
                                    class="h-3 w-3 rounded bg-gray-100"
                                ></span>
                                <span>Idle / Available</span>
                            </div>
                            <div
                                class="flex items-center space-x-2 text-xs text-gray-500"
                            >
                                <span class="h-3 w-3 rounded bg-red-400"></span>
                                <span>Error / Down</span>
                            </div>
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
                                ← Kembali ke Visualisasi
                            </Button>
                        </div>
                        <InterfaceManager
                            :olt-id="olt.id"
                            :olt-type="'Modules\\ActiveDevice\\Models\\Olt'"
                            :interfaces="activeInterfaces"
                        />
                    </div>
                </TabsContent>

                <TabsContent value="topology" class="m-0 p-6">
                    <h3
                        class="mb-6 text-xs font-bold tracking-widest text-gray-400 uppercase"
                    >
                        Active Links
                    </h3>

                    <div class="relative space-y-6">
                        <div
                            class="absolute top-2 bottom-2 left-[7px] w-0.5 border-l-2 border-dashed border-blue-100"
                        ></div>

                        <div
                            v-for="conn in sourceConnections"
                            :key="conn.id"
                            class="relative pl-8"
                        >
                            <div
                                class="absolute top-1 left-0 h-4 w-4 rounded-full border-2 border-white bg-blue-600 shadow-sm"
                            ></div>
                            <div
                                class="rounded-lg border border-blue-100 bg-blue-50 p-4"
                            >
                                <div
                                    class="mb-2 flex items-center justify-between"
                                >
                                    <span
                                        class="rounded bg-blue-600 px-2 py-0.5 text-[10px] font-bold tracking-tight text-white uppercase"
                                    >
                                        Local: {{ conn.source_port }}
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
                                                conn.destination?.name ||
                                                'Unknown'
                                            }}
                                        </p>
                                        <p class="text-xs text-slate-500">
                                            Target Port:
                                            <span
                                                class="font-mono text-blue-600"
                                                >{{
                                                    conn.destination_port
                                                }}</span
                                            >
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="sourceConnections.length === 0"
                            class="py-8 text-center text-sm text-muted-foreground italic"
                        >
                            No active links detected.
                        </div>

                        <Button
                            variant="outline"
                            class="mt-4 w-full border-dashed py-6 text-gray-400 hover:border-blue-300 hover:text-blue-600"
                        >
                            <PlusCircle class="mr-2 h-4 w-4" />
                            Tambah Link Koneksi
                        </Button>
                    </div>
                </TabsContent>

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
            </div>
        </Tabs>
    </div>
</template>
