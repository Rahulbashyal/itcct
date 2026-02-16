<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
    isEmbedded: { type: Boolean, default: false }
});

const page = usePage();
const user = computed(() => props.user || page.props.auth.user);
const isAdmin = computed(() => user.value && (user.value.role === 'SuperAdmin' || user.value.role === 'Admin' || user.value.role === 'Secretary'));

// Form Data
const form = ref({
    title: '',
    summary: '',
    content: '',
    category: 'General',
    image_file: null
});
const imagePreview = ref(null);

const categories = ['General', 'Tech', 'Club', 'Announcement', 'Workshop'];

const fetchNews = async () => {
    try {
        const response = await axios.get('/api/v1/news');
        newsItems.value = response.data.data;
    } catch (error) {
        console.error("Failed to fetch news", error);
    } finally {
        loading.value = false;
    }
};

const handleImageUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.value.image_file = file;
        imagePreview.value = URL.createObjectURL(file);
    }
};

const submitNews = async () => {
    if (!form.value.title || !form.value.content) return;
    
    submitting.value = true;
    const formData = new FormData();
    formData.append('title', form.value.title);
    formData.append('summary', form.value.summary);
    formData.append('content', form.value.content);
    formData.append('category', form.value.category);
    formData.append('is_published', 1);
    
    if (form.value.image_file) {
        formData.append('image_file', form.value.image_file);
    }
    
    try {
        await axios.post('/api/v1/news', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        showComposer.value = false;
        resetForm();
        fetchNews();
    } catch (error) {
        alert("Failed to post news: " + (error.response?.data?.message || error.message));
    } finally {
        submitting.value = false;
    }
};

const deleteNews = async (id) => {
    if (!confirm("Are you sure you want to delete this news item?")) return;
    
    try {
        await axios.delete(`/api/v1/news/${id}`);
        fetchNews();
        if (showDetail.value?.id === id) showDetail.value = null;
    } catch (error) {
        alert("Failed to delete news.");
    }
};

const resetForm = () => {
    form.value = { title: '', summary: '', content: '', category: 'General', image_file: null };
    imagePreview.value = null;
};

const openDetail = (item) => {
    showDetail.value = item;
};

onMounted(() => {
    fetchNews();
});
</script>

<template>
    <div class="news-app flex h-full bg-slate-50 text-slate-800 overflow-hidden relative">
        
        <!-- Sidebar / Filter (Left) -->
        <aside v-if="!isEmbedded" class="w-64 bg-white border-r border-slate-200 flex flex-col hidden md:flex shrink-0">
            <div class="p-6 border-b border-slate-100 italic">
                <h2 class="font-black text-2xl text-slate-900 flex items-center gap-3">
                    <div class="w-10 h-10 bg-pink-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-pink-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2.5 2.5 0 00-2.5-2.5H15" /></svg>
                    </div>
                    Alerts
                </h2>
            </div>
            <nav class="flex-1 p-4 space-y-2">
                <button class="w-full text-left px-4 py-3 rounded-2xl bg-indigo-50 text-indigo-700 font-black flex items-center gap-3 transition-all border border-indigo-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                    All Bulletins
                </button>
                <div class="px-4 pt-4 pb-2 text-[10px] font-black text-slate-400 uppercase tracking-widest">Departments</div>
                <button v-for="cat in categories" :key="cat" class="w-full text-left px-4 py-3 rounded-2xl hover:bg-slate-50 text-slate-500 font-bold flex items-center gap-3 transition-all group">
                    <div class="w-2 h-2 rounded-full bg-slate-300 group-hover:bg-pink-500 transition-all"></div>
                    {{ cat }}
                </button>
            </nav>
            <div v-if="isAdmin" class="p-6 border-t border-slate-100">
                <button @click="showComposer = true" class="w-full bg-slate-900 hover:bg-black text-white py-3.5 rounded-2xl font-black text-xs uppercase tracking-widest flex items-center justify-center gap-2 transition-all shadow-xl shadow-slate-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" /></svg>
                    Publish News
                </button>
            </div>
        </aside>

        <!-- Main Feed (Center) -->
        <main class="flex-1 overflow-y-auto p-8 scrollbar-thin">
            <!-- Mobile Header -->
            <div class="md:hidden flex justify-between items-center mb-8">
                <h2 class="font-black text-2xl text-slate-900 italic">News Feed</h2>
                <button v-if="isAdmin" @click="showComposer = true" class="bg-pink-600 text-white px-4 py-2 rounded-xl text-xs font-black uppercase">
                    Publish
                </button>
            </div>

            <div v-if="loading" class="flex flex-col items-center justify-center py-24">
                <div class="flex gap-2">
                    <div class="w-4 h-4 bg-pink-500 rounded-full animate-bounce"></div>
                    <div class="w-4 h-4 bg-pink-500 rounded-full animate-bounce [animation-delay:-0.15s]"></div>
                    <div class="w-4 h-4 bg-pink-500 rounded-full animate-bounce [animation-delay:-0.3s]"></div>
                </div>
                <p class="mt-6 text-slate-400 font-black uppercase tracking-[0.3em] text-[10px]">Filtering Headlines</p>
            </div>

            <div v-else-if="newsItems.length === 0" class="flex flex-col items-center justify-center py-32 bg-white rounded-[3rem] border-2 border-dashed border-slate-100 max-w-4xl mx-auto shadow-inner">
                <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mb-8 border border-slate-100">
                    <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                </div>
                <h3 class="text-2xl font-black text-slate-800 uppercase tracking-tight">The wire is quiet</h3>
                <p class="text-slate-400 font-bold mt-2 text-center max-w-xs">No active alerts or news bulletins found in the current transmission.</p>
                <button v-if="isAdmin" @click="showComposer = true" class="mt-8 px-8 py-3 bg-pink-600 text-white font-black rounded-2xl shadow-lg shadow-pink-100 hover:bg-pink-700 transition-all">Start Posting</button>
            </div>

            <div v-else class="grid grid-cols-1 xl:grid-cols-2 gap-8 max-w-6xl mx-auto pb-20">
                <article 
                    v-for="item in newsItems" 
                    :key="item.id" 
                    class="bg-white border border-slate-200 rounded-[2.5rem] overflow-hidden hover:border-pink-500/30 transition-all cursor-pointer shadow-sm hover:shadow-2xl hover:shadow-pink-100 group flex flex-col"
                    @click="openDetail(item)"
                >
                    <div class="h-56 bg-slate-100 relative overflow-hidden">
                        <img 
                            v-if="item.image" 
                            :src="item.image" 
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
                        >
                        <div v-else class="w-full h-full bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center text-slate-300 font-black text-2xl uppercase tracking-widest italic">Nexus Prime</div>
                        
                        <span class="absolute top-6 left-6 px-4 py-2 bg-white/90 backdrop-blur rounded-2xl text-[10px] font-black uppercase tracking-widest text-pink-600 shadow-xl border border-white">
                            {{ item.category }}
                        </span>
                    </div>
                    <div class="p-8 flex-1 flex flex-col">
                        <div class="flex justify-between items-center mb-4">
                             <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-slate-400">
                                 <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                 {{ new Date(item.created_at).toLocaleDateString() }}
                             </div>
                             <div v-if="isAdmin" class="flex gap-2">
                                 <button @click.stop="deleteNews(item.id)" class="w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-all flex items-center justify-center">
                                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                 </button>
                             </div>
                        </div>
                        <h3 class="text-xl font-black text-slate-800 mb-4 leading-tight group-hover:text-pink-600 transition-colors">{{ item.title }}</h3>
                        <p class="text-slate-500 text-sm leading-relaxed mb-8 line-clamp-3 font-medium">{{ item.summary || item.content }}</p>
                        
                        <div class="mt-auto pt-6 border-t border-slate-50 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-2xl bg-indigo-600 flex items-center justify-center text-xs font-black text-white shadow-lg shadow-indigo-100">
                                    {{ item.author?.name?.substring(0,1) || '?' }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-[11px] font-black text-slate-800">{{ item.author?.name }}</span>
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ item.author?.role }}</span>
                                </div>
                            </div>
                            <div class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center group-hover:bg-indigo-600 transition-all shadow-sm">
                                <svg class="w-5 h-5 text-slate-300 group-hover:text-white transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                            </div>
                        </div>
                    </div>
                </article>
            </div>
        </main>

        <!-- Composer Modal -->
        <div v-if="showComposer" class="absolute inset-0 z-50 bg-slate-900/40 backdrop-blur-md flex items-center justify-center p-4">
            <div class="bg-white w-full max-w-2xl rounded-[2.5rem] border border-slate-200 shadow-2xl flex flex-col max-h-[90vh] overflow-hidden">
                <header class="p-8 border-b border-slate-100 flex justify-between items-center bg-white">
                    <h3 class="font-black text-2xl text-slate-900 flex items-center gap-3 italic">
                        <div class="w-10 h-10 bg-pink-100 rounded-xl flex items-center justify-center text-pink-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                        </div>
                        Publish Bulletin
                    </h3>
                    <button @click="showComposer = false" class="text-slate-400 hover:text-slate-900 transition-colors">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </header>
                
                <div class="flex-1 overflow-y-auto p-8 space-y-6 scrollbar-thin">
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Headline Title</label>
                        <input v-model="form.title" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-4 text-slate-900 font-bold placeholder-slate-300 focus:ring-4 focus:ring-pink-500/10 focus:border-pink-500 transition-all outline-none" placeholder="Enter bulletin headline...">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-6">
                         <div class="space-y-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Category</label>
                            <select v-model="form.category" class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-4 text-slate-700 font-bold outline-none focus:ring-4 focus:ring-indigo-500/10">
                                <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                             <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Visual Asset</label>
                             <div class="relative">
                                 <input @change="handleImageUpload" type="file" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer z-10">
                                 <div class="w-full bg-slate-50 border border-slate-200 border-dashed rounded-2xl p-4 text-xs font-bold text-slate-500 flex items-center gap-2 hover:bg-slate-100 transition-colors">
                                     <svg class="w-4 h-4 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                     {{ form.image_file ? form.image_file.name : 'Upload Header Image' }}
                                 </div>
                             </div>
                        </div>
                    </div>

                    <div v-if="imagePreview" class="rounded-[2rem] overflow-hidden border border-slate-200 shadow-lg h-48">
                        <img :src="imagePreview" class="w-full h-full object-cover">
                    </div>
                    
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Hook / summary</label>
                        <input v-model="form.summary" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-4 text-slate-600 font-medium placeholder-slate-300 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none" placeholder="A short, catchy overview...">
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Deep Content</label>
                        <textarea v-model="form.content" rows="6" class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-4 text-slate-700 font-medium placeholder-slate-300 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none resize-none" placeholder="The full article details go here..."></textarea>
                    </div>
                </div>

                <footer class="p-8 border-t border-slate-100 bg-white flex justify-end gap-4">
                    <button @click="showComposer = false" class="px-8 py-3.5 text-slate-500 font-black text-xs uppercase tracking-widest hover:bg-slate-50 rounded-2xl transition-all">Discard</button>
                    <button 
                        @click="submitNews" 
                        :disabled="submitting"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-10 py-3.5 rounded-2xl font-black text-xs uppercase tracking-widest disabled:opacity-50 transition-all shadow-xl shadow-indigo-100 flex items-center gap-2"
                    >
                        <span v-if="submitting">Transmitting...</span>
                        <span v-else>Broadcast Now</span>
                    </button>
                </footer>
            </div>
        </div>

        <!-- Detail Modal -->
        <div v-if="showDetail" class="absolute inset-0 z-[60] bg-slate-50/90 backdrop-blur-xl overflow-y-auto p-4 md:p-12">
             <div class="max-w-4xl mx-auto bg-white shadow-2xl rounded-[3rem] border border-slate-100 overflow-hidden relative">
                 <button @click="showDetail = null" class="absolute top-8 right-8 z-50 bg-white/50 backdrop-blur hover:bg-slate-900 hover:text-white text-slate-900 w-12 h-12 rounded-2xl shadow-xl flex items-center justify-center transition-all">
                     <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                 </button>
                 
                 <div class="h-80 md:h-[450px] relative overflow-hidden">
                     <img v-if="showDetail.image" :src="showDetail.image" class="w-full h-full object-cover">
                     <div v-else class="w-full h-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white/20 text-9xl font-black italic">N</div>
                     <div class="absolute inset-0 bg-gradient-to-t from-white via-white/20 to-transparent"></div>
                     <div class="absolute bottom-12 left-12 right-12">
                         <span class="px-4 py-2 bg-pink-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] mb-6 inline-block shadow-lg shadow-pink-200">{{ showDetail.category }}</span>
                         <h1 class="text-4xl md:text-6xl font-black text-slate-900 leading-[1.1] mb-6 tracking-tight italic">{{ showDetail.title }}</h1>
                         <div class="flex items-center gap-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white font-black shadow-lg shadow-indigo-100">{{ showDetail.author?.name?.substring(0,1) }}</div>
                                <div class="flex flex-col">
                                    <span class="text-xs font-black text-slate-900">{{ showDetail.author?.name }}</span>
                                    <span class="text-[9px] font-black text-indigo-500 uppercase tracking-widest">Lead Correspondent</span>
                                </div>
                            </div>
                            <div class="h-8 w-px bg-slate-200"></div>
                             <div class="flex flex-col text-slate-400">
                                 <span class="text-[9px] font-black uppercase tracking-widest">Timestamp</span>
                                 <span class="text-xs font-bold text-slate-600">{{ new Date(showDetail.created_at).toLocaleDateString(undefined, { dateStyle: 'long' }) }}</span>
                             </div>
                         </div>
                     </div>
                 </div>

                 <div class="p-12 md:p-20 text-slate-700">
                     <p v-if="showDetail.summary" class="text-2xl text-slate-800 leading-relaxed font-bold mb-12 italic border-l-4 border-pink-500 pl-8">{{ showDetail.summary }}</p>
                     <div class="whitespace-pre-wrap text-lg leading-[1.8] font-medium text-slate-600">{{ showDetail.content }}</div>
                 </div>
             </div>
        </div>

    </div>
</template>

<style scoped>
.scrollbar-thin::-webkit-scrollbar { width: 6px; }
.scrollbar-thin::-webkit-scrollbar-thumb { background: #4b5563; border-radius: 3px; }
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
