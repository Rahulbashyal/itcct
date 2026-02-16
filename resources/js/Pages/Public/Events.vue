<script setup>
import { Head } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import GlassCard from '@/Components/GlassCard.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    upcomingEvents: Array,
    pastEvents: Array,
});

// Sample data for initial visual check
const sampleEvents = [
    { title: 'National IT Hackathon 2026', event_date: '2026-04-15 10:00:00', location: 'Main Auditorium, CCT', description: 'Collaborate and compete with the best minds across the country.', is_published: true },
    { title: 'Cyber Security Workshop', event_date: '2026-03-20 09:00:00', location: 'Lab 4, IT Dept', description: 'Hands-on training by industry experts on penetration testing.', is_published: true },
];

const currentUpcoming = props.upcomingEvents.length > 0 ? props.upcomingEvents : sampleEvents;
</script>

<template>
    <Head title="Events" />

    <GuestLayout>
        <div class="py-20 px-6 max-w-7xl mx-auto space-y-32">
            <!-- Hero / Header -->
            <div class="text-center">
                <h1 class="text-5xl md:text-7xl font-black mb-6 bg-gradient-to-r from-primary-blue to-secondary-teal bg-clip-text text-transparent">
                    Club Events
                </h1>
                <p class="text-gray-500 max-w-2xl mx-auto text-lg">
                    Discover workshops, hackathons, and seminars that define the technical pulse of our college.
                </p>
            </div>

            <!-- Upcoming Events -->
            <section>
                <div class="flex items-center space-x-4 mb-12">
                    <div class="h-[1px] flex-1 bg-white/5"></div>
                    <h2 class="text-2xl font-bold uppercase tracking-widest text-secondary-teal">Upcoming</h2>
                    <div class="h-[1px] flex-1 bg-white/5"></div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <GlassCard v-for="(event, i) in currentUpcoming" :key="i" class="group hover:scale-[1.01] transition-transform duration-500">
                        <div class="flex flex-col md:flex-row gap-8">
                            <!-- Event Date Badge -->
                            <div class="w-24 h-24 bg-primary-blue/10 border border-primary-blue/20 rounded-2xl flex flex-col items-center justify-center shrink-0">
                                <span class="text-3xl font-black text-primary-blue">{{ new Date(event.event_date).getDate() }}</span>
                                <span class="text-xs font-bold uppercase tracking-widest text-gray-500">{{ new Date(event.event_date).toLocaleString('default', { month: 'short' }) }}</span>
                            </div>

                            <div class="flex-1">
                                <h3 class="text-2xl font-bold mb-2 group-hover:text-secondary-teal transition-colors">{{ event.title }}</h3>
                                <div class="flex items-center text-sm text-gray-500 mb-4 space-x-6">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        {{ new Date(event.event_date).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                        {{ event.location }}
                                    </div>
                                </div>
                                <p class="text-gray-400 text-sm leading-relaxed mb-6">{{ event.description }}</p>
                                <PrimaryButton class="text-xs font-bold px-4 py-2">Register Now</PrimaryButton>
                            </div>
                        </div>
                    </GlassCard>
                </div>
            </section>

            <!-- Past Events Placeholder -->
            <section v-if="pastEvents?.length > 0">
                <div class="flex items-center space-x-4 mb-12">
                     <h2 class="text-2xl font-bold uppercase tracking-widest text-gray-700">Past Legacy</h2>
                     <div class="h-[1px] flex-1 bg-white/5"></div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 opacity-60 grayscale hover:grayscale-0 transition-all duration-700">
                    <div v-for="i in 3" :key="i" class="bg-white/5 border border-white/10 rounded-2xl p-6">
                        <div class="h-4 w-24 bg-white/10 rounded mb-4"></div>
                        <div class="h-6 w-48 bg-white/10 rounded mb-2"></div>
                        <div class="h-4 w-full bg-white/5 rounded"></div>
                    </div>
                </div>
            </section>
        </div>
    </GuestLayout>
</template>
