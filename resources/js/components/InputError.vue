<script setup lang="ts">
defineProps<{
    message?: string
}>()
</script>

<template>
    <p v-if="message" class="text-xs text-red-600">{{ message }}</p>
</template>
