<script setup>
import { Head, Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

defineProps({
    status: String,
});
</script>

<template>
    <GuestLayout>
        <Head title="Email Verification" />

        <div class="mb-4 text-sm text-gray-600">
            Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.
        </div>

        <div v-if="status === 'verification-link-sent'" class="mb-4 font-medium text-sm text-green-600">
            A new verification link has been sent to the email address you provided during registration.
        </div>

        <div class="mt-4 flex items-center justify-between">
            <form method="POST" action="/email/verification-notification">
                <button type="submit" class="px-6 py-3 bg-primary-blue text-white rounded-full font-bold text-xs uppercase tracking-widest hover:bg-primary-blue/90 transition-all">
                    Resend Verification Email
                </button>
            </form>

            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-blue"
            >
                Log Out
            </Link>
        </div>
    </GuestLayout>
</template>
