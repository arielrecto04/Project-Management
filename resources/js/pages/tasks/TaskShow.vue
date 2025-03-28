<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Calendar, Clock, FileText, Folder, User, Users } from 'lucide-vue-next';

interface Task {
    id: number;
    name: string;
    description: string;
    status: string;
    due_date: string;
    project: {
        id: number;
        name: string;
    };
    assignee_to: {
        id: number;
        name: string;
    };
    created_by: {
        id: number;
        name: string;
    };
}

const props = defineProps<{
    task: Task;
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Tasks', href: '/tasks' },
    { title: props.task.name, href: '#' },
];

// Format functions
const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'pending':
            return 'bg-yellow-100 text-yellow-800 border-yellow-200';
        case 'in_progress':
            return 'bg-blue-100 text-blue-800 border-blue-200';
        case 'completed':
            return 'bg-green-100 text-green-800 border-green-200';
        default:
            return 'bg-gray-100 text-gray-800 border-gray-200';
    }
};

const isOverdue = (dueDate: string) => {
    return new Date(dueDate) < new Date();
};
</script>

<template>
    <Head :title="task.name" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-5xl space-y-6 p-6">
            <!-- Task Header -->
            <Card>
                <CardHeader class="border-b pb-4">
                    <div class="flex items-start justify-between">
                        <div class="space-y-2">
                            <CardTitle class="text-2xl">{{ task.name }}</CardTitle>
                            <div class="flex items-center gap-3">
                                <span
                                    class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-medium"
                                    :class="getStatusColor(task.status)"
                                >
                                    {{ task.status.replace('_', ' ') }}
                                </span>
                                <span v-if="isOverdue(task.due_date)" class="inline-flex items-center text-xs text-red-600">
                                    <Clock class="mr-1 h-3 w-3" />
                                    Overdue
                                </span>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <Link :href="route('tasks.edit', task.id)">
                                <Button variant="outline">Edit Task</Button>
                            </Link>
                            <Button variant="default">Mark as Complete</Button>
                        </div>
                    </div>
                </CardHeader>

                <CardContent class="space-y-8 pt-6">
                    <!-- Description Section -->
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-sm font-medium text-gray-600">
                            <FileText class="h-4 w-4" />
                            <h3>Description</h3>
                        </div>
                        <div class="pl-6">
                            <p class="whitespace-pre-wrap text-gray-600">
                                {{ task.description || 'No description provided' }}
                            </p>
                        </div>
                    </div>

                    <!-- Task Details Grid -->
                    <div class="grid gap-6 md:grid-cols-2">
                        <!-- Project -->
                        <div class="space-y-2">
                            <div class="flex items-center gap-2 text-sm font-medium text-gray-600">
                                <Folder class="h-4 w-4" />
                                <h3>Project</h3>
                            </div>
                            <div class="pl-6">
                                <Link :href="route('projects.show', task.project.id)" class="text-orange-600 hover:underline">
                                    {{ task.project.name }}
                                </Link>
                            </div>
                        </div>

                        <!-- Assignee -->
                        <div class="space-y-2">
                            <div class="flex items-center gap-2 text-sm font-medium text-gray-600">
                                <User class="h-4 w-4" />
                                <h3>Assigned To</h3>
                            </div>
                            <div class="flex items-center gap-2 pl-6">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-orange-100">
                                    <span class="text-sm font-medium text-orange-600">
                                        {{ task.assignee_to.name.charAt(0) }}
                                    </span>
                                </div>
                                <span>{{ task.assignee_to.name }}</span>
                            </div>
                        </div>

                        <!-- Due Date -->
                        <div class="space-y-2">
                            <div class="flex items-center gap-2 text-sm font-medium text-gray-600">
                                <Calendar class="h-4 w-4" />
                                <h3>Due Date</h3>
                            </div>
                            <div class="pl-6">
                                <span :class="{ 'text-red-600': isOverdue(task.due_date) }">
                                    {{ formatDate(task.due_date) }}
                                </span>
                            </div>
                        </div>

                        <!-- Created By -->
                        <div class="space-y-2">
                            <div class="flex items-center gap-2 text-sm font-medium text-gray-600">
                                <Users class="h-4 w-4" />
                                <h3>Created By</h3>
                            </div>
                            <div class="flex items-center gap-2 pl-6">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100">
                                    <span class="text-sm font-medium text-gray-600">
                                        {{ task.created_by.name.charAt(0) }}
                                    </span>
                                </div>
                                <span>{{ task.created_by.name }}</span>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
