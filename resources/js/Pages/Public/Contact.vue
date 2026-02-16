<script setup>
import { Head } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import GlassCard from '@/Components/GlassCard.vue';
</script>

<template>
    <Head title="Connect with Nexus" />

    <GuestLayout>
        <div class="pt-32 pb-20 px-4 sm:px-8">
            <div class="max-w-7xl mx-auto shadow-sm">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-20">
                    <!-- Text Side -->
                    <div class="space-y-12">
                        <div class="space-y-6">
                            <h1 class="text-5xl sm:text-8xl font-black text-white tracking-tighter italic">DIRECT <span class="text-secondary-teal">LINK.</span></h1>
                            <p class="text-xl text-gray-500 font-medium max-w-lg">
                                Have a project ideation? Want to collaborate? Or need system support? Reach out to the IT Club executive council.
                            </p>
                        </div>

                        <div class="space-y-8">
                            <div class="flex items-center space-x-6 group">
                                <div class="w-14 h-14 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-primary-blue group-hover:bg-primary-blue group-hover:text-white transition-all">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-gray-600 uppercase tracking-widest">Protocol Email</p>
                                    <p class="text-xl font-black text-white tracking-tight">nexus@itclubcct.com</p>
                                </div>
                            </div>

                            <div class="flex items-center space-x-6 group">
                                <div class="w-14 h-14 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-secondary-teal group-hover:bg-secondary-teal group-hover:text-white transition-all">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-gray-600 uppercase tracking-widest">Command Center</p>
                                    <p class="text-xl font-black text-white tracking-tight">CCT Block A, 4th Floor</p>
                                </div>
                            </div>
                        </div>

                        <div class="pt-8 flex space-x-6">
                            <a href="#" class="text-gray-500 hover:text-white font-black uppercase text-xs tracking-widest">Facebook</a>
                            <a href="#" class="text-gray-500 hover:text-white font-black uppercase text-xs tracking-widest">LinkedIn</a>
                            <a href="#" class="text-gray-500 hover:text-white font-black uppercase text-xs tracking-widest">GitHub</a>
                        </div>
                    </div>

                    <!-- Form Side -->
                    <GlassCard class="p-8 sm:p-12">
                        <form @submit.prevent class="space-y-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-2">Operator Name</label>
                                    <input type="text" placeholder="Your full name" class="w-full bg-white/[0.03] border border-white/10 rounded-2xl px-6 py-4 text-white placeholder-gray-600 focus:outline-none focus:border-primary-blue transition-all" />
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-2">Digital ID / Symbol</label>
                                    <input type="text" placeholder="Symbol Number" class="w-full bg-white/[0.03] border border-white/10 rounded-2xl px-6 py-4 text-white placeholder-gray-600 focus:outline-none focus:border-primary-blue transition-all" />
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-2">Message Payload</label>
                                <textarea rows="4" placeholder="Transmission content..." class="w-full bg-white/[0.03] border border-white/10 rounded-2xl px-6 py-4 text-white placeholder-gray-600 focus:outline-none focus:border-primary-blue transition-all resize-none"></textarea>
                            </div>
                            <PrimaryButton class="w-full h-16 text-lg">Send Transmission</PrimaryButton>
                        </form>
                    </GlassCard>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
