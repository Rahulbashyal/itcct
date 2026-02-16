<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import GlassCard from '@/Components/GlassCard.vue';
import FormInput from '@/Components/FormInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { computed } from 'vue';

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email || '',
    batch: user.batch || '',
});

const submit = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
    });
};

const idCardData = computed(() => ({
    name: user.name,
    symbol: user.symbol_number,
    role: user.role,
    batch: user.batch || '2081',
}));
</script>

<template>
    <Head title="My Profile" />

    <AuthenticatedLayout>
        <template #header_breadcrumb>
            Account / Profile
        </template>

        <div class="max-w-4xl mx-auto space-y-12">
            <!-- Header -->
            <div>
                <h1 class="text-4xl font-extrabold tracking-tight mb-2">My Profile</h1>
                <p class="text-gray-400">Manage your digital presence within the IT Club ecosystem.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
                <!-- Update Profile Form -->
                <GlassCard class="space-y-6">
                    <h2 class="text-xl font-bold mb-4">Personal Information</h2>
                    <form @submit.prevent="submit" class="space-y-4">
                        <FormInput 
                            label="Full Name"
                            v-model="form.name"
                            :error="form.errors.name"
                            placeholder="Enter your full name"
                        />
                        <FormInput 
                            label="Email Address"
                            v-model="form.email"
                            :error="form.errors.email"
                            type="email"
                            placeholder="your.email@example.com"
                        />
                        <FormInput 
                            label="Graduation Batch (e.g., 2081)"
                            v-model="form.batch"
                            :error="form.errors.batch"
                            placeholder="2081"
                        />
                        
                        <div class="pt-4">
                            <PrimaryButton :disabled="form.processing" class="w-full justify-center">
                                <span v-if="form.processing">Updating...</span>
                                <span v-else>Save Changes</span>
                            </PrimaryButton>
                        </div>
                    </form>
                </GlassCard>

                <!-- Digital ID Card Preview -->
                <div class="space-y-6">
                    <h2 class="text-xl font-bold mb-4">Digital Member ID</h2>
                    
                    <div class="relative group">
                        <!-- ID Card Component Placeholder Style -->
                        <div class="w-full aspect-[1.586/1] bg-gradient-to-br from-[#1a1c2c] to-[#4a192c] rounded-3xl p-8 border border-white/10 shadow-2xl relative overflow-hidden flex flex-col justify-between">
                            <!-- Background Pattern -->
                             <div class="absolute inset-0 opacity-10 pointer-events-none">
                                <svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg">
                                    <defs>
                                        <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                                            <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="0.5"/>
                                        </pattern>
                                    </defs>
                                    <rect width="100%" height="100%" fill="url(#grid)" />
                                </svg>
                            </div>
                            
                            <!-- Card Header -->
                            <div class="flex justify-between items-start relative z-10">
                                <div class="flex flex-col">
                                    <span class="text-xs font-black uppercase tracking-[0.2em] text-secondary-teal opacity-80">Official Member</span>
                                    <span class="text-lg font-bold">IT Club of CCT</span>
                                </div>
                                <div class="w-12 h-12 bg-white/5 rounded-xl border border-white/20 flex items-center justify-center">
                                     <svg class="w-6 h-6 text-white/50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                </div>
                            </div>

                            <!-- Card Middle -->
                            <div class="flex items-center space-x-6 relative z-10">
                                <div class="w-24 h-24 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center font-black text-3xl shrink-0 backdrop-blur-md">
                                    {{ user.name.charAt(0) }}
                                </div>
                                <div>
                                    <h3 class="text-2xl font-black tracking-tight leading-none mb-2">{{ user.name }}</h3>
                                    <p class="text-sm font-bold text-gray-400 uppercase tracking-widest">{{ user.role }}</p>
                                </div>
                            </div>

                            <!-- Card Footer -->
                            <div class="flex justify-between items-end relative z-10">
                                <div class="flex flex-col">
                                    <span class="text-[10px] text-gray-500 font-bold uppercase tracking-widest">Symbol Number</span>
                                    <span class="text-sm font-mono tracking-tighter">{{ user.symbol_number }}</span>
                                </div>
                                <div class="flex flex-col items-end">
                                    <span class="text-[10px] text-gray-500 font-bold uppercase tracking-widest">Batch</span>
                                    <span class="text-sm font-black">{{ form.batch || '2081' }}</span>
                                </div>
                            </div>

                            <!-- Holographic Overlay -->
                            <div class="absolute inset-0 bg-gradient-to-tr from-transparent via-white/5 to-transparent skew-x-[-20deg] group-hover:translate-x-[200%] transition-transform duration-1000 ease-in-out pointer-events-none"></div>
                        </div>
                    </div>

                    <p class="text-xs text-center text-gray-500 px-8">
                        This digital ID card is generated in real-time. Use it for entry to events and accessing club services.
                    </p>
                    
                    <button class="w-full py-3 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl text-xs font-bold uppercase tracking-widest transition-colors flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Download ID Card
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
