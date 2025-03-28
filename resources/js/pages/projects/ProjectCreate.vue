<script setup lang="ts">
import QuillEditor from '@/components/QuillEditor.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Projects', href: '/projects' },
    { title: 'Create Project', href: '#' },
];

const form = useForm({
    name: '',
    description: '', // Initialize as empty string
    start_date: '',
    end_date: '',
    assignee_id: '',
    status: 'To Do',
});

const submit = () => {
    form.post(route('projects.store'));
};
</script>

<template>
    <Head title="Create Project" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <Card>
                <CardHeader>
                    <CardTitle>Create New Project</CardTitle>
                </CardHeader>
                <form @submit.prevent="submit">
                    <CardContent class="space-y-4">
                        <div>
                            <Label for="name">Project Name</Label>
                            <Input id="name" v-model="form.name" type="text" required />
                        </div>

                        <div>
                            <Label for="description">Description</Label>
                            <QuillEditor v-model="form.description" placeholder="Enter description..." />
                            <!-- <Textarea id="description" v-model="form.description" /> -->
                        </div>

                        <div>
                            <Label for="start_date">Start Date</Label>
                            <Input id="start_date" v-model="form.start_date" type="date" />
                        </div>

                        <div>
                            <Label for="end_date">End Date</Label>
                            <Input id="end_date" v-model="form.end_date" type="date" />
                        </div>
                    </CardContent>
                    <CardFooter class="flex justify-end space-x-2">
                        <Button variant="outline" :href="route('projects.index')">Cancel</Button>
                        <Button type="submit" :disabled="form.processing">Create Project</Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>
