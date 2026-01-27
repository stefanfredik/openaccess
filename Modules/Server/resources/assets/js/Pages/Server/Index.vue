<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
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
import { Badge } from '@/components/ui/badge';
import { Plus, Server as ServerIcon, MapPin } from 'lucide-vue-next';
import SearchFilter from '@/components/SearchFilter.vue';
import ShowAction from '@/components/ShowAction.vue';
import EditAction from '@/components/EditAction.vue';
import DeleteAction from '@/components/DeleteAction.vue';
import { index as serverIndex, create as serverCreate, edit as serverEdit, show as serverShow, destroy as serverDestroy } from '@/routes/server';

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

    <AppLayout :breadcrumbs="[{ title: 'Server', href: serverIndex().url }]">
        <div class="flex flex-col gap-4 p-4 md:p-6 mb-10">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div class="flex flex-col gap-1">
                    <h1 class="text-3xl font-black tracking-tight text-foreground">Data Server & Core</h1>
                    <p class="text-muted-foreground text-sm font-medium">Kelola infrastruktur server, OLT, dan core network.</p>
                </div>
                <Button as-child>
                    <Link :href="serverCreate().url">
                        <Plus class="mr-2 h-5 w-5" />
                        Tambah Server
                    </Link>
                </Button>
            </div>

            <Card class="border-none shadow-sm overflow-hidden">
                <CardHeader class="p-0">
                    <div class="px-6 py-4 bg-slate-50/50 border-b">
                        <SearchFilter
                            :route="serverIndex().url"
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
                                    <TableHead class="w-[150px]">Kode</TableHead>
                                    <TableHead class="min-w-[200px]">Nama Perangkat</TableHead>
                                    <TableHead>Fungsi</TableHead>
                                    <TableHead>Lokasi</TableHead>
                                    <TableHead>Area / POP</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="text-right w-[150px]">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="server in servers" :key="server.id">
                                    <TableCell class="font-medium whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <ServerIcon class="h-4 w-4 text-primary/70" />
                                            {{ server.code }}
                                        </div>
                                    </TableCell>
                                    <TableCell class="font-bold whitespace-nowrap">{{ server.name }}</TableCell>
                                    <TableCell>
                                        <Badge :variant="getFunctionBadge(server.function)" class="whitespace-nowrap">
                                            {{ server.function }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-xs flex flex-col gap-0.5">
                                            <span class="font-medium">{{ server.building || '-' }}</span>
                                            <span v-if="server.floor" class="text-muted-foreground">Lantai {{ server.floor }}</span>
                                            <span v-if="server.area_location" class="text-muted-foreground">{{ server.area_location }}</span>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-xs flex flex-col gap-0.5">
                                            <div class="font-medium flex items-center gap-1">
                                                <MapPin class="h-3 w-3 text-muted-foreground" />
                                                {{ server.area?.name || '-' }}
                                            </div>
                                            <div class="text-muted-foreground pl-4" v-if="server.pop">{{ server.pop?.name }}</div>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="getUiStatus(server.status)" class="px-3 py-0.5 rounded-md shadow-sm">
                                            {{ server.status }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <ShowAction :href="serverShow({ server: server.id }).url" title="Detail Server" />
                                            <EditAction :href="serverEdit({ server: server.id }).url" title="Ubah Server" />
                                            <DeleteAction :href="serverDestroy({ server: server.id }).url" />
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="servers.length === 0">
                                    <TableCell colspan="7" class="h-24 text-center text-muted-foreground">
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
