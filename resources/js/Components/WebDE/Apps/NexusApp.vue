<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import NexusFeed from './Nexus/NexusFeed.vue';
import ChatApp from './ChatApp.vue';
import UserManApp from './UserManApp.vue';
import NewsApp from './NewsApp.vue';
import FacebookFeed from './Nexus/FacebookFeed.vue';
import NexusNotifications from './Nexus/NexusNotifications.vue';

const props = defineProps({
    user: Object
});

const page = usePage();
const activeTab = ref(localStorage.getItem('nexus_active_tab') || 'feed');

// Persist tab change
const changeTab = (id) => {
    activeTab.value = id;
    localStorage.setItem('nexus_active_tab', id);
};

// Social media tabs
const tabs = computed(() => {
    const baseTabs = [
        { id: 'feed', name: 'Feed', component: NexusFeed },
        { id: 'notifications', name: 'Activity', component: NexusNotifications },
        { id: 'announcements', name: 'Alerts', component: NewsApp },
        { id: 'chat', name: 'Messages', component: ChatApp, badge: 3 },
        { id: 'facebook', name: 'CCT FB', component: FacebookFeed },
    ];

    if (props.user?.role === 'SuperAdmin' || props.user?.role === 'Secretary') {
        baseTabs.push({ id: 'community', name: 'Members', component: UserManApp });
    }

    return baseTabs;
});

const activeComponent = computed(() => {
    const tab = tabs.value.find(t => t.id === activeTab.value);
    return tab?.component || NexusFeed;
});
</script>

<template>
    <div class="nexus-shell flex flex-col h-full bg-white text-gray-900">
        <!-- Top Navigation Bar (High-Fidelity) -->
        <div class="h-20 bg-white border-b border-slate-200 flex items-center justify-between px-6 shrink-0 shadow-sm z-30">
            <!-- Logo Section -->
            <div class="flex items-center gap-4">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-indigo-600 to-purple-600 flex items-center justify-center shadow-xl shadow-indigo-100 transform transition-all duration-500 hover:rotate-6">
                    <img src="/images/icons/nexus.png" class="w-7 h-7 object-contain brightness-0 invert" alt="Nexus">
                </div>
                <div class="flex flex-col">
                    <span class="text-sm font-black uppercase tracking-[0.2em] text-slate-800 leading-none">Nexus</span>
                    <span class="text-[8px] font-black uppercase tracking-[0.3em] text-indigo-500 mt-1">Prime Console</span>
                </div>
            </div>

            <!-- Center Navigation -->
            <nav class="flex items-center gap-8 h-full">
                <button 
                    v-for="tab in tabs" 
                    :key="tab.id"
                    @click="changeTab(tab.id)"
                    class="relative h-full flex flex-col items-center justify-center transition-all duration-300 group"
                    :class="activeTab === tab.id 
                        ? 'text-indigo-600' 
                        : 'text-slate-400 hover:text-slate-600'"
                >
                    <div class="mb-1.5 transform transition-transform duration-300 group-hover:-translate-y-0.5" :class="activeTab === tab.id ? 'scale-110' : ''">
                        <svg v-if="tab.id === 'feed'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                        <svg v-else-if="tab.id === 'notifications'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                        <svg v-else-if="tab.id === 'announcements'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                        <svg v-else-if="tab.id === 'chat'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                        <svg v-else-if="tab.id === 'facebook'" class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        <svg v-else-if="tab.id === 'community'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    </div>
                    <span class="text-[9px] font-black uppercase tracking-[0.2em] text-center">{{ tab.name }}</span>
                    
                    <!-- Active indicator -->
                    <div 
                        v-if="activeTab === tab.id" 
                        class="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-600 shadow-[0_-2px_10px_rgba(79,70,229,0.5)]"
                    ></div>
                    
                    <!-- Badge -->
                    <div 
                        v-if="tab.badge" 
                        class="absolute top-3 -right-1.5 px-1.5 py-0.5 bg-pink-500 text-white text-[8px] rounded-full flex items-center justify-center font-black shadow-lg border border-white"
                    >
                        {{ tab.badge }}
                    </div>
                </button>
            </nav>

            <!-- User Profile -->
            <div class="flex items-center gap-3">
                <img 
                    :src="'https://ui-avatars.com/api/?name=' + (user?.name || 'User') + '&background=4F46E5&color=fff&rounded=true'" 
                    class="w-9 h-9 rounded-full border-2 border-gray-200 cursor-pointer hover:border-blue-500 transition-colors"
                    :title="user?.name"
                >
            </div>
        </div>

        <!-- Main Content Area -->
        <main class="flex-1 bg-gray-50 overflow-hidden">
            <Transition name="fade" mode="out-in">
                <component 
                    :is="activeComponent" 
                    :key="activeTab"
                    :user="user"
                    :isEmbedded="true"
                    class="nexus-content h-full"
                />
            </Transition>
        </main>
    </div>
</template>

<style scoped>
.nexus-shell {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.nexus-content {
    height: 100%;
    width: 100%;
}

/* Override child app styles for social media theme */
:deep(.news-app),
:deep(.events-app),
:deep(.nexus-feed) {
    background: #f3f4f6 !important;
}

:deep(main) {
    padding: 0 !important;
}
</style>
