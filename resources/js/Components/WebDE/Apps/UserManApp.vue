<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
    isEmbedded: { type: Boolean, default: false }
});

const page = usePage();
const currentUser = computed(() => props.user || page.props.auth.user);
// Only SuperAdmin can access this app
const isSuperAdmin = computed(() => currentUser.value && currentUser.value.role === 'SuperAdmin');

const users = ref([]);
const loading = ref(true);
const processing = ref(null);

const roles = ['User', 'Admin', 'SuperAdmin', 'Alumni'];

const fetchUsers = async () => {
    try {
        // Need an API endpoint for this. 
        // I'll assume I need to create one or use an existing one. 
        // AdminController in web.php exists but maybe not API.
        // I'll use a new endpoint /api/v1/admin/users
        const response = await axios.get('/api/v1/admin/users');
        users.value = response.data.data || response.data;
    } catch (error) {
        console.error("Failed to fetch users", error);
    } finally {
        loading.value = false;
    }
};

const updateUserRole = async (user, newRole) => {
    if (user.id === currentUser.value.id) {
        alert("You cannot change your own role.");
        return;
    }
    if (!confirm(`Promote ${user.name} to ${newRole}?`)) return;

    processing.value = user.id;
    try {
        await axios.post(`/api/v1/admin/users/${user.id}/role`, { role: newRole });
        user.role = newRole;
        alert("Role updated.");
    } catch (error) {
        alert("Failed to update role.");
    } finally {
        processing.value = null;
    }
};

const toggleBan = async (user) => {
    if (user.id === currentUser.value.id) return;
    const action = user.is_hidden ? 'Unban' : 'Ban';
    if (!confirm(`${action} ${user.name}?`)) return;

    processing.value = user.id;
    try {
        await axios.post(`/api/v1/admin/users/${user.id}/toggle-status`);
        user.is_hidden = !user.is_hidden;
    } catch (error) {
        alert("Failed to change status.");
    } finally {
        processing.value = null;
    }
};

onMounted(() => {
    if (isSuperAdmin.value) {
        fetchUsers();
    }
});
</script>

<template>
    <div class="user-man-app h-full flex flex-col bg-slate-50">
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 shrink-0 shadow-sm">
            <h2 class="font-black text-slate-800 flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-indigo-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                </div>
                User Management
            </h2>
            <div class="flex items-center gap-4">
                <div class="text-right hidden sm:block">
                    <div class="text-[10px] text-slate-400 font-black uppercase tracking-widest">Database Sync</div>
                    <div class="text-xs text-slate-600 font-bold">Total Members: {{ users.length }}</div>
                </div>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-6 scrollbar-thin">
            <div v-if="!isSuperAdmin" class="flex flex-col items-center justify-center h-full text-slate-800">
                <div class="w-20 h-20 bg-red-50 rounded-3xl flex items-center justify-center mb-6 shadow-xl shadow-red-100 italic">
                    <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                </div>
                <h3 class="text-2xl font-black uppercase tracking-tight mb-2">Security Breach</h3>
                <p class="text-slate-400 font-medium max-w-sm text-center">Your account does not have SuperAdmin authorization to access the identity firewall.</p>
            </div>

            <div v-else-if="loading" class="flex flex-col items-center justify-center h-full">
                <div class="flex gap-2">
                    <div class="w-4 h-4 bg-indigo-600 rounded-full animate-bounce"></div>
                    <div class="w-4 h-4 bg-indigo-600 rounded-full animate-bounce [animation-delay:-0.15s]"></div>
                    <div class="w-4 h-4 bg-indigo-600 rounded-full animate-bounce [animation-delay:-0.3s]"></div>
                </div>
                <p class="mt-4 text-slate-400 font-black uppercase tracking-[0.3em] text-[10px]">Scanning Identity Directory</p>
            </div>

            <div v-else class="max-w-6xl mx-auto">
                <div class="bg-white rounded-[2rem] border border-slate-200 shadow-2xl shadow-indigo-100/50 overflow-hidden">
                    <table class="w-full text-sm text-left border-separate border-spacing-0">
                        <thead class="bg-slate-50/80 backdrop-blur-md text-slate-400 uppercase text-[10px] font-black tracking-widest">
                            <tr>
                                <th class="px-8 py-6 border-b border-slate-100">User Identity</th>
                                <th class="px-8 py-6 border-b border-slate-100 hidden md:table-cell">Contact Point</th>
                                <th class="px-8 py-6 border-b border-slate-100">Access Level</th>
                                <th class="px-8 py-6 border-b border-slate-100">Clearance</th>
                                <th class="px-8 py-6 border-b border-slate-100 text-center">Operations</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="u in users" :key="u.id" class="hover:bg-indigo-50/30 transition-all group">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center font-black text-white text-sm shadow-lg shadow-indigo-200">
                                            {{ u.name.charAt(0) }}
                                        </div>
                                        <div>
                                            <div class="font-black text-slate-800 group-hover:text-indigo-600 transition-colors">{{ u.name }}</div>
                                            <div class="text-[10px] text-slate-400 uppercase font-black tracking-widest">{{ u.is_hidden ? 'Restricted' : 'Authenticated' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-5 hidden md:table-cell">
                                    <div class="text-slate-600 font-medium">{{ u.email }}</div>
                                </td>
                                <td class="px-8 py-5">
                                    <select 
                                        :value="u.role" 
                                        @change="updateUserRole(u, $event.target.value)"
                                        class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-xs font-bold outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-slate-700 transition-all cursor-pointer"
                                        :disabled="processing === u.id || u.id === currentUser.id"
                                    >
                                        <option v-for="r in roles" :key="r" :value="r">{{ r }}</option>
                                    </select>
                                </td>
                                <td class="px-8 py-5">
                                    <div v-if="u.is_hidden" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-red-50 text-red-600 text-[10px] font-black uppercase tracking-wider border border-red-100">
                                        <div class="w-1.5 h-1.5 rounded-full bg-red-500"></div>
                                        Access Denied
                                    </div>
                                    <div v-else class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-green-50 text-green-600 text-[10px] font-black uppercase tracking-wider border border-green-100">
                                        <div class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></div>
                                        Verified
                                    </div>
                                </td>
                                <td class="px-8 py-5 text-center">
                                    <button 
                                        @click="toggleBan(u)" 
                                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all border shadow-sm"
                                        :class="u.is_hidden 
                                            ? 'bg-green-600 text-white border-green-600 hover:bg-green-700 hover:shadow-green-200' 
                                            : 'bg-white text-red-600 border-slate-200 hover:bg-red-50 hover:border-red-200 hover:text-red-700'"
                                        :disabled="processing === u.id || u.id === currentUser.id"
                                    >
                                        <svg v-if="u.is_hidden" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                        {{ u.is_hidden ? 'Authorized' : 'Terminate' }}
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.scrollbar-thin::-webkit-scrollbar { width: 6px; }
.scrollbar-thin::-webkit-scrollbar-thumb { background: #4b5563; border-radius: 3px; }
</style>
