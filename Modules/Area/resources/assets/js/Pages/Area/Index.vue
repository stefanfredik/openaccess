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
import {
    create as areaCreate,
    destroy as areaDestroy,
    edit as areaEdit,
    index as areaIndex,
    show as areaShow,
} from '@/routes/area';
import { Head, Link } from '@inertiajs/vue3';
import { MapPin, Plus } from 'lucide-vue-next';

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
        <div class="mb-10 flex flex-col gap-4 p-4 md:p-6">
            <div
                class="mb-6 flex flex-col justify-between gap-4 md:flex-row md:items-center"
            >
                <div class="flex flex-col gap-1">
                    <h1
                        class="text-3xl font-black tracking-tight text-foreground"
                    >
                        Wilayah
                    </h1>
                    <p class="text-sm font-medium text-muted-foreground">
                        Wilayah Infrastruktur
                    </p>
                </div>
                <Button as-child>
                    <Link :href="areaCreate().url">
                        <Plus class="mr-2 h-5 w-5" />
                        Tambah Wilayah
                    </Link>
                </Button>
            </div>

            <Card class="overflow-hidden border-none shadow-sm">
                <CardHeader class="p-0">
                    <div class="border-b bg-muted/50 px-6 py-4">
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
                                    <TableHead class="w-[150px]"
                                        >Kode</TableHead
                                    >
                                    <TableHead class="min-w-[200px]"
                                        >Nama Wilayah</TableHead
                                    >
                                    <TableHead class="w-[150px]"
                                        >Tipe</TableHead
                                    >
                                    <TableHead>Keterangan</TableHead>
                                    <TableHead class="w-[150px] text-right"
                                        >Aksi</TableHead
                                    >
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="area in areas" :key="area.id">
                                    <TableCell
                                        class="font-medium whitespace-nowrap"
                                    >
                                        <div class="flex items-center gap-2">
                                            <MapPin
                                                class="h-4 w-4 text-primary/70"
                                            />
                                            {{ area.code || '-' }}
                                        </div>
                                    </TableCell>
                                    <TableCell
                                        class="font-bold whitespace-nowrap"
                                        >{{ area.name }}</TableCell
                                    >
                                    <TableCell>
                                        <Badge
                                            variant="outline"
                                            class="rounded-md px-3 py-0.5 whitespace-nowrap capitalize"
                                        >
                                            {{ area.type.replace('_', ' ') }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell
                                        class="max-w-[300px] truncate text-muted-foreground"
                                        >{{
                                            area.description || '-'
                                        }}</TableCell
                                    >
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <ShowAction
                                                :href="
                                                    areaShow({ area: area.id })
                                                        .url
                                                "
                                                title="Detail Wilayah"
                                            />
                                            <EditAction
                                                :href="
                                                    areaEdit({ area: area.id })
                                                        .url
                                                "
                                                title="Ubah Wilayah"
                                            />
                                            <DeleteAction
                                                :href="
                                                    areaDestroy({
                                                        area: area.id,
                                                    }).url
                                                "
                                            />
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="areas.length === 0">
                                    <TableCell
                                        colspan="5"
                                        class="h-24 text-center"
                                    >
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
