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
    SidebarSeparator,
} from '@/components/ui/sidebar';
import { SideBarMainItems } from '@/routes/index';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { ExternalLink } from 'lucide-vue-next';

const mainNavItems: NavItem[] = [...SideBarMainItems];

const footerNavItems: NavItem[] = [
    {
        title: 'Visit Innovato',
        href: 'https://www.innovatotec.com',
        icon: ExternalLink,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset" class="border-r border-orange-100 bg-orange-50/50">
        <SidebarHeader class="p-4">
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')" class="flex gap-2 items-center">
                            <img src="https://iits.website/logo.png" alt="Innovato" class="w-auto h-8" />
                            <span class="text-lg font-semibold text-orange-600"> Project Hub </span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarSeparator class="bg-orange-200/50" />

        <SidebarContent class="px-2">
            <NavMain
                :items="mainNavItems"
                class="[&_[data-sidebar=menu-button]]: [&_[data-sidebar=menu-button]] [&_[data-sidebar=menu-button][data-active=true]]:bg-orange-100 [&_[data-sidebar=menu-button][data-active=true]]:text-orange-700 [&_[data-sidebar=menu-button]]:text-gray-600"
            />
        </SidebarContent>

        <SidebarFooter class="mt-auto border-t border-orange-100">
            <NavFooter
                :items="footerNavItems"
                class="[&_[data-sidebar=menu-button]]:text-gray-500 [&_[data-sidebar=menu-button]]:hover:text-orange-600"
            />
            <SidebarSeparator class="bg-orange-200/50" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>

<style scoped>
:deep([data-sidebar='sidebar']) {
    --sidebar-background: theme('colors.orange.50');
    --sidebar-foreground: theme('colors.gray.900');
    --sidebar-border: theme('colors.orange.200');
    --sidebar-accent: theme('colors.orange.100');
    --sidebar-accent-foreground: theme('colors.orange.700');
}
</style>
