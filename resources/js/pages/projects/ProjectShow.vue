<script setup lang="ts">
import FileIcon from '@/components/FileIcon.vue';
import KanbanBoard from '@/components/KanbanBoard.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Drawer, DrawerClose, DrawerContent, DrawerFooter, DrawerHeader, DrawerTitle, DrawerTrigger } from '@/components/ui/drawer';
import { ProjectStatus, ProjectStatusLabels } from '@/enums/ProjectStatus';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Calendar, Download, Eye, FileX, Info, Menu, Paperclip, Plus, Trash2, User, Users, X, List, Send, Loader } from 'lucide-vue-next';
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
    comments?: Comment[];
    created_at: string;
    updated_at: string;
}

interface User {
    name: string;
    email: string;
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

interface Comment {
    id: number;
    user: User;
    body: string;
    timestamp: string;
    mentionedUsers?: string[];
}


const props = defineProps<{
    project: Project;
    errors: Record<string, string>;
}>();

console.log('Project data:', props.project);

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Projects', href: '/projects' },
    { title: props.project.name, href: '#' },
];

const mobileMenuOpen = ref(false);
const drawerOpen = ref(false);
const selectedTask = ref<Task | null>(null);

// --- Share modal state & helpers ---
const shareModalOpen = ref(false);
const shareCopied = ref(false);
const projectShareLink = computed(() => {
    // prefer named route helper if available, fallback to current location
    try {
        // route() comes from Ziggy / Inertia helpers
        return typeof route === 'function' ? route('projects.show', props.project.id) : window.location.href;
    } catch {
        return window.location.href;
    }
});

const openShareModal = () => {
    shareCopied.value = false;
    shareModalOpen.value = true;
};

const closeShareModal = () => {
    shareModalOpen.value = false;
};

const copyShareLink = async () => {
    try {
        await navigator.clipboard.writeText(projectShareLink.value);
        shareCopied.value = true;
    } catch (err) {
        console.error('Copy failed', err);
        // fallback: select and prompt
        const fallbackInput = document.getElementById('project-share-link') as HTMLInputElement | null;
        if (fallbackInput) {
            fallbackInput.select();
            document.execCommand('copy');
            shareCopied.value = true;
        }
    }
};

// comment edit/delete state
const editingCommentId = ref<number | null>(null);
const editingCommentBody = ref('');

// Start editing a comment (only owner)
const startEditComment = (comment: any) => {
    if (!currentUser || comment.user?.id !== currentUser.id) return;
    editingCommentId.value = comment.id;
    editingCommentBody.value = comment.body || '';
};

// Cancel edit
const cancelEditComment = () => {
    editingCommentId.value = null;
    editingCommentBody.value = '';
};

// Submit updated comment
const submitUpdateComment = (commentId: number) => {
    if (!selectedTask.value) return;
    const payload = { body: editingCommentBody.value };
    // optimistic UI: update local comments if present
    const task = props.project.tasks.find(t => t.id === selectedTask.value!.id);
    const localComments = task?.comments || [];

    const idx = localComments.findIndex((c: any) => c.id === commentId);
    const previousBody = idx !== -1 ? localComments[idx].body : null;
    if (idx !== -1) localComments[idx].body = editingCommentBody.value;

    router.put(route('tasks.comments.update', [selectedTask.value.id, commentId]), payload, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            editingCommentId.value = null;
            editingCommentBody.value = '';
        },
        onError: () => {
            // revert optimistic change
            if (idx !== -1 && previousBody !== null) localComments[idx].body = previousBody;
        },
    });
};

// Delete comment (only owner)
const deleteComment = (commentId: number) => {
    if (!selectedTask.value) return;
    if (!confirm('Are you sure you want to delete this comment?')) return;

    const task = props.project.tasks.find(t => t.id === selectedTask.value!.id);
    const localComments = task?.comments || [];
    const idx = localComments.findIndex((c: any) => c.id === commentId);
    const removed = idx !== -1 ? localComments.splice(idx, 1)[0] : null;

    router.delete(route('tasks.comments.destroy', [selectedTask.value.id, commentId]), {
        preserveScroll: true,
        preserveState: true,
        onError: () => {
            // restore on error
            if (removed) localComments.splice(idx, 0, removed);
        },
    });
};

// Group tasks by status
const groupedTasks = computed(() => {
    const pending = props.project.tasks?.filter((t) => t.status === ProjectStatus.Pending) || [];
    const inProgress = props.project.tasks?.filter((t) => t.status === ProjectStatus.InProgress) || [];
    const completed = props.project.tasks?.filter((t) => t.status === ProjectStatus.Completed) || [];
    console.log(pending, inProgress, completed);
    return [
        {
            id: 1,
            title: ProjectStatusLabels[ProjectStatus.Pending],
            tasks: pending.map((t) => ({
                id: t.id,
                title: t.name,
                status: t.status,
                description: t.description,
                assignee: t.assignee_to,
                end_date: t.due_date,
                created_at: t.created_at,
                updated_at: t.updated_at,
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
                assignee: t.assignee_to,
                end_date: t.due_date,
                created_at: t.created_at,
                updated_at: t.updated_at,
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
                assignee: t.assignee_to,
                end_date: t.due_date,
                created_at: t.created_at,
                updated_at: t.updated_at,
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
    return props.project.tasks.find(task => task.id === taskId)?.comments || [];
};

// Get attachments for task
const getTaskAttachments = (taskId: number) => {
    return props.project.tasks.find(task => task.id === taskId)?.attachments || [];
};


const handleSubmitComment = (taskId: number, content: string) => {
    router.post(route('tasks.comments.store', taskId), { body: content }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            console.log('Comment added successfully');

        },
        onError: (errors) => {
            console.error('Failed to add comment:', errors);
        },
    });
};

// -- ASSIGN USER STATE & FUNCTIONS --
const showAssignSearch = ref(false);
const assignQuery = ref('');
const assignResults = ref<Array<{ id: number, name: string, username?: string }>>([]);
const assignLoading = ref(false);
let assignTimer: number | undefined = undefined;

const openAssignSearch = () => {
    showAssignSearch.value = true;
    assignQuery.value = '';
    assignResults.value = [];
};

const cancelAssignSearch = () => {
    showAssignSearch.value = false;
    assignQuery.value = '';
    assignResults.value = [];
};

// search users (calls UserController::search, route name: users.search)
// adjust route name if yours differs
const searchAssignUsers = async (term: string) => {
    assignLoading.value = true;
    try {
        const url = (typeof route === 'function') ? route('users.search', term) : `/users/search/${encodeURIComponent(term)}`;
        const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (!res.ok) {
            assignResults.value = [];
            assignLoading.value = false;
            return;
        }
        const data = await res.json();
        assignResults.value = data.map((u: any) => ({ id: u.id, name: u.name, username: u.username || (u.email || '').split('@')[0] }));
    } catch (e) {
        console.error('User search error', e);
        assignResults.value = [];
    } finally {
        assignLoading.value = false;
    }
};

// debounced handler for input
const handleAssignInput = (e: Event) => {
    const t = e.target as HTMLInputElement;
    assignQuery.value = t.value;
    window.clearTimeout(assignTimer);
    assignTimer = window.setTimeout(() => {
        if (assignQuery.value.trim().length === 0) {
            // optionally load recent users
            searchAssignUsers('');
        } else {
            searchAssignUsers(assignQuery.value.trim());
        }
    }, 200);
};

// select a user and assign to task
const selectAssignee = (user: { id: number, name: string }) => {
    if (!selectedTask.value) return;
    const taskId = selectedTask.value.id;

    router.put(route('tasks.assign', taskId), { assignee_id: user.id }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (page) => {
            // update drawer UI immediately
            selectedTask.value = { ...selectedTask.value!, assignee: { name: user.name } } as any;
            // hide search
            cancelAssignSearch();
        },
        onError: (errors) => {
            console.error('Failed to assign user:', errors);
        },
    });
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
                <button @click="mobileMenuOpen = !mobileMenuOpen"
                    class="flex gap-2 items-center self-start px-3 py-2 bg-white rounded-lg border border-gray-300 sm:hidden">
                    <Menu v-if="!mobileMenuOpen" class="w-5 h-5" />
                    <X v-else class="w-5 h-5" />
                </button>

                <!-- Desktop Action Buttons -->
                <div class="hidden flex-col gap-2 sm:flex sm:flex-row sm:gap-3">
                    <Link :href="route('projects.edit', project.id)" size="sm"
                        class="text-xs sm:text-sm border-2 border-gray-200 rounded-lg p-2">
                    Edit Project </Link>
                    <Button variant="default" size="sm" class="text-xs sm:text-sm" @click="openShareModal">
                        Share
                    </Button>
                    <Link :href="route('projects.timeline', project.id)">
                    <Button variant="outline" size="sm" class="text-xs sm:text-sm">View Timeline</Button>
                    </Link>
                </div>
            </div>

            <!-- Mobile Action Menu -->
            <div v-show="mobileMenuOpen" class="space-y-2 sm:hidden">
                <Button variant="outline" :href="route('projects.edit', project.id)" class="justify-start w-full"> Edit
                    Project </Button>
                <Button variant="default" class="justify-start w-full" @click="openShareModal"> Share </Button>
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
                            <span
                                :class="['rounded-full px-2.5 py-0.5 text-xs font-medium sm:text-sm', getStatusColor(project.status)]">
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
                                <div
                                    class="flex flex-shrink-0 justify-center items-center w-8 h-8 bg-gray-200 rounded-full">
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
                                <p class="text-xs font-medium text-gray-900 sm:text-sm">{{ project.start_date
                                    || 'Not set' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">End Date</p>
                                <p class="text-xs font-medium text-gray-900 sm:text-sm">{{ project.end_date || 'Not set'
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
                    <KanbanBoard :columns="groupedTasks" type="task" @item-moved="updateTaskStatus"
                        class="min-h-[500px]">


                        <template #card-header="{ item }">


                            <div class="flex justify-between items-start gap-3 mb-3">
                                <h3 class="text-sm font-semibold text-gray-900 leading-tight flex-1 line-clamp-2">
                                    {{ item.title }}
                                </h3>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium rounded-full whitespace-nowrap flex-shrink-0 shadow-sm"
                                    :class="getStatusColor(item.status)">
                                    {{ ProjectStatusLabels[item.status as ProjectStatus] }}
                                </span>
                            </div>
                        </template>

                        <template #card-content="{ item }">
                            <div class="space-y-3">
                                <!-- Description -->
                                <p v-if="item.description" class="text-sm text-gray-600 line-clamp-3 leading-relaxed">
                                    <span v-html="item.description"></span>
                                </p>

                                <div class="flex flex-col gap-2 pt-2 border-t border-gray-100">
                                    <!-- Due Date -->
                                    <div v-if="item.end_date" class="flex items-center gap-2 text-xs text-gray-500">
                                        <Calendar class="w-3.5 h-3.5 flex-shrink-0" />
                                        <span class="font-medium">Due:</span>
                                        <span>{{ new Date(item.end_date).toLocaleDateString('en-US', {
                                            month: 'short',
                                            day: 'numeric', year: 'numeric'
                                        }) }}</span>
                                    </div>

                                    <!-- Assignee -->
                                    <div v-if="item.assignee" class="flex items-center gap-2">
                                        <div
                                            class="flex justify-center items-center w-7 h-7 text-xs font-semibold text-orange-700 bg-gradient-to-br from-orange-50 to-orange-100 rounded-full ring-2 ring-orange-200 ring-offset-1">
                                            {{ item.assignee.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-xs text-gray-500 font-medium">Assigned to</span>
                                            <span class="text-sm text-gray-700 font-medium">{{ item.assignee.name
                                            }}</span>
                                        </div>
                                    </div>
                                    <div v-else class="flex items-center gap-2 text-xs text-gray-400">
                                        <User class="w-3.5 h-3.5" />
                                        <span>Unassigned</span>
                                    </div>
                                </div>
                            </div>
                        </template>
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
                                                    {{ ProjectStatusLabels[selectedTask.status] || selectedTask?.status
                                                    }}
                                                </Badge>

                                                <Button variant="ghost" size="sm"
                                                    class="p-1 h-auto text-muted-foreground">
                                                    <span class="text-sm">Normal</span>
                                                </Button>
                                            </div>
                                        </DrawerHeader>

                                        <div class="flex gap-2 w-full">
                                            <div class="w-1/2 border-t border-b divide-y">
                                                <div class="grid grid-cols-[24px_1fr] items-start gap-4 px-4 py-3">
                                                    <Users class="mt-1 w-5 h-5 text-muted-foreground" />
                                                    <div>
                                                        <h4 class="text-sm font-medium text-muted-foreground">Assignees
                                                        </h4>
                                                        <div class="flex flex-col gap-2 mt-2">
                                                            <div v-if="selectedTask?.assignee"
                                                                class="flex gap-2 items-center">
                                                                <span
                                                                    class="flex justify-center items-center w-6 h-6 text-xs font-medium text-gray-600 bg-gray-200 rounded-full">
                                                                    {{ selectedTask.assignee.name.charAt(0) }}
                                                                </span>
                                                                <span class="text-sm">{{ selectedTask.assignee.name
                                                                }}</span>
                                                                <Link
                                                                    :href="route('tasks.remove-assign', selectedTask.id)"
                                                                    method="put" variant="ghost" size="sm">
                                                                <Trash2 class="w-4 h-4 text-red-500" />
                                                                </Link>
                                                            </div>
                                                            <div v-else class="text-sm text-gray-500">Unassigned</div>

                                                            <div v-if="!showAssignSearch" class="mt-1">
                                                                <Button variant="ghost" size="sm"
                                                                    class="justify-start p-0 h-auto text-sm text-blue-600 hover:text-blue-700"
                                                                    @click="openAssignSearch">
                                                                    + Add or change
                                                                </Button>
                                                            </div>

                                                            <!-- Assign search inline -->
                                                            <div v-else class="mt-2">
                                                                <div class="flex items-center gap-2">
                                                                    <input type="text" placeholder="Search users..."
                                                                        class="flex-1 px-3 py-2 text-sm rounded-lg border border-gray-300 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                                        v-model="assignQuery"
                                                                        @input="handleAssignInput" />
                                                                    <button
                                                                        class="px-3 py-2 text-sm rounded-md bg-gray-100"
                                                                        @click="cancelAssignSearch">Cancel</button>
                                                                </div>

                                                                <div
                                                                    class="mt-2 bg-white border border-gray-200 rounded shadow-sm max-h-56 overflow-auto">
                                                                    <div v-if="assignLoading"
                                                                        class="px-3 py-2 text-sm text-gray-500">
                                                                        Searching...</div>
                                                                    <div v-else-if="assignResults.length === 0"
                                                                        class="px-3 py-2 text-sm text-gray-500">No users
                                                                        found</div>
                                                                    <template v-else>
                                                                        <button v-for="u in assignResults" :key="u.id"
                                                                            @click="selectAssignee(u)"
                                                                            class="w-full text-left px-3 py-2 hover:bg-blue-50 flex items-center gap-3">
                                                                            <span
                                                                                class="flex justify-center items-center w-7 h-7 text-xs font-medium text-gray-600 bg-gray-200 rounded-full">
                                                                                {{ u.name.charAt(0) }}
                                                                            </span>
                                                                            <div>
                                                                                <div class="text-sm font-medium">{{
                                                                                    u.name }}</div>
                                                                                <div class="text-xs text-gray-500">@{{
                                                                                    u.username }}</div>
                                                                            </div>
                                                                        </button>
                                                                    </template>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-[24px_1fr] items-start gap-4 px-4 py-3">
                                                    <Calendar class="mt-1 w-5 h-5 text-muted-foreground" />
                                                    <div>
                                                        <h4 class="text-sm font-medium text-muted-foreground">Start &
                                                            Due date</h4>
                                                        <div class="mt-2 space-y-1">
                                                            <div class="text-sm"><span class="text-gray-600">Start:
                                                                    {{ new
                                                                        Date(selectedTask.created_at).toLocaleDateString()
                                                                    }}</span>

                                                            </div>
                                                            <div class="text-sm">
                                                                <span class="text-gray-600">Due:</span> {{
                                                                    new Date(selectedTask.end_date).toLocaleDateString() ||
                                                                    'Not set' }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-[24px_1fr] items-start gap-4 px-4 py-3">
                                                    <FileText class="mt-1 w-5 h-5 text-muted-foreground" />
                                                    <div>
                                                        <h4 class="text-sm font-medium text-muted-foreground">
                                                            Description</h4>
                                                        <div v-if="selectedTask.description"
                                                            class="mt-2 text-sm text-gray-800 prose prose-sm"
                                                            v-html="selectedTask.description" />
                                                        <p v-else class="mt-2 text-sm text-gray-500">No description
                                                            provided.</p>
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-[24px_1fr] items-start gap-4 px-4 py-3">
                                                    <Paperclip class="mt-1 w-5 h-5 text-muted-foreground" />
                                                    <div>
                                                        <h4 class="text-sm font-medium text-muted-foreground">
                                                            Attachments</h4>
                                                        <div class="mt-2">
                                                            <div v-if="getTaskAttachments(selectedTask.id).length"
                                                                class="space-y-2">
                                                                <div v-for="att in getTaskAttachments(selectedTask.id)"
                                                                    :key="att.id"
                                                                    class="flex justify-between items-center p-2 rounded-lg border">
                                                                    <div class="flex gap-2 items-center">
                                                                        <FileIcon :type="att.type"
                                                                            class="flex-shrink-0 w-5 h-5 text-gray-500" />
                                                                        <div>
                                                                            <p
                                                                                class="text-sm font-medium text-gray-900">
                                                                                {{ att.name }}</p>
                                                                            <p class="text-xs text-gray-500">{{
                                                                                formatFileSize(att.size) }}</p>
                                                                        </div>
                                                                    </div>
                                                                    <Button variant="ghost" size="sm"
                                                                        class="p-1 text-gray-500">
                                                                        <Download class="w-4 h-4" />
                                                                    </Button>
                                                                </div>
                                                            </div>
                                                            <p v-else class="text-sm text-gray-500">No attachments for
                                                                this task.</p>
                                                            <Button variant="outline" size="sm" class="mt-2 text-xs">
                                                                Add attachment </Button>
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
                                                    <form
                                                        @submit.prevent="handleSubmitComment(selectedTask.id, $event.target[0].value)"
                                                        class="flex flex-col w-full">
                                                        <div class="flex gap-2 w-full">
                                                            <input type="text" name="body"
                                                                placeholder="Comment or mention others with @"
                                                                class="flex-1 px-3 py-2 text-sm rounded-lg border border-gray-300 focus:outline-none focus:ring-1 focus:ring-blue-500" />


                                                            <Button size="sm">Send</Button>
                                                        </div>


                                                        <span v-if="props?.errors?.body" class="text-xs text-red-500">
                                                            {{ props?.errors?.body }}
                                                        </span>
                                                    </form>
                                                </div>

                                                <div class="mt-4 space-y-3">
                                                    <div v-if="getTaskComments(selectedTask.id).length"
                                                        class="space-y-3">
                                                        <div v-for="comment in getTaskComments(selectedTask.id)"
                                                            :key="comment.id" class="flex gap-2">
                                                            <span
                                                                class="flex justify-center items-center w-8 h-8 text-sm font-medium text-gray-600 bg-gray-200 rounded-full">
                                                                {{ comment.user?.name?.charAt(0) || '?' }}
                                                            </span>

                                                            <div class="flex-1 p-2 bg-gray-100 rounded-lg">
                                                                <div class="flex items-center justify-between">
                                                                    <p class="text-sm font-semibold text-gray-900">{{
                                                                        comment.user?.name }}</p>

                                                                    <div class="flex gap-2">
                                                                        <!-- show edit/delete only to owner -->
                                                                        <template
                                                                            v-if="currentUser && comment.user && comment.user.id === currentUser.id">
                                                                            <button
                                                                                v-if="editingCommentId !== comment.id"
                                                                                @click="startEditComment(comment)"
                                                                                class="text-xs text-blue-600">Edit</button>
                                                                            <button v-else
                                                                                @click="submitUpdateComment(comment.id)"
                                                                                class="text-xs text-green-600">Save</button>
                                                                            <button
                                                                                v-if="editingCommentId === comment.id"
                                                                                @click="cancelEditComment"
                                                                                class="text-xs">Cancel</button>
                                                                            <button @click="deleteComment(comment.id)"
                                                                                class="text-xs text-red-500">Delete</button>
                                                                        </template>
                                                                    </div>
                                                                </div>

                                                                <!-- edit mode -->
                                                                <div v-if="editingCommentId === comment.id"
                                                                    class="mt-2">
                                                                    <textarea v-model="editingCommentBody" rows="3"
                                                                        class="w-full rounded border p-2 text-sm"></textarea>
                                                                </div>

                                                                <!-- display mode -->
                                                                <p v-else class="text-sm text-gray-700 mt-1">{{
                                                                    comment.body }}</p>

                                                                <p class="mt-1 text-xs text-gray-500">{{
                                                                    comment.timestamp }}</p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <p v-else class="py-4 text-sm text-center text-gray-500">No comments
                                                        yet.</p>
                                                </div>
                                            </div>
                                        </div>

                                        <DrawerFooter
                                            class="sticky bottom-0 flex-row gap-2 px-4 mt-4 border-t bg-background">
                                            <Button v-if="selectedTask.status !== 'completed'" @click="markAsDone"
                                                class="flex-1">
                                                Mark as Done
                                            </Button>
                                            <DrawerClose as-child>
                                                <Button variant="outline" class="flex-1" @click="closeDrawer"> Close
                                                </Button>
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
                            <input type="file" ref="fileInput" class="hidden" @change="handleFileUpload" multiple
                                accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" />
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <!-- Upload Area -->
                    <div class="p-4 mb-6 rounded-lg border-2 border-gray-200 border-dashed sm:p-6">
                        <div class="flex flex-col justify-center items-center">
                            <FileX class="w-8 h-8 text-gray-400 sm:h-12 sm:w-12" />
                            <p class="mt-2 text-xs text-gray-500 sm:text-sm">Drag and drop files here or</p>
                            <Button variant="outline" size="sm" class="mt-3 text-xs sm:mt-4 sm:text-sm"
                                @click="openFileInput">
                                Browse Files </Button>
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
                            <div v-for="attachment in project.attachments" :key="attachment.id"
                                class="flex flex-col gap-3 py-3 group sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                                <div class="flex gap-2 items-center min-w-0 sm:gap-3">
                                    <div
                                        class="flex flex-shrink-0 justify-center items-center w-8 h-8 bg-gray-50 rounded-lg sm:h-10 sm:w-10">
                                        <FileIcon :type="attachment.type" class="w-4 h-4 text-gray-500 sm:h-5 sm:w-5" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4
                                            class="text-xs font-medium text-gray-900 truncate cursor-pointer group-hover:text-orange-600 sm:text-sm">
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
                                        <Button variant="ghost" size="sm" class="p-1 text-gray-500 hover:text-gray-700"
                                            @click="downloadAttachment(attachment)">
                                            <Download class="w-4 h-4" />
                                        </Button>
                                        <Button variant="ghost" size="sm" class="p-1 text-red-500 hover:text-red-700"
                                            @click="deleteAttachment(attachment.id)">
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

    <div v-if="shareModalOpen" class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/40" @click="closeShareModal" aria-hidden="true"></div>
        <div class="relative w-full max-w-lg mx-4 bg-white rounded-lg shadow-lg border overflow-hidden" role="dialog"
            aria-modal="true" aria-label="Share project link">
            <header class="flex items-center justify-between px-4 py-3 border-b">
                <h3 class="text-sm font-semibold text-gray-900">Share project</h3>
                <button class="text-gray-500 hover:text-gray-700" @click="closeShareModal" aria-label="Close">
                    <X class="w-5 h-5" />
                </button>
            </header>

            <div class="p-4">
                <p class="text-xs text-gray-600 mb-3">Anyone with this link can view the project.</p>

                <label class="sr-only" for="project-share-link">Project link</label>
                <div class="flex gap-2 items-center">
                    <input id="project-share-link" type="text" readonly :value="projectShareLink"
                        class="flex-1 px-3 py-2 text-sm rounded border border-gray-200 bg-gray-50" />
                    <button @click="copyShareLink"
                        class="inline-flex items-center gap-2 px-3 py-2 rounded bg-blue-600 text-white text-sm hover:bg-blue-700">
                        <span v-if="!shareCopied">Copy link</span>
                        <span v-else>Copied</span>
                    </button>
                </div>

                <p v-if="shareCopied" class="mt-3 text-xs text-green-600">Link copied to clipboard.</p>

                <p class="mt-4 text-xs text-gray-500">Tip: You can paste this link into email or chat to share.</p>
            </div>

            <footer class="flex justify-end gap-2 px-4 py-3 border-t">
                <Button variant="outline" size="sm" @click="closeShareModal">Close</Button>
                <Button size="sm" @click="copyShareLink">{{ shareCopied ? 'Copied' : 'Copy link' }}</Button>
            </footer>
        </div>
    </div>

</template>

<!-- Share Modal -->

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
