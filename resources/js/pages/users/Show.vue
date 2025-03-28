<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { ProjectStatus } from '@/enums/ProjectStatus';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Briefcase, Calendar, CheckCircle, ChevronLeft, ChevronRight, ClipboardList, Clock } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Project {
    id: number;
    name: string;
    status: string;
    tasks_count: number;
    completed_tasks_count: number;
}

interface Task {
    id: number;
    name: string;
    status: string;
    project: {
        name: string;
    };
    due_date: string;
}

interface User {
    id: number;
    name: string;
    email: string;
    projects: Project[];
    tasks: Task[];
    created_at: string;
}

const props = defineProps<{
    user: User;
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Users', href: '/users' },
    { title: props.user.name, href: route('users.show', props.user.id) },
];

const stats = computed(() => ({
    totalProjects: props.user.projects?.length || 0,
    totalTasks: props.user.tasks?.length || 0,
    completedTasks: props.user.tasks.filter((t) => t.status === ProjectStatus.Completed).length,
    upcomingTasks: props.user.tasks.filter((t) => {
        const dueDate = new Date(t.due_date);
        const today = new Date();
        const inSevenDays = new Date();
        inSevenDays.setDate(today.getDate() + 7);
        return dueDate >= today && dueDate <= inSevenDays;
    }).length,
}));

const TASKS_PER_PAGE = 5;
const currentPage = ref(1);

const paginatedTasks = computed(() => {
    const start = (currentPage.value - 1) * TASKS_PER_PAGE;
    const end = start + TASKS_PER_PAGE;
    return props.user.tasks.slice(start, end);
});

const totalPages = computed(() => Math.ceil(props.user.tasks.length / TASKS_PER_PAGE));

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++;
    }
};

const prevPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--;
    }
};

const getStatusIcon = (status: string) => {
    switch (status) {
        case ProjectStatus.Completed:
            return CheckCircle;
        case ProjectStatus.InProgress:
            return Clock;
        default:
            return ClipboardList;
    }
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};
</script>

<template>
    <Head :title="user.name" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- User Header -->
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-orange-100 ring-4 ring-orange-50">
                        <span class="text-2xl font-medium text-orange-600">
                            {{ user.name.charAt(0).toUpperCase() }}
                        </span>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ user.name }}</h1>
                        <p class="text-gray-500">{{ user.email }}</p>
                        <p class="mt-1 text-sm text-gray-400">Member since {{ formatDate(user.created_at) }}</p>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid gap-6 md:grid-cols-4">
                <Card class="border-l-4 border-orange-400">
                    <CardHeader class="pb-2">
                        <CardTitle class="flex items-center gap-2 text-sm font-medium text-gray-500">
                            <Briefcase class="h-4 w-4 text-orange-500" />
                            Projects
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-gray-900">{{ stats.totalProjects }}</div>
                    </CardContent>
                </Card>

                <Card class="border-l-4 border-blue-400">
                    <CardHeader class="pb-2">
                        <CardTitle class="flex items-center gap-2 text-sm font-medium text-gray-500">
                            <ClipboardList class="h-4 w-4 text-blue-500" />
                            Total Tasks
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-gray-900">{{ stats.totalTasks }}</div>
                    </CardContent>
                </Card>

                <Card class="border-l-4 border-green-400">
                    <CardHeader class="pb-2">
                        <CardTitle class="flex items-center gap-2 text-sm font-medium text-gray-500">
                            <CheckCircle class="h-4 w-4 text-green-500" />
                            Completed
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-gray-900">{{ stats.completedTasks }}</div>
                    </CardContent>
                </Card>

                <Card class="border-l-4 border-yellow-400">
                    <CardHeader class="pb-2">
                        <CardTitle class="flex items-center gap-2 text-sm font-medium text-gray-500">
                            <Calendar class="h-4 w-4 text-yellow-500" />
                            Upcoming
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-gray-900">{{ stats.upcomingTasks }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Projects Section -->
            <Card>
                <CardHeader class="border-b">
                    <div class="flex items-center justify-between">
                        <CardTitle>Active Projects</CardTitle>
                        <span class="text-sm text-gray-500">{{ user.projects?.length || 0 }} total</span>
                    </div>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="divide-y">
                        <div v-for="project in user.projects" :key="project.id" class="flex items-center justify-between p-4 hover:bg-gray-50">
                            <div class="space-y-1">
                                <h3 class="font-medium text-gray-900">{{ project.name }}</h3>
                                <p class="text-sm text-gray-500">{{ project.completed_tasks_count }}/{{ project.tasks_count }} tasks completed</p>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="flex items-center gap-2">
                                    <div class="h-2 w-24 overflow-hidden rounded-full bg-gray-100">
                                        <div
                                            class="h-full rounded-full bg-orange-600 transition-all duration-300"
                                            :style="`width: ${(project.completed_tasks_count / project.tasks_count) * 100}%`"
                                        />
                                    </div>
                                    <span class="text-sm font-medium text-gray-900">
                                        {{ Math.round((project.completed_tasks_count / project.tasks_count) * 100) }}%
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Recent Tasks -->
            <Card>
                <CardHeader class="border-b">
                    <div class="flex items-center justify-between">
                        <CardTitle>Recent Tasks</CardTitle>
                        <span class="text-sm text-gray-500">
                            {{ (currentPage - 1) * TASKS_PER_PAGE + 1 }}-{{ Math.min(currentPage * TASKS_PER_PAGE, user.tasks.length) }} of
                            {{ user.tasks.length }}
                        </span>
                    </div>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="divide-y">
                        <div v-for="task in paginatedTasks" :key="task.id" class="flex items-center justify-between p-4 hover:bg-gray-50">
                            <div class="space-y-1">
                                <h3 class="font-medium text-gray-900">{{ task.name }}</h3>
                                <div class="flex items-center gap-2 text-sm text-gray-500">
                                    <span>{{ task.project.name }}</span>
                                    <span>•</span>
                                    <span>Due {{ formatDate(task.due_date) }}</span>
                                </div>
                            </div>
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium"
                                :class="{
                                    'bg-green-50 text-green-700': task.status === ProjectStatus.Completed,
                                    'bg-yellow-50 text-yellow-700': task.status === ProjectStatus.Pending,
                                    'bg-blue-50 text-blue-700': task.status === ProjectStatus.InProgress,
                                }"
                            >
                                <component :is="getStatusIcon(task.status)" class="h-3.5 w-3.5" />
                                {{ task.status }}
                            </span>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="flex items-center justify-between border-t p-4">
                        <button
                            @click="prevPage"
                            :disabled="currentPage === 1"
                            class="inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <ChevronLeft class="h-4 w-4" />
                            Previous
                        </button>
                        <button
                            @click="nextPage"
                            :disabled="currentPage === totalPages"
                            class="inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Next
                            <ChevronRight class="h-4 w-4" />
                        </button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<style scoped>
.divide-y > * + * {
    @apply border-t border-gray-200;
}
</style>
