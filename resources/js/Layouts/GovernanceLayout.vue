<script setup>
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const isSidebarOpen = ref(true);
const user = usePage().props.auth.user;

const navItems = [
    { name: 'Control Center', icon: 'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z', route: 'admin.dashboard' },
    { name: 'User Management', icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197', route: 'admin.users.index' },
    { name: 'Election Command', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', route: 'admin.elections.index' },
    { name: 'Financial Ledger', icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', route: 'admin.finance.index' },
    { name: 'News Feed', icon: 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10l4 4v10a2 2 0 01-2 2zM7 8h5m-5 4h5m-5 4h10', route: 'admin.news.index' },
    { name: 'Event Manager', icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', route: 'admin.events.index' },
    { name: 'Back to Site', icon: 'M10 19l-7-7m0 0l7-7m-7 7h18', route: 'dashboard' },
];
</script>

<template>
    <div class="min-h-screen bg-[#0a0b10] text-gray-200 flex">
        <!-- Admin Sidebar -->
        <aside 
            class="bg-[#111218] border-r border-white/5 transition-all duration-500 flex flex-col z-50 fixed inset-y-0 left-0"
            :class="isSidebarOpen ? 'w-72' : 'w-20'"
        >
            <!-- Logo Area -->
            <div class="h-20 flex items-center px-6 border-b border-white/5 mb-6">
                <div class="w-10 h-10 bg-primary-blue rounded-xl flex items-center justify-center font-black text-white shrink-0">G</div>
                <span v-if="isSidebarOpen" class="ml-4 font-black tracking-widest text-sm uppercase text-white animate-in fade-in duration-500">Governance</span>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-4 space-y-2">
                <Link 
                    v-for="item in navItems" 
                    :key="item.name"
                    :href="item.route.includes('.') ? route(item.route) : '#'"
                    class="flex items-center h-12 px-4 rounded-xl transition-all group overflow-hidden"
                    :class="route().current(item.route) ? 'bg-primary-blue/20 text-primary-blue' : 'hover:bg-white/5 text-gray-500 hover:text-white'"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon"></path>
                    </svg>
                    <span v-if="isSidebarOpen" class="ml-4 text-xs font-bold uppercase tracking-widest whitespace-nowrap">{{ item.name }}</span>
                    
                    <div v-if="!isSidebarOpen" class="absolute left-16 bg-white text-dark-gray text-[10px] font-bold px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-50">
                        {{ item.name }}
                    </div>
                </Link>
            </nav>

            <!-- User Info -->
            <div class="p-6 border-t border-white/5 bg-white/[0.02]">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-xl bg-primary-blue/20 flex items-center justify-center font-bold text-primary-blue uppercase italic">
                        {{ user.name.charAt(0) }}
                    </div>
                    <div v-if="isSidebarOpen" class="ml-4 overflow-hidden">
                        <p class="text-xs font-black text-white truncate">{{ user.name }}</p>
                        <p class="text-[10px] text-primary-blue font-bold uppercase tracking-tight">SuperAdmin</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main 
            class="flex-1 transition-all duration-500 min-h-screen"
            :class="isSidebarOpen ? 'ml-72' : 'ml-20'"
        >
            <!-- Topbar -->
            <header class="h-20 border-b border-white/5 bg-[#0a0b10]/80 backdrop-blur-xl sticky top-0 z-40 flex items-center justify-between px-8">
                <button @click="isSidebarOpen = !isSidebarOpen" class="p-2 hover:bg-white/5 rounded-xl transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16"></path></svg>
                </button>

                <div class="flex items-center space-x-6">
                    <div class="text-[10px] font-bold uppercase tracking-[0.2em] text-accent-green bg-accent-green/10 px-3 py-1 rounded-full border border-accent-green/20">
                        Live System • Encryption Encoded
                    </div>
                    <Link :href="route('logout')" method="post" as="button" class="text-xs font-black text-gray-500 hover:text-white uppercase transition-colors">
                        Secure Logoff
                    </Link>
                </div>
            </header>

            <div class="p-8">
                <slot />
            </div>
        </main>
    </div>
</template>
