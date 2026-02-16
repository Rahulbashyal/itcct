<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage, Link } from '@inertiajs/vue3';
import GlassCard from '@/Components/GlassCard.vue';
import { computed } from 'vue';

const user = usePage().props.auth.user;

const dashboardTitle = computed(() => {
    switch (user.role) {
        case 'President': return 'Executive Command';
        case 'Secretary': return 'Administrative Nexus';
        case 'Treasurer': return 'Financial Vault';
        case 'SuperAdmin': return 'Global Override';
        default: return 'Innovation Dashboard';
    }
});

const stats = computed(() => {
    let items = [
        { label: 'System Pulse', value: 'OPTIMAL', color: 'text-accent-green' },
        { label: 'Active Sessions', value: '128', color: 'text-primary-blue' },
    ];

    if (user.role === 'Treasurer' || user.role === 'President') {
        items.push({ label: 'Treasury Balance', value: 'रू 45,600', color: 'text-accent-green' });
    }

    if (user.role === 'Secretary' || user.role === 'President') {
        items.push({ label: 'Pending Requests', value: '5', color: 'text-secondary-teal' });
    }

    return items;
});

const quickActions = computed(() => {
    let actions = [];
    if (user.role === 'SuperAdmin' || user.role === 'President') {
        actions.push({ name: 'Manage Innovations', route: 'admin.dashboard', icon: 'M13 10V3L4 14h7v7l9-11h-7z' });
    }
    actions.push({ name: 'Broadcast Message', route: 'chat.index', icon: 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z' });
    actions.push({ name: 'Member Directory', route: 'dashboard', icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197' });
    return actions;
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header_breadcrumb>Core System / {{ dashboardTitle }}</template>

        <div class="space-y-12">
            <!-- Hero Greeting -->
            <div class="relative overflow-hidden p-12 bg-white/[0.02] border border-white/5 rounded-[3rem] group">
                <div class="absolute top-0 right-0 w-64 h-64 bg-primary-blue/10 rounded-full blur-[100px] -mr-32 -mt-32"></div>
                <div class="relative z-10">
                    <h1 class="text-5xl font-black tracking-tighter text-white mb-2">Welcome Back, <span class="bg-gradient-to-r from-primary-blue to-secondary-teal bg-clip-text text-transparent">{{ user.name }}</span></h1>
                    <p class="text-gray-500 font-medium">Logged in as <span class="text-white font-bold uppercase tracking-widest text-[10px]">{{ user.role }}</span></p>
                </div>
            </div>

            <!-- Role-Specific Global Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <GlassCard v-for="stat in stats" :key="stat.label">
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-500 mb-2">{{ stat.label }}</p>
                    <h3 class="text-3xl font-black text-white" :class="stat.color">{{ stat.value }}</h3>
                </GlassCard>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Feed Area -->
                <div class="lg:col-span-2 space-y-8">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-black uppercase tracking-widest text-white">System Feed</h2>
                        <div class="h-[1px] flex-1 bg-white/5 mx-6"></div>
                    </div>

                    <!-- Placeholder for dynamic activities -->
                    <div class="space-y-4">
                        <div v-for="i in 3" :key="i" class="p-6 bg-white/[0.01] border border-white/5 rounded-3xl flex items-center justify-between group hover:border-white/10 transition-all">
                            <div class="flex items-center space-x-6">
                                <div class="w-12 h-12 rounded-2xl bg-white/5 flex items-center justify-center text-primary-blue">
                                     <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-300">New system update propagated successfully.</p>
                                    <p class="text-[10px] text-gray-600 font-bold uppercase tracking-widest">2 hours ago • Global Dispatch</p>
                                </div>
                            </div>
                            <button class="text-[10px] font-black text-gray-600 uppercase tracking-widest group-hover:text-white transition-colors">Acknowledge</button>
                        </div>
                    </div>
                </div>

                <!-- Quick Operations Sidebar -->
                <div class="space-y-8">
                    <h2 class="text-xl font-black uppercase tracking-widest text-white px-2">Operations</h2>
                    <div class="space-y-4">
                        <Link v-for="action in quickActions" :key="action.name" :href="route(action.route)" class="w-full flex items-center p-6 bg-white/[0.02] border border-white/5 rounded-3xl hover:bg-white/[0.05] hover:border-white/10 transition-all group">
                            <div class="w-12 h-12 rounded-2xl bg-white/5 flex items-center justify-center text-gray-500 mr-4 group-hover:scale-110 group-hover:bg-primary-blue/20 group-hover:text-primary-blue transition-all">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="action.icon"></path>
                                </svg>
                            </div>
                            <div class="text-left">
                                <p class="text-sm font-black text-white">{{ action.name }}</p>
                                <p class="text-[10px] text-gray-600 font-bold uppercase tracking-widest">Execute Protocol</p>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
