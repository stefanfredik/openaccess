<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { 
    MapPin, Server, Network, HardDrive, Cpu, ArrowRight, 
    Database, Plus, List, Clock, CheckCircle2, AlertCircle,
    BarChart3, Settings2, ShieldCheck, Activity,
    Layers, Zap, Radio, Smartphone, Component, Trash2, Eye
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

interface Activity {
    id: number;
    user: string;
    action: string;
    time: string;
    type: 'passive' | 'infrastructure' | 'active';
}

const props = defineProps<{
    stats: {
        areas: number;
        pops: number;
        active_devices: {
            total: number;
            online: number;
            olt: number;
            ont: number;
            router: number;
            switch: number;
            ap: number;
        };
        passive_devices: {
            total: number;
            odp: number;
            odf: number;
            pole: number;
            tower: number;
            joint_box: number;
        };
    };
    recent_activities: Activity[];
}>();

const summaryCards = [
    { title: 'Total OLT', count: props.stats.active_devices.olt, icon: Database, color: 'text-indigo-600', bg: 'bg-indigo-50', link: '/active-devices/olt' },
    { title: 'Total ODP', count: props.stats.passive_devices.odp, icon: Layers, color: 'text-emerald-600', bg: 'bg-emerald-50', link: '/passive-devices/odp' },
    { title: 'Total Tiang', count: props.stats.passive_devices.pole, icon: Component, color: 'text-amber-600', bg: 'bg-amber-50', link: '/passive-devices/pole' },
    { title: 'Total Router', count: props.stats.active_devices.router, icon: Network, color: 'text-rose-600', bg: 'bg-rose-50', link: '/active-devices/router' },
    { title: 'Total Switch', count: props.stats.active_devices.switch, icon: Layers, color: 'text-cyan-600', bg: 'bg-cyan-50', link: '/active-devices/switch' },
    { title: 'Total POP', count: props.stats.pops, icon: MapPin, color: 'text-blue-600', bg: 'bg-blue-50', link: '/pops' },
];

const modules = [
    {
        title: 'Infrastruktur Dasar',
        description: 'Area, POP, dan Node Utama.',
        icon: MapPin,
        color: 'bg-indigo-500',
        lightColor: 'bg-indigo-50',
        iconColor: 'text-indigo-600',
        items: [
            { name: 'Wilayah Operasional', href: '/areas', desc: `${props.stats.areas} Area` },
            { name: 'Point of Presence', href: '/pops', desc: `${props.stats.pops} POP` },
            { name: 'Server & Host', href: '/servers', desc: 'Manajemen Node' },
        ]
    },
    {
        title: 'Perangkat Aktif',
        description: 'Hardware bertenaga.',
        icon: Cpu,
        color: 'bg-emerald-500',
        lightColor: 'bg-emerald-50',
        iconColor: 'text-emerald-600',
        items: [
            { name: 'OLT Management', href: '/active-devices/olt', desc: `${props.stats.active_devices.olt} Unit` },
            { name: 'Router', href: '/active-devices/router', desc: `${props.stats.active_devices.router} Unit` },
            { name: 'ONT/Laptop', href: '/active-devices/ont', desc: `${props.stats.active_devices.ont} Unit` },
        ]
    },
    {
        title: 'Perangkat Pasif',
        description: 'Jalur distribusi & FO.',
        icon: HardDrive,
        color: 'bg-amber-500',
        lightColor: 'bg-amber-50',
        iconColor: 'text-amber-600',
        items: [
            { name: 'ODP', href: '/passive-devices/odp', desc: `${props.stats.passive_devices.odp} Titik` },
            { name: 'Tiang', href: '/passive-devices/pole', desc: `${props.stats.passive_devices.pole} Batang` },
            { name: 'Joint Box', href: '/passive-devices/joint-box', desc: `${props.stats.passive_devices.joint_box} Titik` },
        ]
    }
];

const getActivityIcon = (type: string) => {
    switch (type) {
        case 'active': return Cpu;
        case 'passive': return HardDrive;
        default: return MapPin;
    }
};

const getActivityColor = (type: string) => {
    switch (type) {
        case 'active': return 'text-emerald-500 bg-emerald-50';
        case 'passive': return 'text-amber-500 bg-amber-50';
        default: return 'text-indigo-500 bg-indigo-50';
    }
};
</script>

<template>
    <Head title="Control Center - Pendataan" />

    <AppLayout :breadcrumbs="[{ title: 'Beranda', href: '/dashboard' }, { title: 'Pusat Pendataan', href: '#' }]">
        <div class="p-6 md:p-10 max-w-[1600px] mx-auto w-full space-y-10">
            
            <!-- Hero Header -->
            <div class="relative overflow-hidden rounded-[2.5rem] bg-slate-950 p-8 md:p-12 text-white shadow-2xl">
                <div class="absolute top-0 right-0 -mt-20 -mr-20 h-96 w-96 rounded-full bg-primary/20 blur-[100px]"></div>
                
                <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-8">
                    <div class="space-y-4 text-center md:text-left">
                        <Badge variant="outline" class="border-primary/50 text-primary-foreground bg-primary/10 px-4 py-1.5 text-xs font-bold uppercase tracking-widest backdrop-blur-md">
                           <Activity class="mr-2 h-3 w-3" /> Data Control Center
                        </Badge>
                        <h1 class="text-4xl md:text-5xl font-black tracking-tighter">Ringkasan Data <span class="text-primary">Aset Jaringan</span></h1>
                        <p class="text-slate-400 text-lg max-w-2xl leading-relaxed">
                            Pantau jumlah distribusi perangkat dan infrastruktur secara real-time.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Detailed Stats Cards -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
                <Link v-for="card in summaryCards" :key="card.title" :href="card.link" class="group">
                    <Card class="border-none bg-white shadow-[0_4px_20px_rgb(0,0,0,0.03)] hover:shadow-[0_15px_40px_rgb(0,0,0,0.08)] transition-all duration-300 rounded-[2rem] overflow-hidden">
                        <CardContent class="p-6 flex flex-col items-center text-center gap-4">
                            <div :class="[card.bg, card.color]" class="h-14 w-14 rounded-2xl flex items-center justify-center transition-transform group-hover:scale-110 duration-300">
                                <component :is="card.icon" class="h-7 w-7" />
                            </div>
                            <div>
                                <div class="text-3xl font-black tracking-tighter text-slate-900 leading-none mb-1">{{ card.count }}</div>
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ card.title }}</div>
                            </div>
                        </CardContent>
                    </Card>
                </Link>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- Left: Modules -->
                <div class="lg:col-span-8 space-y-8">
                    <div class="flex items-center justify-between px-2">
                        <h2 class="text-2xl font-black tracking-tight flex items-center gap-3">
                            <Settings2 class="h-6 w-6 text-primary" />
                            Manajemen Modul
                        </h2>
                    </div>

                    <div class="grid gap-6 md:grid-cols-1">
                        <Card v-for="module in modules" :key="module.title" 
                            class="group relative overflow-hidden border-none bg-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_20px_50px_rgb(0,0,0,0.1)] transition-all duration-500 rounded-[2rem]">
                            <CardContent class="p-0 flex flex-col md:flex-row">
                                <div :class="module.color" class="w-full md:w-3 z-10"></div>
                                <div class="p-8 flex-grow flex flex-col md:flex-row items-center gap-8">
                                    <div :class="module.lightColor" class="h-16 w-16 min-w-[4rem] rounded-2xl flex items-center justify-center transition-transform group-hover:scale-110 duration-500">
                                        <component :is="module.icon" :class="module.iconColor" class="h-8 w-8" />
                                    </div>
                                    <div class="flex-grow text-center md:text-left space-y-0.5">
                                        <h3 class="text-xl font-bold tracking-tight">{{ module.title }}</h3>
                                        <p class="text-muted-foreground text-xs font-medium">{{ module.description }}</p>
                                    </div>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 w-full md:w-auto">
                                        <Link v-for="item in module.items" :key="item.name" :href="item.href" class="group/item">
                                            <div class="bg-slate-50 hover:bg-white hover:shadow-lg border border-transparent p-4 rounded-xl transition-all duration-300 min-w-[140px]">
                                                <div class="text-[10px] font-bold text-slate-800 tracking-tight group-hover/item:text-primary transition-colors truncate">{{ item.name }}</div>
                                                <div class="text-[9px] text-slate-400 font-bold mt-0.5">{{ item.desc }}</div>
                                            </div>
                                        </Link>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <!-- Right: Information & Activity -->
                <div class="lg:col-span-4 space-y-8">
                    <Card class="border-none bg-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-[2rem] overflow-hidden">
                        <CardHeader class="pb-4">
                            <CardTitle class="text-xl font-black tracking-tight flex items-center gap-2">
                                <Clock class="h-5 w-5 text-primary" />
                                Aktivitas Terakhir
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="px-0">
                            <div class="space-y-0 text-sm">
                                <div v-for="act in recent_activities" :key="act.id" 
                                     class="flex items-center gap-4 px-6 py-4 hover:bg-slate-50 transition-colors border-b border-slate-50 last:border-0 group/act">
                                    <div :class="getActivityColor(act.type)" class="h-9 w-9 min-w-[2.25rem] rounded-xl flex items-center justify-center transition-transform group-hover/act:scale-110">
                                        <component :is="getActivityIcon(act.type)" class="h-4 w-4" />
                                    </div>
                                    <div class="flex-grow min-w-0">
                                        <div class="text-sm font-bold text-slate-900 truncate tracking-tight">{{ act.action }}</div>
                                        <div class="flex items-center justify-between text-[10px] font-bold text-slate-400 uppercase tracking-tighter mt-0.5">
                                            <span>{{ act.user }}</span>
                                            <span>{{ act.time }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <div class="bg-gradient-to-br from-indigo-600 to-primary rounded-[2rem] p-8 text-white shadow-xl shadow-primary/20 relative overflow-hidden group">
                        <div class="relative z-10 space-y-4 text-center">
                            <h3 class="text-xl font-black tracking-tight">Interactive Map</h3>
                            <p class="text-indigo-100 text-xs leading-relaxed opacity-80">Kelola visualisasi aset Anda langsung melalui peta interaktif.</p>
                            <Link href="/maps" class="block">
                                <Button class="w-full bg-white text-primary hover:bg-indigo-50 font-bold rounded-xl h-11 transition-all">
                                    Buka GIS Map
                                </Button>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
