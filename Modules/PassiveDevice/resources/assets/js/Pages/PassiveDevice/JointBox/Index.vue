<script setup lang="ts">
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
// import { index as jointBoxIndex, create as jointBoxCreate, edit as jointBoxEdit, show as jointBoxShow, destroy as jointBoxDestroy } from '@/routes/passive-device/joint-box';

defineProps<{
    jointBoxes: {
        data: Array<any>;
    };
}>();
</script>

<template>
    <Head title="Joint Boxes" />

    <AppLayout
        :breadcrumbs="[
            {
                title: 'Joint Boxes',
                href: route('passive-device.joint-box.index'),
            },
        ]"
    >
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        Joint Boxes
                    </h1>
                    <p class="text-muted-foreground">
                        Manage fiber optic splice enclosures (Joint Boxes).
                    </p>
                </div>
                <Button as-child>
                    <Link :href="route('passive-device.joint-box.create')">
                        <Plus class="mr-2 h-4 w-4" />
                        Add Joint Box
                    </Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Joint Boxes</CardTitle>
                    <CardDescription>
                        List of all Joint Boxes.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-[100px]">Code</TableHead>
                                <TableHead>Name</TableHead>
                                <TableHead>Area</TableHead>
                                <TableHead>Capacity</TableHead>
                                <TableHead>Ports (In/Out)</TableHead>
                                <TableHead class="text-right"
                                    >Actions</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="box in jointBoxes.data"
                                :key="box.id"
                            >
                                <TableCell class="font-medium">{{
                                    box.code || '-'
                                }}</TableCell>
                                <TableCell>{{ box.name }}</TableCell>
                                <TableCell>{{
                                    box.area?.name || '-'
                                }}</TableCell>
                                <TableCell
                                    >{{ box.core_capacity }} Cores</TableCell
                                >
                                <TableCell
                                    >{{ box.input_count }} /
                                    {{ box.output_count }}</TableCell
                                >
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
                                                    route(
                                                        'passive-device.joint-box.show',
                                                        box.id,
                                                    )
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
                                                    route(
                                                        'passive-device.joint-box.edit',
                                                        box.id,
                                                    )
                                                "
                                            >
                                                <Pencil class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <DeleteAction
                                            :href="
                                                route(
                                                    'passive-device.joint-box.destroy',
                                                    box.id,
                                                )
                                            "
                                        />
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="jointBoxes.data.length === 0">
                                <TableCell colspan="6" class="h-24 text-center">
                                    No Joint Boxes found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
