<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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
import { Switch } from '@/components/ui/switch';
import { router, useForm } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const props = defineProps<{
    deviceId: number;
    deviceType: string;
    interfaces: any[];
}>();

const isAddOpen = ref(false);
const editingInterface = ref<any>(null);

const isBulkMode = ref(false);

// Delete confirmation state
const deleteConfirmOpen = ref(false);
const deletingInterfaceId = ref<number | null>(null);

const form = useForm({
    interfacable_id: props.deviceId,
    interfacable_type: props.deviceType,
    name: '',
    type: 'Gigabit',
    speed: '1000Mbps',
    status: 'down',
    description: '',
    is_bulk: false,
    prefix: '',
    start_number: 1,
    count: 1,
});

// Sync local ref with form
watch(isBulkMode, (val) => {
    form.is_bulk = val;
});

const submit = () => {
    // Ensure correct polymorphic types are set before submit
    form.interfacable_id = props.deviceId;
    form.interfacable_type = props.deviceType;

    if (editingInterface.value) {
        form.patch(
            route('active-device.interfaces.update', editingInterface.value.id),
            {
                onSuccess: () => {
                    isAddOpen.value = false;
                    editingInterface.value = null;
                    form.reset();
                },
            },
        );
    } else {
        form.post(route('active-device.interfaces.store'), {
            onSuccess: () => {
                isAddOpen.value = false;
                form.reset();
            },
        });
    }
};

const editInterface = (inf: any) => {
    editingInterface.value = inf;
    form.name = inf.name;
    form.type = inf.type;
    form.speed = inf.speed;
    form.mac_address = inf.mac_address;
    form.status = inf.status;
    form.description = inf.description;
    isAddOpen.value = true;
};

const openDeleteConfirm = (id: number) => {
    deletingInterfaceId.value = id;
    deleteConfirmOpen.value = true;
};

const confirmDelete = () => {
    if (deletingInterfaceId.value) {
        router.delete(
            route(
                'active-device.interfaces.destroy',
                deletingInterfaceId.value,
            ),
            {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    deleteConfirmOpen.value = false;
                    deletingInterfaceId.value = null;
                },
            },
        );
    }
};

const cancelDelete = () => {
    deleteConfirmOpen.value = false;
    deletingInterfaceId.value = null;
};

const openAdd = () => {
    editingInterface.value = null;
    isBulkMode.value = false;
    form.reset();
    isAddOpen.value = true;
};

defineExpose({ openAdd });
</script>

<template>
    <Card>
        <CardHeader class="flex flex-row items-center justify-between pb-3">
            <CardTitle
                class="text-sm font-medium tracking-wider text-muted-foreground uppercase"
            >
                Physical Interfaces
            </CardTitle>
            <Button size="sm" class="h-8 gap-1" @click="openAdd">
                <Plus class="h-4 w-4" />
                <span class="sr-only sm:not-sr-only sm:whitespace-nowrap">
                    Add Interface
                </span>
            </Button>
        </CardHeader>
        <CardContent>
            <div class="overflow-hidden rounded-md border">
                <table class="w-full text-left text-sm">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-2 font-semibold">Name</th>
                            <th class="px-4 py-2 font-semibold">Type</th>
                            <th class="px-4 py-2 font-semibold">Status</th>
                            <th class="px-4 py-2 text-right font-semibold">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr
                            v-for="inf in interfaces"
                            :key="inf.id"
                            class="transition-colors hover:bg-muted/30"
                        >
                            <td class="px-4 py-3">
                                <div class="font-medium">
                                    {{ inf.name }}
                                </div>
                                <div
                                    class="font-mono text-[10px] text-muted-foreground"
                                >
                                    {{ inf.mac_address || 'No MAC' }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-xs text-muted-foreground">
                                <div>{{ inf.type }}</div>
                                <div class="text-[10px]">{{ inf.speed }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <Badge
                                    :variant="
                                        inf.status === 'up'
                                            ? 'default'
                                            : inf.status === 'error'
                                              ? 'destructive'
                                              : 'secondary'
                                    "
                                    class="h-5 px-1.5 py-0 text-[10px] capitalize"
                                >
                                    {{ inf.status }}
                                </Badge>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-1">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        @click="editInterface(inf)"
                                        class="h-7 w-7"
                                    >
                                        <Pencil class="h-3.5 w-3.5" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        @click="openDeleteConfirm(inf.id)"
                                        class="h-7 w-7 text-destructive"
                                    >
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="interfaces.length === 0">
                            <td
                                colspan="4"
                                class="px-4 py-8 text-center text-sm text-muted-foreground italic"
                            >
                                No interfaces configured yet.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </CardContent>

        <Dialog :open="isAddOpen" @update:open="isAddOpen = $event">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>{{
                        editingInterface
                            ? 'Edit Interface'
                            : 'Add Physical Interface'
                    }}</DialogTitle>
                    <DialogDescription>
                        Konfigurasi port fisik pada perangkat
                        {{ deviceType.split('\\').pop() }}.
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submit" class="grid gap-4 py-4">
                    <div
                        v-if="!editingInterface"
                        class="grid grid-cols-4 items-center gap-4"
                    >
                        <Label for="bulk" class="text-right">Bulk Mode</Label>
                        <div class="col-span-3 flex items-center gap-2">
                            <Switch
                                id="bulk"
                                :model-value="isBulkMode"
                                @update:model-value="isBulkMode = $event"
                            />
                            <Label for="bulk" class="font-normal"
                                >Create multiple interfaces sequentially</Label
                            >
                        </div>
                    </div>

                    <div
                        v-if="isBulkMode && !editingInterface"
                        class="grid gap-4"
                    >
                        <div class="grid grid-cols-4 items-center gap-4">
                            <Label for="prefix" class="text-right"
                                >Prefix</Label
                            >
                            <Input
                                id="prefix"
                                v-model="form.prefix"
                                placeholder="e.g. GE1/0/"
                                class="col-span-3"
                            />
                        </div>
                        <div class="grid grid-cols-4 items-center gap-4">
                            <Label for="start" class="text-right"
                                >Start From</Label
                            >
                            <Input
                                id="start"
                                type="number"
                                v-model="form.start_number"
                                class="col-span-3"
                                min="0"
                            />
                        </div>
                        <div class="grid grid-cols-4 items-center gap-4">
                            <Label for="count" class="text-right">Count</Label>
                            <Input
                                id="count"
                                type="number"
                                v-model="form.count"
                                class="col-span-3"
                                min="1"
                                max="48"
                            />
                        </div>
                    </div>

                    <div
                        v-if="!isBulkMode && !editingInterface"
                        class="grid grid-cols-4 items-center gap-4"
                    >
                        <Label for="name" class="text-right">Name</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            placeholder="GE01, SFP01..."
                            class="col-span-3"
                            required
                        />
                    </div>
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="type" class="text-right">Type</Label>
                        <Select v-model="form.type">
                            <SelectTrigger class="col-span-3">
                                <SelectValue placeholder="Select type" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="Gigabit"
                                    >Gigabit (Copper)</SelectItem
                                >
                                <SelectItem value="Fiber"
                                    >Fiber (SFP)</SelectItem
                                >
                                <SelectItem value="PON">PON Port</SelectItem>
                                <SelectItem value="Console">Console</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="speed" class="text-right">Speed</Label>
                        <Input
                            id="speed"
                            v-model="form.speed"
                            placeholder="1000Mbps, 10Gbps..."
                            class="col-span-3"
                        />
                    </div>
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="mac" class="text-right">MAC</Label>
                        <Input
                            id="mac"
                            v-model="form.mac_address"
                            placeholder="00:11:22:33:44:55"
                            class="col-span-3"
                        />
                    </div>
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="status" class="text-right">Status</Label>
                        <Select v-model="form.status">
                            <SelectTrigger class="col-span-3">
                                <SelectValue placeholder="Select status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="up"
                                    >UP (Connected)</SelectItem
                                >
                                <SelectItem value="down">DOWN</SelectItem>
                                <SelectItem value="idle">IDLE</SelectItem>
                                <SelectItem value="error">ERROR</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </form>

                <DialogFooter>
                    <Button
                        type="button"
                        variant="ghost"
                        @click="isAddOpen = false"
                        >Cancel</Button
                    >
                    <Button
                        type="submit"
                        @click="submit"
                        :disabled="form.processing"
                    >
                        {{
                            editingInterface
                                ? 'Save Changes'
                                : isBulkMode
                                  ? `Create ${form.count} Interfaces`
                                  : 'Create Interface'
                        }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation Dialog -->
        <Dialog
            :open="deleteConfirmOpen"
            @update:open="deleteConfirmOpen = $event"
        >
            <DialogContent class="z-[100] sm:max-w-[400px]">
                <DialogHeader>
                    <DialogTitle>Konfirmasi Hapus</DialogTitle>
                    <DialogDescription>
                        Apakah Anda yakin ingin menghapus interface ini?
                        Tindakan ini tidak dapat dibatalkan.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-2 sm:gap-0">
                    <Button variant="outline" @click="cancelDelete">
                        Batal
                    </Button>
                    <Button variant="destructive" @click="confirmDelete">
                        Hapus
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </Card>
</template>
