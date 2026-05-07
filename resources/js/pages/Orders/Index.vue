<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppEmptyState from '@/components/AppEmptyState.vue'
import AppLayout from '@/components/AppLayout.vue'
import AppPagination from '@/components/AppPagination.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import { formatUSD } from '@/lib/utils'

interface Order {
    id: number
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

defineProps<{
    orders: PaginatedOrders
}>()
</script>

<template>
    <Head title="Order History" />

    <AppLayout>
        <div class="flex flex-col gap-8">

            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-[oklch(0.15_0.01_260)]">Order History</h1>
                    <p class="mt-1 text-base text-[oklch(0.55_0.006_260)]">
                        {{ orders.total }} {{ orders.total === 1 ? 'order' : 'orders' }} total
                    </p>
                </div>
                <Link
                    href="/"
                    class="border border-[oklch(0.87_0.006_70)] bg-white px-4 py-2 text-sm font-medium text-[oklch(0.22_0.01_260)] transition-colors hover:bg-[oklch(0.965_0.005_70)]"
                >
                    Browse Products
                </Link>
            </div>

            <AppEmptyState
                v-if="orders.data.length === 0"
                title="No orders yet"
                description="Your purchases will appear here."
            >
                <template #icon>
                    <svg class="h-5 w-5 text-[oklch(0.55_0.006_260)]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                    </svg>
                </template>
                <template #action>
                    <Link
                        href="/"
                        class="mt-6 bg-[oklch(0.22_0.01_260)] px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-[oklch(0.32_0.01_260)]"
                    >
                        Browse Products
                    </Link>
                </template>
            </AppEmptyState>

            <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="order in orders.data"
                    :key="order.id"
                    class="flex flex-col border border-[oklch(0.87_0.006_70)] bg-white"
                >
                    <div class="flex items-center justify-between px-5 pt-4 pb-3">
                        <span class="text-xs font-semibold uppercase tracking-[0.08em] text-[oklch(0.62_0.006_260)]">
                            Order #{{ order.id }}
                        </span>
                        <StatusBadge :status="order.status" />
                    </div>

                    <div class="flex-1 px-5 pb-5">
                        <p class="text-base font-semibold leading-snug text-[oklch(0.15_0.01_260)]">
                            {{ order.product_name }}
                        </p>
                        <p class="mt-1.5 font-mono text-sm text-[oklch(0.58_0.006_260)] [font-variant-numeric:tabular-nums]">
                            {{ order.quantity }} &times; {{ formatUSD(order.unit_price_in_mills) }}
                        </p>
                    </div>

                    <div class="flex items-center justify-between border-t border-[oklch(0.93_0.005_70)] px-5 py-3.5">
                        <p class="text-xs text-[oklch(0.62_0.006_260)]">
                            {{ order.created_at }}
                        </p>
                        <p class="font-mono text-[1.125rem] font-semibold leading-none text-[oklch(0.15_0.01_260)] [font-variant-numeric:tabular-nums]">
                            {{ formatUSD(order.total_amount_in_mills) }}
                        </p>
                    </div>
                </div>
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
