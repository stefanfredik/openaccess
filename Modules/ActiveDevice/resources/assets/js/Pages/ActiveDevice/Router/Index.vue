<script setup lang="ts">
import DeleteAction from '@/components/DeleteAction.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';

import DeviceDetailPreview from '@/../../Modules/ActiveDevice/resources/assets/js/Components/DeviceDetailPreview.vue';
import DeviceStatusBadge from '@/../../Modules/ActiveDevice/resources/assets/js/Components/DeviceStatusBadge.vue';
import InventoryHeader from '@/../../Modules/ActiveDevice/resources/assets/js/Components/InventoryHeader.vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { Head, Link, router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import { Eye, FileText, MoreVertical, Settings, Trash } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    routers: {
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
const selectedRouterId = ref<number | null>(null);
const isDrawerOpen = ref(false);

const updateFilters = debounce(() => {
    router.get(
        route('active-device.router.index'),
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

const selectedRouter = computed(() => {
    return (
        props.routers.data.find((r: any) => r.id === selectedRouterId.value) ||
        null
    );
});

const openDrawer = (router: any) => {
    console.log('Opening drawer for:', router.name);
    selectedRouterId.value = router.id;
    isDrawerOpen.value = true;
};
</script>

<template>
    <Head title="Routers" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Inventory', href: '#' },
            { title: 'Routers', href: route('active-device.router.index') },
        ]"
    >
        <div class="flex flex-col gap-6 p-4 md:p-8">
            <InventoryHeader
                title="Router Inventory"
                description="Kelola inventori perangkat Router dan konfigurasi jaringan."
                search-placeholder="Cari IP, Serial Number, atau Nama..."
                add-button-text="Tambah Router"
                :add-route="route('active-device.router.create')"
                v-model="searchQuery"
                v-model:area-id="areaId"
                :areas="areas"
            />

            <Card
                class="overflow-hidden rounded-xl border-none bg-white shadow-sm"
            >
                <div
                    class="flex items-center justify-between border-b border-gray-50 bg-white p-6"
                >
                    <h2 class="text-lg font-bold text-slate-800">
                        Daftar Inventori Router
                    </h2>
                    <div class="flex space-x-2">
                        <Button variant="outline" size="sm" class="h-8 text-xs"
                            >Filter</Button
                        >
                        <Button variant="outline" size="sm" class="h-8 text-xs"
                            >Export</Button
                        >
                    </div>
                </div>
                <CardContent class="p-0">
                    <Table>
                        <TableHeader class="bg-gray-50/50">
                            <TableRow class="border-b hover:bg-transparent">
                                <TableHead
                                    class="px-6 py-4 text-xs font-semibold tracking-wider text-gray-500 uppercase"
                                    >Nama Perangkat</TableHead
                                >
                                <TableHead
                                    class="px-6 py-4 text-xs font-semibold tracking-wider text-gray-500 uppercase"
                                    >Area / POP</TableHead
                                >
                                <TableHead
                                    class="px-6 py-4 text-xs font-semibold tracking-wider text-gray-500 uppercase"
                                    >Ports</TableHead
                                >
                                <TableHead
                                    class="px-6 py-4 text-xs font-semibold tracking-wider text-gray-500 uppercase"
                                    >IP Address</TableHead
                                >
                                <TableHead
                                    class="px-6 py-4 text-xs font-semibold tracking-wider text-gray-500 uppercase"
                                    >Status</TableHead
                                >
                                <TableHead
                                    class="px-6 py-4 text-right text-xs font-semibold tracking-wider text-gray-500 uppercase"
                                    >Actions</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="item in routers.data"
                                :key="item.id"
                                @click="openDrawer(item)"
                                class="group cursor-pointer border-b border-gray-50 transition-colors hover:bg-blue-50/40"
                            >
                                <TableCell
                                    class="px-6 py-4 font-semibold text-slate-800"
                                >
                                    <div class="flex flex-col">
                                        <span>{{ item.name }}</span>
                                        <span
                                            class="font-mono text-[10px] text-muted-foreground"
                                        >
                                            {{ item.brand }} {{ item.model }}
                                            <span v-if="item.code"
                                                >({{ item.code }})</span
                                            >
                                        </span>
                                    </div>
                                </TableCell>
                                <TableCell
                                    class="px-6 py-4 text-sm text-gray-600"
                                >
                                    <div class="flex flex-col">
                                        <span>{{
                                            item.area?.name || '-'
                                        }}</span>
                                        <span
                                            class="text-[10px] text-muted-foreground"
                                            >{{ item.pop?.name || '-' }}</span
                                        >
                                    </div>
                                </TableCell>
                                <TableCell class="px-6 py-4 text-sm">
                                    {{ item.port_count }} Ports
                                </TableCell>
                                <TableCell
                                    class="px-6 py-4 font-mono text-xs text-blue-600"
                                    >{{ item.ip_address || '-' }}</TableCell
                                >
                                <TableCell class="px-6 py-4">
                                    <DeviceStatusBadge
                                        :status="
                                            item.is_active
                                                ? 'Active'
                                                : 'Inactive'
                                        "
                                    />
                                </TableCell>
                                <TableCell
                                    class="px-6 py-4 text-right"
                                    @click.stop
                                >
                                    <div
                                        class="flex items-center justify-end gap-1"
                                    >
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8 text-blue-600 opacity-60 transition-opacity hover:bg-blue-50 hover:opacity-100"
                                            @click="openDrawer(item)"
                                            title="Pratinjau Cepat"
                                        >
                                            <Eye class="h-4 w-4" />
                                        </Button>

                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    class="h-8 w-8 p-0 opacity-60 transition-opacity hover:opacity-100"
                                                >
                                                    <MoreVertical
                                                        class="h-4 w-4"
                                                    />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent
                                                align="end"
                                                class="w-48"
                                            >
                                                <DropdownMenuItem
                                                    as-child
                                                    class="cursor-pointer"
                                                >
                                                    <Link
                                                        :href="
                                                            route(
                                                                'active-device.router.show',
                                                                item.id,
                                                            )
                                                        "
                                                    >
                                                        <FileText
                                                            class="mr-2 h-4 w-4"
                                                        />
                                                        Detail Lengkap
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuItem
                                                    as-child
                                                    class="cursor-pointer"
                                                >
                                                    <Link
                                                        :href="
                                                            route(
                                                                'active-device.router.edit',
                                                                item.id,
                                                            )
                                                        "
                                                    >
                                                        <Settings
                                                            class="mr-2 h-4 w-4"
                                                        />
                                                        Edit Router
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <div @click.stop>
                                                    <DeleteAction
                                                        :href="
                                                            route(
                                                                'active-device.router.destroy',
                                                                item.id,
                                                            )
                                                        "
                                                        title="Hapus Router"
                                                        class="h-auto w-full justify-start px-2 py-1.5 font-normal text-red-600 transition-colors hover:bg-red-50 hover:text-red-700"
                                                    >
                                                        <template #trigger>
                                                            <div
                                                                class="flex items-center"
                                                            >
                                                                <Trash
                                                                    class="mr-2 h-4 w-4"
                                                                />
                                                                Hapus Router
                                                            </div>
                                                        </template>
                                                    </DeleteAction>
                                                </div>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="routers.data.length === 0">
                                <TableCell
                                    colspan="6"
                                    class="h-32 text-center text-muted-foreground italic"
                                >
                                    No Routers found in inventory.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <!-- Detail Drawer (Sheet) -->
            <Sheet :open="isDrawerOpen" @update:open="isDrawerOpen = $event">
                <SheetTrigger class="hidden" />
                <SheetContent
                    class="flex h-full flex-col border-none p-0 shadow-2xl sm:max-w-[500px]"
                >
                    <SheetHeader class="sr-only">
                        <SheetTitle
                            >Detail Router:
                            {{ selectedRouter?.name }}</SheetTitle
                        >
                        <SheetDescription
                            >Koneksi, interface, dan layanan aktif pada
                            perangkat ini.</SheetDescription
                        >
                    </SheetHeader>
                    <DeviceDetailPreview
                        v-if="selectedRouter"
                        :device="selectedRouter"
                    />
                </SheetContent>
            </Sheet>
        </div>
    </AppLayout>
</template>
