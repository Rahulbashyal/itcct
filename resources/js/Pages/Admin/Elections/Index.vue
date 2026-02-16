<script setup>
import GovernanceLayout from '@/Layouts/GovernanceLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import GlassCard from '@/Components/GlassCard.vue';
import FormInput from '@/Components/FormInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref } from 'vue';

const props = defineProps({
    elections: Array
});

const showCreateForm = ref(false);

const form = useForm({
    title: '',
    description: '',
    start_date: '',
    end_date: '',
});

const submit = () => {
    form.post(route('admin.elections.store'), {
        onSuccess: () => {
            showCreateForm.ref = false;
            form.reset();
        }
    });
};

const toggleElection = (id) => {
    router.patch(route('admin.elections.toggle', id));
};
</script>

<template>
    <Head title="Election Command" />

    <GovernanceLayout>
        <div class="space-y-12">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-black tracking-tighter text-white mb-2 text-primary-blue">Election Command</h1>
                    <p class="text-gray-500 font-medium">Control digital democracy and manage electoral protocols.</p>
                </div>
                <PrimaryButton @click="showCreateForm = !showCreateForm">
                    {{ showCreateForm ? 'Close Engine' : 'Initialize New Election' }}
                </PrimaryButton>
            </div>

            <!-- Create Form -->
            <div v-if="showCreateForm" class="animate-in slide-in-from-top duration-500">
                <GlassCard>
                    <h2 class="text-xl font-bold mb-6">Election Configuration</h2>
                    <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                             <FormInput label="Election Title" v-model="form.title" :error="form.errors.title" placeholder="e.g. 2082 Executive Committee" />
                        </div>
                        <div class="md:col-span-2">
                             <label class="block text-[10px] font-black uppercase tracking-widest text-gray-500 mb-2">Manifesto Guidelines / Description</label>
                             <textarea 
                                v-model="form.description"
                                class="w-full bg-[#0a0b10] border border-white/10 rounded-2xl p-4 text-sm text-gray-200 outline-none focus:border-primary-blue transition-colors min-h-[100px]"
                             ></textarea>
                        </div>
                        <FormInput label="Start Date & Time" v-model="form.start_date" type="datetime-local" :error="form.errors.start_date" />
                        <FormInput label="End Date & Time" v-model="form.end_date" type="datetime-local" :error="form.errors.end_date" />
                        
                        <div class="md:col-span-2 pt-4">
                            <PrimaryButton :disabled="form.processing" class="w-full justify-center">Launch Election Framework</PrimaryButton>
                        </div>
                    </form>
                </GlassCard>
            </div>

            <!-- Elections Table -->
            <GlassCard class="p-0 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white/[0.02] border-b border-white/5">
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-gray-500">Election</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-gray-500">Phase</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-gray-500">Candidates</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-gray-500 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="election in elections" :key="election.id" class="hover:bg-white/[0.01] transition-colors">
                            <td class="px-6 py-5">
                                <p class="text-sm font-bold text-white">{{ election.title }}</p>
                                <p class="text-[10px] text-gray-600 font-medium whitespace-nowrap">
                                    {{ new Date(election.start_date).toLocaleDateString() }} - {{ new Date(election.end_date).toLocaleDateString() }}
                                </p>
                            </td>
                            <td class="px-6 py-5">
                                <span class="text-[10px] font-black uppercase tracking-widest" :class="election.is_active ? 'text-accent-green' : 'text-gray-500'">
                                    {{ election.is_active ? 'LIVE BALLOT' : 'DRAFT PHASE' }}
                                </span>
                            </td>
                            <td class="px-6 py-5">
                                <span class="text-xs font-bold text-primary-blue bg-primary-blue/10 px-2 py-1 rounded">
                                    {{ election.candidates_count }} Registered
                                </span>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <button 
                                    @click="toggleElection(election.id)"
                                    class="text-[10px] font-black uppercase tracking-widest px-4 py-2 rounded-xl transition-all border"
                                    :class="election.is_active ? 'border-red-500/50 text-red-500 hover:bg-red-500/10' : 'border-accent-green/50 text-accent-green hover:bg-accent-green/10'"
                                >
                                    {{ election.is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </GlassCard>
        </div>
    </GovernanceLayout>
</template>
