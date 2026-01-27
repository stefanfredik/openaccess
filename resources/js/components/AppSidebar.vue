<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index as companiesIndex } from '@/routes/companies';
import { index as areaIndex } from '@/routes/area';
import { index as siteIndex } from '@/routes/site';
import { index as popIndex } from '@/routes/pop';
import { index as serverIndex } from '@/routes/server';
import { index as userIndex } from '@/routes/user';
import { index as odfIndex } from '@/routes/passive-device/odf';
import { index as poleIndex } from '@/routes/passive-device/pole';
import { index as odpIndex } from '@/routes/passive-device/odp';
import { index as cableIndex } from '@/routes/passive-device/cable';
import { index as jointBoxIndex } from '@/routes/passive-device/joint-box';
import { index as splitterIndex } from '@/routes/passive-device/splitter';
import { index as slackIndex } from '@/routes/passive-device/slack';
import { index as towerIndex } from '@/routes/passive-device/tower';
import { index as splicingPointIndex } from '@/routes/passive-device/splicing-point';
import { index as oltIndex } from '@/routes/active-device/olt';
import { index as ontIndex } from '@/routes/active-device/ont';
import { index as switchIndex } from '@/routes/active-device/switch';
import { index as routerIndex } from '@/routes/active-device/router';
import { index as apIndex } from '@/routes/active-device/access-point';
import { index as cpeIndex } from '@/routes/cpe';
import { index as mapIndex } from '@/routes/map';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Building2, MapPin, Server, Users, Network, Map } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const isSuperAdmin = computed(() => (page.props.auth as any).roles.includes('superadmin'));


const generalNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
        icon: LayoutGrid,
    },
    {
        title: 'GIS Map',
        href: mapIndex().url,
        icon: Map,
    },
];

const infrastructureNavItems: NavItem[] = [
    {
        title: 'Areas',
        href: areaIndex().url,
        icon: MapPin,
    },
    {
        title: 'POPs',
        href: popIndex().url,
        icon: MapPin, 
    },
    {
        title: 'Servers',
        href: serverIndex().url,
        icon: Server, 
    },
    {
        title: 'Sites',
        href: siteIndex().url,
        icon: Server, 
    },
    {
        title: 'CPEs',
        href: cpeIndex().url,
        icon: Server,
    },
];

const passiveDeviceNavItems: NavItem[] = [
    {
        title: 'ODFs',
        href: odfIndex().url,
        icon: MapPin,
    },
    {
        title: 'Poles',
        href: poleIndex().url,
        icon: MapPin,
    },
    {
        title: 'ODPs',
        href: odpIndex().url,
        icon: MapPin,
    },
    {
        title: 'Cables',
        href: cableIndex().url,
        icon: Server,
    },
    {
        title: 'Joint Boxes',
        href: jointBoxIndex().url,
        icon: MapPin,
    },
    {
        title: 'Splitters',
        href: splitterIndex().url,
        icon: MapPin,
    },
    {
        title: 'Slacks',
        href: slackIndex().url,
        icon: MapPin,
    },
    {
        title: 'Towers',
        href: towerIndex().url,
        icon: MapPin,
    },
    {
        title: 'Splicing Points',
        href: splicingPointIndex().url,
        icon: MapPin,
    },
];

const activeDeviceNavItems: NavItem[] = [
    {
        title: 'OLTs',
        href: oltIndex().url,
        icon: Server,
    },
    {
        title: 'ONTs',
        href: ontIndex().url,
        icon: Server,
    },
    {
        title: 'Switches',
        href: switchIndex().url,
        icon: Server,
    },
    {
        title: 'Routers',
        href: routerIndex().url,
        icon: Server,
    },
    {
        title: 'APs',
        href: apIndex().url,
        icon: Server,
    },
    {
        title: 'Topology',
        href: '/active-devices/topology',
        icon: Network,
    },
];

const managementNavItems: NavItem[] = [
    {
        title: 'Companies',
        href: companiesIndex().url,
        icon: Building2,
    },
    {
        title: 'Users',
        href: userIndex().url,
        icon: Users,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
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

        <SidebarContent>
            <NavMain :items="isSuperAdmin ? [generalNavItems[0]] : generalNavItems" title="Navigation" />
            
            <template v-if="!isSuperAdmin">
                <NavMain :items="infrastructureNavItems" title="Infrastructure" />
                <NavMain :items="activeDeviceNavItems" title="Active Devices" />
                <NavMain :items="passiveDeviceNavItems" title="Passive Devices" />
            </template>

            <NavMain :items="managementNavItems" title="Management" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
