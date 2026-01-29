<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
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
import {
    ArrowRight,
    Link as LinkIcon,
    Plus,
    Server,
    Trash2,
} from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    device: any;
    deviceType: string;
    connections: Array<any>;
    incomingConnections?: Array<any>;
    availableDevices: Array<any>;
}>();

const showAddForm = ref(false);

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
</script>

<template>
    <div class="space-y-6">
        <!-- Incoming Connections section -->
        <div v-if="incomingConnections && incomingConnections.length > 0">
            <h2
                class="mb-3 flex items-center gap-2 text-sm font-bold tracking-widest text-muted-foreground uppercase"
            >
                <div class="h-1 w-4 rounded-full bg-primary"></div>
                Incoming Connections (Parents)
            </h2>
            <div class="grid gap-3">
                <Card
                    v-for="conn in incomingConnections"
                    :key="conn.id"
                    class="border-dashed bg-muted/20"
                >
                    <CardContent class="flex items-center justify-between p-4">
                        <div class="flex items-center gap-4">
                            <div class="rounded-lg bg-blue-500/10 p-2">
                                <LinkIcon class="h-4 w-4 text-blue-500" />
                            </div>
                            <div>
                                <div class="text-sm font-bold">
                                    {{ conn.source?.name }} ({{
                                        conn.source?.code
                                    }})
                                </div>
                                <div class="text-xs text-muted-foreground">
                                    Parent Port:
                                    <span class="font-mono text-primary">{{
                                        conn.source_port || '-'
                                    }}</span>
                                    → Local Port:
                                    <span class="font-mono text-primary">{{
                                        conn.destination_port || '-'
                                    }}</span>
                                </div>
                            </div>
                        </div>
                        <Badge variant="outline" class="text-[10px]">{{
                            conn.connection_type
                        }}</Badge>
                    </CardContent>
                </Card>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <h2
                class="flex items-center gap-2 text-sm font-bold tracking-widest text-muted-foreground uppercase"
            >
                <div class="h-1 w-4 rounded-full bg-green-500"></div>
                Outbound Connections
            </h2>
            <Dialog v-model:open="showAddForm">
                <DialogTrigger as-child>
                    <Button size="sm" variant="ghost" class="h-8 px-2 text-xs">
                        <Plus class="mr-1 h-3 w-3" />
                        <span>New Link</span>
                    </Button>
                </DialogTrigger>
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
                                                (d) =>
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
                                <Input
                                    v-model="form.source_port"
                                    class="h-10 w-full"
                                    placeholder="e.g. ETH 1"
                                />
                            </div>
                            <div class="space-y-2">
                                <Label
                                    class="text-[11px] font-bold tracking-wider text-muted-foreground uppercase"
                                    >Remote Port (Dest)</Label
                                >
                                <Input
                                    v-model="form.destination_port"
                                    class="h-10 w-full"
                                    placeholder="e.g. Uplink"
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
                            <Button type="submit" :disabled="form.processing"
                                >Save Link</Button
                            >
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>

        <div v-if="connections.length > 0">
            <div class="rounded-md border bg-card">
                <Table>
                    <TableHeader class="bg-muted/50">
                        <TableRow>
                            <TableHead class="w-[100px]">Port</TableHead>
                            <TableHead>Destination</TableHead>
                            <TableHead class="w-[100px]">Port</TableHead>
                            <TableHead>Label</TableHead>
                            <TableHead class="w-[50px]"></TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="conn in connections"
                            :key="conn.id"
                            class="hover:bg-muted/5"
                        >
                            <TableCell>
                                <div class="flex items-center gap-2">
                                    <Badge
                                        variant="outline"
                                        class="border-primary/20 bg-background font-mono text-xs shadow-sm"
                                        >{{ conn.source_port || 'N/A' }}</Badge
                                    >
                                    <ArrowRight
                                        class="h-3 w-3 text-muted-foreground/50"
                                    />
                                </div>
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded bg-primary/10 text-primary"
                                    >
                                        <Server class="h-4 w-4" />
                                    </div>
                                    <div class="flex flex-col">
                                        <span
                                            class="text-sm font-bold text-foreground"
                                            >{{ conn.destination?.name }}</span
                                        >
                                        <div
                                            class="flex items-center gap-1.5 text-[10px] text-muted-foreground"
                                        >
                                            <span
                                                class="rounded border border-border/50 bg-muted/60 px-1 font-mono"
                                                >{{
                                                    conn.destination?.code
                                                }}</span
                                            >
                                        </div>
                                    </div>
                                </div>
                            </TableCell>
                            <TableCell>
                                <Badge
                                    variant="outline"
                                    class="bg-muted/50 font-mono text-xs"
                                    >{{ conn.destination_port || 'N/A' }}</Badge
                                >
                            </TableCell>
                            <TableCell>
                                <div class="flex flex-col items-start gap-1">
                                    <span
                                        class="text-sm font-medium text-foreground/90"
                                        v-if="conn.description"
                                        >{{ conn.description }}</span
                                    >
                                    <Badge
                                        :variant="
                                            conn.connection_type === 'Uplink'
                                                ? 'default'
                                                : 'secondary'
                                        "
                                        class="h-4.5 px-1.5 text-[10px]"
                                    >
                                        {{ conn.connection_type }}
                                    </Badge>
                                </div>
                            </TableCell>
                            <TableCell>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    class="h-8 w-8 text-destructive opacity-70 hover:bg-destructive/10 hover:opacity-100"
                                    @click="deleteConnection(conn.id)"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
        <div
            v-else-if="!showAddForm"
            class="rounded-xl border border-dashed bg-muted/10 py-10 text-center"
        >
            <LinkIcon class="mx-auto mb-2 h-8 w-8 text-muted-foreground/30" />
            <p
                class="text-xs font-bold tracking-widest text-muted-foreground/60 uppercase"
            >
                No Outbound Links
            </p>
        </div>
    </div>
</template>
