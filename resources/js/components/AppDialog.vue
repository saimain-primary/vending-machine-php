<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue';

const props = defineProps<{
    open: boolean;
    title: string;
}>();

const emit = defineEmits<{
    close: [];
}>();

const mounted = ref(false);

function onKeydown(e: KeyboardEvent) {
    if (e.key === 'Escape' && props.open) {
        emit('close');
    }
}

onMounted(() => {
    mounted.value = true;
    document.addEventListener('keydown', onKeydown);
});
onUnmounted(() => document.removeEventListener('keydown', onKeydown));
</script>

<template>
    <Teleport v-if="mounted" to="body" defer>
        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="open"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
            >
                <div
                    class="absolute inset-0 bg-[oklch(0.1_0.01_260)]/40"
                    @click="emit('close')"
                />
                <Transition
                    enter-active-class="transition duration-150 ease-out"
                    enter-from-class="opacity-0 scale-95"
                    enter-to-class="opacity-100 scale-100"
                    leave-active-class="transition duration-100 ease-in"
                    leave-from-class="opacity-100 scale-100"
                    leave-to-class="opacity-0 scale-95"
                >
                    <div
                        v-if="open"
                        class="relative w-full max-w-sm bg-white p-7 shadow-2xl"
                    >
                        <h2
                            class="text-base font-semibold text-[oklch(0.15_0.01_260)]"
                        >
                            {{ title }}
                        </h2>
                        <div
                            class="mt-2 text-sm leading-relaxed text-[oklch(0.45_0.008_260)]"
                        >
                            <slot />
                        </div>
                        <div class="mt-6 flex gap-3">
                            <slot name="actions" />
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
