<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/AppLayout.vue';
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';

interface CalendarItem {
    id: number;
    title: string;
    start: string;
    end: string;
    type: 'project' | 'task';
    status: string;
    url: string;
}

const props = defineProps<{
    projects: {
        id: number;
        name: string;
        start_date: string;
        end_date: string;
        status: string;
    }[];
    tasks: {
        id: number;
        name: string;
        start_date: string;
        end_date: string;
        status: string;
    }[];
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Calendar', href: '/calendar' },
];

// Summary calculations
const summary = computed(() => ({
    totalProjects: props.projects.length,
    totalTasks: props.tasks.length,
    upcomingDeadlines: props.projects.concat(props.tasks).filter((item) => {
        const deadline = new Date(item.end_date);
        const today = new Date();
        const inSevenDays = new Date();
        inSevenDays.setDate(today.getDate() + 7);
        return deadline >= today && deadline <= inSevenDays;
    }).length,
}));

// Calendar ref
const calendarEl = ref<HTMLElement | null>(null);

// Filter states
const showProjects = ref(true);
const showTasks = ref(true);
const statusFilter = ref<string[]>([]);

// Filtered events
const filteredEvents = computed(() => {
    let filtered = [];

    if (showProjects.value) {
        filtered.push(
            ...props.projects.map((project) => ({
                id: `project-${project.id}`,
                title: `🏢 ${project.name}`,
                start: project.start_date,
                end: project.end_date,
                type: 'project',
                status: project.status,
                url: route('projects.show', project.id),
                backgroundColor: '#f97316',
            })),
        );
    }

    if (showTasks.value) {
        filtered.push(
            ...props.tasks.map((task) => ({
                id: `task-${task.id}`,
                title: `📋 ${task.name}`,
                start: task.start_date,
                end: task.end_date,
                type: 'task',
                status: task.status,
                url: route('tasks.show', task.id),
                backgroundColor: '#3b82f6',
            })),
        );
    }

    if (statusFilter.value.length > 0) {
        filtered = filtered.filter((item) => statusFilter.value.includes(item.status));
    }

    return filtered;
});

// Calendar instance ref
const calendarInstance = ref<Calendar | null>(null);

// Initialize calendar
onMounted(() => {
    if (calendarEl.value) {
        calendarInstance.value = new Calendar(calendarEl.value, {
            plugins: [dayGridPlugin, interactionPlugin],
            initialView: 'dayGridMonth',
            events: filteredEvents.value,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,dayGridWeek',
            },
            eventClick: (info) => {
                window.location.href = info.event.url;
            },
            eventDidMount: (info) => {
                info.el.title = info.event.title;
            },
        });

        calendarInstance.value.render();
    }
});

// Watch for filter changes
watch([showProjects, showTasks, statusFilter], () => {
    if (calendarInstance.value) {
        calendarInstance.value.removeAllEvents();
        calendarInstance.value.addEventSource(filteredEvents.value);
    }
});

// Get unique statuses
const availableStatuses = computed(() => {
    const statuses = new Set<string>();
    props.projects.forEach((p) => statuses.add(p.status));
    props.tasks.forEach((t) => statuses.add(t.status));
    return Array.from(statuses);
});

// Toggle status filter
const toggleStatus = (status: string) => {
    if (statusFilter.value.includes(status)) {
        statusFilter.value = statusFilter.value.filter((s) => s !== status);
    } else {
        statusFilter.value.push(status);
    }
};
</script>

<template>
    <Head title="Calendar" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader>
                        <CardTitle class="text-center text-orange-600">Total Projects</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-center text-3xl font-bold">{{ summary.totalProjects }}</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="text-center text-blue-600">Total Tasks</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-center text-3xl font-bold">{{ summary.totalTasks }}</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="text-center text-yellow-600">Upcoming Deadlines</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-center text-3xl font-bold">{{ summary.upcomingDeadlines }}</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Filters -->
            <Card>
                <CardContent class="p-4">
                    <div class="flex flex-wrap items-center gap-6">
                        <!-- Type Filters -->
                        <div class="flex items-center gap-6">
                            <div class="flex items-center space-x-2">
                                <Switch id="show-projects" v-model:checked="showProjects" class="bg-orange-600" />
                                <Label for="show-projects">Show Projects</Label>
                            </div>

                            <div class="flex items-center space-x-2">
                                <Switch id="show-tasks" v-model:checked="showTasks" class="bg-blue-600" />
                                <Label for="show-tasks">Show Tasks</Label>
                            </div>
                        </div>

                        <!-- Status Filters -->
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="status in availableStatuses"
                                :key="status"
                                class="rounded-full px-3 py-1 text-sm"
                                :class="{
                                    'bg-orange-100 text-orange-700': statusFilter.includes(status),
                                    'bg-gray-100 text-gray-700': !statusFilter.includes(status),
                                }"
                                @click="toggleStatus(status)"
                            >
                                {{ status }}
                            </button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Calendar -->
            <Card>
                <CardContent class="p-6">
                    <div ref="calendarEl" class="min-h-[600px]"></div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<style>
/* Note: Removed :deep() and changed to global styles since FullCalendar loads dynamically */
.fc {
    @apply font-sans;
}

.fc-toolbar-title {
    @apply font-bold text-gray-900;
}

.fc-button-primary {
    @apply border-orange-600 bg-orange-600 hover:border-orange-700 hover:bg-orange-700 !important;
}

.fc-event {
    @apply cursor-pointer;
}

/* Additional styles for better calendar appearance */
.fc-day-today {
    @apply bg-orange-50 !important;
}

.fc-event-title {
    @apply font-medium;
}

.fc-header-toolbar {
    @apply mb-4;
}

/* Add styles for filters */
.status-filter-active {
    @apply bg-orange-100 text-orange-700;
}

.status-filter-inactive {
    @apply bg-gray-100 text-gray-700 hover:bg-gray-200;
}
</style>
