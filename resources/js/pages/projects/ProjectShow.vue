<script setup lang="ts">
import FileIcon from '@/components/FileIcon.vue';
import KanbanBoard from '@/components/KanbanBoard.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { ProjectStatus, ProjectStatusLabels } from '@/enums/ProjectStatus';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Calendar, Download, FileX, Info, Paperclip, Plus, Trash2, User, Users, Menu, X, Eye, Check, MessageCircle, Clock } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Task {
    id: number;
    name: string;
    description: string;
    status: string;
    due_date: string;
    assignee?: {
        name: string;
    };
    type?: 'task';
}

interface Attachment {
    id: number;
    name: string;
    file_path: string;
    file_type: string;
    size: number;
    created_at: string;
    user: {
        name: string;
    };
}

interface Project {
    id: number;
    name: string;
    description: string;
    start_date: string;
    end_date: string;
    status: string;
    tasks: Task[];
    assignee: {
        name: string;
    } | null;
    creator: {
        name: string;
    };
    attachments: Attachment[];
}

const props = defineProps<{
    project: Project;
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Projects', href: '/projects' },
    { title: props.project.name, href: '#' },
];

const mobileMenuOpen = ref(false);
const drawerOpen = ref(false);
const selectedTask = ref<Task | null>(null);

// Hardcoded task comments
const taskComments = {
    1: [
        { id: 1, author: 'John Doe', avatar: 'J', content: 'Started working on this task', timestamp: '2 hours ago' },
        { id: 2, author: 'Sarah Smith', avatar: 'S', content: 'Great progress! Keep it up', timestamp: '1 hour ago' },
        { id: 3, author: 'Mike Johnson', avatar: 'M', content: 'Please review the latest changes', timestamp: '30 minutes ago' },
    ],
    2: [
        { id: 1, author: 'Sarah Smith', avatar: 'S', content: 'Task created', timestamp: '1 day ago' },
        { id: 2, author: 'John Doe', avatar: 'J', content: 'I can help with this', timestamp: '12 hours ago' },
    ],
    3: [
        { id: 1, author: 'Mike Johnson', avatar: 'M', content: 'Assigned to me', timestamp: '3 days ago' },
    ],
};

// Hardcoded task attachments
const taskAttachments = {
    1: [
        { id: 1, name: 'project-requirements.pdf', size: 2048000, type: 'pdf', user: { name: 'John Doe' }, created_at: '2024-01-15' },
        { id: 2, name: 'design-mockup.fig', size: 5120000, type: 'fig', user: { name: 'Sarah Smith' }, created_at: '2024-01-14' },
    ],
    2: [
        { id: 1, name: 'api-documentation.docx', size: 1024000, type: 'docx', user: { name: 'Mike Johnson' }, created_at: '2024-01-13' },
    ],
    3: [],
};

// Group tasks by status
const groupedTasks = computed(() => {
    const pending = props.project.tasks?.filter((t) => t.status === ProjectStatus.Pending) || [];
    const inProgress = props.project.tasks?.filter((t) => t.status === ProjectStatus.InProgress) || [];
    const completed = props.project.tasks?.filter((t) => t.status === ProjectStatus.Completed) || [];

    return [
        {
            id: 1,
            title: ProjectStatusLabels[ProjectStatus.Pending],
            tasks: pending.map((t) => ({
                id: t.id,
                title: t.name,
                status: t.status,
                description: t.description,
                assignee: t.assignee?.name,
                end_date: t.due_date,
                type: 'task',
            })),
        },
        {
            id: 2,
            title: ProjectStatusLabels[ProjectStatus.InProgress],
            tasks: inProgress.map((t) => ({
                id: t.id,
                title: t.name,
                status: t.status,
                description: t.description,
                assignee: t.assignee?.name,
                end_date: t.due_date,
                type: 'task',
            })),
        },
        {
            id: 3,
            title: ProjectStatusLabels[ProjectStatus.Completed],
            tasks: completed.map((t) => ({
                id: t.id,
                title: t.name,
                status: t.status,
                description: t.description,
                assignee: t.assignee?.name,
                end_date: t.due_date,
                type: 'task',
            })),
        },
    ];
});

const getStatusColor = (status: string) => {
    switch (status.toLowerCase()) {
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'in progress':
            return 'bg-blue-100 text-blue-800';
        case 'completed':
            return 'bg-green-100 text-green-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const updateTaskStatus = (payload: { itemId: number; newStatus: string }) => {
    console.log('Updating task status:', payload);

    router.put(
        route('tasks.update-status', payload.itemId),
        {
            status: payload.newStatus,
        },
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                console.log('Task status updated successfully');
            },
            onError: (errors) => {
                console.error('Failed to update task status:', errors);
            },
        });

};

// Drawer functions
const openTaskDrawer = (task: any) => {
    selectedTask.value = task;
    drawerOpen.value = true;
};

const closeDrawer = () => {
    drawerOpen.value = false;
    selectedTask.value = null;
};

const markAsDone = () => {
    if (selectedTask.value) {
        updateTaskStatus({
            itemId: selectedTask.value.id,
            newStatus: ProjectStatus.Completed,
        });
        closeDrawer();
    }
};

// File input and attachment handling
const fileInput = ref<HTMLInputElement | null>(null);

const openFileInput = () => {
    fileInput.value?.click();
};

const handleFileUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const files = target.files;
    if (!files?.length) return;

    const formData = new FormData();
    Array.from(files).forEach((file) => {
        formData.append('files[]', file);
    });

    router.post(route('projects.attachments.store', props.project.id), formData, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            if (fileInput.value) {
                fileInput.value.value = '';
            }
        },
    });
};

const formatFileSize = (bytes: number) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return `${parseFloat((bytes / Math.pow(k, i)).toFixed(2))} ${sizes[i]}`;
};

const downloadAttachment = (attachment: Attachment) => {
    window.open(route('projects.attachments.download', [props.project.id, attachment.id]));
};

const deleteAttachment = (attachmentId: number) => {
    if (!confirm('Are you sure you want to delete this attachment?')) return;

    router.delete(route('projects.attachments.destroy', [props.project.id, attachmentId]), {
        preserveScroll: true,
        preserveState: true,
    });
};

// Get comments for task
const getTaskComments = (taskId: number) => {
    return taskComments[taskId as keyof typeof taskComments] || [];
};

// Get attachments for task
const getTaskAttachments = (taskId: number) => {
    return taskAttachments[taskId as keyof typeof taskAttachments] || [];
};
</script>

<template>

    <Head :title="project.name" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full space-y-6 px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
            <!-- Project Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="flex-1 min-w-0">
                    <h1 class="text-xl font-bold text-gray-900 sm:text-2xl lg:text-3xl truncate">{{ project.name }}</h1>
                    <p class="mt-1 text-xs sm:text-sm text-gray-500">Created by {{ project.creator?.name }}</p>
                </div>

                <!-- Mobile Menu Button -->
                <button @click="mobileMenuOpen = !mobileMenuOpen"
                    class="sm:hidden flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 self-start">
                    <Menu v-if="!mobileMenuOpen" class="h-5 w-5" />
                    <X v-else class="h-5 w-5" />
                </button>

                <!-- Desktop Action Buttons -->
                <div class="hidden sm:flex flex-col sm:flex-row gap-2 sm:gap-3">
                    <Button variant="outline" :href="route('projects.edit', project.id)" size="sm"
                        class="text-xs sm:text-sm">
                        Edit Project
                    </Button>
                    <Button variant="default" size="sm" class="text-xs sm:text-sm">
                        Share
                    </Button>
                    <Link :href="route('projects.timeline', project.id)">
                    <Button variant="outline" size="sm" class="text-xs sm:text-sm">View Timeline</Button>
                    </Link>
                </div>
            </div>

            <!-- Mobile Action Menu -->
            <div v-show="mobileMenuOpen" class="sm:hidden space-y-2">
                <Button variant="outline" :href="route('projects.edit', project.id)" class="w-full justify-start">
                    Edit Project
                </Button>
                <Button variant="default" class="w-full justify-start">
                    Share
                </Button>
                <Link :href="route('projects.timeline', project.id)">
                <Button variant="outline" class="w-full justify-start">View Timeline</Button>
                </Link>
            </div>

            <!-- Project Details Grid -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Status Card -->
                <Card class="col-span-1">
                    <CardContent class="pt-4 sm:pt-6">
                        <div class="flex items-center space-x-2">
                            <Info class="h-4 w-4 text-gray-500 flex-shrink-0" />
                            <h3 class="text-xs sm:text-sm font-medium text-gray-700">Status</h3>
                        </div>
                        <div class="mt-2 sm:mt-3">
                            <span
                                :class="['rounded-full px-2.5 py-0.5 text-xs sm:text-sm font-medium', getStatusColor(project.status)]">
                                {{ project.status }}
                            </span>
                        </div>
                    </CardContent>
                </Card>

                <!-- Assignee Card -->
                <Card class="col-span-1">
                    <CardContent class="pt-4 sm:pt-6">
                        <div class="flex items-center space-x-2">
                            <User class="h-4 w-4 text-gray-500 flex-shrink-0" />
                            <h3 class="text-xs sm:text-sm font-medium text-gray-700">Assignee</h3>
                        </div>
                        <div class="mt-2 sm:mt-3">
                            <div class="flex items-center space-x-2 min-w-0">
                                <div
                                    class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-gray-200">
                                    <span class="text-xs sm:text-sm font-medium text-gray-600">
                                        {{ project.assignee?.name?.[0] || 'U' }}
                                    </span>
                                </div>
                                <span class="text-xs sm:text-sm text-gray-900 truncate">
                                    {{ project.assignee?.name || 'Unassigned' }}
                                </span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Dates Card -->
                <Card class="col-span-1 sm:col-span-2 lg:col-span-2">
                    <CardContent class="pt-4 sm:pt-6">
                        <div class="flex items-center space-x-2">
                            <Calendar class="h-4 w-4 text-gray-500 flex-shrink-0" />
                            <h3 class="text-xs sm:text-sm font-medium text-gray-700">Timeline</h3>
                        </div>
                        <div class="mt-2 sm:mt-3 grid grid-cols-2 gap-2 sm:gap-4">
                            <div>
                                <p class="text-xs text-gray-500">Start Date</p>
                                <p class="text-xs sm:text-sm font-medium text-gray-900">{{ project.start_date
                                    || 'Not set' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">End Date</p>
                                <p class="text-xs sm:text-sm font-medium text-gray-900">{{ project.end_date || 'Not set'
                                    }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Description Section -->
            <Card>
                <CardHeader class="pb-3 sm:pb-4">
                    <CardTitle class="text-base sm:text-lg">Description</CardTitle>
                </CardHeader>
                <CardContent class="text-xs sm:text-sm">
                    <div class="prose prose-sm max-w-none">
                        <div v-html="project.description || 'No description provided'" />
                    </div>
                </CardContent>
            </Card>

            <!-- Tasks Section -->
            <div class="space-y-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center space-x-2">
                        <Users class="h-4 w-4 sm:h-5 sm:w-5 text-gray-500" />
                        <h2 class="text-base sm:text-lg font-semibold text-gray-900">Tasks</h2>
                    </div>
                    <Link :href="route('tasks.create', { project_id: project.id })">
                    <Button variant="default" size="sm" class="text-xs sm:text-sm">Add Task</Button>
                    </Link>
                </div>
                <div class="overflow-x-auto">
                    <KanbanBoard :columns="groupedTasks" type="task" @item-moved="updateTaskStatus"
                        class="min-h-[500px]">
                        <template #actions="{ item }">
                            <button @click="openTaskDrawer(item)"
                                class="p-1 text-gray-400 hover:text-orange-600 transition-colors">
                                <Eye class="h-4 w-4" />
                            </button>
                        </template>
                    </KanbanBoard>
                </div>
            </div>

            <!-- Attachments Section -->
            <Card>
                <CardHeader class="pb-3 sm:pb-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <CardTitle class="flex items-center gap-2 text-base sm:text-lg">
                            <Paperclip class="h-4 w-4 sm:h-5 sm:w-5 text-gray-500 flex-shrink-0" />
                            <span>Attachments</span>
                        </CardTitle>
                        <div class="flex items-center gap-2 sm:gap-3">
                            <p class="text-xs sm:text-sm text-gray-500">{{ project.attachments?.length || 0 }} files</p>
                            <Button variant="outline" size="sm" @click="openFileInput" class="text-xs sm:text-sm">
                                <Plus class="mr-1 sm:mr-2 h-3 w-3 sm:h-4 sm:w-4" />
                                <span class="hidden sm:inline">Add</span>
                                <span class="sm:hidden">+</span>
                            </Button>
                            <!-- Hidden file input -->
                            <input type="file" ref="fileInput" class="hidden" @change="handleFileUpload" multiple
                                accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" />
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <!-- Upload Area -->
                    <div class="mb-6 rounded-lg border-2 border-dashed border-gray-200 p-4 sm:p-6">
                        <div class="flex flex-col items-center justify-center">
                            <FileX class="h-8 w-8 sm:h-12 sm:w-12 text-gray-400" />
                            <p class="mt-2 text-xs sm:text-sm text-gray-500">Drag and drop files here or</p>
                            <Button variant="outline" size="sm" class="mt-3 sm:mt-4 text-xs sm:text-sm"
                                @click="openFileInput">
                                Browse Files
                            </Button>
                            <p class="mt-2 text-xs text-gray-400">
                                Supported: PDF, Word, Excel, Images • Max: 10MB
                            </p>
                        </div>
                    </div>

                    <!-- Attachments List -->
                    <div v-if="project.attachments?.length" class="space-y-4">
                        <div class="flex items-center justify-between border-b pb-2">
                            <h3 class="text-xs sm:text-sm font-medium text-gray-900">Attached Files</h3>
                            <p class="text-xs text-gray-500 hidden sm:block">Size</p>
                        </div>
                        <div class="divide-y space-y-2 sm:space-y-0">
                            <div v-for="attachment in project.attachments" :key="attachment.id"
                                class="group flex flex-col sm:flex-row sm:items-center sm:justify-between py-3 gap-3 sm:gap-4">
                                <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                                    <div
                                        class="flex h-8 w-8 sm:h-10 sm:w-10 flex-shrink-0 items-center justify-center rounded-lg bg-gray-50">
                                        <FileIcon :type="attachment.type" class="h-4 w-4 sm:h-5 sm:w-5 text-gray-500" />
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h4
                                            class="cursor-pointer text-xs sm:text-sm font-medium text-gray-900 group-hover:text-orange-600 truncate">
                                            {{ attachment.name }}
                                        </h4>
                                        <p class="text-xs text-gray-500 truncate">
                                            {{ attachment.user.name }} •
                                            {{ new Date(attachment.created_at).toLocaleDateString() }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 sm:gap-4 justify-between sm:justify-end">
                                    <span class="text-xs sm:text-sm text-gray-500 flex-shrink-0">
                                        {{ formatFileSize(attachment.size) }}
                                    </span>
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <Button variant="ghost" size="sm" class="text-gray-500 hover:text-gray-700 p-1"
                                            @click="downloadAttachment(attachment)">
                                            <Download class="h-4 w-4" />
                                        </Button>
                                        <Button variant="ghost" size="sm" class="text-red-500 hover:text-red-700 p-1"
                                            @click="deleteAttachment(attachment.id)">
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else-if="!project.attachments?.length" class="mt-4 text-center">
                        <p class="text-xs sm:text-sm text-gray-500">No attachments yet</p>
                    </div>
                </CardContent>
            </Card>
        </div>



    </AppLayout>
</template>

<style scoped>
.prose h1 {
    @apply mb-3 sm:mb-4 mt-4 sm:mt-6 text-xl sm:text-3xl font-bold;
}

.prose p {
    @apply mb-3 sm:mb-4 text-xs sm:text-gray-600;
}

.prose br {
    @apply block h-2 sm:h-4 content-[''];
}

/* Responsive typography */
@media (max-width: 640px) {
    .prose {
        font-size: 0.875rem;
    }
}

/* Drawer Animations */
.drawer-enter-active,
.drawer-leave-active {
    transition: opacity 0.3s ease;
}

.drawer-enter-from,
.drawer-leave-to {
    opacity: 0;
}

.slide-enter-active,
.slide-leave-active {
    transition: transform 0.3s ease;
}

.slide-enter-from,
.slide-leave-to {
    transform: translateX(100%);
}
</style>
