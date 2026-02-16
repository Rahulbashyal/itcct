<script setup>
import GovernanceLayout from '@/Layouts/GovernanceLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import GlassCard from '@/Components/GlassCard.vue';
import FormInput from '@/Components/FormInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref } from 'vue';

const props = defineProps({
    events: Array
});

const showForm = ref(false);

const form = useForm({
    title: '',
    description: '',
    event_date: '',
    location: '',
});

const submit = () => {
    form.post(route('admin.events.store'), {
        onSuccess: () => {
            showForm.value = false;
            form.reset();
        }
    });
};

const deleteEvent = (id) => {
    if (confirm('Cancel this event?')) {
        router.delete(route('admin.events.destroy', id));
    }
};
</script>

<template>
    <Head title="Event Nexus" />

    <GovernanceLayout>
        <div class="space-y-12 text-white text-dark">
            <div class="flex justify-between items-center">
                <div>
                     <h1 class="text-4xl font-black tracking-tighter text-white mb-2">Event Nexus</h1>
                     <p class="text-gray-500 font-medium">Coordinate and schedule the tactical operations of IT Club.</p>
                </div>
                <PrimaryButton @click="showForm = !showForm">
                    {{ showForm ? 'Close Nexus' : 'Schedule Event' }}
                </PrimaryButton>
            </div>

            <div v-if="showForm" class="animate-in slide-in-from-top duration-500">
                <GlassCard>
                    <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <FormInput label="Event Name" v-model="form.title" :error="form.errors.title" />
                        </div>
                        <div class="md:col-span-2">
                             <label class="block text-[10px] font-black uppercase tracking-widest text-gray-500 mb-2">Description / Objectives</label>
                             <textarea 
                                v-model="form.description"
                                class="w-full bg-[#0a0b10] border border-white/10 rounded-2xl p-4 text-sm text-gray-200 outline-none focus:border-primary-blue min-h-[100px]"
                             ></textarea>
                        </div>
                        <FormInput label="Date & Time" v-model="form.event_date" type="datetime-local" :error="form.errors.event_date" />
                        <FormInput label="Deployment Location" v-model="form.location" :error="form.errors.location" placeholder="e.g. Lab 404, Main Hall" />

                        <div class="md:col-span-2 pt-4">
                            <PrimaryButton :disabled="form.processing" class="w-full justify-center text-dark">Propagate Event</PrimaryButton>
                        </div>
                    </form>
                </GlassCard>
            </div>

            <GlassCard class="p-0 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white/[0.02] border-b border-white/5">
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-gray-500">Event</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-gray-500">Deployment</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-gray-500">Status</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-gray-500 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="event in events" :key="event.id" class="hover:bg-white/[0.01] transition-colors">
                            <td class="px-6 py-5">
                                <p class="text-sm font-bold text-white">{{ event.title }}</p>
                                <p class="text-[10px] text-gray-600 font-medium">{{ event.location }}</p>
                            </td>
                            <td class="px-6 py-5">
                                <span class="text-xs font-mono text-gray-500">{{ new Date(event.event_date).toLocaleString() }}</span>
                            </td>
                            <td class="px-6 py-5">
                                <span class="text-[10px] font-black uppercase tracking-widest text-accent-green bg-accent-green/10 px-2 py-1 rounded inline-block">Active</span>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <button @click="deleteEvent(event.id)" class="text-red-500/50 hover:text-red-500 transition-colors uppercase text-[10px] font-black">Cancel</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </GlassCard>
        </div>
    </GovernanceLayout>
</template>
