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
import { router, useForm } from '@inertiajs/vue3';
import { Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    device: any;
    deviceType?: string; // Optional if we extract from device, but better explicit for polymorphic
}>();

// Determines the polymorphic type.
// If explicitly passed, use it. Otherwise, try to infer or default.
// Ideally, the parent passes the full class name e.g. "Modules\ActiveDevice\Models\Olt"
const polymorphicType =
    props.deviceType ||
    props.device.type ||
    'Modules\\ActiveDevice\\Models\\Olt'; // Fallback if needed

const isAddPortOpen = ref(false);

// Delete confirmation state
const deleteConfirmOpen = ref(false);
const deletingPortId = ref<number | null>(null);

const form = useForm({
    portable_id: props.device.id,
    portable_type: polymorphicType,
    name: '',
    port: '',
    status: 'Active',
});

const addPort = () => {
    // Ensure we use the correct type before submitting
    form.portable_type = polymorphicType;

    form.post(route('active-device.service-ports.store'), {
        onSuccess: () => {
            isAddPortOpen.value = false;
            form.reset('name', 'port', 'status');
        },
    });
};

const openDeleteConfirm = (id: number) => {
    deletingPortId.value = id;
    deleteConfirmOpen.value = true;
};

const confirmDelete = () => {
    if (deletingPortId.value) {
        router.delete(
            route('active-device.service-ports.destroy', deletingPortId.value),
            {
                preserveScroll: true,
                onSuccess: () => {
                    deleteConfirmOpen.value = false;
                    deletingPortId.value = null;
                },
            },
        );
    }
};

const cancelDelete = () => {
    deleteConfirmOpen.value = false;
    deletingPortId.value = null;
};
</script>

<template>
    <Card>
        <CardHeader class="flex flex-row items-center justify-between pb-3">
            <CardTitle
                class="text-sm font-medium tracking-wider text-muted-foreground uppercase"
                >Service Ports</CardTitle
            >
            <Dialog v-model:open="isAddPortOpen">
                <DialogTrigger as-child>
                    <Button size="sm" class="h-8 gap-1">
                        <Plus class="h-4 w-4" />
                        <span
                            class="sr-only sm:not-sr-only sm:whitespace-nowrap"
                        >
                            Add Service Port
                        </span>
                    </Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Add Service Port</DialogTitle>
                    </DialogHeader>
                    <form @submit.prevent="addPort" class="space-y-6 pt-4">
                        <div class="space-y-2">
                            <Label
                                for="name"
                                class="text-[11px] font-bold tracking-wider text-muted-foreground uppercase"
                                >Service Name</Label
                            >
                            <Input
                                id="name"
                                v-model="form.name"
                                class="h-10 w-full"
                                placeholder="e.g. SSH"
                                required
                            />
                        </div>
                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label
                                    for="port"
                                    class="text-[11px] font-bold tracking-wider text-muted-foreground uppercase"
                                    >Port Number</Label
                                >
                                <Input
                                    id="port"
                                    type="number"
                                    v-model="form.port"
                                    class="h-10 w-full"
                                    placeholder="e.g. 22"
                                    required
                                />
                            </div>
                            <div class="space-y-2">
                                <Label
                                    for="status"
                                    class="text-[11px] font-bold tracking-wider text-muted-foreground uppercase"
                                    >Status</Label
                                >
                                <Select v-model="form.status">
                                    <SelectTrigger class="h-10 w-full">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Active"
                                            >Active</SelectItem
                                        >
                                        <SelectItem value="Inactive"
                                            >Inactive</SelectItem
                                        >
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                        <DialogFooter class="border-t pt-4">
                            <Button
                                type="button"
                                variant="outline"
                                @click="isAddPortOpen = false"
                                >Cancel</Button
                            >
                            <Button type="submit" :disabled="form.processing"
                                >Save Port</Button
                            >
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </CardHeader>
        <CardContent>
            <div class="overflow-hidden rounded-md border">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-2 text-left font-medium">
                                Service Port
                            </th>
                            <th class="px-4 py-2 text-left font-medium">
                                Port
                            </th>
                            <th class="px-4 py-2 text-center font-medium">
                                Status
                            </th>
                            <th class="px-4 py-2 text-right font-medium">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr
                            v-for="port in device.service_ports"
                            :key="port.id"
                            class="hover:bg-muted/30"
                        >
                            <td class="px-4 py-2">
                                {{ port.name }}
                            </td>
                            <td class="px-4 py-2 font-mono text-xs">
                                {{ port.port }}
                            </td>
                            <td class="px-4 py-2 text-center">
                                <Badge
                                    :variant="
                                        port.status === 'Active'
                                            ? 'default'
                                            : 'secondary'
                                    "
                                    class="h-5 px-1.5 py-0 text-[10px]"
                                >
                                    {{ port.status }}
                                </Badge>
                            </td>
                            <td class="px-4 py-2 text-right">
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    class="h-7 w-7 text-destructive"
                                    @click="openDeleteConfirm(port.id)"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </td>
                        </tr>
                        <tr v-if="!device.service_ports?.length">
                            <td
                                colspan="4"
                                class="px-4 py-4 text-center text-muted-foreground italic"
                            >
                                No service ports defined.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </CardContent>

        <!-- Delete Confirmation Dialog -->
        <Dialog
            :open="deleteConfirmOpen"
            @update:open="deleteConfirmOpen = $event"
        >
            <DialogContent class="z-[100] sm:max-w-[400px]">
                <DialogHeader>
                    <DialogTitle>Konfirmasi Hapus</DialogTitle>
                    <DialogDescription>
                        Apakah Anda yakin ingin menghapus service port ini?
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
