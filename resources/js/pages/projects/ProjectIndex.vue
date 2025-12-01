<script setup lang="ts">
import KanbanBoard from '@/components/KanbanBoard.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { ProjectStatus, ProjectStatusLabels } from '@/enums/ProjectStatus';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { CheckCircle2, ChevronUp, Clock, ListTodo, PlusCircle, TimerIcon, LayoutGrid, Table as TableIcon, Calendar, User, Menu, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { reactive } from 'vue';

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

interface BoardStage {
    id: number;
    name: string;
    color?: string;
    position?: number;
}

const props = defineProps<{
    projects: Project[];
    stages?: BoardStage[]; // new prop from server
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Projects', href: '/projects' },
];



console.log('Loaded projects:', props.projects, 'stages:', props.stages);

// fallback viewMode, etc...
const viewMode = ref<'table' | 'kanban'>('table');
const mobileMenuOpen = ref(false);
// stage modal
const showStageModal = ref(false);
const stageForm = reactive({
    name: '',
    color: '#60A5FA',
    position: null as number | null,

});

const submitStage = () => {
    router.post(route('users.board-stages.store'), {
        name: stageForm.name,
        color: stageForm.color,
        position: stageForm.position,
    }, {
        preserveState: true,
        onSuccess: () => {
            showStageModal.value = false;
            stageForm.name = '';
            stageForm.color = '#60A5FA';
            stageForm.position = null;
        },
    });
};

// Group projects by stages (use stages from props if present)
const groupedProjects = computed(() => {
    const stages = props.stages ?? [];

    console.log('Grouping projects by stages:', stages);
    return stages.map((s) => ({
        id: s.id,
        title: s.name,
        color: s.color || '#E5E7EB',
        tasks: (props.projects || []).filter((p: Project) => {
            return p.status === s.name;
        }).map((p: Project) => ({
            id: p.id,
            title: p.name,
            status: p.status,
            description: p.description,
            assignee: p.assignee?.name,
            start_date: p.start_date,
            end_date: p.end_date,
        })),
    }));
});

// Project counts for summary cards
const projectCounts = computed(() => ({
    pending: groupedProjects.value[0]?.tasks.length,
    inProgress: groupedProjects.value[1]?.tasks.length,
    completed: groupedProjects.value[2]?.tasks.length,
}));

const updateProjectStatus = (payload: { itemId: number; newStatus: string }) => {
    console.log('Updating project status:', payload);

    router.put(
        route('projects.status', { project: payload.itemId }),
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

// Format date
const formatDate = (date: string) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

// Calculate days until deadline
const daysUntilDeadline = (endDate: string) => {
    if (!endDate) return null;
    const today = new Date();
    const deadline = new Date(endDate);
    const diffTime = deadline.getTime() - today.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
};

// Get deadline color
const getDeadlineColor = (days: number | null) => {
    if (days === null) return 'text-gray-500';
    if (days < 0) return 'text-red-600 font-semibold';
    if (days <= 3) return 'text-red-500';
    if (days <= 7) return 'text-orange-500';
    return 'text-green-500';
};

// status options for select
const statusOptions = [ProjectStatus.Pending, ProjectStatus.InProgress, ProjectStatus.Completed] as string[];

/**
 * Change project status via API.
 * Uses existing updateProjectStatus helper to perform the PUT request.
 */
const changeProjectStatus = (projectId: number, newStatus: string) => {
    updateProjectStatus({ itemId: projectId, newStatus });
};

/**
 * Move a project to a different board stage.
 * payload expected: { itemId: number, newStageId: number }
 */
const moveProjectToStage = (payload: { itemId: number; newStatus: string }) => {
    console.log('Moving project to new stage:', payload);

    router.put(route('projects.update-status', { project: payload.itemId }), {
        status: payload.newStatus,
    }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            // optimistic UI update: find project and set board_stage_id
            const p = props.projects.find((x: any) => x.id === payload.itemId);
            //    if (p) p. = payload.newStatus;
        },
    });
};
</script>

<template>

    <Head title="Projects" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full space-y-6 px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
            <!-- Header with Create Button and View Toggle -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex-1">
                    <h1 class="text-xl font-bold text-gray-900 sm:text-2xl">Projects Overview</h1>
                    <p class="mt-1 text-xs text-gray-500 sm:text-sm">Manage and track all your projects in one place</p>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-4">
                    <!-- Mobile Menu Button -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen"
                        class="sm:hidden flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2">
                        <Menu v-if="!mobileMenuOpen" class="h-5 w-5" />
                        <X v-else class="h-5 w-5" />
                    </button>

                    <!-- Desktop Controls -->
                    <div class="hidden gap-4 sm:flex sm:items-center">
                        <!-- View Toggle Buttons -->
                        <div class="inline-flex rounded-lg border border-gray-300 bg-white p-1">
                            <Button :variant="viewMode === 'table' ? 'default' : 'ghost'" size="sm" class="gap-2"
                                @click="viewMode = 'table'">
                                <TableIcon class="h-4 w-4" />
                                <span class="hidden md:inline">Table</span>
                            </Button>
                            <Button :variant="viewMode === 'kanban' ? 'default' : 'ghost'" size="sm" class="gap-2"
                                @click="viewMode = 'kanban'">
                                <LayoutGrid class="h-4 w-4" />
                                <span class="hidden md:inline">Kanban</span>
                            </Button>
                        </div>
                        <Link :href="route('projects.create')">
                        <Button class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 whitespace-nowrap">
                            <PlusCircle class="h-4 w-4" />
                            <span class="hidden lg:inline">Create Project</span>
                            <span class="lg:hidden">Create</span>
                        </Button>
                        </Link>

                    </div>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div v-show="mobileMenuOpen" class="sm:hidden space-y-2">
                <div class="flex gap-2 rounded-lg border border-gray-300 bg-white p-1">
                    <Button :variant="viewMode === 'table' ? 'default' : 'ghost'" size="sm" class="flex-1 gap-2"
                        @click="viewMode = 'table'; mobileMenuOpen = false">
                        <TableIcon class="h-4 w-4" />
                        Table
                    </Button>
                    <Button :variant="viewMode === 'kanban' ? 'default' : 'ghost'" size="sm" class="flex-1 gap-2"
                        @click="viewMode = 'kanban'; mobileMenuOpen = false">
                        <LayoutGrid class="h-4 w-4" />
                        Kanban
                    </Button>
                </div>
                <Link :href="route('projects.create')">
                <Button class="w-full inline-flex items-center gap-2 bg-primary hover:bg-primary/90">
                    <PlusCircle class="h-4 w-4" />
                    Create Project
                </Button>
                </Link>
            </div>

            <!-- Project Summary Cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Card class="border-l-4 border-l-yellow-400">
                    <CardHeader class="pb-2">
                        <div class="flex items-center justify-between gap-2">
                            <CardTitle class="text-sm font-medium text-gray-700 sm:text-base">To Do</CardTitle>
                            <ListTodo class="h-4 w-4 text-yellow-500 sm:h-5 sm:w-5" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-baseline justify-between">
                            <span class="text-2xl font-bold text-gray-900 sm:text-3xl">{{ projectCounts.pending
                                }}</span>
                            <div class="flex items-center gap-1 text-xs text-yellow-600 sm:text-sm">
                                <ChevronUp class="h-3 w-3 sm:h-4 sm:w-4" />
                                <span>Pending</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="border-l-4 border-l-blue-400">
                    <CardHeader class="pb-2">
                        <div class="flex items-center justify-between gap-2">
                            <CardTitle class="text-sm font-medium text-gray-700 sm:text-base">In Progress</CardTitle>
                            <TimerIcon class="h-4 w-4 text-blue-500 sm:h-5 sm:w-5" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-baseline justify-between">
                            <span class="text-2xl font-bold text-gray-900 sm:text-3xl">{{ projectCounts.inProgress
                                }}</span>
                            <div class="flex items-center gap-1 text-xs text-blue-600 sm:text-sm">
                                <Clock class="h-3 w-3 sm:h-4 sm:w-4" />
                                <span>Active</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="border-l-4 border-l-green-400 sm:col-span-2 lg:col-span-1">
                    <CardHeader class="pb-2">
                        <div class="flex items-center justify-between gap-2">
                            <CardTitle class="text-sm font-medium text-gray-700 sm:text-base">Completed</CardTitle>
                            <CheckCircle2 class="h-4 w-4 text-green-500 sm:h-5 sm:w-5" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-baseline justify-between">
                            <span class="text-2xl font-bold text-gray-900 sm:text-3xl">{{ projectCounts.completed
                                }}</span>
                            <div class="text-xs text-green-600 sm:text-sm">
                                <span>Done</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Table View -->
            <div v-show="viewMode === 'table'" class="space-y-4">
                <div class="flex flex-col items-start justify-between gap-2 sm:flex-row sm:items-center">
                    <h2 class="text-lg font-semibold text-gray-900">All Projects</h2>
                    <div class="text-xs text-gray-500 sm:text-sm">{{ props.projects.length }} total projects</div>
                </div>

                <Card class="overflow-hidden">
                    <CardContent class="p-0">
                        <div class="overflow-x-auto">
                            <Table>


                                <TableHead class="min-w-[200px] sm:min-w-[250px]">Project Name</TableHead>
                                <TableHead class="min-w-[120px]">Status</TableHead>
                                <TableHead class="min-w-[100px] hidden sm:table-cell">Assignee</TableHead>
                                <TableHead class="min-w-[100px] hidden md:table-cell">Start Date</TableHead>
                                <TableHead class="min-w-[100px] hidden lg:table-cell">End Date</TableHead>
                                <TableHead class="min-w-[80px]">Days</TableHead>
                                <TableHead class="min-w-[80px] text-right">Actions</TableHead>


                                <TableBody>
                                    <TableRow v-for="project in props.projects" :key="project.id"
                                        class="hover:bg-gray-50">
                                        <TableCell class="max-w-xs">
                                            <Link :href="route('projects.show', project.id)"
                                                class="font-medium text-primary hover:underline truncate block">
                                            {{ project.name }}
                                            </Link>
                                            <p v-if="project.description" class="text-xs text-gray-500 line-clamp-1"
                                                v-html="project.description">
                                            </p>
                                        </TableCell>
                                        <TableCell>
                                            <div class="relative inline-block">
                                                <select :value="project.status"
                                                    @change="changeProjectStatus(project.id, $event.target.value)"
                                                    class="block w-full text-xs rounded-md border px-2 py-1 bg-white"
                                                    aria-label="Change project status">
                                                    <option v-for="s in statusOptions" :key="s" :value="s">
                                                        {{ ProjectStatusLabels[s as keyof typeof ProjectStatusLabels] ??
                                                            s }}
                                                    </option>
                                                </select>
                                                <!-- visual pill overlay for status color (non-interactive) -->
                                                <span
                                                    class="pointer-events-none absolute inset-0 flex items-center justify-center text-xs px-2 py-0.5 rounded"
                                                    :class="getStatusColor(project.status)">
                                                    {{ ProjectStatusLabels[project.status as keyof typeof
                                                        ProjectStatusLabels] ?? project.status }}
                                                </span>
                                            </div>
                                        </TableCell>


                                        <TableCell class="hidden sm:table-cell">
                                            <div v-if="project.assignee" class="flex items-center gap-2">
                                                <div
                                                    class="flex h-6 w-6 items-center justify-center rounded-full bg-orange-100 text-xs">
                                                    <span class="font-medium text-orange-700">
                                                        {{ project.assignee.name.charAt(0).toUpperCase() }}
                                                    </span>
                                                </div>
                                                <span class="text-xs sm:text-sm">{{ project.assignee.name }}</span>
                                            </div>
                                            <span v-else class="text-xs text-gray-500">-</span>
                                        </TableCell>
                                        <TableCell class="hidden md:table-cell">
                                            <div class="flex items-center gap-1 text-xs text-gray-600">
                                                <Calendar class="h-3 w-3" />
                                                {{ formatDate(project.start_date) }}
                                            </div>
                                        </TableCell>
                                        <TableCell class="hidden lg:table-cell">
                                            <div class="flex items-center gap-1 text-xs text-gray-600">
                                                <Calendar class="h-3 w-3" />
                                                {{ formatDate(project.end_date) }}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <span
                                                :class="`text-xs font-medium ${getDeadlineColor(daysUntilDeadline(project.end_date))}`">
                                                {{ daysUntilDeadline(project.end_date) !== null ?
                                                    `${daysUntilDeadline(project.end_date)}d` : '-' }}
                                            </span>
                                        </TableCell>
                                        <TableCell class="text-right">
                                            <Link :href="route('projects.show', project.id)">
                                            <Button variant="outline" size="sm" class="text-xs">View</Button>
                                            </Link>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>

                        <!-- Empty State -->
                        <div v-if="props.projects.length === 0"
                            class="flex h-48 flex-col items-center justify-center px-4 sm:h-64">
                            <ListTodo class="mb-4 h-10 w-10 text-gray-400 sm:h-12 sm:w-12" />
                            <p class="text-sm text-gray-500">No projects yet</p>
                            <Link :href="route('projects.create')">
                            <Button variant="outline" class="mt-4">Create your first project</Button>
                            </Link>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Kanban View -->
            <div v-show="viewMode === 'kanban'" class="space-y-4 max-w-[90%] overflow-x-auto">
                <div class="flex flex-col items-start justify-between gap-2 sm:flex-row sm:items-center">
                    <h2 class="text-lg font-semibold text-gray-900">Project Board</h2>
                    <div class="text-xs text-gray-500 sm:text-sm">{{ props.projects.length }} total projects</div>
                </div>


                <KanbanBoard :columns="groupedProjects" type="project"
                    @item-moved="(payload) => moveProjectToStage(payload)" class="min-h-[600px]">

                    <template #extra-columns>
                        <div class="w-full sm:w-[320px] md:w-[350px] flex-shrink-0 rounded-lg
                                bg-orange-50/50 p-4 shadow-sm">
                            <!-- Add Stage Button -->
                            <Button variant="outline" size="sm" @click="showStageModal = true">
                                + Add Stage
                            </Button>
                        </div>
                    </template>
                </KanbanBoard>


            </div>


            <Dialog v-model:open="showStageModal">
                <DialogContent class="max-w-md">
                    <DialogHeader>
                        <DialogTitle>Create new board stage</DialogTitle>
                    </DialogHeader>

                    <div class="space-y-3 py-2">
                        <label class="block text-xs font-medium text-gray-700">Name</label>
                        <input v-model="stageForm.name" type="text" class="w-full rounded border p-2"
                            placeholder="e.g. Backlog" />
                        <label class="block text-xs font-medium text-gray-700">Color</label>
                        <input v-model="stageForm.color" type="color" class="w-20 h-8 p-0 border rounded" />
                        <!-- <label class="block text-xs font-medium text-gray-700">Position (optional)</label>
                        <input v-model.number="stageForm.position" type="number" min="0" class="w-24 rounded border p-2"
                            placeholder="0" /> -->
                    </div>

                    <DialogFooter class="flex gap-2 justify-end">
                        <button type="button" class="px-3 py-1 rounded border bg-white"
                            @click="showStageModal = false">Cancel</button>
                        <button type="button" class="px-3 py-1 rounded bg-primary text-white"
                            @click="submitStage">Create</button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>


        </div>
    </AppLayout>
</template>

<style scoped>
.kanban-column {
    @apply rounded-lg bg-gray-50 p-4;
}

.kanban-card {
    @apply mb-3 cursor-move rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition-shadow hover:shadow-md;
}

/* Line clamp for description */
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Responsive adjustments */
@media (max-width: 640px) {
    :deep(.table) {
        font-size: 0.875rem;
    }
}

@media (max-width: 768px) {
    :deep(.table) {
        font-size: 0.8125rem;
    }
}
</style>
