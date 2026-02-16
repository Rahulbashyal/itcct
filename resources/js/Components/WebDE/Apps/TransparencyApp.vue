<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js';
import { Doughnut } from 'vue-chartjs';

ChartJS.register(ArcElement, Tooltip, Legend);

const loading = ref(true);
const financialData = ref({ income: 0, expense: 0, balance: 0 });
const transactions = ref([]);
const receiptModalOpen = ref(false);
const selectedReceipt = ref(null);
const createModalOpen = ref(false);

const props = defineProps({
    user: Object
});

const isTreasurer = computed(() => {
    return props.user?.role === 'Treasurer' || props.user?.role === 'SuperAdmin';
});

const form = ref({
    amount: '',
    type: 'income',
    category: '',
    description: '',
    transaction_date: new Date().toISOString().split('T')[0],
    receipt: null
});

const handleFileUpload = (e) => {
    form.value.receipt = e.target.files[0];
};

const submitTransaction = async () => {
    const formData = new FormData();
    Object.keys(form.value).forEach(key => {
        formData.append(key, form.value[key]);
    });

    try {
        await axios.post('/api/v1/transparency/data', formData);
        createModalOpen.value = false;
        fetchFinancials();
        // Reset form
        form.value = {
            amount: '',
            type: 'income',
            category: '',
            description: '',
            transaction_date: new Date().toISOString().split('T')[0],
            receipt: null
        };
    } catch (error) {
        alert('Failed to record transaction');
    }
};

const chartData = computed(() => {
    return {
        labels: ['Income', 'Expense'],
        datasets: [{
            backgroundColor: ['#10b981', '#ef4444'],
            data: [financialData.value.income, financialData.value.expense]
        }]
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'bottom', labels: { color: '#fff' } }
    }
};

const fetchFinancials = async () => {
    try {
        const response = await axios.get('/api/v1/transparency/data');
        financialData.value = response.data.financials;
        transactions.value = response.data.transactions;
    } catch (error) {
        console.error('Failed to load financial data:', error);
    } finally {
        loading.value = false;
    }
};

const openReceipt = (url) => {
    if (!url) return;
    selectedReceipt.value = url;
    receiptModalOpen.value = true;
};

const closeReceipt = () => {
    receiptModalOpen.value = false;
    selectedReceipt.value = null;
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-NP', { style: 'currency', currency: 'NPR' }).format(amount);
};

onMounted(() => {
    fetchFinancials();
});
</script>

<template>
    <div class="transparency-app h-full flex flex-col bg-[#1a1b26] text-white overflow-hidden">
        <!-- Header -->
        <header class="p-6 border-b border-white/5 flex justify-between items-center bg-[#13141f]">
            <div>
                <h2 class="text-2xl font-bold text-cyan-400">🛡️ Transparency Portal</h2>
                <p class="text-xs text-gray-400">Open Ledger & Financial Reporting</p>
            </div>
            <div class="text-right">
                <div class="text-xs text-gray-400">Current Balance</div>
                <div class="text-2xl font-mono font-bold text-emerald-400">{{ formatCurrency(financialData.balance) }}</div>
            </div>
        </header>

        <div v-if="loading" class="flex-1 flex items-center justify-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-cyan-400"></div>
        </div>

        <div v-else class="flex-1 flex overflow-hidden">
            <!-- Left Panel: Stats -->
            <aside class="w-1/3 p-6 border-r border-white/5 bg-[#16161e] flex flex-col">
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-gray-400 mb-4 uppercase tracking-wider">Financial Overview</h3>
                    <div class="h-64">
                        <Doughnut :data="chartData" :options="chartOptions" />
                    </div>
                </div>
                
                <div class="space-y-4 mt-auto">
                    <div class="bg-gray-800/50 p-4 rounded-lg flex justify-between">
                        <span class="text-gray-400">Total Income</span>
                        <span class="text-emerald-400 font-bold">+{{ formatCurrency(financialData.income) }}</span>
                    </div>
                    <div class="bg-gray-800/50 p-4 rounded-lg flex justify-between">
                        <span class="text-gray-400">Total Expense</span>
                        <span class="text-red-400 font-bold">-{{ formatCurrency(financialData.expense) }}</span>
                    </div>
                </div>
            </aside>

            <!-- Right Panel: Ledger -->
            <main class="flex-1 flex flex-col">
                <div class="p-4 bg-[#13141f] border-b border-white/5 flex justify-between items-center">
                    <h3 class="font-semibold">Recent Transactions</h3>
                    <div class="flex gap-2">
                        <button 
                            v-if="isTreasurer"
                            @click="createModalOpen = true"
                            class="px-3 py-1 bg-emerald-500/10 text-emerald-400 rounded text-xs hover:bg-emerald-500/20 transition-colors border border-emerald-500/20"
                        >
                            + Add Transaction
                        </button>
                        <button class="px-3 py-1 bg-cyan-500/10 text-cyan-400 rounded text-xs hover:bg-cyan-500/20 transition-colors">
                            Download Report
                        </button>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-400 uppercase bg-gray-800/30 sticky top-0 backdrop-blur-sm">
                            <tr>
                                <th class="px-6 py-3">Date</th>
                                <th class="px-6 py-3">Description</th>
                                <th class="px-6 py-3">Category</th>
                                <th class="px-6 py-3 text-right">Amount</th>
                                <th class="px-6 py-3 text-center">Receipt</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800/50">
                            <tr v-for="t in transactions" :key="t.id" class="hover:bg-white/5 transition-colors">
                                <td class="px-6 py-4 font-mono text-gray-400">{{ t.date }}</td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-white">{{ t.description }}</div>
                                    <div class="text-xs text-gray-500">By {{ t.user }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium"
                                        :class="t.type === 'income' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-500'">
                                        {{ t.category }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-mono"
                                    :class="t.type === 'income' ? 'text-emerald-400' : 'text-red-400'">
                                    {{ t.type === 'income' ? '+' : '-' }}{{ formatCurrency(t.amount) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button 
                                        v-if="t.receipt" 
                                        @click="openReceipt(t.receipt)"
                                        class="text-cyan-400 hover:text-cyan-300 transition-colors"
                                        title="View Receipt"
                                    >
                                        <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </button>
                                    <span v-else class="text-gray-600">-</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>

        <!-- Receipt Modal -->
        <div v-if="receiptModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm" @click.self="closeReceipt">
            <div class="bg-[#1a1b26] p-2 rounded-lg max-w-2xl w-full mx-4 shadow-2xl relative">
                <button @click="closeReceipt" class="absolute -top-4 -right-4 bg-red-500 text-white rounded-full p-2 hover:bg-red-600 shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
                <img :src="selectedReceipt" alt="Receipt" class="w-full h-auto rounded border border-white/10">
            </div>
        </div>
        <!-- Create Transaction Modal -->
        <div v-if="createModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm" @click.self="createModalOpen = false">
            <div class="bg-[#1a1b26] p-6 rounded-xl max-w-md w-full mx-4 shadow-2xl border border-white/10">
                <h3 class="text-xl font-bold mb-4">Record Transaction</h3>
                <form @submit.prevent="submitTransaction" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Type</label>
                            <select v-model="form.type" class="w-full bg-gray-800 border-none rounded p-2 text-sm">
                                <option value="income">Income</option>
                                <option value="expense">Expense</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Amount (NPR)</label>
                            <input type="number" v-model="form.amount" class="w-full bg-gray-800 border-none rounded p-2 text-sm" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Category</label>
                        <input type="text" v-model="form.category" placeholder="e.g., Sponsorship, Event Cost" class="w-full bg-gray-800 border-none rounded p-2 text-sm" required>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Description</label>
                        <input type="text" v-model="form.description" class="w-full bg-gray-800 border-none rounded p-2 text-sm" required>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Date</label>
                        <input type="date" v-model="form.transaction_date" class="w-full bg-gray-800 border-none rounded p-2 text-sm" required>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Receipt (Image)</label>
                        <input type="file" @change="handleFileUpload" class="text-xs text-gray-400">
                    </div>
                    <div class="flex gap-3 mt-6">
                        <button type="button" @click="createModalOpen = false" class="flex-1 px-4 py-2 rounded bg-gray-800 hover:bg-gray-700 transition-colors">Cancel</button>
                        <button type="submit" class="flex-1 px-4 py-2 rounded bg-emerald-600 hover:bg-emerald-500 transition-colors font-bold">Save Record</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Custom Scrollbar */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}
.overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}
.overflow-y-auto::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 3px;
}
.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.2);
}
</style>
