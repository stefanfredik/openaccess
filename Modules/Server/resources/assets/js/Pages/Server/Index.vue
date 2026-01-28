<script setup lang="ts">
import DeleteAction from '@/components/DeleteAction.vue';
import EditAction from '@/components/EditAction.vue';
import SearchFilter from '@/components/SearchFilter.vue';
import ShowAction from '@/components/ShowAction.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { MapPin, Plus, Server as ServerIcon } from 'lucide-vue-next';
// import { index as serverIndex, create as serverCreate, edit as serverEdit, show as serverShow, destroy as serverDestroy } from '@/routes/server';

defineProps<{
    servers: Array<any>;
    filters: any;
}>();

const statusOptions = [
    { label: 'Active', value: 'Active' },
    { label: 'Inactive', value: 'Inactive' },
    { label: 'Planned', value: 'Planned' },
];

const functionOptions = [
    { label: 'Server', value: 'Server' },
    { label: 'OLT', value: 'OLT' },
    { label: 'Core Network', value: 'Core Network' },
    { label: 'NOC', value: 'NOC' },
];

const getUiStatus = (status: string) => {
    switch (status) {
        case 'Active':
            return 'default';
        case 'Inactive':
            return 'destructive';
        case 'Planned':
            return 'secondary';
        default:
            return 'outline';
    }
};

const getFunctionBadge = (func: string) => {
    switch (func) {
        case 'OLT':
            return 'secondary';
        case 'Core Network':
            return 'default';
        case 'NOC':
            return 'destructive';
        default:
            return 'outline';
    }
};
</script>

<template>
    <Head title="Data Server" />

    <AppLayout
        :breadcrumbs="[{ title: 'Server', href: route('server.index') }]"
    >
        <div class="mb-10 flex flex-col gap-4 p-4 md:p-6">
            <div
                class="mb-6 flex flex-col justify-between gap-4 md:flex-row md:items-center"
            >
                <div class="flex flex-col gap-1">
                    <h1
                        class="text-3xl font-black tracking-tight text-foreground"
                    >
                        Data Server & Core
                    </h1>
                    <p class="text-sm font-medium text-muted-foreground">
                        Kelola infrastruktur server, OLT, dan core network.
                    </p>
                </div>
                <Button as-child>
                    <Link :href="route('server.create')">
                        <Plus class="mr-2 h-5 w-5" />
                        Tambah Server
                    </Link>
                </Button>
            </div>

            <Card class="overflow-hidden border-none shadow-sm">
                <CardHeader class="p-0">
                    <div class="border-b bg-slate-50/50 px-6 py-4">
                        <SearchFilter
                            :route="route('server.index')"
                            :filters="filters"
                            placeholder="Cari Server..."
                            show-type-filter
                            type-label="Status"
                            :type-options="statusOptions"
                        />
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead class="w-[150px]"
                                        >Kode</TableHead
                                    >
                                    <TableHead class="min-w-[200px]"
                                        >Nama Perangkat</TableHead
                                    >
                                    <TableHead>Fungsi</TableHead>
                                    <TableHead>Lokasi</TableHead>
                                    <TableHead>Area / POP</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="w-[150px] text-right"
                                        >Aksi</TableHead
                                    >
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="server in servers"
                                    :key="server.id"
                                >
                                    <TableCell
                                        class="font-medium whitespace-nowrap"
                                    >
                                        <div class="flex items-center gap-2">
                                            <ServerIcon
                                                class="h-4 w-4 text-primary/70"
                                            />
                                            {{ server.code }}
                                        </div>
                                    </TableCell>
                                    <TableCell
                                        class="font-bold whitespace-nowrap"
                                        >{{ server.name }}</TableCell
                                    >
                                    <TableCell>
                                        <Badge
                                            :variant="
                                                getFunctionBadge(
                                                    server.function,
                                                )
                                            "
                                            class="whitespace-nowrap"
                                        >
                                            {{ server.function }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div
                                            class="flex flex-col gap-0.5 text-xs"
                                        >
                                            <span class="font-medium">{{
                                                server.building || '-'
                                            }}</span>
                                            <span
                                                v-if="server.floor"
                                                class="text-muted-foreground"
                                                >Lantai {{ server.floor }}</span
                                            >
                                            <span
                                                v-if="server.area_location"
                                                class="text-muted-foreground"
                                                >{{
                                                    server.area_location
                                                }}</span
                                            >
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div
                                            class="flex flex-col gap-0.5 text-xs"
                                        >
                                            <div
                                                class="flex items-center gap-1 font-medium"
                                            >
                                                <MapPin
                                                    class="h-3 w-3 text-muted-foreground"
                                                />
                                                {{ server.area?.name || '-' }}
                                            </div>
                                            <div
                                                class="pl-4 text-muted-foreground"
                                                v-if="server.pop"
                                            >
                                                {{ server.pop?.name }}
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge
                                            :variant="
                                                getUiStatus(server.status)
                                            "
                                            class="rounded-md px-3 py-0.5 shadow-sm"
                                        >
                                            {{ server.status }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <ShowAction
                                                :href="
                                                    route(
                                                        'server.show',
                                                        server.id,
                                                    )
                                                "
                                                title="Detail Server"
                                            />
                                            <EditAction
                                                :href="
                                                    route(
                                                        'server.edit',
                                                        server.id,
                                                    )
                                                "
                                                title="Ubah Server"
                                            />
                                            <DeleteAction
                                                :href="
                                                    route(
                                                        'server.destroy',
                                                        server.id,
                                                    )
                                                "
                                            />
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="servers.length === 0">
                                    <TableCell
                                        colspan="7"
                                        class="h-24 text-center text-muted-foreground"
                                    >
                                        Tidak ada data server ditemukan.
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
