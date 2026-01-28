<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    Activity,
    Clock,
    Component,
    Cpu,
    Database,
    HardDrive,
    Layers,
    MapPin,
    Network,
    Settings2,
} from 'lucide-vue-next';

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
    {
        title: 'Total OLT',
        count: props.stats.active_devices.olt,
        icon: Database,
        color: 'text-indigo-600',
        bg: 'bg-indigo-50',
        link: 'active-device.olt.index',
    },
    {
        title: 'Total ODP',
        count: props.stats.passive_devices.odp,
        icon: Layers,
        color: 'text-emerald-600',
        bg: 'bg-emerald-50',
        link: 'passive-device.odp.index',
    },
    {
        title: 'Total Tiang',
        count: props.stats.passive_devices.pole,
        icon: Component,
        color: 'text-amber-600',
        bg: 'bg-amber-50',
        link: 'passive-device.pole.index',
    },
    {
        title: 'Total Router',
        count: props.stats.active_devices.router,
        icon: Network,
        color: 'text-rose-600',
        bg: 'bg-rose-50',
        link: 'active-device.router.index',
    },
    {
        title: 'Total Switch',
        count: props.stats.active_devices.switch,
        icon: Layers,
        color: 'text-cyan-600',
        bg: 'bg-cyan-50',
        link: 'active-device.switch.index',
    },
    {
        title: 'Total POP',
        count: props.stats.pops,
        icon: MapPin,
        color: 'text-blue-600',
        bg: 'bg-blue-50',
        link: 'pop.index',
    },
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
            {
                name: 'Wilayah Operasional',
                href: 'area.index',
                desc: `${props.stats.areas} Area`,
            },
            {
                name: 'Point of Presence',
                href: 'pop.index',
                desc: `${props.stats.pops} POP`,
            },
            {
                name: 'Server & Host',
                href: 'server.index',
                desc: 'Manajemen Node',
            },
        ],
    },
    {
        title: 'Perangkat Aktif',
        description: 'Hardware bertenaga.',
        icon: Cpu,
        color: 'bg-emerald-500',
        lightColor: 'bg-emerald-50',
        iconColor: 'text-emerald-600',
        items: [
            {
                name: 'OLT Management',
                href: 'active-device.olt.index',
                desc: `${props.stats.active_devices.olt} Unit`,
            },
            {
                name: 'Router',
                href: 'active-device.router.index',
                desc: `${props.stats.active_devices.router} Unit`,
            },
            {
                name: 'ONT/Laptop',
                href: 'active-device.ont.index',
                desc: `${props.stats.active_devices.ont} Unit`,
            },
        ],
    },
    {
        title: 'Perangkat Pasif',
        description: 'Jalur distribusi & FO.',
        icon: HardDrive,
        color: 'bg-amber-500',
        lightColor: 'bg-amber-50',
        iconColor: 'text-amber-600',
        items: [
            {
                name: 'ODP',
                href: 'passive-device.odp.index',
                desc: `${props.stats.passive_devices.odp} Titik`,
            },
            {
                name: 'Tiang',
                href: 'passive-device.pole.index',
                desc: `${props.stats.passive_devices.pole} Batang`,
            },
            {
                name: 'Joint Box',
                href: 'passive-device.joint-box.index',
                desc: `${props.stats.passive_devices.joint_box} Titik`,
            },
        ],
    },
];

const getActivityIcon = (type: string) => {
    switch (type) {
        case 'active':
            return Cpu;
        case 'passive':
            return HardDrive;
        default:
            return MapPin;
    }
};

const getActivityColor = (type: string) => {
    switch (type) {
        case 'active':
            return 'text-emerald-500 bg-emerald-50';
        case 'passive':
            return 'text-amber-500 bg-amber-50';
        default:
            return 'text-indigo-500 bg-indigo-50';
    }
};
</script>

<template>
    <Head title="Control Center - Pendataan" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Beranda', href: route('dashboard') },
            { title: 'Pusat Pendataan', href: '#' },
        ]"
    >
        <div class="mx-auto w-full max-w-[1600px] space-y-10 p-6 md:p-10">
            <!-- Hero Header -->
            <div
                class="relative overflow-hidden rounded-[2.5rem] bg-slate-950 p-8 text-white shadow-2xl md:p-12"
            >
                <div
                    class="absolute top-0 right-0 -mt-20 -mr-20 h-96 w-96 rounded-full bg-primary/20 blur-[100px]"
                ></div>

                <div
                    class="relative z-10 flex flex-col items-center justify-between gap-8 md:flex-row"
                >
                    <div class="space-y-4 text-center md:text-left">
                        <Badge
                            variant="outline"
                            class="border-primary/50 bg-primary/10 px-4 py-1.5 text-xs font-bold tracking-widest text-primary-foreground uppercase backdrop-blur-md"
                        >
                            <Activity class="mr-2 h-3 w-3" /> Data Control
                            Center
                        </Badge>
                        <h1
                            class="text-4xl font-black tracking-tighter md:text-5xl"
                        >
                            Ringkasan Data
                            <span class="text-primary">Aset Jaringan</span>
                        </h1>
                        <p
                            class="max-w-2xl text-lg leading-relaxed text-slate-400"
                        >
                            Pantau jumlah distribusi perangkat dan infrastruktur
                            secara real-time.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Detailed Stats Cards -->
            <div class="grid grid-cols-2 gap-6 md:grid-cols-3 lg:grid-cols-6">
                <Link
                    v-for="card in summaryCards"
                    :key="card.title"
                    :href="route(card.link)"
                    class="group"
                >
                    <Card
                        class="overflow-hidden rounded-[2rem] border-none bg-white shadow-[0_4px_20px_rgb(0,0,0,0.03)] transition-all duration-300 hover:shadow-[0_15px_40px_rgb(0,0,0,0.08)]"
                    >
                        <CardContent
                            class="flex flex-col items-center gap-4 p-6 text-center"
                        >
                            <div
                                :class="[card.bg, card.color]"
                                class="flex h-14 w-14 items-center justify-center rounded-2xl transition-transform duration-300 group-hover:scale-110"
                            >
                                <component :is="card.icon" class="h-7 w-7" />
                            </div>
                            <div>
                                <div
                                    class="mb-1 text-3xl leading-none font-black tracking-tighter text-slate-900"
                                >
                                    {{ card.count }}
                                </div>
                                <div
                                    class="text-[10px] font-bold tracking-widest text-slate-400 uppercase"
                                >
                                    {{ card.title }}
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </Link>
            </div>

            <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
                <!-- Left: Modules -->
                <div class="space-y-8 lg:col-span-8">
                    <div class="flex items-center justify-between px-2">
                        <h2
                            class="flex items-center gap-3 text-2xl font-black tracking-tight"
                        >
                            <Settings2 class="h-6 w-6 text-primary" />
                            Manajemen Modul
                        </h2>
                    </div>

                    <div class="grid gap-6 md:grid-cols-1">
                        <Card
                            v-for="module in modules"
                            :key="module.title"
                            class="group relative overflow-hidden rounded-[2rem] border-none bg-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition-all duration-500 hover:shadow-[0_20px_50px_rgb(0,0,0,0.1)]"
                        >
                            <CardContent class="flex flex-col p-0 md:flex-row">
                                <div
                                    :class="module.color"
                                    class="z-10 w-full md:w-3"
                                ></div>
                                <div
                                    class="flex flex-grow flex-col items-center gap-8 p-8 md:flex-row"
                                >
                                    <div
                                        :class="module.lightColor"
                                        class="flex h-16 w-16 min-w-[4rem] items-center justify-center rounded-2xl transition-transform duration-500 group-hover:scale-110"
                                    >
                                        <component
                                            :is="module.icon"
                                            :class="module.iconColor"
                                            class="h-8 w-8"
                                        />
                                    </div>
                                    <div
                                        class="flex-grow space-y-0.5 text-center md:text-left"
                                    >
                                        <h3
                                            class="text-xl font-bold tracking-tight"
                                        >
                                            {{ module.title }}
                                        </h3>
                                        <p
                                            class="text-xs font-medium text-muted-foreground"
                                        >
                                            {{ module.description }}
                                        </p>
                                    </div>
                                    <div
                                        class="grid w-full grid-cols-2 gap-3 sm:grid-cols-3 md:w-auto"
                                    >
                                        <Link
                                            v-for="item in module.items"
                                            :key="item.name"
                                            :href="route(item.href)"
                                            class="group/item"
                                        >
                                            <div
                                                class="min-w-[140px] rounded-xl border border-transparent bg-slate-50 p-4 transition-all duration-300 hover:bg-white hover:shadow-lg"
                                            >
                                                <div
                                                    class="truncate text-[10px] font-bold tracking-tight text-slate-800 transition-colors group-hover/item:text-primary"
                                                >
                                                    {{ item.name }}
                                                </div>
                                                <div
                                                    class="mt-0.5 text-[9px] font-bold text-slate-400"
                                                >
                                                    {{ item.desc }}
                                                </div>
                                            </div>
                                        </Link>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <!-- Right: Information & Activity -->
                <div class="space-y-8 lg:col-span-4">
                    <Card
                        class="overflow-hidden rounded-[2rem] border-none bg-white shadow-[0_8px_30px_rgb(0,0,0,0.04)]"
                    >
                        <CardHeader class="pb-4">
                            <CardTitle
                                class="flex items-center gap-2 text-xl font-black tracking-tight"
                            >
                                <Clock class="h-5 w-5 text-primary" />
                                Aktivitas Terakhir
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="px-0">
                            <div class="space-y-0 text-sm">
                                <div
                                    v-for="act in recent_activities"
                                    :key="act.id"
                                    class="group/act flex items-center gap-4 border-b border-slate-50 px-6 py-4 transition-colors last:border-0 hover:bg-slate-50"
                                >
                                    <div
                                        :class="getActivityColor(act.type)"
                                        class="flex h-9 w-9 min-w-[2.25rem] items-center justify-center rounded-xl transition-transform group-hover/act:scale-110"
                                    >
                                        <component
                                            :is="getActivityIcon(act.type)"
                                            class="h-4 w-4"
                                        />
                                    </div>
                                    <div class="min-w-0 flex-grow">
                                        <div
                                            class="truncate text-sm font-bold tracking-tight text-slate-900"
                                        >
                                            {{ act.action }}
                                        </div>
                                        <div
                                            class="mt-0.5 flex items-center justify-between text-[10px] font-bold tracking-tighter text-slate-400 uppercase"
                                        >
                                            <span>{{ act.user }}</span>
                                            <span>{{ act.time }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <div
                        class="group relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-indigo-600 to-primary p-8 text-white shadow-xl shadow-primary/20"
                    >
                        <div class="relative z-10 space-y-4 text-center">
                            <h3 class="text-xl font-black tracking-tight">
                                Interactive Map
                            </h3>
                            <p
                                class="text-xs leading-relaxed text-indigo-100 opacity-80"
                            >
                                Kelola visualisasi aset Anda langsung melalui
                                peta interaktif.
                            </p>
                            <Link :href="route('map.index')" class="block">
                                <Button
                                    class="h-11 w-full rounded-xl bg-white font-bold text-primary transition-all hover:bg-indigo-50"
                                >
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
