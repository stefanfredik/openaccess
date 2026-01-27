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
import { Plus, MapPin } from 'lucide-vue-next';
import SearchFilter from '@/components/SearchFilter.vue';
import ShowAction from '@/components/ShowAction.vue';
import EditAction from '@/components/EditAction.vue';
import DeleteAction from '@/components/DeleteAction.vue';
import { index as popIndex, create as popCreate, edit as popEdit, show as popShow, destroy as popDestroy } from '@/routes/pop';

defineProps<{
    pops: Array<any>;
    filters: any;
}>();

const statusOptions = [
    { label: 'Active', value: 'Active' },
    { label: 'Inactive', value: 'Inactive' },
    { label: 'Planned', value: 'Planned' },
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
</script>

<template>
    <Head title="Data POP" />

    <AppLayout :breadcrumbs="[{ title: 'POP', href: popIndex().url }]">
        <div class="flex flex-col gap-4 p-4 md:p-6 mb-10">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div class="flex flex-col gap-1">
                    <h1 class="text-3xl font-black tracking-tight text-foreground">Data POP</h1>
                    <p class="text-muted-foreground text-sm font-medium">Kelola infrastruktur Point of Presence Anda.</p>
                </div>
                <Button as-child >
                    <Link :href="popCreate().url">
                        <Plus class="mr-2 h-5 w-5" />
                        Tambah POP
                    </Link>
                </Button>
            </div>

            <Card class="border-none shadow-sm overflow-hidden">
                <CardHeader class="p-0">
                    <div class="px-6 py-4 bg-slate-50/50 border-b">
                        <SearchFilter
                            :route="popIndex().url"
                            :filters="filters"
                            placeholder="Cari POP..."
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
                                    <TableHead class="min-w-[200px]">Nama POP</TableHead>
                                    <TableHead>Area</TableHead>
                                    <TableHead>Power Source</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="text-right w-[150px]">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="pop in pops" :key="pop.id">
                                    <TableCell class="font-medium whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <MapPin class="h-4 w-4 text-primary/70" />
                                            {{ pop.code || '-' }}
                                        </div>
                                    </TableCell>
                                    <TableCell class="font-bold whitespace-nowrap">{{ pop.name }}</TableCell>
                                    <TableCell class="whitespace-nowrap">{{ pop.area?.name || '-' }}</TableCell>
                                    <TableCell>
                                        <div class="flex flex-col">
                                            <span class="font-medium">{{ pop.power_source }}</span>
                                            <span class="text-xs text-muted-foreground">{{ pop.electrical_capacity }} VA</span>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="getUiStatus(pop.status)" class="px-3 py-0.5 rounded-md shadow-sm">
                                            {{ pop.status }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <ShowAction :href="popShow({ pop: pop.id }).url" title="Detail POP" />
                                            <EditAction :href="popEdit({ pop: pop.id }).url" title="Ubah POP" />
                                            <DeleteAction :href="popDestroy({ pop: pop.id }).url" />
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="pops.length === 0">
                                    <TableCell colspan="6" class="h-24 text-center text-muted-foreground">
                                        Tidak ada data POP ditemukan.
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
