<script setup>
import { Head, Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import GlassCard from '@/Components/GlassCard.vue';

const props = defineProps({
    news: Array
});

const sampleNews = [
    { 
        title: 'New Digital Ecosystem Launched', 
        category: 'Announcement', 
        created_at: '2026-02-13T10:00:00Z', 
        author: { name: 'SuperAdmin' },
        content: 'We are thrilled to announce the official launch of the IT Club CCT digital platform. This marks a new era of transparency and governance...'
    },
    { 
        title: 'Call for Hackathon Volunteers', 
        category: 'Events', 
        created_at: '2026-02-11T14:30:00Z', 
        author: { name: 'Event Lead' },
        content: 'We are looking for enthusiastic volunteers to help us organize the upcoming National IT Hackathon. Gain experience and certificates!'
    },
];

const displayNews = props.news.length > 0 ? props.news : sampleNews;
</script>

<template>
    <Head title="News & Updates" />

    <GuestLayout>
        <div class="py-20 px-6 max-w-5xl mx-auto">
            <div class="mb-20 text-center">
                <h1 class="text-5xl font-black mb-4 tracking-tight">The <span class="text-primary-blue">Pulse</span> of CCT</h1>
                <p class="text-gray-500">Official news, announcements, and developer logs from the IT Club.</p>
            </div>

            <div class="space-y-12">
                <article v-for="(item, i) in displayNews" :key="i" class="group">
                    <div class="flex flex-col md:flex-row gap-8 items-start">
                        <div class="md:w-32 shrink-0 pt-2">
                            <div class="text-sm font-bold text-gray-600 uppercase tracking-widest">
                                {{ new Date(item.created_at).toLocaleDateString([], { month: 'short', day: 'numeric' }) }}
                            </div>
                            <div class="text-[10px] text-gray-700 font-bold mt-1">{{ new Date(item.created_at).getFullYear() }}</div>
                        </div>

                        <div class="flex-1">
                            <span class="text-[10px] font-bold text-secondary-teal bg-secondary-teal/10 px-2 py-0.5 rounded-full mb-4 inline-block tracking-widest uppercase">
                                {{ item.category }}
                            </span>
                            <h2 class="text-3xl font-bold mb-4 group-hover:text-primary-blue transition-colors cursor-pointer">{{ item.title }}</h2>
                            <p class="text-gray-400 leading-relaxed mb-6 line-clamp-3">
                                {{ item.content }}
                            </p>
                            <div class="flex items-center justify-between pt-6 border-t border-white/5">
                                <div class="flex items-center space-x-3">
                                    <div class="w-6 h-6 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-[8px] font-bold">
                                        {{ item.author.name.charAt(0) }}
                                    </div>
                                    <span class="text-xs text-gray-500 italic">Posted by {{ item.author.name }}</span>
                                </div>
                                <button class="text-xs font-bold uppercase tracking-widest text-primary-blue hover:text-white transition-colors">
                                    Read Full Story &rarr;
                                </button>
                            </div>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </GuestLayout>
</template>
