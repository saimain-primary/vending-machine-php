<script setup lang="ts">
import { Head, Link, router, usePage, useHttp } from '@inertiajs/vue3'
import { computed, onMounted, ref } from 'vue'
import { recommendations as recommendationsUrl } from '@/actions/App/Http/Controllers/Api/V1/ProductController'
import { show as showUrl, buy as buyUrl } from '@/actions/App/Http/Controllers/ProductController'
import AppDialog from '@/components/AppDialog.vue'
import AppLayout from '@/components/AppLayout.vue'
import StockBadge from '@/components/StockBadge.vue'
import { formatUSD } from '@/lib/utils'

interface Product {
    id: number
    name: string
    slug: string
    price_in_mills: number
    quantity_available: number
    stock_status: string
}

interface ApiResponse<T> {
    data?: T
}

const props = defineProps<{
    product: Product
}>()

const page = usePage()
const isAuthenticated = computed(() => !!page.props.auth?.user)
const isAdmin = computed(() => (page.props.auth?.user as any)?.role === 'admin')

const showConfirmDialog = ref(false)
const purchased = ref(false)
const buying = ref(false)
const showGuestDialog = ref(false)

function handleBuy() {
    if (!isAuthenticated.value) {
        showGuestDialog.value = true

        return
    }

    showConfirmDialog.value = true
}

function confirmBuy() {
    buying.value = true
    router.post(buyUrl.url(props.product.slug), {}, {
        onSuccess: () => {
            showConfirmDialog.value = false
            purchased.value = true
        },
        onFinish: () => {
            buying.value = false
        },
    })
}

// Recommendations
const http = useHttp()
const recommendations = ref<Product[]>([])

onMounted(() => {
    http.get(recommendationsUrl.url(props.product.slug), {
        onSuccess: (response: unknown) => {
            const payload = response as ApiResponse<Product[]>

            recommendations.value = payload.data ?? []
        },
    })
})
</script>

<template>
    <Head :title="product.name" />

    <AppLayout>
        <div class="flex flex-col gap-10">

            <Link
                href="/"
                class="inline-flex items-center gap-1.5 text-sm text-[oklch(0.55_0.006_260)] transition-colors hover:text-[oklch(0.22_0.01_260)]"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                All products
            </Link>

            <!-- Product detail -->
            <div class="flex flex-col gap-6 border border-[oklch(0.87_0.006_70)] bg-white p-8 sm:flex-row sm:items-start sm:gap-10">
                <div class="flex h-20 w-20 flex-shrink-0 items-center justify-center bg-[oklch(0.93_0.006_70)] text-2xl font-bold text-[oklch(0.35_0.01_260)]">
                    {{ product.name.charAt(0).toUpperCase() }}
                </div>

                <div class="flex flex-1 flex-col gap-6">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h1 class="text-2xl font-bold text-[oklch(0.15_0.01_260)]">{{ product.name }}</h1>
                            <p class="mt-1 font-mono text-3xl font-medium tracking-tight text-[oklch(0.15_0.01_260)] [font-variant-numeric:tabular-nums]">
                                {{ formatUSD(product.price_in_mills) }}
                            </p>
                        </div>
                        <StockBadge :quantity="product.quantity_available" />
                    </div>

                    <p class="font-mono text-sm text-[oklch(0.55_0.006_260)] [font-variant-numeric:tabular-nums]">
                        {{ product.quantity_available }} in stock
                    </p>

                    <div v-if="purchased" class="flex items-center gap-2 border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                        <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        Purchase successful! Check your order history.
                    </div>

                    <div v-else-if="!isAdmin">
                        <button
                            :disabled="product.quantity_available === 0"
                            class="cursor-pointer bg-[oklch(0.22_0.01_260)] px-8 py-3 text-sm font-semibold text-white transition-colors hover:bg-[oklch(0.32_0.01_260)] disabled:cursor-not-allowed disabled:opacity-40"
                            @click="handleBuy"
                        >
                            {{ product.quantity_available === 0 ? 'Out of stock' : 'Buy now' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Recommendations -->
            <div class="flex flex-col gap-4">
                <h2 class="text-base font-semibold text-[oklch(0.15_0.01_260)]">You may also like</h2>

                <!-- Skeleton while loading -->
                <div v-if="http.processing" class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div
                        v-for="n in 4"
                        :key="n"
                        class="animate-pulse border border-[oklch(0.87_0.006_70)] bg-white p-5"
                    >
                        <div class="mb-4 h-10 w-10 bg-[oklch(0.93_0.005_70)]" />
                        <div class="mb-2 h-3 w-3/4 rounded bg-[oklch(0.93_0.005_70)]" />
                        <div class="h-5 w-1/2 rounded bg-[oklch(0.93_0.005_70)]" />
                    </div>
                </div>

                <!-- Results -->
                <div v-else-if="recommendations.length > 0" class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <Link
                        v-for="rec in recommendations"
                        :key="rec.id"
                        :href="showUrl.url(rec.slug)"
                        :class="[
                            'flex flex-col border border-[oklch(0.87_0.006_70)] bg-white p-5 transition-colors hover:bg-[oklch(0.99_0.003_70)]',
                            rec.quantity_available === 0 && 'opacity-50',
                        ]"
                    >
                        <div class="mb-4 flex items-start justify-between gap-2">
                            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center bg-[oklch(0.93_0.006_70)] text-sm font-bold text-[oklch(0.35_0.01_260)]">
                                {{ rec.name.charAt(0).toUpperCase() }}
                            </div>
                            <StockBadge :quantity="rec.quantity_available" />
                        </div>
                        <p class="mb-2 text-sm font-semibold leading-snug text-[oklch(0.15_0.01_260)]">{{ rec.name }}</p>
                        <p class="mt-auto font-mono text-base font-medium text-[oklch(0.22_0.01_260)] [font-variant-numeric:tabular-nums]">
                            {{ formatUSD(rec.price_in_mills) }}
                        </p>
                    </Link>
                </div>
            </div>

        </div>
    </AppLayout>

    <AppDialog
        :open="showConfirmDialog"
        title="Confirm purchase"
        @close="showConfirmDialog = false"
    >
        Buy <strong class="font-semibold text-[oklch(0.15_0.01_260)]">{{ product.name }}</strong>
        for <strong class="font-mono font-medium text-[oklch(0.15_0.01_260)] [font-variant-numeric:tabular-nums]">{{ formatUSD(product.price_in_mills) }}</strong>?
        <template #actions>
            <button
                :disabled="buying"
                class="flex-1 cursor-pointer bg-[oklch(0.22_0.01_260)] py-2.5 text-sm font-semibold text-white transition-colors hover:bg-[oklch(0.32_0.01_260)] disabled:cursor-not-allowed disabled:opacity-50"
                @click="confirmBuy"
            >
                {{ buying ? 'Processing…' : 'Confirm' }}
            </button>
            <button
                class="flex-1 cursor-pointer border border-[oklch(0.87_0.006_70)] py-2.5 text-sm font-medium text-[oklch(0.48_0.008_260)] transition-colors hover:bg-[oklch(0.96_0.005_70)]"
                @click="showConfirmDialog = false"
            >
                Cancel
            </button>
        </template>
    </AppDialog>

    <AppDialog
        :open="showGuestDialog"
        title="Login required"
        @close="showGuestDialog = false"
    >
        You need to be logged in to make a purchase.
        <template #actions>
            <Link
                :href="`/login?redirect=${showUrl.url(product.slug)}`"
                class="flex-1 bg-[oklch(0.22_0.01_260)] py-2.5 text-center text-sm font-semibold text-white transition-colors hover:bg-[oklch(0.32_0.01_260)]"
            >
                Go to Login
            </Link>
            <button
                class="flex-1 cursor-pointer border border-[oklch(0.87_0.006_70)] py-2.5 text-sm font-medium text-[oklch(0.48_0.008_260)] transition-colors hover:bg-[oklch(0.96_0.005_70)]"
                @click="showGuestDialog = false"
            >
                Cancel
            </button>
        </template>
    </AppDialog>
</template>
