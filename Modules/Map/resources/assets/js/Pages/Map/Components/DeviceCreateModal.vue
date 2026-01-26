<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { toast } from 'vue-sonner';

const props = defineProps<{
    open: boolean;
    deviceType: string | null;
    lat: number | null;
    lng: number | null;
    path?: Array<[number, number]> | null;
    areas: Array<any>;
    pops: Array<any>;
}>();

const emit = defineEmits(['update:open', 'success']);

const form = useForm({
    infrastructure_area_id: '',
    pop_id: '',
    code: '',
    name: '',
    brand: '',
    model: '',
    latitude: '',
    longitude: '',
    path: [] as Array<[number, number]>,
    description: '',
    // specific fields
    port_count: 0,
    core_capacity: 0,
    onu_type: '',
    frequency: '',
    length: 0,
    core_count: 0,
    type: 'Single Mode',
    // tower fields
    height: '',
    ownership: 'Sendiri',
    antenna_capacity: '',
});
watch(() => props.open, (newVal) => {
    if (newVal) {
        form.reset();
        form.latitude = props.lat?.toString() || '';
        form.longitude = props.lng?.toString() || '';
        form.path = props.path || [];
        form.infrastructure_area_id = '';
        form.pop_id = '';
    }
});

const getStoreUrl = () => {
    const urls: Record<string, string> = {
        'olt': '/active-devices/olt',
        'ont': '/active-devices/ont',
        'router': '/active-devices/router',
        'switch': '/active-devices/switch',
        'access-point': '/active-devices/access-point',
        'odp': '/passive-devices/odp',
        'odf': '/passive-devices/odf',
        'pole': '/passive-devices/pole',
        'tower': '/passive-devices/tower',
        'joint-box': '/passive-devices/joint-box',
        'cable': '/passive-devices/cable',
    };
    return urls[props.deviceType || ''] || '';
};

const submit = () => {
    form.post(getStoreUrl(), {
        onSuccess: () => {
            emit('update:open', false);
            emit('success');
            toast.success('Device created successfully');
        },
        onError: (errors) => {
            toast.error('Failed to create device. Please check the form.');
            console.error(errors);
        }
    });
};

const isActiveDevice = () => {
    return ['olt', 'ont', 'router', 'switch', 'access-point'].includes(props.deviceType || '');
};
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-[500px]">
            <DialogHeader>
                <DialogTitle>Add New {{ deviceType?.toUpperCase().replace('-', ' ') }}</DialogTitle>
                <DialogDescription>
                    Fill in the details for the new device at the selected location.
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-4 py-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label>Area</Label>
                        <Select v-model="form.infrastructure_area_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Select Area" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="area in areas" :key="area.id" :value="area.id.toString()">
                                    {{ area.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.infrastructure_area_id" class="text-xs text-red-500">{{ form.errors.infrastructure_area_id }}</p>
                    </div>

                    <div v-if="isActiveDevice()" class="space-y-2">
                        <Label>POP</Label>
                        <Select v-model="form.pop_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Select POP" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="pop in pops" :key="pop.id" :value="pop.id.toString()">
                                    {{ pop.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.pop_id" class="text-xs text-red-500">{{ form.errors.pop_id }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label>Code</Label>
                        <Input v-model="form.code" placeholder="Device Code" />
                        <p v-if="form.errors.code" class="text-xs text-red-500">{{ form.errors.code }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label>Name</Label>
                        <Input v-model="form.name" placeholder="Device Name" />
                        <p v-if="form.errors.name" class="text-xs text-red-500">{{ form.errors.name }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label>Latitude</Label>
                        <Input v-model="form.latitude" readonly class="bg-gray-50" />
                    </div>
                    <div class="space-y-2">
                        <Label>Longitude</Label>
                        <Input v-model="form.longitude" readonly class="bg-gray-50" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4" v-if="deviceType !== 'tower'">
                    <div class="space-y-2">
                        <Label>Brand</Label>
                        <Input v-model="form.brand" />
                    </div>
                    <div class="space-y-2">
                        <Label>Model</Label>
                        <Input v-model="form.model" />
                    </div>
                </div>

                <!-- Type Specific Fields -->
                <div v-if="deviceType === 'odp' || deviceType === 'router' || deviceType === 'switch'" class="space-y-2">
                    <Label>Port Count</Label>
                    <Input type="number" v-model="form.port_count" />
                </div>

                <div v-if="deviceType === 'odf'" class="space-y-2">
                    <Label>Core Capacity</Label>
                    <Input type="number" v-model="form.core_capacity" />
                </div>

                <div v-if="deviceType === 'ont'" class="space-y-2">
                    <Label>ONU Type (GPON/EPON/XPON)</Label>
                    <Input v-model="form.onu_type" />
                </div>

                <div v-if="deviceType === 'access-point'" class="space-y-2">
                    <Label>Frequency</Label>
                    <Input v-model="form.frequency" placeholder="2.4GHz / 5GHz" />
                </div>

                <!-- Tower Specific Fields -->
                <div v-if="deviceType === 'tower'" class="space-y-4 pt-2 border-t">
                     <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>Tower Type</Label>
                            <Select v-model="form.type">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select Type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="SST">SST</SelectItem>
                                    <SelectItem value="Monopole">Monopole</SelectItem>
                                    <SelectItem value="Guyed">Guyed</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label>Height (m)</Label>
                            <Input type="number" v-model="form.height" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                         <div class="space-y-2">
                            <Label>Ownership</Label>
                            <Select v-model="form.ownership">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select Ownership" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="Sendiri">Sendiri</SelectItem>
                                    <SelectItem value="Sewa">Sewa</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label>Antenna Capacity</Label>
                            <Input v-model="form.antenna_capacity" />
                        </div>
                    </div>
                </div>

                <!-- Cable Specific Fields -->
                <div v-if="deviceType === 'cable'" class="space-y-4 pt-2 border-t">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>Cable Type</Label>
                            <Select v-model="form.type">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select Type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="Single Mode">Single Mode</SelectItem>
                                    <SelectItem value="Multi Mode">Multi Mode</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label>Core Count</Label>
                            <Input type="number" v-model="form.core_count" />
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label>Approx. Length (m)</Label>
                        <Input type="number" step="0.01" v-model="form.length" />
                    </div>
                </div>

                <div class="space-y-2">
                    <Label>Description</Label>
                    <Textarea v-model="form.description" rows="2" />
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="emit('update:open', false)">
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Saving...' : 'Save Device' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
