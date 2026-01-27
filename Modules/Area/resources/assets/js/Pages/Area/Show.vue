<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Pencil } from 'lucide-vue-next';
import { index as areaIndex, show as areaShow, edit as areaEdit } from '@/routes/area';
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps<{
    area: any;
}>();

const provinceName = ref('-');
const regencyName = ref('-');
const districtName = ref('-');
const villageName = ref('-');

const fetchLocationNames = async () => {
    try {
        if (props.area.province_id) {
            const provRes = await axios.get(`https://www.emsifa.com/api-wilayah-indonesia/api/province/${props.area.province_id}.json`);
            provinceName.value = provRes.data.name;
        }

        if (props.area.regency_id) {
            const regRes = await axios.get(`https://www.emsifa.com/api-wilayah-indonesia/api/regency/${props.area.regency_id}.json`);
            regencyName.value = regRes.data.name;
        }

        if (props.area.district_id) {
            const distRes = await axios.get(`https://www.emsifa.com/api-wilayah-indonesia/api/district/${props.area.district_id}.json`);
            districtName.value = distRes.data.name;
        }

        if (props.area.village_id) {
            const villRes = await axios.get(`https://www.emsifa.com/api-wilayah-indonesia/api/village/${props.area.village_id}.json`);
            villageName.value = villRes.data.name;
        }
    } catch (error) {
        console.error('Error fetching location names:', error);
    }
};

onMounted(() => {
    fetchLocationNames();
});
</script>

<template>
    <Head :title="area.name" />

    <AppLayout :breadcrumbs="[
        { title: 'Wilayah', href: areaIndex().url },
        { title: area.name, href: areaShow({ area: area.id }).url }
    ]">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">{{ area.name }}</h1>
                    <p class="text-muted-foreground">{{ area.type }}</p>
                </div>
                <Button as-child>
                    <Link :href="areaEdit({ area: area.id }).url">
                        <Pencil class="mr-2 h-4 w-4" />
                        Edit
                    </Link>
                </Button>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Details</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <div class="text-muted-foreground">Code</div>
                            <div>{{ area.code || '-' }}</div>
                            
                            <div class="text-muted-foreground">Type</div>
                            <div><Badge variant="outline">{{ area.type }}</Badge></div>
                            
                            <div class="text-muted-foreground">Description</div>
                            <div>{{ area.description || '-' }}</div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Location</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <div class="text-muted-foreground">Province</div>
                            <div>{{ provinceName }}</div>
                            
                            <div class="text-muted-foreground">Regency</div>
                            <div>{{ regencyName }}</div>
                            
                            <div class="text-muted-foreground">District</div>
                            <div>{{ districtName }}</div>
                            
                            <div class="text-muted-foreground">Village</div>
                            <div>{{ villageName }}</div>
                        </div>
                    </CardContent>
                </Card>
            </div>
            
             <Card>
                <CardHeader>
                    <CardTitle>Statistics</CardTitle>
                    <CardDescription>Infrastructure stats in this area.</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="text-muted-foreground">Coming soon...</div>
                </CardContent>
             </Card>
        </div>
    </AppLayout>
</template>
