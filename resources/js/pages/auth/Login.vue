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
        <div class="flex flex-col flex-1 justify-center py-12 min-h-full sm:px-6 lg:px-8">
            <div class="sm:mx-auto sm:w-full sm:max-w-md">
                <img class="mx-auto w-auto h-10" src="https://iits.website/logo.png" alt="Innovato" />
                <h2 class="mt-6 text-2xl font-bold tracking-tight leading-9 text-center text-gray-900">Log in to your
                    account</h2>
            </div>

            <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-[480px]">
                <div class="px-6 py-12 bg-white shadow sm:rounded-lg sm:px-12">
                    <div v-if="status" class="mb-6 text-sm font-medium text-center text-green-600">
                        {{ status }}
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid gap-2">
                            <Label for="email" class="text-sm font-medium text-gray-700">Email address</Label>
                            <Input id="email" type="email" required autofocus :tabindex="1" autocomplete="email"
                                v-model="form.email" placeholder="email@example.com"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500" />
                            <InputError :message="form.errors.email" />
                        </div>

                        <div class="grid gap-2">
                            <div class="flex justify-between items-center">
                                <Label for="password" class="text-sm font-medium text-gray-700">Password</Label>
                                <TextLink v-if="canResetPassword" :href="route('password.request')"
                                    class="text-sm text-orange-600 hover:text-orange-500" :tabindex="5">
                                    Forgot password?
                                </TextLink>
                            </div>
                            <Input id="password" type="password" required :tabindex="2" autocomplete="current-password"
                                v-model="form.password" placeholder="Password"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500" />
                            <InputError :message="form.errors.password" />
                        </div>

                        <div class="flex items-center">
                            <Label for="remember" class="flex items-center space-x-3">
                                <Checkbox id="remember" v-model:checked="form.remember" :tabindex="4"
                                    class="text-orange-600 rounded border-gray-300 focus:ring-orange-500" />
                                <span class="text-sm text-gray-500">Remember me</span>
                            </Label>
                        </div>

                        <Button type="submit" class="w-full bg-orange-600 hover:bg-orange-700" :tabindex="4"
                            :disabled="form.processing">
                            <LoaderCircle v-if="form.processing" class="mr-2 w-4 h-4 animate-spin" />
                            Log in
                        </Button>

                        <div class="text-sm text-center text-gray-500">
                            Don't have an account?
                            <!-- <TextLink :href="route('register')" :tabindex="5" class="font-semibold text-orange-600 hover:text-orange-500">
                                Sign up
                            </TextLink> -->
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
