<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import GlassCard from '@/Components/GlassCard.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref } from 'vue';

const props = defineProps({
    files: Array,
});

const form = useForm({
    file: null,
});

const fileInput = ref(null);

const submit = () => {
    form.post(route('vault.store'), {
        onSuccess: () => {
            form.reset();
            if (fileInput.value) fileInput.value.value = '';
        },
    });
};

const formatSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};
</script>

<template>
    <Head title="Digital Vault" />

    <AuthenticatedLayout>
        <template #header_breadcrumb>
            Resources / Digital Vault
        </template>

        <div class="max-w-6xl mx-auto space-y-12">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h1 class="text-4xl font-extrabold tracking-tight mb-2">Digital Vault</h1>
                    <p class="text-gray-400">Secure storage for club documents, assets, and project files.</p>
                </div>
                
                <!-- Upload Area -->
                <GlassCard class="w-full md:w-auto p-4 flex items-center gap-4">
                    <input 
                        type="file" 
                        ref="fileInput"
                        @input="form.file = $event.target.files[0]"
                        class="hidden"
                        id="vault-upload"
                    >
                    <label for="vault-upload" class="cursor-pointer px-4 py-2 border border-white/10 rounded-xl text-sm font-bold hover:bg-white/5 transition-colors">
                        {{ form.file ? form.file.name : 'Choose File' }}
                    </label>
                    <PrimaryButton @click="submit" :disabled="!form.file || form.processing">
                        <span v-if="form.processing">Uploading...</span>
                        <span v-else>Upload</span>
                    </PrimaryButton>
                </GlassCard>
            </div>

            <!-- Files Grid -->
            <div v-if="files.length > 0" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <GlassCard v-for="file in files" :key="file.id" class="flex flex-col h-full group">
                    <div class="flex-1 flex flex-col items-center justify-center p-6 text-center">
                        <!-- Icon based on mime -->
                        <div class="w-16 h-16 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                             <svg v-if="file.mime_type.includes('image')" class="w-8 h-8 text-primary-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                             <svg v-else-if="file.mime_type.includes('pdf')" class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                             <svg v-else class="w-8 h-8 text-secondary-teal" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <h3 class="font-bold text-sm truncate w-full mb-1">{{ file.name }}</h3>
                        <p class="text-[10px] text-gray-500 uppercase tracking-widest">{{ formatSize(file.size) }}</p>
                    </div>

                    <div class="mt-auto p-4 border-t border-white/5 flex gap-2">
                         <a 
                            :href="route('vault.download', file.id)" 
                            class="flex-1 py-2 bg-white/5 hover:bg-white/10 rounded-lg text-[10px] font-bold uppercase tracking-widest text-center transition-colors"
                         >
                            Download
                         </a>
                    </div>
                </GlassCard>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-32 border-2 border-dashed border-white/5 rounded-3xl">
                <svg class="w-16 h-16 text-white/5 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                <h4 class="text-xl font-bold text-gray-500">Vault is empty</h4>
                <p class="text-sm text-gray-600">Start by uploading important club documents.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
