<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import StatsCard from '../../Components/StatsCard.vue';
import { Building2, MapPin, Server, Users } from 'lucide-vue-next';

defineProps<{
    stats: any;
    isSuperAdmin: boolean;
}>();
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '#' }]">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <h1 class="text-2xl font-bold tracking-tight">
                {{ isSuperAdmin ? 'Super Admin Dashboard' : 'ISP Overview' }}
            </h1>
            
            <div v-if="isSuperAdmin" class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <StatsCard 
                    title="Total Companies" 
                    :value="stats.companies" 
                    :icon="Building2"
                    description="Registered entities"
                />
                <StatsCard 
                    title="System Users" 
                    :value="stats.users" 
                    :icon="Users"
                    description="Total global accounts"
                />
            </div>

            <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <StatsCard 
                    title="Coverage Areas" 
                    :value="stats.areas" 
                    :icon="MapPin"
                    description="Registered areas"
                />
                <StatsCard 
                    title="POPs" 
                    :value="stats.pops" 
                    :icon="MapPin"
                    description="Point of presence"
                />
                <StatsCard 
                    title="Sites" 
                    :value="stats.sites" 
                    :icon="Server"
                    description="Infrastructure sites"
                />
                <StatsCard 
                    title="Network Devices" 
                    :value="stats.active_devices" 
                    :icon="Server"
                    description="Total active hardware"
                />
            </div>

            <!-- Future: Activity Feed or Map -->
        </div>
    </AppLayout>
</template>
