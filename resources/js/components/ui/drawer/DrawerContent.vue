<script lang="ts" setup>
import type { DialogContentEmits, DialogContentProps } from "reka-ui"
import { computed, type HTMLAttributes } from "vue"
import { useForwardPropsEmits } from "reka-ui"
import { DrawerContent, DrawerPortal } from "vaul-vue"
import { cn } from "@/lib/utils"
import DrawerOverlay from "./DrawerOverlay.vue"

const props = defineProps<DialogContentProps & { class?: HTMLAttributes["class"] } & {
    position?: 'left' | 'right' | 'top' | 'bottom';
    width?: string;
}>()
const emits = defineEmits<DialogContentEmits>()



const computePosition = computed(() => {
  // Default to right position if not specified
  const position = props.position || 'right';

  // Map position to appropriate classes for vaul-vue
  switch (position) {
    case 'left':
      return 'inset-y-0 left-0';
    case 'top':
      return 'top-0 left-0 w-full';
    case 'right':
      return 'inset-y-0 right-0';
    case 'bottom':
      return 'inset-x-0 bottom-0';
  }
});


const computedWidth = computed(() => {
    return `${props.width}rem` || '40rem';
})


const forwarded = useForwardPropsEmits(props, emits)
</script>

<template>
  <DrawerPortal>
    <DrawerOverlay />
    <DrawerContent
      v-bind="forwarded" :class="cn(
        `fixed z-50 flex w-[${computedWidth}] h-auto flex-col rounded-t-[10px] border bg-background`,
        props.class,
        computePosition,
      )"
    >
      <!-- <div class="mx-auto mt-4 h-2 w-[40rem] rounded-full bg-muted" /> -->
      <slot />
    </DrawerContent>
  </DrawerPortal>
</template>
