<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import AppEmptyState from '@/components/AppEmptyState.vue'
import AppLayout from '@/components/AppLayout.vue'
import AppPagination from '@/components/AppPagination.vue'
import StockBadge from '@/components/StockBadge.vue'
import { show as productShow } from '@/actions/App/Http/Controllers/ProductController'
import { formatUSD } from '@/lib/utils'

interface Product {
    id: number
    name: string
    slug: string
    price_in_mills: number
    quantity_available: number
    stock_status: string
}

interface PaginatedProducts {
    data: Product[]
    current_page: number
    last_page: number
    from: number
    to: number
    total: number
    links: { url: string | null; label: string; active: boolean }[]
}

interface Filters {
    search?: string
    sort?: string
    direction?: string
}

const props = defineProps<{
    products: PaginatedProducts
    filters: Filters
    isAdmin: boolean
}>()

const page = usePage()
const isAuthenticated = computed(() => !!page.props.auth?.user)

const search = ref(props.filters.search ?? '')
const currentSort = ref(props.filters.sort ?? 'name')
const currentDirection = ref(props.filters.direction ?? 'asc')

watch(search, (value) => {
    router.get('/', { search: value, sort: currentSort.value, direction: currentDirection.value }, {
        preserveState: true,
        replace: true,
    })
})

function sortBy(column: string) {
    const direction = currentSort.value === column && currentDirection.value === 'asc' ? 'desc' : 'asc'
    currentSort.value = column
    currentDirection.value = direction
    router.get('/', { search: search.value, sort: column, direction }, { preserveState: true })
}

function productInitial(name: string): string {
    return name.charAt(0).toUpperCase()
}
</script>

<template>
    <Head title="Products" />

    <AppLayout>
        <div class="flex flex-col gap-8">

            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-2">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search products…"
                        class="w-60 border border-[oklch(0.87_0.006_70)] bg-white px-4 py-2.5 text-sm text-[oklch(0.15_0.01_260)] placeholder:text-[oklch(0.65_0.005_260)] focus:border-[oklch(0.45_0.01_260)] focus:outline-none focus:ring-2 focus:ring-[oklch(0.45_0.01_260)]/10"
                    />
                    <div class="flex items-center border border-[oklch(0.87_0.006_70)] bg-white">
                        <button
                            v-for="option in [
                                { label: 'Name', value: 'name' },
                                { label: 'Price', value: 'price_in_mills' },
                                { label: 'Stock', value: 'quantity_available' },
                            ]"
                            :key="option.value"
                            :class="[
                                'flex cursor-pointer items-center gap-1.5 px-4 py-2.5 text-sm font-medium transition-colors',
                                currentSort === option.value
                                    ? 'bg-[oklch(0.22_0.01_260)] text-white'
                                    : 'text-[oklch(0.50_0.008_260)] hover:bg-[oklch(0.96_0.005_70)] hover:text-[oklch(0.22_0.01_260)]',
                            ]"
                            @click="sortBy(option.value)"
                        >
                            {{ option.label }}
                            <span v-if="currentSort === option.value" class="opacity-60">
                                {{ currentDirection === 'asc' ? '↑' : '↓' }}
                            </span>
                        </button>
                    </div>
                </div>

                <button
                    v-if="isAdmin"
                    class="cursor-pointer bg-[oklch(0.22_0.01_260)] px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-[oklch(0.32_0.01_260)]"
                >
                    + Add Product
                </button>
            </div>

            <AppEmptyState
                v-if="products.data.length === 0"
                title="No products found"
                :description="search ? 'Try a different search term' : 'No products are available right now'"
            >
                <template #icon>
                    <svg class="h-5 w-5 text-[oklch(0.55_0.006_260)]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </template>
            </AppEmptyState>

            <div
                v-else
                class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
            >
                <div
                    v-for="product in products.data"
                    :key="product.id"
                    :class="[
                        'flex flex-col border border-[oklch(0.87_0.006_70)] bg-white transition-colors',
                        product.quantity_available === 0 ? 'opacity-50' : '',
                    ]"
                >
                    <Link
                        :href="productShow.url(product.slug)"
                        class="flex flex-1 flex-col p-6 hover:bg-[oklch(0.99_0.003_70)]"
                    >
                        <div class="mb-5 flex items-start justify-between gap-2">
                            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center bg-[oklch(0.93_0.006_70)] text-sm font-bold text-[oklch(0.35_0.01_260)]">
                                {{ productInitial(product.name) }}
                            </div>
                            <StockBadge :quantity="product.quantity_available" />
                        </div>

                        <h3 class="mb-3 text-base font-semibold leading-snug text-[oklch(0.15_0.01_260)]">
                            {{ product.name }}
                        </h3>

                        <p class="font-mono text-3xl font-medium tracking-tight text-[oklch(0.15_0.01_260)] [font-variant-numeric:tabular-nums]">
                            {{ formatUSD(product.price_in_mills) }}
                        </p>
                        <p class="mt-1.5 font-mono text-sm text-[oklch(0.55_0.006_260)] [font-variant-numeric:tabular-nums]">
                            {{ product.quantity_available }} available
                        </p>
                    </Link>

                    <div v-if="isAdmin" class="border-t border-[oklch(0.93_0.005_70)] px-6 py-4">
                        <div class="flex gap-2">
                            <button class="flex-1 cursor-pointer border border-[oklch(0.87_0.006_70)] py-2.5 text-sm font-medium text-[oklch(0.35_0.01_260)] transition-colors hover:bg-[oklch(0.96_0.005_70)]">
                                Edit
                            </button>
                            <button class="flex-1 cursor-pointer border border-red-200 py-2.5 text-sm font-medium text-red-600 transition-colors hover:bg-red-50">
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <AppPagination
                :from="products.from"
                :to="products.to"
                :total="products.total"
                :last-page="products.last_page"
                :links="products.links"
            />

        </div>
    </AppLayout>
</template>
