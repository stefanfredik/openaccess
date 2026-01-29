<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { ArrowRight, Link as LinkIcon, Server, Trash2 } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    device: any;
    deviceType: string;
    connections: Array<any>;
    incomingConnections?: Array<any>;
    availableDevices: Array<any>;
}>();

const showAddForm = ref(false);
const destinationInterfaces = ref<Array<any>>([]);

const form = useForm({
    source_id: props.device.id,
    source_type: props.deviceType,
    destination_id: '',
    destination_type: '',
    connection_type: 'Downlink',
    source_port: '',
    destination_port: '',
    description: '',
});

// Merge interfaces with connections
const portTableData = computed(() => {
    const interfaces = props.device.interfaces || [];
    return interfaces.map((inf: any) => {
        // Check Outbound (Source = This Port)
        const outbound = props.connections.find(
            (c: any) => c.source_port === inf.id,
        );
        // Check Inbound (Dest = This Port)
        const inbound = props.incomingConnections?.find(
            (c: any) => c.destination_port === inf.id,
        );

        return {
            interface: inf,
            status: outbound
                ? 'connected_out'
                : inbound
                  ? 'connected_in'
                  : 'available',
            connection: outbound || inbound,
            is_parent: !!inbound,
        };
    });
});

watch(
    () => form.destination_id,
    async (newId: string | number | null) => {
        if (!newId || !form.destination_type) {
            destinationInterfaces.value = [];
            form.destination_port = '';
            return;
        }

        try {
            const response = await axios.get(
                route('active-device.interfaces.list'),
                {
                    params: {
                        type: form.destination_type,
                        id: newId,
                    },
                },
            );
            destinationInterfaces.value = response.data;
            form.destination_port = ''; // Reset port
        } catch (error) {
            console.error('Failed to fetch destination interfaces', error);
            destinationInterfaces.value = [];
        }
    },
);

watch(
    () => form.destination_type,
    () => {
        form.destination_id = '';
        destinationInterfaces.value = [];
        form.destination_port = '';
    },
);

const submit = () => {
    form.post('/topology/connections', {
        onSuccess: () => {
            form.reset(
                'destination_id',
                'destination_type',
                'source_port',
                'destination_port',
                'description',
            );
            showAddForm.value = false;
        },
    });
};

const deleteConnection = (id: number) => {
    if (confirm('Are you sure you want to remove this connection?')) {
        router.delete(`/topology/connections/${id}`);
    }
};

const openAdd = (preselectedPort: any = '') => {
    if (preselectedPort) {
        form.source_port = preselectedPort;
    }
    showAddForm.value = true;
};

defineExpose({ openAdd });
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2
                class="flex items-center gap-2 text-sm font-bold tracking-widest text-muted-foreground uppercase"
            >
                <div class="h-1 w-4 rounded-full bg-primary/50"></div>
                Connection Status
            </h2>
            <Dialog v-model:open="showAddForm">
                <DialogContent class="sm:max-w-[500px]">
                    <DialogHeader>
                        <DialogTitle>Add New Link</DialogTitle>
                        <DialogDescription>
                            Connect this device to a downstream target.
                        </DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="submit" class="space-y-6 pt-4">
                        <div
                            class="grid grid-cols-1 gap-x-6 gap-y-4 md:grid-cols-2"
                        >
                            <div class="space-y-2">
                                <Label
                                    class="text-[11px] font-bold tracking-wider text-muted-foreground uppercase"
                                    >Target Device Type</Label
                                >
                                <Select v-model="form.destination_type">
                                    <SelectTrigger class="h-10 w-full">
                                        <SelectValue
                                            placeholder="Select Type"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            value="Modules\ActiveDevice\Models\Router"
                                            >Router</SelectItem
                                        >
                                        <SelectItem
                                            value="Modules\ActiveDevice\Models\AdSwitch"
                                            >Switch</SelectItem
                                        >
                                        <SelectItem
                                            value="Modules\ActiveDevice\Models\Olt"
                                            >OLT</SelectItem
                                        >
                                        <SelectItem
                                            value="Modules\ActiveDevice\Models\Ont"
                                            >ONT</SelectItem
                                        >
                                        <SelectItem
                                            value="Modules\ActiveDevice\Models\AccessPoint"
                                            >AP</SelectItem
                                        >
                                        <SelectItem
                                            value="Modules\Cpe\Models\Cpe"
                                            >CPE</SelectItem
                                        >
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="space-y-2">
                                <Label
                                    class="text-[11px] font-bold tracking-wider text-muted-foreground uppercase"
                                    >Target Device</Label
                                >
                                <Select
                                    v-model="form.destination_id"
                                    :disabled="!form.destination_type"
                                >
                                    <SelectTrigger class="h-10 w-full">
                                        <SelectValue
                                            placeholder="Select Device"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="d in availableDevices.filter(
                                                (d: any) =>
                                                    d.type ===
                                                    form.destination_type,
                                            )"
                                            :key="d.id"
                                            :value="d.id.toString()"
                                        >
                                            {{ d.name }} ({{ d.code }})
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="space-y-2">
                                <Label
                                    class="text-[11px] font-bold tracking-wider text-muted-foreground uppercase"
                                    >Local Port (Source)</Label
                                >
                                <div
                                    v-if="
                                        device.interfaces &&
                                        device.interfaces.length > 0
                                    "
                                >
                                    <Select v-model="form.source_port">
                                        <SelectTrigger class="h-10 w-full">
                                            <SelectValue
                                                placeholder="Select Source Port"
                                            />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="inf in device.interfaces"
                                                :key="inf.id"
                                                :value="inf.id"
                                            >
                                                {{ inf.name }}
                                                <span
                                                    v-if="inf.status === 'up'"
                                                    class="ml-2 text-xs text-green-500"
                                                    >(UP)</span
                                                >
                                                <span
                                                    v-else-if="
                                                        inf.status === 'idle'
                                                    "
                                                    class="ml-2 text-xs text-muted-foreground"
                                                    >(IDLE)</span
                                                >
                                                <span
                                                    v-else
                                                    class="ml-2 text-xs text-red-400"
                                                    >({{ inf.status }})</span
                                                >
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div
                                    v-else
                                    class="rounded-md border border-yellow-200 bg-yellow-50 p-3 text-sm text-yellow-800 dark:border-yellow-900/50 dark:bg-yellow-900/20 dark:text-yellow-200"
                                >
                                    No physical interfaces found. Please add
                                    interfaces in the "Physical Interfaces" tab
                                    first.
                                </div>
                            </div>
                            <div class="space-y-2">
                                <Label
                                    class="text-[11px] font-bold tracking-wider text-muted-foreground uppercase"
                                    >Remote Port (Dest)</Label
                                >
                                <div v-if="destinationInterfaces.length > 0">
                                    <Select v-model="form.destination_port">
                                        <SelectTrigger class="h-10 w-full">
                                            <SelectValue
                                                placeholder="Select Destination Port"
                                            />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="inf in destinationInterfaces"
                                                :key="inf.id"
                                                :value="inf.id"
                                            >
                                                {{ inf.name }}
                                                <span
                                                    v-if="inf.status === 'up'"
                                                    class="ml-2 text-xs text-green-500"
                                                    >(UP)</span
                                                >
                                                <span
                                                    v-else-if="
                                                        inf.status === 'idle'
                                                    "
                                                    class="ml-2 text-xs text-muted-foreground"
                                                    >(IDLE)</span
                                                >
                                                <span
                                                    v-else
                                                    class="ml-2 text-xs text-red-400"
                                                    >({{ inf.status }})</span
                                                >
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div
                                    v-else-if="form.destination_id"
                                    class="rounded-md border border-yellow-200 bg-yellow-50 p-3 text-sm text-yellow-800 dark:border-yellow-900/50 dark:bg-yellow-900/20 dark:text-yellow-200"
                                >
                                    No physical interfaces found on target
                                    device.
                                </div>
                                <Input
                                    v-else
                                    v-model="form.destination_port"
                                    class="h-10 w-full"
                                    disabled
                                    placeholder="Select a target device first"
                                />
                            </div>

                            <div class="space-y-2">
                                <Label
                                    class="text-[11px] font-bold tracking-wider text-muted-foreground uppercase"
                                    >Link Type</Label
                                >
                                <Select v-model="form.connection_type">
                                    <SelectTrigger class="h-10 w-full">
                                        <SelectValue
                                            placeholder="Select Type"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Uplink"
                                            >Uplink</SelectItem
                                        >
                                        <SelectItem value="Downlink"
                                            >Downlink</SelectItem
                                        >
                                        <SelectItem value="Fiber"
                                            >Fiber</SelectItem
                                        >
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="space-y-2">
                                <Label
                                    class="text-[11px] font-bold tracking-wider text-muted-foreground uppercase"
                                    >Description</Label
                                >
                                <Input
                                    v-model="form.description"
                                    class="h-10 w-full"
                                    placeholder="Optional description"
                                />
                            </div>
                        </div>

                        <DialogFooter class="border-t pt-4">
                            <Button
                                type="button"
                                variant="outline"
                                @click="showAddForm = false"
                                >Cancel</Button
                            >
                            <Button
                                type="submit"
                                :disabled="
                                    form.processing || !form.destination_port
                                "
                                >Save Link</Button
                            >
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>

        <div class="rounded-md border bg-card">
            <Table>
                <TableHeader class="bg-muted/50">
                    <TableRow>
                        <TableHead class="w-[100px]">Interface</TableHead>
                        <TableHead class="w-[120px]">Status</TableHead>
                        <TableHead>Connected To</TableHead>
                        <TableHead class="w-[120px]">Type</TableHead>
                        <TableHead class="w-[50px]"></TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="(row, index) in portTableData"
                        :key="index"
                        class="hover:bg-muted/5"
                    >
                        <TableCell>
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold">{{
                                    row.interface.name
                                }}</span>
                                <Badge
                                    v-if="row.interface.status === 'up'"
                                    variant="outline"
                                    class="border-green-500/50 text-[10px] text-green-600 uppercase"
                                >
                                    UP
                                </Badge>
                                <Badge
                                    v-else
                                    variant="outline"
                                    class="text-[10px] text-muted-foreground uppercase"
                                >
                                    {{ row.interface.status }}
                                </Badge>
                            </div>
                        </TableCell>
                        <TableCell>
                            <div v-if="row.status === 'connected_out'">
                                <Badge class="bg-green-500 hover:bg-green-600">
                                    <ArrowRight class="mr-1 h-3 w-3" />
                                    Uplink
                                </Badge>
                            </div>
                            <div v-else-if="row.status === 'connected_in'">
                                <Badge class="bg-blue-500 hover:bg-blue-600">
                                    <LinkIcon class="mr-1 h-3 w-3" />
                                    Downlink
                                </Badge>
                            </div>
                            <div v-else>
                                <Badge
                                    variant="outline"
                                    class="border-dashed text-muted-foreground"
                                >
                                    Available
                                </Badge>
                            </div>
                        </TableCell>
                        <TableCell>
                            <div
                                v-if="row.connection"
                                class="flex items-center gap-3"
                            >
                                <div
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded bg-primary/10 text-primary"
                                >
                                    <Server class="h-4 w-4" />
                                </div>
                                <div class="flex flex-col">
                                    <span
                                        class="text-sm font-bold text-foreground"
                                    >
                                        {{
                                            row.is_parent
                                                ? row.connection.source?.name
                                                : row.connection.destination
                                                      ?.name
                                        }}
                                    </span>
                                    <div
                                        class="flex items-center gap-1.5 text-[10px] text-muted-foreground"
                                    >
                                        <span class="mr-1">
                                            {{
                                                row.is_parent
                                                    ? 'Source Port:'
                                                    : 'Dest Port:'
                                            }}
                                        </span>
                                        <span
                                            class="rounded border border-border/50 bg-muted/60 px-1 font-mono"
                                        >
                                            {{
                                                row.is_parent
                                                    ? (row.connection
                                                          .source_interface
                                                          ?.name ??
                                                      row.connection
                                                          .source_port)
                                                    : (row.connection
                                                          .destination_interface
                                                          ?.name ??
                                                      row.connection
                                                          .destination_port)
                                            }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <span v-else class="text-xs text-muted-foreground"
                                >-</span
                            >
                        </TableCell>
                        <TableCell>
                            <Badge
                                v-if="row.connection"
                                variant="outline"
                                class="font-mono text-xs"
                            >
                                {{ row.connection.connection_type }}
                            </Badge>
                            <span v-else class="text-xs text-muted-foreground"
                                >-</span
                            >
                        </TableCell>
                        <TableCell>
                            <Button
                                v-if="row.connection && !row.is_parent"
                                variant="ghost"
                                size="icon"
                                class="h-8 w-8 text-destructive opacity-70 hover:bg-destructive/10 hover:opacity-100"
                                @click="deleteConnection(row.connection.id)"
                            >
                                <Trash2 class="h-4 w-4" />
                            </Button>
                            <Button
                                v-else-if="!row.connection"
                                variant="ghost"
                                size="icon"
                                class="h-8 w-8 text-primary opacity-70 hover:bg-primary/10 hover:opacity-100"
                                @click="openAdd(row.interface.id)"
                            >
                                <Plus class="h-4 w-4" />
                            </Button>
                        </TableCell>
                    </TableRow>
                    <TableRow v-if="portTableData.length === 0">
                        <TableCell
                            colspan="5"
                            class="py-8 text-center text-muted-foreground"
                        >
                            No interfaces found. Please add Physical Interfaces
                            first.
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </div>
</template>
