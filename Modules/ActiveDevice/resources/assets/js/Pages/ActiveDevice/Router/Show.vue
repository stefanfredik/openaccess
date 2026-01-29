<script setup lang="ts">
import ConnectionManager from '@/../../Modules/ActiveDevice/resources/assets/js/Components/ConnectionManager.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
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
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router as inertiaRouter, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    router: any;
    availableDevices: Array<any>;
}>();

const isAddPortOpen = ref(false);

const form = useForm({
    portable_id: props.router.id,
    portable_type: 'Modules\\ActiveDevice\\Models\\Router',
    name: '',
    port: '',
    status: 'Active',
});

const addPort = () => {
    form.post(route('active-device.service-ports.store'), {
        onSuccess: () => {
            isAddPortOpen.value = false;
            form.reset('name', 'port', 'status');
        },
    });
};

const deletePort = (id: number) => {
    if (confirm('Are you sure you want to delete this service port?')) {
        inertiaRouter.delete(route('active-device.service-ports.destroy', id));
    }
};
</script>

<template>
    <Head :title="`Router: ${router.name}`" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Routers', href: route('active-device.router.index') },
            { title: router.name, href: '#' },
        ]"
    >
        <div class="flex w-full flex-col gap-6 p-4 md:p-6 lg:p-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" as-child>
                        <Link :href="route('active-device.router.index')">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">
                            {{ router.name }}
                        </h1>
                        <p class="text-muted-foreground">{{ router.code }}</p>
                    </div>
                </div>
                <Button as-child>
                    <Link :href="route('active-device.router.edit', router.id)">
                        <Pencil class="mr-2 h-4 w-4" />
                        Edit Router
                    </Link>
                </Button>
            </div>

            <div class="flex flex-col gap-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Details</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <dl
                            class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2 md:grid-cols-3"
                        >
                            <div>
                                <dt
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Area
                                </dt>
                                <dd class="text-sm font-semibold">
                                    {{ router.area?.name || '-' }}
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    POP / Site
                                </dt>
                                <dd class="text-sm font-semibold">
                                    {{ router.pop?.name || '-' }}
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Status
                                </dt>
                                <dd>
                                    <Badge
                                        :variant="
                                            router.is_active
                                                ? 'default'
                                                : 'destructive'
                                        "
                                    >
                                        {{
                                            router.is_active
                                                ? 'Active'
                                                : 'Inactive'
                                        }}
                                    </Badge>
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Ports
                                </dt>
                                <dd class="text-sm font-semibold">
                                    {{ router.port_count }} Ports
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Brand / Model
                                </dt>
                                <dd class="text-sm font-semibold">
                                    {{ router.brand || '-' }} /
                                    {{ router.model || '-' }}
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    IP Address
                                </dt>
                                <dd class="font-mono text-sm font-semibold">
                                    {{ router.ip_address || '-' }}
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    MAC Address
                                </dt>
                                <dd class="font-mono text-sm font-semibold">
                                    {{ router.mac_address || '-' }}
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Serial Number
                                </dt>
                                <dd class="font-mono text-sm font-semibold">
                                    {{ router.serial_number || '-' }}
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Installed At
                                </dt>
                                <dd class="text-sm font-semibold">
                                    {{ router.installed_at || '-' }}
                                </dd>
                            </div>
                            <div class="sm:col-span-2 md:col-span-3">
                                <dt
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Description
                                </dt>
                                <dd class="mt-1 text-sm">
                                    {{
                                        router.description ||
                                        'No description provided.'
                                    }}
                                </dd>
                            </div>
                        </dl>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between"
                    >
                        <CardTitle>Service Ports</CardTitle>
                        <Dialog v-model:open="isAddPortOpen">
                            <DialogTrigger as-child>
                                <Button size="sm">
                                    <Plus class="mr-2 h-4 w-4" />
                                    Add Port
                                </Button>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogHeader>
                                    <DialogTitle>Add Service Port</DialogTitle>
                                </DialogHeader>
                                <form
                                    @submit.prevent="addPort"
                                    class="space-y-6 pt-4"
                                >
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
                                                <SelectTrigger
                                                    class="h-10 w-full"
                                                >
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
                                        <Button
                                            type="submit"
                                            :disabled="form.processing"
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
                                        <th
                                            class="px-4 py-2 text-left font-medium"
                                        >
                                            Service Port
                                        </th>
                                        <th
                                            class="px-4 py-2 text-left font-medium"
                                        >
                                            Port
                                        </th>
                                        <th
                                            class="px-4 py-2 text-center font-medium"
                                        >
                                            Status
                                        </th>
                                        <th
                                            class="px-4 py-2 text-right font-medium"
                                        >
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr
                                        v-for="port in router.service_ports"
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
                                                @click="deletePort(port.id)"
                                            >
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </td>
                                    </tr>
                                    <tr v-if="!router.service_ports?.length">
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
                </Card>

                <div class="mt-4">
                    <ConnectionManager
                        :device="router"
                        device-type="Modules\ActiveDevice\Models\Router"
                        :connections="router.source_connections"
                        :incoming-connections="router.destination_connections"
                        :available-devices="availableDevices"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
