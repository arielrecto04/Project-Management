<script setup lang="ts">
import type { ChartConfig } from '@/components/ui/chart'
import { VisLine, VisXYContainer } from '@unovis/vue'
import { ChartContainer } from '@/components/ui/chart'
import { computed } from 'vue'

type Point = { date: Date; value: number }

const defaultData: Point[] = [
    { date: new Date('2024-01-01'), value: 120 },
    { date: new Date('2024-02-01'), value: 200 },
    { date: new Date('2024-03-01'), value: 150 },
    { date: new Date('2024-04-01'), value: 220 },
]

const defaultConfig = { color: '#2563eb' } as ChartConfig

const props = defineProps<{
    data?: Point[]
    config?: ChartConfig
}>()

const chartData = computed(() => (props?.data && props?.data.length ? props?.data : defaultData))
const chartConfig = computed(() => (props?.config && Object.keys(props?.config).length ? props?.config : defaultConfig))
</script>

<template>
    <ChartContainer :config="chartConfig" class="min-h-[220px] w-full">
        <VisXYContainer :data="chartData">
            <VisLine :x="(d: Point) => d.date" :y="(d: Point) => d.value" :stroke="chartConfig.color"
                curve="monotoneX" />
        </VisXYContainer>
    </ChartContainer>
</template>
