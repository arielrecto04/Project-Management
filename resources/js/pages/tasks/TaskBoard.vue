<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import FileIcon from '@/components/FileIcon.vue';
import KanbanBoard from '@/components/KanbanBoard.vue';
import { Button } from '@/components/ui/button';
import { Drawer, DrawerContent } from '@/components/ui/drawer';
import { defineProps } from 'vue';

type TaskItem = {
    id: number;
    name: string;
    description?: string;
    due_date?: string | null;
    start_date?: string | null;
    status?: string;
    project?: { id: number; name: string } | null;
    assigneeTo?: { id: number; name: string } | null;
    attachments?: any[];
    comments?: any[];
    created_at?: string;
};

const props = defineProps<{
    tasks: TaskItem[];
    currentUser: { id: number; name: string } | null;
}>();

const selectedTask = ref<TaskItem | null>(null);
const drawerOpen = ref(false);

// grouping helpers
const today = new Date();
const startOfDay = (d: Date) => new Date(d.getFullYear(), d.getMonth(), d.getDate());
const isSameDay = (a?: string | null, b?: Date) => {
    if (!a) return false;
    const da = new Date(a);
    return startOfDay(da).getTime() === startOfDay(b).getTime();
};
const isBeforeDay = (a?: string | null, b?: Date) => {
    if (!a) return false;
    const da = new Date(a);
    return startOfDay(da).getTime() < startOfDay(b).getTime();
};
const addDays = (d: Date, offset: number) => {
    const x = new Date(d);
    x.setDate(x.getDate() + offset);
    return x;
};

const overdue = computed(() =>
    props.tasks.filter((t) => t.due_date && isBeforeDay(t.due_date, today) && t.status !== 'completed')
);

const dueToday = computed(() =>
    props.tasks.filter((t) => t.due_date && isSameDay(t.due_date, today) && t.status !== 'completed')
);

const dueTomorrow = computed(() =>
    props.tasks.filter((t) => t.due_date && isSameDay(t.due_date, addDays(today, 1)) && t.status !== 'completed')
);

const future = computed(() =>
    props.tasks.filter((t) => t.due_date && !isSameDay(t.due_date, today) && !isSameDay(t.due_date, addDays(today, 1)) && !isBeforeDay(t.due_date, today) && t.status !== 'completed')
);

const done = computed(() => props.tasks.filter((t) => t.status === 'completed'));

const noDue = computed(() => props.tasks.filter((t) => !t.due_date && t.status !== 'completed'));

// UI helpers
const openTask = (task: TaskItem) => {
    selectedTask.value = task;
    drawerOpen.value = true;
};
const closeDrawer = () => {
    drawerOpen.value = false;
    selectedTask.value = null;
};

const markDone = async (task: TaskItem) => {
    await router.put(route('tasks.update-status', task.id), { status: 'completed' }, { preserveState: true });
    // // optimistic local update
    // task.status = 'completed';
};

const assignToMe = async (task: TaskItem) => {
    const userId = props.currentUser?.id;
    if (!userId) return;
    await router.put(route('tasks.assign', task.id), { assignee_id: userId }, { preserveState: true });
    task.assigneeTo = { id: userId, name: props.currentUser?.name || 'You' };
};

</script>

<template>

    <Head title="My work" />

    <AppLayout :breadcrumbs="[
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Tasks', href: '/tasks' },
        { title: 'My Work', href: '#' },
    ]">
        <div class=" px-6 py-4">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <h1 class="text-xl font-semibold">My work</h1>
                    <div class="text-sm text-gray-500">Personal board — grouped by due date</div>
                </div>

                <div class="flex items-center gap-2">
                    <Link :href="route('tasks.create')"
                        class="text-sm bg-white px-3 py-1 rounded shadow-sm hover:bg-gray-50">+ New Task</Link>
                    <Button @click="router.reload()">Refresh</Button>
                </div>
            </div>

            <!-- quick filters -->
            <div class="flex gap-2 mb-6 flex-wrap">
                <Button variant="ghost" size="sm" class="px-3">Assigned to me</Button>
                <Button variant="ghost" size="sm" class="px-3">Subscribed</Button>
                <Button variant="ghost" size="sm" class="px-3">Created</Button>
            </div>

            <!-- groups -->
            <div class="space-y-4">
                <section v-if="overdue.length">
                    <header class="flex items-center gap-3 px-3 py-2 rounded-md bg-red-50 border border-red-100">
                        <span class="text-red-600 font-medium">🔥 Overdue</span>
                        <span class="text-sm text-gray-500">{{ overdue.length }}</span>
                    </header>
                    <div class="bg-white rounded-md shadow-sm divide-y mt-2">
                        <div v-for="task in overdue" :key="task.id" class="p-3 flex items-center justify-between">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-48 truncate">
                                    <button class="text-sm text-left font-medium truncate" @click="openTask(task)">{{
                                        task.name }}</button>
                                    <div class="text-xs text-gray-500 truncate">{{ task.project?.name }}</div>
                                </div>
                                <div class="text-xs text-gray-500">Due: {{ new Date(task.due_date).toLocaleDateString()
                                }}</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button @click="assignToMe(task)" class="text-xs px-2 py-1 bg-gray-50 rounded">Assign to
                                    me</button>
                                <button @click="markDone(task)"
                                    class="text-xs px-2 py-1 bg-green-50 text-green-600 rounded">Mark done</button>
                            </div>
                        </div>
                    </div>
                </section>

                <section>
                    <header class="flex items-center gap-3 px-3 py-2 rounded-md bg-white border">
                        <span class="text-gray-700 font-medium">Today</span>
                        <span class="text-sm text-gray-500">{{ dueToday.length }}</span>
                    </header>
                    <div class="bg-white rounded-md shadow-sm divide-y mt-2">
                        <div v-for="task in dueToday" :key="task.id" class="p-3 flex items-center justify-between">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-48 truncate">
                                    <button class="text-sm text-left font-medium truncate" @click="openTask(task)">{{
                                        task.name }}</button>
                                    <div class="text-xs text-gray-500 truncate">{{ task.project?.name }}</div>
                                </div>
                                <div class="text-xs text-gray-500">Due: {{ new Date(task.due_date).toLocaleDateString()
                                }}</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button @click="assignToMe(task)" class="text-xs px-2 py-1 bg-gray-50 rounded">Assign to
                                    me</button>
                                <button @click="markDone(task)"
                                    class="text-xs px-2 py-1 bg-green-50 text-green-600 rounded">Mark done</button>
                            </div>
                        </div>
                    </div>
                </section>

                <section v-if="dueTomorrow.length">
                    <header class="flex items-center gap-3 px-3 py-2 rounded-md bg-white border">
                        <span class="text-gray-700 font-medium">Tomorrow</span>
                        <span class="text-sm text-gray-500">{{ dueTomorrow.length }}</span>
                    </header>
                    <div class="bg-white rounded-md shadow-sm divide-y mt-2">
                        <div v-for="task in dueTomorrow" :key="task.id" class="p-3 flex items-center justify-between">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-48 truncate">
                                    <button class="text-sm text-left font-medium truncate" @click="openTask(task)">{{
                                        task.name }}</button>
                                    <div class="text-xs text-gray-500 truncate">{{ task.project?.name }}</div>
                                </div>
                                <div class="text-xs text-gray-500">Due: {{ new Date(task.due_date).toLocaleDateString()
                                }}</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button @click="assignToMe(task)" class="text-xs px-2 py-1 bg-gray-50 rounded">Assign to
                                    me</button>
                                <button @click="markDone(task)"
                                    class="text-xs px-2 py-1 bg-green-50 text-green-600 rounded">Mark done</button>
                            </div>
                        </div>
                    </div>
                </section>

                <section v-if="future.length">
                    <header class="flex items-center gap-3 px-3 py-2 rounded-md bg-white border">
                        <span class="text-gray-700 font-medium">Future</span>
                        <span class="text-sm text-gray-500">{{ future.length }}</span>
                    </header>
                    <div class="bg-white rounded-md shadow-sm divide-y mt-2">
                        <div v-for="task in future" :key="task.id" class="p-3 flex items-center justify-between">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-48 truncate">
                                    <button class="text-sm text-left font-medium truncate" @click="openTask(task)">{{
                                        task.name }}</button>
                                    <div class="text-xs text-gray-500 truncate">{{ task.project?.name }}</div>
                                </div>
                                <div class="text-xs text-gray-500">Due: {{ new Date(task.due_date).toLocaleDateString()
                                }}</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button @click="assignToMe(task)" class="text-xs px-2 py-1 bg-gray-50 rounded">Assign to
                                    me</button>
                                <button @click="markDone(task)"
                                    class="text-xs px-2 py-1 bg-green-50 text-green-600 rounded">Mark done</button>
                            </div>
                        </div>
                    </div>
                </section>

                <section>
                    <header class="flex items-center gap-3 px-3 py-2 rounded-md bg-white border">
                        <span class="text-gray-700 font-medium">Done</span>
                        <span class="text-sm text-gray-500">{{ done.length }}</span>
                    </header>
                    <div class="bg-white rounded-md shadow-sm divide-y mt-2">
                        <div v-for="task in done" :key="task.id" class="p-3 flex items-center justify-between">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-48 truncate">
                                    <button class="text-sm text-left font-medium truncate" @click="openTask(task)">{{
                                        task.name }}</button>
                                    <div class="text-xs text-gray-500 truncate">{{ task.project?.name }}</div>
                                </div>
                                <div class="text-xs text-green-600">Done</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <Link :href="route('tasks.show', task.id)" class="text-xs text-blue-600">View</Link>
                            </div>
                        </div>
                    </div>
                </section>

                <section v-if="noDue.length">
                    <header class="flex items-center gap-3 px-3 py-2 rounded-md bg-white border">
                        <span class="text-gray-700 font-medium">No due date</span>
                        <span class="text-sm text-gray-500">{{ noDue.length }}</span>
                    </header>
                    <div class="bg-white rounded-md shadow-sm divide-y mt-2">
                        <div v-for="task in noDue" :key="task.id" class="p-3 flex items-center justify-between">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-48 truncate">
                                    <button class="text-sm text-left font-medium truncate" @click="openTask(task)">{{
                                        task.name }}</button>
                                    <div class="text-xs text-gray-500 truncate">{{ task.project?.name }}</div>
                                </div>
                                <div class="text-xs text-gray-500">—</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button @click="assignToMe(task)" class="text-xs px-2 py-1 bg-gray-50 rounded">Assign to
                                    me</button>
                                <button @click="markDone(task)"
                                    class="text-xs px-2 py-1 bg-green-50 text-green-600 rounded">Mark done</button>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Drawer for task details -->
            <Drawer v-model:open="drawerOpen">
                <DrawerContent class="max-w-2xl p-4">
                    <div v-if="selectedTask">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-lg font-semibold">{{ selectedTask.name }}</h3>
                                <div class="text-sm text-gray-500">{{ selectedTask.project?.name }}</div>
                            </div>
                            <div class="text-sm text-gray-500">{{ selectedTask.assigneeTo?.name ?? 'Unassigned' }}</div>
                        </div>

                        <div class="mt-4">
                            <p class="text-sm text-gray-700" v-html="selectedTask.description"></p>

                            <div class="mt-4">
                                <h4 class="text-xs font-medium text-gray-500">Attachments</h4>
                                <div class="mt-2 space-y-2">
                                    <div v-for="att in selectedTask.attachments || []" :key="att.id"
                                        class="flex items-center justify-between p-2 border rounded">
                                        <div class="flex items-center gap-2">
                                            <FileIcon :type="att.type" />
                                            <div class="text-sm">{{ att.name }}</div>
                                        </div>
                                        <div class="flex gap-2">
                                            <a :href="att.path" class="text-xs text-blue-600">Download</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <h4 class="text-xs font-medium text-gray-500">Comments</h4>
                                <div class="mt-2 space-y-2 max-h-60 overflow-y-auto">
                                    <div v-for="c in selectedTask.comments || []" :key="c.id"
                                        class="p-2 bg-gray-50 rounded">
                                        <div class="text-xs font-semibold">{{ c.user?.name }}</div>
                                        <div class="text-sm">{{ c.body }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 flex gap-2">
                                <button @click="assignToMe(selectedTask)" class="px-3 py-1 bg-gray-50 rounded">Assign to
                                    me</button>
                                <button @click="markDone(selectedTask)"
                                    class="px-3 py-1 bg-green-50 text-green-600 rounded">Mark done</button>
                                <Link :href="route('tasks.edit', selectedTask.id)"
                                    class="px-3 py-1 bg-white rounded border">Edit</Link>
                            </div>
                        </div>
                    </div>
                </DrawerContent>
            </Drawer>
        </div>
    </AppLayout>
</template>

<style scoped>
/* small visual tweaks */
.panel {}
</style>
