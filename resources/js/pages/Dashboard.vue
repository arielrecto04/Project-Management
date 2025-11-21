<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { CheckCircleIcon, ClipboardDocumentListIcon, ClockIcon, UsersIcon } from '@heroicons/vue/24/outline';
import { Head } from '@inertiajs/vue3';

// added chart imports
import AreaChart from '@/components/charts/AreaChart.vue';
import LineChart from '@/components/charts/LineChart.vue';
import StackedBarChart from '@/components/charts/StackedBarChart.vue';
import DonutChart from '@/components/charts/DonutChart.vue';
import ScatterChart from '@/components/charts/ScatterChart.vue';
import GroupedBarChart from '@/components/charts/GroupedBarChart.vue';
import TimelineChart from '@/components/charts/TimelineChart.vue';

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
        <div class="flex flex-col flex-1 gap-6 p-6 h-full">
            <!-- Stats Grid -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <Card v-for="stat in statsCards" :key="stat.title" class="overflow-hidden relative">
                    <CardHeader class="flex flex-row justify-between items-center pb-2 space-y-0">
                        <CardTitle class="text-sm font-medium">{{ stat.title }}</CardTitle>
                        <component :is="stat.icon" class="w-4 h-4 text-gray-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stat.value }}</div>
                    </CardContent>
                    <div class="absolute bottom-0 left-0 w-full h-1" :class="stat.color" />
                </Card>
            </div>

            <!-- Charts Grid: import and use all charts from /components/charts -->
            <div class="grid gap-2 grid-cols-1 md:grid-cols-2 lg:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Area (Overview)</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <AreaChart />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Line (Trend)</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <LineChart />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Stacked Bar</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <StackedBarChart />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Donut (Distribution)</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <DonutChart />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Scatter</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <ScatterChart />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Grouped Bar</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <GroupedBarChart />
                    </CardContent>
                </Card>

                <!-- <Card class="md:col-span-2 lg:col-span-3">
                    <CardHeader>
                        <CardTitle>Timeline</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <TimelineChart />
                    </CardContent>
                </Card> -->
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
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-sm font-medium">{{ status.name }}</span>
                                        <span class="text-sm text-gray-500">{{ status.count }}</span>
                                    </div>
                                    <div class="w-full h-2 bg-gray-100 rounded-full">
                                        <div class="h-2 bg-orange-500 rounded-full"
                                            :style="`width: ${(status.count / stats.totalProjects) * 100}%`" />
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
                            <div v-for="activity in stats.recentActivities" :key="activity.id"
                                class="flex items-center space-x-4">
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
