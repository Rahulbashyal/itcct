<script setup>
import { ref, onMounted, onUnmounted, nextTick, watch, computed } from 'vue';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

const props = defineProps({
    user: Object,
    isEmbedded: { type: Boolean, default: false }
});

// Setup Echo
window.Pusher = Pusher;
const echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});

const page = usePage();
const currentUser = page.props.auth.user;

const activeView = ref('chats'); // 'chats' or 'people'
const activeChatId = ref('general'); // Channel ID or DM ID
const activeChatType = ref('channel'); // 'channel' or 'dm'
const activeChatUser = ref(null);

const messages = ref([]);
const messageInput = ref('');
const messagesContainer = ref(null);
const sending = ref(false);
const searchQuery = ref('');
const showInfoSidebar = ref(true);

// Calling state
const activeCall = ref(null);
const callTimer = ref(0);
const callInterval = ref(null);
const localStream = ref(null);
const remoteStream = ref(null);
const localVideo = ref(null);
const remoteVideo = ref(null);

// File sharing state
const fileInput = ref(null);
const selectedFile = ref(null);
const filePreview = ref(null);

// Emoji state
const showEmojiPicker = ref(false);
const commonEmojis = [
    '😀', '😃', '😄', '😁', '😅', '😂', '🤣', '😊', '😇', '🙂', '🙃', '😉', '😌', '😍', '🥰', '😘', '😗', '😙', '😚', '😋', '😛', '😝', '😜', '🤪', '🤨', '🧐', '🤓', '😎', '🤩', '🥳', '😏', '😒', '😞', '😔', '😟', '😕', '🙁', '☹️', '😣', '😖', '😫', '😩', '🥺', '😢', '😭', '😤', '😠', '😡', '🤬', '🤯', '😳', '🥵', '🥶', '😱', '😨', '😰', '😥', '😓', '🤗', '🤔', '🤭', '🤫', '🤥', '😶', '😐', '😑', '😬', '🙄', '😯', '😦', '😧', '😮', '😲', '🥱', '😴', '🤤', '😪', '😵', '🤐', '🥴', '🤢', '🤮', '🤧', '😷', '🤒', '🤕', '🤑', '🤠', '😈', '👿', '👹', '👺', '🤡', '💩', '👻', '💀', '☠️', '👽', '👾', '🤖', '🎃', '😺', '😸', '😹', '😻', '😼', '😽', '🙀', '😿', '😾', '👍', '👎', '👌', '✌️', '🤞', '🤟', '🤘', '🤙', '👈', '👉', '👆', '🖕', '👇', '☝️', '👍', '👊', '🤜', '🤛', '👏', '🙌', '👐', '🤲', '🤝', '🙏', '✍️', '💅', '🤳', '💪', '🦾'
];

const channels = ref([]);
const members = ref([]);
const membersByRole = ref({});

// Fetch initial data
const fetchData = async () => {
    try {
        const [channelsRes, membersRes] = await Promise.all([
            axios.get('/api/v1/chat/channels'),
            axios.get('/api/v1/chat/members')
        ]);
        channels.value = channelsRes.data;
        membersByRole.value = membersRes.data;
        
        // Flatten members for search
        members.value = Object.values(membersRes.data).flat();
    } catch (error) {
        console.error("Failed to load chat data:", error);
    }
};

const fetchMessages = async () => {
    try {
        const response = await axios.get(`/api/v1/chat/history?channel=${activeChatId.value}`);
        messages.value = response.data;
        scrollToBottom();
    } catch (error) {
        console.error("Failed to load messages:", error);
    }
};

const triggerFileSelect = () => {
    fileInput.value.click();
};

const handleFileChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    
    selectedFile.value = file;
    if (file.type.startsWith('image/')) {
        filePreview.value = URL.createObjectURL(file);
    } else {
        filePreview.value = null;
    }
};

const removeFile = () => {
    selectedFile.value = null;
    filePreview.value = null;
    fileInput.value.value = '';
};

const sendMessage = async () => {
    if ((!messageInput.value.trim() && !selectedFile.value) || sending.value) return;
    
    sending.value = true;
    const content = messageInput.value;
    const file = selectedFile.value;
    
    // Clear immediately for UX
    messageInput.value = '';
    const tempFileId = file ? Math.random().toString(36).substr(2, 9) : null;
    
    try {
        const formData = new FormData();
        formData.append('content', content);
        formData.append('channel', activeChatId.value);
        if (file) {
            formData.append('file', file);
        }

        const response = await axios.post('/api/v1/chat/send', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        
        if (!messages.value.find(m => m.id === response.data.id)) {
            messages.value.push(response.data);
            scrollToBottom();
        }
        
        removeFile();
    } catch (error) {
        console.error("Failed to send message:", error);
        messageInput.value = content; // restore
    } finally {
        sending.value = false;
    }
};

const addEmoji = (emoji) => {
    messageInput.value += emoji;
    showEmojiPicker.value = false;
};

const deleteMessage = async (id) => {
    try {
        await axios.delete(`/api/v1/chat/${id}`);
        messages.value = messages.value.filter(m => m.id !== id);
    } catch (error) {
        alert("Forbidden");
    }
};

const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
};

const selectChat = (chat) => {
    if (chat.type === 'channel') {
        activeChatId.value = chat.id;
        activeChatType.value = 'channel';
        activeChatUser.value = null;
    } else {
        // Direct Message
        // Convention: dm:smallerID:largerID
        const ids = [currentUser.id, chat.id].sort((a, b) => a - b);
        activeChatId.value = `dm:${ids[0]}:${ids[1]}`;
        activeChatType.value = 'dm';
        activeChatUser.value = chat;
    }
    fetchMessages();
    listenToChannel(activeChatId.value);
};

const listenToChannel = (channelId) => {
    // Leave previous channel
    echo.leave(`chat.${activeChatId.value}`);
    
    echo.channel(`chat.${channelId}`)
        .listen('MessageSent', (e) => {
            if (!messages.value.find(m => m.id === e.message.id)) {
                messages.value.push(e.message);
                scrollToBottom();
            }
        });
};

const filteredMembers = computed(() => {
    if (!searchQuery.value) return members.value;
    return members.value.filter(m => 
        m.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        m.role.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

const getInitials = (name) => name?.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase() || '??';

onMounted(() => {
    fetchData();
    fetchMessages();
    listenToChannel(activeChatId.value);

    // Listen for personal notifications (like calls)
    echo.channel(`user.${currentUser.id}`)
        .listen('.ChatCallSent', (e) => {
            if (!activeCall.value) {
                activeCall.value = {
                    user: e.fromUser,
                    type: e.type,
                    status: 'incoming',
                    channelId: e.channelId
                };
                playRingtone();
            }
        })
        .listen('.ChatCallResponse', (e) => {
            if (activeCall.value && activeCall.value.status === 'outgoing') {
                if (e.status === 'accepted') {
                    activeCall.value.status = 'active';
                    startTimer();
                } else {
                    endCall();
                }
            }
        });
});

const requestMediaPermissions = async (type) => {
    try {
        const constraints = {
            audio: true,
            video: type === 'video'
        };
        const stream = await navigator.mediaDevices.getUserMedia(constraints);
        localStream.value = stream;
        
        // Attach to local video element
        nextTick(() => {
            if (localVideo.value) {
                localVideo.value.srcObject = stream;
            }
        });
        
        return true;
    } catch (error) {
        console.error("Media permission denied:", error);
        alert("Camera and Microphone access are required for calls.");
        return false;
    }
};

const startCall = async (type = 'audio') => {
    if (activeChatType.value !== 'dm') return;
    
    // Request permission first
    const permitted = await requestMediaPermissions(type);
    if (!permitted) return;

    console.log(`Starting ${type} call to ${activeChatUser.value.name}...`);
    
    activeCall.value = {
        user: activeChatUser.value,
        type: type,
        status: 'outgoing'
    };
    
    playRingtone();
    
    try {
        await axios.post('/api/v1/chat/call', {
            to_user_id: activeChatUser.value.id,
            type: type,
            channel_id: activeChatId.value
        });
        console.log("Call signal sent successfully.");
    } catch (error) {
        console.error("Call signal failed:", error);
        endCall();
    }
};

const acceptCall = async () => {
    if (!activeCall.value) return;
    
    // Request permission on acceptance
    const permitted = await requestMediaPermissions(activeCall.value.type);
    if (!permitted) {
        endCall();
        return;
    }

    console.log("Accepting call from " + activeCall.value.user.name);
    stopRingtone();
    activeCall.value.status = 'active';
    startTimer();
    
    // Notify caller
    try {
        await axios.post('/api/v1/chat/call-response', {
            to_user_id: activeCall.value.user.id,
            status: 'accepted'
        });
    } catch (e) {}

    if (activeCall.value.channelId) {
        selectChatById(activeCall.value.channelId);
    }
};

const endCall = async () => {
    if (activeCall.value) {
        console.log("Ending call with " + activeCall.value.user.name);
        try {
            await axios.post('/api/v1/chat/call-response', {
                to_user_id: activeCall.value.user.id,
                status: 'rejected'
            });
        } catch (e) {}
    }
    
    // Stop local streams
    if (localStream.value) {
        localStream.value.getTracks().forEach(track => track.stop());
        localStream.value = null;
    }
    
    stopRingtone();
    clearInterval(callInterval.value);
    activeCall.value = null;
    callTimer.value = 0;
};

const playRingtone = () => {
    // Simulated audio logic
    console.log("Ringing...");
};

const stopRingtone = () => {
    console.log("Call stopped.");
};

const startTimer = () => {
    callTimer.value = 0;
    callInterval.value = setInterval(() => {
        callTimer.value++;
    }, 1000);
};

const formatTime = (seconds) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
};

const selectChatById = (id) => {
    const chat = channels.value.find(c => c.id === id);
    if (chat) selectChat(chat);
};

onUnmounted(() => {
    echo.leave(`chat.${activeChatId.value}`);
    echo.leave(`user.${currentUser.id}`);
    stopRingtone();
    clearInterval(callInterval.value);
});

const showCreateGroupModal = ref(false);
const newGroupName = ref('');
const selectedMemberIds = ref([]);

const createGroup = async () => {
    if (!newGroupName.value.trim() || selectedMemberIds.value.length === 0) return;
    try {
        const response = await axios.post('/api/v1/chat/groups', {
            name: newGroupName.value,
            member_ids: selectedMemberIds.value
        });
        if (response.data.success) {
            channels.value.push(response.data.group);
            showCreateGroupModal.value = false;
            newGroupName.value = '';
            selectedMemberIds.value = [];
            selectChat(response.data.group);
        }
    } catch (error) {
        console.error("Failed to create group:", error);
    }
};

const toggleMemberSelection = (id) => {
    const index = selectedMemberIds.value.indexOf(id);
    if (index === -1) {
        selectedMemberIds.value.push(id);
    } else {
        selectedMemberIds.value.splice(index, 1);
    }
};

const viewProfile = () => {
    showInfoSidebar.value = true;
    console.log("Viewing profile...");
};

const viewMedia = () => {
    alert("Media gallery coming soon!");
};

watch(activeChatId, () => {
    scrollToBottom();
});
</script>

<template>
    <div class="messenger-theme h-full flex bg-white text-slate-900 overflow-hidden font-sans">
        <!-- Sidebar -->
        <aside class="w-80 border-r border-slate-200 flex flex-col shrink-0">
            <!-- Sidebar Header -->
            <div class="p-4 flex items-center justify-between">
                <h1 class="text-2xl font-black tracking-tight">Chats</h1>
                <div class="flex gap-2">
                    <button class="w-9 h-9 bg-slate-100 rounded-full flex items-center justify-center hover:bg-slate-200 transition-colors">
                        <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </button>
                    <button 
                        @click="showCreateGroupModal = true"
                        class="w-9 h-9 bg-slate-100 rounded-full flex items-center justify-center hover:bg-slate-200 transition-colors"
                        title="Create Group"
                    >
                        <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    </button>
                </div>
            </div>

            <!-- Search -->
            <div class="px-4 mb-4">
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </span>
                    <input 
                        v-model="searchQuery"
                        type="text" 
                        placeholder="Search Messenger"
                        class="w-full bg-slate-100 border-none rounded-full py-2.5 pl-10 pr-4 text-sm focus:ring-0 placeholder-slate-500"
                    >
                </div>
            </div>

            <!-- Tab Switcher -->
            <div class="px-4 mb-2 flex gap-1">
                <button 
                    @click="activeView = 'chats'"
                    class="flex-1 py-1.5 rounded-lg text-sm font-bold transition-all"
                    :class="activeView === 'chats' ? 'bg-blue-50 text-blue-600' : 'text-slate-500 hover:bg-slate-50'"
                >
                    Inbox
                </button>
                <button 
                    @click="activeView = 'people'"
                    class="flex-1 py-1.5 rounded-lg text-sm font-bold transition-all"
                    :class="activeView === 'people' ? 'bg-blue-50 text-blue-600' : 'text-slate-500 hover:bg-slate-50'"
                >
                    People
                </button>
            </div>

            <!-- List Area -->
            <div class="flex-1 overflow-y-auto overflow-x-hidden">
                <!-- Inbox View (Channels + Recent DMs) -->
                <div v-if="activeView === 'chats'" class="space-y-0.5 px-2">
                    <div class="text-[11px] font-bold text-slate-400 px-3 py-2 uppercase tracking-wider">Public Groups</div>
                    <button
                        v-for="ch in channels"
                        :key="ch.id"
                        @click="selectChat({ ...ch, type: 'channel' })"
                        class="w-full text-left p-3 rounded-xl flex items-center gap-3 transition-all group"
                        :class="activeChatId === ch.id ? 'bg-blue-50' : 'hover:bg-slate-50'"
                    >
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-xl shadow-sm">
                            {{ ch.icon || '#' }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-bold text-slate-900 truncate">{{ ch.name }}</div>
                            <div class="text-xs text-slate-500 truncate font-medium">Click to join discussion</div>
                        </div>
                    </button>
                </div>

                <!-- People View (All Members) -->
                <div v-else class="space-y-0.5 px-2">
                    <div v-for="(members, role) in membersByRole" :key="role">
                        <div class="text-[11px] font-bold text-slate-400 px-3 py-2 uppercase tracking-wider">{{ role }}</div>
                        <button
                            v-for="m in members"
                            :key="m.id"
                            v-show="m.id !== currentUser.id"
                            @click="selectChat({ ...m, type: 'dm' })"
                            class="w-full text-left p-2 rounded-xl flex items-center gap-3 transition-all"
                            :class="activeChatId.includes(`:${m.id}:`) || activeChatId.endsWith(`:${m.id}`) ? 'bg-blue-50' : 'hover:bg-slate-50'"
                        >
                            <div class="relative">
                                <img :src="'https://ui-avatars.com/api/?name=' + m.name + '&background=f1f5f9&color=6366f1'" class="w-10 h-10 rounded-full border border-slate-100">
                                <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white"></div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-bold text-slate-900 truncate text-sm">{{ m.name }}</div>
                                <div class="text-[10px] text-slate-500 uppercase font-black tracking-tighter">{{ m.role }}</div>
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- My Status -->
            <div class="p-4 border-t border-slate-100 flex items-center gap-3 bg-slate-50/50">
                <img :src="'https://ui-avatars.com/api/?name=' + currentUser.name + '&background=3b82f6&color=fff'" class="w-9 h-9 rounded-full shadow-sm">
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-bold truncate text-slate-900">{{ currentUser.name }}</div>
                    <div class="text-[10px] text-slate-400 font-bold uppercase">{{ currentUser.role }}</div>
                </div>
            </div>
        </aside>

        <!-- Main Chat Area -->
        <main class="flex-1 flex flex-col bg-white">
            <!-- Header -->
            <header class="h-16 px-4 flex items-center justify-between border-b border-slate-100 shadow-sm z-10">
                <div class="flex items-center gap-3">
                    <div v-if="activeChatType === 'dm'" class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center border border-slate-100">
                         <img :src="'https://ui-avatars.com/api/?name=' + activeChatUser?.name + '&background=f1f5f9&color=6366f1'" class="w-full h-full rounded-full">
                    </div>
                    <div v-else class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white text-xl">
                        {{ channels.find(c => c.id === activeChatId)?.icon || '#' }}
                    </div>
                    <div>
                        <div class="font-bold text-slate-900">{{ activeChatType === 'dm' ? activeChatUser?.name : (channels.find(c => c.id === activeChatId)?.name || activeChatId) }}</div>
                        <div class="text-[10px] text-green-600 font-bold flex items-center gap-1">
                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span> Active now
                        </div>
                    </div>
                </div>
                <div class="flex gap-2 text-blue-600">
                    <button @click="showInfoSidebar = !showInfoSidebar" class="p-2.5 rounded-full hover:bg-slate-50 transition-colors" :class="{ 'bg-slate-100': showInfoSidebar }">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </button>
                    <button @click="startCall('audio')" class="p-2.5 rounded-full hover:bg-slate-50 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                    </button>
                    <button @click="startCall('video')" class="p-2.5 rounded-full hover:bg-slate-50 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                    </button>
                </div>
            </header>

            <!-- Messages Area -->
            <div ref="messagesContainer" class="flex-1 overflow-y-auto p-6 space-y-3">
                <!-- Welcome Info -->
                <div class="flex flex-col items-center py-16 text-center">
                    <div class="w-24 h-24 rounded-full bg-slate-50 mb-6 flex items-center justify-center text-5xl shadow-inner border border-slate-100 overflow-hidden">
                         <img v-if="activeChatType === 'dm'" :src="'https://ui-avatars.com/api/?name=' + activeChatUser?.name + '&background=f1f5f9&color=6366f1'" class="w-full h-full object-cover">
                         <span v-else>{{ channels.find(c => c.id === activeChatId)?.icon }}</span>
                    </div>
                    <h2 class="text-2xl font-black text-slate-900">{{ activeChatType === 'dm' ? activeChatUser?.name : activeChatId }}</h2>
                    <p class="text-slate-500 text-sm max-w-xs mt-3 leading-relaxed">
                        {{ activeChatType === 'dm' ? 'You\'re friends on IT Club OS. Direct messages are private.' : 'Everyone in the club can see messages in public groups.' }}
                    </p>
                    <button @click="showInfoSidebar = true" class="mt-6 px-6 py-2 bg-slate-100 text-sm font-bold text-blue-600 rounded-full hover:bg-slate-200 transition-all">View Profile</button>
                </div>

                <!-- Message Grouping -->
                <div v-for="(msg, index) in messages" :key="msg.id" class="flex flex-col">
                    <!-- Date separator -->
                    <div v-if="index === 0 || new Date(messages[index-1].created_at).toDateString() !== new Date(msg.created_at).toDateString()" class="text-center my-8">
                        <span class="text-[10px] font-black uppercase text-slate-300 tracking-[0.2em]">{{ new Date(msg.created_at).toLocaleDateString([], { month: 'long', day: 'numeric' }) }}</span>
                    </div>

                    <div class="flex group gap-3" :class="msg.user_id === currentUser.id ? 'flex-row-reverse' : 'flex-row'">
                        <!-- Profile Image -->
                        <div class="w-8 h-8 shrink-0 self-end mb-1" v-if="msg.user_id !== currentUser.id">
                            <img :src="'https://ui-avatars.com/api/?name=' + msg.user?.name + '&background=f1f5f9&color=6366f1'" class="w-full h-full rounded-full border border-slate-100">
                        </div>
                        <div class="w-8 h-8 shrink-0 v-else" v-else></div>

                        <!-- Message Content Stack -->
                        <div class="max-w-[75%] flex flex-col" :class="msg.user_id === currentUser.id ? 'items-end' : 'items-start'">
                            <!-- Name for groups -->
                            <span v-if="activeChatType === 'channel' && msg.user_id !== currentUser.id" class="text-[10px] font-bold text-slate-400 ml-2 mb-1">
                                {{ msg.user?.name }}
                            </span>
                            
                            <div class="relative group/bubble">
                                <div 
                                    class="px-5 py-3 rounded-[22px] text-[15px] leading-snug whitespace-pre-wrap shadow-sm border"
                                    :class="msg.user_id === currentUser.id 
                                        ? 'bg-blue-600 text-white border-blue-600 rounded-tr-none' 
                                        : 'bg-slate-100 text-slate-900 border-slate-100 rounded-tl-none'"
                                >
                                    <!-- File Display -->
                                    <div v-if="msg.file_path" class="mb-3">
                                        <div v-if="msg.file_type?.startsWith('image/')" class="rounded-xl overflow-hidden cursor-pointer hover:opacity-95 transition-opacity border-none">
                                            <img :src="'/storage/' + msg.file_path" class="max-w-full max-h-80 object-cover">
                                        </div>
                                        <div v-else class="flex items-center gap-3 p-3 bg-black/10 rounded-xl text-white">
                                            <svg class="w-8 h-8 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                            <div class="flex-1 min-w-0">
                                                <div class="text-[10px] font-black tracking-widest uppercase opacity-50">FILE</div>
                                                <a :href="'/storage/' + msg.file_path" target="_blank" class="text-sm font-bold hover:underline truncate block">Download Attachment</a>
                                            </div>
                                        </div>
                                    </div>
                                    {{ msg.content }}
                                </div>
                                
                                <!-- Read Status (Simulated) -->
                                <div v-if="msg.user_id === currentUser.id" class="text-[10px] text-blue-500 mt-1 mr-1 flex items-center gap-0.5">
                                    <span>✔✔</span>
                                </div>
                                
                                <!-- Hover Actions -->
                                <div 
                                    class="absolute top-1/2 -translate-y-1/2 opacity-0 group-hover/bubble:opacity-100 transition-opacity whitespace-nowrap px-4"
                                    :class="msg.user_id === currentUser.id ? 'right-full' : 'left-full'"
                                >
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] font-bold text-slate-300">{{ new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}</span>
                                        <button v-if="msg.user_id === currentUser.id" @click="deleteMessage(msg.id)" class="hover:text-red-500 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Input Area -->
            <div class="p-6 bg-white border-t border-slate-50">
                <!-- File Preview -->
                <div v-if="selectedFile" class="mb-3 flex items-center gap-3 p-2 bg-slate-50 rounded-xl border border-slate-100">
                    <div v-if="filePreview" class="w-12 h-12 rounded-lg overflow-hidden border">
                        <img :src="filePreview" class="w-full h-full object-cover">
                    </div>
                    <div v-else class="w-12 h-12 bg-slate-200 rounded-lg flex items-center justify-center text-xl">
                        📄
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-bold text-slate-900 truncate">{{ selectedFile.name }}</div>
                        <div class="text-[10px] text-slate-500 uppercase">{{ (selectedFile.size / 1024).toFixed(1) }} KB</div>
                    </div>
                    <button @click="removeFile" class="w-8 h-8 rounded-full hover:bg-slate-200 flex items-center justify-center text-slate-400 hover:text-red-500">×</button>
                </div>

                <div class="flex items-center gap-3 relative">
                    <!-- Hidden File Input -->
                    <input type="file" ref="fileInput" @change="handleFileChange" class="hidden">

                    <button @click="triggerFileSelect" class="text-blue-600 p-2 rounded-full hover:bg-slate-50 transition-all">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                    </button>
                    <button @click="triggerFileSelect" class="text-blue-600 p-2 rounded-full hover:bg-slate-50 transition-all">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    </button>
                    
                    <div class="flex-1 relative flex items-center">
                        <input 
                            v-model="messageInput"
                            @keydown.enter="sendMessage"
                            type="text" 
                            :placeholder="'Type a message to ' + (activeChatType === 'dm' ? activeChatUser?.name : activeChatId) + '...'"
                            class="w-full bg-slate-100 border-none rounded-full py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-100 transition-all"
                            :disabled="sending"
                        >
                        <button @click="showEmojiPicker = !showEmojiPicker" class="absolute right-3 text-slate-400 hover:text-blue-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </button>
                        
                        <!-- Emoji Picker -->
                        <div v-if="showEmojiPicker" class="absolute bottom-full right-0 mb-4 bg-white p-4 rounded-3xl shadow-2xl border border-slate-100 max-h-80 w-80 overflow-y-auto grid grid-cols-6 gap-2 z-[110]">
                            <button 
                                v-for="emoji in commonEmojis" 
                                :key="emoji"
                                @click="addEmoji(emoji)"
                                class="w-10 h-10 flex items-center justify-center hover:bg-slate-100 rounded-xl text-xl transition-colors"
                            >
                                {{ emoji }}
                            </button>
                        </div>
                    </div>
                    
                    <button 
                        @click="sendMessage"
                        :disabled="!messageInput.trim() || sending"
                        class="w-11 h-11 bg-blue-600 text-white rounded-full flex items-center justify-center shadow-lg shadow-blue-200 hover:bg-blue-700 disabled:bg-slate-200 disabled:shadow-none transition-all shrink-0"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                    </button>
                </div>
                <div class="text-center mt-2">
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Powered by IT Club Realtime Engine</span>
                </div>
            </div>
        </main>

        <!-- Right Info Sidebar -->
        <aside v-if="showInfoSidebar" class="w-72 border-l border-slate-100 bg-white flex flex-col items-center p-6 shrink-0 hidden lg:flex">
            <div class="w-24 h-24 rounded-full bg-slate-50 mb-4 flex items-center justify-center text-4xl shadow-inner italic border border-slate-100 overflow-hidden">
                <img v-if="activeChatType === 'dm'" :src="'https://ui-avatars.com/api/?name=' + activeChatUser?.name + '&background=f1f5f9&color=6366f1'" class="w-full h-full object-cover">
                <span v-else>{{ channels.find(c => c.id === activeChatId)?.icon }}</span>
            </div>
            <h2 class="text-xl font-black text-slate-900 text-center">{{ activeChatType === 'dm' ? activeChatUser?.name : activeChatId }}</h2>
            <p class="text-[11px] text-slate-400 font-bold uppercase mt-1">{{ activeChatType === 'dm' ? activeChatUser?.role : 'Public Group' }}</p>

            <div class="w-full mt-8 space-y-2">
                <button @click="viewProfile" class="w-full flex items-center justify-between p-3 hover:bg-slate-50 rounded-xl transition-colors font-bold text-sm text-slate-700">
                    <span>Profile Settings</span>
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                </button>
                <button @click="viewMedia" class="w-full flex items-center justify-between p-3 hover:bg-slate-50 rounded-xl transition-colors font-bold text-sm text-slate-700">
                    <span>Media & Files</span>
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                </button>
                <button @click="activeCall = null" class="w-full flex items-center justify-between p-3 hover:bg-slate-50 rounded-xl transition-colors font-bold text-sm text-red-500">
                    <span>Block / Leave</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                </button>
            </div>

            <div class="mt-auto p-4 bg-blue-50 rounded-2xl w-full text-center">
                <p class="text-[10px] font-bold text-blue-600 uppercase tracking-widest mb-1">CCT Realtime</p>
                <p class="text-xs text-blue-400">End-to-end encrypted signals enabled</p>
            </div>
        </aside>

        <!-- Create Group Modal (Existing) -->
        <!-- ... -->
        
        <!-- CALL OVERLAY -->
        <Transition name="scale">
            <div v-if="activeCall" class="absolute inset-0 z-[200] bg-slate-900/95 backdrop-blur-2xl flex flex-col items-center justify-center text-white overflow-hidden p-8">
                <!-- Background Animated Circles -->
                <div class="absolute inset-0 overflow-hidden pointer-events-none opacity-20">
                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-blue-500 rounded-full blur-[120px] animate-pulse"></div>
                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[400px] h-[400px] bg-purple-500 rounded-full blur-[100px] animate-pulse delay-700"></div>
                </div>

                <!-- Background Media / Video Streams -->
                <div v-if="activeCall.type === 'video' && activeCall.status === 'active'" class="absolute inset-0 z-0">
                    <video ref="remoteVideo" autoplay playsinline class="w-full h-full object-cover opacity-60"></video>
                    <!-- Local Preview (PIP) -->
                    <div class="absolute bottom-32 right-8 w-48 h-64 bg-black rounded-2xl overflow-hidden border-2 border-white/20 shadow-2xl z-20">
                        <video ref="localVideo" autoplay muted playsinline class="w-full h-full object-cover"></video>
                    </div>
                </div>

                <!-- Call Content -->
                <div class="relative z-10 flex flex-col items-center text-center">
                    <div class="relative mb-8" v-if="activeCall.status !== 'active' || activeCall.type === 'audio'">
                        <div class="absolute inset-0 bg-blue-500 rounded-full animate-ping opacity-30" v-if="activeCall.status !== 'active'"></div>
                        <img 
                            :src="'https://ui-avatars.com/api/?name=' + activeCall.user?.name + '&background=3b82f6&color=fff&size=200'" 
                            class="w-32 h-32 rounded-full border-4 border-white/20 shadow-2xl relative z-10"
                        >
                        <div v-if="activeCall.type === 'video'" class="absolute -bottom-2 -right-2 w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center border-4 border-slate-900">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                        </div>
                    </div>
                    
                    <!-- Minified profile when video is active -->
                    <div v-else class="absolute top-8 left-8 flex items-center gap-3 bg-black/40 backdrop-blur-md p-3 rounded-2xl border border-white/10 z-30">
                         <img 
                            :src="'https://ui-avatars.com/api/?name=' + activeCall.user?.name + '&background=3b82f6&color=fff'" 
                            class="w-10 h-10 rounded-full border border-white/20"
                        >
                        <div class="text-left">
                            <div class="text-sm font-bold">{{ activeCall.user?.name }}</div>
                            <div class="text-[10px] text-blue-400 font-bold uppercase tracking-widest">{{ formatTime(callTimer) }}</div>
                        </div>
                    </div>

                    <h2 class="text-3xl font-black tracking-tight mb-2">{{ activeCall.user?.name }}</h2>
                    <p class="text-blue-400 font-bold uppercase tracking-widest text-xs mb-8">
                        <span v-if="activeCall.status === 'outgoing'">Calling...</span>
                        <span v-else-if="activeCall.status === 'incoming'">Incoming {{ activeCall.type }} call...</span>
                        <span v-else-if="activeCall.status === 'active'">On Call - {{ formatTime(callTimer) }}</span>
                    </p>

                    <!-- Call Actions -->
                    <div class="flex gap-8 items-center mt-8">
                        <!-- Decline / End Call -->
                        <button 
                            @click="endCall"
                            class="w-16 h-16 bg-red-500 rounded-full flex items-center justify-center text-2xl hover:bg-red-600 hover:scale-110 transition-all shadow-lg shadow-red-500/20"
                        >
                            <svg class="w-8 h-8 text-white rotate-[135deg]" fill="currentColor" viewBox="0 0 24 24"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                        </button>

                        <!-- Accept (Only if incoming) -->
                        <button 
                            v-if="activeCall.status === 'incoming'"
                            @click="acceptCall"
                            class="w-20 h-20 bg-green-500 rounded-full flex items-center justify-center text-3xl hover:bg-green-600 hover:scale-110 transition-all shadow-lg shadow-green-500/20 animate-bounce"
                        >
                            <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                        </button>

                        <!-- Extra Controls (If active) -->
                        <div v-if="activeCall.status === 'active'" class="flex gap-4">
                            <button class="w-12 h-12 bg-white/10 rounded-full flex items-center justify-center hover:bg-white/20 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" /></svg>
                            </button>
                            <button class="w-12 h-12 bg-white/10 rounded-full flex items-center justify-center hover:bg-white/20 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" /></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Footer Text -->
                <div class="absolute bottom-8 left-0 right-0 text-center opacity-40">
                    <p class="text-[10px] font-bold uppercase tracking-widest">Secure CCT Connection • Encrypted Signals</p>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.scale-enter-active, .scale-leave-active {
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.scale-enter-from, .scale-leave-to {
  opacity: 0;
  transform: scale(0.9);
}

.messenger-theme {
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

::-webkit-scrollbar {
    width: 6px;
}
::-webkit-scrollbar-track {
    background: transparent;
}
::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}

/* Messenger bubble logic */
.rounded-3xl {
    border-radius: 18px;
}

/* Animation for bubbles */
@keyframes bubbleFade {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}
.messenger-theme .flex.group {
    animation: bubbleFade 0.2s ease-out forwards;
}
</style>
