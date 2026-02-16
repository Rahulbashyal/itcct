<script setup>
import GovernanceLayout from '@/Layouts/GovernanceLayout.vue';
import { Head } from '@inertiajs/vue3';
import GlassCard from '@/Components/GlassCard.vue';

const props = defineProps({
    stats: Object
});
</script>

<template>
    <Head title="Admin Control Center" />

    <GovernanceLayout>
        <div class="space-y-12">
            <!-- Header -->
            <div>
                <h1 class="text-4xl font-black tracking-tighter text-white mb-2">Governance Dashboard</h1>
                <p class="text-gray-500 font-medium">Global oversight and digital command of the IT Club CCT ecosystem.</p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <GlassCard class="relative overflow-hidden group">
                    <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-primary-blue/10 rounded-full blur-2xl group-hover:bg-primary-blue/20 transition-colors"></div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-500 mb-2">Total Innovators</p>
                    <h3 class="text-5xl font-black text-white">{{ stats.total_users }}</h3>
                    <p class="text-[10px] text-accent-green font-bold mt-2 uppercase tracking-tighter">Registered Members</p>
                </GlassCard>

                <GlassCard class="relative overflow-hidden group">
                     <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-primary-blue/10 rounded-full blur-2xl group-hover:bg-primary-blue/20 transition-colors"></div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-500 mb-2">Live Elections</p>
                    <h3 class="text-5xl font-black text-white">{{ stats.active_elections }}</h3>
                    <p class="text-[10px] text-primary-blue font-bold mt-2 uppercase tracking-tighter">Voting Sessions</p>
                </GlassCard>

                <GlassCard class="relative overflow-hidden group">
                     <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-accent-green/10 rounded-full blur-2xl group-hover:bg-accent-green/20 transition-colors"></div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-500 mb-2">System Pulse</p>
                    <h3 class="text-3xl font-black text-white">{{ stats.system_status }}</h3>
                    <p class="text-[10px] text-accent-green font-bold mt-2 uppercase tracking-tighter">Normal Operations</p>
                </GlassCard>

                <GlassCard class="relative overflow-hidden group">
                     <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-secondary-teal/10 rounded-full blur-2xl group-hover:bg-secondary-teal/20 transition-colors"></div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-500 mb-2">Server Load</p>
                    <h3 class="text-3xl font-black text-white">0.02ms</h3>
                    <p class="text-[10px] text-gray-500 font-bold mt-2 uppercase tracking-tighter">Optimum Performance</p>
                </GlassCard>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Recent Security Logs -->
                <GlassCard class="lg:col-span-2">
                    <div class="flex items-center justify-between mb-8">
                        <h2 class="text-lg font-black uppercase tracking-widest text-white">Security Audit Trail</h2>
                        <button class="text-[10px] font-black text-gray-500 hover:text-white uppercase transition-colors">View All Logs &rarr;</button>
                    </div>

                    <div class="space-y-4">
                        <div v-for="log in stats.recent_logs" :key="log.id" class="flex items-center justify-between p-4 bg-white/[0.02] border border-white/5 rounded-2xl group hover:border-white/10 transition-all">
                            <div class="flex items-center space-x-4">
                                <div class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center text-[10px] font-black">
                                    {{ log.user ? log.user.name.charAt(0) : 'S' }}
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-white">{{ log.action }}</p>
                                    <p class="text-[10px] text-gray-500 font-medium">{{ log.user ? log.user.name : 'System' }} • {{ log.ip_address }}</p>
                                </div>
                            </div>
                            <div class="text-[10px] font-mono text-gray-600">
                                {{ new Date(log.created_at).toLocaleTimeString() }}
                            </div>
                        </div>
                    </div>
                </GlassCard>

                <!-- Quick Commands -->
                <div class="space-y-6">
                    <h2 class="text-lg font-black uppercase tracking-widest text-white px-2">Quick Commands</h2>
                    
                    <button class="w-full flex items-center p-6 bg-primary-blue/10 border border-primary-blue/20 rounded-3xl hover:bg-primary-blue/20 transition-all group">
                         <div class="w-12 h-12 rounded-2xl bg-primary-blue/20 flex items-center justify-center text-primary-blue mr-4 group-hover:scale-110 transition-transform">
                             <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                         </div>
                         <div class="text-left">
                             <p class="text-sm font-black text-white">Generate Symbols</p>
                             <p class="text-[10px] text-primary-blue font-bold uppercase tracking-tight">Bulk ID Factory</p>
                         </div>
                    </button>

                    <button class="w-full flex items-center p-6 bg-accent-green/10 border border-accent-green/20 rounded-3xl hover:bg-accent-green/20 transition-all group">
                         <div class="w-12 h-12 rounded-2xl bg-accent-green/20 flex items-center justify-center text-accent-green mr-4 group-hover:scale-110 transition-transform">
                             <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0h-3m-9-4h.01M12 11h.01M12 13h.01M12 15h.01M12 17h.01M10 11h.01M10 13h.01M10 15h.01M10 17h.01M8 11h.01M8 13h.01M8 15h.01M8 17h.01"></path></svg>
                         </div>
                         <div class="text-left">
                             <p class="text-sm font-black text-white">Financial Audit</p>
                             <p class="text-[10px] text-accent-green font-bold uppercase tracking-tight">Ledger Entry</p>
                         </div>
                    </button>

                     <button class="w-full flex items-center p-6 bg-white/[0.02] border border-white/10 rounded-3xl hover:bg-white/[0.05] transition-all group">
                         <div class="w-12 h-12 rounded-2xl bg-white/5 flex items-center justify-center text-gray-500 mr-4 group-hover:scale-110 transition-transform">
                             <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                         </div>
                         <div class="text-left">
                             <p class="text-sm font-black text-white">System Settings</p>
                             <p class="text-[10px] text-gray-500 font-bold uppercase tracking-tight">Global Configuration</p>
                         </div>
                    </button>
                </div>
            </div>
        </div>
    </GovernanceLayout>
</template>
