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
                console.log('Emitting item-moved with:', { itemId: item.id, newStatus });
                emit('item-moved', {
                    itemId: item.id,
                    newStatus,
                });
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

console.log(props.columns);
</script>

<template>
    <div class="flex overflow-x-auto gap-4 p-4">
        <div class="flex gap-4 min-w-full">
            <div
                v-for="column in columns"
                :key="column.id"
                class="w-[350px] flex-shrink-0 rounded-lg bg-orange-50/50 p-4 shadow-md"
                @dragover.prevent
                @drop="handleDrop($event, column)"
            >
                <h2 class="flex justify-between items-center pb-2 mb-4 font-semibold text-gray-700 border-b border-orange-200/50">
                    <span>{{ column.title }}</span>
                    <span class="px-2 py-0.5 text-sm text-orange-700 bg-orange-100 rounded-full">
                        {{ column.tasks.length }}
                    </span>
                </h2>
                <div class="flex flex-col gap-3">
                    <div
                        v-for="item in column.tasks"
                        :key="item.id"
                        class="p-4 bg-white rounded-lg shadow-sm transition-all cursor-move group hover:shadow-md"
                        draggable="true"
                        @dragstart="handleDragStart($event, item)"
                    >
                        <!-- Card Header -->
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="font-medium text-gray-800">{{ item.title }}</h3>
                            <span class="px-2 py-0.5 text-xs rounded-full" :class="getStatusColor(item.status)">
                                {{ ProjectStatusLabels[item.status as ProjectStatus] }}
                            </span>
                        </div>

                        <!-- Card Content -->
                        <div class="space-y-3 text-sm">
                            <p v-if="item.description" class="text-gray-600 line-clamp-2">
                                <span v-html="item.description"></span>
                            </p>

                            <!-- Dates -->
                            <div v-if="item.start_date || item.end_date" class="flex gap-4 text-xs text-gray-500">
                                <span v-if="item.start_date">{{ new Date(item.start_date).toLocaleDateString() }}</span>
                                <span v-if="item.end_date">{{ new Date(item.end_date).toLocaleDateString() }}</span>
                            </div>

                            <!-- Assignee -->
                            <div v-if="item.assignee" class="flex gap-2 items-center">
                                <div class="flex justify-center items-center w-6 h-6 text-xs text-orange-700 bg-orange-100 rounded-full">
                                    {{ item.assignee.charAt(0).toUpperCase() }}
                                </div>
                                <span class="text-sm text-gray-600">{{ item.assignee }}</span>
                            </div>
                        </div>

                        <!-- Card Actions -->

                        <div class="flex gap-2 justify-end pt-2 mt-3 border-t border-gray-100 opacity-0 transition-opacity group-hover:opacity-100">
                            <slot name="actions" :item="item">
                                <Link :href="getItemRoute('edit', item)" class="p-1 text-gray-400 hover:text-orange-600">
                                    <PencilIcon class="w-4 h-4" />
                                </Link>
                                <Link :href="getItemRoute('show', item)" class="p-1 text-gray-400 hover:text-orange-600">
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
.kanban-board {
    /* Style as needed */
}

.kanban-column {
    min-height: 300px;
}

.task {
    user-select: none;
}
</style>
