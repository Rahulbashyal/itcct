<script setup>
import { computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);
const isVerified = computed(() => !!user.value.email_verified_at);

const resendVerification = () => {
    router.post(route('verification.send'), {}, {
        onSuccess: () => alert('Verification email resent!')
    });
};
</script>

<template>
    <div class="verify-app h-full flex flex-col items-center justify-center bg-[#1e293b] text-white p-8 text-center">
        <div class="w-20 h-20 rounded-full flex items-center justify-center mb-6 text-4xl shadow-lg"
             :class="isVerified ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'">
            {{ isVerified ? '✓' : '✉' }}
        </div>
        
        <h2 class="text-2xl font-bold mb-2">{{ isVerified ? 'Verified Account' : 'Action Required' }}</h2>
        <p class="text-gray-400 max-w-sm mb-8">
            {{ isVerified 
                ? 'Your email is verified. You have full access to all IT Club CCT digital assets.' 
                : 'Your email address is not yet verified. Please check your inbox or click below to resend the link.' 
            }}
        </p>

        <div v-if="!isVerified">
            <button 
                @click="resendVerification" 
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-xl font-bold transition-all shadow-xl hover:transform hover:scale-105"
            >
                Resend Verification Email
            </button>
        </div>
        <div v-else>
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-green-500/10 text-green-400 rounded-full text-sm font-bold">
                <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                Account in Good Standing
            </div>
        </div>
    </div>
</template>
