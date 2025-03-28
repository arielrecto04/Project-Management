<script setup lang="ts">
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';
import { nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps<{
    modelValue: string;
    placeholder?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const quillRef = ref();
const suggestionsMenu = ref<HTMLElement>();
const suggestionsVisible = ref(false);
const selectedIndex = ref(0);
const lastSlashIndex = ref<number | null>(null);
let isUpdating = false;

// Watch for external model updates and set Quill content if changed
watch(
    () => props.modelValue,
    (newValue) => {
        if (!quillRef.value || isUpdating) return;
        const quill = quillRef.value.getQuill();
        if (!quill) return;

        const currentContent = quill.root.innerHTML;
        if (currentContent !== newValue) {
            isUpdating = true;
            quill.clipboard.dangerouslyPasteHTML(newValue);
            isUpdating = false;
        }
    },
);

// Handle content updates
const handleContentUpdate = (delta: any, oldDelta: any, source: string) => {
    if (isUpdating || source !== 'user') return;
    isUpdating = true;

    const quill = quillRef.value?.getQuill();
    if (!quill) return;

    const html = quill.root.innerHTML;
    emit('update:modelValue', html);
    isUpdating = false;

    // Detect slash command
    const text = quill.getText();
    const cursorPosition = quill.getSelection()?.index ?? 0;
    const lastText = text.substring(0, cursorPosition);

    const lastSlash = lastText.lastIndexOf('/');
    if (lastSlash !== -1 && (lastSlash === 0 || lastText[lastSlash - 1] === ' ')) {
        lastSlashIndex.value = lastSlash;
        showSuggestions(quill);
    } else {
        suggestionsVisible.value = false;
    }
};

// Slash command options
const SLASH_COMMANDS = [
    {
        id: 'heading1',
        label: 'Heading 1',
        icon: 'H1',
        description: 'Large section heading',
        handler: (quill: any) => quill.format('header', 1),
    },
    {
        id: 'heading2',
        label: 'Heading 2',
        icon: 'H2',
        description: 'Medium section heading',
        handler: (quill: any) => quill.format('header', 2),
    },
    {
        id: 'bullet',
        label: 'Bullet List',
        icon: '•',
        description: 'Create a bullet list',
        handler: (quill: any) => quill.format('list', 'bullet'),
    },
    {
        id: 'numbered',
        label: 'Numbered List',
        icon: '1.',
        description: 'Create a numbered list',
        handler: (quill: any) => quill.format('list', 'ordered'),
    },
    {
        id: 'blockquote',
        label: 'Quote Block',
        icon: '"',
        description: 'Insert a quote',
        handler: (quill: any) => quill.format('blockquote', true),
    },
    {
        id: 'code',
        label: 'Code Block',
        icon: '</>',
        description: 'Insert code snippet',
        handler: (quill: any) => quill.format('code-block', true),
    },
];

// Show slash command menu
const showSuggestions = (quill: any) => {
    suggestionsVisible.value = true;
    selectedIndex.value = 0;

    nextTick(() => {
        if (!suggestionsMenu.value) return;
        const range = quill.getSelection();
        if (!range) return;

        const bounds = quill.getBounds(range.index);
        suggestionsMenu.value.style.top = `${bounds.top + 30}px`;
        suggestionsMenu.value.style.left = `${bounds.left}px`;
        suggestionsMenu.value.style.visibility = 'visible';
    });
};

// Execute selected command
const executeCommand = (command: (typeof SLASH_COMMANDS)[0]) => {
    const quill = quillRef.value?.getQuill();
    if (!quill || lastSlashIndex.value === null) return;

    try {
        // Get current selection and text
        const range = quill.getSelection();
        if (!range) return;

        // Delete the slash command text
        const deleteLength = range.index - lastSlashIndex.value;
        quill.deleteText(lastSlashIndex.value, deleteLength + 1); // +1 to include the slash

        // Move cursor to slash position
        quill.setSelection(lastSlashIndex.value, 0);

        // Apply the formatting
        command.handler(quill);

        // Insert a newline after formatting
        const endPosition = lastSlashIndex.value;
        quill.insertText(endPosition, '\n', 'user');

        // Move cursor to new position
        quill.setSelection(endPosition + 1, 0);

        // Emit updated content
        const html = quill.root.innerHTML;
        emit('update:modelValue', html);

        // Ensure focus remains on editor
        quill.focus();
    } catch (error) {
        console.error('Error executing command:', error);
    } finally {
        // Reset state
        suggestionsVisible.value = false;
        lastSlashIndex.value = null;
    }
};

// Handle keyboard navigation for slash menu
const handleKeyDown = (e: KeyboardEvent) => {
    if (!suggestionsVisible.value) return;

    switch (e.key) {
        case 'ArrowUp':
            e.preventDefault();
            selectedIndex.value = Math.max(0, selectedIndex.value - 1);
            highlightSelected();
            break;
        case 'ArrowDown':
            e.preventDefault();
            selectedIndex.value = Math.min(SLASH_COMMANDS.length - 1, selectedIndex.value + 1);
            highlightSelected();
            break;
        case 'Enter':
            e.preventDefault();
            const selectedCommand = SLASH_COMMANDS[selectedIndex.value];
            if (selectedCommand) {
                executeCommand(selectedCommand);
            }
            break;
        case 'Escape':
            e.preventDefault();
            suggestionsVisible.value = false;
            break;
    }
};

// Add helper function to highlight selected item
const highlightSelected = () => {
    const items = document.querySelectorAll('.suggestion-item');
    items.forEach((item, index) => {
        if (index === selectedIndex.value) {
            item.classList.add('bg-gray-100');
        } else {
            item.classList.remove('bg-gray-100');
        }
    });
};

// Mount & unmount listeners
onMounted(() => {
    const quill = quillRef.value?.getQuill();
    if (!quill) return;

    if (props.modelValue) {
        quill.clipboard.dangerouslyPasteHTML(props.modelValue);
    }

    quill.on('text-change', handleContentUpdate);
    document.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeyDown);
});
</script>

<template>
    <div class="relative">
        <QuillEditor
            ref="quillRef"
            @text-change="handleContentUpdate"
            :options="{
                theme: 'snow',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline'],
                        ['blockquote', 'code-block'],
                        [{ header: 1 }, { header: 2 }],
                    ],
                },
                placeholder: placeholder,
            }"
        />
        <div
            v-if="suggestionsVisible"
            ref="suggestionsMenu"
            class="suggestions-menu absolute w-64 overflow-hidden rounded-md border bg-white shadow-lg"
        >
            <div
                v-for="(command, index) in SLASH_COMMANDS"
                :key="command.id"
                class="suggestion-item flex cursor-pointer items-center gap-2 p-2 hover:bg-gray-100"
                :class="{ 'bg-gray-100': index === selectedIndex }"
                @click="executeCommand(command)"
                @mouseenter="selectedIndex = index"
            >
                <span class="flex h-6 w-6 items-center justify-center rounded bg-gray-50">
                    <strong>{{ command.icon }}</strong>
                </span>
                <div>
                    <div class="font-medium">{{ command.label }}</div>
                    <div class="text-sm text-gray-500">{{ command.description }}</div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
.ql-editor {
    min-height: 200px;
    padding: 12px 15px;
}
.suggestions-menu {
    position: absolute;
    z-index: 1000;
    visibility: hidden;
    max-height: 300px;
    overflow-y: auto;
}
.suggestion-item {
    transition: background-color 0.15s ease;
}
</style>
