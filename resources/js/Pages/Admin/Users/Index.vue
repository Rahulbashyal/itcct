<script setup>
import GovernanceLayout from '@/Layouts/GovernanceLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import GlassCard from '@/Components/GlassCard.vue';

const props = defineProps({
    users: Array
});

const updateRole = (userId, newRole) => {
    router.patch(route('admin.users.update-role', userId), {
        role: newRole
    }, {
        preserveScroll: true
    });
};

const roles = ['SuperAdmin', 'President', 'Secretary', 'Treasurer', 'Member'];
</script>

<template>
    <Head title="Innovator Management" />

    <GovernanceLayout>
        <div class="space-y-12">
            <div>
                <h1 class="text-4xl font-black tracking-tighter text-white mb-2">Innovator Directory</h1>
                <p class="text-gray-500 font-medium">Manage permissions and leadership roles across the member ecosystem.</p>
            </div>

            <GlassCard class="p-0 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white/[0.02] border-b border-white/5">
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-gray-500">Member</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-gray-500">Symbol</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-gray-500">Current Role</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-gray-500 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="user in users" :key="user.id" class="hover:bg-white/[0.01] transition-colors">
                            <td class="px-6 py-5">
                                <div class="flex items-center space-x-4">
                                    <div class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center font-black">
                                        {{ user.name.charAt(0) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-white leading-none mb-1">{{ user.name }}</p>
                                        <p class="text-[10px] text-gray-600 font-medium">{{ user.email || 'No email' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <span class="font-mono text-xs text-primary-blue bg-primary-blue/10 px-2 py-1 rounded border border-primary-blue/20">
                                    {{ user.symbol_number }}
                                </span>
                            </td>
                            <td class="px-6 py-5">
                                <span class="text-[10px] font-black uppercase tracking-widest" :class="user.role === 'SuperAdmin' ? 'text-accent-green' : 'text-gray-400'">
                                    {{ user.role }}
                                </span>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <select 
                                    @change="(e) => updateRole(user.id, e.target.value)"
                                    class="bg-dark-gray border border-white/10 rounded-lg text-[10px] font-bold uppercase tracking-widest text-gray-400 px-3 py-2 outline-none focus:border-primary-blue transition-colors"
                                >
                                    <option value="" disabled selected>Update Role</option>
                                    <option v-for="role in roles" :key="role" :value="role" :selected="user.role === role">
                                        {{ role }}
                                    </option>
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </GlassCard>
        </div>
    </GovernanceLayout>
</template>
