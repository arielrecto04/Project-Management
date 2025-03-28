<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    project: {
        id: number;
        name: string;
        description: string;
        assignee_id: number;
        start_date: string;
        end_date: string;
        status: string;
    };
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Projects', href: '/projects' },
    { title: 'Edit Project', href: '#' },
];

const form = useForm({
    name: props.project.name,
    description: props.project.description,
    assignee_id: props.project.assignee_id,
    start_date: props.project.start_date,
    end_date: props.project.end_date,
    status: props.project.status,
});

const submit = () => {
    form.put(route('projects.update', props.project.id));
};
</script>

<template>
    <Head title="Edit Project" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <Card>
                <CardHeader>
                    <CardTitle>Edit Project</CardTitle>
                </CardHeader>
                <form @submit.prevent="submit">
                    <CardContent class="space-y-4">
                        <div>
                            <Label for="name">Project Name</Label>
                            <Input id="name" v-model="form.name" type="text" required />
                        </div>

                        <div>
                            <Label for="description">Description</Label>
                            <Textarea id="description" v-model="form.description" />
                        </div>

                        <div>
                            <Label for="start_date">Start Date</Label>
                            <Input id="start_date" v-model="form.start_date" type="date" />
                        </div>

                        <div>
                            <Label for="end_date">End Date</Label>
                            <Input id="end_date" v-model="form.end_date" type="date" />
                        </div>

                        <div>
                            <Label for="status">Status</Label>
                            <select id="status" v-model="form.status" class="w-full rounded-md border">
                                <option value="To Do">To Do</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Done">Done</option>
                            </select>
                        </div>
                    </CardContent>
                    <CardFooter class="flex justify-end space-x-2">
                        <Button variant="outline" :href="route('projects.index')">Cancel</Button>
                        <Button type="submit" :disabled="form.processing">Update Project</Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>
