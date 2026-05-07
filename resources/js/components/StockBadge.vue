<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{ quantity: number }>()

const badge = computed(() => {
    if (props.quantity === 0) {
        return { label: 'Out of Stock', classes: 'bg-red-100 text-red-700' }
    }
    if (props.quantity <= 5) {
        return { label: 'Low Stock', classes: 'bg-yellow-100 text-yellow-700' }
    }
    return { label: 'In Stock', classes: 'bg-green-100 text-green-700' }
})
</script>

<template>
    <span :class="['inline-flex items-center px-2 py-0.5 text-xs font-semibold uppercase tracking-[0.08em]', badge.classes]">
        {{ badge.label }}
    </span>
</template>
