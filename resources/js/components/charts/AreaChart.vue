<script setup lang="ts">
import type { ChartConfig } from '@/components/ui/chart'
import { VisArea, VisXYContainer } from '@unovis/vue'
import { ChartContainer } from '@/components/ui/chart'
import { computed } from 'vue'

type DefaultPoint = {
    date: Date
    desktop: number
    mobile: number
}

const defaultChartData: DefaultPoint[] = [
    { date: new Date('2024-01-01'), desktop: 186, mobile: 80 },
    { date: new Date('2024-02-01'), desktop: 305, mobile: 200 },
    { date: new Date('2024-03-01'), desktop: 237, mobile: 120 },
    { date: new Date('2024-04-01'), desktop: 73, mobile: 190 },
    { date: new Date('2024-05-01'), desktop: 209, mobile: 130 },
    { date: new Date('2024-06-01'), desktop: 214, mobile: 140 },
]

const defaultChartConfig = {
    desktop: { label: 'Desktop', color: '#2563eb' },
    mobile: { label: 'Mobile', color: '#60a5fa' },
} as ChartConfig

const props = defineProps<{
    data?: DefaultPoint[]
    config?: ChartConfig
}>()

const chartData = computed<DefaultPoint[]>(() => {
    return (props.data && props.data.length) ? props.data : defaultChartData
})

const chartConfig = computed<ChartConfig>(() => {
    return (props.config && Object.keys(props.config).length) ? props.config : defaultChartConfig
})

type Data = DefaultPoint
</script>

<template>
    <ChartContainer :config="chartConfig" class="min-h-[220px] w-full">
        <VisXYContainer :data="chartData">
            <!-- Desktop area (behind) -->
            <VisArea :x="(d: Data) => d.date" :y="(d: Data) => d.desktop" :color="chartConfig.desktop.color"
                :stroke="chartConfig.desktop.color" curve="monotoneX" :areaOpacity="0.6" />
            <!-- Mobile area (above) -->
            <VisArea :x="(d: Data) => d.date" :y="(d: Data) => d.mobile" :color="chartConfig.mobile.color"
                :stroke="chartConfig.mobile.color" curve="monotoneX" :areaOpacity="0.45" />
        </VisXYContainer>
    </ChartContainer>
</template>

<style scoped>
/* responsive container handled by parent */
</style>
