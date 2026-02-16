<script setup>
import GovernanceLayout from '@/Layouts/GovernanceLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import GlassCard from '@/Components/GlassCard.vue';
import FormInput from '@/Components/FormInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref } from 'vue';

const props = defineProps({
    transactions: Array,
    summary: Object
});

const showEntryForm = ref(false);

const form = useForm({
    amount: '',
    type: 'expense',
    category: '',
    description: '',
    transaction_date: new Date().toISOString().split('T')[0],
});

const submit = () => {
    form.post(route('admin.finance.store'), {
        onSuccess: () => {
            showEntryForm.value = false;
            form.reset('amount', 'category', 'description');
        }
    });
};

const categories = ['Hardware', 'Software', 'Event', 'Stationery', 'Refreshments', 'Membership Fee', 'Sponsorship', 'Miscellaneous'];
</script>

<template>
    <Head title="Financial Ledger" />

    <GovernanceLayout>
        <div class="space-y-12">
            <!-- Header & Summary -->
            <div class="flex flex-col md:flex-row justify-between gap-8">
                <div>
                    <h1 class="text-4xl font-black tracking-tighter text-white mb-2">Financial Ledger</h1>
                    <p class="text-gray-500 font-medium">Radical transparency starts here. Record every transaction.</p>
                </div>
                
                <div class="flex gap-4">
                    <GlassCard class="px-6 py-4 border-accent-green/20">
                        <p class="text-[10px] font-black uppercase text-gray-500 mb-1">Balance</p>
                        <h4 class="text-2xl font-black text-white">Rs. {{ summary.balance }}</h4>
                    </GlassCard>
                    <PrimaryButton @click="showEntryForm = !showEntryForm">
                        {{ showEntryForm ? 'Close Entry' : 'Record Transaction' }}
                    </PrimaryButton>
                </div>
            </div>

            <!-- Transaction Form -->
            <div v-if="showEntryForm" class="animate-in slide-in-from-top duration-500">
                <GlassCard>
                    <h2 class="text-xl font-bold mb-6">Ledger Entry</h2>
                    <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-3 gap-6 text-white">
                        <FormInput label="Amount (NPR)" v-model="form.amount" type="number" step="0.01" :error="form.errors.amount" />
                        
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-gray-500 mb-2">Type</label>
                            <select v-model="form.type" class="w-full bg-[#0a0b10] border border-white/10 rounded-2xl p-4 text-sm text-gray-200 outline-none focus:border-primary-blue">
                                <option value="income">Income (+)</option>
                                <option value="expense">Expense (-)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-gray-500 mb-2">Category</label>
                            <select v-model="form.category" class="w-full bg-[#0a0b10] border border-white/10 rounded-2xl p-4 text-sm text-gray-200 outline-none focus:border-primary-blue">
                                <option value="" disabled>Select Category</option>
                                <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <FormInput label="Description" v-model="form.description" :error="form.errors.description" placeholder="e.g. Cables for networking workshop" />
                        </div>

                        <FormInput label="Date" v-model="form.transaction_date" type="date" :error="form.errors.transaction_date" />

                        <div class="md:col-span-3 pt-4">
                            <PrimaryButton :disabled="form.processing" class="w-full justify-center">Commit to Ledger</PrimaryButton>
                        </div>
                    </form>
                </GlassCard>
            </div>

            <!-- Transactions Table -->
            <GlassCard class="p-0 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white/[0.02] border-b border-white/5">
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-gray-500">Date</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-gray-500">Description</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-gray-500">Category</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-gray-500 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="tx in transactions" :key="tx.id" class="hover:bg-white/[0.01] transition-colors">
                            <td class="px-6 py-5">
                                <span class="text-xs font-mono text-gray-500">{{ new Date(tx.transaction_date).toLocaleDateString() }}</span>
                            </td>
                            <td class="px-6 py-5">
                                <p class="text-sm font-bold text-white">{{ tx.description || 'No description' }}</p>
                                <p class="text-[10px] text-gray-600 font-medium whitespace-nowrap uppercase tracking-tighter">Recorded by {{ tx.user.name }}</p>
                            </td>
                            <td class="px-6 py-5">
                                <span class="text-[10px] font-black uppercase tracking-widest text-primary-blue bg-primary-blue/10 px-2 py-1 rounded">
                                    {{ tx.category }}
                                </span>
                            </td>
                            <td class="px-6 py-5 text-right font-mono font-bold" :class="tx.type === 'income' ? 'text-accent-green' : 'text-red-400'">
                                {{ tx.type === 'income' ? '+' : '-' }} Rs. {{ tx.amount }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </GlassCard>
        </div>
    </GovernanceLayout>
</template>
