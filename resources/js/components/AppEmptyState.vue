<script setup lang="ts">
defineProps<{
    title: string
    description: string
}>()
</script>

<template>
    <div class="flex flex-col items-center justify-center border border-dashed border-[oklch(0.85_0.006_70)] bg-white py-24 text-center">
        <div class="mb-4 flex h-12 w-12 items-center justify-center bg-[oklch(0.93_0.006_70)]">
            <slot name="icon" />
        </div>
        <p class="text-base font-semibold text-[oklch(0.22_0.01_260)]">{{ title }}</p>
        <p class="mt-1 text-sm text-[oklch(0.55_0.006_260)]">{{ description }}</p>
        <slot name="action" />
    </div>
</template>
