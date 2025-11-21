<script setup lang="ts">
import { VisTimeline } from '@unovis/vue'
import { ChartContainer } from '@/components/ui/chart'
import { computed, withDefaults, defineProps } from 'vue'

type EventRow = { id: string; start: Date | string; end?: Date | string; group?: string; label?: string }

// 1. Re-declare defaultData locally for use in the computed property if needed,
//    but keep the prop default value inline in withDefaults.
const LOCAL_DEFAULT_DATA: EventRow[] = [
    { id: '1', start: new Date('2024-01-01'), end: new Date('2024-01-10'), label: 'Planning' },
    { id: '2', start: new Date('2024-01-11'), end: new Date('2024-02-05'), label: 'Development' },
]

// 2. Define props and use the raw array value in withDefaults
const props = withDefaults(defineProps<{
    data?: EventRow[]
}>(), {
    // The value here must be directly resolvable (a literal or a function returning a literal)
    data: () => [
        { id: '1', start: new Date('2024-01-01'), end: new Date('2024-01-10'), label: 'Planning' },
        { id: '2', start: new Date('2024-01-11'), end: new Date('2024-02-05'), label: 'Development' },
    ] as EventRow[],
})

// 3. The chartData computed property safely reads from props.data,
//    which is guaranteed to be an array thanks to withDefaults.
const chartData = computed(() =>
    // props.data is guaranteed to exist and be an array due to withDefaults
    (props?.data).map(d => ({
        ...d,
        // Ensure that date strings are converted to Date objects
        start: d.start instanceof Date ? d.start : new Date(d.start),
        end: d.end ? (d.end instanceof Date ? d.end : new Date(d.end)) : undefined,
    })) as EventRow[]
)

</script>

<template>
    <ChartContainer class="min-h-[260px] w-full">
        <VisTimeline :data="chartData" :x="(d: EventRow) => d.start" :xEnd="(d: EventRow) => d.end" labelKey="label" />
    </ChartContainer>
</template>
