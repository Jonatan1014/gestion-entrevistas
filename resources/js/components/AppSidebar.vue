<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import ThemeToggle from '@/components/ThemeToggle.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BarChart, Briefcase, Calendar, ClipboardCheck, LayoutGrid, Shield, Users } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

interface NavItemWithPermission extends NavItem {
    permission?: string | string[];
}

const page = usePage<SharedData>();
const userPermissions = computed<string[]>(() => page.props.auth.permissions ?? []);

const hasPermission = (permission?: string | string[]): boolean => {
    if (!permission) {
        return true;
    }

    const required = Array.isArray(permission) ? permission : [permission];

    return required.some((name) => userPermissions.value.includes(name));
};

const mainNavItems: NavItemWithPermission[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Vacancies',
        href: '/vacancies',
        icon: Briefcase,
        permission: 'view-vacancies',
    },
    {
        title: 'Applicants',
        href: '/applicants',
        icon: Users,
        permission: 'view-applicants',
    },
    {
        title: 'Tests',
        href: '/tests',
        icon: ClipboardCheck,
        permission: ['view-tests', 'manage-tests'],
    },
    {
        title: 'Interviews',
        href: '/interviews',
        icon: Calendar,
        permission: 'view-interviews',
    },
    {
        title: 'Reports',
        href: '/reports',
        icon: BarChart,
        permission: 'view-reports',
    },
    {
        title: 'Roles',
        href: '/admin/roles',
        icon: Shield,
        permission: 'manage-roles',
    },
];

const visibleNavItems = computed<NavItem[]>(() => mainNavItems.filter((item) => hasPermission(item.permission)));
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="visibleNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <ThemeToggle />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
