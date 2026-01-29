<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import Sidebar from '@/components/ui/sidebar/Sidebar.vue';
import SidebarContent from '@/components/ui/sidebar/SidebarContent.vue';
import SidebarFooter from '@/components/ui/sidebar/SidebarFooter.vue';
import SidebarGroup from '@/components/ui/sidebar/SidebarGroup.vue';
import SidebarHeader from '@/components/ui/sidebar/SidebarHeader.vue';
import SidebarMenu from '@/components/ui/sidebar/SidebarMenu.vue';
import SidebarMenuButton from '@/components/ui/sidebar/SidebarMenuButton.vue';
import SidebarMenuItem from '@/components/ui/sidebar/SidebarMenuItem.vue';
import { dashboard } from '@/routes';
import { index as apIndex } from '@/routes/active-device/access-point';
import { index as oltIndex } from '@/routes/active-device/olt';
import { index as ontIndex } from '@/routes/active-device/ont';
import { index as routerIndex } from '@/routes/active-device/router';
import { index as switchIndex } from '@/routes/active-device/switch';
import { index as areaIndex } from '@/routes/area';
import { index as companiesIndex } from '@/routes/companies';
import { index as cpeIndex } from '@/routes/cpe';
import { index as mapIndex } from '@/routes/map';
import { index as cableIndex } from '@/routes/passive-device/cable';
import { index as jointBoxIndex } from '@/routes/passive-device/joint-box';
import { index as odfIndex } from '@/routes/passive-device/odf';
import { index as odpIndex } from '@/routes/passive-device/odps';
import { index as poleIndex } from '@/routes/passive-device/pole';
import { index as slackIndex } from '@/routes/passive-device/slack';
import { index as splicingPointIndex } from '@/routes/passive-device/splicing-point';
import { index as splitterIndex } from '@/routes/passive-device/splitter';
import { index as towerIndex } from '@/routes/passive-device/tower';
import { index as popIndex } from '@/routes/pop';
import { index as serverIndex } from '@/routes/server';
import { index as userIndex } from '@/routes/user';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import {
    Building2,
    Cable,
    Component,
    Cpu,
    Database,
    Globe,
    HardDrive,
    Layers,
    LayoutGrid,
    MapPin,
    MapPinned,
    Network,
    Radio,
    Server,
    Smartphone,
    Users,
    Zap,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage();
const isSuperAdmin = computed(
    () => (page.props.auth as any)?.roles?.includes('superadmin') || false,
);
const currentUrl = computed(() => page.url);

const isInPendataanScope = computed(() => {
    const pendataanPaths = [
        '/pendataan',
        '/pendataan/areas',
        '/pendataan/pops',
        '/pendataan/servers',
        '/pendataan/sites',
        '/pendataan/cpes',
        '/pendataan/active-devices',
        '/pendataan/passive-devices',
    ];
    // Exact match for /topology should be excluded from pendataan scope
    if (currentUrl.value === '/topology') return false;

    return pendataanPaths.some((path) => currentUrl.value.startsWith(path));
});

const filteredGeneralNavItems = computed(() => {
    if (isSuperAdmin.value) return [generalNavItems[0]];
    if (isInPendataanScope.value) {
        // In Pendataan focus mode: only show Dashboard to keep focus
        return [generalNavItems[0]];
    }
    return generalNavItems;
});

const shouldShowManagement = computed(() => {
    if (isSuperAdmin.value) return true;
    // Hide management for normal users when in Pendataan scope to reduce noise
    return !isInPendataanScope.value;
});

const generalNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
        icon: LayoutGrid,
    },
    {
        title: 'Pendataan',
        href: '/pendataan',
        icon: Database,
    },
    {
        title: 'GIS Map',
        href: mapIndex().url,
        icon: MapPinned,
    },
    {
        title: 'Topology',
        href: '/topology',
        icon: Network,
    },
];

const infrastructureNavItems: NavItem[] = [
    {
        title: 'Wilayah',
        href: areaIndex().url,
        icon: Globe,
    },
    {
        title: 'POP',
        href: popIndex().url,
        icon: MapPin,
    },
    {
        title: 'Server',
        href: serverIndex().url,
        icon: Server,
    },
    {
        title: 'CPE',
        href: cpeIndex().url,
        icon: Smartphone,
    },
];

const passiveDeviceNavItems: NavItem[] = [
    {
        title: 'ODF',
        href: odfIndex().url,
        icon: Layers,
    },
    {
        title: 'Tiang',
        href: poleIndex().url,
        icon: Component,
    },
    {
        title: 'ODP',
        href: odpIndex().url,
        icon: Layers,
    },
    {
        title: 'Kabel',
        href: cableIndex().url,
        icon: Cable,
    },
    {
        title: 'Joint Boxes',
        href: jointBoxIndex().url,
        icon: Zap,
    },
    {
        title: 'Splitter',
        href: splitterIndex().url,
        icon: Component,
    },
    {
        title: 'Slack',
        href: slackIndex().url,
        icon: Cable,
    },
    {
        title: 'Tower',
        href: towerIndex().url,
        icon: Radio,
    },
    {
        title: 'Splicing Points',
        href: splicingPointIndex().url,
        icon: Zap,
    },
];

const activeDeviceNavItems: NavItem[] = [
    {
        title: 'Router',
        href: routerIndex().url,
        icon: Network,
    },
    {
        title: 'Switch',
        href: switchIndex().url,
        icon: Layers,
    },
    {
        title: 'OLT',
        href: oltIndex().url,
        icon: Database,
    },
    {
        title: 'ONT',
        href: ontIndex().url,
        icon: Smartphone,
    },
    {
        title: 'Akses Point',
        href: apIndex().url,
        icon: Radio,
    },
];

const managementNavItems: NavItem[] = [
    {
        title: 'Perusahaan',
        href: companiesIndex().url,
        icon: Building2,
    },
    {
        title: 'User',
        href: userIndex().url,
        icon: Users,
    },
];

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard().url">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent class="scrollbar-thin px-2">
            <div class="mt-4 first:mt-0">
                <NavMain :items="filteredGeneralNavItems" title="Beranda" />
            </div>

            <template v-if="!isSuperAdmin && isInPendataanScope">
                <SidebarGroup class="px-2 py-0">
                    <!-- Inventory Label -->
                    <div
                        class="mb-3 flex h-8 items-center gap-2 px-2 text-sm font-medium text-foreground"
                    >
                        <Database class="h-4 w-4 shrink-0" />
                        <span>Inventory</span>
                    </div>

                    <div class="space-y-3">
                        <!-- Infrastruktur Section -->
                        <div
                            class="space-y-1 rounded-lg bg-muted/40 px-2 py-2 dark:bg-muted/20"
                        >
                            <div
                                class="flex items-center gap-2 px-2 py-1 text-[9px] font-bold text-muted-foreground/60 uppercase"
                            >
                                <MapPin class="h-3 w-3" /> Infrastruktur
                            </div>
                            <NavMain :items="infrastructureNavItems" />
                        </div>

                        <!-- Perangkat Aktif Section -->
                        <div
                            class="space-y-1 rounded-lg bg-muted/40 px-2 py-2 dark:bg-muted/20"
                        >
                            <div
                                class="flex items-center gap-2 px-2 py-1 text-[9px] font-bold text-muted-foreground/60 uppercase"
                            >
                                <Cpu class="h-3 w-3" /> Perangkat Aktif
                            </div>
                            <NavMain :items="activeDeviceNavItems" />
                        </div>

                        <!-- Perangkat Pasif Section -->
                        <div
                            class="space-y-1 rounded-lg bg-muted/40 px-2 py-2 dark:bg-muted/20"
                        >
                            <div
                                class="flex items-center gap-2 px-2 py-1 text-[9px] font-bold text-muted-foreground/60 uppercase"
                            >
                                <HardDrive class="h-3 w-3" /> Perangkat Pasif
                            </div>
                            <NavMain :items="passiveDeviceNavItems" />
                        </div>
                    </div>
                </SidebarGroup>
            </template>

            <div
                v-if="shouldShowManagement"
                class="mt-6 border-t border-border/50 pt-4"
            >
                <div
                    v-if="isSuperAdmin"
                    class="px-4 py-2 text-[10px] font-black tracking-[0.2em] text-muted-foreground/50 uppercase"
                >
                    Administrasi Sistem
                </div>
                <NavMain :items="managementNavItems" title="Manajemen" />
            </div>
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
