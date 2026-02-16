<script setup>
import { Head, Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import GlassCard from '@/Components/GlassCard.vue';

const visionValues = [
    { title: 'Governance', desc: 'Implementing digital-first operational standards for student leadership.', icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z' },
    { title: 'Innovation', desc: 'Providing a sandbox for builders to turn code into real-world impact.', icon: 'M13 10V3L4 14h7v7l9-11h-7z' },
    { title: 'Legacy', desc: 'Preserving the history of Crimson IT excellence via digital archiving.', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' }
];
</script>

<template>
    <Head title="About the Legacy" />

    <GuestLayout>
        <div class="pt-32 pb-20 px-4 sm:px-8">
            <div class="max-w-7xl mx-auto">
                <!-- Hero Section -->
                <div class="text-center mb-24 space-y-6">
                    <h1 class="text-5xl sm:text-8xl font-black text-white tracking-tighter italic">OUR <span class="text-primary-blue">GENESIS.</span></h1>
                    <p class="text-xl text-gray-500 max-w-2xl mx-auto font-medium leading-relaxed">
                        Born at Crimson College of Technology, the IT Club evolved from a student group into a powerhouse of digital governance and tech innovation.
                    </p>
                </div>

                <!-- Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center mb-40">
                    <div class="space-y-8">
                        <div class="inline-flex items-center px-4 py-2 rounded-full bg-white/5 border border-white/10 text-secondary-teal text-[10px] font-black tracking-widest uppercase">The Mission</div>
                        <h2 class="text-4xl font-black text-white leading-tight tracking-tight">Digitizing Leadership for the <span class="text-secondary-teal underline decoration-secondary-teal/30">Next Generation</span>.</h2>
                        <p class="text-lg text-gray-400 font-medium">
                            We don't just host events. We build infrastructure. Our platform serves as the digital backbone for elections, budget tracking, and real-time collaboration. We believe that student governance should be as transparent and efficient as the technology we study.
                        </p>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-0 bg-primary-blue/20 blur-[100px] rounded-full"></div>
                        <GlassCard class="p-12 border-2 border-white/10 relative z-10">
                            <div class="grid grid-cols-2 gap-8">
                                <div>
                                    <p class="text-4xl font-black text-white">5+</p>
                                    <p class="text-[10px] font-black text-gray-600 uppercase tracking-widest">Years of Legacy</p>
                                </div>
                                <div>
                                    <p class="text-4xl font-black text-white">500+</p>
                                    <p class="text-[10px] font-black text-gray-600 uppercase tracking-widest">Alumni Network</p>
                                </div>
                                <div>
                                    <p class="text-4xl font-black text-white">45+</p>
                                    <p class="text-[10px] font-black text-gray-600 uppercase tracking-widest">Live Projects</p>
                                </div>
                                <div>
                                    <p class="text-4xl font-black text-white">100%</p>
                                    <p class="text-[10px] font-black text-gray-600 uppercase tracking-widest">Transparent</p>
                                </div>
                            </div>
                        </GlassCard>
                    </div>
                </div>

                <!-- Values -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div v-for="value in visionValues" :key="value.title" class="p-10 bg-white/[0.02] border border-white/5 rounded-[3rem] group hover:bg-white/[0.05] transition-all">
                        <div class="w-12 h-12 rounded-2xl bg-primary-blue/10 flex items-center justify-center text-primary-blue mb-8 group-hover:scale-110 transition-transform">
                             <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="value.icon"></path></svg>
                        </div>
                        <h3 class="text-2xl font-black text-white mb-4 italic tracking-tight">{{ value.title }}</h3>
                        <p class="text-gray-500 font-medium leading-relaxed">{{ value.desc }}</p>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
