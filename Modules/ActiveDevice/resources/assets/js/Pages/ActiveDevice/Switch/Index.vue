<script setup lang="ts">
import InventoryHeader from '@/../../Modules/ActiveDevice/resources/assets/js/Components/InventoryHeader.vue';
import DeleteAction from '@/components/DeleteAction.vue';
import EditAction from '@/components/EditAction.vue';
import ShowAction from '@/components/ShowAction.vue';
import { Badge } from '@/components/ui/badge';
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
import { Head, router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import { ref, watch } from 'vue';

const props = defineProps<{
    switches: {
        data: Array<any>;
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
        route('active-device.switch.index'),
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
    <Head title="Switches" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Switches', href: route('active-device.switch.index') },
        ]"
    >
        <div class="flex flex-col gap-6 p-4 md:p-8">
            <InventoryHeader
                title="Switch Inventory"
                description="Kelola inventori perangkat Switch (L2/L3) dan distribusi jaringan."
                search-placeholder="Cari IP, Port, atau Nama..."
                add-button-text="Tambah Switch"
                :add-route="route('active-device.switch.create')"
                v-model="searchQuery"
                v-model:area-id="areaId"
                :areas="areas"
            />

            <Card>
                <CardHeader>
                    <CardTitle>Switches</CardTitle>
                    <CardDescription>
                        List of all registered Switches.
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
                                <TableHead>Ports</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right"
                                    >Actions</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="item in switches.data"
                                :key="item.id"
                            >
                                <TableCell class="font-medium">{{
                                    item.code
                                }}</TableCell>
                                <TableCell>{{ item.name }}</TableCell>
                                <TableCell>{{
                                    item.area?.name || '-'
                                }}</TableCell>
                                <TableCell>{{
                                    item.switch_type || '-'
                                }}</TableCell>
                                <TableCell>{{ item.port_count }}</TableCell>
                                <TableCell>
                                    <Badge
                                        :variant="
                                            item.is_active
                                                ? 'default'
                                                : 'destructive'
                                        "
                                    >
                                        {{
                                            item.is_active
                                                ? 'Active'
                                                : 'Inactive'
                                        }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <ShowAction
                                            :href="
                                                route(
                                                    'active-device.switch.show',
                                                    item.id,
                                                )
                                            "
                                            title="View Detail"
                                        />
                                        <EditAction
                                            :href="
                                                route(
                                                    'active-device.switch.edit',
                                                    item.id,
                                                )
                                            "
                                            title="Edit"
                                        />
                                        <DeleteAction
                                            :href="
                                                route(
                                                    'active-device.switch.destroy',
                                                    item.id,
                                                )
                                            "
                                        />
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="switches.data.length === 0">
                                <TableCell colspan="7" class="h-24 text-center">
                                    No Switches found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
