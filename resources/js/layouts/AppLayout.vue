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
        <Transition name="fade">
            <div v-if="flash?.message" :class="{
                'mb-4 rounded-lg p-3 shadow-sm transition-all duration-300 sm:p-4': true,
                'border border-orange-200 bg-orange-50 text-orange-700': flash?.type === 'success',
                'border border-red-200 bg-red-50 text-red-700': flash?.type === 'error',
            }" role="alert">
                <div class="flex items-start gap-2 sm:items-center sm:gap-3">
                    <CheckCircleIcon v-if="flash?.type === 'success'" class="h-5 w-5 flex-shrink-0" />
                    <XCircleIcon v-if="flash?.type === 'error'" class="h-5 w-5 flex-shrink-0" />
                    <p class="text-sm font-medium sm:text-base">{{ flash?.message }}</p>
                </div>
            </div>
        </Transition>

        <!-- Main Content -->
        <div class="flex-1 space-y-4 p-4 sm:space-y-6 sm:p-6 max-w-[1280px] w-full">
            <slot />
        </div>

        <!-- Footer -->
        <footer class="mt-auto border-t border-gray-200 bg-white py-3 sm:py-4">
            <div class="mx-auto w-full max-w-full px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col items-center justify-between gap-3 sm:flex-row sm:gap-4">
                    <p class="text-center text-xs text-gray-500 sm:text-left sm:text-sm">
                        &copy; {{ new Date().getFullYear() }} Innovato. All rights reserved.
                    </p>
                    <a href="https://www.innovatotec.com" target="_blank" rel="noopener noreferrer"
                        class="text-xs text-orange-600 hover:text-orange-700 hover:underline sm:text-sm">
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

/* Responsive utilities */
@media (max-width: 640px) {
    :deep(.container) {
        padding: 0;
    }
}
</style>
