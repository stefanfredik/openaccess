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
import { index as popIndex, create as popCreate, edit as popEdit, show as popShow, destroy as popDestroy } from '@/routes/pop';

defineProps<{
    pops: Array<any>;
}>();

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
    <Head title="POPs" />

    <AppLayout :breadcrumbs="[{ title: 'POPs', href: popIndex().url }]">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">POPs</h1>
                    <p class="text-muted-foreground">Manage Point of Presence (POP) infrastructure.</p>
                </div>
                <Button as-child>
                    <Link :href="popCreate().url">
                        <Plus class="mr-2 h-4 w-4" />
                        Add POP
                    </Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>POPs</CardTitle>
                    <CardDescription>
                        List of all Point of Presence locations.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-[100px]">Code</TableHead>
                                <TableHead>Name</TableHead>
                                <TableHead>Area</TableHead>
                                <TableHead>Power Source</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="pop in pops" :key="pop.id">
                                <TableCell class="font-medium">{{ pop.code || '-' }}</TableCell>
                                <TableCell>{{ pop.name }}</TableCell>
                                <TableCell>{{ pop.area?.name || '-' }}</TableCell>
                                <TableCell>
                                    <div class="flex flex-col">
                                        <span>{{ pop.power_source }}</span>
                                        <span class="text-xs text-muted-foreground">{{ pop.electrical_capacity }} VA</span>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="getUiStatus(pop.status)">{{ pop.status }}</Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <Button variant="ghost" size="icon" as-child title="View Detail">
                                            <Link :href="popShow({ pop: pop.id }).url">
                                                <Eye class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Button variant="ghost" size="icon" as-child title="Edit">
                                            <Link :href="popEdit({ pop: pop.id }).url">
                                                <Pencil class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Link
                                            :href="popDestroy({ pop: pop.id }).url"
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
                            <TableRow v-if="pops.length === 0">
                                <TableCell colspan="6" class="h-24 text-center">
                                    No POPs found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
