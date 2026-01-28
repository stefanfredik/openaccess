<script setup lang="ts">
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
import { Head, Link } from '@inertiajs/vue3';
import { Eye, Pencil, Plus } from 'lucide-vue-next';

defineProps<{
    cpes: {
        data: Array<any>;
        links: Array<any>;
    };
}>();
</script>

<template>
    <Head title="CPEs" />

    <AppLayout :breadcrumbs="[{ title: 'CPEs', href: route('cpe.index') }]">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">CPEs</h1>
                    <p class="text-muted-foreground">
                        Manage Customer Premises Equipment.
                    </p>
                </div>
                <Button as-child>
                    <Link :href="route('cpe.create')">
                        <Plus class="mr-2 h-4 w-4" />
                        Add CPE
                    </Link>
                </Button>
            </div>

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
