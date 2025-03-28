<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Download, X } from 'lucide-vue-next';
import * as pdfjsLib from 'pdfjs-dist';
import { ref, watch } from 'vue';
import * as XLSX from 'xlsx';

pdfjsLib.GlobalWorkerOptions.workerSrc = '/js/pdf.worker.min.js';

interface Props {
    isOpen: boolean;
    file: {
        name: string;
        path: string;
        type: string;
    } | null;
}

const props = defineProps<Props>();
const emit = defineEmits(['close']);

const viewerContainer = ref<HTMLElement | null>(null);
const isLoading = ref(false);
const error = ref<string | null>(null);

const isImage = (type: string) => type?.startsWith('image/');
const isPDF = (type: string) => type === 'application/pdf';
const isExcel = (type: string) => {
    return ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'].includes(type);
};

const clearViewer = () => {
    if (viewerContainer.value) {
        while (viewerContainer.value.firstChild) {
            viewerContainer.value.removeChild(viewerContainer.value.firstChild);
        }
    }
};

const displayImage = async () => {
    if (!props.file || !viewerContainer.value) return;

    clearViewer();

    const container = document.createElement('div');
    container.style.width = '100%';
    container.style.height = '100%';
    container.style.display = 'flex';
    container.style.justifyContent = 'center';
    container.style.alignItems = 'center';

    const img = new Image();
    img.style.maxWidth = '100%';
    img.style.maxHeight = '600px';
    img.style.objectFit = 'contain';

    const loadPromise = new Promise((resolve, reject) => {
        img.onload = resolve;
        img.onerror = reject;
    });

    img.src = props.file.path;
    container.appendChild(img);
    viewerContainer.value.appendChild(container);

    await loadPromise;
};

const displayPDF = async () => {
    if (!props.file || !viewerContainer.value) return;

    clearViewer();

    const canvas = document.createElement('canvas');
    canvas.style.width = '100%';
    viewerContainer.value.appendChild(canvas);

    try {
        const loadingTask = pdfjsLib.getDocument(props.file.path);
        const pdf = await loadingTask.promise;
        const page = await pdf.getPage(1);

        const viewport = page.getViewport({ scale: 1.5 });
        canvas.height = viewport.height;
        canvas.width = viewport.width;

        await page.render({
            canvasContext: canvas.getContext('2d')!,
            viewport,
        }).promise;
    } catch (err) {
        error.value = 'Failed to load PDF';
        console.error(err);
    }
};

const displayExcel = async () => {
    if (!props.file || !viewerContainer.value) return;

    clearViewer();

    try {
        const response = await fetch(props.file.path);
        const arrayBuffer = await response.arrayBuffer();
        const workbook = XLSX.read(arrayBuffer);
        const worksheet = workbook.Sheets[workbook.SheetNames[0]];
        const data = XLSX.utils.sheet_to_json(worksheet, { header: 1 });

        const table = document.createElement('table');
        table.className = 'min-w-full divide-y divide-gray-200';

        // Create header
        if (data.length > 0) {
            const thead = table.createTHead();
            const headerRow = thead.insertRow();
            data[0].forEach((cell: any) => {
                const th = document.createElement('th');
                th.className = 'px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase';
                th.textContent = cell?.toString() || '';
                headerRow.appendChild(th);
            });
        }

        // Create body
        const tbody = table.createTBody();
        data.slice(1).forEach((row: any) => {
            const tr = tbody.insertRow();
            row.forEach((cell: any) => {
                const td = tr.insertCell();
                td.className = 'px-6 py-4 whitespace-nowrap text-sm text-gray-900';
                td.textContent = cell?.toString() || '';
            });
        });

        viewerContainer.value.appendChild(table);
    } catch (err) {
        error.value = 'Failed to load Excel file';
        console.error(err);
    }
};

const loadContent = async () => {
    if (!props.file) return;

    isLoading.value = true;
    error.value = null;

    try {
        if (isImage(props.file.type)) {
            await displayImage();
        } else if (isPDF(props.file.type)) {
            await displayPDF();
        } else if (isExcel(props.file.type)) {
            await displayExcel();
        } else {
            error.value = 'Unsupported file type';
        }
    } catch (err) {
        error.value = 'Failed to load file';
        console.error(err);
    } finally {
        isLoading.value = false;
    }
};

watch(
    () => props.isOpen,
    (newValue) => {
        if (newValue && props.file) {
            loadContent();
        }
    },
);

const close = () => {
    clearViewer();
    emit('close');
};
</script>

<template>
    <Dialog :open="isOpen" @close="close">
        <DialogContent class="max-h-[90vh] max-w-4xl">
            <DialogHeader>
                <DialogTitle class="flex items-center justify-between">
                    <span>{{ file?.name }}</span>
                    <div class="flex gap-2">
                        <Button v-if="file" variant="ghost" size="sm" @click="window.open(file.path, '_blank')">
                            <Download class="h-4 w-4" />
                        </Button>
                        <Button variant="ghost" size="sm" @click="close">
                            <X class="h-4 w-4" />
                        </Button>
                    </div>
                </DialogTitle>
            </DialogHeader>

            <div class="overflow-auto p-4" style="max-height: calc(90vh - 100px)">
                <div v-if="isLoading" class="flex h-64 items-center justify-center">
                    <div class="h-8 w-8 animate-spin rounded-full border-2 border-orange-500 border-t-transparent"></div>
                </div>

                <div v-else-if="error" class="flex h-64 flex-col items-center justify-center">
                    <p class="text-red-500">{{ error }}</p>
                    <Button v-if="file" variant="outline" size="sm" class="mt-4" @click="window.open(file.path, '_blank')"> Download Instead </Button>
                </div>

                <div v-else ref="viewerContainer" class="min-h-[400px]"></div>
            </div>
        </DialogContent>
    </Dialog>
</template>
