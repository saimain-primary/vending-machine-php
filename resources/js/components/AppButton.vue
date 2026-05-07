<script setup lang="ts">
withDefaults(defineProps<{
    variant?: 'primary' | 'danger' | 'secondary'
    type?: 'button' | 'submit'
    disabled?: boolean
    size?: 'sm' | 'md'
}>(), {
    variant: 'primary',
    type: 'button',
    disabled: false,
    size: 'md',
})
</script>

<template>
    <button
        :type="type"
        :disabled="disabled"
        :class="[
            'inline-flex items-center justify-center rounded-md font-medium transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed',
            size === 'sm' ? 'px-3 py-1.5 text-xs' : 'px-4 py-2 text-sm',
            variant === 'primary' && 'bg-blue-600 text-white hover:bg-blue-700',
            variant === 'danger'  && 'bg-red-600 text-white hover:bg-red-700',
            variant === 'secondary' && 'bg-gray-100 text-gray-700 hover:bg-gray-200',
        ]"
    >
        <slot />
    </button>
</template>
