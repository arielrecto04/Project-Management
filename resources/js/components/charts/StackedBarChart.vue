<script setup lang="ts">
import { VisStackedBar, VisXYContainer } from '@unovis/vue'
import { ChartContainer } from '@/components/ui/chart'
import { computed, withDefaults, defineProps } from 'vue'

type Row = { category: string; seriesA: number; seriesB: number }

// We define this as a constant, but we MUST NOT reference it in withDefaults
const LOCAL_DEFAULT_DATA: Row[] = [
    { category: 'Jan', seriesA: 30, seriesB: 20 },
    { category: 'Feb', seriesA: 45, seriesB: 25 },
    { category: 'Mar', seriesA: 50, seriesB: 30 },
]

const defaultConfig = { colors: ['#2563eb', '#60a5fa'] }

// 1. Use withDefaults and inline the default data array content
const props = withDefaults(defineProps<{
    data?: Row[]
    config?: { colors?: string[] }
}>(), {
    // FIX: Inline the array literal here instead of referencing defaultData
    data: () => [
        { category: 'Jan', seriesA: 30, seriesB: 20 },
        { category: 'Feb', seriesA: 45, seriesB: 25 },
        { category: 'Mar', seriesA: 50, seriesB: 30 },
    ] as Row[], // Use 'as Row[]' for type safety

    config: () => ({}),
})

// 2. chartData now relies only on the guaranteed prop value
const chartData = computed(() => props.data)

const colors = computed(() => props.config.colors ?? defaultConfig.colors)
</script>

<template>
    <ChartContainer :config="{ colors }" class="min-h-[220px] w-full">
        <VisXYContainer :data="chartData">
            <VisStackedBar :x="(d: Row) => d.category" :y="[(d: Row) => d.seriesA, (d: Row) => d.seriesB]"
                :color="colors" />
        </VisXYContainer>
    </ChartContainer>
</template>
