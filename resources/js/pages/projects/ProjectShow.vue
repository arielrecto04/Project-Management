<script setup lang="ts">
import FileIcon from '@/components/FileIcon.vue';
import FileViewer from '@/components/FileViewer.vue';
import KanbanBoard from '@/components/KanbanBoard.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { ProjectStatus, ProjectStatusLabels } from '@/enums/ProjectStatus';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Calendar, Download, FileX, Info, Paperclip, Plus, Trash2, User, Users } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Task {
    id: number;
    name: string; // Changed from title to name to match backend
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

// Update the updateTaskStatus function and its type
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

// File viewer handling
const selectedFile = ref(null);
const isFileViewerOpen = ref(false);

const openFileViewer = (attachment: Attachment) => {
    selectedFile.value = attachment;
    isFileViewerOpen.value = true;
};
</script>

<template>
    <Head :title="project.name" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Project Header -->
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ project.name }}</h1>
                    <p class="mt-1 text-sm text-gray-500">Created by {{ project.creator?.name }}</p>
                </div>
                <div class="flex space-x-3">
                    <Button variant="outline" :href="route('projects.edit', project.id)"> Edit Project </Button>
                    <Button variant="default"> Share </Button>
                    <Link :href="route('projects.timeline', project.id)">
                        <Button variant="outline">View Timeline</Button>
                    </Link>
                </div>
            </div>

            <!-- Project Details Grid -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                <!-- Status Card -->
                <Card>
                    <CardContent class="pt-6">
                        <div class="flex items-center space-x-2">
                            <Info class="h-4 w-4 text-gray-500" />
                            <h3 class="text-sm font-medium text-gray-700">Status</h3>
                        </div>
                        <div class="mt-2">
                            <span :class="['rounded-full px-2.5 py-0.5 text-sm font-medium', getStatusColor(project.status)]">
                                {{ project.status }}
                            </span>
                        </div>
                    </CardContent>
                </Card>

                <!-- Assignee Card -->
                <Card>
                    <CardContent class="pt-6">
                        <div class="flex items-center space-x-2">
                            <User class="h-4 w-4 text-gray-500" />
                            <h3 class="text-sm font-medium text-gray-700">Assignee</h3>
                        </div>
                        <div class="mt-2">
                            <div class="flex items-center space-x-2">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-200">
                                    <span class="text-sm font-medium text-gray-600">
                                        {{ project.assignee?.name?.[0] || 'U' }}
                                    </span>
                                </div>
                                <span class="text-sm text-gray-900">
                                    {{ project.assignee?.name || 'Unassigned' }}
                                </span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Dates Card -->
                <Card class="col-span-2">
                    <CardContent class="pt-6">
                        <div class="flex items-center space-x-2">
                            <Calendar class="h-4 w-4 text-gray-500" />
                            <h3 class="text-sm font-medium text-gray-700">Timeline</h3>
                        </div>
                        <div class="mt-2 grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-500">Start Date</p>
                                <p class="text-sm font-medium text-gray-900">{{ project.start_date || 'Not set' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">End Date</p>
                                <p class="text-sm font-medium text-gray-900">{{ project.end_date || 'Not set' }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Description Section -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-lg">Description</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="prose max-w-none">
                        <div v-html="project.description || 'No description provided'" />
                    </div>
                </CardContent>
            </Card>

            <!-- Tasks Section -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <Users class="h-5 w-5 text-gray-500" />
                        <h2 class="text-lg font-semibold text-gray-900">Tasks</h2>
                    </div>
                    <Link :href="route('tasks.create', { project_id: project.id })">
                        <Button variant="default">Add Task</Button>
                    </Link>
                </div>
                <KanbanBoard :columns="groupedTasks" type="task" @item-moved="updateTaskStatus" class="min-h-[500px]" />
            </div>

            <!-- Attachments Section -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <CardTitle class="flex items-center gap-2 text-lg">
                            <Paperclip class="h-5 w-5 text-gray-500" />
                            Attachments
                        </CardTitle>
                        <div class="flex items-center gap-2">
                            <p class="text-sm text-gray-500">{{ project.attachments?.length || 0 }} files</p>
                            <Button variant="outline" @click="openFileInput">
                                <Plus class="mr-2 h-4 w-4" />
                                Add Attachment
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
                    <div class="mb-6 rounded-lg border-2 border-dashed border-gray-200 p-4">
                        <div class="flex flex-col items-center justify-center">
                            <FileX class="h-12 w-12 text-gray-400" />
                            <p class="mt-2 text-sm text-gray-500">Drag and drop files here or</p>
                            <Button variant="outline" size="sm" class="mt-4" @click="openFileInput"> Browse Files </Button>
                            <p class="mt-2 text-xs text-gray-400">Supported formats: PDF, Word, Excel, Images • Max size: 10MB</p>
                        </div>
                    </div>

                    <!-- Attachments List -->
                    <div v-if="project.attachments?.length" class="space-y-4">
                        <div class="flex items-center justify-between border-b pb-2">
                            <h3 class="font-medium text-gray-900">Attached Files</h3>
                            <p class="text-sm text-gray-500">Size</p>
                        </div>
                        <div class="divide-y">
                            <div v-for="attachment in project.attachments" :key="attachment.id" class="group flex items-center justify-between py-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-50">
                                        <FileIcon :type="attachment.type" class="h-5 w-5 text-gray-500" />
                                    </div>
                                    <div>
                                        <h4
                                            class="cursor-pointer font-medium text-gray-900 group-hover:text-orange-600"
                                            @click="openFileViewer(attachment)"
                                        >
                                            {{ attachment.name }}
                                        </h4>
                                        <p class="text-sm text-gray-500">
                                            Uploaded by {{ attachment.user.name }} •
                                            {{ new Date(attachment.created_at).toLocaleDateString() }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <span class="text-sm text-gray-500">
                                        {{ formatFileSize(attachment.size) }}
                                    </span>
                                    <div class="flex items-center gap-2">
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            class="text-gray-500 hover:text-gray-700"
                                            @click="downloadAttachment(attachment)"
                                        >
                                            <Download class="h-4 w-4" />
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            class="text-red-500 hover:text-red-700"
                                            @click="deleteAttachment(attachment.id)"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else-if="!project.attachments?.length" class="mt-4 text-center">
                        <p class="text-sm text-gray-500">No attachments yet</p>
                    </div>
                </CardContent>
            </Card>
        </div>

        <FileViewer :is-open="isFileViewerOpen" :file="selectedFile" @close="isFileViewerOpen = false" />
    </AppLayout>
</template>

<style>
.prose h1 {
    @apply mb-4 mt-6 text-3xl font-bold;
}

.prose p {
    @apply mb-4 text-gray-600;
}

.prose br {
    @apply block h-4 content-[''];
}
</style>
