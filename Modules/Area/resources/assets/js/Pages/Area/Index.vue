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
import { Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { index as areaIndex, create as areaCreate, edit as areaEdit, destroy as areaDestroy } from '@/routes/area';

defineProps<{
    areas: Array<any>;
}>();
</script>

<template>
    <Head title="Infrastructure Areas" />

    <AppLayout :breadcrumbs="[{ title: 'Areas', href: areaIndex().url }]">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Infrastructure Areas</h1>
                    <p class="text-muted-foreground">Manage infrastructure areas.</p>
                </div>
                <Button as-child>
                    <Link :href="areaCreate().url">
                        <Plus class="mr-2 h-4 w-4" />
                        Add Area
                    </Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Areas</CardTitle>
                    <CardDescription>
                        List of all infrastructure areas.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-[100px]">Code</TableHead>
                                <TableHead>Name</TableHead>
                                <TableHead>Type</TableHead>
                                <TableHead>Description</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="area in areas" :key="area.id">
                                <TableCell class="font-medium">{{ area.code || '-' }}</TableCell>
                                <TableCell>{{ area.name }}</TableCell>
                                <TableCell>
                                    <Badge variant="outline" class="capitalize">
                                        {{ area.type.replace('_', ' ') }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="truncate max-w-[200px]">{{ area.description }}</TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <Button variant="ghost" size="icon" as-child>
                                            <Link :href="areaEdit({ area: area.id }).url">
                                                <Pencil class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Link
                                            :href="areaDestroy({ area: area.id }).url"
                                            method="delete"
                                            as="button"
                                            class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-10 w-10 text-destructive hover:text-destructive"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Link>
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
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
