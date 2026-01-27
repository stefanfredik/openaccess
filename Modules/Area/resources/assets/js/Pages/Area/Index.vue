<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Plus } from 'lucide-vue-next';
import SearchFilter from '@/components/SearchFilter.vue';
import ShowAction from '@/components/ShowAction.vue';
import EditAction from '@/components/EditAction.vue';
import DeleteAction from '@/components/DeleteAction.vue';
import { MapPin } from 'lucide-vue-next';
import { index as areaIndex, create as areaCreate, edit as areaEdit, destroy as areaDestroy, show as areaShow } from '@/routes/area';

defineProps<{
    areas: Array<any>;
    filters: any;
}>();

const typeOptions = [
    { label: 'Region', value: 'region' },
    { label: 'Area', value: 'area' },
    { label: 'Sub-Area', value: 'subarea' },
    { label: 'POP Location', value: 'pop_location' },
];
</script>

<template>
    <Head title="Wilayah Jaringan" />

    <AppLayout :breadcrumbs="[{ title: 'Wilayah', href: areaIndex().url }]">
        <div class="flex flex-col gap-4 p-4 md:p-6 mb-10">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div class="flex flex-col gap-1">
                    <h1 class="text-3xl font-black tracking-tight text-foreground">Wilayah</h1>
                    <p class="text-muted-foreground text-sm font-medium">Wilayah Infrastruktur</p>
                </div>
                <Button as-child >
                    <Link :href="areaCreate().url">
                        <Plus class="mr-2 h-5 w-5" />
                        Tambah Wilayah
                    </Link>
                </Button>
            </div>

            <Card class="border-none shadow-sm overflow-hidden">
                <CardHeader class="p-0">
                    <div class="px-6 py-4 bg-slate-50/50 border-b">
                        <SearchFilter
                            :route="areaIndex().url"
                            :filters="filters"
                            placeholder="Cari"
                            show-type-filter
                            :type-options="typeOptions"
                        />
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead class="w-[150px]">Kode</TableHead>
                                    <TableHead class="min-w-[200px]">Nama Wilayah</TableHead>
                                    <TableHead class="w-[150px]">Tipe</TableHead>
                                    <TableHead>Keterangan</TableHead>
                                    <TableHead class="text-right w-[150px]">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="area in areas" :key="area.id">
                                    <TableCell class="font-medium whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <MapPin class="h-4 w-4 text-primary/70" />
                                            {{ area.code || '-' }}
                                        </div>
                                    </TableCell>
                                    <TableCell class="font-bold whitespace-nowrap">{{ area.name }}</TableCell>
                                    <TableCell>
                                        <Badge variant="outline" class="capitalize whitespace-nowrap px-3 py-0.5 rounded-md">
                                            {{ area.type.replace('_', ' ') }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="truncate max-w-[300px] text-muted-foreground">{{ area.description || '-' }}</TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <ShowAction :href="areaShow({ area: area.id }).url" title="Detail Wilayah" />
                                            <EditAction :href="areaEdit({ area: area.id }).url" title="Ubah Wilayah" />
                                            <DeleteAction :href="areaDestroy({ area: area.id }).url" />
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="areas.length === 0">
                                    <TableCell colspan="5" class="h-24 text-center">
                                        No areas found.
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
