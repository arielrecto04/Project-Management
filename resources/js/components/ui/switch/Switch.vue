<script setup lang="ts">
import { computed } from 'vue'
import { cn } from '@/lib/utils'

interface Props {
  checked?: boolean
  defaultChecked?: boolean
  disabled?: boolean
  class?: string
}

const props = withDefaults(defineProps<Props>(), {
  checked: false,
  defaultChecked: false,
  disabled: false,
  class: '',
})

const emit = defineEmits<{
  (e: 'update:checked', checked: boolean): void
}>()

const isChecked = computed({
  get: () => props.checked,
  set: (value) => emit('update:checked', value),
})

const toggleSwitch = () => {
  if (!props.disabled) {
    isChecked.value = !isChecked.value
  }
}
</script>

<template>
  <button
    type="button"
    role="switch"
    :aria-checked="isChecked"
    :data-state="isChecked ? 'checked' : 'unchecked'"
    :disabled="disabled"
    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-400 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
    :class="[
      cn(
        isChecked ? 'bg-orange-600' : 'bg-gray-200',
        props.class
      )
    ]"
    @click="toggleSwitch"
  >
    <span
      class="pointer-events-none block h-5 w-5 rounded-full bg-white shadow-lg ring-0 transition-transform"
      :class="[
        isChecked ? 'translate-x-5' : 'translate-x-0'
      ]"
    />
  </button>
</template>
