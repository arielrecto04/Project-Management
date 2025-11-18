<script setup lang="ts">
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'

const {
  title = 'Sales Overview',
  description = 'Monthly sales data for the first 5 months.'
} = defineProps<{
  title?: string
  description?: string
}>()
</script>

<template>
  <Card class="w-full max-w-xl">
    <CardHeader>
      <CardTitle>{{ title }}</CardTitle>
      <CardDescription>{{ description }}</CardDescription>
    </CardHeader>
    <CardContent>
        <slot />
    </CardContent>
  </Card>
</template>
