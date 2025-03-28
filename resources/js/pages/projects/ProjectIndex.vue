<script setup lang="ts">
import KanbanBoard from '@/components/KanbanBoard.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { ProjectStatus, ProjectStatusLabels } from '@/enums/ProjectStatus';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { CheckCircle2, ChevronUp, Clock, ListTodo, PlusCircle, TimerIcon } from 'lucide-vue-next';
import { computed, defineProps } from 'vue';

interface Project {
    id: number;
    name: string;
    description: string;
    status: string;
    start_date: string;
    end_date: string;
    assignee?: {
        id: number;
        name: string;
    };
}

const props = defineProps<{
    projects: Project[];
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Projects', href: '/projects' },
];

// Group projects by status using computed properties
const groupedProjects = computed(() => {
    const pending = (props.projects || []).filter((p) => p.status === ProjectStatus.Pending);
    const inProgress = (props.projects || []).filter((p) => p.status === ProjectStatus.InProgress);
    const completed = (props.projects || []).filter((p) => p.status === ProjectStatus.Completed);

    return [
        {
            id: 1,
            title: ProjectStatusLabels[ProjectStatus.Pending],
            tasks: pending.map((p) => ({
                id: p.id,
                title: p.name,
                status: p.status,
                description: p.description,
                assignee: p.assignee?.name,
                start_date: p.start_date,
                end_date: p.end_date,
            })),
        },
        {
            id: 2,
            title: ProjectStatusLabels[ProjectStatus.InProgress],
            tasks: inProgress.map((p) => ({
                id: p.id,
                title: p.name,
                status: p.status,
                description: p.description,
                assignee: p.assignee?.name,
                start_date: p.start_date,
                end_date: p.end_date,
            })),
        },
        {
            id: 3,
            title: ProjectStatusLabels[ProjectStatus.Completed],
            tasks: completed.map((p) => ({
                id: p.id,
                title: p.name,
                status: p.status,
                description: p.description,
                assignee: p.assignee?.name,
                start_date: p.start_date,
                end_date: p.end_date,
            })),
        },
    ];
});

// Project counts for summary cards
const projectCounts = computed(() => ({
    pending: groupedProjects.value[0].tasks.length,
    inProgress: groupedProjects.value[1].tasks.length,
    completed: groupedProjects.value[2].tasks.length,
}));

const updateProjectStatus = (payload: { itemId: number; newStatus: string }) => {
    console.log('Updating project status:', payload);

    router.put(
        route('projects.update-status', { project: payload.itemId }),
        {
            status: payload.newStatus,
        },
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                console.log('Status updated successfully');
            },
            onError: (errors) => {
                console.error('Failed to update status:', errors);
            },
        },
    );
};

// Add status color utility
const getStatusColor = (status: string) => {
    switch (status.toLowerCase()) {
        case 'pending':
            return 'bg-yellow-50 text-yellow-700 border-yellow-200';
        case 'in progress':
            return 'bg-blue-50 text-blue-700 border-blue-200';
        case 'completed':
            return 'bg-green-50 text-green-700 border-green-200';
        default:
            return 'bg-gray-50 text-gray-700 border-gray-200';
    }
};
</script>

<template>
    <Head title="Projects" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 p-6">
            <!-- Header with Create Button -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Projects Overview</h1>
                    <p class="mt-1 text-sm text-gray-500">Manage and track all your projects in one place</p>
                </div>
                <Link :href="route('projects.create')">
                    <Button class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90">
                        <PlusCircle class="h-4 w-4" />
                        Create Project
                    </Button>
                </Link>
            </div>

            <!-- Project Summary Cards -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <Card class="border-l-4 border-l-yellow-400">
                    <CardHeader class="pb-2">
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-base font-medium text-gray-700">To Do</CardTitle>
                            <ListTodo class="h-5 w-5 text-yellow-500" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-baseline justify-between">
                            <span class="text-3xl font-bold text-gray-900">{{ projectCounts.pending }}</span>
                            <div class="flex items-center text-sm text-yellow-600">
                                <ChevronUp class="h-4 w-4" />
                                <span>Pending</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="border-l-4 border-l-blue-400">
                    <CardHeader class="pb-2">
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-base font-medium text-gray-700">In Progress</CardTitle>
                            <TimerIcon class="h-5 w-5 text-blue-500" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-baseline justify-between">
                            <span class="text-3xl font-bold text-gray-900">{{ projectCounts.inProgress }}</span>
                            <div class="flex items-center text-sm text-blue-600">
                                <Clock class="h-4 w-4" />
                                <span>Active</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="border-l-4 border-l-green-400">
                    <CardHeader class="pb-2">
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-base font-medium text-gray-700">Completed</CardTitle>
                            <CheckCircle2 class="h-5 w-5 text-green-500" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-baseline justify-between">
                            <span class="text-3xl font-bold text-gray-900">{{ projectCounts.completed }}</span>
                            <div class="flex items-center text-sm text-green-600">
                                <span>Done</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Kanban Board Section -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Project Board</h2>
                    <div class="text-sm text-gray-500">{{ props.projects.length }} total projects</div>
                </div>

                <KanbanBoard :columns="groupedProjects" type="project" @item-moved="updateProjectStatus" class="min-h-[600px]" />
            </div>
        </div>
    </AppLayout>
</template>

<style>
.kanban-column {
    @apply rounded-lg bg-gray-50 p-4;
}

.kanban-card {
    @apply mb-3 cursor-move rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition-shadow hover:shadow-md;
}
</style>
