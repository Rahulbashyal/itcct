<script setup>
import { ref, onMounted } from 'vue';

const pageId = ref('ITClubCCT'); // Placeholder Page ID, should be confirmed
const loading = ref(true);

onMounted(() => {
    // Relying on iframe @load to set loading to false
});
</script>

<template>
    <div class="facebook-feed flex flex-col h-full bg-slate-100 items-center overflow-y-auto pt-4">
        <div class="max-w-xl w-full bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4">
            <h2 class="text-xl font-bold text-blue-700 flex items-center gap-2 mb-2">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                IT Club CCT Official Page
            </h2>
            <p class="text-sm text-gray-500">Real-time updates directly from our Facebook community.</p>
        </div>

        <!-- FB Page Display -->
        <div class="flex-1 w-full max-w-2xl flex flex-col items-center">
            <div class="fb-container bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden mb-8 relative min-h-[600px] w-full max-w-[500px]">
                <div v-if="loading" class="absolute inset-0 flex flex-col items-center justify-center bg-white z-10">
                    <div class="flex gap-1 mb-4">
                        <div class="w-2.5 h-2.5 bg-blue-600 rounded-full animate-bounce"></div>
                        <div class="w-2.5 h-2.5 bg-blue-600 rounded-full animate-bounce [animation-delay:-0.15s]"></div>
                        <div class="w-2.5 h-2.5 bg-blue-600 rounded-full animate-bounce [animation-delay:-0.3s]"></div>
                    </div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Synchronizing Feed</p>
                </div>
                
                <iframe 
                    v-show="!loading"
                    src="https://www.facebook.com/plugins/page.php?href=https%3A%2F%2Fwww.facebook.com%2Fitclubofcct&tabs=timeline&width=500&height=1000&small_header=false&adapt_container_width=true&hide_cover=false&show_facepile=true&appId" 
                    width="500" 
                    height="1000" 
                    style="border:none;overflow:hidden;width:100%;height:100%;min-height:800px" 
                    scrolling="no" 
                    frameborder="0" 
                    allowfullscreen="true" 
                    allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"
                    @load="loading = false"
                ></iframe>
            </div>

            <!-- Fallback for Adblockers -->
            <div class="text-center pb-12">
                <a 
                    href="https://www.facebook.com/itclubofcct" 
                    target="_blank" 
                    class="inline-flex items-center gap-2 px-6 py-3 bg-white border border-slate-200 rounded-full text-blue-600 text-sm font-bold shadow-sm hover:bg-slate-50 transition-all hover:shadow-md"
                >
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    Visit Official Page on Facebook
                </a>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-4 opacity-50">Note: Privacy settings or Adblockers may restrict embedded content.</p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.facebook-feed {
    scrollbar-width: thin;
}
.fb-container {
    width: 500px;
    min-height: 800px;
}
@media (max-width: 600px) {
    .fb-container {
        width: 100%;
    }
    iframe {
        width: 100%;
    }
}
</style>
