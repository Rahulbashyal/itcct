<script setup>
import { Head } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import GlassCard from '@/Components/GlassCard.vue';

const props = defineProps({
    alumni: Array
});

// Dummy data for initial view if DB is empty
const sampleAlumni = [
    { 
        name: 'Bishal Sharma Parajuli', 
        role: 'Fifth President (2025–Present)', 
        batch: '2082', 
        achievements: 'Current President leading the club towards national-level excellence and technical innovation.', 
        image: null 
    },
    { 
        name: 'Rahul Bashyal', 
        role: 'Fourth President (2024–2025)', 
        batch: '2081', 
        achievements: 'Spearheaded the digital transformation of the club and developed the IT Ecosystem platform.', 
        image: null 
    },
    { 
        name: 'Nabin Khanal', 
        role: 'Third President (2023–2024)', 
        batch: '2080', 
        achievements: 'Enhanced club outreach and established strong community engagement initiatives.', 
        image: null 
    },
    { 
        name: 'Amrit Lamshal', 
        role: 'Second President (2022–2023)', 
        batch: '2079', 
        achievements: 'Focused on internal growth, skill development, and organizing seminal tech workshops.', 
        image: null 
    },
    { 
        name: 'Ashok Kc', 
        role: 'Founding President (2021–2022)', 
        batch: '2078', 
        achievements: 'Entrepreneur and CEO of Abssoft Company Pvt Ltd. Founded the club in 2021 with a vision to empower tech enthusiasts.', 
        image: null 
    },
];

const displayAlumni = props.alumni.length > 0 ? props.alumni : sampleAlumni;
</script>

<template>
    <Head title="Hall of Fame" />

    <GuestLayout>
        <section class="py-20 px-6 max-w-7xl mx-auto">
            <div class="text-center mb-20 scroll-reveal">
                <h1 class="text-5xl md:text-7xl font-black mb-6 tracking-tight bg-gradient-to-br from-white to-gray-500 bg-clip-text text-transparent">
                    Hall of <span class="text-secondary-teal">Fame</span>
                </h1>
                <p class="text-gray-500 max-w-2xl mx-auto text-lg leading-relaxed">
                    Honoring the visionaries and leaders who built the legacy of the IT Club of Crimson College of Technology.
                </p>
            </div>

            <div class="relative">
                <!-- Vertical Center Line -->
                <div class="absolute left-1/2 transform -translate-x-1/2 h-full w-[2px] bg-gradient-to-b from-secondary-teal/50 via-primary-blue/30 to-transparent hidden md:block"></div>

                <div class="space-y-16 md:space-y-32">
                    <div 
                        v-for="(member, index) in displayAlumni" 
                        :key="index"
                        class="relative flex flex-col md:flex-row items-center gap-8 md:gap-0"
                        :class="index % 2 === 0 ? 'md:flex-row' : 'md:flex-row-reverse'"
                    >
                        <!-- Timeline Node -->
                        <div class="absolute left-1/2 transform -translate-x-1/2 w-4 h-4 rounded-full bg-secondary-teal shadow-[0_0_15px_rgba(0,168,232,0.8)] z-10 hidden md:block"></div>

                        <!-- Card Container -->
                        <div class="w-full md:w-[45%]">
                            <GlassCard class="hover:scale-[1.02] transition-transform duration-500 group">
                                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
                                    <!-- Profile Image Placeholder -->
                                    <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-primary-blue to-secondary-teal p-[1px] shrink-0">
                                        <div class="w-full h-full rounded-2xl bg-dark-gray flex items-center justify-center overflow-hidden">
                                            <svg v-if="!member.image" class="w-12 h-12 text-white/10" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                                            <img v-else :src="member.image" class="w-full h-full object-cover">
                                        </div>
                                    </div>

                                    <div class="flex-1 text-center sm:text-left">
                                        <span class="text-[10px] font-bold uppercase tracking-widest text-secondary-teal bg-secondary-teal/10 border border-secondary-teal/20 px-2 py-0.5 rounded-full mb-3 inline-block">
                                            Batch {{ member.batch }}
                                        </span>
                                        <h3 class="text-2xl font-black mb-1 leading-none">{{ member.name }}</h3>
                                        <p class="text-sm text-primary-blue font-bold mb-4 uppercase tracking-tighter">{{ member.role }}</p>
                                        <p class="text-gray-500 text-sm leading-relaxed border-l-2 border-white/5 pl-4 ml-1">
                                            {{ member.achievements }}
                                        </p>
                                    </div>
                                </div>
                            </GlassCard>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Future batch selector/archive CTA -->
            <div class="mt-40 text-center pb-20">
                <p class="text-gray-600 italic">Want to see more of our history?</p>
                <div class="mt-6 flex justify-center gap-4">
                    <button class="px-6 py-2 border border-white/10 rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-white/5 transition-colors">
                        Batch Archive
                    </button>
                    <button class="px-6 py-2 bg-white/5 rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-white/10 transition-colors">
                        Historical Gallery
                    </button>
                </div>
            </div>
        </section>
    </GuestLayout>
</template>

<style scoped>
.scroll-reveal {
    animation: reveal 1s ease-out;
}
@keyframes reveal {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
