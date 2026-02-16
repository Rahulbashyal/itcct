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
    description: '',
    event_date: '',
    end_date: '',
    location: '',
    type: 'Meetup',
    registration_link: '',
    image_file: null
});
const imagePreview = ref(null);

const eventTypes = ['Meetup', 'Workshop', 'Hackathon', 'Competition', 'Social'];

const fetchEvents = async () => {
    try {
        const response = await axios.get('/api/v1/events?upcoming=true');
        events.value = response.data.data;
    } catch (error) {
        console.error("Failed to fetch events", error);
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

const submitEvent = async () => {
    if (!form.value.title || !form.value.event_date) return;
    
    submitting.value = true;
    const formData = new FormData();
    formData.append('title', form.value.title);
    formData.append('description', form.value.description);
    formData.append('event_date', form.value.event_date);
    if (form.value.end_date) formData.append('end_date', form.value.end_date);
    formData.append('location', form.value.location);
    formData.append('type', form.value.type);
    if (form.value.registration_link) formData.append('registration_link', form.value.registration_link);
    formData.append('is_published', 1);
    
    if (form.value.image_file) {
        formData.append('image_file', form.value.image_file);
    }
    
    try {
        await axios.post('/api/v1/events', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        showComposer.value = false;
        resetForm();
        fetchEvents();
    } catch (error) {
        alert("Failed to post event: " + (error.response?.data?.message || error.message));
    } finally {
        submitting.value = false;
    }
};

const deleteEvent = async (id) => {
    if (!confirm("Are you sure you want to delete this event?")) return;
    
    try {
        await axios.delete(`/api/v1/events/${id}`);
        fetchEvents();
        if (showDetail.value?.id === id) showDetail.value = null;
    } catch (error) {
        alert("Failed to delete event.");
    }
};

const resetForm = () => {
    form.value = { 
        title: '', description: '', event_date: '', end_date: '', 
        location: '', type: 'Meetup', registration_link: '', image_file: null 
    };
    imagePreview.value = null;
};

const openDetail = (item) => {
    showDetail.value = item;
};

onMounted(() => {
    fetchEvents();
});
</script>

<template>
    <div class="events-app flex h-full bg-slate-50 text-slate-800 overflow-hidden relative">
        
        <!-- Sidebar / Filter (Left) -->
        <aside v-if="!isEmbedded" class="w-64 bg-white border-r border-slate-200 flex flex-col hidden md:flex shrink-0">
            <div class="p-6 border-b border-slate-100 italic">
                <h2 class="font-black text-2xl text-slate-900 flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-indigo-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    </div>
                    Nexus Events
                </h2>
            </div>
            <nav class="flex-1 p-4 space-y-2">
                <button class="w-full text-left px-4 py-3 rounded-2xl bg-indigo-50 text-indigo-700 font-black flex items-center gap-3 transition-all border border-indigo-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Upcoming Sessions
                </button>
                <div class="px-4 pt-4 pb-2 text-[10px] font-black text-slate-400 uppercase tracking-widest">Archive</div>
                <button class="w-full text-left px-4 py-3 rounded-2xl hover:bg-slate-50 text-slate-500 font-bold flex items-center gap-3 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Past Records
                </button>
            </nav>
            <div v-if="isAdmin" class="p-6 border-t border-slate-100">
                <button @click="showComposer = true" class="w-full bg-slate-900 hover:bg-black text-white py-3.5 rounded-2xl font-black text-xs uppercase tracking-widest flex items-center justify-center gap-2 transition-all shadow-xl shadow-slate-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" /></svg>
                    Draft Event
                </button>
            </div>
        </aside>

        <!-- Main Feed (Center) -->
        <main class="flex-1 overflow-y-auto p-8 scrollbar-thin">
            <!-- Mobile Header -->
            <div class="md:hidden flex justify-between items-center mb-8">
                <h2 class="font-black text-2xl text-slate-900 italic">Timeline</h2>
                <button v-if="isAdmin" @click="showComposer = true" class="bg-indigo-600 text-white px-4 py-2 rounded-xl text-xs font-black uppercase">
                    + New
                </button>
            </div>

            <div v-if="loading" class="flex flex-col items-center justify-center py-24">
                <div class="flex gap-2">
                    <div class="w-4 h-4 bg-indigo-600 rounded-full animate-bounce"></div>
                    <div class="w-4 h-4 bg-indigo-600 rounded-full animate-bounce [animation-delay:-0.15s]"></div>
                    <div class="w-4 h-4 bg-indigo-600 rounded-full animate-bounce [animation-delay:-0.3s]"></div>
                </div>
                <p class="mt-6 text-slate-400 font-black uppercase tracking-[0.3em] text-[10px]">Syncing Events</p>
            </div>

            <div v-else-if="events.length === 0" class="flex flex-col items-center justify-center py-32 bg-white rounded-[3rem] border-2 border-dashed border-slate-100 max-w-4xl mx-auto shadow-inner">
                <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mb-8 border border-slate-100">
                    <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                </div>
                <h3 class="text-2xl font-black text-slate-800 uppercase tracking-tight">CCT Calendar is Clear</h3>
                <p class="text-slate-400 font-bold mt-2 text-center max-w-xs">No upcoming sessions detected in the near future.</p>
                <button v-if="isAdmin" @click="showComposer = true" class="mt-8 px-8 py-3 bg-indigo-600 text-white font-black rounded-2xl shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all">Create First Event</button>
            </div>

            <div v-else class="grid grid-cols-1 xl:grid-cols-2 gap-8 max-w-6xl mx-auto pb-20">
                <article 
                    v-for="item in events" 
                    :key="item.id" 
                    class="bg-white border border-slate-200 rounded-[2.5rem] overflow-hidden hover:border-indigo-500/30 transition-all cursor-pointer shadow-sm hover:shadow-2xl hover:shadow-indigo-100 group flex flex-col h-auto"
                    @click="openDetail(item)"
                >
                    <div class="h-48 bg-slate-100 relative overflow-hidden">
                        <img 
                            v-if="item.image" 
                            :src="item.image" 
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                        >
                        <div v-else class="w-full h-full bg-gradient-to-br from-indigo-500 to-indigo-700 flex items-center justify-center text-white/10 font-black text-7xl italic">EVENT</div>
                        
                        <div class="absolute top-6 left-6 px-4 py-2 bg-white/95 backdrop-blur rounded-2xl text-[10px] font-black uppercase tracking-widest text-indigo-600 shadow-xl border border-white">
                            {{ item.type }}
                        </div>

                        <div v-if="isAdmin" class="absolute top-6 right-6">
                             <button @click.stop="deleteEvent(item.id)" class="w-10 h-10 rounded-2xl bg-white/95 backdrop-blur text-red-500 hover:bg-red-500 hover:text-white transition-all flex items-center justify-center shadow-xl border border-white">
                                 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                             </button>
                        </div>
                    </div>

                    <div class="p-8 flex-1 flex flex-col">
                        <div class="flex items-center gap-6 mb-6">
                            <div class="flex flex-col items-center justify-center w-14 h-14 bg-indigo-50 rounded-2xl border border-indigo-100">
                                <span class="text-[10px] font-black uppercase text-indigo-400 leading-none mb-1">{{ new Date(item.event_date).toLocaleString('default', { month: 'short' }) }}</span>
                                <span class="text-xl font-black text-indigo-700 leading-none uppercase">{{ new Date(item.event_date).getDate() }}</span>
                            </div>
                            <div class="flex flex-col">
                                <h3 class="text-xl font-black text-slate-800 leading-tight group-hover:text-indigo-600 transition-colors">{{ item.title }}</h3>
                                <div class="flex items-center gap-3 text-[10px] font-black uppercase tracking-widest text-slate-400 mt-1">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        {{ new Date(item.event_date).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}
                                    </span>
                                    <span v-if="item.location" class="flex items-center gap-1">
                                        <svg class="w-3 h-3 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                        {{ item.location }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <p class="text-slate-500 text-sm leading-relaxed line-clamp-2 font-medium mb-8">{{ item.description }}</p>

                        <div class="mt-auto flex items-center justify-between pt-6 border-t border-slate-50">
                            <div class="flex -space-x-3">
                                <div v-for="i in 3" :key="i" class="w-8 h-8 rounded-full border-2 border-white bg-slate-200 flex items-center justify-center font-black text-[10px] text-slate-400">
                                    {{ ['A','J','S'][i-1] }}
                                </div>
                                <div class="w-8 h-8 rounded-full border-2 border-white bg-indigo-600 flex items-center justify-center font-black text-[8px] text-white">+12</div>
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
                        <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center text-indigo-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                        Broadcast Event
                    </h3>
                    <button @click="showComposer = false" class="text-slate-400 hover:text-slate-900 transition-colors">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </header>
                
                <div class="flex-1 overflow-y-auto p-8 space-y-6 scrollbar-thin">
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Session Title</label>
                        <input v-model="form.title" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-4 text-slate-900 font-bold placeholder-slate-300 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none" placeholder="Enter session title...">
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                         <div class="space-y-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Event Type</label>
                            <select v-model="form.type" class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-4 text-slate-700 font-bold outline-none focus:ring-4 focus:ring-indigo-500/10">
                                <option v-for="type in eventTypes" :key="type" :value="type">{{ type }}</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                             <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Visual Preview</label>
                             <div class="relative">
                                 <input @change="handleImageUpload" type="file" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer z-10">
                                 <div class="w-full bg-slate-50 border border-slate-200 border-dashed rounded-2xl p-4 text-xs font-bold text-slate-500 flex items-center gap-2 hover:bg-slate-100 transition-colors">
                                     <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                     {{ form.image_file ? form.image_file.name : 'Upload Event Cover' }}
                                 </div>
                             </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                         <div class="space-y-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Commencement</label>
                            <input v-model="form.event_date" type="datetime-local" class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-4 text-slate-700 font-bold outline-none focus:ring-4 focus:ring-indigo-500/10 transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Termination</label>
                            <input v-model="form.end_date" type="datetime-local" class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-4 text-slate-700 font-bold outline-none focus:ring-4 focus:ring-indigo-500/10 transition-all">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Operating Location</label>
                        <input v-model="form.location" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-4 text-slate-900 font-bold placeholder-slate-300 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none" placeholder="e.g. Virtual Portal or Club Lounge">
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Briefing Description</label>
                        <textarea v-model="form.description" rows="4" class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-4 text-slate-700 font-medium placeholder-slate-300 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none resize-none" placeholder="Explain the session objective..."></textarea>
                    </div>
                </div>

                <footer class="p-8 border-t border-slate-100 bg-white flex justify-end gap-4">
                    <button @click="showComposer = false" class="px-8 py-3.5 text-slate-500 font-black text-xs uppercase tracking-widest hover:bg-slate-50 rounded-2xl transition-all">Discard</button>
                    <button 
                        @click="submitEvent" 
                        :disabled="submitting"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-10 py-3.5 rounded-2xl font-black text-xs uppercase tracking-widest disabled:opacity-50 transition-all shadow-xl shadow-indigo-100 flex items-center gap-2"
                    >
                        <span v-if="submitting">Transmitting...</span>
                        <span v-else>Confirm Session</span>
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
                 
                 <div class="h-96 relative overflow-hidden">
                     <img v-if="showDetail.image" :src="showDetail.image" class="w-full h-full object-cover">
                     <div v-else class="w-full h-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white/20 text-9xl font-black italic">E</div>
                     <div class="absolute inset-0 bg-gradient-to-t from-white via-white/10 to-transparent"></div>
                 </div>

                 <div class="p-12 md:p-20 -mt-40 relative">
                     <div class="bg-white/80 backdrop-blur-xl border border-white rounded-[3rem] p-12 shadow-2xl shadow-indigo-100/50 flex flex-col md:flex-row gap-12 items-center mb-12">
                         <div class="flex-1 text-center md:text-left">
                             <span class="px-4 py-2 bg-indigo-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] mb-6 inline-block leading-none italic">{{ showDetail.type }}</span>
                             <h1 class="text-4xl md:text-6xl font-black text-slate-900 leading-[1.1] mb-8 tracking-tight italic">{{ showDetail.title }}</h1>
                             
                             <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                 <div class="flex items-center gap-4">
                                     <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600">
                                         <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                     </div>
                                     <div class="flex flex-col text-left">
                                         <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">Date & Time</span>
                                         <span class="text-xs font-black text-slate-800">{{ new Date(showDetail.event_date).toLocaleString(undefined, { dateStyle: 'long', timeStyle: 'short' }) }}</span>
                                     </div>
                                 </div>
                                 <div class="flex items-center gap-4">
                                     <div class="w-12 h-12 bg-pink-50 rounded-2xl flex items-center justify-center text-pink-600">
                                         <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                     </div>
                                     <div class="flex flex-col text-left">
                                         <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">Location</span>
                                         <span class="text-xs font-black text-slate-800">{{ showDetail.location || 'Nexus Virtual Portal' }}</span>
                                     </div>
                                 </div>
                             </div>
                         </div>
                         <div v-if="showDetail.registration_link" class="shrink-0">
                             <a :href="showDetail.registration_link" target="_blank" class="bg-slate-900 hover:bg-black text-white px-12 py-5 rounded-[2rem] font-black text-xs uppercase tracking-widest inline-flex items-center gap-3 transition-all shadow-2xl shadow-slate-200">
                                 Claim Seat
                                 <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                             </a>
                         </div>
                     </div>

                     <div class="prose prose-lg max-w-none px-4">
                         <h3 class="text-[10px] font-black uppercase tracking-[0.3em] text-indigo-400 mb-8 border-b border-slate-100 pb-4">Session Objective</h3>
                         <div class="whitespace-pre-wrap text-xl leading-[1.8] font-medium text-slate-600 italic">{{ showDetail.description }}</div>
                     </div>
                 </div>
             </div>
        </div>

    </div>
</template>

<style scoped>
.scrollbar-thin::-webkit-scrollbar { width: 6px; }
.scrollbar-thin::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<style scoped>
.scrollbar-thin::-webkit-scrollbar { width: 6px; }
.scrollbar-thin::-webkit-scrollbar-thumb { background: #4b5563; border-radius: 3px; }
</style>
