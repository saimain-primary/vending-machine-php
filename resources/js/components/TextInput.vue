<script setup lang="ts">
defineProps<{
    id: string
    type?: string
    modelValue: string
    placeholder?: string
    autocomplete?: string
    required?: boolean
    step?: string
    min?: string
    error?: boolean
}>()

defineEmits<{
    'update:modelValue': [value: string]
}>()
</script>

<template>
    <input
        :id="id"
        :type="type ?? 'text'"
        :value="modelValue"
        :placeholder="placeholder"
        :autocomplete="autocomplete"
        :required="required"
        :step="step"
        :min="min"
        :class="[
            'w-full border bg-white px-4 py-2.5 text-sm text-[oklch(0.15_0.01_260)] placeholder:text-[oklch(0.65_0.005_260)] focus:outline-none focus:ring-2',
            error
                ? 'border-red-400 focus:border-red-500 focus:ring-red-500/10'
                : 'border-[oklch(0.87_0.006_70)] focus:border-[oklch(0.45_0.01_260)] focus:ring-[oklch(0.45_0.01_260)]/10',
        ]"
        @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
    />
</template>
