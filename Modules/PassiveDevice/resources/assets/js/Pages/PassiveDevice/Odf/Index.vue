<script setup lang="ts">
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
import {
    create as odfCreate,
    destroy as odfDestroy,
    edit as odfEdit,
    index as odfIndex,
    show as odfShow,
} from '@/routes/passive-device/odf';
import { Head, Link } from '@inertiajs/vue3';
import { Eye, Pencil, Plus, Trash2 } from 'lucide-vue-next';

defineProps<{
    odfs: {
        data: Array<any>;
        links: Array<any>;
    };
}>();
</script>

<template>
    <Head title="ODFs" />

    <AppLayout :breadcrumbs="[{ title: 'ODFs', href: odfIndex().url }]">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">ODFs</h1>
                    <p class="text-muted-foreground">
                        Manage Optical Distribution Frame (ODF) infrastructure.
                    </p>
                </div>
                <Button as-child>
                    <Link :href="odfCreate().url">
                        <Plus class="mr-2 h-4 w-4" />
                        Add ODF
                    </Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>ODFs</CardTitle>
                    <CardDescription>
                        List of all ODF devices.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-[100px]">Code</TableHead>
                                <TableHead>Name</TableHead>
                                <TableHead>Area</TableHead>
                                <TableHead>Ports (Used/Total)</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right"
                                    >Actions</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="odf in odfs.data" :key="odf.id">
                                <TableCell class="font-medium">{{
                                    odf.code || '-'
                                }}</TableCell>
                                <TableCell>{{ odf.name }}</TableCell>
                                <TableCell>{{
                                    odf.area?.name || '-'
                                }}</TableCell>
                                <TableCell>
                                    {{ odf.used_port_count }} /
                                    {{ odf.port_count }}
                                </TableCell>
                                <TableCell>
                                    <Badge
                                        :variant="
                                            odf.is_active
                                                ? 'default'
                                                : 'destructive'
                                        "
                                    >
                                        {{
                                            odf.is_active
                                                ? 'Active'
                                                : 'Inactive'
                                        }}
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
                                            <Link :href="odfShow(odf.id).url">
                                                <Eye class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            as-child
                                            title="Edit"
                                        >
                                            <Link :href="odfEdit(odf.id).url">
                                                <Pencil class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Link
                                            :href="odfDestroy(odf.id).url"
                                            method="delete"
                                            as="button"
                                            class="inline-flex h-10 w-10 items-center justify-center rounded-md text-sm font-medium whitespace-nowrap text-destructive ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground hover:text-destructive focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                                            title="Delete"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Link>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="odfs.data.length === 0">
                                <TableCell colspan="6" class="h-24 text-center">
                                    No ODFs found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
