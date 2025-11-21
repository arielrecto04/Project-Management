<script setup lang="ts">
import { VisScatter, VisXYContainer } from '@unovis/vue'
import { ChartContainer } from '@/components/ui/chart'
import { computed } from 'vue'

type P = { x: number; y: number; r?: number }

const defaultData: P[] = [
    { x: 10, y: 20, r: 4 },
    { x: 20, y: 10, r: 6 },
    { x: 30, y: 30, r: 5 },
]

const props = defineProps<{
    data?: P[]
    color?: string
}>()

const chartData = computed(() => (props?.data && props?.data.length ? props?.data : defaultData))
const color = computed(() => props.color ?? '#2563eb')
</script>

<template>
    <ChartContainer class="min-h-[220px] w-full">
        <VisXYContainer :data="chartData">
            <VisScatter :x="(d: P) => d.x" :y="(d: P) => d.y" :r="(d: P) => d.r ?? 4" :fill="color" />
        </VisXYContainer>
    </ChartContainer>
</template>
