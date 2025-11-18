<script setup lang="ts">
import FileIcon from '@/components/FileIcon.vue';
import KanbanBoard from '@/components/KanbanBoard.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Drawer, DrawerClose, DrawerContent, DrawerFooter, DrawerHeader, DrawerTitle, DrawerTrigger } from '@/components/ui/drawer';
import { ProjectStatus, ProjectStatusLabels } from '@/enums/ProjectStatus';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Calendar, Download, Eye, FileX, Info, Menu, Paperclip, Plus, Trash2, User, Users, X } from 'lucide-vue-next';
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
    3: [{ id: 1, author: 'Mike Johnson', avatar: 'M', content: 'Assigned to me', timestamp: '3 days ago' }],
};

// Hardcoded task attachments
const taskAttachments = {
    1: [
        { id: 1, name: 'project-requirements.pdf', size: 2048000, type: 'pdf', user: { name: 'John Doe' }, created_at: '2024-01-15' },
        { id: 2, name: 'design-mockup.fig', size: 5120000, type: 'fig', user: { name: 'Sarah Smith' }, created_at: '2024-01-14' },
    ],
    2: [{ id: 1, name: 'api-documentation.docx', size: 1024000, type: 'docx', user: { name: 'Mike Johnson' }, created_at: '2024-01-13' }],
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
        },
    );
};

// Drawer functions
const openTaskDrawer = (task: any) => {
    console.log('Opening drawer for task:', task);
    selectedTask.value = task;
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
        <div class="px-4 py-6 space-y-6 w-full sm:px-6 sm:py-8 lg:px-8">
            <!-- Project Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="flex-1 min-w-0">
                    <h1 class="text-xl font-bold text-gray-900 truncate sm:text-2xl lg:text-3xl">{{ project.name }}</h1>
                    <p class="mt-1 text-xs text-gray-500 sm:text-sm">Created by {{ project.creator?.name }}</p>
                </div>

                <!-- Mobile Menu Button -->
                <button
                    @click="mobileMenuOpen = !mobileMenuOpen"
                    class="flex gap-2 items-center self-start px-3 py-2 bg-white rounded-lg border border-gray-300 sm:hidden"
                >
                    <Menu v-if="!mobileMenuOpen" class="w-5 h-5" />
                    <X v-else class="w-5 h-5" />
                </button>

                <!-- Desktop Action Buttons -->
                <div class="hidden flex-col gap-2 sm:flex sm:flex-row sm:gap-3">
                    <Button variant="outline" :href="route('projects.edit', project.id)" size="sm" class="text-xs sm:text-sm"> Edit Project </Button>
                    <Button variant="default" size="sm" class="text-xs sm:text-sm"> Share </Button>
                    <Link :href="route('projects.timeline', project.id)">
                        <Button variant="outline" size="sm" class="text-xs sm:text-sm">View Timeline</Button>
                    </Link>
                </div>
            </div>

            <!-- Mobile Action Menu -->
            <div v-show="mobileMenuOpen" class="space-y-2 sm:hidden">
                <Button variant="outline" :href="route('projects.edit', project.id)" class="justify-start w-full"> Edit Project </Button>
                <Button variant="default" class="justify-start w-full"> Share </Button>
                <Link :href="route('projects.timeline', project.id)">
                    <Button variant="outline" class="justify-start w-full">View Timeline</Button>
                </Link>
            </div>

            <!-- Project Details Grid -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Status Card -->
                <Card class="col-span-1">
                    <CardContent class="pt-4 sm:pt-6">
                        <div class="flex items-center space-x-2">
                            <Info class="flex-shrink-0 w-4 h-4 text-gray-500" />
                            <h3 class="text-xs font-medium text-gray-700 sm:text-sm">Status</h3>
                        </div>
                        <div class="mt-2 sm:mt-3">
                            <span :class="['rounded-full px-2.5 py-0.5 text-xs font-medium sm:text-sm', getStatusColor(project.status)]">
                                {{ project.status }}
                            </span>
                        </div>
                    </CardContent>
                </Card>

                <!-- Assignee Card -->
                <Card class="col-span-1">
                    <CardContent class="pt-4 sm:pt-6">
                        <div class="flex items-center space-x-2">
                            <User class="flex-shrink-0 w-4 h-4 text-gray-500" />
                            <h3 class="text-xs font-medium text-gray-700 sm:text-sm">Assignee</h3>
                        </div>
                        <div class="mt-2 sm:mt-3">
                            <div class="flex items-center space-x-2 min-w-0">
                                <div class="flex flex-shrink-0 justify-center items-center w-8 h-8 bg-gray-200 rounded-full">
                                    <span class="text-xs font-medium text-gray-600 sm:text-sm">
                                        {{ project.assignee?.name?.[0] || 'U' }}
                                    </span>
                                </div>
                                <span class="text-xs text-gray-900 truncate sm:text-sm">
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
                            <Calendar class="flex-shrink-0 w-4 h-4 text-gray-500" />
                            <h3 class="text-xs font-medium text-gray-700 sm:text-sm">Timeline</h3>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mt-2 sm:mt-3 sm:gap-4">
                            <div>
                                <p class="text-xs text-gray-500">Start Date</p>
                                <p class="text-xs font-medium text-gray-900 sm:text-sm">{{ project.start_date || 'Not set' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">End Date</p>
                                <p class="text-xs font-medium text-gray-900 sm:text-sm">{{ project.end_date || 'Not set' }}</p>
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
                    <div class="max-w-none prose prose-sm">
                        <div v-html="project.description || 'No description provided'" />
                    </div>
                </CardContent>
            </Card>

            <!-- Tasks Section -->
            <div class="space-y-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center space-x-2">
                        <Users class="w-4 h-4 text-gray-500 sm:h-5 sm:w-5" />
                        <h2 class="text-base font-semibold text-gray-900 sm:text-lg">Tasks</h2>
                    </div>
                    <Link :href="route('tasks.create', { project_id: project.id })">
                        <Button variant="default" size="sm" class="text-xs sm:text-sm">Add Task</Button>
                    </Link>
                </div>
                <div class="overflow-x-auto">
                    <KanbanBoard :columns="groupedTasks" type="task" @item-moved="updateTaskStatus" class="min-h-[500px]">
                        <template #actions="{ item }">
                            <Drawer>
                                <DrawerTrigger as-child @click="openTaskDrawer(item)">
                                    <button class="p-1 text-gray-400 transition-colors hover:text-orange-600">
                                        <Eye class="w-4 h-4" />
                                    </button>
                                </DrawerTrigger>
                                <DrawerContent class="max-w-1/2 min-w-96" position="right" width="50">
                                    <div v-if="selectedTask" class="max-h-[90vh] p-5 w-full overflow-y-auto">
                                        <div class="mx-auto mt-4 w-full h-2 rounded-full bg-muted" />

                                        <DrawerHeader class="pt-4">
                                            <DrawerTitle class="text-xl font-bold text-left">
                                                {{ selectedTask?.name }}
                                            </DrawerTitle>

                                            <div class="flex gap-4 items-center pt-2">
                                                <Badge :class="getStatusColor(selectedTask.status)">
                                                    {{ ProjectStatusLabels[selectedTask.status] || selectedTask?.status }}
                                                </Badge>

                                                <Button variant="ghost" size="sm" class="p-1 h-auto text-muted-foreground">
                                                    <span class="text-sm">Normal</span>
                                                </Button>
                                            </div>
                                        </DrawerHeader>

                                        <div class="flex gap-2 w-full">
                                            <div class="w-1/2 border-t border-b divide-y">
                                                <div class="grid grid-cols-[24px_1fr] items-start gap-4 px-4 py-3">
                                                    <Users class="mt-1 w-5 h-5 text-muted-foreground" />
                                                    <div>
                                                        <h4 class="text-sm font-medium text-muted-foreground">Assignees</h4>
                                                        <div class="flex flex-col gap-2 mt-2">
                                                            <div v-if="selectedTask.assignee" class="flex gap-2 items-center">
                                                                <span
                                                                    class="flex justify-center items-center w-6 h-6 text-xs font-medium text-gray-600 bg-gray-200 rounded-full"
                                                                >
                                                                    {{ selectedTask.assignee.name.charAt(0) }}
                                                                </span>
                                                                <span class="text-sm">{{ selectedTask.assignee.name }}</span>
                                                            </div>
                                                            <div v-else class="text-sm text-gray-500">Unassigned</div>

                                                            <Button
                                                                variant="ghost"
                                                                size="sm"
                                                                class="justify-start p-0 h-auto text-sm text-blue-600 hover:text-blue-700"
                                                            >
                                                                + Add or change
                                                            </Button>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-[24px_1fr] items-start gap-4 px-4 py-3">
                                                    <Calendar class="mt-1 w-5 h-5 text-muted-foreground" />
                                                    <div>
                                                        <h4 class="text-sm font-medium text-muted-foreground">Start & Due date</h4>
                                                        <div class="mt-2 space-y-1">
                                                            <div class="text-sm"><span class="text-gray-600">Start:</span> N/A</div>
                                                            <div class="text-sm">
                                                                <span class="text-gray-600">Due:</span> {{ selectedTask.due_date || 'Not set' }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-[24px_1fr] items-start gap-4 px-4 py-3">
                                                    <FileText class="mt-1 w-5 h-5 text-muted-foreground" />
                                                    <div>
                                                        <h4 class="text-sm font-medium text-muted-foreground">Description</h4>
                                                        <div
                                                            v-if="selectedTask.description"
                                                            class="mt-2 text-sm text-gray-800 prose prose-sm"
                                                            v-html="selectedTask.description"
                                                        />
                                                        <p v-else class="mt-2 text-sm text-gray-500">No description provided.</p>
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-[24px_1fr] items-start gap-4 px-4 py-3">
                                                    <Paperclip class="mt-1 w-5 h-5 text-muted-foreground" />
                                                    <div>
                                                        <h4 class="text-sm font-medium text-muted-foreground">Attachments</h4>
                                                        <div class="mt-2">
                                                            <div v-if="getTaskAttachments(selectedTask.id).length" class="space-y-2">
                                                                <div
                                                                    v-for="att in getTaskAttachments(selectedTask.id)"
                                                                    :key="att.id"
                                                                    class="flex justify-between items-center p-2 rounded-lg border"
                                                                >
                                                                    <div class="flex gap-2 items-center">
                                                                        <FileIcon :type="att.type" class="flex-shrink-0 w-5 h-5 text-gray-500" />
                                                                        <div>
                                                                            <p class="text-sm font-medium text-gray-900">{{ att.name }}</p>
                                                                            <p class="text-xs text-gray-500">{{ formatFileSize(att.size) }}</p>
                                                                        </div>
                                                                    </div>
                                                                    <Button variant="ghost" size="sm" class="p-1 text-gray-500">
                                                                        <Download class="w-4 h-4" />
                                                                    </Button>
                                                                </div>
                                                            </div>
                                                            <p v-else class="text-sm text-gray-500">No attachments for this task.</p>
                                                            <Button variant="outline" size="sm" class="mt-2 text-xs"> Add attachment </Button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="px-4 py-4 w-1/2">
                                                <div class="flex gap-2 items-center mb-3">
                                                    <List class="w-5 h-5 text-muted-foreground" />
                                                    <h4 class="font-semibold text-gray-800 text-md">Activity</h4>
                                                </div>

                                                <div class="flex gap-2">
                                                    <input
                                                        type="text"
                                                        placeholder="Comment or mention others with @"
                                                        class="flex-1 px-3 py-2 text-sm rounded-lg border border-gray-300 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                    />
                                                    <Button size="sm">Send</Button>
                                                </div>

                                                <div class="mt-4 space-y-3">
                                                    <div v-if="getTaskComments(selectedTask.id).length" class="space-y-3">
                                                        <div v-for="comment in getTaskComments(selectedTask.id)" :key="comment.id" class="flex gap-2">
                                                            <span
                                                                class="flex justify-center items-center w-8 h-8 text-sm font-medium text-gray-600 bg-gray-200 rounded-full"
                                                            >
                                                                {{ comment.avatar }}
                                                            </span>
                                                            <div class="flex-1 p-2 bg-gray-100 rounded-lg">
                                                                <p class="text-sm font-semibold text-gray-900">{{ comment.author }}</p>
                                                                <p class="text-sm text-gray-700">{{ comment.content }}</p>
                                                                <p class="mt-1 text-xs text-gray-500">{{ comment.timestamp }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <p v-else class="py-4 text-sm text-center text-gray-500">No comments yet.</p>
                                                </div>
                                            </div>
                                        </div>

                                        <DrawerFooter class="sticky bottom-0 flex-row gap-2 px-4 mt-4 border-t bg-background">
                                            <Button v-if="selectedTask.status !== 'completed'" @click="markAsDone" class="flex-1">
                                                Mark as Done
                                            </Button>
                                            <DrawerClose as-child>
                                                <Button variant="outline" class="flex-1" @click="closeDrawer"> Close </Button>
                                            </DrawerClose>
                                        </DrawerFooter>
                                    </div>
                                </DrawerContent>
                            </Drawer>
                        </template>
                    </KanbanBoard>
                </div>
            </div>

            <!-- Attachments Section -->
            <Card>
                <CardHeader class="pb-3 sm:pb-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <CardTitle class="flex gap-2 items-center text-base sm:text-lg">
                            <Paperclip class="flex-shrink-0 w-4 h-4 text-gray-500 sm:h-5 sm:w-5" />
                            <span>Attachments</span>
                        </CardTitle>
                        <div class="flex gap-2 items-center sm:gap-3">
                            <p class="text-xs text-gray-500 sm:text-sm">{{ project.attachments?.length || 0 }} files</p>
                            <Button variant="outline" size="sm" @click="openFileInput" class="text-xs sm:text-sm">
                                <Plus class="mr-1 w-3 h-3 sm:mr-2 sm:h-4 sm:w-4" />
                                <span class="hidden sm:inline">Add</span>
                                <span class="sm:hidden">+</span>
                            </Button>
                            <!-- Hidden file input -->
                            <input
                                type="file"
                                ref="fileInput"
                                class="hidden"
                                @change="handleFileUpload"
                                multiple
                                accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                            />
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <!-- Upload Area -->
                    <div class="p-4 mb-6 rounded-lg border-2 border-gray-200 border-dashed sm:p-6">
                        <div class="flex flex-col justify-center items-center">
                            <FileX class="w-8 h-8 text-gray-400 sm:h-12 sm:w-12" />
                            <p class="mt-2 text-xs text-gray-500 sm:text-sm">Drag and drop files here or</p>
                            <Button variant="outline" size="sm" class="mt-3 text-xs sm:mt-4 sm:text-sm" @click="openFileInput"> Browse Files </Button>
                            <p class="mt-2 text-xs text-gray-400">Supported: PDF, Word, Excel, Images • Max: 10MB</p>
                        </div>
                    </div>

                    <!-- Attachments List -->
                    <div v-if="project.attachments?.length" class="space-y-4">
                        <div class="flex justify-between items-center pb-2 border-b">
                            <h3 class="text-xs font-medium text-gray-900 sm:text-sm">Attached Files</h3>
                            <p class="hidden text-xs text-gray-500 sm:block">Size</p>
                        </div>
                        <div class="space-y-2 divide-y sm:space-y-0">
                            <div
                                v-for="attachment in project.attachments"
                                :key="attachment.id"
                                class="flex flex-col gap-3 py-3 group sm:flex-row sm:items-center sm:justify-between sm:gap-4"
                            >
                                <div class="flex gap-2 items-center min-w-0 sm:gap-3">
                                    <div class="flex flex-shrink-0 justify-center items-center w-8 h-8 bg-gray-50 rounded-lg sm:h-10 sm:w-10">
                                        <FileIcon :type="attachment.type" class="w-4 h-4 text-gray-500 sm:h-5 sm:w-5" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-xs font-medium text-gray-900 truncate cursor-pointer group-hover:text-orange-600 sm:text-sm">
                                            {{ attachment.name }}
                                        </h4>
                                        <p class="text-xs text-gray-500 truncate">
                                            {{ attachment.user.name }} •
                                            {{ new Date(attachment.created_at).toLocaleDateString() }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex gap-2 justify-between items-center sm:justify-end sm:gap-4">
                                    <span class="flex-shrink-0 text-xs text-gray-500 sm:text-sm">
                                        {{ formatFileSize(attachment.size) }}
                                    </span>
                                    <div class="flex flex-shrink-0 gap-2 items-center">
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            class="p-1 text-gray-500 hover:text-gray-700"
                                            @click="downloadAttachment(attachment)"
                                        >
                                            <Download class="w-4 h-4" />
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            class="p-1 text-red-500 hover:text-red-700"
                                            @click="deleteAttachment(attachment.id)"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else-if="!project.attachments?.length" class="mt-4 text-center">
                        <p class="text-xs text-gray-500 sm:text-sm">No attachments yet</p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<style scoped>
.prose h1 {
    @apply mb-3 mt-4 text-xl font-bold sm:mb-4 sm:mt-6 sm:text-3xl;
}

.prose p {
    @apply mb-3 text-xs sm:mb-4 sm:text-gray-600;
}

.prose br {
    @apply block h-2 content-[''] sm:h-4;
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
