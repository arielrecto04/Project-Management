<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';
import { CheckCircleIcon, XCircleIcon } from '@heroicons/vue/24/outline';
import { usePage } from '@inertiajs/vue3';
import { computed, defineProps, withDefaults } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const flash = computed(() => usePage().props.flash);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Flash Message -->
        <div
            v-if="flash?.message"
            :class="{
                'mb-4 rounded-lg p-4 shadow-sm transition-all': true,
                'border border-orange-200 bg-orange-50 text-orange-700': flash?.type === 'success',
                'border border-red-200 bg-red-50 text-red-700': flash?.type === 'error',
            }"
        >
            <div class="flex items-center gap-3">
                <CheckCircleIcon v-if="flash?.type === 'success'" class="h-5 w-5" />
                <XCircleIcon v-if="flash?.type === 'error'" class="h-5 w-5" />
                <p class="text-sm font-medium">{{ flash?.message }}</p>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 space-y-6 p-6">
            <slot />
        </div>

        <!-- Footer -->
        <footer class="mt-auto border-t border-gray-200 bg-white py-4">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-500">&copy; {{ new Date().getFullYear() }} Innovato. All rights reserved.</p>
                    <a
                        href="https://www.innovatotec.com"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="text-sm text-orange-600 hover:text-orange-700"
                    >
                        Visit Innovato
                    </a>
                </div>
            </div>
        </footer>
    </AppLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
