<script setup lang="ts">
import InventoryHeader from '@/../../Modules/ActiveDevice/resources/assets/js/Components/InventoryHeader.vue';
import DeleteAction from '@/components/DeleteAction.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import { Eye, Pencil } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const props = defineProps<{
    cpes: {
        data: Array<any>;
        links: Array<any>;
    };
    areas: any[];
    filters: {
        search?: string;
        area_id?: string;
    };
}>();

const searchQuery = ref(props.filters.search || '');
const areaId = ref(props.filters.area_id || 'all');

const updateFilters = debounce(() => {
    router.get(
        route('cpe.index'),
        {
            search: searchQuery.value,
            area_id: areaId.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}, 300);

watch([searchQuery, areaId], () => {
    updateFilters();
});
</script>

<template>
    <Head title="CPEs" />

    <AppLayout :breadcrumbs="[{ title: 'CPEs', href: route('cpe.index') }]">
        <div class="flex flex-col gap-6 p-4 md:p-8">
            <InventoryHeader
                title="CPE Inventory"
                description="Kelola inventori perangkat Customer Premises Equipment (CPE)."
                search-placeholder="Cari IP, Port, atau Nama..."
                add-button-text="Tambah CPE"
                :add-route="route('cpe.create')"
                v-model="searchQuery"
                v-model:area-id="areaId"
                :areas="areas"
            />

            <Card>
                <CardHeader>
                    <CardTitle>CPEs</CardTitle>
                    <CardDescription>
                        List of all registered CPEs.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-[120px]">Code</TableHead>
                                <TableHead>Name</TableHead>
                                <TableHead>Area</TableHead>
                                <TableHead>Type</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right"
                                    >Actions</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="cpe in cpes.data" :key="cpe.id">
                                <TableCell class="font-medium">{{
                                    cpe.code
                                }}</TableCell>
                                <TableCell>{{ cpe.name }}</TableCell>
                                <TableCell>{{
                                    cpe.area?.name || '-'
                                }}</TableCell>
                                <TableCell>{{ cpe.type || '-' }}</TableCell>
                                <TableCell>
                                    <Badge
                                        :variant="
                                            cpe.status === 'Active'
                                                ? 'default'
                                                : 'destructive'
                                        "
                                    >
                                        {{ cpe.status }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            as-child
                                            title="View Detail"
                                        >
                                            <Link
                                                :href="
                                                    route('cpe.show', cpe.id)
                                                "
                                            >
                                                <Eye class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            as-child
                                            title="Edit"
                                        >
                                            <Link
                                                :href="
                                                    route('cpe.edit', cpe.id)
                                                "
                                            >
                                                <Pencil class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <DeleteAction
                                            :href="route('cpe.destroy', cpe.id)"
                                        />
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="cpes.data.length === 0">
                                <TableCell colspan="6" class="h-24 text-center">
                                    No CPEs found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
