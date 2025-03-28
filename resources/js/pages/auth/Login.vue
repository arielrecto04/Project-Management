<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <div class="flex min-h-full flex-1 flex-col justify-center py-12 sm:px-6 lg:px-8">
            <div class="sm:mx-auto sm:w-full sm:max-w-md">
                <img class="mx-auto h-10 w-auto" src="https://www.innovatotec.com/web/image/277-b178a65d/full%20logo.webp" alt="Innovato" />
                <h2 class="mt-6 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900">Log in to your account</h2>
            </div>

            <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-[480px]">
                <div class="bg-white px-6 py-12 shadow sm:rounded-lg sm:px-12">
                    <div v-if="status" class="mb-6 text-center text-sm font-medium text-green-600">
                        {{ status }}
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid gap-2">
                            <Label for="email" class="text-sm font-medium text-gray-700">Email address</Label>
                            <Input
                                id="email"
                                type="email"
                                required
                                autofocus
                                :tabindex="1"
                                autocomplete="email"
                                v-model="form.email"
                                placeholder="email@example.com"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"
                            />
                            <InputError :message="form.errors.email" />
                        </div>

                        <div class="grid gap-2">
                            <div class="flex items-center justify-between">
                                <Label for="password" class="text-sm font-medium text-gray-700">Password</Label>
                                <TextLink
                                    v-if="canResetPassword"
                                    :href="route('password.request')"
                                    class="text-sm text-orange-600 hover:text-orange-500"
                                    :tabindex="5"
                                >
                                    Forgot password?
                                </TextLink>
                            </div>
                            <Input
                                id="password"
                                type="password"
                                required
                                :tabindex="2"
                                autocomplete="current-password"
                                v-model="form.password"
                                placeholder="Password"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"
                            />
                            <InputError :message="form.errors.password" />
                        </div>

                        <div class="flex items-center">
                            <Label for="remember" class="flex items-center space-x-3">
                                <Checkbox
                                    id="remember"
                                    v-model:checked="form.remember"
                                    :tabindex="4"
                                    class="rounded border-gray-300 text-orange-600 focus:ring-orange-500"
                                />
                                <span class="text-sm text-gray-500">Remember me</span>
                            </Label>
                        </div>

                        <Button type="submit" class="w-full bg-orange-600 hover:bg-orange-700" :tabindex="4" :disabled="form.processing">
                            <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                            Log in
                        </Button>

                        <div class="text-center text-sm text-gray-500">
                            Don't have an account?
                            <TextLink :href="route('register')" :tabindex="5" class="font-semibold text-orange-600 hover:text-orange-500">
                                Sign up
                            </TextLink>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
