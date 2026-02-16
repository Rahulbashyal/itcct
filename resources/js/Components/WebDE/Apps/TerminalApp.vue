<script setup>
import { ref, onMounted, nextTick, watch, computed } from 'vue';

const props = defineProps({
    user: Object
});

const input = ref('');
const history = ref([]);
const commandHistory = ref([]);
const historyIndex = ref(-1);
const terminalOutput = ref(null);
const currentDirectory = ref('~');
const isNanoMode = ref(false);
const nanoContent = ref('');
const nanoFileName = ref('');

// Virtual File System persisted in LocalStorage
const storageKey = computed(() => {
    const identifier = props.user?.symbol_number || props.user?.name?.toLowerCase().replace(/\s+/g, '') || 'guest';
    return `itclub_terminal_fs_${identifier}`;
});

const defaultFS = {
    '~': {
        type: 'dir',
        children: {
            'Desktop': { type: 'dir', children: {} },
            'Documents': { type: 'dir', children: {} },
            'Downloads': { type: 'dir', children: {} },
            'Pictures': { type: 'dir', children: {} },
            'Music': { type: 'dir', children: {} },
            'Videos': { type: 'dir', children: {} },
            'readme.txt': { type: 'file', content: 'Welcome to IT Club CCT OS Terminal!\nThis file system is persisted in your browser.' }
        }
    }
};

const fileSystem = ref(defaultFS);

const saveFS = () => {
    localStorage.setItem(storageKey.value, JSON.stringify(fileSystem.value));
};

const loadFS = () => {
    const saved = localStorage.getItem(storageKey.value);
    if (saved) {
        try {
            fileSystem.value = JSON.parse(saved);
        } catch (e) {
            console.error('Failed to load terminal FS:', e);
            fileSystem.value = defaultFS;
        }
    }
};

const MOTD = [
    "Welcome to IT Club CCT Terminal v1.2.0 (POWER USER)",
    "Type 'help' to see active commands.",
    ""
];

onMounted(() => {
    history.value = [...MOTD];
    loadFS();
});

watch(fileSystem, () => {
    saveFS();
}, { deep: true });

const scrollToBottom = async () => {
    await nextTick();
    if (terminalOutput.value) {
        terminalOutput.value.scrollTop = terminalOutput.value.scrollHeight;
    }
};

watch(history, () => {
    scrollToBottom();
}, { deep: true });

const getDir = (path) => {
    if (path === '~') return fileSystem.value['~'];
    const parts = path.replace(/^~\//, '').split('/');
    let current = fileSystem.value['~'];
    for (const part of parts) {
        if (!part) continue;
        if (current.children && current.children[part]) {
            current = current.children[part];
        } else {
            return null;
        }
    }
    return current;
};

const handleTab = (e) => {
    e.preventDefault();
    const parts = input.value.split(' ');
    const lastPart = parts[parts.length - 1];
    
    const dir = getDir(currentDirectory.value);
    if (!dir) return;

    const matches = Object.keys(dir.children).filter(name => name.startsWith(lastPart));
    
    if (matches.length === 1) {
        parts[parts.length - 1] = matches[0];
        if (dir.children[matches[0]].type === 'dir') {
            parts[parts.length - 1] += '/';
        }
        input.value = parts.join(' ');
    } else if (matches.length > 1) {
        history.value.push(matches.join('  '));
    }
};

const commands = {
    help: () => [
        "Core Commands:",
        "  ls, cd, mkdir, touch, rm, pwd, cat, clear",
        "  whoami, date, echo, neofetch, ping",
        "Power Tools:",
        "  nano [file]       - Terminal Text Editor",
        "  [cmd] > [file]    - Write output to file",
        "  [cmd] >> [file]   - Append output to file",
        "  Tab Key           - Auto-completion"
    ],
    clear: () => {
        history.value = [];
        return null;
    },
    ls: (args) => {
        const targetPath = args[0] || currentDirectory.value;
        const dir = getDir(targetPath);
        if (!dir || dir.type !== 'dir') return [`ls: cannot access '${targetPath}': No such directory`];
        return [Object.keys(dir.children).join('  ')];
    },
    cd: (args) => {
        const target = args[0];
        if (!target || target === '~') {
            currentDirectory.value = '~';
            return null;
        }
        if (target === '..') {
            if (currentDirectory.value === '~') return null;
            const parts = currentDirectory.value.split('/');
            parts.pop();
            currentDirectory.value = parts.join('/') || '~';
            return null;
        }
        const newPath = currentDirectory.value === '~' ? `~/${target.replace(/\/$/, '')}` : `${currentDirectory.value}/${target.replace(/\/$/, '')}`;
        const dir = getDir(newPath);
        if (dir && dir.type === 'dir') {
            currentDirectory.value = newPath;
            return null;
        }
        return [`cd: ${target}: No such directory`];
    },
    mkdir: (args) => {
        if (!args[0]) return ['mkdir: missing operand'];
        const dir = getDir(currentDirectory.value);
        if (dir.children[args[0]]) return [`mkdir: cannot create directory '${args[0]}': File exists`];
        dir.children[args[0]] = { type: 'dir', children: {} };
        return null;
    },
    touch: (args) => {
        if (!args[0]) return ['touch: missing operand'];
        const dir = getDir(currentDirectory.value);
        if (!dir.children[args[0]]) {
            dir.children[args[0]] = { type: 'file', content: '' };
        }
        return null;
    },
    cat: (args) => {
        if (!args[0]) return ['cat: missing operand'];
        const dir = getDir(currentDirectory.value);
        const file = dir.children[args[0]];
        if (!file) return [`cat: ${args[0]}: No such file`];
        if (file.type === 'dir') return [`cat: ${args[0]}: Is a directory`];
        return file.content.split('\n');
    },
    rm: (args) => {
        if (!args[0]) return ['rm: missing operand'];
        const dir = getDir(currentDirectory.value);
        if (!dir.children[args[0]]) return [`rm: cannot remove '${args[0]}': No such file or directory`];
        delete dir.children[args[0]];
        return null;
    },
    pwd: () => ["/home/" + (props.user?.name?.toLowerCase().replace(/\s+/g, '') || 'user') + currentDirectory.value.replace('~', '')],
    whoami: () => [props.user?.name || 'Anonymous User'],
    date: () => [new Date().toLocaleString()],
    echo: (args) => [args.join(' ')],
    ping: (args) => {
        if (!args[0]) return ['ping: missing host'];
        return [
            `PING ${args[0]} (127.0.0.1): 56 data bytes`,
            `64 bytes from 127.0.0.1: icmp_seq=0 ttl=64 time=0.045 ms`,
            `64 bytes from 127.0.0.1: icmp_seq=1 ttl=64 time=0.061 ms`,
            `--- ${args[0]} ping statistics ---`,
            `2 packets transmitted, 2 packets received, 0% packet loss`
        ];
    },
    nano: (args) => {
        if (!args[0]) return ['nano: missing filename'];
        const fileName = args[0];
        const dir = getDir(currentDirectory.value);
        const file = dir.children[fileName];
        
        nanoFileName.value = fileName;
        nanoContent.value = file && file.type === 'file' ? file.content : '';
        isNanoMode.value = true;
        return null;
    },
    neofetch: () => [
        "            .-/+oossssoo+/-.               IT Club CCT OS",
        "        `:+ssssssssssssssssss+:`           --------------",
        "      -+ssssssssssssssssssyyssss+-         OS: IT Club WebDE v1.2",
        "    .ossssssssssssssssssdMMMNysssso.       Host: Web Browser",
        "   /ssssssssssshdmmNNmmyNMMMMhssssss/      Kernel: Javascript V8",
        "  +ssssssssshmydMMMMMMMNddddyssssssss+     Uptime: Infinite",
        " /sssssssshNMMMyhhyyyyhmNMMMNhssssssss/    Packages: 1 (TerminalApp.vue)",
        ".ssssssssdMMMNhsssssssssshNMMMdssssssss.   Shell: WebShell 2.1",
        "+sssshhhyNMMNyssssssssssssyNMMMysssssss+   Resolution: Full HD (Virtual)",
        "osssyNMMMNyhyyyyyyhmMMMMMNhssssssssssssso  DE: IT-Plasma",
        "ossyNMMMNyysssssssshNMMMNysssssssssssssso  WM: WindowManager.vue",
        "+sssshhhyNMMNyssssssssssssyNMMMysssssss+   Theme: Transparent Glass",
        ".ssssssssdMMMNhsssssssssshNMMMdssssssss.   Terminal: TerminalApp.vue",
        " /sssssssshNMMMyhhyyyyhdNMMMNhssssssss/    CPU: AI Core Matrix",
        "  +ssssssssshmydMMMMMMMNddddyssssssss+     GPU: WebGL Next-Gen",
        "   /ssssssssssshdmmNNmmyNMMMMhssssss/      Memory: Unlimited Context",
        "    .ossssssssssssssssssdMMMNysssso.       ",
        "      -+ssssssssssssssssssyyssss+-         ",
        "        `:+ssssssssssssssssss+:`           ",
        "            .-/+oossssoo+/-.               "
    ],
};

const handleCommand = () => {
    const trimmedInput = input.value.trim();
    if (!trimmedInput) return;

    const fullPrompt = `${props.user?.name?.toLowerCase().replace(/\s+/g, '') || 'user'}@itclub:${currentDirectory.value}$ `;
    history.value.push(`${fullPrompt}${trimmedInput}`);
    commandHistory.value.push(trimmedInput);
    historyIndex.value = commandHistory.value.length;

    let finalInput = trimmedInput;
    let redirectFile = null;
    let append = false;

    if (trimmedInput.includes('>>')) {
        const segs = trimmedInput.split('>>');
        finalInput = segs[0].trim();
        redirectFile = segs[1].trim();
        append = true;
    } else if (trimmedInput.includes('>')) {
        const segs = trimmedInput.split('>');
        finalInput = segs[0].trim();
        redirectFile = segs[1].trim();
    }

    const parts = finalInput.split(' ');
    const cmd = parts[0].toLowerCase();
    const args = parts.slice(1);

    if (commands[cmd]) {
        const result = commands[cmd](args);
        if (result && redirectFile) {
            const dir = getDir(currentDirectory.value);
            const content = result.join('\n');
            if (append && dir.children[redirectFile]) {
                dir.children[redirectFile].content += '\n' + content;
            } else {
                dir.children[redirectFile] = { type: 'file', content };
            }
            history.value.push(`Output written to ${redirectFile}`);
        } else if (result) {
            history.value.push(...result);
        }
    } else {
        history.value.push(`sh: command not found: ${cmd}`);
    }

    input.value = '';
};

const exitNano = () => {
    isNanoMode.value = false;
    history.value.push(`nano: ${nanoFileName.value} saved.`);
};

const saveNano = () => {
    const dir = getDir(currentDirectory.value);
    dir.children[nanoFileName.value] = { type: 'file', content: nanoContent.value };
    saveFS();
};

const handleKeyDown = (e) => {
    if (isNanoMode.value) {
        if (e.key === 's' && e.ctrlKey) {
            e.preventDefault();
            saveNano();
        } else if (e.key === 'x' && e.ctrlKey) {
            e.preventDefault();
            saveNano();
            exitNano();
        }
        return;
    }

    if (e.key === 'Tab') {
        handleTab(e);
    } else if (e.key === 'ArrowUp') {
        if (historyIndex.value > 0) {
            historyIndex.value--;
            input.value = commandHistory.value[historyIndex.value];
        }
    } else if (e.key === 'ArrowDown') {
        if (historyIndex.value < commandHistory.value.length - 1) {
            historyIndex.value++;
            input.value = commandHistory.value[historyIndex.value];
        } else {
            historyIndex.value = commandHistory.value.length;
            input.value = '';
        }
    }
};
</script>

<template>
    <div class="terminal-container" @click="!isNanoMode && $refs.commandInput.focus()">
        <!-- Standard Terminal Mode -->
        <div v-if="!isNanoMode" class="terminal-body" ref="terminalOutput">
            <div v-for="(line, index) in history" :key="index" class="terminal-line">
                <pre>{{ line }}</pre>
            </div>
            <div class="terminal-input-line">
                <span class="prompt">
                    <span class="user-part">{{ props.user?.name?.toLowerCase().replace(/\s+/g, '') || 'user' }}@itclub</span>:<span class="path-part">{{ currentDirectory }}</span>$
                </span>
                <input 
                    ref="commandInput"
                    v-model="input" 
                    @keydown.enter="handleCommand"
                    @keydown="handleKeyDown"
                    type="text" 
                    class="command-input"
                    autofocus
                />
            </div>
        </div>

        <!-- Nano Editor Mode -->
        <div v-else class="nano-editor">
            <div class="nano-header">NANO 1.2.0 - Editing: {{ nanoFileName }}</div>
            <textarea 
                v-model="nanoContent" 
                @keydown="handleKeyDown"
                class="nano-textarea"
                autofocus
            ></textarea>
            <div class="nano-footer">
                <span>^S Save</span>
                <span>^X Exit</span>
            </div>
        </div>
    </div>
</template>

<style scoped>
.terminal-container {
    background: rgba(10, 10, 15, 0.9);
    color: #e0e0e0;
    font-family: 'JetBrains Mono', 'Fira Code', monospace;
    height: 100%;
    width: 100%;
    padding: 12px;
    box-sizing: border-box;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.terminal-body {
    flex: 1;
    overflow-y: auto;
    padding-bottom: 20px;
}

.terminal-line {
    margin-bottom: 4px;
    font-size: 13px;
    line-height: 1.5;
}

pre {
    margin: 0;
    white-space: pre-wrap;
    color: #f0f0f0;
}

.terminal-input-line {
    display: flex;
    align-items: center;
    gap: 8px;
}

.prompt { font-weight: bold; font-size: 13px; }
.user-part { color: #4ade80; }
.path-part { color: #60a5fa; }

.command-input {
    background: transparent;
    border: none;
    color: #fff;
    font-family: inherit;
    font-size: 13px;
    flex: 1;
    outline: none;
    caret-color: #00ff00;
}

/* Nano Editor Component */
.nano-editor {
    display: flex;
    flex-direction: column;
    height: 100%;
    background: #000;
    color: #fff;
    border: 1px solid #333;
}

.nano-header {
    background: #fff;
    color: #000;
    padding: 2px 8px;
    font-size: 11px;
    font-weight: bold;
    text-transform: uppercase;
}

.nano-textarea {
    flex: 1;
    background: transparent;
    color: #fff;
    border: none;
    outline: none;
    resize: none;
    padding: 10px;
    font-family: inherit;
    font-size: 13px;
    line-height: 1.5;
}

.nano-footer {
    background: #333;
    padding: 4px 10px;
    display: flex;
    gap: 20px;
    font-size: 11px;
}

/* Custom Scrollbar */
.terminal-body::-webkit-scrollbar { width: 6px; }
.terminal-body::-webkit-scrollbar-track { background: transparent; }
.terminal-body::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
}
</style>
