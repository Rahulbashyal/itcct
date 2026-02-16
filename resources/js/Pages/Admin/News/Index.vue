<script setup>
import GovernanceLayout from '@/Layouts/GovernanceLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import GlassCard from '@/Components/GlassCard.vue';
import FormInput from '@/Components/FormInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref } from 'vue';

const props = defineProps({
    news: Array
});

const showForm = ref(false);

const form = useForm({
    title: '',
    summary: '',
    content: '',
});

const submit = () => {
    form.post(route('admin.news.store'), {
        onSuccess: () => {
            showForm.value = false;
            form.reset();
        }
    });
};

const deleteNews = (id) => {
    if (confirm('Permanently delete this article?')) {
        router.delete(route('admin.news.destroy', id));
    }
};
</script>

<template>
    <Head title="News Factory" />

    <GovernanceLayout>
        <div class="space-y-12 text-white">
            <div class="flex justify-between items-center">
                <div>
                     <h1 class="text-4xl font-black tracking-tighter text-white mb-2">News Factory</h1>
                     <p class="text-gray-500 font-medium">Broadcast club updates to the entire member ecosystem.</p>
                </div>
                <PrimaryButton @click="showForm = !showForm">
                    {{ showForm ? 'Close Editor' : 'Compose News' }}
                </PrimaryButton>
            </div>

            <div v-if="showForm" class="animate-in slide-in-from-top duration-500">
                <GlassCard>
                    <form @submit.prevent="submit" class="space-y-6">
                        <FormInput label="Article Title" v-model="form.title" :error="form.errors.title" />
                        <FormInput label="Short Summary" v-model="form.summary" :error="form.errors.summary" placeholder="A one-line hook for the article card." />
                        
                        <div>
                             <label class="block text-[10px] font-black uppercase tracking-widest text-gray-500 mb-2">Full Content (Markdown Supported)</label>
                             <textarea 
                                v-model="form.content"
                                class="w-full bg-[#0a0b10] border border-white/10 rounded-2xl p-4 text-sm text-gray-200 outline-none focus:border-primary-blue min-h-[150px]"
                             ></textarea>
                        </div>

                        <PrimaryButton :disabled="form.processing" class="w-full justify-center">Publish Broadcast</PrimaryButton>
                    </form>
                </GlassCard>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <GlassCard v-for="item in news" :key="item.id" class="flex flex-col">
                    <div class="flex-1">
                         <div class="flex justify-between items-start mb-4">
                             <span class="text-[10px] font-black uppercase tracking-widest text-primary-blue bg-primary-blue/10 px-2 py-1 rounded inline-block">Published</span>
                             <button @click="deleteNews(item.id)" class="text-gray-600 hover:text-red-400">
                                 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                             </button>
                         </div>
                         <h3 class="text-xl font-bold mb-2">{{ item.title }}</h3>
                         <p class="text-sm text-gray-500 mb-4">{{ item.summary }}</p>
                    </div>
                    <div class="pt-4 border-t border-white/5 flex justify-between items-center text-[10px] font-bold text-gray-600 uppercase tracking-widest">
                        <span>By {{ item.author.name }}</span>
                        <span>{{ new Date(item.created_at).toLocaleDateString() }}</span>
                    </div>
                </GlassCard>
            </div>
        </div>
    </GovernanceLayout>
</template>
