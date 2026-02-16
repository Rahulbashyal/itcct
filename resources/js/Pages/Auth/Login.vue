<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    symbol_number: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Login" />

    <div class="min-h-screen bg-dark-gray flex items-center justify-center p-6 relative overflow-hidden">
        <!-- Animated Background Elements -->
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-primary-blue/10 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-secondary-teal/10 rounded-full blur-[120px]"></div>

        <div class="w-full max-w-md relative z-10">
            <!-- Logo & Title -->
            <div class="text-center mb-10">
                <h1 class="text-4xl font-bold bg-gradient-to-r from-white to-gray-400 bg-clip-text text-transparent mb-2">
                    Digital Governance
                </h1>
                <p class="text-gray-400">IT Club of Crimson College of Technology</p>
            </div>

            <!-- Login Card -->
            <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-3xl p-8 shadow-2xl">
                <h2 class="text-2xl font-semibold mb-6">Welcome Back</h2>

                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Symbol Number</label>
                        <input 
                            v-model="form.symbol_number"
                            type="text" 
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 focus:outline-none focus:border-secondary-teal focus:ring-1 focus:ring-secondary-teal transition-all"
                            placeholder="Enter your symbol number"
                            required
                        >
                        <div v-if="form.errors.symbol_number" class="text-red-400 text-xs mt-1">{{ form.errors.symbol_number }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Password</label>
                        <input 
                            v-model="form.password"
                            type="password" 
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 focus:outline-none focus:border-secondary-teal focus:ring-1 focus:ring-secondary-teal transition-all"
                            placeholder="••••••••"
                            required
                        >
                        <div v-if="form.errors.password" class="text-red-400 text-xs mt-1">{{ form.errors.password }}</div>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" v-model="form.remember" class="w-4 h-4 rounded border-white/10 bg-white/5 text-secondary-teal focus:ring-secondary-teal">
                            <span class="text-sm text-gray-400">Remember me</span>
                        </label>
                    </div>

                    <button 
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-primary-blue hover:bg-primary-blue/80 text-white font-semibold py-3 rounded-xl transition-all shadow-lg shadow-primary-blue/20 disabled:opacity-50"
                    >
                        <span v-if="form.processing">Authenticating...</span>
                        <span v-else>Login to Ecosystem</span>
                    </button>
                </form>
            </div>

            <!-- Footer links -->
            <div class="text-center mt-8 space-x-4 text-sm text-gray-500">
                <a href="#" class="hover:text-white transition-colors">Forgot password?</a>
                <span>•</span>
                <a href="/" class="hover:text-white transition-colors">Back to Home</a>
            </div>
        </div>
    </div>
</template>
