<script setup lang="ts">
import { Link } from '@inertiajs/vue3'

interface PaginationLink {
    url: string | null
    label: string
    active: boolean
}

defineProps<{
    from: number
    to: number
    total: number
    lastPage: number
    links: PaginationLink[]
}>()
</script>

<template>
    <div
        v-if="lastPage > 1"
        class="flex flex-wrap items-center justify-between gap-3"
    >
        <p class="text-sm text-[oklch(0.55_0.006_260)]">
            Showing {{ from }}&ndash;{{ to }} of {{ total }}
        </p>
        <div class="flex">
            <Link
                v-for="link in links"
                :key="link.label"
                :href="link.url ?? '#'"
                :class="[
                    'inline-flex h-9 min-w-[2.25rem] cursor-pointer items-center justify-center border-r border-[oklch(0.87_0.006_70)] px-3 text-sm font-medium transition-colors first:border-l',
                    link.active
                        ? 'bg-[oklch(0.22_0.01_260)] text-white'
                        : 'bg-white text-[oklch(0.48_0.008_260)] hover:bg-[oklch(0.96_0.005_70)]',
                    !link.url && 'pointer-events-none opacity-35',
                ]"
            ><span v-html="link.label" /></Link>
        </div>
    </div>
</template>
