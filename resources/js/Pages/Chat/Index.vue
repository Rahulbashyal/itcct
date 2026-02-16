<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, onMounted, nextTick } from 'vue';

const props = defineProps({
    initialMessages: Array
});

const user = usePage().props.auth.user;
const messagesContainer = ref(null);

const form = useForm({
    content: ''
});

const scrollToBottom = () => {
    if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
};

onMounted(() => {
    scrollToBottom();
});

const submit = () => {
    if (!form.content.trim()) return;
    
    form.post(route('chat.store'), {
        onSuccess: () => {
            form.reset();
            nextTick(() => scrollToBottom());
        },
        preserveScroll: true
    });
};
</script>

<template>
    <Head title="Nexus Chat" />

    <AuthenticatedLayout>
        <template #header_breadcrumb>Digital Nexus / Global Chat</template>

        <div class="h-[calc(100vh-160px)] flex flex-col bg-white/[0.02] border border-white/5 rounded-3xl overflow-hidden backdrop-blur-xl">
            <!-- Chat Header -->
            <div class="px-8 py-6 border-b border-white/5 bg-white/[0.01] flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-3 h-3 bg-accent-green rounded-full animate-pulse"></div>
                    <div>
                        <h2 class="text-lg font-black text-white tracking-tight">Nexus Public Channel</h2>
                        <p class="text-[10px] text-gray-500 font-bold uppercase tracking-widest">End-to-End Encrypted Communication</p>
                    </div>
                </div>
                <div class="flex -space-x-2">
                    <div v-for="i in 3" :key="i" class="w-8 h-8 rounded-full bg-white/5 border border-dark-gray flex items-center justify-center text-[10px] font-bold text-gray-500">
                        {{ String.fromCharCode(64 + i) }}
                    </div>
                </div>
            </div>

            <!-- Messages Area -->
            <div 
                ref="messagesContainer"
                class="flex-1 overflow-y-auto p-8 space-y-6 custom-scrollbar"
            >
                <div 
                    v-for="msg in initialMessages" 
                    :key="msg.id"
                    :class="msg.user_id === user.id ? 'flex-row-reverse space-x-reverse' : ''"
                    class="flex items-end space-x-4 group"
                >
                    <!-- Avatar -->
                    <div class="w-10 h-10 rounded-2xl bg-white/5 border border-white/5 flex items-center justify-center text-xs font-black text-gray-400 shrink-0 group-hover:border-primary-blue/30 transition-colors">
                        {{ msg.user.name.charAt(0) }}
                    </div>

                    <!-- Bubble -->
                    <div class="max-w-[70%] space-y-1">
                        <div class="flex items-center space-x-2" :class="msg.user_id === user.id ? 'justify-end' : ''">
                            <span class="text-[10px] font-black uppercase tracking-widest text-gray-500">{{ msg.user.name }}</span>
                            <span class="text-[8px] font-bold text-primary-blue bg-primary-blue/10 px-1.5 py-0.5 rounded uppercase">{{ msg.user.role }}</span>
                        </div>
                        <div 
                            :class="msg.user_id === user.id ? 'bg-primary-blue text-white rounded-t-2xl rounded-l-2xl shadow-lg shadow-primary-blue/10' : 'bg-white/5 text-gray-300 rounded-t-2xl rounded-r-2xl border border-white/5'"
                            class="p-4 text-sm font-medium leading-relaxed"
                        >
                            {{ msg.content }}
                        </div>
                        <p class="text-[8px] font-medium text-gray-600 uppercase tracking-tighter" :class="msg.user_id === user.id ? 'text-right' : ''">
                            {{ new Date(msg.created_at).toLocaleTimeString() }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Input Area -->
            <div class="p-6 border-t border-white/5 bg-white/[0.01]">
                <form @submit.prevent="submit" class="relative">
                    <input 
                        v-model="form.content"
                        placeholder="Type a message to the Nexus..."
                        class="w-full bg-[#0a0b10] border border-white/10 rounded-2xl py-4 pl-6 pr-16 text-sm text-gray-200 focus:border-primary-blue transition-all outline-none placeholder:text-gray-700 font-medium"
                    />
                    <button 
                        type="submit"
                        :disabled="form.processing || !form.content.trim()"
                        class="absolute right-2 top-2 bottom-2 px-4 bg-primary-blue text-white rounded-xl hover:bg-primary-blue/80 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                    </button>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 10px;
}
</style>
