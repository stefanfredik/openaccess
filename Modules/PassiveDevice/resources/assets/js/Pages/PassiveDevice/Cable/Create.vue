<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
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
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
// import { index as cableIndex, store as cableStore } from '@/routes/passive-device/cable';

defineProps<{
    areas: Array<any>;
}>();

const form = useForm({
    infrastructure_area_id: '',
    code: '',
    name: '',
    type: '',
    length: 0,
    core_count: 0,
    brand: '',
    model: '',
    description: '',
    is_active: true,
    installed_at: '',
});

const submit = () => {
    form.post(route('passive-device.cable.store'));
};
</script>

<template>
    <Head title="Add Cable" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Cables', href: route('passive-device.cable.index') },
            { title: 'Add Cable', href: '#' },
        ]"
    >
        <div class="mx-auto flex max-w-4xl flex-col gap-6 p-4 md:p-6 lg:p-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Add Cable</h1>
                    <p class="text-muted-foreground">
                        Register a new Fiber Optic Cable asset.
                    </p>
                </div>
            </div>

            <form @submit.prevent="submit">
                <Card>
                    <CardHeader>
                        <CardTitle>Cable Information</CardTitle>
                        <CardDescription>
                            Technical specifications and type of the cable.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="grid gap-6">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="area">Infrastructure Area</Label>
                                <Select v-model="form.infrastructure_area_id">
                                    <SelectTrigger
                                        :class="{
                                            'border-destructive':
                                                form.errors
                                                    .infrastructure_area_id,
                                        }"
                                    >
                                        <SelectValue
                                            placeholder="Select Area"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="area in areas"
                                            :key="area.id"
                                            :value="area.id.toString()"
                                        >
                                            {{ area.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p
                                    v-if="form.errors.infrastructure_area_id"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.infrastructure_area_id }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="code">Code</Label>
                                <Input
                                    id="code"
                                    v-model="form.code"
                                    :class="{
                                        'border-destructive': form.errors.code,
                                    }"
                                    placeholder="CABLE-XYZ"
                                />
                                <p
                                    v-if="form.errors.code"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.code }}
                                </p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="name">Name</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                :class="{
                                    'border-destructive': form.errors.name,
                                }"
                                placeholder="Core Backbone Jl. Merdeka"
                            />
                            <p
                                v-if="form.errors.name"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div class="space-y-2">
                                <Label for="type">Cable Type</Label>
                                <Input
                                    id="type"
                                    v-model="form.type"
                                    :class="{
                                        'border-destructive': form.errors.type,
                                    }"
                                    placeholder="ADSS, FIG8, etc."
                                />
                                <p
                                    v-if="form.errors.type"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.type }}
                                </p>
                            </div>
                            <div class="space-y-2">
                                <Label for="length">Length (meters)</Label>
                                <Input
                                    id="length"
                                    type="number"
                                    v-model="form.length"
                                    :class="{
                                        'border-destructive':
                                            form.errors.length,
                                    }"
                                />
                                <p
                                    v-if="form.errors.length"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.length }}
                                </p>
                            </div>
                            <div class="space-y-2">
                                <Label for="core_count">Core Count</Label>
                                <Input
                                    id="core_count"
                                    type="number"
                                    v-model="form.core_count"
                                    :class="{
                                        'border-destructive':
                                            form.errors.core_count,
                                    }"
                                />
                                <p
                                    v-if="form.errors.core_count"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.core_count }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="brand">Brand</Label>
                                <Input
                                    id="brand"
                                    v-model="form.brand"
                                    :class="{
                                        'border-destructive': form.errors.brand,
                                    }"
                                />
                                <p
                                    v-if="form.errors.brand"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.brand }}
                                </p>
                            </div>
                            <div class="space-y-2">
                                <Label for="model">Model</Label>
                                <Input
                                    id="model"
                                    v-model="form.model"
                                    :class="{
                                        'border-destructive': form.errors.model,
                                    }"
                                />
                                <p
                                    v-if="form.errors.model"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.model }}
                                </p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="installed_at">Installed At</Label>
                            <Input
                                id="installed_at"
                                type="date"
                                v-model="form.installed_at"
                                :class="{
                                    'border-destructive':
                                        form.errors.installed_at,
                                }"
                            />
                            <p
                                v-if="form.errors.installed_at"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.installed_at }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                                :class="{
                                    'border-destructive':
                                        form.errors.description,
                                }"
                            />
                            <p
                                v-if="form.errors.description"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.description }}
                            </p>
                        </div>

                        <div class="flex items-center space-x-2">
                            <Switch
                                id="is_active"
                                v-model:checked="form.is_active"
                            />
                            <Label for="is_active">Active Status</Label>
                        </div>
                    </CardContent>
                    <CardFooter
                        class="mt-6 flex justify-end gap-2 border-t p-6"
                    >
                        <Button variant="outline" as-child>
                            <Link :href="route('passive-device.cable.index')"
                                >Cancel</Link
                            >
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Saving...' : 'Save Cable' }}
                        </Button>
                    </CardFooter>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
