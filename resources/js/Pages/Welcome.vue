<script setup>
import { Head, Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

defineProps({
    stats: Object,
    features: Array,
    featuredProjects: Array,
    upcomingEvents: Array,
});
</script>

<template>
    <Head title="IT Club CCT — Home" />

    <GuestLayout>
        <!-- Hero Section -->
        <section class="relative min-h-[85vh] flex items-center">
            <!-- Background -->
            <div class="absolute inset-0 z-0">
                <img src="/images/hero-gen.png" alt="Hero" class="w-full h-full object-cover opacity-30" />
                <div class="absolute inset-0 bg-gradient-to-r from-[#090910]/95 to-[#090910]/70"></div>
            </div>

            <div class="max-w-7xl mx-auto px-6 py-20 relative z-10 w-full">
                <div class="max-w-3xl">
                    <h1 class="text-5xl sm:text-6xl font-bold text-white mb-6 leading-tight">
                        IT Club of <span class="text-blue-500">CCT</span>
                    </h1>
                    
                    <p class="text-xl text-gray-300 mb-8 leading-relaxed">
                        The official technical organization at Crimson College of Technology. 
                        We bring together students passionate about technology, innovation, and leadership.
                    </p>

                    <div class="flex flex-wrap gap-4">
                        <Link :href="route('login')">
                            <PrimaryButton class="h-12 px-8">
                                Member Login
                            </PrimaryButton>
                        </Link>
                        <Link :href="route('about')">
                            <button class="h-12 px-8 rounded-lg border border-white/30 bg-white/10 text-white font-medium hover:bg-white/20 transition-all">
                                Learn More
                            </button>
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Mission Statement -->
        <section class="py-16 px-6 bg-blue-600/5">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-2xl font-semibold text-white mb-6">Our Mission</h2>
                <p class="text-lg text-gray-300 leading-relaxed">
                    "To empower students with technical skills, foster innovation, and build leaders through 
                    collaborative learning experiences while maintaining transparency and democratic governance."
                </p>
            </div>
        </section>

        <!-- About Section -->
        <section class="py-20 px-6">
            <div class="max-w-7xl mx-auto">
                <div class="grid md:grid-cols-2 gap-16 items-center">
                    <div>
                        <h2 class="text-3xl font-bold text-white mb-6">
                            About IT Club CCT
                        </h2>
                        <div class="space-y-4 text-gray-400 leading-relaxed">
                            <p>
                                Founded in 2021, IT Club CCT is a student-led organization dedicated to fostering 
                                technical excellence and leadership among students at Crimson College of Technology.
                            </p>
                            <p>
                                We organize workshops, hackathons, technical events, and provide platforms for 
                                students to learn, collaborate, and innovate.
                            </p>
                            <p>
                                Our mission is to prepare students for the real world by providing hands-on 
                                experience in software development, digital governance, and community building.
                            </p>
                        </div>
                        <Link :href="route('about')" class="inline-flex items-center text-blue-500 font-medium mt-6 hover:text-white transition-colors">
                            Read more about us
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </Link>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-white/[0.03] border border-white/10 rounded-xl p-6 text-center">
                            <div class="text-3xl font-bold text-blue-500 mb-2">{{ stats?.years_active || 5 }}+</div>
                            <div class="text-sm text-gray-500">Years Active</div>
                        </div>
                        <div class="bg-white/[0.03] border border-white/10 rounded-xl p-6 text-center">
                            <div class="text-3xl font-bold text-blue-500 mb-2">{{ stats?.members || 50 }}+</div>
                            <div class="text-sm text-gray-500">Members</div>
                        </div>
                        <div class="bg-white/[0.03] border border-white/10 rounded-xl p-6 text-center">
                            <div class="text-3xl font-bold text-blue-500 mb-2">{{ stats?.events || 30 }}+</div>
                            <div class="text-sm text-gray-500">Events</div>
                        </div>
                        <div class="bg-white/[0.03] border border-white/10 rounded-xl p-6 text-center">
                            <div class="text-3xl font-bold text-blue-500 mb-2">{{ stats?.projects || 20 }}+</div>
                            <div class="text-sm text-gray-500">Projects</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Why Join / Benefits -->
        <section class="py-20 px-6 bg-white/[0.02]">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold text-white mb-4">Why Join IT Club CCT?</h2>
                    <p class="text-gray-500 max-w-2xl mx-auto">
                        Become part of something bigger. Here's what our members get:
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <div class="bg-white/[0.03] border border-white/10 rounded-xl p-8 text-center">
                        <div class="w-16 h-16 rounded-full bg-blue-500/10 flex items-center justify-center mx-auto mb-6">
                            <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-3">Learn New Skills</h3>
                        <p class="text-gray-500">
                            Access workshops, tutorials, and hands-on training in programming, web development, and more.
                        </p>
                    </div>

                    <div class="bg-white/[0.03] border border-white/10 rounded-xl p-8 text-center">
                        <div class="w-16 h-16 rounded-full bg-teal-500/10 flex items-center justify-center mx-auto mb-6">
                            <svg class="w-8 h-8 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-3">Network & Connect</h3>
                        <p class="text-gray-500">
                            Meet like-minded students, industry professionals, and build lasting professional relationships.
                        </p>
                    </div>

                    <div class="bg-white/[0.03] border border-white/10 rounded-xl p-8 text-center">
                        <div class="w-16 h-16 rounded-full bg-purple-500/10 flex items-center justify-center mx-auto mb-6">
                            <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-3">Leadership Opportunities</h3>
                        <p class="text-gray-500">
                            Develop leadership skills through organizing events and serving in executive roles.
                        </p>
                    </div>

                    <div class="bg-white/[0.03] border border-white/10 rounded-xl p-8 text-center">
                        <div class="w-16 h-16 rounded-full bg-green-500/10 flex items-center justify-center mx-auto mb-6">
                            <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-3">Build Projects</h3>
                        <p class="text-gray-500">
                            Collaborate on real-world projects and build your portfolio with tangible work.
                        </p>
                    </div>

                    <div class="bg-white/[0.03] border border-white/10 rounded-xl p-8 text-center">
                        <div class="w-16 h-16 rounded-full bg-yellow-500/10 flex items-center justify-center mx-auto mb-6">
                            <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-3">Industry Exposure</h3>
                        <p class="text-gray-500">
                            Connect with industry experts through tech talks, workshops, and internship opportunities.
                        </p>
                    </div>

                    <div class="bg-white/[0.03] border border-white/10 rounded-xl p-8 text-center">
                        <div class="w-16 h-16 rounded-full bg-pink-500/10 flex items-center justify-center mx-auto mb-6">
                            <svg class="w-8 h-8 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-3">Innovation & Fun</h3>
                        <p class="text-gray-500">
                            Participate in hackathons, tech challenges, and fun tech-related activities.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- What We Do Section -->
        <section class="py-20 px-6">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold text-white mb-4">What We Do</h2>
                    <p class="text-gray-500 max-w-2xl mx-auto">
                        We provide various opportunities for students to learn, grow, and showcase their technical skills.
                    </p>
                </div>

                <div class="grid md:grid-cols-4 gap-6">
                    <div class="bg-white/[0.03] border border-white/10 rounded-xl p-6 hover:border-blue-500/30 transition-colors">
                        <div class="w-12 h-12 rounded-xl bg-blue-500/10 flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <h3 class="font-semibold text-white mb-2">Digital Governance</h3>
                        <p class="text-sm text-gray-500">Transparent elections & democratic processes</p>
                    </div>

                    <div class="bg-white/[0.03] border border-white/10 rounded-xl p-6 hover:border-teal-500/30 transition-colors">
                        <div class="w-12 h-12 rounded-xl bg-teal-500/10 flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h3 class="font-semibold text-white mb-2">Technical Events</h3>
                        <p class="text-sm text-gray-500">Workshops, hackathons & tech talks</p>
                    </div>

                    <div class="bg-white/[0.03] border border-white/10 rounded-xl p-6 hover:border-purple-500/30 transition-colors">
                        <div class="w-12 h-12 rounded-xl bg-purple-500/10 flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                        <h3 class="font-semibold text-white mb-2">Learning Hub</h3>
                        <p class="text-sm text-gray-500">Tutorials & coding resources</p>
                    </div>

                    <div class="bg-white/[0.03] border border-white/10 rounded-xl p-6 hover:border-green-500/30 transition-colors">
                        <div class="w-12 h-12 rounded-xl bg-green-500/10 flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                            </svg>
                        </div>
                        <h3 class="font-semibold text-white mb-2">Projects</h3>
                        <p class="text-sm text-gray-500">Real-world applications</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Upcoming Events -->
        <section v-if="upcomingEvents && upcomingEvents.length" class="py-20 px-6 bg-white/[0.02]">
            <div class="max-w-7xl mx-auto">
                <div class="flex justify-between items-center mb-12">
                    <div>
                        <h2 class="text-3xl font-bold text-white mb-2">Upcoming Events</h2>
                        <p class="text-gray-500">Don't miss our upcoming activities</p>
                    </div>
                    <Link :href="route('events')" class="text-blue-500 hover:text-white font-medium transition-colors">
                        View all events →
                    </Link>
                </div>

                <div class="grid md:grid-cols-3 gap-6">
                    <div 
                        v-for="event in upcomingEvents" 
                        :key="event.id"
                        class="bg-white/[0.03] border border-white/10 rounded-xl overflow-hidden hover:border-blue-500/30 transition-colors"
                    >
                        <div class="h-36 bg-gradient-to-br from-blue-600/20 to-teal-600/20 flex items-center justify-center">
                            <svg class="w-16 h-16 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="p-6">
                            <div class="text-sm text-blue-500 font-medium mb-2">
                                {{ new Date(event.event_date).toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' }) }}
                            </div>
                            <h3 class="text-lg font-semibold text-white mb-2">{{ event.title }}</h3>
                            <p class="text-sm text-gray-500 line-clamp-2">{{ event.description || 'Join us for this exciting event.' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Quick Links -->
        <section class="py-20 px-6">
            <div class="max-w-7xl mx-auto">
                <div class="grid md:grid-cols-4 gap-6">
                    <Link :href="route('election-portal')" class="bg-white/[0.03] border border-white/10 rounded-xl p-6 hover:border-blue-500/30 transition-colors text-center group">
                        <svg class="w-10 h-10 text-blue-500 mx-auto mb-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="font-semibold text-white">Elections</h3>
                        <p class="text-sm text-gray-500 mt-1">Vote securely</p>
                    </Link>

                    <Link :href="route('transparency')" class="bg-white/[0.03] border border-white/10 rounded-xl p-6 hover:border-teal-500/30 transition-colors text-center group">
                        <svg class="w-10 h-10 text-teal-500 mx-auto mb-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="font-semibold text-white">Transparency</h3>
                        <p class="text-sm text-gray-500 mt-1">View finances</p>
                    </Link>

                    <Link :href="route('hall-of-fame')" class="bg-white/[0.03] border border-white/10 rounded-xl p-6 hover:border-purple-500/30 transition-colors text-center group">
                        <svg class="w-10 h-10 text-purple-500 mx-auto mb-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                        </svg>
                        <h3 class="font-semibold text-white">Hall of Fame</h3>
                        <p class="text-sm text-gray-500 mt-1">Our alumni</p>
                    </Link>

                    <Link :href="route('contact')" class="bg-white/[0.03] border border-white/10 rounded-xl p-6 hover:border-green-500/30 transition-colors text-center group">
                        <svg class="w-10 h-10 text-green-500 mx-auto mb-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <h3 class="font-semibold text-white">Contact</h3>
                        <p class="text-sm text-gray-500 mt-1">Get in touch</p>
                    </Link>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 px-6 bg-blue-600/10">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-3xl font-bold text-white mb-4">
                    Join IT Club CCT Today
                </h2>
                <p class="text-gray-400 max-w-xl mx-auto mb-8">
                    Be part of a community that nurtures technical skills and leadership. 
                    Connect, learn, and grow with fellow tech enthusiasts.
                </p>
                <Link :href="route('login')">
                    <PrimaryButton class="h-12 px-10">
                        Become a Member
                    </PrimaryButton>
                </Link>
            </div>
        </section>
    </GuestLayout>
</template>
