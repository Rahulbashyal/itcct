<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import GlassCard from '@/Components/GlassCard.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref } from 'vue';

const props = defineProps({
    election: Object,
    candidatesByPosition: Object,
    userVotes: Array, // Array of positions the user already voted for
});

const submitVote = (candidateId) => {
    if (confirm('Are you sure you want to cast your ballot for this candidate? This action cannot be undone.')) {
        router.post(route('elections.vote', props.election.id), {
            candidate_id: candidateId
        }, {
            preserveScroll: true,
        });
    }
};

// Simplified UI layout for voting
</script>

<template>
    <Head :title="election.title" />

    <AuthenticatedLayout>
        <template #header_breadcrumb>
            Elections / {{ election.title }}
        </template>

        <div class="max-w-7xl mx-auto space-y-16">
            <!-- Election Header -->
            <div class="text-center space-y-4">
                <h1 class="text-4xl font-black">{{ election.title }}</h1>
                <p class="text-gray-500 max-w-2xl mx-auto">{{ election.description }}</p>
                <div class="flex justify-center gap-4 pt-4">
                    <div class="px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-xs font-bold text-gray-500">
                        OPEN: {{ new Date(election.start_date).toLocaleString() }}
                    </div>
                    <div class="px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-xs font-bold text-gray-500">
                        CLOSE: {{ new Date(election.end_date).toLocaleString() }}
                    </div>
                </div>
            </div>

            <!-- Position Categories -->
            <div v-for="(candidates, position) in candidatesByPosition" :key="position" class="space-y-8">
                <div class="flex items-center space-x-6">
                    <h2 class="text-2xl font-black tracking-tight text-primary-blue uppercase">{{ position }}</h2>
                    <div class="h-[1px] flex-1 bg-white/5"></div>
                    <div v-if="userVotes.includes(position)" class="flex items-center text-xs font-bold text-accent-green uppercase tracking-widest bg-accent-green/10 px-3 py-1 rounded-full border border-accent-green/20">
                         <svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                         Ballot Cast
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <GlassCard v-for="candidate in candidates" :key="candidate.id" class="relative group overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-primary-blue/5 rounded-full blur-3xl -mr-16 -mt-16 pointer-events-none"></div>
                        
                        <div class="flex flex-col items-center text-center space-y-6">
                            <!-- Candidate Avatar -->
                            <div class="w-24 h-24 rounded-3xl bg-white/5 border border-white/10 p-[1px] relative">
                                <div class="w-full h-full rounded-3xl bg-dark-gray flex items-center justify-center font-black text-3xl">
                                    {{ candidate.user.name.charAt(0) }}
                                </div>
                            </div>

                            <div class="space-y-1">
                                <h3 class="text-xl font-bold">{{ candidate.user.name }}</h3>
                                <p class="text-[10px] font-bold text-gray-600 uppercase tracking-widest">Candidate ID: {{ candidate.id }}</p>
                            </div>

                            <div class="italic text-sm text-gray-400 line-clamp-3 px-4">
                                "{{ candidate.vision_statement || 'Building a stronger future for the IT Club community.' }}"
                            </div>

                            <div class="w-full pt-6 border-t border-white/5">
                                <button 
                                    v-if="!userVotes.includes(position)"
                                    @click="submitVote(candidate.id)"
                                    class="w-full py-3 bg-primary-blue/10 hover:bg-primary-blue text-primary-blue hover:text-white border border-primary-blue/20 rounded-xl font-bold text-sm transition-all"
                                >
                                    Select Candidate
                                </button>
                                <div v-else class="w-full py-3 text-gray-600 font-bold text-xs uppercase tracking-widest">
                                    Selection Locked
                                </div>
                            </div>
                        </div>
                    </GlassCard>
                </div>
            </div>

            <!-- Empty State for Positions -->
            <div v-if="Object.keys(candidatesByPosition).length === 0" class="text-center py-20 border-2 border-dashed border-white/5 rounded-3xl">
                <p class="text-gray-500 italic">No candidates have filed for this election yet.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
