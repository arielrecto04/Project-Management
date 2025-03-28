<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Settings, UserCheck, UserPlus, Users as UsersIcon } from 'lucide-vue-next';
import { computed, defineProps } from 'vue';

interface User {
    id: number;
    name: string;
    email: string;
    projects_count: number;
    tasks_count: number;
    completed_tasks_count: number;
    avatar?: string;
}

const props = defineProps<{
    users: User[];
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Users', href: '/users' },
];

const getUserCompletionRate = (user: User) => {
    if (user.tasks_count === 0) return 0;
    return Math.round((user.completed_tasks_count / user.tasks_count) * 100);
};

const getCompletionColor = (rate: number) => {
    if (rate >= 75) return 'bg-green-600';
    if (rate >= 50) return 'bg-orange-600';
    return 'bg-red-600';
};

const getTotalStats = computed(() => {
    return {
        total: props.users.length,
        activeProjects: 0,
        completedTasks: props.users.reduce((acc, user) => acc + user.completed_tasks_count, 0),
    };
});
</script>

<template>
    <Head title="Users" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header Section -->
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Team Members</h1>
                    <p class="mt-1 text-sm text-gray-500">Manage and track team performance</p>
                </div>
                <Button class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90">
                    <UserPlus class="h-4 w-4" />
                    Add Member
                </Button>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium text-gray-500">Total Members</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-center justify-between">
                            <span class="text-2xl font-bold">{{ getTotalStats.total }}</span>
                            <UsersIcon class="h-5 w-5 text-gray-400" />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium text-gray-500">Active Projects</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-center justify-between">
                            <span class="text-2xl font-bold">{{ getTotalStats.activeProjects }}</span>
                            <Settings class="h-5 w-5 text-gray-400" />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium text-gray-500">Completed Tasks</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-center justify-between">
                            <span class="text-2xl font-bold">{{ getTotalStats.completedTasks }}</span>
                            <UserCheck class="h-5 w-5 text-gray-400" />
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Team Members Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Team Overview</CardTitle>
                </CardHeader>
                <CardContent class="p-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>User</TableHead>
                                <TableHead class="text-center">Projects</TableHead>
                                <TableHead class="text-center">Tasks</TableHead>
                                <TableHead>Completion Rate</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="user in users" :key="user.id" class="hover:bg-gray-50">
                                <TableCell>
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-orange-100">
                                            <span v-if="!user.avatar" class="font-medium text-orange-600">
                                                {{ user.name.charAt(0).toUpperCase() }}
                                            </span>
                                            <img v-else :src="user.avatar" :alt="user.name" class="h-full w-full rounded-full object-cover" />
                                        </div>
                                        <div>
                                            <div class="font-medium">{{ user.name }}</div>
                                            <div class="text-sm text-gray-500">{{ user.email }}</div>
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell class="text-center">
                                    <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700">
                                        {{ user.projects_count }}
                                    </span>
                                </TableCell>
                                <TableCell class="text-center">
                                    <span class="text-sm">{{ user.completed_tasks_count }}/{{ user.tasks_count }}</span>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center gap-2">
                                        <div class="h-2 w-24 rounded-full bg-gray-100">
                                            <div
                                                class="h-2 rounded-full transition-all duration-300"
                                                :class="getCompletionColor(getUserCompletionRate(user))"
                                                :style="`width: ${getUserCompletionRate(user)}%`"
                                            />
                                        </div>
                                        <span class="text-sm text-gray-600">{{ getUserCompletionRate(user) }}%</span>
                                    </div>
                                </TableCell>
                                <TableCell class="text-right">
                                    <Link
                                        :href="route('users.show', user.id)"
                                        class="inline-flex items-center rounded-md bg-orange-50 px-2 py-1 text-sm font-medium text-orange-700 hover:bg-orange-100"
                                    >
                                        View Details
                                    </Link>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<style scoped>
.table-row-hover {
    @apply transition-colors duration-200;
}
</style>
