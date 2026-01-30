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
    create as popCreate,
    destroy as popDestroy,
    edit as popEdit,
    index as popIndex,
    show as popShow,
} from '@/routes/pop';
import { Head, Link } from '@inertiajs/vue3';
import { MapPin, Plus } from 'lucide-vue-next';

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
        <div class="mb-10 flex flex-col gap-4 p-4 md:p-6">
            <div
                class="mb-6 flex flex-col justify-between gap-4 md:flex-row md:items-center"
            >
                <div class="flex flex-col gap-1">
                    <h1
                        class="text-3xl font-black tracking-tight text-foreground"
                    >
                        Data POP
                    </h1>
                    <p class="text-sm font-medium text-muted-foreground">
                        Kelola infrastruktur Point of Presence Anda.
                    </p>
                </div>
                <Button as-child>
                    <Link :href="popCreate().url">
                        <Plus class="mr-2 h-5 w-5" />
                        Tambah POP
                    </Link>
                </Button>
            </div>

            <Card class="overflow-hidden border-none shadow-sm">
                <CardHeader class="p-0">
                    <div class="border-b bg-muted/50 px-6 py-4">
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
                                    <TableHead class="w-[150px]"
                                        >Kode</TableHead
                                    >
                                    <TableHead class="min-w-[200px]"
                                        >Nama POP</TableHead
                                    >
                                    <TableHead>Area</TableHead>
                                    <TableHead>Power Source</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="w-[150px] text-right"
                                        >Aksi</TableHead
                                    >
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="pop in pops" :key="pop.id">
                                    <TableCell
                                        class="font-medium whitespace-nowrap"
                                    >
                                        {{ pop.code || '-' }}
                                    </TableCell>
                                    <TableCell
                                        class="font-bold whitespace-nowrap"
                                    >
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex h-9 w-9 items-center justify-center rounded-lg border border-red-100/50 bg-red-50 text-red-600 shadow-sm dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-400"
                                            >
                                                <MapPin class="h-4.5 w-4.5" />
                                            </div>
                                            <span>{{ pop.name }}</span>
                                        </div>
                                    </TableCell>
                                    <TableCell class="whitespace-nowrap">{{
                                        pop.area?.name || '-'
                                    }}</TableCell>
                                    <TableCell>
                                        <div class="flex flex-col">
                                            <span class="font-medium">{{
                                                pop.power_source
                                            }}</span>
                                            <span
                                                class="text-xs text-muted-foreground"
                                                >{{
                                                    pop.electrical_capacity
                                                }}
                                                VA</span
                                            >
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge
                                            :variant="getUiStatus(pop.status)"
                                            class="rounded-md px-3 py-0.5 shadow-sm"
                                        >
                                            {{ pop.status }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <ShowAction
                                                :href="
                                                    popShow({ pop: pop.id }).url
                                                "
                                                title="Detail POP"
                                            />
                                            <EditAction
                                                :href="
                                                    popEdit({ pop: pop.id }).url
                                                "
                                                title="Ubah POP"
                                            />
                                            <DeleteAction
                                                :href="
                                                    popDestroy({ pop: pop.id })
                                                        .url
                                                "
                                            />
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="pops.length === 0">
                                    <TableCell
                                        colspan="6"
                                        class="h-24 text-center text-muted-foreground"
                                    >
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
