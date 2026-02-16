<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const isSidebarOpen = ref(true);
const isMobileMenuOpen = ref(false);
const user = usePage().props.auth.user;

// Handle window resize for responsiveness
onMounted(() => {
    if (window.innerWidth < 1024) {
        isSidebarOpen.value = false;
    }
    window.addEventListener('resize', () => {
        if (window.innerWidth < 1024) {
            isSidebarOpen.value = false;
        } else {
            isSidebarOpen.value = true;
        }
    });
});

const navItems = [
    { name: 'Dashboard', icon: 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z', route: 'dashboard' },
    { name: 'My Profile', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', route: 'profile.edit' },
    { name: 'Digital Vault', icon: 'M4 7v10c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3V7c0-2-1.5-3-3.5-3h-9C5.5 4 4 5 4 7zM9 11h6M9 15h3', route: 'vault.index' },
    { name: 'Elections', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', route: 'elections.index' },
    { name: 'Financials', icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', route: 'dashboard' },
    { name: 'Nexus Chat', icon: 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z', route: 'chat.index' },
    { name: 'Learning', icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253', route: 'learning.index' },
    { name: 'Governance', icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', route: 'admin.dashboard', adminOnly: true },
];

const filteredNavItems = computed(() => {
    return navItems.filter(item => !item.adminOnly || user.role === 'SuperAdmin');
});
</script>

<template>
    <div class="min-h-screen bg-dark-gray text-white flex overflow-hidden font-sans">
        <!-- Sidebar -->
        <aside 
            :class="[
                isSidebarOpen ? 'w-72 translate-x-0' : 'w-20 -translate-x-full lg:translate-x-0',
                isMobileMenuOpen ? 'translate-x-0 w-72' : ''
            ]" 
            class="bg-dark-gray border-r border-white/5 flex flex-col transition-all duration-300 ease-in-out fixed lg:relative z-50 h-full"
        >
            <div class="p-6 flex items-center justify-between">
                <span v-if="isSidebarOpen || isMobileMenuOpen" class="text-xl font-bold bg-gradient-to-r from-primary-blue to-secondary-teal bg-clip-text text-transparent truncate">
                    IT CLUB CCT
                </span>
                <button @click="isSidebarOpen = !isSidebarOpen; isMobileMenuOpen = false" class="p-2 hover:bg-white/5 rounded-lg text-gray-400 hidden lg:block">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <button @click="isMobileMenuOpen = false" class="lg:hidden p-2 text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto custom-scrollbar">
                <Link 
                    v-for="item in filteredNavItems" 
                    :key="item.name"
                    :href="route(item.route)"
                    @click="isMobileMenuOpen = false"
                    class="flex items-center p-3 rounded-xl transition-all group"
                    :class="route().current(item.route) ? 'bg-primary-blue text-white shadow-lg shadow-primary-blue/20' : 'text-gray-400 hover:bg-white/5 hover:text-white'"
                >
                    <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"></path>
                    </svg>
                    <span v-if="isSidebarOpen || isMobileMenuOpen" class="ml-4 font-medium">{{ item.name }}</span>
                </Link>
            </nav>

            <div class="p-4 border-t border-white/5">
                <Link 
                    :href="route('logout')" 
                    method="post" 
                    as="button"
                    class="w-full flex items-center p-3 text-red-400 hover:bg-red-400/10 rounded-xl transition-colors group"
                >
                    <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    <span v-if="isSidebarOpen || isMobileMenuOpen" class="ml-4 font-medium">Logout</span>
                </Link>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden relative">
            <!-- Mobile Overlay -->
            <div 
                v-if="isMobileMenuOpen" 
                @click="isMobileMenuOpen = false"
                class="absolute inset-0 bg-black/50 backdrop-blur-sm z-40 lg:hidden"
            ></div>

            <!-- Top Header -->
            <header class="h-20 border-b border-white/5 flex items-center justify-between px-4 lg:px-8 shrink-0 bg-dark-gray/50 backdrop-blur-md z-30">
                <div class="flex items-center space-x-4">
                    <button @click="isMobileMenuOpen = true" class="lg:hidden p-2 text-gray-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16"></path></svg>
                    </button>
                    <h2 class="text-xs lg:text-lg font-medium text-gray-400 truncate">
                        <slot name="header_breadcrumb" />
                    </h2>
                </div>
                
                <div class="flex items-center space-x-6">
                    <div class="flex flex-col items-end hidden sm:flex">
                        <span class="text-sm font-semibold tracking-wide">{{ $page.props.auth.user.name }}</span>
                        <span class="text-[10px] uppercase font-bold text-secondary-teal tracking-widest px-2 py-0.5 bg-secondary-teal/10 rounded-full border border-secondary-teal/20">
                            {{ $page.props.auth.user.role }}
                        </span>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-blue to-secondary-teal p-[1px]">
                        <div class="w-full h-full rounded-xl bg-dark-gray flex items-center justify-center font-bold text-sm uppercase italic">
                            {{ $page.props.auth.user.name.charAt(0) }}
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-4 lg:p-8 custom-scrollbar">
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.1);
}
</style>
