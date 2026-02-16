<script setup>
import { ref, computed } from 'vue';

const url = ref('http://localhost:8080');
const iframeUrl = ref('http://localhost:8080');
const isLoading = ref(false);

const navigate = () => {
    isLoading.value = true;
    iframeUrl.value = url.value;
};

const handleLoad = () => {
    isLoading.value = false;
};

const reload = () => {
    const current = iframeUrl.value;
    iframeUrl.value = '';
    setTimeout(() => {
        iframeUrl.value = current;
        isLoading.value = true;
    }, 100);
};
</script>

<template>
    <div class="nexus-browser h-full flex flex-col bg-[#1a1b26] text-[#a9b1d6] overflow-hidden">
        <!-- Address Bar -->
        <div class="h-12 bg-[#16161e] border-b border-white/5 flex items-center px-4 gap-4">
            <div class="flex items-center gap-2">
                <button @click="reload" class="p-2 hover:bg-white/5 rounded-lg transition-colors" title="Reload">
                    <svg class="w-4 h-4" :class="{ 'animate-spin': isLoading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </button>
            </div>
            
            <div class="flex-1 flex items-center bg-black/20 border border-white/10 rounded-xl px-4 py-1.5 gap-3 focus-within:border-indigo-500/50 transition-all">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-4.514A11.042 11.042 0 001.051 10C1.51 5.824 4.137 2.49 7.588.706m9.948 1.854a11.034 11.034 0 012.251 5.174m-4.354 11.22L17.304 22m-8.02-4.446L4.282 22" />
                </svg>
                <input 
                    v-model="url" 
                    @keyup.enter="navigate"
                    type="text" 
                    class="bg-transparent border-none outline-none text-xs font-bold text-slate-300 w-full placeholder-slate-600"
                    placeholder="Enter URL or localhost port..."
                />
            </div>

            <div class="flex items-center gap-2">
                <div class="px-2 py-1 rounded bg-emerald-500/10 border border-emerald-500/20">
                    <span class="text-[10px] font-black text-emerald-400 uppercase tracking-tighter">Secure Link</span>
                </div>
            </div>
        </div>

        <!-- Viewport -->
        <div class="flex-1 bg-white relative">
            <div v-if="isLoading" class="absolute inset-0 flex items-center justify-center bg-[#1a1b26] z-10 transition-opacity">
                <div class="flex flex-col items-center gap-4">
                    <div class="w-12 h-12 border-4 border-indigo-500/20 border-t-indigo-500 rounded-full animate-spin"></div>
                    <span class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">Resolving Localhost...</span>
                </div>
            </div>
            
            <div v-if="!iframeUrl" class="absolute inset-0 flex items-center justify-center bg-[#1a1b26]">
                <div class="text-center px-6">
                    <div class="w-20 h-20 bg-emerald-500/10 rounded-3xl flex items-center justify-center mx-auto mb-6 border border-emerald-500/20">
                        <svg class="w-10 h-10 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-black text-white mb-2 uppercase tracking-tight">Nexus Web Engine</h3>
                    <p class="text-slate-500 text-sm font-bold mb-8">Preview your web projects in real-time.</p>
                    
                    <div class="bg-black/40 border border-white/5 rounded-2xl p-5 max-w-sm mx-auto shadow-2xl">
                        <div class="text-[10px] font-black text-emerald-500 uppercase tracking-widest mb-3 text-left flex items-center gap-2">
                           <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                           Deployment Guide
                        </div>
                        <ul class="text-[11px] text-slate-400 text-left space-y-3 leading-relaxed">
                            <li>1. Open <span class="text-indigo-400 font-bold">IDE Lab</span> terminal.</li>
                            <li>2. Start server on <span class="text-white">0.0.0.0</span> (not 127.0.0.1):<br>
                                <code class="bg-white/5 px-2 py-1 rounded text-emerald-400 block mt-1.5 border border-white/5 font-mono">php -S 0.0.0.0:8080</code>
                            </li>
                            <li>3. Enter <code class="text-white">http://localhost:8080</code> in the bar above.</li>
                        </ul>
                        <div class="mt-4 pt-3 border-t border-white/5 text-[9px] text-slate-500 text-left italic">
                           Tip: If it still fails, try using 127.0.0.1 instead of localhost.
                        </div>
                    </div>
                </div>
            </div>

            <iframe 
                v-if="iframeUrl"
                :src="iframeUrl" 
                @load="handleLoad"
                class="w-full h-full border-none bg-white"
            ></iframe>
        </div>
    </div>
</template>

<style scoped>
.nexus-browser {
    font-family: 'Inter', system-ui, sans-serif;
}
</style>
