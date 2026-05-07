<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/components/AppLayout.vue'
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout title="Dashboard">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <p class="text-sm text-gray-500">Welcome! Browse available products to make a purchase.</p>
        </div>
    </AppLayout>
</template>
