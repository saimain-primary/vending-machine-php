<script setup lang="ts">
defineProps<{
    for: string
    value: string
}>()
</script>

<template>
    <label :for="for" class="text-sm font-medium text-[oklch(0.22_0.01_260)]">
        {{ value }}
    </label>
</template>
