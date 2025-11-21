<script setup lang="ts">
import { VisGroupedBar, VisXYContainer } from '@unovis/vue'
import { ChartContainer } from '@/components/ui/chart'
import { computed } from 'vue'

type Row = { category: string; a: number; b: number; c?: number }

const defaultData: Row[] = [
    { category: 'Q1', a: 30, b: 20, c: 15 },
    { category: 'Q2', a: 45, b: 30, c: 25 },
]

const props = defineProps<{
    data?: Row[]
    colors?: string[]
}>()

const chartData = computed(() => (props?.data && props?.data.length ? props?.data : defaultData))
const colors = computed(() => props?.colors ?? ['#2563eb', '#f97316', '#60a5fa'])
</script>

<template>
    <ChartContainer class="min-h-[220px] w-full">
        <VisXYContainer :data="chartData">
            <VisGroupedBar :x="(d: Row) => d.category" :y="[(d: Row) => d.a, (d: Row) => d.b, (d: Row) => d.c ?? 0]"
                :color="colors" />
        </VisXYContainer>
    </ChartContainer>
</template>
