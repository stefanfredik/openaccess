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
import { index as poleIndex, create as poleCreate, edit as poleEdit, show as poleShow, destroy as poleDestroy } from '@/routes/passive-device/pole';

defineProps<{
    poles: {
        data: Array<any>;
    };
}>();
</script>

<template>
    <Head title="Poles" />

    <AppLayout :breadcrumbs="[{ title: 'Poles', href: poleIndex().url }]">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Poles</h1>
                    <p class="text-muted-foreground">Manage Pole infrastructure assets.</p>
                </div>
                <Button as-child>
                    <Link :href="poleCreate().url">
                        <Plus class="mr-2 h-4 w-4" />
                        Add Pole
                    </Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Poles</CardTitle>
                    <CardDescription>
                        List of all Poles.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-[100px]">Code</TableHead>
                                <TableHead>Name</TableHead>
                                <TableHead>Area</TableHead>
                                <TableHead>Material / Ownership</TableHead>
                                <TableHead>Height</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="pole in poles.data" :key="pole.id">
                                <TableCell class="font-medium">{{ pole.code || '-' }}</TableCell>
                                <TableCell>{{ pole.name }}</TableCell>
                                <TableCell>{{ pole.area?.name || '-' }}</TableCell>
                                <TableCell>
                                    <div class="flex flex-col">
                                        <span>{{ pole.material }}</span>
                                        <span class="text-xs text-muted-foreground">{{ pole.ownership }}</span>
                                    </div>
                                </TableCell>
                                <TableCell>{{ pole.height }} m</TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <Button variant="ghost" size="icon" as-child title="View Detail">
                                            <Link :href="poleShow(pole.id).url">
                                                <Eye class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Button variant="ghost" size="icon" as-child title="Edit">
                                            <Link :href="poleEdit(pole.id).url">
                                                <Pencil class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Link
                                            :href="poleDestroy(pole.id).url"
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
                            <TableRow v-if="poles.data.length === 0">
                                <TableCell colspan="6" class="h-24 text-center">
                                    No Poles found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
