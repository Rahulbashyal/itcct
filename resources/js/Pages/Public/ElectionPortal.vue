<script setup>
import { Head, Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import GlassCard from '@/Components/GlassCard.vue';

defineProps({
    elections: Array
});
</script>

<template>
    <Head title="Election Nexus" />

    <GuestLayout>
        <div class="pt-32 pb-20 px-4 sm:px-8 relative overflow-hidden">
            <!-- Background Glow -->
            <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-primary-blue/10 rounded-full blur-[150px] -mr-32 -mt-32"></div>

            <div class="max-w-7xl mx-auto relative z-10">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-end gap-12 mb-20">
                    <div class="max-w-2xl space-y-6">
                        <div class="inline-flex items-center px-4 py-2 rounded-full bg-primary-blue/10 border border-primary-blue/20 text-primary-blue text-[10px] font-black tracking-widest uppercase">Digital Democracy</div>
                        <h1 class="text-5xl sm:text-8xl font-black text-white leading-[0.85] tracking-tighter italic">ELECTION <span class="text-primary-blue underline decoration-primary-blue/30">NEXUS.</span></h1>
                        <p class="text-xl text-gray-500 font-medium">
                            Welcome to the most transparent student election platform in Nepal. Verified, encrypted, and real-time.
                        </p>
                    </div>
                </div>

                <!-- Elections List -->
                <div class="space-y-12">
                    <div v-for="election in elections" :key="election.id" class="relative group">
                        <div class="absolute -inset-1 bg-gradient-to-r from-primary-blue to-secondary-teal rounded-[3rem] blur opacity-25 group-hover:opacity-50 transition duration-1000 group-hover:duration-200"></div>
                        <GlassCard class="relative p-10 sm:p-16 flex flex-col xl:flex-row items-center gap-12 border-2 border-white/5 group-hover:border-white/20 transition-all overflow-hidden rounded-[3rem]">
                            <!-- Status Badge -->
                            <div class="absolute top-8 right-8 px-4 py-2 bg-accent-green/10 border border-accent-green/20 rounded-full text-accent-green text-[10px] font-black uppercase tracking-widest animate-pulse">
                                {{ election.status }}
                            </div>

                            <div class="xl:w-2/3 space-y-6">
                                <h2 class="text-4xl sm:text-6xl font-black text-white tracking-tighter leading-tight">{{ election.title }}</h2>
                                <p class="text-lg text-gray-400 font-medium leading-relaxed">{{ election.description }}</p>
                                
                                <div class="flex flex-wrap gap-8 text-white">
                                    <div class="space-y-1">
                                        <p class="text-[10px] font-black text-gray-600 uppercase tracking-widest">Commencement</p>
                                        <p class="text-lg font-black italic">{{ new Date(election.start_date).toLocaleDateString() }}</p>
                                    </div>
                                    <div class="space-y-1">
                                        <p class="text-[10px] font-black text-gray-600 uppercase tracking-widest">Termination</p>
                                        <p class="text-lg font-black italic">{{ new Date(election.end_date).toLocaleDateString() }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="xl:w-1/3 w-full space-y-4">
                                <Link :href="route('elections.show', election.id)">
                                    <PrimaryButton class="w-full h-16 text-lg group-hover:scale-105 transition-transform">Enter Election Lobby</PrimaryButton>
                                </Link>
                                <p class="text-center text-[10px] font-black text-gray-600 uppercase tracking-widest">Login required to cast vote</p>
                            </div>
                        </GlassCard>
                    </div>

                    <!-- No active elections -->
                    <div v-if="elections.length === 0" class="p-20 text-center bg-white/[0.02] border border-white/5 rounded-[4rem]">
                        <div class="w-24 h-24 bg-white/5 rounded-full flex items-center justify-center mx-auto mb-8 border border-white/10 text-gray-600">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <h3 class="text-3xl font-black text-white italic mb-2">Passive Phase.</h3>
                        <p class="text-gray-500 font-medium text-lg">No active elections are currently live. Check back soon for the next cycle.</p>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
