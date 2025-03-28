<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

interface Project {
    id: number;
    name: string;
}

interface User {
    id: number;
    name: string;
}

const props = defineProps<{
    projects: Project[];
    users: User[];
    project_id?: string;
}>();

const form = useForm({
    name: '',
    description: '',
    project_id: props.project_id || '',
    assignee_to: '',
    due_date: '',
    status: 'pending',
});

const submit = () => {
    form.post(route('tasks.store'));
};

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Tasks', href: '/tasks' },
    { title: 'Create Task', href: '#' },
];
</script>

<template>
    <Head title="Create Task" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <Card>
                <CardHeader>
                    <CardTitle>Create New Task</CardTitle>
                </CardHeader>
                <form @submit.prevent="submit">
                    <CardContent class="space-y-4">
                        <div>
                            <Label for="name">Task Name</Label>
                            <Input id="name" v-model="form.name" required />
                            <span v-if="form.errors.name" class="text-sm text-red-500">
                                {{ form.errors.name }}
                            </span>
                        </div>

                        <div>
                            <Label for="description">Description</Label>
                            <Textarea id="description" v-model="form.description" />
                            <span v-if="form.errors.description" class="text-sm text-red-500">
                                {{ form.errors.description }}
                            </span>
                        </div>

                        <div>
                            <Label for="project">Project</Label>
                            <select id="project" v-model="form.project_id" class="w-full rounded-md border-gray-300" required>
                                <option value="">Select a project</option>
                                <option v-for="project in projects" :key="project.id" :value="project.id">
                                    {{ project.name }}
                                </option>
                            </select>
                            <span v-if="form.errors.project_id" class="text-sm text-red-500">
                                {{ form.errors.project_id }}
                            </span>
                        </div>

                        <div>
                            <Label for="assignee">Assignee</Label>
                            <select id="assignee" v-model="form.assignee_to" class="w-full rounded-md border-gray-300">
                                <option value="">Select an assignee</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">
                                    {{ user.name }}
                                </option>
                            </select>
                            <span v-if="form.errors.assignee_to" class="text-sm text-red-500">
                                {{ form.errors.assignee_to }}
                            </span>
                        </div>

                        <div>
                            <Label for="due_date">Due Date</Label>
                            <Input id="due_date" type="date" v-model="form.due_date" required />
                            <span v-if="form.errors.due_date" class="text-sm text-red-500">
                                {{ form.errors.due_date }}
                            </span>
                        </div>

                        <div>
                            <Label for="status">Status</Label>
                            <select id="status" v-model="form.status" class="w-full rounded-md border-gray-300" required>
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                            <span v-if="form.errors.status" class="text-sm text-red-500">
                                {{ form.errors.status }}
                            </span>
                        </div>
                    </CardContent>
                    <CardFooter class="flex justify-end gap-2">
                        <Button type="button" variant="outline" :href="route('tasks.index')"> Cancel </Button>
                        <Button type="submit" :disabled="form.processing"> Create Task </Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>
