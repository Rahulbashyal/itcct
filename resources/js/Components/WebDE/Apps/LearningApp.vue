<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);
const isAdmin = computed(() => user.value && (user.value.role === 'SuperAdmin' || user.value.role === 'Admin'));

const resources = ref([]);
const loading = ref(true);
const showUploader = ref(false);
const uploading = ref(false);
const currentCategory = ref('All');

const form = ref({
    title: '',
    type: 'Link',
    category: 'Programming',
    description: '',
    link: '',
    file: null
});

const categories = ['All', 'Programming', 'Design', 'Soft Skills', 'Certifications', 'Other'];
const resourceTypes = ['Link', 'Video', 'PDF', 'GitHub', 'Article'];

const filteredResources = computed(() => {
    if (currentCategory.value === 'All') return resources.value;
    return resources.value.filter(r => r.category === currentCategory.value);
});

const fetchResources = async () => {
    try {
        const response = await axios.get('/api/v1/resources');
        resources.value = response.data.data || response.data;
    } catch (error) {
        console.error("Failed to fetch resources", error);
    } finally {
        loading.value = false;
    }
};

const handleFileUpload = (event) => {
    form.value.file = event.target.files[0];
};

const submitResource = async () => {
    if (!form.value.title || (!form.value.link && !form.value.file)) {
        alert("Title and Link/File are required.");
        return;
    }

    uploading.value = true;
    const formData = new FormData();
    formData.append('title', form.value.title);
    formData.append('type', form.value.type);
    formData.append('category', form.value.category);
    formData.append('description', form.value.description);
    if (form.value.link) formData.append('link', form.value.link);
    if (form.value.file) formData.append('file', form.value.file);
    formData.append('is_published', 1);

    try {
        await axios.post('/api/v1/resources', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        showUploader.value = false;
        form.value = { title: '', type: 'Link', category: 'Programming', description: '', link: '', file: null };
        fetchResources();
    } catch (error) {
        alert("Upload failed: " + (error.response?.data?.message || error.message));
    } finally {
        uploading.value = false;
    }
};

const deleteResource = async (id) => {
    if (!confirm("Delete this resource?")) return;
    try {
        await axios.delete(`/api/v1/resources/${id}`);
        fetchResources();
    } catch (error) {
        alert("Delete failed.");
    }
};

onMounted(fetchResources);
</script>

<template>
    <div class="learning-app h-full flex flex-col bg-[#1e293b] text-white">
        <!-- Toolbar -->
        <header class="h-16 bg-[#0f1218] border-b border-gray-700 flex items-center justify-between px-4 shrink-0">
            <h2 class="font-bold text-lg flex items-center gap-2">📚 Learning Hub</h2>
            <div class="flex items-center gap-4">
                <nav class="hidden md:flex bg-[#242c3e] rounded-lg p-1">
                    <button 
                        v-for="cat in categories" 
                        :key="cat" 
                        @click="currentCategory = cat"
                        class="px-3 py-1 rounded text-sm font-medium transition-colors"
                        :class="currentCategory === cat ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white'"
                    >
                        {{ cat }}
                    </button>
                </nav>
                <select v-model="currentCategory" class="md:hidden bg-[#242c3e] border border-gray-700 rounded p-1 text-sm outline-none">
                    <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                </select>
                <button v-if="isAdmin" @click="showUploader = true" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded text-sm font-bold flex items-center gap-2">
                    <span>+</span> Add
                </button>
            </div>
        </header>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto p-6 scrollbar-thin">
            <div v-if="loading" class="flex justify-center py-20">
                <div class="animate-spin text-4xl">⏳</div>
            </div>

            <div v-else-if="filteredResources.length === 0" class="text-center py-20 text-gray-500">
                <div class="text-4xl mb-4">📖</div>
                <p>No resources found in this category.</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <div v-for="res in filteredResources" :key="res.id" class="bg-[#242c3e] border border-white/5 rounded-xl p-5 hover:border-indigo-500/30 hover:transform hover:-translate-y-1 transition-all group relative flex flex-col">
                    <div class="flex items-start justify-between mb-3">
                         <div class="text-3xl">
                             <span v-if="res.type === 'Video'">🎥</span>
                             <span v-else-if="res.type === 'PDF'">📕</span>
                             <span v-else-if="res.type === 'GitHub'">👾</span>
                             <span v-else-if="res.type === 'Article'">📰</span>
                             <span v-else>🔗</span>
                         </div>
                         <div v-if="isAdmin">
                             <button @click="deleteResource(res.id)" class="text-gray-500 hover:text-red-400">🗑️</button>
                         </div>
                    </div>
                    
                    <h3 class="font-bold text-gray-200 text-lg mb-2 leading-tight group-hover:text-indigo-400 transition-colors">{{ res.title }}</h3>
                    <p class="text-sm text-gray-400 mb-4 line-clamp-3 flex-1">{{ res.description || 'No description provided.' }}</p>
                    
                    <div class="mt-auto">
                        <span class="text-xs bg-gray-700 px-2 py-1 rounded text-gray-300 mb-3 inline-block">{{ res.category }}</span>
                        <a :href="res.link" target="_blank" class="block w-full text-center bg-[#1e293b] hover:bg-indigo-600 text-indigo-400 hover:text-white border border-indigo-500/30 py-2 rounded-lg text-sm font-bold transition-all">
                            Open Resource
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Uploader Modal -->
        <div v-if="showUploader" class="absolute inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-[#1e293b] w-full max-w-md rounded-xl border border-gray-700 shadow-2xl p-6">
                <h3 class="font-bold text-lg mb-4">Add Learning Resource</h3>
                
                <div class="space-y-3">
                    <input v-model="form.title" type="text" placeholder="Resource Title" class="w-full bg-[#0f1218] border border-gray-700 rounded p-2 text-white outline-none focus:border-indigo-500">
                    
                    <div class="grid grid-cols-2 gap-3">
                        <select v-model="form.type" class="w-full bg-[#0f1218] border border-gray-700 rounded p-2 text-white outline-none">
                            <option v-for="t in resourceTypes" :key="t" :value="t">{{ t }}</option>
                        </select>
                        <select v-model="form.category" class="w-full bg-[#0f1218] border border-gray-700 rounded p-2 text-white outline-none">
                            <option v-for="c in categories.filter(x=>x!=='All')" :key="c" :value="c">{{ c }}</option>
                        </select>
                    </div>

                    <input v-model="form.link" type="url" placeholder="External Link (https://...)" class="w-full bg-[#0f1218] border border-gray-700 rounded p-2 text-white outline-none focus:border-indigo-500">

                    <div class="border-2 border-dashed border-gray-700 rounded p-4 text-center hover:border-indigo-500 transition-colors">
                        <input @change="handleFileUpload" type="file" class="hidden" id="res-upload">
                        <label for="res-upload" class="cursor-pointer text-sm text-gray-400">
                            <span v-if="form.file" class="text-white">{{ form.file.name }}</span>
                            <span v-else>Or Upload File</span>
                        </label>
                    </div>

                    <textarea v-model="form.description" rows="2" placeholder="Description (Optional)" class="w-full bg-[#0f1218] border border-gray-700 rounded p-2 text-white outline-none"></textarea>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button @click="showUploader = false" class="text-gray-400 hover:text-white">Cancel</button>
                    <button @click="submitResource" :disabled="uploading" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded font-bold disabled:opacity-50">
                        {{ uploading ? 'Saving...' : 'Save Resource' }}
                    </button>
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
