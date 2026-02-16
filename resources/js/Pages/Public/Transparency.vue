<script setup>
import { Head } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import GlassCard from '@/Components/GlassCard.vue';

const props = defineProps({
    transactions: Array,
    summary: Object
});

const stats = [
    { label: 'Total Treasury', value: ` रू ${props.summary.balance}`, sub: 'Cash on Hand', color: 'text-accent-green' },
    { label: 'Total Income', value: ` रू ${props.summary.income}`, sub: '2081-82 Session', color: 'text-primary-blue' },
    { label: 'Total Expenses', value: ` रू ${props.summary.expense}`, sub: 'Invested into Innovation', color: 'text-red-400' },
];
</script>

<template>
    <Head title="Transparency Portal" />

    <GuestLayout>
        <div class="py-20 px-6 max-w-7xl mx-auto space-y-24">
            <div class="text-center">
                 <div class="inline-flex items-center px-4 py-2 rounded-full bg-accent-green/10 border border-accent-green/20 text-accent-green text-[10px] font-bold tracking-widest uppercase mb-6">
                    Real-time Governance
                </div>
                <h1 class="text-5xl font-black mb-4 tracking-tighter">Radical <span class="text-secondary-teal">Transparency</span></h1>
                <p class="text-gray-500 max-w-2xl mx-auto font-medium">
                    The first club portal in Nepal to offer real-time financial tracking. We believe trust is built through open data and accountable leadership.
                </p>
            </div>

            <!-- Dashboard Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <GlassCard v-for="stat in stats" :key="stat.label" class="relative group">
                    <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-white/5 rounded-full blur-2xl group-hover:bg-white/10 transition-colors"></div>
                    <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-3">{{ stat.label }}</p>
                    <h3 class="text-3xl font-black mb-1" :class="stat.color">{{ stat.value }}</h3>
                    <p class="text-[10px] text-gray-600 font-bold uppercase tracking-tight">{{ stat.sub }}</p>
                </GlassCard>
            </div>

            <!-- Ledger List -->
            <div class="space-y-8">
                <div class="flex items-center space-x-6">
                    <h2 class="text-2xl font-black tracking-tight text-white uppercase">Live Audit Trail</h2>
                    <div class="h-[1px] flex-1 bg-white/5"></div>
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Showing Last 20 Entries</span>
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <div v-for="tx in transactions" :key="tx.id" class="flex items-center justify-between p-6 bg-white/[0.02] border border-white/5 rounded-3xl hover:border-white/10 transition-all group">
                        <div class="flex items-center space-x-6">
                            <div class="w-12 h-12 rounded-2xl bg-white/5 flex items-center justify-center text-xs font-mono text-gray-500">
                                {{ new Date(tx.transaction_date).getDate() }}<br>{{ new Date(tx.transaction_date).toLocaleString('default', { month: 'short' }) }}
                            </div>
                            <div>
                                <p class="text-base font-bold text-gray-200">{{ tx.description }}</p>
                                <span class="text-[10px] uppercase font-black tracking-widest text-primary-blue">{{ tx.category }}</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-black font-mono tracking-tighter" :class="tx.type === 'income' ? 'text-accent-green' : 'text-red-400'">
                                {{ tx.type === 'income' ? '+' : '-' }} रू {{ tx.amount }}
                            </p>
                            <p class="text-[10px] text-gray-600 font-bold uppercase tracking-widest">Verified Ballot</p>
                        </div>
                    </div>

                    <div v-if="transactions.length === 0" class="text-center py-20 border-2 border-dashed border-white/5 rounded-3xl">
                        <p class="text-gray-500 font-bold uppercase text-xs tracking-widest italic">No public entries recorded for this session yet.</p>
                    </div>
                </div>
            </div>

            <!-- Security Commitment -->
            <GlassCard class="bg-gradient-to-br from-primary-blue/5 to-transparent border-primary-blue/10 p-12 text-center">
                 <div class="max-w-xl mx-auto">
                    <p class="text-lg font-medium text-gray-400 mb-6 italic">
                        "Financial integrity is the bedrock of our innovation. Every rupee contributed by members or sponsors is tracked precisely and displayed here for the world to see."
                    </p>
                    <div class="w-16 h-1 bg-primary-blue mx-auto mb-6"></div>
                    <span class="text-xs font-black uppercase tracking-[0.3em] text-gray-500">Executive Committee of CCT IT Club</span>
                 </div>
            </GlassCard>
        </div>
    </GuestLayout>
</template>
