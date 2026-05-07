<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{ status: string }>()

const badge = computed(() => {
    switch (props.status.toLowerCase()) {
        case 'completed':
        case 'complete':
            return { label: 'Completed', classes: 'bg-green-100 text-green-700' }
        case 'pending':
            return { label: 'Pending', classes: 'bg-yellow-100 text-yellow-700' }
        case 'processing':
            return { label: 'Processing', classes: 'bg-blue-100 text-blue-700' }
        case 'failed':
            return { label: 'Failed', classes: 'bg-red-100 text-red-700' }
        case 'cancelled':
        case 'canceled':
            return { label: 'Cancelled', classes: 'bg-red-100 text-red-700' }
        case 'refunded':
            return { label: 'Refunded', classes: 'bg-purple-100 text-purple-700' }
        default:
            return { label: props.status, classes: 'bg-gray-100 text-gray-700' }
    }
})
</script>

<template>
    <span :class="['inline-flex items-center px-2 py-0.5 text-xs font-semibold uppercase tracking-[0.08em]', badge.classes]">
        {{ badge.label }}
    </span>
</template>
