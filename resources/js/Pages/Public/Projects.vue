<script setup>
import { Head, Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import GlassCard from '@/Components/GlassCard.vue';

defineProps({
    projects: Array
});
</script>

<template>
    <Head title="Technical Portfolio" />

    <GuestLayout>
        <div class="pt-32 pb-20 px-4 sm:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-24 space-y-6">
                    <h1 class="text-5xl sm:text-8xl font-black text-white tracking-tighter italic uppercase underline decoration-primary-blue/30 decoration-8 underline-offset-12">Projects.</h1>
                    <p class="text-xl text-gray-500 font-medium max-w-2xl mx-auto">
                        A retrospective and prospective look into the digital tools and systems built by our elite developers.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="project in projects" :key="project.id" class="group h-full">
                        <GlassCard class="h-full flex flex-col p-8 sm:p-10 border-2 border-white/5 group-hover:border-primary-blue/30 transition-all duration-500 rounded-[2.5rem] relative overflow-hidden">
                            <div class="absolute top-0 right-0 p-8 opacity-10 group-hover:opacity-100 group-hover:text-primary-blue transition-opacity">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                            </div>

                            <div class="flex flex-wrap gap-2 mb-8 relative z-10">
                                <span v-for="tech in project.technologies" :key="tech" class="px-3 py-1 bg-white/5 border border-white/10 rounded-full text-[10px] font-black uppercase text-gray-400">
                                    {{ tech }}
                                </span>
                            </div>

                            <h3 class="text-3xl font-black text-white mb-6 group-hover:text-primary-blue transition-colors italic tracking-tight relative z-10">{{ project.title }}</h3>
                            <p class="text-gray-500 font-medium leading-relaxed mb-auto relative z-10">{{ project.description }}</p>
                            
                            <div class="pt-10 flex items-center justify-between border-t border-white/5 mt-auto relative z-10">
                                <a :href="project.link" class="text-xs font-black uppercase tracking-widest text-primary-blue hover:text-white transition-colors">Vist Source</a>
                                <div v-if="project.is_featured" class="flex items-center space-x-2">
                                    <span class="w-1.5 h-1.5 bg-accent-green rounded-full animate-ping"></span>
                                    <span class="text-[8px] font-black uppercase tracking-widest text-accent-green">Featured</span>
                                </div>
                            </div>
                        </GlassCard>
                    </div>

                    <!-- No projects -->
                    <div v-if="projects.length === 0" class="lg:col-span-3 p-20 text-center border-2 border-dashed border-white/5 rounded-[4rem]">
                        <p class="text-gray-600 font-black uppercase text-sm tracking-widest">No Projects Found in the Index</p>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
