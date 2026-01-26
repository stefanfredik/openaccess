<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';

const props = defineProps<{
    splicingPoint: any;
    areas: Array<any>;
    jointBoxes: Array<any>;
}>();

const form = useForm({
    infrastructure_area_id: props.splicingPoint.infrastructure_area_id.toString(),
    joint_box_id: props.splicingPoint.joint_box_id.toString(),
    code: props.splicingPoint.code,
    name: props.splicingPoint.name,
    tray_number: props.splicingPoint.tray_number || '',
    splicing_type: props.splicingPoint.splicing_type || '',
    status: props.splicingPoint.status || 'Active',
    loss: props.splicingPoint.loss || 0,
    description: props.splicingPoint.description || '',
    is_active: !!props.splicingPoint.is_active,
    spliced_at: props.splicingPoint.spliced_at || '',
});

const submit = () => {
    form.put(`/passive-devices/splicing-point/${props.splicingPoint.id}`);
};
</script>

<template>
    <Head title="Edit Splicing Point" />

    <AppLayout :breadcrumbs="[
        { title: 'Splicing Points', href: '/passive-devices/splicing-point' },
        { title: 'Edit Splicing Point', href: '#' }
    ]">
        <div class="flex flex-col gap-6 p-4 md:p-6 lg:p-8 max-w-4xl mx-auto">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Edit Splicing Point</h1>
                    <p class="text-muted-foreground">Update fiber splicing point details.</p>
                </div>
            </div>

            <form @submit.prevent="submit">
                <Card>
                    <CardHeader>
                        <CardTitle>Splicing Information</CardTitle>
                        <CardDescription>
                            Update details for {{ splicingPoint.name }}.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="grid gap-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="area">Infrastructure Area</Label>
                                <Select v-model="form.infrastructure_area_id">
                                    <SelectTrigger :class="{ 'border-destructive': form.errors.infrastructure_area_id }">
                                        <SelectValue placeholder="Select Area" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="area in areas" :key="area.id" :value="area.id.toString()">
                                            {{ area.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.infrastructure_area_id" class="text-sm text-destructive">{{ form.errors.infrastructure_area_id }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="joint_box">Joint Box</Label>
                                <Select v-model="form.joint_box_id">
                                    <SelectTrigger :class="{ 'border-destructive': form.errors.joint_box_id }">
                                        <SelectValue placeholder="Select Joint Box" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="jb in jointBoxes" :key="jb.id" :value="jb.id.toString()">
                                            {{ jb.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.joint_box_id" class="text-sm text-destructive">{{ form.errors.joint_box_id }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="code">Code</Label>
                                <Input id="code" v-model="form.code" :class="{ 'border-destructive': form.errors.code }" />
                                <p v-if="form.errors.code" class="text-sm text-destructive">{{ form.errors.code }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="name">Name</Label>
                                <Input id="name" v-model="form.name" :class="{ 'border-destructive': form.errors.name }" />
                                <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="tray_number">Tray Number</Label>
                                <Input id="tray_number" v-model="form.tray_number" />
                            </div>
                            <div class="space-y-2">
                                <Label for="splicing_type">Splicing Type</Label>
                                <Select v-model="form.splicing_type">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select Type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Core-Core">Core-Core</SelectItem>
                                        <SelectItem value="Pigtail-Core">Pigtail-Core</SelectItem>
                                        <SelectItem value="Splitter-Core">Splitter-Core</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="status">Status</Label>
                                <Select v-model="form.status">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select Status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Active">Active</SelectItem>
                                        <SelectItem value="Spare">Spare</SelectItem>
                                        <SelectItem value="Damaged">Damaged</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="space-y-2">
                                <Label for="loss">Loss (dB)</Label>
                                <Input id="loss" type="number" step="0.01" v-model="form.loss" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="spliced_at">Spliced At</Label>
                                <Input id="spliced_at" type="date" v-model="form.spliced_at" />
                            </div>
                            <div class="flex items-center space-x-2 pt-8">
                                <Switch id="is_active" v-model:checked="form.is_active" />
                                <Label for="is_active">Active Status</Label>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea id="description" v-model="form.description" />
                        </div>
                    </CardContent>
                    <CardFooter class="flex justify-end gap-2 border-t p-6 mt-6">
                        <Button variant="outline" as-child>
                            <Link href="/passive-devices/splicing-point">Cancel</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Updating...' : 'Update Splicing Point' }}
                        </Button>
                    </CardFooter>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
