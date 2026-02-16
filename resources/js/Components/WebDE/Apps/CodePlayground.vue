<script setup>
import { ref, onMounted, onUnmounted, computed, watch, nextTick } from 'vue';
import axios from 'axios';
import * as monaco from 'monaco-editor';
import { Terminal } from 'xterm';
import { FitAddon } from 'xterm-addon-fit';
import 'xterm/css/xterm.css';

// Monaco Workers
import editorWorker from 'monaco-editor/esm/vs/editor/editor.worker?worker';
import jsonWorker from 'monaco-editor/esm/vs/language/json/json.worker?worker';
import cssWorker from 'monaco-editor/esm/vs/language/css/css.worker?worker';
import htmlWorker from 'monaco-editor/esm/vs/language/html/html.worker?worker';
import tsWorker from 'monaco-editor/esm/vs/language/typescript/ts.worker?worker';

self.MonacoEnvironment = {
    getWorker(_, label) {
        if (label === 'json') return new jsonWorker();
        if (label === 'css' || label === 'scss' || label === 'less') return new cssWorker();
        if (label === 'html' || label === 'handlebars' || label === 'razor') return new htmlWorker();
        if (label === 'typescript' || label === 'javascript') return new tsWorker();
        return new editorWorker();
    }
};

const props = defineProps(['user']);

// State
const explorerFiles = ref([]);
const openTabs = ref([]);
const activeTabId = ref(null);
const editorContainer = ref(null);
const terminalContainer = ref(null);
let editorInstance = null;
let terminalInstance = null;
let fitAddon = null;

// Terminal persistence
const terminalOutput = ref('');
const isSidebarOpen = ref(true);

const activeFile = computed(() => {
    return openTabs.value.find(tab => tab.id === activeTabId.value);
});

// Initialization
onMounted(async () => {
    initEditor();
    initTerminal();
    
    try {
        const response = await axios.get('/api/v1/ide/workspace');
        explorerFiles.value = response.data.files;
        
        if (explorerFiles.value.length > 0) {
            openFile(explorerFiles.value[0]);
        }
    } catch (error) {
        console.error('Failed to load workspace:', error);
    }

    window.addEventListener('resize', handleResize);
});

// Auto-save logic
let saveTimeout = null;
watch(() => activeFile.value?.content, (newContent) => {
    if (!activeFile.value) return;
    
    if (saveTimeout) clearTimeout(saveTimeout);
    saveTimeout = setTimeout(async () => {
        try {
            await axios.post(`/api/v1/ide/files/${activeFile.value.id}/save`, {
                content: newContent
            });
            console.log('File saved');
        } catch (error) {
            console.error('Save failed:', error);
        }
    }, 2000);
});

onUnmounted(() => {
    if (editorInstance) editorInstance.dispose();
    if (terminalInstance) terminalInstance.dispose();
    window.removeEventListener('resize', handleResize);
});

const initEditor = () => {
    editorInstance = monaco.editor.create(editorContainer.value, {
        theme: 'vs-dark',
        automaticLayout: true,
        fontSize: 13,
        fontFamily: "'JetBrains Mono', 'Fira Code', monospace",
        minimap: { enabled: true },
        padding: { top: 12 },
        smoothScrolling: true,
        cursorBlinking: 'smooth',
        lineHeight: 20,
    });

    editorInstance.onDidChangeModelContent(() => {
        if (activeFile.value) {
            activeFile.value.content = editorInstance.getValue();
        }
    });
};

const initTerminal = () => {
    terminalInstance = new Terminal({
        cursorBlinking: true,
        fontSize: 12,
        fontFamily: "'JetBrains Mono', 'Fira Code', monospace",
        theme: {
            background: '#1a1b26',
            foreground: '#a9b1d6',
            cursor: '#f7768e',
        },
        convertEol: true
    });
    
    fitAddon = new FitAddon();
    terminalInstance.loadAddon(fitAddon);
    terminalInstance.open(terminalContainer.value);
    fitAddon.fit();

    terminalInstance.writeln('\x1b[35mNexus Studio Runtime v1.0.0\x1b[0m');
    terminalInstance.write('\r\n\x1b[32mroot@nexus:\x1b[0m~# ');

    let currentLine = '';
    terminalInstance.onData(async (data) => {
        const char = data;
        if (char === '\r') {
            terminalInstance.write('\r\n');
            if (currentLine.trim()) {
                await executeTerminalCommand(currentLine);
            } else {
                terminalInstance.write('\r\x1b[32mroot@nexus:\x1b[0m~# ');
            }
            currentLine = '';
        } else if (char === '\u007F') { // Backspace
            if (currentLine.length > 0) {
                currentLine = currentLine.slice(0, -1);
                terminalInstance.write('\b \b');
            }
        } else {
            currentLine += char;
            terminalInstance.write(char);
        }
    });
};

const executeTerminalCommand = async (command) => {
    try {
        const response = await axios.post('/api/v1/ide/terminal/run', { command });
        if (response.data.output) terminalInstance.write(response.data.output);
        if (response.data.error) terminalInstance.write('\x1b[31m' + response.data.error + '\x1b[0m');
    } catch (error) {
        terminalInstance.write('\x1b[31mTerminal Error: ' + (error.response?.data?.output || error.message) + '\x1b[0m\r\n');
    }
    terminalInstance.write('\r\x1b[32mroot@nexus:\x1b[0m~# ');
};

const handleResize = () => {
    if (fitAddon) fitAddon.fit();
};

const openFile = (file) => {
    if (!openTabs.value.find(tab => tab.id === file.id)) {
        openTabs.value.push({ ...file });
    }
    activeTabId.value = file.id;
    
    nextTick(() => {
        if (editorInstance) {
            const model = monaco.editor.createModel(file.content, file.lang);
            editorInstance.setModel(model);
        }
    });
};

const closeTab = (e, tabId) => {
    e.stopPropagation();
    const index = openTabs.value.findIndex(t => t.id === tabId);
    openTabs.value = openTabs.value.filter(t => t.id !== tabId);
    
    if (activeTabId.value === tabId) {
        if (openTabs.value.length > 0) {
            openFile(openTabs.value[Math.max(0, index - 1)]);
        } else {
            activeTabId.value = null;
            if (editorInstance) editorInstance.setValue('');
        }
    }
};

const runProject = async () => {
    if (!activeFile.value) return;
    
    terminalInstance.writeln(`\r\n\x1b[33mExecuting ${activeFile.value.name}...\x1b[0m`);
    
    try {
        const response = await axios.post('/api/v1/code/execute', {
            language: activeFile.value.lang,
            code: editorInstance.getValue()
        });
        
        terminalInstance.writeln(response.data.output);
    } catch (error) {
        terminalInstance.writeln(`\x1b[31mError: ${error.message}\x1b[0m`);
    }
    
    terminalInstance.write('\x1b[32mroot@nexus:\x1b[0m~# ');
};
</script>

<template>
    <div class="nexus-studio h-full flex flex-col bg-[#1a1b26] text-[#a9b1d6] overflow-hidden selection:bg-indigo-500/30">
        <!-- Top Toolbar -->
        <div class="h-10 bg-[#16161e] border-b border-white/5 flex items-center justify-between px-3">
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 px-2 py-1 rounded bg-indigo-500/10 border border-indigo-500/20">
                    <span class="text-xs font-black text-indigo-400 tracking-tighter italic">NEXUS STUDIO</span>
                </div>
                <!-- Controls -->
                <div class="flex items-center gap-1">
                    <button class="tool-btn" title="New File">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    </button>
                    <button class="tool-btn" title="Save All">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg>
                    </button>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button @click="runProject" class="run-btn">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z" /></svg>
                    <span>RUN SYSTEM</span>
                </button>
            </div>
        </div>

        <div class="flex-1 flex overflow-hidden">
            <!-- Sidebar -->
            <div v-if="isSidebarOpen" class="w-60 bg-[#16161e] border-r border-white/5 flex flex-col">
                <div class="p-3 text-[10px] font-black text-slate-500 uppercase tracking-widest flex justify-between items-center">
                    <span>Explorer</span>
                    <button @click="isSidebarOpen = false" class="hover:text-white transition-colors">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7" /></svg>
                    </button>
                </div>
                
                <!-- File Tree -->
                <div class="flex-1 overflow-y-auto px-2">
                    <div 
                        v-for="file in explorerFiles" 
                        :key="file.id"
                        @click="openFile(file)"
                        class="group flex items-center gap-2 px-3 py-1.5 rounded-lg cursor-pointer transition-all mb-0.5"
                        :class="activeTabId === file.id ? 'bg-indigo-500/10 border border-indigo-500/20' : 'hover:bg-white/5 border border-transparent'"
                    >
                        <span class="text-lg leading-none opacity-80 group-hover:opacity-100">{{ file.lang === 'python' ? '🐍' : file.lang === 'javascript' ? '📜' : file.lang === 'css' ? '🎨' : '🐘' }}</span>
                        <span class="text-[11px] font-bold" :class="activeTabId === file.id ? 'text-indigo-400' : 'text-slate-400 group-hover:text-slate-200'">{{ file.name }}</span>
                    </div>
                </div>
            </div>

            <!-- Resize Button when sidebar closed -->
            <button v-if="!isSidebarOpen" @click="isSidebarOpen = true" class="w-6 bg-[#16161e] border-r border-white/5 flex items-center justify-center hover:bg-white/5">
                <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7" /></svg>
            </button>

            <!-- Main Editor Area -->
            <div class="flex-1 flex flex-col overflow-hidden bg-[#1a1b26]">
                <!-- Tabs -->
                <div class="h-9 bg-[#16161e] flex items-center overflow-x-auto scrollbar-none">
                    <div 
                        v-for="tab in openTabs" 
                        :key="'tab-'+tab.id"
                        @click="activeTabId = tab.id"
                        class="tab-item"
                        :class="{ 'active': activeTabId === tab.id }"
                    >
                        <span class="text-[10px] font-bold tracking-tight">{{ tab.name }}</span>
                        <button @click="closeTab($event, tab.id)" class="tab-close">
                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                </div>

                <!-- Editor -->
                <div class="flex-1 overflow-hidden relative">
                    <div ref="editorContainer" class="absolute inset-0"></div>
                </div>

                <!-- Terminal -->
                <div class="h-64 bg-[#1a1b26] border-t border-white/5 flex flex-col">
                    <div class="h-8 bg-[#16161e] border-b border-white/5 flex items-center justify-between px-4">
                        <div class="flex items-center gap-4">
                            <span class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">Interactive Terminal</span>
                            <div class="flex gap-1.5 grayscale opacity-50">
                                <div class="w-1.5 h-1.5 rounded-full bg-red-500"></div>
                                <div class="w-1.5 h-1.5 rounded-full bg-yellow-500"></div>
                                <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div>
                            </div>
                        </div>
                        <button @click="terminalInstance.clear()" class="text-[9px] font-black text-slate-500 hover:text-white transition-colors">CLEAR LOGS</button>
                    </div>
                    <div ref="terminalContainer" class="flex-1 overflow-hidden p-2"></div>
                </div>
            </div>
        </div>

        <!-- Status Bar -->
        <div class="h-6 bg-indigo-600 flex items-center justify-between px-3">
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-1.5">
                    <div class="w-2 h-2 rounded-full bg-white animate-pulse"></div>
                    <span class="text-[9px] font-black text-white uppercase tracking-tighter">Container: Operational</span>
                </div>
                <span class="text-[9px] font-bold text-indigo-200 uppercase">UTF-8</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-[9px] font-black text-white uppercase tracking-tighter">Nexus OS Core v4.0.2</span>
            </div>
        </div>
    </div>
</template>

<style scoped>
.nexus-studio {
    font-family: 'Inter', system-ui, sans-serif;
}

.tool-btn {
    padding: 0.375rem;
    color: #64748b;
    border-radius: 0.25rem;
    transition: all 0.2s;
}

.tool-btn:hover {
    color: #ffffff;
    background-color: rgba(255, 255, 255, 0.05);
}

.run-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.25rem 0.75rem;
    background-color: rgba(16, 185, 129, 0.1);
    border: 1px solid rgba(16, 185, 129, 0.3);
    border-radius: 0.25rem;
    font-size: 10px;
    font-weight: 900;
    color: #34d399;
    transition: all 0.2s;
    box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.1);
}

.run-btn:hover {
    background-color: #10b981;
    color: #ffffff;
}

.tab-item {
    height: 100%;
    padding: 0 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    border-right: 1px solid rgba(255, 255, 255, 0.05);
    cursor: pointer;
    transition: all 0.2s;
    position: relative;
    flex-shrink: 0;
    min-width: 120px;
    background: rgba(22, 22, 30, 0.5);
}

.tab-item.active {
    background-color: #1a1b26;
}

.tab-item.active::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 2px;
    background-color: #6366f1;
}

.tab-item.active span {
    color: #818cf8;
}

.tab-item span {
    color: #64748b;
    transition: color 0.2s;
}

.tab-item:hover span {
    color: #e2e8f0;
}

.tab-close {
    padding: 0.125rem;
    border-radius: 0.375rem;
    color: #475569;
    transition: all 0.2s;
    opacity: 0;
}

.tab-close:hover {
    background-color: rgba(255, 255, 255, 0.1);
    color: #ffffff;
}

.tab-item:hover .tab-close, .tab-item.active .tab-close {
    opacity: 100;
}

:deep(.monaco-editor) {
    --vscode-editor-background: transparent !important;
    background-color: transparent !important;
}

:deep(.xterm-viewport) {
    background-color: transparent !important;
}

:deep(.xterm-screen) {
    padding: 8px;
}
</style>
