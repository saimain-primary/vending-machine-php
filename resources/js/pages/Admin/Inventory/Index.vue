<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { index as inventoryIndex } from '@/actions/App/Http/Controllers/Admin/InventoryController';
import AppEmptyState from '@/components/AppEmptyState.vue';
import AppLayout from '@/components/AppLayout.vue';
import AppPagination from '@/components/AppPagination.vue';

interface ProductOption {
    id: number;
    name: string;
}

interface StockMovement {
    id: number;
    product_name: string;
    user_name: string | null;
    type: string;
    type_label: string;
    quantity_change: number;
    quantity_after: number;
    note: string | null;
    created_at: string;
}

interface PaginatedStockMovements {
    data: StockMovement[];
    current_page: number;
    last_page: number;
    from: number;
    to: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

interface Filters {
    product_id: number | null;
}

const props = defineProps<{
    stockMovements: PaginatedStockMovements;
    products: ProductOption[];
    filters: Filters;
}>();

const selectedProductId = ref(
    props.filters.product_id ? String(props.filters.product_id) : '',
);

watch(selectedProductId, (productId) => {
    router.get(
        inventoryIndex.url(),
        { product_id: productId || undefined },
        {
            preserveState: true,
            replace: true,
        },
    );
});

function formatDateTime(iso: string): string {
    return new Intl.DateTimeFormat('en-US', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(iso));
}

function formatSignedQuantity(quantity: number): string {
    return quantity > 0 ? `+${quantity}` : String(quantity);
}

function movementTone(quantity: number): string {
    if (quantity > 0) {
        return 'text-emerald-700';
    }

    if (quantity < 0) {
        return 'text-red-600';
    }

    return 'text-[oklch(0.42_0.008_260)]';
}
</script>

<template>
    <Head title="Inventory Activity" />

    <AppLayout>
        <div class="flex flex-col gap-8">
            <div>
                <h1 class="text-2xl font-bold text-[oklch(0.15_0.01_260)]">
                    Inventory Activity
                </h1>
                <p class="mt-1 text-sm text-[oklch(0.55_0.006_260)]">
                    {{ stockMovements.total }}
                    {{ stockMovements.total === 1 ? 'movement' : 'movements' }}
                    total
                </p>
            </div>

            <div class="flex items-center gap-3">
                <select
                    v-model="selectedProductId"
                    class="w-72 border border-[oklch(0.87_0.006_70)] bg-white px-4 py-2.5 text-sm text-[oklch(0.15_0.01_260)] focus:border-[oklch(0.45_0.01_260)] focus:ring-2 focus:ring-[oklch(0.45_0.01_260)]/10 focus:outline-none"
                >
                    <option value="">All products</option>
                    <option
                        v-for="product in products"
                        :key="product.id"
                        :value="String(product.id)"
                    >
                        {{ product.name }}
                    </option>
                </select>
            </div>

            <AppEmptyState
                v-if="stockMovements.data.length === 0"
                title="No inventory activity"
                description="Stock changes will appear here after products are created, adjusted, or purchased."
            >
                <template #icon>
                    <svg
                        class="h-5 w-5 text-[oklch(0.55_0.006_260)]"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M3 7.5 12 3l9 4.5m-18 0L12 12m-9-4.5v9L12 21m0-9 9-4.5M12 12v9m9-13.5v9L12 21"
                        />
                    </svg>
                </template>
            </AppEmptyState>

            <div
                v-else
                class="overflow-x-auto border border-[oklch(0.87_0.006_70)] bg-white"
            >
                <table class="w-full text-sm">
                    <thead>
                        <tr
                            class="border-b border-[oklch(0.93_0.005_70)] bg-[oklch(0.975_0.004_70)]"
                        >
                            <th
                                class="px-5 py-3 text-left text-xs font-semibold tracking-[0.08em] text-[oklch(0.55_0.006_260)] uppercase"
                            >
                                Product
                            </th>
                            <th
                                class="px-5 py-3 text-left text-xs font-semibold tracking-[0.08em] text-[oklch(0.55_0.006_260)] uppercase"
                            >
                                Type
                            </th>
                            <th
                                class="px-5 py-3 text-left text-xs font-semibold tracking-[0.08em] text-[oklch(0.55_0.006_260)] uppercase"
                            >
                                Change
                            </th>
                            <th
                                class="px-5 py-3 text-left text-xs font-semibold tracking-[0.08em] text-[oklch(0.55_0.006_260)] uppercase"
                            >
                                After
                            </th>
                            <th
                                class="px-5 py-3 text-left text-xs font-semibold tracking-[0.08em] text-[oklch(0.55_0.006_260)] uppercase"
                            >
                                By
                            </th>
                            <th
                                class="px-5 py-3 text-left text-xs font-semibold tracking-[0.08em] text-[oklch(0.55_0.006_260)] uppercase"
                            >
                                When
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="movement in stockMovements.data"
                            :key="movement.id"
                            class="border-b border-[oklch(0.93_0.005_70)] last:border-0"
                        >
                            <td
                                class="px-5 py-3.5 font-semibold text-[oklch(0.15_0.01_260)]"
                            >
                                {{ movement.product_name }}
                            </td>
                            <td
                                class="px-5 py-3.5 text-[oklch(0.42_0.008_260)]"
                            >
                                {{ movement.type_label }}
                            </td>
                            <td
                                class="px-5 py-3.5 font-mono font-semibold [font-variant-numeric:tabular-nums]"
                                :class="movementTone(movement.quantity_change)"
                            >
                                {{
                                    formatSignedQuantity(
                                        movement.quantity_change,
                                    )
                                }}
                            </td>
                            <td
                                class="px-5 py-3.5 font-mono text-[oklch(0.22_0.01_260)] [font-variant-numeric:tabular-nums]"
                            >
                                {{ movement.quantity_after }}
                            </td>
                            <td
                                class="px-5 py-3.5 text-[oklch(0.42_0.008_260)]"
                            >
                                {{ movement.user_name ?? 'System' }}
                            </td>
                            <td
                                class="px-5 py-3.5 font-mono text-xs text-[oklch(0.58_0.006_260)]"
                            >
                                {{ formatDateTime(movement.created_at) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <AppPagination
                :from="stockMovements.from"
                :to="stockMovements.to"
                :total="stockMovements.total"
                :last-page="stockMovements.last_page"
                :links="stockMovements.links"
            />
        </div>
    </AppLayout>
</template>
