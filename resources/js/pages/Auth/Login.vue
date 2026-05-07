<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import InputError from '@/components/InputError.vue'
import InputLabel from '@/components/InputLabel.vue'
import TextInput from '@/components/TextInput.vue'
import { useClientValidation } from '@/composables/useClientValidation'

interface DemoAccount {
    role: string
    email: string
    password: string
}

const props = defineProps<{
    demoAccounts: DemoAccount[]
}>()

const form = useForm({
    email: '',
    password: '',
})

const { errors: clientErrors, validate, touchField, reset } = useClientValidation({
    email: [
        (v) => !v.trim() ? 'Email is required.' : null,
        (v) => !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()) ? 'Please enter a valid email address.' : null,
    ],
    password: [
        (v) => !v ? 'Password is required.' : null,
    ],
})

const displayErrors = computed(() => ({
    email: clientErrors.value.email || form.errors.email,
    password: clientErrors.value.password || form.errors.password,
}))

function fillDemo(account: DemoAccount) {
    form.email = account.email
    form.password = account.password
    reset()
}

function submit() {
    if (!validate({ email: form.email, password: form.password })) { return }
    form.post('/login', {
        onSuccess: () => reset(),
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <Head title="Sign in" />

    <div class="flex min-h-screen flex-col items-center justify-center bg-[oklch(0.965_0.005_70)] px-4">

        <div class="mb-8 text-center">
            <Link
                href="/"
                class="text-xl font-bold text-[oklch(0.15_0.01_260)] transition-colors hover:text-[oklch(0.38_0.01_260)]"
            >
                Vending Machine
            </Link>
            <p class="mt-1.5 text-sm text-[oklch(0.55_0.006_260)]">Sign in to your account</p>
        </div>

        <div class="w-full max-w-sm border border-[oklch(0.87_0.006_70)] bg-white">

            <div
                v-if="props.demoAccounts.length > 0"
                class="border-b border-[oklch(0.93_0.005_70)] px-6 pt-5 pb-4"
            >
                <p class="mb-3 text-xs font-semibold uppercase tracking-[0.08em] text-[oklch(0.62_0.006_260)]">
                    Demo accounts
                </p>
                <div class="flex flex-col gap-1.5">
                    <button
                        v-for="account in props.demoAccounts"
                        :key="account.email"
                        type="button"
                        class="flex cursor-pointer items-center justify-between border border-[oklch(0.87_0.006_70)] px-3 py-2 text-left transition-colors hover:bg-[oklch(0.965_0.005_70)]"
                        @click="fillDemo(account)"
                    >
                        <span class="text-xs font-semibold text-[oklch(0.22_0.01_260)]">{{ account.role }}</span>
                        <span class="font-mono text-xs text-[oklch(0.58_0.006_260)]">{{ account.email }}</span>
                    </button>
                </div>
            </div>

            <form class="flex flex-col gap-5 px-6 pt-5 pb-6" @submit.prevent="submit">
                <div class="flex flex-col gap-1.5">
                    <InputLabel for="email" value="Email" />
                    <TextInput
                        id="email"
                        v-model="form.email"
                        type="email"
                        placeholder="you@example.com"
                        autocomplete="email"
                        :error="!!displayErrors.email"
                        @blur="touchField('email', form.email)"
                    />
                    <InputError :message="displayErrors.email" />
                </div>

                <div class="flex flex-col gap-1.5">
                    <InputLabel for="password" value="Password" />
                    <TextInput
                        id="password"
                        v-model="form.password"
                        type="password"
                        placeholder="••••••••"
                        autocomplete="current-password"
                        :error="!!displayErrors.password"
                        @blur="touchField('password', form.password)"
                    />
                    <InputError :message="displayErrors.password" />
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="mt-1 w-full cursor-pointer bg-[oklch(0.22_0.01_260)] py-2.5 text-sm font-semibold text-white transition-colors hover:bg-[oklch(0.32_0.01_260)] disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {{ form.processing ? 'Signing in…' : 'Sign in' }}
                </button>
            </form>

        </div>
    </div>
</template>
