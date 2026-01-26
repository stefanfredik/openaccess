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
import { Plus, Pencil, Trash2, Eye } from 'lucide-vue-next';
import { index as odpIndex, create as odpCreate, edit as odpEdit, show as odpShow, destroy as odpDestroy } from '@/routes/passive-device/odp';

defineProps<{
    odps: {
        data: Array<any>;
    };
}>();
</script>

<template>
    <Head title="ODPs" />

    <AppLayout :breadcrumbs="[{ title: 'ODPs', href: odpIndex().url }]">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">ODPs</h1>
                    <p class="text-muted-foreground">Manage Optical Distribution Point (ODP) assets.</p>
                </div>
                <Button as-child>
                    <Link :href="odpCreate().url">
                        <Plus class="mr-2 h-4 w-4" />
                        Add ODP
                    </Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>ODPs</CardTitle>
                    <CardDescription>
                        List of all ODPs.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-[100px]">Code</TableHead>
                                <TableHead>Name</TableHead>
                                <TableHead>Area</TableHead>
                                <TableHead>Splitter Type</TableHead>
                                <TableHead>Ports (Used/Total)</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="odp in odps.data" :key="odp.id">
                                <TableCell class="font-medium">{{ odp.code || '-' }}</TableCell>
                                <TableCell>{{ odp.name }}</TableCell>
                                <TableCell>{{ odp.area?.name || '-' }}</TableCell>
                                <TableCell>{{ odp.splitter_type || '-' }}</TableCell>
                                <TableCell>{{ odp.used_port_count }} / {{ odp.port_count }}</TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <Button variant="ghost" size="icon" as-child title="View Detail">
                                            <Link :href="odpShow(odp.id).url">
                                                <Eye class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Button variant="ghost" size="icon" as-child title="Edit">
                                            <Link :href="odpEdit(odp.id).url">
                                                <Pencil class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Link
                                            :href="odpDestroy(odp.id).url"
                                            method="delete"
                                            as="button"
                                            class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-10 w-10 text-destructive hover:text-destructive"
                                            title="Delete"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Link>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="odps.data.length === 0">
                                <TableCell colspan="6" class="h-24 text-center">
                                    No ODPs found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
