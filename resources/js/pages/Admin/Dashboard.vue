<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref, watch, toRef } from 'vue';
import { index as dashboardIndex } from '@/actions/App/Http/Controllers/Admin/DashboardController';
import { index as inventoryIndex } from '@/actions/App/Http/Controllers/Admin/InventoryController';
import {
    store,
    update,
    destroy,
} from '@/actions/App/Http/Controllers/Admin/ProductController';
import AppDialog from '@/components/AppDialog.vue';
import AppEmptyState from '@/components/AppEmptyState.vue';
import AppLayout from '@/components/AppLayout.vue';
import AppPagination from '@/components/AppPagination.vue';
import InputError from '@/components/InputError.vue';
import InputLabel from '@/components/InputLabel.vue';
import StockBadge from '@/components/StockBadge.vue';
import TextInput from '@/components/TextInput.vue';
import { useClientValidation } from '@/composables/useClientValidation';
import { formatUSD } from '@/lib/utils';

const productRules = {
    name: [
        (v: string) => (!v.trim() ? 'Product name is required.' : null),
        (v: string) =>
            v.trim().length < 2
                ? 'Product name must be at least 2 characters.'
                : null,
        (v: string) =>
            v.trim().length > 255
                ? 'Product name must not exceed 255 characters.'
                : null,
    ],
    price: [
        (v: string) => (!v.trim() ? 'Price is required.' : null),
        (v: string) =>
            isNaN(Number(v)) || Number(v) < 0.01
                ? 'Price must be at least $0.01.'
                : null,
    ],
    quantity_available: [
        (v: string) => (v.trim() === '' ? 'Quantity is required.' : null),
        (v: string) =>
            !Number.isInteger(Number(v)) || Number(v) < 0
                ? 'Quantity must be a non-negative whole number.'
                : null,
    ],
};

interface Product {
    id: number;
    name: string;
    price_in_mills: number;
    quantity_available: number;
    stock_status: string;
    updated_at: string;
}

interface InventorySummary {
    total_units: number;
    low_stock_count: number;
    out_of_stock_count: number;
}

function formatDate(iso: string): string {
    return new Intl.DateTimeFormat('en-US', { dateStyle: 'medium' }).format(
        new Date(iso),
    );
}

interface PaginatedProducts {
    data: Product[];
    current_page: number;
    last_page: number;
    from: number;
    to: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

interface Filters {
    search?: string;
    sort?: string;
    direction?: string;
}

const props = defineProps<{
    products: PaginatedProducts;
    inventorySummary: InventorySummary;
    filters: Filters;
}>();

const search = ref(props.filters.search ?? '');
const currentSort = ref(props.filters.sort ?? '');
const currentDirection = ref(props.filters.direction ?? 'asc');

watch(toRef(props, 'filters'), (filters) => {
    search.value = filters.search ?? '';
    currentSort.value = filters.sort ?? '';
    currentDirection.value = filters.direction ?? 'asc';
});

watch(search, (value) => {
    router.get(
        dashboardIndex.url(),
        {
            search: value,
            sort: currentSort.value || undefined,
            direction: currentSort.value ? currentDirection.value : undefined,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
});

function sortBy(column: string) {
    const direction =
        currentSort.value === column && currentDirection.value === 'asc'
            ? 'desc'
            : 'asc';
    currentSort.value = column;
    currentDirection.value = direction;
    router.get(
        dashboardIndex.url(),
        {
            search: search.value || undefined,
            sort: column,
            direction,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
}

const showCreateDialog = ref(false);
const showEditDialog = ref(false);
const showDeleteDialog = ref(false);
const editingProduct = ref<Product | null>(null);
const deletingProduct = ref<Product | null>(null);
const deleting = ref(false);

const createForm = useForm({
    name: '',
    price: '',
    quantity_available: '',
});

const editForm = useForm({
    name: '',
    price: '',
    quantity_available: '',
});

const createValidation = useClientValidation(productRules);
const editValidation = useClientValidation(productRules);

const createDisplayErrors = computed(() => ({
    ...createValidation.errors.value,
    ...createForm.errors,
}));

const editDisplayErrors = computed(() => ({
    ...editValidation.errors.value,
    ...editForm.errors,
}));

function openCreate() {
    createForm.reset();
    createValidation.reset();
    showCreateDialog.value = true;
}

function submitCreate() {
    if (
        !createValidation.validate({
            name: createForm.name,
            price: createForm.price,
            quantity_available: createForm.quantity_available,
        })
    ) {
        return;
    }

    createForm.post(store.url(), {
        onSuccess: () => {
            showCreateDialog.value = false;
            createForm.reset();
            createValidation.reset();
        },
    });
}

function openEdit(product: Product) {
    editingProduct.value = product;
    editForm.name = product.name;
    editForm.price = (product.price_in_mills / 1000).toFixed(2);
    editForm.quantity_available = String(product.quantity_available);
    editValidation.reset();
    showEditDialog.value = true;
}

function submitEdit() {
    if (!editingProduct.value) {
        return;
    }

    if (
        !editValidation.validate({
            name: editForm.name,
            price: editForm.price,
            quantity_available: editForm.quantity_available,
        })
    ) {
        return;
    }

    editForm.put(update.url(editingProduct.value.id), {
        onSuccess: () => {
            showEditDialog.value = false;
            editingProduct.value = null;
            editValidation.reset();
        },
    });
}

function openDelete(product: Product) {
    deletingProduct.value = product;
    showDeleteDialog.value = true;
}

function confirmDelete() {
    if (!deletingProduct.value) {
        return;
    }

    deleting.value = true;
    router.delete(destroy.url(deletingProduct.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteDialog.value = false;
            deletingProduct.value = null;
        },
        onFinish: () => {
            deleting.value = false;
        },
    });
}
</script>

<template>
    <Head title="Admin Dashboard" />

    <AppLayout>
        <div class="flex flex-col gap-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-[oklch(0.15_0.01_260)]">
                        Products
                    </h1>
                    <p class="mt-1 text-sm text-[oklch(0.55_0.006_260)]">
                        {{ products.total }}
                        {{ products.total === 1 ? 'product' : 'products' }}
                        total
                    </p>
                </div>
                <button
                    class="cursor-pointer bg-[oklch(0.22_0.01_260)] px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-[oklch(0.32_0.01_260)]"
                    @click="openCreate"
                >
                    + Add product
                </button>
            </div>

            <div class="flex items-center gap-3">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search products…"
                    class="w-60 border border-[oklch(0.87_0.006_70)] bg-white px-4 py-2.5 text-sm text-[oklch(0.15_0.01_260)] placeholder:text-[oklch(0.65_0.005_260)] focus:border-[oklch(0.45_0.01_260)] focus:ring-2 focus:ring-[oklch(0.45_0.01_260)]/10 focus:outline-none"
                />
            </div>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                <div class="border border-[oklch(0.87_0.006_70)] bg-white p-4">
                    <p
                        class="text-xs font-semibold tracking-[0.08em] text-[oklch(0.55_0.006_260)] uppercase"
                    >
                        Units on hand
                    </p>
                    <p
                        class="mt-2 font-mono text-2xl font-semibold text-[oklch(0.15_0.01_260)] [font-variant-numeric:tabular-nums]"
                    >
                        {{ inventorySummary.total_units }}
                    </p>
                </div>
                <div class="border border-[oklch(0.87_0.006_70)] bg-white p-4">
                    <p
                        class="text-xs font-semibold tracking-[0.08em] text-[oklch(0.55_0.006_260)] uppercase"
                    >
                        Low stock
                    </p>
                    <p
                        class="mt-2 font-mono text-2xl font-semibold text-amber-700 [font-variant-numeric:tabular-nums]"
                    >
                        {{ inventorySummary.low_stock_count }}
                    </p>
                </div>
                <div class="border border-[oklch(0.87_0.006_70)] bg-white p-4">
                    <p
                        class="text-xs font-semibold tracking-[0.08em] text-[oklch(0.55_0.006_260)] uppercase"
                    >
                        Out of stock
                    </p>
                    <p
                        class="mt-2 font-mono text-2xl font-semibold text-red-600 [font-variant-numeric:tabular-nums]"
                    >
                        {{ inventorySummary.out_of_stock_count }}
                    </p>
                </div>
            </div>

            <AppEmptyState
                v-if="products.data.length === 0"
                title="No products found"
                :description="
                    search
                        ? 'Try a different search term'
                        : 'Add your first product to get started.'
                "
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
                            d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"
                        />
                    </svg>
                </template>
            </AppEmptyState>

            <div
                v-else
                class="overflow-hidden border border-[oklch(0.87_0.006_70)] bg-white"
            >
                <table class="w-full table-fixed text-sm">
                    <colgroup>
                        <col class="w-14" />
                        <col />
                        <col class="w-24" />
                        <col class="w-24" />
                        <col class="w-28" />
                        <col class="w-28" />
                        <col class="w-48" />
                    </colgroup>
                    <thead>
                        <tr
                            class="border-b border-[oklch(0.93_0.005_70)] bg-[oklch(0.975_0.004_70)]"
                        >
                            <th
                                class="px-3 py-3 text-left text-xs font-semibold tracking-[0.08em] whitespace-nowrap text-[oklch(0.55_0.006_260)] uppercase"
                            >
                                #
                            </th>
                            <th
                                class="px-3 py-3 text-left text-xs font-semibold tracking-[0.08em] whitespace-nowrap text-[oklch(0.55_0.006_260)] uppercase"
                            >
                                <button
                                    class="flex cursor-pointer items-center gap-1 hover:text-[oklch(0.22_0.01_260)]"
                                    @click="sortBy('name')"
                                >
                                    Name
                                    <span class="flex flex-col leading-none">
                                        <svg
                                            class="h-2.5 w-2.5"
                                            :class="
                                                currentSort === 'name' &&
                                                currentDirection === 'asc'
                                                    ? 'text-[oklch(0.22_0.01_260)]'
                                                    : 'text-[oklch(0.78_0.005_260)]'
                                            "
                                            viewBox="0 0 10 6"
                                            fill="currentColor"
                                        >
                                            <path d="M5 0l5 6H0z" />
                                        </svg>
                                        <svg
                                            class="h-2.5 w-2.5"
                                            :class="
                                                currentSort === 'name' &&
                                                currentDirection === 'desc'
                                                    ? 'text-[oklch(0.22_0.01_260)]'
                                                    : 'text-[oklch(0.78_0.005_260)]'
                                            "
                                            viewBox="0 0 10 6"
                                            fill="currentColor"
                                        >
                                            <path d="M5 6L0 0h10z" />
                                        </svg>
                                    </span>
                                </button>
                            </th>
                            <th
                                class="px-3 py-3 text-left text-xs font-semibold tracking-[0.08em] whitespace-nowrap text-[oklch(0.55_0.006_260)] uppercase"
                            >
                                <button
                                    class="flex cursor-pointer items-center gap-1 hover:text-[oklch(0.22_0.01_260)]"
                                    @click="sortBy('price_in_mills')"
                                >
                                    Price
                                    <span class="flex flex-col leading-none">
                                        <svg
                                            class="h-2.5 w-2.5"
                                            :class="
                                                currentSort ===
                                                    'price_in_mills' &&
                                                currentDirection === 'asc'
                                                    ? 'text-[oklch(0.22_0.01_260)]'
                                                    : 'text-[oklch(0.78_0.005_260)]'
                                            "
                                            viewBox="0 0 10 6"
                                            fill="currentColor"
                                        >
                                            <path d="M5 0l5 6H0z" />
                                        </svg>
                                        <svg
                                            class="h-2.5 w-2.5"
                                            :class="
                                                currentSort ===
                                                    'price_in_mills' &&
                                                currentDirection === 'desc'
                                                    ? 'text-[oklch(0.22_0.01_260)]'
                                                    : 'text-[oklch(0.78_0.005_260)]'
                                            "
                                            viewBox="0 0 10 6"
                                            fill="currentColor"
                                        >
                                            <path d="M5 6L0 0h10z" />
                                        </svg>
                                    </span>
                                </button>
                            </th>
                            <th
                                class="px-3 py-3 text-left text-xs font-semibold tracking-[0.08em] whitespace-nowrap text-[oklch(0.55_0.006_260)] uppercase"
                            >
                                <button
                                    class="flex cursor-pointer items-center gap-1 hover:text-[oklch(0.22_0.01_260)]"
                                    @click="sortBy('quantity_available')"
                                >
                                    Quantity
                                    <span class="flex flex-col leading-none">
                                        <svg
                                            class="h-2.5 w-2.5"
                                            :class="
                                                currentSort ===
                                                    'quantity_available' &&
                                                currentDirection === 'asc'
                                                    ? 'text-[oklch(0.22_0.01_260)]'
                                                    : 'text-[oklch(0.78_0.005_260)]'
                                            "
                                            viewBox="0 0 10 6"
                                            fill="currentColor"
                                        >
                                            <path d="M5 0l5 6H0z" />
                                        </svg>
                                        <svg
                                            class="h-2.5 w-2.5"
                                            :class="
                                                currentSort ===
                                                    'quantity_available' &&
                                                currentDirection === 'desc'
                                                    ? 'text-[oklch(0.22_0.01_260)]'
                                                    : 'text-[oklch(0.78_0.005_260)]'
                                            "
                                            viewBox="0 0 10 6"
                                            fill="currentColor"
                                        >
                                            <path d="M5 6L0 0h10z" />
                                        </svg>
                                    </span>
                                </button>
                            </th>
                            <th
                                class="px-3 py-3 text-left text-xs font-semibold tracking-[0.08em] whitespace-nowrap text-[oklch(0.55_0.006_260)] uppercase"
                            >
                                Stock
                            </th>
                            <th
                                class="px-3 py-3 text-left text-xs font-semibold tracking-[0.08em] whitespace-nowrap text-[oklch(0.55_0.006_260)] uppercase"
                            >
                                <button
                                    class="flex cursor-pointer items-center gap-1 hover:text-[oklch(0.22_0.01_260)]"
                                    @click="sortBy('updated_at')"
                                >
                                    Updated
                                    <span class="flex flex-col leading-none">
                                        <svg
                                            class="h-2.5 w-2.5"
                                            :class="
                                                currentSort === 'updated_at' &&
                                                currentDirection === 'asc'
                                                    ? 'text-[oklch(0.22_0.01_260)]'
                                                    : 'text-[oklch(0.78_0.005_260)]'
                                            "
                                            viewBox="0 0 10 6"
                                            fill="currentColor"
                                        >
                                            <path d="M5 0l5 6H0z" />
                                        </svg>
                                        <svg
                                            class="h-2.5 w-2.5"
                                            :class="
                                                currentSort === 'updated_at' &&
                                                currentDirection === 'desc'
                                                    ? 'text-[oklch(0.22_0.01_260)]'
                                                    : 'text-[oklch(0.78_0.005_260)]'
                                            "
                                            viewBox="0 0 10 6"
                                            fill="currentColor"
                                        >
                                            <path d="M5 6L0 0h10z" />
                                        </svg>
                                    </span>
                                </button>
                            </th>
                            <th
                                class="px-3 py-3 text-right text-xs font-semibold tracking-[0.08em] whitespace-nowrap text-[oklch(0.55_0.006_260)] uppercase"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="product in products.data"
                            :key="product.id"
                            class="border-b border-[oklch(0.93_0.005_70)] last:border-0 hover:bg-[oklch(0.99_0.003_70)]"
                        >
                            <td
                                class="px-3 py-3.5 font-mono text-xs whitespace-nowrap text-[oklch(0.58_0.006_260)]"
                            >
                                {{ product.id }}
                            </td>
                            <td
                                class="truncate px-3 py-3.5 font-semibold whitespace-nowrap text-[oklch(0.15_0.01_260)]"
                                :title="product.name"
                            >
                                {{ product.name }}
                            </td>
                            <td
                                class="px-3 py-3.5 font-mono whitespace-nowrap text-[oklch(0.22_0.01_260)] [font-variant-numeric:tabular-nums]"
                            >
                                {{ formatUSD(product.price_in_mills) }}
                            </td>
                            <td
                                class="px-3 py-3.5 font-mono whitespace-nowrap text-[oklch(0.22_0.01_260)] [font-variant-numeric:tabular-nums]"
                            >
                                {{ product.quantity_available }}
                            </td>
                            <td class="px-3 py-3.5 whitespace-nowrap">
                                <StockBadge
                                    :quantity="product.quantity_available"
                                />
                            </td>
                            <td
                                class="px-3 py-3.5 font-mono text-xs whitespace-nowrap text-[oklch(0.58_0.006_260)]"
                            >
                                {{ formatDate(product.updated_at) }}
                            </td>
                            <td class="px-3 py-3.5 whitespace-nowrap">
                                <div
                                    class="flex flex-nowrap items-center justify-end gap-1"
                                >
                                    <button
                                        class="cursor-pointer border border-[oklch(0.87_0.006_70)] px-2 py-1.5 text-xs font-medium text-[oklch(0.35_0.01_260)] transition-colors hover:bg-[oklch(0.96_0.005_70)]"
                                        @click="openEdit(product)"
                                    >
                                        Edit
                                    </button>
                                    <Link
                                        :href="
                                            inventoryIndex.url({
                                                query: {
                                                    product_id: product.id,
                                                },
                                            })
                                        "
                                        class="cursor-pointer border border-[oklch(0.87_0.006_70)] px-2 py-1.5 text-xs font-medium text-[oklch(0.35_0.01_260)] transition-colors hover:bg-[oklch(0.96_0.005_70)]"
                                    >
                                        Activity
                                    </Link>
                                    <button
                                        class="cursor-pointer border border-red-200 px-2 py-1.5 text-xs font-medium text-red-600 transition-colors hover:bg-red-50"
                                        @click="openDelete(product)"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <AppPagination
                :from="products.from"
                :to="products.to"
                :total="products.total"
                :last-page="products.last_page"
                :links="products.links"
            />
        </div>
        <AppDialog
            :open="showCreateDialog"
            title="Add product"
            @close="showCreateDialog = false"
        >
            <form
                id="create-form"
                class="flex flex-col gap-4 pt-1"
                @submit.prevent="submitCreate"
            >
                <div class="flex flex-col gap-1.5">
                    <InputLabel for="create-name" value="Name" />
                    <TextInput
                        id="create-name"
                        v-model="createForm.name"
                        type="text"
                        placeholder="e.g. Coca-Cola 330ml"
                        :error="!!createDisplayErrors.name"
                        @blur="
                            createValidation.touchField('name', createForm.name)
                        "
                    />
                    <InputError :message="createDisplayErrors.name" />
                </div>
                <div class="flex flex-col gap-1.5">
                    <InputLabel for="create-price" value="Price (USD)" />
                    <TextInput
                        id="create-price"
                        v-model="createForm.price"
                        type="number"
                        placeholder="e.g. 1.50"
                        step="0.01"
                        min="0.01"
                        :error="!!createDisplayErrors.price"
                        @blur="
                            createValidation.touchField(
                                'price',
                                createForm.price,
                            )
                        "
                    />
                    <InputError :message="createDisplayErrors.price" />
                </div>
                <div class="flex flex-col gap-1.5">
                    <InputLabel for="create-quantity" value="Quantity" />
                    <TextInput
                        id="create-quantity"
                        v-model="createForm.quantity_available"
                        type="number"
                        placeholder="e.g. 10"
                        min="0"
                        :error="!!createDisplayErrors.quantity_available"
                        @blur="
                            createValidation.touchField(
                                'quantity_available',
                                createForm.quantity_available,
                            )
                        "
                    />
                    <InputError
                        :message="createDisplayErrors.quantity_available"
                    />
                </div>
            </form>
            <template #actions>
                <button
                    type="submit"
                    form="create-form"
                    :disabled="createForm.processing"
                    class="flex-1 cursor-pointer bg-[oklch(0.22_0.01_260)] py-2.5 text-sm font-semibold text-white transition-colors hover:bg-[oklch(0.32_0.01_260)] disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {{ createForm.processing ? 'Saving…' : 'Add product' }}
                </button>
                <button
                    type="button"
                    class="flex-1 cursor-pointer border border-[oklch(0.87_0.006_70)] py-2.5 text-sm font-medium text-[oklch(0.48_0.008_260)] transition-colors hover:bg-[oklch(0.96_0.005_70)]"
                    @click="showCreateDialog = false"
                >
                    Cancel
                </button>
            </template>
        </AppDialog>

        <AppDialog
            :open="showEditDialog"
            title="Edit product"
            @close="showEditDialog = false"
        >
            <form
                id="edit-form"
                class="flex flex-col gap-4 pt-1"
                @submit.prevent="submitEdit"
            >
                <div class="flex flex-col gap-1.5">
                    <InputLabel for="edit-name" value="Name" />
                    <TextInput
                        id="edit-name"
                        v-model="editForm.name"
                        type="text"
                        placeholder="e.g. Coca-Cola 330ml"
                        :error="!!editDisplayErrors.name"
                        @blur="editValidation.touchField('name', editForm.name)"
                    />
                    <InputError :message="editDisplayErrors.name" />
                </div>
                <div class="flex flex-col gap-1.5">
                    <InputLabel for="edit-price" value="Price (USD)" />
                    <TextInput
                        id="edit-price"
                        v-model="editForm.price"
                        type="number"
                        placeholder="e.g. 1.50"
                        step="0.01"
                        min="0.01"
                        :error="!!editDisplayErrors.price"
                        @blur="
                            editValidation.touchField('price', editForm.price)
                        "
                    />
                    <InputError :message="editDisplayErrors.price" />
                </div>
                <div class="flex flex-col gap-1.5">
                    <InputLabel for="edit-quantity" value="Quantity" />
                    <TextInput
                        id="edit-quantity"
                        v-model="editForm.quantity_available"
                        type="number"
                        placeholder="e.g. 10"
                        min="0"
                        :error="!!editDisplayErrors.quantity_available"
                        @blur="
                            editValidation.touchField(
                                'quantity_available',
                                editForm.quantity_available,
                            )
                        "
                    />
                    <InputError
                        :message="editDisplayErrors.quantity_available"
                    />
                </div>
            </form>
            <template #actions>
                <button
                    type="submit"
                    form="edit-form"
                    :disabled="editForm.processing"
                    class="flex-1 cursor-pointer bg-[oklch(0.22_0.01_260)] py-2.5 text-sm font-semibold text-white transition-colors hover:bg-[oklch(0.32_0.01_260)] disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {{ editForm.processing ? 'Saving…' : 'Save changes' }}
                </button>
                <button
                    type="button"
                    class="flex-1 cursor-pointer border border-[oklch(0.87_0.006_70)] py-2.5 text-sm font-medium text-[oklch(0.48_0.008_260)] transition-colors hover:bg-[oklch(0.96_0.005_70)]"
                    @click="showEditDialog = false"
                >
                    Cancel
                </button>
            </template>
        </AppDialog>

        <AppDialog
            :open="showDeleteDialog"
            title="Delete product?"
            @close="showDeleteDialog = false"
        >
            <span v-if="deletingProduct">
                <strong class="font-semibold text-[oklch(0.15_0.01_260)]">{{
                    deletingProduct.name
                }}</strong>
                will be permanently removed. This cannot be undone.
            </span>
            <template #actions>
                <button
                    :disabled="deleting"
                    class="flex-1 cursor-pointer bg-red-600 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50"
                    @click="confirmDelete"
                >
                    {{ deleting ? 'Deleting…' : 'Delete' }}
                </button>
                <button
                    type="button"
                    class="flex-1 cursor-pointer border border-[oklch(0.87_0.006_70)] py-2.5 text-sm font-medium text-[oklch(0.48_0.008_260)] transition-colors hover:bg-[oklch(0.96_0.005_70)]"
                    @click="showDeleteDialog = false"
                >
                    Cancel
                </button>
            </template>
        </AppDialog>
    </AppLayout>
</template>
