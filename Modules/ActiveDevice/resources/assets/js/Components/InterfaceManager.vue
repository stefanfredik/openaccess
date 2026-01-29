<script setup lang="ts">
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
import { useForm } from '@inertiajs/vue3';
import { Pencil, PlusCircle, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    oltId: number;
    oltType: string;
    interfaces: any[];
}>();

const isAddOpen = ref(false);
const editingInterface = ref<any>(null);

const form = useForm({
    interfacable_id: props.oltId,
    interfacable_type: props.oltType,
    name: '',
    type: 'Gigabit',
    speed: '1000Mbps',
    mac_address: '',
    status: 'down',
    description: '',
});

const submit = () => {
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

const deleteInterface = (id: number) => {
    if (confirm('Are you sure you want to delete this interface?')) {
        form.delete(route('active-device.interfaces.destroy', id));
    }
};

const openAdd = () => {
    editingInterface.value = null;
    form.reset();
    isAddOpen.value = true;
};
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h3
                class="text-xs font-bold tracking-widest text-gray-400 uppercase"
            >
                Manage Interfaces
            </h3>
            <Button variant="outline" size="sm" @click="openAdd" class="h-8">
                <PlusCircle class="mr-2 h-4 w-4" />
                Add Interface
            </Button>
        </div>

        <div class="overflow-hidden rounded-lg border bg-white shadow-sm">
            <table class="w-full text-left text-sm">
                <thead class="border-b bg-slate-50">
                    <tr>
                        <th class="px-4 py-2 font-semibold">Name</th>
                        <th class="px-4 py-2 font-semibold">Type</th>
                        <th class="px-4 py-2 font-semibold">Status</th>
                        <th class="px-4 py-2 text-right font-semibold">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <tr
                        v-for="inf in interfaces"
                        :key="inf.id"
                        class="transition-colors hover:bg-slate-50/50"
                    >
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-900">
                                {{ inf.name }}
                            </div>
                            <div class="font-mono text-[10px] text-slate-500">
                                {{ inf.mac_address || 'No MAC' }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-xs text-slate-600">
                                {{ inf.type }}
                            </div>
                            <div class="text-[10px] text-slate-400">
                                {{ inf.speed }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span
                                :class="[
                                    'inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium transition-colors',
                                    inf.status === 'up'
                                        ? 'bg-green-100 text-green-700'
                                        : inf.status === 'error'
                                          ? 'bg-red-100 text-red-700'
                                          : inf.status === 'idle'
                                            ? 'bg-blue-100 text-blue-700'
                                            : 'bg-gray-100 text-gray-700',
                                ]"
                            >
                                {{ inf.status.toUpperCase() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-1">
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    @click="editInterface(inf)"
                                    class="h-8 w-8"
                                >
                                    <Pencil class="h-3.5 w-3.5" />
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    @click="deleteInterface(inf.id)"
                                    class="h-8 w-8 text-red-500 hover:text-red-700"
                                >
                                    <Trash2 class="h-3.5 w-3.5" />
                                </Button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="interfaces.length === 0">
                        <td
                            colspan="4"
                            class="px-4 py-8 text-center text-sm text-slate-400 italic"
                        >
                            No interfaces configured yet.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

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
                        {{ oltType.split('\\').pop() }}.
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submit" class="grid gap-4 py-4">
                    <div class="grid grid-cols-4 items-center gap-4">
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
                                : 'Create Interface'
                        }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
