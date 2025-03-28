<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Project Management System">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" />
    </Head>

    <div class="min-h-screen bg-white">
        <!-- Navigation -->
        <nav class="fixed top-0 z-50 w-full bg-white shadow-sm">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between">
                    <div class="flex">
                        <div class="flex flex-shrink-0 items-center">
                            <img class="h-8 w-auto" src="https://www.innovatotec.com/web/image/277-b178a65d/full%20logo.webp" alt="Innovato" />
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <template v-if="$page.props.auth.user">
                            <Link
                                :href="route('dashboard')"
                                class="rounded-md bg-orange-600 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-700"
                            >
                                Dashboard
                            </Link>
                        </template>
                        <template v-else>
                            <Link :href="route('login')" class="text-sm font-semibold text-gray-700 hover:text-orange-600"> Log in </Link>
                            <Link
                                :href="route('register')"
                                class="rounded-md bg-orange-600 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-700"
                            >
                                Get Started
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="relative isolate pt-14">
            <div class="mx-auto max-w-7xl px-6 py-24 sm:py-32 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-6xl">Project Management Made Simple</h1>
                    <p class="mt-6 text-lg leading-8 text-gray-600">
                        Streamline your projects, boost collaboration, and deliver results with Innovato's comprehensive project management solution.
                    </p>
                    <div class="mt-10 flex items-center justify-center gap-x-6">
                        <Link
                            :href="route('register')"
                            class="rounded-md bg-orange-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-orange-700"
                        >
                            Start Free Trial
                        </Link>
                        <a href="#features" class="text-sm font-semibold leading-6 text-gray-900"> Learn more <span aria-hidden="true">→</span> </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div id="features" class="bg-gray-50 py-24 sm:py-32">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-2xl lg:text-center">
                    <h2 class="text-base font-semibold leading-7 text-orange-600">Manage Better</h2>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">Everything you need to manage your projects</p>
                </div>
                <div class="mx-auto mt-16 max-w-2xl sm:mt-20 lg:mt-24 lg:max-w-none">
                    <dl class="grid max-w-xl grid-cols-1 gap-x-8 gap-y-16 lg:max-w-none lg:grid-cols-3">
                        <div class="flex flex-col">
                            <dt class="flex items-center gap-x-3 text-base font-semibold leading-7 text-gray-900">
                                <svg class="h-5 w-5 flex-none text-orange-600" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                                    />
                                </svg>
                                Task Management
                            </dt>
                            <dd class="mt-4 flex flex-auto flex-col text-base leading-7 text-gray-600">
                                <p class="flex-auto">Create, assign, and track tasks easily. Use our Kanban board for visual project management.</p>
                            </dd>
                        </div>
                        <!-- Add more features -->
                    </dl>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-gray-900">
            <div class="mx-auto max-w-7xl px-6 py-12 md:flex md:items-center md:justify-between lg:px-8">
                <div class="flex justify-center space-x-6 md:order-2">
                    <a href="https://www.innovatotec.com" class="text-gray-400 hover:text-gray-300"> Visit Innovato </a>
                </div>
                <div class="mt-8 md:order-1 md:mt-0">
                    <p class="text-center text-xs leading-5 text-gray-400">&copy; {{ new Date().getFullYear() }} Innovato. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<style>
/* Add custom styles here */
body {
    font-family: 'Inter', sans-serif;
}
</style>
