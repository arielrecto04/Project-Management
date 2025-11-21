<script setup lang="ts">
import { ProjectStatus, ProjectStatusLabels } from '@/enums/ProjectStatus';
import { EyeIcon, PencilIcon } from '@heroicons/vue/24/outline';
import { Link } from '@inertiajs/vue3';
import { defineEmits, defineProps } from 'vue';

interface BaseItem {
    id: number;
    title: string;
    status: string;
    description?: string;
    assignee?: string;
    start_date?: string;
    end_date?: string;
    priority?: 'low' | 'medium' | 'high';
}

interface Column {
    id: number;
    title: string;
    tasks: BaseItem[];
}

interface Props {
    columns: Column[];
    type: 'project' | 'task';
}

const props = defineProps<Props>();

const emit = defineEmits<{
    (event: 'item-moved', payload: { itemId: number; newStatus: string }): void;
}>();

const handleDragStart = (event: DragEvent, item: BaseItem) => {
    if (event.dataTransfer) {
        event.dataTransfer.setData('application/json', JSON.stringify(item));
        event.dataTransfer.effectAllowed = 'move';
    }
};

const handleDrop = (event: DragEvent, targetColumn: Column) => {
    event.preventDefault();

    if (event.dataTransfer) {
        const data = event.dataTransfer.getData('application/json');
        if (!data) return;

        try {
            const item: BaseItem = JSON.parse(data);
            const statusMap: Record<string, string> = {
                'To Do': ProjectStatus.Pending,
                'In Progress': ProjectStatus.InProgress,
                Done: ProjectStatus.Completed,
            };

            const newStatus = statusMap[targetColumn.title];

            if (newStatus && item.status !== newStatus) {
                emit('item-moved', { itemId: item.id, newStatus });
            }
        } catch (error) {
            console.error('Error parsing drag data:', error);
        }
    }
};

const getItemRoute = (action: 'edit' | 'show', item: BaseItem) => {
    const baseRoute = props.type === 'project' ? 'projects' : 'tasks';
    return route(`${baseRoute}.${action}`, item.id);
};

const getStatusColor = (status: string) => {
    switch (status) {
        case ProjectStatus.Pending:
            return 'bg-yellow-100 text-yellow-800';
        case ProjectStatus.InProgress:
            return 'bg-blue-100 text-blue-800';
        case ProjectStatus.Completed:
            return 'bg-green-100 text-green-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};
</script>

<template>
    <!-- outer: wraps on small screens, horizontal on md+ -->
    <div class="flex flex-wrap md:flex-nowrap overflow-x-auto gap-4 p-4">
        <div class="flex gap-4">
            <div v-for="column in props.columns" :key="column.id" role="region" :aria-label="`Column ${column.title}`"
                class="w-full sm:w-[320px] md:w-[350px] flex-shrink-0 rounded-lg bg-orange-50/50 p-4 shadow-sm"
                @dragover.prevent @drop="handleDrop($event, column)">
                <h2
                    class="flex justify-between items-center pb-2 mb-4 font-semibold text-gray-700 border-b border-orange-200/50">
                    <span class="truncate">{{ column.title }}</span>
                    <span class="px-2 py-0.5 text-sm text-orange-700 bg-orange-100 rounded-full">
                        {{ column.tasks.length }}
                    </span>
                </h2>

                <div class="flex flex-col gap-3">
                    <div v-for="item in column.tasks" :key="item.id"
                        class="p-3 sm:p-4 bg-white rounded-lg shadow-sm transition-all cursor-grab touch-pan-y group hover:shadow-md"
                        draggable="true" @dragstart="handleDragStart($event, item)" @touchstart.prevent>
                        <!-- Card Header slot -->
                        <slot name="card-header" :item="item">
                            <div class="flex justify-between items-start mb-2 gap-3">
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-medium text-gray-800 text-sm truncate">{{ item.title }}</h3>
                                    <div class="mt-1 flex items-center gap-2">
                                        <span
                                            :class="['inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full', getStatusColor(item.status)]">
                                            {{ ProjectStatusLabels[item.status as keyof typeof ProjectStatusLabels] ??
                                            item.status }}
                                        </span>
                                        <span v-if="item.priority" class="text-xs text-gray-500">• {{ item.priority
                                            }}</span>
                                    </div>
                                </div>
                                <div
                                    class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <Link :href="getItemRoute('edit', item)"
                                        class="p-1 text-gray-400 hover:text-orange-600" aria-label="Edit">
                                    <PencilIcon class="w-4 h-4" />
                                    </Link>
                                </div>
                            </div>
                        </slot>

                        <!-- Card Content slot -->
                        <slot name="card-content" :item="item">
                            <div class="space-y-3 text-sm">
                                <p v-if="item.description" class="text-gray-600 line-clamp-2 leading-relaxed">
                                    <span v-html="item.description"></span>
                                </p>

                                <div v-if="item.start_date || item.end_date"
                                    class="flex gap-3 text-xs text-gray-500 flex-wrap">
                                    <span v-if="item.start_date">{{ new Date(item.start_date).toLocaleDateString()
                                        }}</span>
                                    <span v-if="item.end_date">{{ new Date(item.end_date).toLocaleDateString() }}</span>
                                </div>

                                <div v-if="item.assignee" class="flex gap-2 items-center mt-1">
                                    <div
                                        class="flex justify-center items-center w-6 h-6 text-xs text-orange-700 bg-orange-100 rounded-full">
                                        {{ item.assignee.charAt(0).toUpperCase() }}
                                    </div>
                                    <span class="text-sm text-gray-600 truncate">{{ item.assignee }}</span>
                                </div>
                            </div>
                        </slot>

                        <!-- Card actions -->
                        <div
                            class="flex gap-2 justify-end pt-2 mt-3 border-t border-gray-100 opacity-0 transition-opacity group-hover:opacity-100">
                            <slot name="actions" :item="item">
                                <Link :href="getItemRoute('show', item)" class="p-1 text-gray-400 hover:text-orange-600"
                                    aria-label="View">
                                <EyeIcon class="w-4 h-4" />
                                </Link>
                            </slot>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Improve touch interaction and responsiveness */
.task {
    user-select: none;
    -webkit-user-drag: none;
}

/* ensure touch scrolling vertically inside column content when needed */
[draggable="true"] {
    touch-action: manipulation;
}

/* small screens: slightly tighter spacing and font scaling */
@media (max-width: 640px) {
    .line-clamp-2 {
        -webkit-line-clamp: 4;
    }
}

/* allow columns to show subtle elevation on wide screens */
@media (min-width: 768px) {
    .shadow-sm {
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
    }
}
</style>
