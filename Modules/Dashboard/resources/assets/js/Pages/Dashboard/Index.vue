<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import StatsCard from '../../Components/StatsCard.vue';
import { Building2, MapPin, Server, Users } from 'lucide-vue-next';

defineProps<{
    stats: {
        companies: number;
        areas: number;
        sites: number;
        users: number;
    };
}>();
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '#' }]">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <h1 class="text-2xl font-bold tracking-tight">Dashboard</h1>
            
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <StatsCard 
                    title="Total Companies" 
                    :value="stats.companies" 
                    :icon="Building2"
                    description="Registered companies"
                />
                <StatsCard 
                    title="Areas" 
                    :value="stats.areas" 
                    :icon="MapPin"
                    description="Infrastructure areas"
                />
                <StatsCard 
                    title="Sites" 
                    :value="stats.sites" 
                    :icon="Server"
                    description="Active sites"
                />
                <StatsCard 
                    title="Users" 
                    :value="stats.users" 
                    :icon="Users"
                    description="System users"
                />
            </div>

            <!-- Future: Activity Feed or Map -->
        </div>
    </AppLayout>
</template>
