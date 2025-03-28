<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { ProjectStatus } from '@/enums/ProjectStatus';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { AlertCircle, Calendar, Check, Circle, Clock, PlayCircle } from 'lucide-vue-next';
import { computed } from 'vue';

interface Task {
    id: number;
    name: string;
    status: string;
    due_date: string;
    created_at: string;
    updated_at?: string;
    assignee_to: {
        name: string;
    };
}

interface Project {
    id: number;
    name: string;
    description: string;
    status: string;
    start_date: string;
    end_date: string;
    tasks: Task[];
    created_at: string;
}

const props = defineProps<{
    project: Project;
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Projects', href: '/projects' },
    { title: props.project.name, href: route('projects.show', props.project.id) },
    { title: 'Timeline', href: '#' },
];

const getStatusConfig = (status: string) => {
    switch (status) {
        case ProjectStatus.Pending:
            return {
                icon: Clock,
                color: 'text-yellow-500',
                bgColor: 'bg-yellow-50',
                borderColor: 'border-yellow-200',
            };
        case ProjectStatus.InProgress:
            return {
                icon: PlayCircle,
                color: 'text-blue-500',
                bgColor: 'bg-blue-50',
                borderColor: 'border-blue-200',
            };
        case ProjectStatus.Completed:
            return {
                icon: Check,
                color: 'text-green-500',
                bgColor: 'bg-green-50',
                borderColor: 'border-green-200',
            };
        default:
            return {
                icon: AlertCircle,
                color: 'text-gray-500',
                bgColor: 'bg-gray-50',
                borderColor: 'border-gray-200',
            };
    }
};

// Update the timelineEvents computed property
const timelineEvents = computed(() => {
    const events = [
        // Project Creation
        {
            date: new Date(props.project.created_at),
            type: 'project_created',
            title: 'Project Created',
            icon: Circle,
            iconClass: 'text-gray-500',
            bgClass: 'bg-gray-50',
            borderClass: 'border-gray-200',
        },
        // Project Status Events
        {
            date: new Date(props.project.start_date),
            type: 'project_started',
            title: 'Project Started',
            icon: PlayCircle,
            iconClass: 'text-blue-500',
            bgClass: 'bg-blue-50',
            borderClass: 'border-blue-200',
        },
        // Tasks Events
        ...props.project.tasks
            .flatMap((task) => [
                // Task Creation
                {
                    date: new Date(task.created_at),
                    type: 'task_created',
                    title: `Task Created: ${task.name}`,
                    description: `Assigned to ${task.assignee_to.name}`,
                    status: task.status,
                    dueDate: new Date(task.due_date),
                    taskId: task.id,
                    ...getStatusConfig(task.status),
                },
                // Task Status Events (for completed tasks)
                task.status === ProjectStatus.Completed
                    ? {
                          date: new Date(task.updated_at),
                          type: 'task_completed',
                          title: `Task Completed: ${task.name}`,
                          icon: Check,
                          iconClass: 'text-green-500',
                          bgClass: 'bg-green-50',
                          borderClass: 'border-green-200',
                          taskId: task.id,
                      }
                    : null,
            ])
            .filter(Boolean),
        // Project End Date (if set and not in the future)
        props.project.end_date && new Date(props.project.end_date) <= new Date()
            ? {
                  date: new Date(props.project.end_date),
                  type: 'project_ended',
                  title: 'Project Ended',
                  icon: Check,
                  iconClass: 'text-green-500',
                  bgClass: 'bg-green-50',
                  borderClass: 'border-green-200',
              }
            : null,
    ].filter(Boolean);

    return events.sort((a, b) => a.date.getTime() - b.date.getTime());
});

const isOverdue = (date: Date) => {
    return date < new Date() && date > new Date(0);
};

const formatDateWithTime = (date: Date) => {
    return new Intl.DateTimeFormat('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: 'numeric',
    }).format(date);
};

const formatDate = (date: Date) => {
    return date.toLocaleDateString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
    });
};
</script>

<template>
    <Head :title="`${project.name} - Timeline`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-5xl space-y-6 p-6">
            <!-- Header -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle class="text-2xl">Project Timeline</CardTitle>
                            <p class="mt-1 text-sm text-gray-500">Track the progress of {{ project.name }}</p>
                        </div>
                        <div class="flex items-center space-x-2 text-sm text-gray-500">
                            <Calendar class="h-4 w-4" />
                            <span>{{ formatDate(new Date(project.start_date)) }}</span>
                            <span>-</span>
                            <span>{{ formatDate(new Date(project.end_date)) }}</span>
                        </div>
                    </div>
                </CardHeader>
            </Card>

            <!-- Timeline -->
            <Card>
                <CardContent class="p-6">
                    <div class="relative">
                        <!-- Vertical Line -->
                        <div class="absolute left-4 top-0 h-full w-0.5 bg-gray-200"></div>

                        <!-- Timeline Events -->
                        <div class="space-y-8">
                            <div v-for="(event, index) in timelineEvents" :key="index" class="relative ml-10">
                                <!-- Event Circle -->
                                <div
                                    class="absolute -left-14 mt-1.5 flex h-8 w-8 items-center justify-center rounded-full border-2 border-white shadow-md"
                                    :class="[event.bgClass || event.bgColor]"
                                >
                                    <component :is="event.icon" :class="[event.iconClass || event.color, 'h-4 w-4']" />
                                </div>

                                <!-- Event Content -->
                                <div
                                    class="flex flex-col rounded-lg border bg-white p-4 shadow-sm"
                                    :class="event.type.startsWith('task_') ? event.borderColor : event.borderClass"
                                >
                                    <div class="flex items-center justify-between">
                                        <div class="space-y-1">
                                            <h3 class="font-medium text-gray-900">{{ event.title }}</h3>
                                            <div v-if="event.type === 'task_created'" class="flex items-center gap-2">
                                                <span
                                                    class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                                                    :class="[event.bgColor, event.color]"
                                                >
                                                    {{ event.status.replace('_', ' ') }}
                                                </span>
                                                <span
                                                    v-if="event.dueDate && isOverdue(event.dueDate)"
                                                    class="inline-flex items-center gap-1 text-xs text-red-600"
                                                >
                                                    <AlertCircle class="h-3 w-3" />
                                                    Overdue
                                                </span>
                                            </div>
                                        </div>
                                        <time class="text-sm text-gray-500">
                                            {{ formatDateWithTime(event.date) }}
                                        </time>
                                    </div>

                                    <!-- Task Details -->
                                    <div v-if="event.type === 'task_created'" class="mt-2 space-y-2">
                                        <p class="text-sm text-gray-500">
                                            {{ event.description }}
                                        </p>
                                        <div class="flex items-center gap-2 text-sm text-gray-500">
                                            <Calendar class="h-4 w-4" />
                                            <span>Due {{ formatDate(event.dueDate) }}</span>
                                        </div>
                                    </div>

                                    <!-- Project Event Details -->
                                    <div v-else-if="event.type.startsWith('project_')" class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            {{
                                                event.type === 'project_created'
                                                    ? 'Project timeline started'
                                                    : event.type === 'project_started'
                                                      ? 'Project work began'
                                                      : event.type === 'project_ended'
                                                        ? 'Project completed'
                                                        : ''
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
