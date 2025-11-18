<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import { Calendar, CheckCircle2, Clock, ListTodo, Search, Users } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

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
    assigneeTo: {
        id: number;
        name: string;
    };
    createdBy: {
        id: number;
        name: string;
    };
}

interface PaginatedTasks {
    data: Task[];

    current_page: number;
    from: number;
    last_page: number;
    per_page: number;
    to: number;
    total: number;
}

const props = defineProps<{
    tasks: PaginatedTasks;
}>();

const activeFilter = ref('all');
const searchQuery = ref('');

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'My Tasks', href: '/tasks' },
];

const taskStats = computed(() => ({
    total: {
        count: props.tasks.total,
        status: 'all',
        color: 'border-gray-400',
        icon: ListTodo,
        label: 'Total Tasks',
    },
    pending: {
        count: props.tasks.data.filter((t) => t.status === 'pending').length,
        status: 'pending',
        color: 'border-yellow-400',
        icon: Clock,
        label: 'Pending',
    },
    inProgress: {
        count: props.tasks.data.filter((t) => t.status === 'in_progress').length,
        status: 'in_progress',
        color: 'border-blue-400',
        icon: Users,
        label: 'In Progress',
    },
    completed: {
        count: props.tasks.data.filter((t) => t.status === 'completed').length,
        status: 'completed',
        color: 'border-green-400',
        icon: CheckCircle2,
        label: 'Completed',
    },
}));

const filteredTasks = computed(() => {
    let filtered = props.tasks.data;

    // Filter by status
    if (activeFilter.value !== 'all') {
        filtered = filtered.filter((task) => task.status === activeFilter.value);
    }

    // Filter by search query
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(
            (task) =>
                task.name.toLowerCase().includes(query) ||
                task.description.toLowerCase().includes(query) ||
                task.project.name.toLowerCase().includes(query),
        );
    }

    return filtered;
});

const handleFilterClick = (status: string) => {
    activeFilter.value = status;
};

const debouncedSearch = debounce((value: string) => {
    router.get(route('tasks.index'), { search: value, status: activeFilter.value }, { preserveState: true, preserveScroll: true });
}, 300);

watch(searchQuery, (value) => {
    debouncedSearch(value);
});

const getStatusColor = (status: string) => {
    switch (status) {
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'in_progress':
            return 'bg-blue-100 text-blue-800';
        case 'completed':
            return 'bg-green-100 text-green-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const isOverdue = (dueDate: string) => {
    return new Date(dueDate) < new Date();
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

    <Head title="My Tasks" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header Section -->
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">My Tasks</h1>
                    <p class="mt-1 text-sm text-gray-500">View and manage your assigned tasks</p>
                </div>
                <div class="relative w-72">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <Search class="h-4 w-4 text-gray-400" />
                    </div>
                    <input v-model="searchQuery" type="search" placeholder="Search tasks..."
                        class="block w-full rounded-md border border-gray-200 py-2 pl-10 pr-3 text-sm placeholder:text-gray-500 focus:border-orange-500 focus:outline-none focus:ring-1 focus:ring-orange-500" />
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid gap-6 sm:grid-cols-4">
                <Card v-for="(stat, key) in taskStats" :key="key" :class="[
                    'cursor-pointer border-l-4 transition-all duration-200 hover:shadow-md',
                    stat.color,
                    activeFilter === stat.status ? 'ring-2 ring-orange-200' : '',
                ]" @click="handleFilterClick(stat.status)">
                    <CardHeader class="pb-2">
                        <CardTitle class="flex items-center gap-2 text-sm font-medium text-gray-500">
                            <component :is="stat.icon" class="h-4 w-4"
                                :class="key === 'total' ? 'text-gray-400' : `text-${stat.color.split('-')[1]}-500`" />
                            {{ stat.label }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-gray-900">{{ stat.count }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Tasks Grid -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Card v-for="task in filteredTasks" :key="task.id"
                    class="transition-all duration-200 hover:border-gray-300 hover:shadow-lg">
                    <CardHeader class="pb-3">
                        <div class="flex items-start justify-between">
                            <div>
                                <CardTitle class="line-clamp-1">{{ task.name }}</CardTitle>
                                <Link :href="route('projects.show', task.project.id)"
                                    class="mt-1 text-sm text-orange-600 hover:underline">
                                {{ task.project.name }}
                                </Link>
                            </div>
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium"
                                :class="getStatusColor(task.status)">
                                {{ task.status.replace('_', ' ') }}
                            </span>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <p class="line-clamp-2 text-sm text-gray-600">{{ task.description }}</p>

                        <div class="flex items-center gap-2">
                            <Calendar class="h-4 w-4 text-gray-400" />
                            <span class="text-sm" :class="{ 'text-red-600': isOverdue(task.due_date) }"> Due {{
                                formatDate(task.due_date) }} </span>
                        </div>

                        <div class="flex justify-end gap-2 pt-2">
                            <Link :href="route('tasks.show', task.id)">
                            <Button variant="default" size="sm">View Details</Button>
                            </Link>
                        </div>
                    </CardContent>
                </Card>

                <!-- Empty State -->
                <div v-if="filteredTasks.length === 0"
                    class="col-span-full flex flex-col items-center justify-center p-8 text-center">
                    <div class="rounded-full bg-gray-50 p-3">
                        <Search v-if="searchQuery" class="h-6 w-6 text-gray-400" />
                        <ListTodo v-else class="h-6 w-6 text-gray-400" />
                    </div>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No tasks found</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        <template v-if="searchQuery">
                            No tasks match your search "{{ searchQuery }}"
                            <template v-if="activeFilter !== 'all'"> with status "{{ activeFilter.replace('_', ' ') }}"
                            </template>
                        </template>
                        <template v-else>
                            {{ activeFilter === 'all' ? "You don't have any tasks yet." : `No
                            ${activeFilter.replace('_', ' ')} tasks found.` }}
                        </template>
                    </p>
                </div>
            </div>

            <!-- Active Filter Indicator -->
            <div v-if="activeFilter !== 'all'" class="flex items-center gap-2 pt-4">
                <span class="text-sm text-gray-500">Filtered by:</span>
                <span
                    class="inline-flex items-center gap-1 rounded-full bg-orange-50 px-2 py-1 text-xs font-medium text-orange-600">
                    {{ activeFilter.replace('_', ' ') }}
                    <button @click="activeFilter = 'all'" class="ml-1 rounded-full p-0.5 hover:bg-orange-100">
                        <span class="sr-only">Clear filter</span>
                        <svg class="h-3 w-3" viewBox="0 0 12 12" fill="none">
                            <path d="M8 4l-4 4m0-4l4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </button>
                </span>
            </div>

            <!-- Pagination -->
            <div v-if="tasks.last_page > 1" class="flex items-center justify-between border-t pt-4">
                <p class="text-sm text-gray-500">Showing {{ tasks.from }} to {{ tasks.to }} of {{ tasks.total }} tasks
                </p>
                <div class="flex gap-2">
                    <Link v-for="page in tasks.last_page" :key="page" :href="route('tasks.index', { page })"
                        class="rounded-md px-3 py-2 text-sm"
                        :class="page === tasks.current_page ? 'bg-orange-50 font-medium text-orange-600' : 'hover:bg-gray-50'">
                    {{ page }}
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.line-clamp-1 {
    overflow: hidden;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 1;
}

.line-clamp-2 {
    overflow: hidden;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
}
</style>
