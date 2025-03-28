<script setup lang="ts">
import { computed, defineEmits, defineProps } from 'vue'

const props = defineProps<{
  modelValue?: string
  class?: string
  error?: string
}>()

const emit = defineEmits(['update:modelValue'])

const classes = computed(() => {
  return [
    'flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
    props.error ? 'border-red-500' : 'border-gray-200',
    props.class
  ]
})
</script>

<template>
  <textarea
    :value="modelValue"
    @input="$emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
    :class="classes"
  />
  <span v-if="error" class="mt-1 text-sm text-red-500">{{ error }}</span>
</template>
