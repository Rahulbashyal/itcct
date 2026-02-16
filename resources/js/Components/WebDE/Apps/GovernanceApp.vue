<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);
const isAdmin = computed(() => user.value && (user.value.role === 'SuperAdmin' || user.value.role === 'Admin'));

const documents = ref([]);
const loading = ref(true);
const showUploader = ref(false);
const uploading = ref(false);

const form = ref({
    title: '',
    type: 'General',
    year: new Date().getFullYear(),
    description: '',
    file: null
});

const docTypes = ['Constitution', 'Meeting Minutes', 'Financial Report', 'Resolution', 'Policy', 'General'];
const years = computed(() => {
    const currentYear = new Date().getFullYear();
    const ys = [];
    for(let i=0; i<5; i++) ys.push(currentYear - i);
    return ys;
});

const fetchDocuments = async () => {
    try {
        const response = await axios.get('/api/v1/documents');
        documents.value = response.data.data || response.data; // Handle pagination or list
    } catch (error) {
        console.error("Failed to fetch docs", error);
    } finally {
        loading.value = false;
    }
};

const handleFileUpload = (event) => {
    form.value.file = event.target.files[0];
};

const uploadDocument = async () => {
    if (!form.value.title || !form.value.file) {
        alert("Title and File are required.");
        return;
    }

    uploading.value = true;
    const formData = new FormData();
    formData.append('title', form.value.title);
    formData.append('type', form.value.type);
    formData.append('year', form.value.year);
    formData.append('description', form.value.description);
    formData.append('file', form.value.file);
    formData.append('is_published', 1);

    try {
        await axios.post('/api/v1/documents', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        showUploader.value = false;
        form.value = { title: '', type: 'General', year: new Date().getFullYear(), description: '', file: null };
        fetchDocuments();
    } catch (error) {
        alert("Upload failed: " + (error.response?.data?.message || error.message));
    } finally {
        uploading.value = false;
    }
};

const deleteDocument = async (id) => {
    if (!confirm("Delete this document?")) return;
    try {
        await axios.delete(`/api/v1/documents/${id}`);
        fetchDocuments();
    } catch (error) {
        alert("Delete failed.");
    }
};

onMounted(fetchDocuments);
</script>

<template>
    <div class="governance-app h-full flex flex-col bg-[#1e293b] text-white">
        <!-- Toolbar -->
        <header class="h-14 bg-[#0f1218] border-b border-gray-700 flex items-center justify-between px-4 shrink-0">
            <h2 class="font-bold text-lg flex items-center gap-2">🏛️ Governance Repository</h2>
            <button v-if="isAdmin" @click="showUploader = true" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded text-sm font-bold flex items-center gap-2">
                <span>⬆</span> Upload
            </button>
        </header>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto p-6 scrollbar-thin">
            <div v-if="loading" class="flex justify-center py-20">
                <div class="animate-spin text-4xl">⏳</div>
            </div>

            <div v-else-if="documents.length === 0" class="text-center py-20 text-gray-500">
                <div class="text-4xl mb-4">📂</div>
                <p>No documents available.</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="doc in documents" :key="doc.id" class="bg-[#242c3e] border border-white/5 rounded-lg p-4 hover:border-blue-500/30 transition-all group relative">
                    <div class="flex items-start justify-between mb-2">
                         <div class="w-10 h-10 bg-red-500/20 text-red-400 rounded flex items-center justify-center text-xl">📄</div>
                         <div v-if="isAdmin">
                             <button @click="deleteDocument(doc.id)" class="text-gray-500 hover:text-red-400">🗑️</button>
                         </div>
                    </div>
                    
                    <h3 class="font-bold text-gray-200 truncate mb-1" :title="doc.title">{{ doc.title }}</h3>
                    <p class="text-xs text-gray-400 mb-4">{{ doc.type }} • {{ doc.year }}</p>
                    
                    <div class="flex justify-between items-center mt-auto">
                        <span class="text-[10px] text-gray-500">{{ new Date(doc.created_at).toLocaleDateString() }}</span>
                        <a :href="doc.file_path" target="_blank" download class="text-blue-400 hover:text-blue-300 text-sm font-bold flex items-center gap-1">
                            Download ⬇
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Uploader Modal -->
        <div v-if="showUploader" class="absolute inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-[#1e293b] w-full max-w-md rounded-xl border border-gray-700 shadow-2xl p-6">
                <h3 class="font-bold text-lg mb-4">Upload Document</h3>
                
                <div class="space-y-3">
                    <input v-model="form.title" type="text" placeholder="Document Title" class="w-full bg-[#0f1218] border border-gray-700 rounded p-2 text-white outline-none focus:border-blue-500">
                    
                    <div class="grid grid-cols-2 gap-3">
                        <select v-model="form.type" class="w-full bg-[#0f1218] border border-gray-700 rounded p-2 text-white outline-none">
                            <option v-for="t in docTypes" :key="t" :value="t">{{ t }}</option>
                        </select>
                        <select v-model="form.year" class="w-full bg-[#0f1218] border border-gray-700 rounded p-2 text-white outline-none">
                            <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                        </select>
                    </div>

                    <textarea v-model="form.description" rows="2" placeholder="Description (Optional)" class="w-full bg-[#0f1218] border border-gray-700 rounded p-2 text-white outline-none"></textarea>
                    
                    <div class="border-2 border-dashed border-gray-700 rounded p-4 text-center hover:border-blue-500 transition-colors">
                        <input @change="handleFileUpload" type="file" accept=".pdf,.doc,.docx,.txt" class="hidden" id="doc-upload">
                        <label for="doc-upload" class="cursor-pointer text-sm text-gray-400">
                            <span v-if="form.file" class="text-white">{{ form.file.name }}</span>
                            <span v-else>Click to Select File</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button @click="showUploader = false" class="text-gray-400 hover:text-white">Cancel</button>
                    <button @click="uploadDocument" :disabled="uploading" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-bold disabled:opacity-50">
                        {{ uploading ? 'Uploading...' : 'Upload' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.scrollbar-thin::-webkit-scrollbar { width: 6px; }
.scrollbar-thin::-webkit-scrollbar-thumb { background: #4b5563; border-radius: 3px; }
</style>
