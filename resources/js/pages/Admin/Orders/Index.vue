<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import AppEmptyState from '@/components/AppEmptyState.vue'
import AppLayout from '@/components/AppLayout.vue'
import AppPagination from '@/components/AppPagination.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import { index as ordersIndex } from '@/actions/App/Http/Controllers/Admin/OrderController'
import { formatUSD } from '@/lib/utils'

interface Order {
    id: number
    customer_name: string
    customer_email: string
    product_name: string
    quantity: number
    unit_price_in_mills: number
    total_amount_in_mills: number
    status: string
    created_at: string
}

interface PaginatedOrders {
    data: Order[]
    current_page: number
    last_page: number
    from: number
    to: number
    total: number
    links: { url: string | null; label: string; active: boolean }[]
}

interface Filters {
    search?: string
}

const props = defineProps<{
    orders: PaginatedOrders
    filters: Filters
}>()

const search = ref(props.filters.search ?? '')

watch(search, (value) => {
    router.get(ordersIndex.url(), { search: value }, {
        preserveState: true,
        replace: true,
    })
})
</script>

<template>
    <Head title="All Orders" />

    <AppLayout>
        <div class="flex flex-col gap-8">

            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-[oklch(0.15_0.01_260)]">All Orders</h1>
                    <p class="mt-1 text-sm text-[oklch(0.55_0.006_260)]">
                        {{ orders.total }} {{ orders.total === 1 ? 'order' : 'orders' }} total
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search by customer or product…"
                    class="w-72 border border-[oklch(0.87_0.006_70)] bg-white px-4 py-2.5 text-sm text-[oklch(0.15_0.01_260)] placeholder:text-[oklch(0.65_0.005_260)] focus:border-[oklch(0.45_0.01_260)] focus:outline-none focus:ring-2 focus:ring-[oklch(0.45_0.01_260)]/10"
                />
            </div>

            <AppEmptyState
                v-if="orders.data.length === 0"
                title="No orders found"
                :description="search ? 'Try a different search term' : 'No orders have been placed yet.'"
            >
                <template #icon>
                    <svg class="h-5 w-5 text-[oklch(0.55_0.006_260)]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                    </svg>
                </template>
            </AppEmptyState>

            <div v-else class="border border-[oklch(0.87_0.006_70)] bg-white">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-[oklch(0.93_0.005_70)] bg-[oklch(0.975_0.004_70)]">
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-[oklch(0.55_0.006_260)]">#</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-[oklch(0.55_0.006_260)]">Customer</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-[oklch(0.55_0.006_260)]">Product</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-[oklch(0.55_0.006_260)]">Qty</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-[oklch(0.55_0.006_260)]">Unit Price</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-[oklch(0.55_0.006_260)]">Total</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-[oklch(0.55_0.006_260)]">Status</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-[oklch(0.55_0.006_260)]">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="order in orders.data"
                            :key="order.id"
                            class="border-b border-[oklch(0.93_0.005_70)] last:border-0 hover:bg-[oklch(0.99_0.003_70)]"
                        >
                            <td class="px-5 py-3.5 font-mono text-xs text-[oklch(0.58_0.006_260)]">
                                {{ order.id }}
                            </td>
                            <td class="px-5 py-3.5">
                                <p class="font-semibold text-[oklch(0.15_0.01_260)]">{{ order.customer_name }}</p>
                                <p class="mt-0.5 text-xs text-[oklch(0.55_0.006_260)]">{{ order.customer_email }}</p>
                            </td>
                            <td class="px-5 py-3.5 font-semibold text-[oklch(0.15_0.01_260)]">
                                {{ order.product_name }}
                            </td>
                            <td class="px-5 py-3.5 font-mono text-[oklch(0.22_0.01_260)] [font-variant-numeric:tabular-nums]">
                                {{ order.quantity }}
                            </td>
                            <td class="px-5 py-3.5 font-mono text-[oklch(0.22_0.01_260)] [font-variant-numeric:tabular-nums]">
                                {{ formatUSD(order.unit_price_in_mills) }}
                            </td>
                            <td class="px-5 py-3.5 font-mono font-semibold text-[oklch(0.15_0.01_260)] [font-variant-numeric:tabular-nums]">
                                {{ formatUSD(order.total_amount_in_mills) }}
                            </td>
                            <td class="px-5 py-3.5">
                                <StatusBadge :status="order.status" />
                            </td>
                            <td class="px-5 py-3.5 text-xs text-[oklch(0.55_0.006_260)]">
                                {{ order.created_at }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <AppPagination
                :from="orders.from"
                :to="orders.to"
                :total="orders.total"
                :last-page="orders.last_page"
                :links="orders.links"
            />

        </div>
    </AppLayout>
</template>
