<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const notifications = ref([]);
const loading = ref(true);

const fetchNotifications = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/v1/nexus/notifications');
        notifications.value = response.data.data || [];
    } catch (error) {
        console.error('Failed to fetch notifications:', error);
    } finally {
        loading.value = false;
    }
};

const markAsRead = async (id) => {
    try {
        await axios.post(`/api/v1/nexus/notifications/${id}/read`);
        const item = notifications.value.find(n => n.id === id);
        if (item) item.read_at = new Date();
    } catch (error) {
        console.error('Failed to mark as read:', error);
    }
};

onMounted(fetchNotifications);
</script>

<template>
    <div class="notifications-view h-full bg-slate-50 overflow-y-auto p-4">
        <div class="max-w-2xl mx-auto">
            <h2 class="text-2xl font-bold text-slate-900 mb-6 flex items-center gap-3">
                <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                Notifications
            </h2>

            <div v-if="loading" class="flex justify-center py-20">
                <div class="flex gap-1">
                    <div class="w-3 h-3 bg-indigo-600 rounded-full animate-bounce"></div>
                    <div class="w-3 h-3 bg-indigo-600 rounded-full animate-bounce [animation-delay:-0.15s]"></div>
                    <div class="w-3 h-3 bg-indigo-600 rounded-full animate-bounce [animation-delay:-0.3s]"></div>
                </div>
            </div>

            <div v-else-if="notifications.length === 0" class="bg-white rounded-3xl p-16 text-center border border-slate-200 shadow-sm">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                </div>
                <p class="text-slate-500 font-black uppercase tracking-widest text-xs mb-1">Status: Clear</p>
                <p class="text-slate-400 text-sm">No new notifications for you right now.</p>
            </div>

            <div v-else class="space-y-3">
                <div 
                    v-for="n in notifications" 
                    :key="n.id" 
                    class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex items-start gap-4 transition-all"
                    :class="{ 'opacity-60 grayscale-[0.5]': n.read_at }"
                >
                    <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center shrink-0">
                        <svg v-if="n.type.includes('Like')" class="w-5 h-5 text-rose-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" /></svg>
                        <svg v-else class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-slate-800">
                            <span class="font-bold">{{ n.data.user_name }}</span> 
                            {{ n.data.message }}
                        </p>
                        <p class="text-xs text-slate-400 mt-1">{{ new Date(n.created_at).toLocaleString() }}</p>
                    </div>
                    <button 
                        v-if="!n.read_at" 
                        @click="markAsRead(n.id)"
                        class="text-xs text-indigo-600 font-bold hover:underline"
                    >
                        Mark as read
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
