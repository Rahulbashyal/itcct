<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    user: Object
});

const feedItems = ref([]);
const loading = ref(true);
const activeFilter = ref('all'); // all, posts, news, events
const showComposer = ref(false);
const composerContent = ref('');
const isSubmitting = ref(false);
const showCommentsFor = ref(null);
const commentInput = ref('');

const isAdmin = computed(() => props.user && (props.user.role === 'SuperAdmin' || props.user.role === 'Secretary'));

const fetchData = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/v1/nexus/feed');
        feedItems.value = response.data.data || [];
    } catch (error) {
        console.error('Failed to fetch nexus feed:', error);
    } finally {
        loading.value = false;
    }
};

const submitPost = async () => {
    if (!composerContent.value.trim()) return;
    isSubmitting.value = true;
    try {
        const response = await axios.post('/api/v1/nexus/posts', {
            content: composerContent.value,
            is_announcement: false
        });
        feedItems.value.unshift({
            ...response.data.data,
            type: 'post',
            likes_count: 0,
            comments_count: 0,
            is_liked: false,
            comments: []
        });
        composerContent.value = '';
        showComposer.value = false;
    } catch (error) {
        console.error('Failed to create post:', error);
    } finally {
        isSubmitting.value = false;
    }
};

const toggleLike = async (item) => {
    try {
        const response = await axios.post('/api/v1/nexus/like', {
            id: item.id,
            type: item.type
        });
        
        if (response.data.action === 'liked') {
            item.likes_count++;
            item.is_liked = true;
        } else {
            item.likes_count--;
            item.is_liked = false;
        }
    } catch (error) {
        console.error('Failed to toggle like:', error);
    }
};

const addComment = async (item) => {
    if (!commentInput.value.trim()) return;
    try {
        const response = await axios.post('/api/v1/nexus/comments', {
            id: item.id,
            type: item.type,
            content: commentInput.value
        });
        
        if (!item.comments) item.comments = [];
        item.comments.push(response.data.data);
        item.comments_count++;
        commentInput.value = '';
    } catch (error) {
        console.error('Failed to add comment:', error);
    }
};

const formatDate = (date) => {
    if (!date) return 'Just now';
    const now = new Date();
    const diff = now - new Date(date);
    const seconds = Math.floor(diff / 1000);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);
    
    if (days > 7) return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    if (days > 0) return `${days}d`;
    if (hours > 0) return `${hours}h`;
    if (minutes > 0) return `${minutes}m`;
    return 'now';
};

const filteredFeed = computed(() => {
    if (activeFilter.value === 'all') return feedItems.value;
    return feedItems.value.filter(i => i.type === activeFilter.value);
});

onMounted(fetchData);
</script>

<template>
    <div class="nexus-feed h-full flex flex-col bg-slate-50 overflow-y-auto">
        <div class="max-w-2xl mx-auto w-full py-6 px-4">
            <!-- Composer -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 mb-6">
                <div class="flex gap-4">
                    <img :src="'https://ui-avatars.com/api/?name=' + user?.name + '&background=6366f1&color=fff'" class="w-10 h-10 rounded-full">
                    <div class="flex-1">
                        <textarea 
                            v-model="composerContent"
                            @focus="showComposer = true"
                            placeholder="What's happening in CCT?"
                            class="w-full bg-slate-50 border-none rounded-xl p-3 text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 transition-all resize-none min-h-[44px]"
                            :class="{ 'min-h-[100px]': showComposer }"
                        ></textarea>
                        
                        <div v-if="showComposer" class="flex items-center justify-between mt-3 border-t border-slate-100 pt-3">
                            <div class="flex gap-2">
                                <button class="p-2 hover:bg-slate-100 rounded-full text-slate-500" title="Add Image">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                </button>
                                <button class="p-2 hover:bg-slate-100 rounded-full text-slate-500" title="Poll">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                                </button>
                            </div>
                            <div class="flex gap-2">
                                <button @click="showComposer = false" class="px-4 py-1.5 text-slate-500 font-medium rounded-full hover:bg-slate-100">Cancel</button>
                                <button 
                                    @click="submitPost"
                                    :disabled="isSubmitting || !composerContent.trim()"
                                    class="px-6 py-1.5 bg-indigo-600 text-white font-bold rounded-full hover:bg-indigo-700 disabled:opacity-50 transition-colors shadow-md"
                                >
                                    {{ isSubmitting ? 'Posting...' : 'Post' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex gap-2 mb-6 overflow-x-auto pb-2 scrollbar-hide">
                <button 
                    v-for="f in ['all', 'post', 'news', 'event']" 
                    :key="f"
                    @click="activeFilter = f"
                    class="px-5 py-2 rounded-full text-sm font-bold capitalize transition-all whitespace-nowrap"
                    :class="activeFilter === f ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'bg-white text-slate-600 border border-slate-200 hover:border-indigo-400'"
                >
                    {{ f === 'all' ? 'Everyting' : f + 's' }}
                </button>
            </div>

            <!-- Feed -->
            <div v-if="loading" class="flex flex-col items-center py-20">
                <div class="w-12 h-12 border-4 border-indigo-100 border-t-indigo-600 rounded-full animate-spin"></div>
                <p class="mt-4 text-slate-400 font-medium">Loading your feed...</p>
            </div>

            <div v-else class="space-y-6">
                <div v-for="item in filteredFeed" :key="item.type + item.id" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden hover:border-indigo-100 transition-all">
                    <!-- Post Header -->
                    <div class="p-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img :src="'https://ui-avatars.com/api/?name=' + (item.user?.name || item.author?.name || 'IT Club') + '&background=' + (item.type === 'news' ? '3b82f6' : '6366f1') + '&color=fff'" class="w-11 h-11 rounded-full border border-slate-100">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-900">{{ item.user?.name || item.author?.name || 'IT Club CCT' }}</span>
                                    <span v-if="item.is_announcement" class="px-2 py-0.5 bg-amber-100 text-amber-700 text-[10px] font-bold rounded-full uppercase tracking-wider">Announcement</span>
                                    <span v-if="item.type === 'news'" class="px-2 py-0.5 bg-blue-100 text-blue-700 text-[10px] font-bold rounded-full uppercase tracking-wider">Official News</span>
                                </div>
                                <div class="text-xs text-slate-400 font-medium">{{ formatDate(item.created_at || item.event_date) }} • {{ item.type }}</div>
                            </div>
                        </div>
                        <button class="text-slate-400 hover:text-slate-600 p-2">•••</button>
                    </div>

                    <!-- Post Content -->
                    <div class="px-4 pb-4">
                        <h3 v-if="item.title" class="text-lg font-bold text-slate-900 mb-2">{{ item.title }}</h3>
                        <p class="text-slate-700 leading-relaxed whitespace-pre-wrap">{{ item.content || item.description || item.summary }}</p>
                    </div>

                    <!-- Media -->
                    <div v-if="item.image" class="w-full bg-slate-100 border-y border-slate-50">
                        <img :src="item.image" class="w-full object-cover max-h-[500px]">
                    </div>

                    <!-- Event Details -->
                    <div v-if="item.type === 'event'" class="mx-4 mb-4 p-4 bg-indigo-50 rounded-xl flex items-center justify-between border border-indigo-100">
                        <div>
                            <div class="text-xs text-indigo-600 font-bold uppercase tracking-widest">Happening on</div>
                            <div class="text-indigo-900 font-bold">{{ new Date(item.event_date).toLocaleDateString() }}</div>
                        </div>
                        <button class="px-6 py-2 bg-indigo-600 text-white text-sm font-bold rounded-lg hover:bg-indigo-700 shadow-sm transition-all">Join Event</button>
                    </div>

                    <!-- Interaction Buttons -->
                    <div class="px-4 py-2 border-t border-slate-50 flex items-center gap-6">
                        <button 
                            @click="toggleLike(item)"
                            class="flex items-center gap-2 py-2 px-3 rounded-lg transition-colors"
                            :class="item.is_liked ? 'text-rose-600 bg-rose-50' : 'text-slate-500 hover:bg-slate-50'"
                        >
                            <svg class="w-5 h-5" :fill="item.is_liked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
                            <span class="text-sm font-bold">{{ item.likes_count || 0 }}</span>
                        </button>
                        <button 
                            @click="showCommentsFor === item.id ? showCommentsFor = null : showCommentsFor = item.id"
                            class="flex items-center gap-2 py-2 px-3 rounded-lg text-slate-500 hover:bg-slate-50 transition-colors"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                            <span class="text-sm font-bold">{{ item.comments_count || 0 }}</span>
                        </button>
                        <button class="flex items-center gap-2 py-2 px-3 rounded-lg text-slate-500 hover:bg-slate-50 transition-colors ml-auto">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" /></svg>
                        </button>
                    </div>

                    <!-- Comments Section -->
                    <div v-if="showCommentsFor === item.id" class="bg-slate-50 p-4 border-t border-slate-100">
                        <div class="space-y-4 mb-4">
                            <div v-for="comment in item.comments" :key="comment.id" class="flex gap-3">
                                <img :src="'https://ui-avatars.com/api/?name=' + comment.user?.name + '&background=94a3b8&color=fff'" class="w-8 h-8 rounded-full shrink-0">
                                <div class="bg-white rounded-2xl p-3 border border-slate-200">
                                    <div class="text-xs font-bold text-slate-900">{{ comment.user?.name }}</div>
                                    <div class="text-sm text-slate-700">{{ comment.content }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <input 
                                v-model="commentInput"
                                @keyup.enter="addComment(item)"
                                placeholder="Add a comment..." 
                                class="flex-1 bg-white border border-slate-200 rounded-full px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
                            >
                            <button @click="addComment(item)" class="text-indigo-600 font-bold text-sm px-2">Send</button>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="filteredFeed.length === 0" class="text-center py-24 bg-white rounded-3xl border border-slate-200 shadow-sm">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    </div>
                    <h3 class="text-xl font-black text-slate-800 tracking-tight">Quiet in Nexus</h3>
                    <p class="text-slate-400 mt-2 text-sm">No {{ activeFilter === 'all' ? 'content' : activeFilter }} matches your filter yet.</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
