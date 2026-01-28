<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Trash2, Link as LinkIcon, Plus, ArrowRight, Server } from 'lucide-vue-next';
import { ref } from 'vue';
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';

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
            form.reset('destination_id', 'destination_type', 'source_port', 'destination_port', 'description');
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
            <h2 class="text-sm font-bold uppercase tracking-widest text-muted-foreground mb-3 flex items-center gap-2">
                <div class="h-1 w-4 bg-primary rounded-full"></div>
                Incoming Connections (Parents)
            </h2>
            <div class="grid gap-3">
                <Card v-for="conn in incomingConnections" :key="conn.id" class="border-dashed bg-muted/20">
                    <CardContent class="flex items-center justify-between p-4">
                        <div class="flex items-center gap-4">
                            <div class="bg-blue-500/10 p-2 rounded-lg">
                                <LinkIcon class="h-4 w-4 text-blue-500" />
                            </div>
                            <div>
                                <div class="font-bold text-sm">
                                    {{ conn.source?.name }} ({{ conn.source?.code }})
                                </div>
                                <div class="text-xs text-muted-foreground">
                                    Parent Port: <span class="text-primary font-mono">{{ conn.source_port || '-' }}</span> 
                                    → Local Port: <span class="text-primary font-mono">{{ conn.destination_port || '-' }}</span>
                                </div>
                            </div>
                        </div>
                        <Badge variant="outline" class="text-[10px]">{{ conn.connection_type }}</Badge>
                    </CardContent>
                </Card>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold uppercase tracking-widest text-muted-foreground flex items-center gap-2">
                <div class="h-1 w-4 bg-green-500 rounded-full"></div>
                Outbound Connections
            </h2>
            <Button size="sm" variant="ghost" class="h-8 px-2 text-xs" @click="showAddForm = !showAddForm">
                <Plus v-if="!showAddForm" class="mr-1 h-3 w-3" />
                <span v-if="!showAddForm">New Link</span>
                <span v-else>Cancel</span>
            </Button>
        </div>

        <Card v-if="showAddForm" class="border-primary/20 bg-primary/5">
            <CardHeader class="pb-2">
                <CardTitle class="text-base font-bold">Add New Link</CardTitle>
                <CardDescription class="text-xs">Connect this device to a downstream target.</CardDescription>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submit" class="grid gap-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <Label class="text-[11px] font-bold uppercase">Target Device Type</Label>
                            <Select v-model="form.destination_type">
                                <SelectTrigger class="h-9">
                                    <SelectValue placeholder="Select Type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="Modules\ActiveDevice\Models\Router">Router</SelectItem>
                                    <SelectItem value="Modules\ActiveDevice\Models\AdSwitch">Switch</SelectItem>
                                    <SelectItem value="Modules\ActiveDevice\Models\Olt">OLT</SelectItem>
                                    <SelectItem value="Modules\ActiveDevice\Models\Ont">ONT</SelectItem>
                                    <SelectItem value="Modules\ActiveDevice\Models\AccessPoint">AP</SelectItem>
                                    <SelectItem value="Modules\Cpe\Models\Cpe">CPE</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-1">
                            <Label class="text-[11px] font-bold uppercase">Target Device</Label>
                            <Select v-model="form.destination_id" :disabled="!form.destination_type">
                                <SelectTrigger class="h-9">
                                    <SelectValue placeholder="Select Device" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="d in availableDevices.filter(d => d.type === form.destination_type)" :key="d.id" :value="d.id.toString()">
                                        {{ d.name }} ({{ d.code }})
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="space-y-1">
                            <Label class="text-[11px] font-bold uppercase">Local Port (Source)</Label>
                            <Input v-model="form.source_port" class="h-9" placeholder="e.g. ETH 1" />
                        </div>
                        <div class="space-y-1">
                            <Label class="text-[11px] font-bold uppercase">Remote Port (Dest)</Label>
                            <Input v-model="form.destination_port" class="h-9" placeholder="e.g. Uplink" />
                        </div>
                        <div class="space-y-1">
                            <Label class="text-[11px] font-bold uppercase">Link Type</Label>
                            <Select v-model="form.connection_type">
                                <SelectTrigger class="h-9">
                                    <SelectValue placeholder="Select Type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="Uplink">Uplink</SelectItem>
                                    <SelectItem value="Downlink">Downlink</SelectItem>
                                    <SelectItem value="Fiber">Fiber</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                    <Button type="submit" size="sm" class="w-full font-bold" :disabled="form.processing">Save Link</Button>
                </form>
            </CardContent>
        </Card>

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
                        <TableRow v-for="conn in connections" :key="conn.id" class="hover:bg-muted/5">
                            <TableCell>
                                <div class="flex items-center gap-2">
                                     <Badge variant="outline" class="font-mono text-xs bg-background shadow-sm border-primary/20">{{ conn.source_port || 'N/A' }}</Badge>
                                     <ArrowRight class="h-3 w-3 text-muted-foreground/50" />
                                </div>
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded bg-primary/10 flex items-center justify-center text-primary shrink-0">
                                        <Server class="h-4 w-4" />
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-sm text-foreground">{{ conn.destination?.name }}</span>
                                        <div class="text-[10px] text-muted-foreground flex items-center gap-1.5">
                                            <span class="font-mono bg-muted/60 px-1 rounded border border-border/50">{{ conn.destination?.code }}</span>
                                        </div>
                                    </div>
                                </div>
                            </TableCell>
                            <TableCell>
                                <Badge variant="outline" class="font-mono text-xs bg-muted/50">{{ conn.destination_port || 'N/A' }}</Badge>
                            </TableCell>
                            <TableCell>
                                <div class="flex flex-col items-start gap-1">
                                    <span class="font-medium text-sm text-foreground/90" v-if="conn.description">{{ conn.description }}</span>
                                    <Badge :variant="conn.connection_type === 'Uplink' ? 'default' : 'secondary'" class="text-[10px] h-4.5 px-1.5">
                                        {{ conn.connection_type }}
                                    </Badge>
                                </div>
                            </TableCell>
                            <TableCell>
                                <Button variant="ghost" size="icon" class="h-8 w-8 text-destructive opacity-70 hover:opacity-100 hover:bg-destructive/10" @click="deleteConnection(conn.id)">
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
        <div v-else-if="!showAddForm" class="text-center py-10 border rounded-xl border-dashed bg-muted/10">
            <LinkIcon class="h-8 w-8 mx-auto text-muted-foreground/30 mb-2" />
            <p class="text-xs font-bold text-muted-foreground/60 uppercase tracking-widest">No Outbound Links</p>
        </div>
    </div>
</template>
