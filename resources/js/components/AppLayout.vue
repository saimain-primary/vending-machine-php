<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { index as adminDashboardIndex } from '@/actions/App/Http/Controllers/Admin/DashboardController';
import { index as adminInventoryIndex } from '@/actions/App/Http/Controllers/Admin/InventoryController';
import { index as adminOrdersIndex } from '@/actions/App/Http/Controllers/Admin/OrderController';
import AppDialog from '@/components/AppDialog.vue';

defineProps<{
    title?: string;
}>();

const page = usePage();
const authenticatedUser = computed(() => page.props.auth?.user ?? null);
const isAdmin = computed(() => authenticatedUser.value?.role === 'admin');
const currentPath = computed(() => new URL(page.url, 'http://x').pathname);

const dropdownOpen = ref(false);
const logoutDialogOpen = ref(false);
const loggingOut = ref(false);

function openLogoutDialog() {
    dropdownOpen.value = false;
    logoutDialogOpen.value = true;
}

function logout() {
    loggingOut.value = true;
    router.post(
        '/logout',
        {},
        {
            onFinish: () => {
                loggingOut.value = false;
                logoutDialogOpen.value = false;
            },
        },
    );
}
</script>

<template>
    <div
        class="min-h-screen bg-[oklch(0.965_0.005_70)]"
        @click="dropdownOpen = false"
    >
        <header class="border-b border-[oklch(0.88_0.006_70)] bg-white">
            <div
                class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4"
            >
                <div class="flex items-center gap-6">
                    <Link
                        href="/"
                        class="text-base font-bold text-[oklch(0.15_0.01_260)] transition-colors hover:text-[oklch(0.38_0.01_260)]"
                    >
                        Vending Machine
                    </Link>

                    <nav v-if="isAdmin" class="flex items-center gap-1">
                        <Link
                            :href="adminDashboardIndex.url()"
                            :class="[
                                'px-3 py-1.5 text-sm font-medium transition-colors',
                                currentPath === '/admin/dashboard'
                                    ? 'bg-[oklch(0.22_0.01_260)] text-white'
                                    : 'text-[oklch(0.38_0.01_260)] hover:bg-[oklch(0.93_0.005_70)]',
                            ]"
                        >
                            Products
                        </Link>
                        <Link
                            :href="adminOrdersIndex.url()"
                            :class="[
                                'px-3 py-1.5 text-sm font-medium transition-colors',
                                currentPath === '/admin/orders'
                                    ? 'bg-[oklch(0.22_0.01_260)] text-white'
                                    : 'text-[oklch(0.38_0.01_260)] hover:bg-[oklch(0.93_0.005_70)]',
                            ]"
                        >
                            All Orders
                        </Link>
                        <Link
                            :href="adminInventoryIndex.url()"
                            :class="[
                                'px-3 py-1.5 text-sm font-medium transition-colors',
                                currentPath === '/admin/inventory'
                                    ? 'bg-[oklch(0.22_0.01_260)] text-white'
                                    : 'text-[oklch(0.38_0.01_260)] hover:bg-[oklch(0.93_0.005_70)]',
                            ]"
                        >
                            Inventory
                        </Link>
                    </nav>
                </div>

                <div v-if="authenticatedUser" class="relative" @click.stop>
                    <button
                        class="flex cursor-pointer items-center gap-2 px-3 py-2 text-sm font-medium text-[oklch(0.22_0.01_260)] transition-colors hover:bg-[oklch(0.965_0.005_70)]"
                        @click="dropdownOpen = !dropdownOpen"
                    >
                        {{ authenticatedUser.name }}
                        <svg
                            :class="[
                                'h-4 w-4 text-[oklch(0.55_0.006_260)] transition-transform duration-150',
                                dropdownOpen && 'rotate-180',
                            ]"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m19 9-7 7-7-7"
                            />
                        </svg>
                    </button>

                    <Transition
                        enter-active-class="transition duration-100 ease-out"
                        enter-from-class="opacity-0 scale-95 -translate-y-1"
                        enter-to-class="opacity-100 scale-100 translate-y-0"
                        leave-active-class="transition duration-75 ease-in"
                        leave-from-class="opacity-100 scale-100 translate-y-0"
                        leave-to-class="opacity-0 scale-95 -translate-y-1"
                    >
                        <div
                            v-if="dropdownOpen"
                            class="absolute top-full right-0 mt-1 w-44 border border-[oklch(0.88_0.006_70)] bg-white py-1 shadow-lg"
                        >
                            <template v-if="!isAdmin">
                                <Link
                                    href="/orders"
                                    class="flex w-full cursor-pointer items-center gap-2 px-4 py-2.5 text-sm font-medium text-[oklch(0.22_0.01_260)] transition-colors hover:bg-[oklch(0.965_0.005_70)]"
                                    @click="dropdownOpen = false"
                                >
                                    <svg
                                        class="h-4 w-4 text-[oklch(0.55_0.006_260)]"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke-width="2"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"
                                        />
                                    </svg>
                                    Order History
                                </Link>
                                <div
                                    class="my-1 border-t border-[oklch(0.92_0.005_70)]"
                                />
                            </template>
                            <button
                                class="flex w-full cursor-pointer items-center gap-2 px-4 py-2.5 text-sm font-medium text-red-600 transition-colors hover:bg-red-50"
                                @click="openLogoutDialog"
                            >
                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="2"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"
                                    />
                                </svg>
                                Logout
                            </button>
                        </div>
                    </Transition>
                </div>

                <Link
                    v-else
                    href="/login"
                    class="cursor-pointer border border-[oklch(0.87_0.006_70)] bg-white px-4 py-2 text-sm font-medium text-[oklch(0.22_0.01_260)] transition-colors hover:bg-[oklch(0.965_0.005_70)]"
                >
                    Login
                </Link>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-6 py-8">
            <h1
                v-if="title"
                class="mb-6 text-2xl font-bold text-[oklch(0.15_0.01_260)]"
            >
                {{ title }}
            </h1>
            <slot />
        </main>
    </div>

    <AppDialog
        :open="logoutDialogOpen"
        title="Log out?"
        @close="logoutDialogOpen = false"
    >
        You'll need to log in again to make purchases.
        <template #actions>
            <button
                :disabled="loggingOut"
                class="flex-1 cursor-pointer bg-red-600 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50"
                @click="logout"
            >
                {{ loggingOut ? 'Logging out…' : 'Logout' }}
            </button>
            <button
                class="flex-1 cursor-pointer border border-[oklch(0.87_0.006_70)] py-2.5 text-sm font-medium text-[oklch(0.48_0.008_260)] transition-colors hover:bg-[oklch(0.96_0.005_70)]"
                @click="logoutDialogOpen = false"
            >
                Cancel
            </button>
        </template>
    </AppDialog>
</template>
