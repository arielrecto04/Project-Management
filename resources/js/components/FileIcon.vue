<script setup lang="ts">
import {
    File,
    FileArchive,
    FileAudio,
    FileCode,
    FileImage,
    FileSpreadsheet,
    FileText,
    FileVideo, // Changed from FilePdf
    Presentation,
} from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    type: string;
}>();

const icon = computed(() => {
    console.log(props.type);
    const mimeType = props.type.toLowerCase();

    // Images
    if (mimeType.startsWith('image/')) return FileImage;

    // Videos
    if (mimeType.startsWith('video/')) return FileVideo;

    // Audio
    if (mimeType.startsWith('audio/')) return FileAudio;

    // PDFs
    if (mimeType === 'application/pdf') return FileText; // Changed from FilePdf

    // Archives
    if (mimeType.includes('zip') || mimeType.includes('compressed') || mimeType.includes('tar') || mimeType.includes('rar')) return FileArchive;

    // Code files
    if (
        mimeType.includes('javascript') ||
        mimeType.includes('json') ||
        mimeType.includes('html') ||
        mimeType.includes('css') ||
        mimeType.includes('xml') ||
        mimeType.includes('php')
    )
        return FileCode;

    // Microsoft Office Documents
    if (mimeType.includes('spreadsheet') || mimeType.includes('excel') || mimeType.includes('xls')) return FileSpreadsheet;

    if (mimeType.includes('wordprocessing') || mimeType.includes('msword') || mimeType.includes('doc')) return FileText;

    if (mimeType.includes('presentation') || mimeType.includes('powerpoint') || mimeType.includes('ppt')) return Presentation;

    // OpenDocument formats
    if (mimeType.includes('opendocument.text')) return FileText;
    if (mimeType.includes('opendocument.spreadsheet')) return FileSpreadsheet;
    if (mimeType.includes('opendocument.presentation')) return Presentation;

    // Plain text
    if (mimeType.startsWith('text/')) return FileText;

    // Default file icon
    return File;
});
</script>

<template>
    <component :is="icon" />
</template>
