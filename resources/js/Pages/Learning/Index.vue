<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import GlassCard from '@/Components/GlassCard.vue';
import { ref, onMounted, watch } from 'vue';

const templates = {
    'Neon Glow': {
        html: '<div class="neon-box">\n  <h2>NEON ENGINE</h2>\n  <p>Innovation Lab System Online</p>\n</div>',
        css: 'body { background: #050505; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }\n.neon-box {\n  padding: 3rem;\n  border: 4px solid #0ea5e9;\n  border-radius: 2rem;\n  box-shadow: 0 0 20px #0ea5e9, inset 0 0 20px #0ea5e9;\n  color: #0ea5e9;\n  text-align: center;\n  font-family: sans-serif;\n  text-shadow: 0 0 10px #0ea5e9;\n}'
    },
    'Glassmorphism': {
        html: '<div class="glass">\n  <h1>GLASS UI</h1>\n  <p>Translucent Excellence</p>\n</div>',
        css: 'body { background: linear-gradient(135deg, #1e293b, #0f172a); min-height: 100vh; display: flex; justify-content: center; align-items: center; margin: 0; }\n.glass {\n  background: rgba(255, 255, 255, 0.05);\n  backdrop-filter: blur(10px);\n  border: 1px solid rgba(255, 255, 255, 0.1);\n  padding: 4rem;\n  border-radius: 3rem;\n  color: white;\n  text-align: center;\n  font-family: system-ui;\n}'
    }
};

const htmlCode = ref(templates['Neon Glow'].html);
const cssCode = ref(templates['Neon Glow'].css);
const iframeRef = ref(null);

const applyTemplate = (name) => {
    htmlCode.value = templates[name].html;
    cssCode.value = templates[name].css;
};

const updatePreview = () => {
    if (!iframeRef.value) return;
    const doc = iframeRef.value.contentDocument;
    const content = `<html><head><style>${cssCode.value}</style></head><body>${htmlCode.value}</body></html>`;
    doc.open();
    doc.write(content);
    doc.close();
};

onMounted(() => updatePreview());
watch([htmlCode, cssCode], () => updatePreview());
</script>

<template>
    <Head title="Innovation Lab" />

    <AuthenticatedLayout>
        <template #header_breadcrumb>Digital Nexus / Innovation Lab</template>

        <div class="space-y-8 flex flex-col h-full">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-black text-white tracking-tighter">The Playground.</h1>
                    <p class="text-gray-500 text-sm font-medium">Real-time HTML/CSS sandboxed environment.</p>
                </div>
                <div class="flex space-x-2">
                    <button 
                        v-for="(_, name) in templates" 
                        :key="name"
                        @click="applyTemplate(name)"
                        class="px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-primary-blue hover:text-white transition-all"
                    >
                        {{ name }}
                    </button>
                </div>
            </div>

            <div class="flex-1 min-h-[600px] grid grid-cols-1 xl:grid-cols-2 gap-8">
                <!-- Editor Section -->
                <div class="flex flex-col space-y-4">
                    <div class="flex-1 flex flex-col group">
                        <div class="bg-dark-gray/50 border border-white/10 rounded-t-3xl px-6 py-3 flex items-center justify-between">
                            <span class="text-[10px] font-black uppercase tracking-widest text-primary-blue">index.html</span>
                        </div>
                        <textarea 
                            v-model="htmlCode" 
                            class="flex-1 bg-[#0a0b10] border-x border-b border-white/10 rounded-b-3xl p-6 font-mono text-xs sm:text-sm text-gray-400 resize-none outline-none focus:border-primary-blue/30 transition-all custom-scrollbar"
                        ></textarea>
                    </div>
                    <div class="flex-1 flex flex-col group">
                        <div class="bg-dark-gray/50 border border-white/10 rounded-t-3xl px-6 py-3 flex items-center justify-between">
                            <span class="text-[10px] font-black uppercase tracking-widest text-secondary-teal">style.css</span>
                        </div>
                        <textarea 
                            v-model="cssCode" 
                            class="flex-1 bg-[#0a0b10] border-x border-b border-white/10 rounded-b-3xl p-6 font-mono text-xs sm:text-sm text-gray-400 resize-none outline-none focus:border-secondary-teal/30 transition-all custom-scrollbar"
                        ></textarea>
                    </div>
                </div>

                <!-- Preview Section -->
                <div class="flex flex-col">
                    <div class="bg-dark-gray/50 border border-white/10 rounded-t-3xl px-6 py-3 flex items-center space-x-4">
                        <div class="flex space-x-1.5">
                            <div class="w-3 h-3 rounded-full bg-red-500/20 border border-red-500/50"></div>
                            <div class="w-3 h-3 rounded-full bg-yellow-500/20 border border-yellow-500/50"></div>
                            <div class="w-3 h-3 rounded-full bg-accent-green/20 border border-accent-green/50"></div>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-[0.3em] text-gray-500">Live Renderer</span>
                    </div>
                    <div class="flex-1 bg-white rounded-b-3xl overflow-hidden shadow-2xl relative min-h-[400px]">
                        <iframe ref="iframeRef" class="w-full h-full border-none"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 10px;
}
</style>
