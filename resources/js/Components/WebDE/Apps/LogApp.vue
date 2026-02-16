<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const logs = ref([]);
const loading = ref(false);
const currentPage = ref(1);
const totalPages = ref(1);

const fetchLogs = async (page = 1) => {
    loading.value = true;
    try {
        const response = await axios.get(`/api/v1/system-logs?page=${page}`);
        logs.value = response.data.data;
        currentPage.value = response.data.current_page;
        totalPages.value = response.data.last_page;
    } catch (error) {
        console.error("Failed to fetch logs", error);
    } finally {
        loading.value = false;
    }
};

const getActionColor = (action) => {
    if (action.includes('login')) return 'text-green-400';
    if (action.includes('delete')) return 'text-red-400';
    if (action.includes('update')) return 'text-blue-400';
    return 'text-gray-300';
};

onMounted(() => {
    fetchLogs();
});
</script>

<template>
    <div class="log-app h-full flex flex-col bg-[#1e1e1e] text-white">
        <!-- Toolbar -->
        <div class="h-12 bg-[#252526] border-b border-[#333] flex items-center justify-between px-4">
            <span class="font-bold text-gray-200">System Logs</span>
            <button 
                @click="fetchLogs(currentPage)" 
                class="text-sm bg-[#333] hover:bg-[#444] px-3 py-1 rounded transition-colors"
                :disabled="loading"
            >
                🔄 Refresh
            </button>
        </div>

        <!-- Table -->
        <div class="flex-1 overflow-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead class="bg-[#2d2d2d] sticky top-0">
                    <tr>
                        <th class="p-3 border-b border-[#444] font-medium text-gray-400">Time</th>
                        <th class="p-3 border-b border-[#444] font-medium text-gray-400">User</th>
                        <th class="p-3 border-b border-[#444] font-medium text-gray-400">Action</th>
                        <th class="p-3 border-b border-[#444] font-medium text-gray-400">Description</th>
                        <th class="p-3 border-b border-[#444] font-medium text-gray-400">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#333]">
                    <tr v-for="log in logs" :key="log.id" class="hover:bg-[#2a2a2a] transition-colors">
                        <td class="p-3 text-gray-400 whitespace-nowrap">
                            {{ new Date(log.created_at).toLocaleString() }}
                        </td>
                        <td class="p-3 font-medium">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-gray-700 flex items-center justify-center text-xs">
                                    {{ log.user?.name?.substring(0, 1) || '?' }}
                                </span>
                                <span>{{ log.user?.name || 'System' }}</span>
                            </div>
                        </td>
                        <td class="p-3 font-mono text-xs" :class="getActionColor(log.action.toLowerCase())">
                            {{ log.action }}
                        </td>
                        <td class="p-3 text-gray-300">{{ log.description }}</td>
                        <td class="p-3 text-gray-500 font-mono text-xs">{{ log.ip_address }}</td>
                    </tr>
                    <tr v-if="logs.length === 0 && !loading">
                        <td colspan="5" class="p-8 text-center text-gray-500">
                            No logs found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="h-10 bg-[#252526] border-t border-[#333] flex items-center justify-between px-4 text-xs text-gray-400">
            <span>Page {{ currentPage }} of {{ totalPages }}</span>
            <div class="flex gap-2">
                <button 
                    @click="fetchLogs(currentPage - 1)" 
                    :disabled="currentPage <= 1 || loading"
                    class="disabled:opacity-50 hover:text-white"
                >
                    &lt; Prev
                </button>
                <button 
                    @click="fetchLogs(currentPage + 1)" 
                    :disabled="currentPage >= totalPages || loading"
                    class="disabled:opacity-50 hover:text-white"
                >
                    Next &gt;
                </button>
            </div>
        </div>
    </div>
</template>
