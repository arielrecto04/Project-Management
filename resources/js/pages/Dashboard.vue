<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { CheckCircleIcon, ClipboardDocumentListIcon, ClockIcon, UsersIcon } from '@heroicons/vue/24/outline';
import { Head } from '@inertiajs/vue3';

interface DashboardStats {
    totalProjects: number;
    totalUsers: number;
    completedProjects: number;
    inProgressProjects: number;
    recentActivities: {
        id: number;
        type: string;
        description: string;
        user: string;
        date: string;
    }[];
    projectsByStatus: {
        name: string;
        count: number;
    }[];
}

const props = defineProps<{
    stats: DashboardStats;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const statsCards = [
    {
        title: 'Total Projects',
        value: props.stats.totalProjects,
        icon: ClipboardDocumentListIcon, // Changed from ClipboardListIcon
        color: 'bg-blue-500',
    },
    {
        title: 'Total Users',
        value: props.stats.totalUsers,
        icon: UsersIcon,
        color: 'bg-green-500',
    },
    {
        title: 'Completed Projects',
        value: props.stats.completedProjects,
        icon: CheckCircleIcon,
        color: 'bg-orange-500',
    },
    {
        title: 'In Progress',
        value: props.stats.inProgressProjects,
        icon: ClockIcon,
        color: 'bg-purple-500',
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Stats Grid -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <Card v-for="stat in statsCards" :key="stat.title" class="relative overflow-hidden">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">{{ stat.title }}</CardTitle>
                        <component :is="stat.icon" class="h-4 w-4 text-gray-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stat.value }}</div>
                    </CardContent>
                    <div class="absolute bottom-0 left-0 h-1 w-full" :class="stat.color" />
                </Card>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Project Status Chart -->
                <Card>
                    <CardHeader>
                        <CardTitle>Projects by Status</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-for="status in stats.projectsByStatus" :key="status.name" class="flex items-center">
                                <div class="w-full">
                                    <div class="mb-1 flex items-center justify-between">
                                        <span class="text-sm font-medium">{{ status.name }}</span>
                                        <span class="text-sm text-gray-500">{{ status.count }}</span>
                                    </div>
                                    <div class="h-2 w-full rounded-full bg-gray-100">
                                        <div
                                            class="h-2 rounded-full bg-orange-500"
                                            :style="`width: ${(status.count / stats.totalProjects) * 100}%`"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Recent Activities -->
                <Card>
                    <CardHeader>
                        <CardTitle>Recent Activities</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-for="activity in stats.recentActivities" :key="activity.id" class="flex items-center space-x-4">
                                <div class="flex-1 space-y-1">
                                    <p class="text-sm font-medium">{{ activity.description }}</p>
                                    <div class="flex items-center text-xs text-gray-500">
                                        <span>{{ activity.user }}</span>
                                        <span class="mx-1">•</span>
                                        <span>{{ activity.date }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.card-hover {
    @apply transition-shadow hover:shadow-lg;
}
</style>
