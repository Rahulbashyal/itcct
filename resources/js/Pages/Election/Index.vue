<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import GlassCard from '@/Components/GlassCard.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    elections: Array
});

// Sample for preview
const sampleElections = [
    { id: 1, title: '2082 Executive Committee Election', description: 'Annual election for the core positions of the IT Club.', status: 'live', start_date: '2026-02-13 00:00:00', end_date: '2026-02-15 23:59:59' },
    { id: 2, title: 'Project Lead Selection (Batch 2081)', description: 'Internal selection for major club projects.', status: 'live', start_date: '2026-02-14 00:00:00', end_date: '2026-02-16 23:59:59' }
];

const activeElections = props.elections.length > 0 ? props.elections : sampleElections;
</script>

<template>
    <Head title="Election Portal" />

    <AuthenticatedLayout>
        <template #header_breadcrumb>
            Governance / Elections
        </template>

        <div class="max-w-6xl mx-auto space-y-12">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h1 class="text-4xl font-extrabold tracking-tight mb-2">Electronic Voting</h1>
                    <p class="text-gray-400">Cast your ballot securely and help shape the future of IT Club.</p>
                </div>
                <div class="px-6 py-4 bg-primary-blue/10 border border-primary-blue/20 rounded-2xl flex items-center">
                    <span class="w-3 h-3 bg-accent-green rounded-full mr-3 animate-ping"></span>
                    <span class="text-sm font-bold text-primary-blue">Digital Ballot System Active</span>
                </div>
            </div>

            <!-- Active Elections Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <GlassCard v-for="election in activeElections" :key="election.id" class="relative group">
                    <div class="flex flex-col h-full">
                        <div class="mb-6 flex justify-between items-start">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-secondary-teal bg-secondary-teal/10 border border-secondary-teal/20 px-3 py-1 rounded-full">
                                {{ election.status }}
                            </span>
                            <span class="text-xs text-gray-600 font-mono italic">
                                Ends: {{ new Date(election.end_date).toLocaleDateString() }}
                            </span>
                        </div>

                        <h3 class="text-2xl font-bold mb-3 group-hover:text-primary-blue transition-colors">{{ election.title }}</h3>
                        <p class="text-sm text-gray-500 leading-relaxed mb-8 flex-1">
                            {{ election.description }}
                        </p>

                        <div class="pt-6 border-t border-white/5 flex items-center justify-between">
                            <div class="flex -space-x-3">
                                <div v-for="i in 3" :key="i" class="w-8 h-8 rounded-full border-2 border-dark-gray bg-white/5 flex items-center justify-center font-bold text-[10px]">
                                    ?
                                </div>
                            </div>
                            <Link :href="route('elections.show', election.id)">
                                <PrimaryButton class="text-xs px-6 py-2">Enter Portal</PrimaryButton>
                            </Link>
                        </div>
                    </div>
                </GlassCard>
            </div>

            <!-- Security Notice -->
            <GlassCard class="bg-gradient-to-br from-primary-blue/5 to-transparent border-primary-blue/10 p-8 text-center italic">
                <p class="text-gray-500 text-sm">
                    "Every vote is recorded with a unique digital signature and timestamp. Double voting is technically impossible within the Digital Vault framework."
                </p>
            </GlassCard>
        </div>
    </AuthenticatedLayout>
</template>
