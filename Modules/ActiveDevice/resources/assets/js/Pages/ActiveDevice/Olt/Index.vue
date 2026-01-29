<script setup lang="ts">
import DeleteAction from '@/components/DeleteAction.vue';
import EditAction from '@/components/EditAction.vue';
import ShowAction from '@/components/ShowAction.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
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
import { Eye, Plus, Search } from 'lucide-vue-next';
import { ref } from 'vue';
import OltDetailPreview from '../../../Components/OltDetailPreview.vue';

const props = defineProps<{
    olts: {
        data: Array<any>;
    };
}>();

const selectedOlt = ref<any>(null);
const isDrawerOpen = ref(false);

const openDrawer = (olt: any) => {
    console.log('Opening drawer for:', olt.name);
    selectedOlt.value = olt;
    isDrawerOpen.value = true;
};

const getPortAbbreviation = (name: string) => {
    if (!name) return 'P';
    const match = name.match(/[A-Za-z]/);
    const numMatch = name.match(/\d+/);
    return (
        (match ? match[0].toUpperCase() : 'P') + (numMatch ? numMatch[0] : '')
    );
};
</script>

<template>
    <Head title="OLT Inventory" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Inventory', href: '#' },
            { title: 'OLT', href: route('active-device.olt.index') },
        ]"
    >
        <div class="flex flex-col gap-6 p-4 md:p-8">
            <!-- Header section -->
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <h1
                        class="font-inter text-2xl font-bold tracking-tight text-slate-900"
                    >
                        OLT Inventory
                    </h1>
                    <p class="text-sm text-muted-foreground italic">
                        Kelola inventori perangkat OLT dan jalur fiber.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="relative hidden sm:block">
                        <span
                            class="absolute inset-y-0 left-0 flex items-center pl-3"
                        >
                            <Search class="h-4 w-4 text-gray-400" />
                        </span>
                        <input
                            type="text"
                            class="block w-full rounded-lg border border-gray-200 bg-gray-50 py-2 pr-3 pl-10 text-sm transition focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            placeholder="Cari IP, Port, atau Nama..."
                        />
                    </div>
                    <Button
                        as-child
                        class="bg-blue-600 shadow-sm transition-all duration-200 hover:bg-blue-700"
                    >
                        <Link :href="route('active-device.olt.create')">
                            <Plus class="mr-2 h-4 w-4" />
                            Tambah OLT
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Table section -->
            <Card
                class="overflow-hidden rounded-xl border-none bg-white shadow-sm"
            >
                <div
                    class="flex items-center justify-between border-b border-gray-50 bg-white p-6"
                >
                    <h2 class="text-lg font-bold text-slate-800">
                        Daftar Inventori OLT
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
                                    >Brand / Model</TableHead
                                >
                                <TableHead
                                    class="px-6 py-4 text-xs font-semibold tracking-wider text-gray-500 uppercase"
                                    >IP Address</TableHead
                                >
                                <TableHead
                                    class="px-6 py-4 text-xs font-semibold tracking-wider text-gray-500 uppercase"
                                    >Port Aktif</TableHead
                                >
                                <TableHead
                                    class="px-6 py-4 text-xs font-semibold tracking-wider text-gray-500 uppercase"
                                    >Status</TableHead
                                >
                                <TableHead
                                    class="px-6 py-4 text-right text-xs font-semibold tracking-wider text-gray-500 uppercase"
                                    >Aksi</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="olt in olts.data"
                                :key="olt.id"
                                @click="openDrawer(olt)"
                                class="group cursor-pointer border-b border-gray-50 transition-colors hover:bg-blue-50/40"
                            >
                                <TableCell
                                    class="px-6 py-4 font-semibold text-slate-800"
                                >
                                    <div class="flex flex-col">
                                        <span>{{ olt.name }}</span>
                                        <span
                                            class="font-mono text-[10px] text-muted-foreground"
                                            >{{ olt.code }}</span
                                        >
                                    </div>
                                </TableCell>
                                <TableCell
                                    class="px-6 py-4 font-medium text-gray-600"
                                >
                                    {{ olt.brand }} {{ olt.model }}
                                </TableCell>
                                <TableCell
                                    class="px-6 py-4 font-mono text-xs text-blue-600"
                                >
                                    {{ olt.ip_address }}
                                </TableCell>
                                <TableCell class="px-6 py-4">
                                    <div class="flex -space-x-1">
                                        <template
                                            v-for="(
                                                port, idx
                                            ) in olt.service_ports?.slice(0, 3)"
                                            :key="port.id"
                                        >
                                            <span
                                                class="flex h-7 w-7 items-center justify-center rounded border-2 border-white bg-green-50 text-[9px] font-bold text-green-700 shadow-sm"
                                                :title="port.name"
                                            >
                                                {{
                                                    getPortAbbreviation(
                                                        port.name,
                                                    )
                                                }}
                                            </span>
                                        </template>
                                        <span
                                            v-if="olt.service_ports?.length > 3"
                                            class="flex h-7 w-7 items-center justify-center rounded border-2 border-white bg-gray-100 text-[9px] font-bold text-gray-400 shadow-sm"
                                        >
                                            +{{ olt.service_ports.length - 3 }}
                                        </span>
                                        <span
                                            v-if="!olt.service_ports?.length"
                                            class="text-xs text-muted-foreground italic"
                                            >None</span
                                        >
                                    </div>
                                </TableCell>
                                <TableCell class="px-6 py-4">
                                    <Badge
                                        :variant="
                                            olt.status === 'Active'
                                                ? 'default'
                                                : olt.status === 'Planned'
                                                  ? 'secondary'
                                                  : 'destructive'
                                        "
                                        class="rounded-md px-2 py-0.5 text-[10px] font-bold tracking-tight"
                                    >
                                        {{ olt.status }}
                                    </Badge>
                                </TableCell>
                                <TableCell
                                    class="px-6 py-4 text-right"
                                    @click.stop
                                >
                                    <div
                                        class="flex justify-end gap-2 opacity-0 transition-opacity group-hover:opacity-100"
                                    >
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            @click.stop="openDrawer(olt)"
                                            title="Quick View"
                                        >
                                            <Eye
                                                class="h-4 w-4 text-blue-600"
                                            />
                                        </Button>
                                        <ShowAction
                                            :href="
                                                route(
                                                    'active-device.olt.show',
                                                    olt.id,
                                                )
                                            "
                                            title="Full Detail"
                                        />
                                        <EditAction
                                            :href="
                                                route(
                                                    'active-device.olt.edit',
                                                    olt.id,
                                                )
                                            "
                                            title="Edit"
                                        />
                                        <DeleteAction
                                            :href="
                                                route(
                                                    'active-device.olt.destroy',
                                                    olt.id,
                                                )
                                            "
                                        />
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="olts.data.length === 0">
                                <TableCell
                                    colspan="6"
                                    class="h-32 text-center text-muted-foreground italic"
                                >
                                    No OLTs found in inventory.
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
                            >Detail OLT: {{ selectedOlt?.name }}</SheetTitle
                        >
                        <SheetDescription
                            >Koneksi, interface, dan layanan aktif pada
                            perangkat ini.</SheetDescription
                        >
                    </SheetHeader>
                    <OltDetailPreview v-if="selectedOlt" :olt="selectedOlt" />
                </SheetContent>
            </Sheet>
        </div>
    </AppLayout>
</template>

<style scoped>
.font-inter {
    font-family: 'Inter', sans-serif;
}
</style>
