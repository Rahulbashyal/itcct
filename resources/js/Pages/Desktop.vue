<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import Window from '@/Components/WebDE/Window.vue';
import ElectionApp from '@/Components/WebDE/Apps/ElectionApp.vue';
import HallOfFameApp from '@/Components/WebDE/Apps/HallOfFameApp.vue';
import TransparencyApp from '@/Components/WebDE/Apps/TransparencyApp.vue';
import CodePlayground from '@/Components/WebDE/Apps/CodePlayground.vue';
import ChatApp from '@/Components/WebDE/Apps/ChatApp.vue';
import LogApp from '@/Components/WebDE/Apps/LogApp.vue';
import NewsApp from '@/Components/WebDE/Apps/NewsApp.vue';
import EventsApp from '@/Components/WebDE/Apps/EventsApp.vue';
import GovernanceApp from '@/Components/WebDE/Apps/GovernanceApp.vue';
import LearningApp from '@/Components/WebDE/Apps/LearningApp.vue';
import UserManApp from '@/Components/WebDE/Apps/UserManApp.vue';
import ProfileVerifyApp from '@/Components/WebDE/Apps/ProfileVerifyApp.vue';
import NexusApp from '@/Components/WebDE/Apps/NexusApp.vue';
import TerminalApp from '@/Components/WebDE/Apps/TerminalApp.vue';
import Browser from '@/Components/WebDE/Apps/Browser.vue';
import axios from 'axios';
import interact from 'interactjs';

// Component Registry for dynamic loading
const componentRegistry = {
    'ElectionApp': ElectionApp,
    'HallOfFameApp': HallOfFameApp,
    'TransparencyApp': TransparencyApp,
    'CodePlayground': CodePlayground,
    'ChatApp': ChatApp,
    'LogApp': LogApp,
    'NewsApp': NewsApp,
    'EventsApp': EventsApp,
    'GovernanceApp': GovernanceApp,
    'LearningApp': LearningApp,
    'UserManApp': UserManApp,
    'ProfileVerifyApp': ProfileVerifyApp,
    'NexusApp': NexusApp,
    'TerminalApp': TerminalApp,
    'Browser': Browser,
};

const page = usePage();
const currentTime = ref(new Date());
const openWindows = ref([]);
const availableApps = ref([]);
const user = ref(null);
const permissions = ref({});
const searchQuery = ref('');
const isSearchOpen = ref(false);
const volume = ref(75);
const isVolumeOpen = ref(false);
const isUserMenuOpen = ref(false);
const isCalendarOpen = ref(false);
const iconPositions = ref({});
const wallpaper = ref(localStorage.getItem('webde_wallpaper') || '/images/bg.png');
const contextMenu = ref({ show: false, x: 0, y: 0, type: 'desktop', targetId: null });
const isAltTabOpen = ref(false);
const activeSwitcherIndex = ref(0);
const notifications = ref([]);
let timeInterval = null;

const pushNotification = (title, message, type = 'info') => {
    const id = Date.now();
    notifications.value.push({ id, title, message, type });
    if (volume.value > 0) {
        const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2358/2358-preview.mp3');
        audio.volume = volume.value / 400; // Very subtle
        audio.play().catch(() => {});
    }
    setTimeout(() => {
        notifications.value = notifications.value.filter(n => n.id !== id);
    }, 5000);
};

const availableWallpapers = [
    { name: 'Aurora Deep', url: '/images/bg.png' },
    { name: 'Night City', url: 'https://images.unsplash.com/photo-1514565131-fce0801e5785?auto=format&fit=crop&q=80&w=2000' },
    { name: 'Abstract Flow', url: 'https://images.unsplash.com/photo-1550684848-fac1c5b4e853?auto=format&fit=crop&q=80&w=2000' },
    { name: 'Cyberpunk', url: 'https://images.unsplash.com/photo-1605142859862-978be7eba909?auto=format&fit=crop&q=80&w=2000' },
    { name: 'Clean Minimal', url: 'https://images.unsplash.com/photo-1494438639946-1ebd1d20bf85?auto=format&fit=crop&q=80&w=2000' }
];

const setWallpaper = (url) => {
    wallpaper.value = url;
    localStorage.setItem('webde_wallpaper', url);
    contextMenu.value.show = false;
};

const handleContextMenu = (e, type, targetId = null) => {
    e.preventDefault();
    contextMenu.value = {
        show: true,
        x: e.clientX,
        y: e.clientY,
        type: type,
        targetId: targetId
    };
};

const closeContextMenu = () => {
    contextMenu.value.show = false;
};

// Keyboard Shortcuts
const handleKeyDown = (e) => {
    // Alt + Tab
    if (e.altKey && e.key === 'Tab' && openWindows.value.length > 0) {
        e.preventDefault();
        if (!isAltTabOpen.value) {
            isAltTabOpen.value = true;
            activeSwitcherIndex.value = 0;
        } else {
            activeSwitcherIndex.value = (activeSwitcherIndex.value + 1) % openWindows.value.length;
        }
    }
    // Win + D (Meta + D)
    if (e.metaKey && e.key === 'd') {
        e.preventDefault();
        const allMinimized = openWindows.value.every(w => w.isMinimized);
        openWindows.value.forEach(w => w.isMinimized = !allMinimized);
    }
};

const handleKeyUp = (e) => {
    if (e.key === 'Alt') {
        if (isAltTabOpen.value) {
            const targetApp = openWindows.value[activeSwitcherIndex.value];
            if (targetApp) {
                targetApp.isMinimized = false;
                focusWindow(targetApp.id);
            }
            isAltTabOpen.value = false;
        }
    }
};
const marquee = ref({ active: false, x1: 0, y1: 0, x2: 0, y2: 0 });

const startMarquee = (e) => {
    if (e.button !== 0) return; // Only left click
    if (contextMenu.value.show) contextMenu.value.show = false;
    marquee.value = { active: true, x1: e.clientX, y1: e.clientY, x2: e.clientX, y2: e.clientY };
    window.addEventListener('mousemove', updateMarquee);
    window.addEventListener('mouseup', endMarquee);
};

const updateMarquee = (e) => {
    marquee.value.x2 = e.clientX;
    marquee.value.y2 = e.clientY;
};

const endMarquee = () => {
    marquee.value.active = false;
    window.removeEventListener('mousemove', updateMarquee);
    window.removeEventListener('mouseup', endMarquee);
};

const marqueeStyle = computed(() => {
    const x = Math.min(marquee.value.x1, marquee.value.x2);
    const y = Math.min(marquee.value.y1, marquee.value.y2);
    const w = Math.abs(marquee.value.x2 - marquee.value.x1);
    const h = Math.abs(marquee.value.y2 - marquee.value.y1);
    return {
        left: x + 'px',
        top: y + 'px',
        width: w + 'px',
        height: h + 'px'
    };
});

const filteredApps = computed(() => {
    if (!searchQuery.value) return availableApps.value;
    return availableApps.value.filter(appId => {
        const app = appRegistry[appId];
        return app && app.title.toLowerCase().includes(searchQuery.value.toLowerCase());
    });
});

// Time-based scene logic
const sceneClass = computed(() => {
    const hour = currentTime.value.getHours();
    if (hour >= 5 && hour < 9) return 'scene-morning';
    if (hour >= 9 && hour < 17) return 'scene-day';
    if (hour >= 17 && hour < 20) return 'scene-evening';
    if (hour >= 20 && hour < 23) return 'scene-night';
    return 'scene-deep-night';
});

const formattedTime = computed(() => {
    return currentTime.value.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
});

const formattedDate = computed(() => {
    return currentTime.value.toLocaleDateString('en-US', {
        weekday: 'short',
        month: 'short',
        day: 'numeric'
    });
});

const handleSearchBlur = () => {
    window.setTimeout(() => {
        isSearchOpen.value = false;
    }, 200);
};

// App registry with folder accent colors and custom icons
// isApp: true = opens in window, false = desktop folder only
const appRegistry = {
    'nexus':          { title: 'Nexus',         color: '#6366f1', badge: '🌐', icon: '/images/icons/nexus.png', component: 'NexusApp', isApp: true },
    'code-playground':{ title: 'IDE Lab',       color: '#8b5cf6', badge: '⟨/⟩', icon: '/images/icons/ide.png', component: 'CodePlayground', isApp: true },
    'terminal':       { title: 'Terminal',      color: '#10b981', badge: '>_', icon: '/images/icons/terminal.png', component: 'TerminalApp', isApp: true },
    'elections':      { title: 'Elections',     color: '#6366f1', badge: '🗳', icon: '/images/icons/vote.png', component: 'ElectionApp', isApp: true },
    'learning':       { title: 'Learning Hub',  color: '#3b82f6', badge: '📚', component: 'LearningApp', isApp: false },
    'hall-of-fame':   { title: 'Hall of Fame',  color: '#eab308', badge: '🏆', component: 'HallOfFameApp', isApp: false },
    'transparency':   { title: 'Transparency',  color: '#06b6d4', badge: '🛡', component: 'TransparencyApp', isApp: false },
    'governance':     { title: 'Governance',    color: '#64748b', badge: '🏛', component: 'GovernanceApp', isApp: false },
    'system-logs':    { title: 'System Logs',   color: '#ef4444', badge: '📋', component: 'LogApp', isApp: false },
    'finance':        { title: 'Finance',       color: '#10b981', badge: '💰', component: 'TransparencyApp', isApp: false },
    'nexus-browser':  { 
        title: 'Nexus Browser',   
        color: '#10b981', 
        badge: '🌍', 
        svg: `<svg viewBox="0 0 24 24" class="w-7 h-7" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="12" cy="12" r="10" fill="url(#browserGradient)" />
                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" stroke="white" stroke-opacity="0.2" />
                <path d="M2 12h20M7 3.3a10.11 10.11 0 0 1 10 0M7 20.7a10.11 10.11 0 0 0 10 0" stroke="white" stroke-opacity="0.2" />
                <defs>
                    <linearGradient id="browserGradient" x1="2" y1="2" x2="22" y2="22" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#34d399"/>
                        <stop offset="1" stop-color="#059669"/>
                    </linearGradient>
                </defs>
            </svg>`,
        component: 'Browser', 
        isApp: true 
    },
    'profile-verification': { title: 'Verify Email', color: '#ef4444', badge: '✉', component: 'ProfileVerifyApp', isApp: true },
};

const pinnedApps = ['nexus', 'terminal', 'elections', 'code-playground', 'nexus-browser'];

// Desktop icons (folders only, exclude windowed apps and pinned apps)
const desktopIcons = computed(() => {
    return availableApps.value.filter(appId => {
        const isApp = appRegistry[appId]?.isApp;
        const isPinned = pinnedApps.includes(appId);
        return !isApp && !isPinned;
    });
});

// --- State Persistence ---

const saveState = () => {
    localStorage.setItem('webde_desktop_windows', JSON.stringify(openWindows.value));
    localStorage.setItem('webde_desktop_icons', JSON.stringify(iconPositions.value));
};

const restoreState = () => {
    try {
        const savedWindows = localStorage.getItem('webde_desktop_windows');
        if (savedWindows) {
            openWindows.value = JSON.parse(savedWindows);
        }

        const savedIcons = localStorage.getItem('webde_desktop_icons');
        if (savedIcons) {
            iconPositions.value = JSON.parse(savedIcons);
        }
    } catch (e) {
        console.error('[Desktop] Failed to restore desktop state:', e);
    }
};

// Auto-save watchers
let saveTimeout = null;
const maxZIndex = ref(1000);
const debouncedSave = () => {
    if (saveTimeout) window.clearTimeout(saveTimeout);
    try {
        saveTimeout = window.setTimeout(saveState, 500);
    } catch (e) {
        console.error('[Desktop] Debounce error:', e);
        saveState();
    }
};

watch(openWindows, debouncedSave, { deep: true });
watch(iconPositions, debouncedSave, { deep: true });

onMounted(async () => {
    window.addEventListener('keydown', handleKeyDown);
    window.addEventListener('keyup', handleKeyUp);
    window.addEventListener('click', closeContextMenu);

    restoreState();

    // Try fetching from API, fallback to page props
    try {
        const response = await axios.get('/api/v1/desktop/icons');
        availableApps.value = response.data.apps;
        user.value = response.data.user;
        permissions.value = response.data.user?.permissions || {};
    } catch (error) {
        console.warn('Desktop API failed, using fallback from props:', error);
        
        // Fallback: use page props
        const authUser = page.props.auth?.user;
        if (authUser) {
            user.value = { 
                name: authUser.name, 
                role: authUser.role,
                symbol_number: authUser.symbol_number 
            };
            
            // Define role-based default apps for fallback
            const roleApps = {
                'SuperAdmin': [
                    'nexus', 'terminal', 'elections', 'learning', 'hall-of-fame',
                    'code-playground', 'governance', 'transparency',
                    'system-logs'
                ],
                'President': [
                    'nexus', 'terminal', 'elections', 'learning', 'hall-of-fame',
                    'code-playground', 'governance', 'transparency'
                ],
                'Secretary': [
                    'nexus', 'terminal', 'elections', 'learning', 'hall-of-fame', 
                    'code-playground', 'finance'
                ],
                'Treasurer': [
                    'nexus', 'terminal', 'elections', 'learning', 'hall-of-fame',
                    'code-playground', 'finance', 'transparency'
                ],
                'Member': [
                    'nexus', 'terminal', 'elections', 'learning', 'hall-of-fame',
                    'code-playground', 'transparency'
                ]
            };

            availableApps.value = roleApps[authUser.role] || ['nexus', 'learning', 'code-playground'];
            
            // Basic permissions fallback
            if (authUser.role === 'SuperAdmin') {
                permissions.value = { '*': ['*'] };
            }
        } else {
            console.error('No authenticated user found in props.');
            router.visit('/login');
        }
    }

    // Initialize draggable icons after DOM is ready
    window.setTimeout(() => {
        desktopIcons.value.forEach((appId) => {
            const iconEl = document.querySelector(`[data-icon-id="${appId}"]`);
            if (iconEl) {
                // Initialize position if not set
                if (!iconPositions.value[appId]) {
                    // const rect = iconEl.getBoundingClientRect();
                    // Don't overwrite if restored from state
                } else {
                    // Apply restored position
                    const pos = iconPositions.value[appId];
                    if (pos) {
                        iconEl.style.transform = `translate(${pos.x}px, ${pos.y}px)`;
                        iconEl.setAttribute('data-x', pos.x);
                        iconEl.setAttribute('data-y', pos.y);
                    }
                }

                // Make icon draggable
                interact(iconEl)
                    .draggable({
                        inertia: true,
                        autoScroll: false,
                        listeners: {
                            move(event) {
                                const target = event.target;
                                const x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx;
                                const y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;

                                target.style.transform = `translate(${x}px, ${y}px)`;
                                target.setAttribute('data-x', x);
                                target.setAttribute('data-y', y);

                                iconPositions.value[appId] = { x, y };
                            }
                        }
                    });
            }
        });
    }, 100);

    // Initial Welcome Notification
    setTimeout(() => {
        pushNotification('System Protocol', `Welcome back, ${user.value?.name || 'Authorized User'}. All systems operational.`, 'success');
    }, 2000);
});

onUnmounted(() => {
    if (timeInterval) clearInterval(timeInterval);
    window.removeEventListener('keydown', handleKeyDown);
    window.removeEventListener('keyup', handleKeyUp);
    window.removeEventListener('click', closeContextMenu);
});

const openApp = (appId) => {
    const existingWindow = openWindows.value.find(w => w.id === appId);
    if (existingWindow) {
        // If focused and not minimized -> minimize it
        // Otherwise -> restore and focus it
        const isFocused = existingWindow.zIndex === maxZIndex.value;
        if (isFocused && !existingWindow.isMinimized) {
            existingWindow.isMinimized = true;
        } else {
            existingWindow.isMinimized = false;
            focusWindow(appId);
        }
        return;
    }
    
    const appDef = appRegistry[appId];
    if (!appDef) return;
    
    maxZIndex.value++;
    openWindows.value.push({
        id: appId,
        title: appDef.title,
        color: appDef.color,
        badge: appDef.badge,
        position: { x: 150 + (openWindows.value.length * 20), y: 80 + (openWindows.value.length * 20) },
        size: { width: 800, height: 600 },
        isMaximized: false,
        isMinimized: false,
        zIndex: maxZIndex.value
    });
};

const updateWindowState = (state) => {
    const windowIndex = openWindows.value.findIndex(w => w.id === state.appId);
    if (windowIndex !== -1) {
        openWindows.value[windowIndex] = {
            ...openWindows.value[windowIndex],
            ...state
        };
    }
};

const closeWindow = (appId) => {
    openWindows.value = openWindows.value.filter(w => w.id !== appId);
};

const minimizeWindow = (appId) => {
    const win = openWindows.value.find(w => w.id === appId);
    if (win) win.isMinimized = true;
};

const focusWindow = (appId) => {
    const win = openWindows.value.find(w => w.id === appId);
    if (win) {
        maxZIndex.value++;
        win.zIndex = maxZIndex.value;
    }
};

const logout = () => {
    router.post('/logout');
};

// Taskbar apps (windowed apps only + pinned apps)
const taskbarApps = computed(() => {
    const appsFromApi = availableApps.value.filter(appId => appRegistry[appId]?.isApp);
    // Merge pinned apps and API apps, maintaining uniqueness
    const combined = [...new Set([...pinnedApps, ...appsFromApi])];
    // Filter out any that don't exist in registry
    return combined.filter(appId => appRegistry[appId]);
});
</script>

<template>
    <Head title="Desktop — IT Club CCT" />

    <div :class="sceneClass" class="webde-desktop" @contextmenu.self="handleContextMenu($event, 'desktop')" @mousedown.self="startMarquee">
        <!-- Ambient Background -->
        <div class="scene-bg" :style="{ backgroundImage: `url(${wallpaper})` }"></div>
        <div class="scene-grain"></div>
        
        <!-- Selection Marquee -->
        <div 
            v-if="marquee.active" 
            class="marquee-box" 
            :style="marqueeStyle"
        ></div>

        <!-- Desktop Icons Grid (Folders Only) -->
        <div class="desktop-icons">
            <div
                v-for="appId in desktopIcons"
                :key="appId"
                :data-icon-id="appId"
                @dblclick="openApp(appId)"
                class="desktop-icon"
            >
                <!-- Custom SVG Icon -->
                <div v-if="appRegistry[appId]?.svg" class="w-12 h-12 flex items-center justify-center mb-3" v-html="appRegistry[appId].svg"></div>

                <!-- Custom Image Icon -->
                <div v-else-if="appRegistry[appId]?.icon" class="custom-icon-wrapper">
                    <img :src="appRegistry[appId].icon" :alt="appRegistry[appId].title" class="custom-icon-img" />
                </div>
                
                <!-- SVG Folder Icon Fallback -->
                <div v-else class="folder-wrapper">
                    <svg class="folder-svg" viewBox="0 0 80 65" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <!-- Folder back -->
                        <rect x="0" y="12" width="80" height="53" rx="4" :fill="appRegistry[appId]?.color + '40'" />
                        <!-- Folder tab -->
                        <path d="M0 8C0 5.8 1.8 4 4 4H28L34 12H0V8Z" :fill="appRegistry[appId]?.color + '60'" />
                        <!-- Folder front -->
                        <rect x="0" y="18" width="80" height="47" rx="4" :fill="appRegistry[appId]?.color + '80'" />
                        <!-- Folder highlight -->
                        <rect x="0" y="18" width="80" height="6" rx="2" :fill="appRegistry[appId]?.color + 'cc'" />
                    </svg>
                    <!-- Badge overlay -->
                    <span class="folder-badge">{{ appRegistry[appId]?.badge }}</span>
                </div>
                <span class="icon-label">{{ appRegistry[appId]?.title }}</span>
            </div>
        </div>

        <!-- Windows Container -->
        <div class="windows-layer">
            <TransitionGroup name="window-flow">
                <Window
                    v-for="win in openWindows"
                    :key="win.id"
                    :title="win.title"
                    :app-id="win.id"
                    :initial-position="win.position"
                    :initial-state="win"
                    @close="closeWindow"
                    @minimize="minimizeWindow"
                    @focus="focusWindow"
                    @update:state="updateWindowState"
                >
                    <component 
                        :is="componentRegistry[appRegistry[win.id]?.component] || 'div'"
                        :user="user"
                        class="window-content-wrapper"
                    >
                        <div v-if="!componentRegistry[appRegistry[win.id]?.component]" class="window-placeholder">
                            <span class="placeholder-badge">{{ win.badge }}</span>
                            <h3>{{ win.title }}</h3>
                            <p>Module content coming soon...</p>
                        </div>
                    </component>
                </Window>
            </TransitionGroup>
        </div>

        <!-- Taskbar -->
        <div class="taskbar">
            <!-- Left: Start Menu & Search -->
            <div class="taskbar-left">
                <button class="start-btn" @click="isUserMenuOpen = !isUserMenuOpen">
                    <img src="/images/logo.jpg" alt="IT Club Logo" class="start-logo" />
                </button>
                <div class="search-container" :class="{ 'focused': isSearchOpen }">
                    <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input 
                        type="text" 
                        v-model="searchQuery" 
                        placeholder="Search apps..." 
                        @focus="isSearchOpen = true"
                        @blur="handleSearchBlur"
                        class="search-input"
                    >
                    <!-- Search Results Popup -->
                    <div v-if="isSearchOpen && searchQuery" class="search-results">
                        <div 
                            v-for="appId in filteredApps" 
                            :key="'search-'+appId"
                            @click="openApp(appId)"
                            class="search-result-item"
                        >
                            <div v-if="appRegistry[appId]?.icon" class="res-icon-wrapper">
                                <img :src="appRegistry[appId].icon" :alt="appRegistry[appId].title" class="res-icon-img" />
                            </div>
                            <span v-else class="res-badge" :style="{ color: appRegistry[appId]?.color }">{{ appRegistry[appId]?.badge }}</span>
                            
                            <span>{{ appRegistry[appId]?.title }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Center: Circular Dock (Apps Only) -->
            <div class="taskbar-center">
                <div class="dock">
                    <div
                        v-for="appId in taskbarApps"
                        :key="'dock-' + appId"
                        class="dock-item"
                        :class="{ 'active': openWindows.find(w => w.id === appId) }"
                        @click="openApp(appId)"
                        :title="appRegistry[appId]?.title"
                    >
                        <!-- Custom SVG Icon -->
                        <div v-if="appRegistry[appId]?.svg" class="dock-circle custom-dock-icon" v-html="appRegistry[appId].svg"></div>

                        <!-- Custom Image Icon -->
                        <div v-else-if="appRegistry[appId]?.icon" class="dock-circle custom-dock-icon">
                            <img :src="appRegistry[appId].icon" :alt="appRegistry[appId].title" class="dock-icon-img" />
                        </div>

                        <!-- Fallback Circle Badge -->
                        <div v-else class="dock-circle" :style="{ backgroundColor: appRegistry[appId]?.color + '20', borderColor: appRegistry[appId]?.color + '40' }">
                            <span class="dock-badge">{{ appRegistry[appId]?.badge }}</span>
                        </div>
                        <div v-if="openWindows.find(w => w.id === appId)" class="dock-indicator"></div>
                    </div>
                </div>
            </div>

            <!-- Right: System Controls -->
            <div class="taskbar-right">
                <!-- Sound Controller -->
                <div class="tray-control">
                    <button @click="isVolumeOpen = !isVolumeOpen" class="tray-btn">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                            <path v-if="volume > 0" d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                        </svg>
                    </button>
                    <div v-if="isVolumeOpen" class="volume-popover">
                        <input type="range" v-model="volume" min="0" max="100" class="volume-slider">
                        <span class="vol-percentage">{{ volume }}%</span>
                    </div>
                </div>

                <!-- System Indicators -->
                <div class="flex items-center gap-3 px-2 border-r border-white/5 mr-2">
                    <div class="flex items-center gap-1.5 text-gray-400" title="Connected to Nexus Fiber">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" /></svg>
                    </div>
                    <div class="flex items-center gap-1.5 text-emerald-500" title="Battery: 100% (Charging)">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10.5h.75a.75.75 0 01.75.75v1.5a.75.75 0 01-.75.75H21M3 13.5V10.5A1.5 1.5 0 014.5 9h15a1.5 1.5 0 011.5 1.5v3a1.5 1.5 0 01-1.5 1.5h-15a1.5 1.5 0 01-1.5-1.5z" /></svg>
                    </div>
                </div>

                <div class="clock-display" @click="isCalendarOpen = !isCalendarOpen">
                    <div class="tray-time">{{ formattedTime }}</div>
                    <div class="tray-date">{{ formattedDate }}</div>
                    
                    <Transition name="scale">
                        <div v-if="isCalendarOpen" class="calendar-popover shadow-2xl overflow-hidden p-6" @click.stop>
                            <div class="flex justify-between items-center mb-6">
                                <span class="text-sm font-black text-white uppercase tracking-widest">{{ formattedDate }}</span>
                                <div class="flex gap-2">
                                    <div class="w-1.5 h-1.5 rounded-full bg-indigo-500"></div>
                                    <div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div>
                                </div>
                            </div>
                            <div class="grid grid-cols-7 gap-1 text-center">
                                <div v-for="day in ['S', 'M', 'T', 'W', 'T', 'F', 'S']" :key="day" class="text-[9px] font-black text-slate-500 mb-2">{{ day }}</div>
                                <div v-for="d in 31" :key="d" class="w-8 h-8 flex items-center justify-center text-[10px] font-bold rounded-lg transition-all" :class="d === new Date().getDate() ? 'bg-indigo-500 text-white shadow-lg shadow-indigo-500/30' : 'text-slate-400 hover:bg-white/10'">
                                    {{ d }}
                                </div>
                            </div>
                            <div class="mt-6 pt-6 border-t border-white/5">
                                <div class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-3">Agenda</div>
                                <div class="flex items-center gap-3 p-3 rounded-xl bg-white/5 border border-white/5">
                                    <div class="w-1 h-8 rounded-full bg-indigo-500"></div>
                                    <div>
                                        <div class="text-[10px] font-black text-white leading-none mb-1">Nexus Sync</div>
                                        <div class="text-[9px] font-bold text-slate-400">All systems green</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </div>

                <div class="user-control">
                    <button @click="isUserMenuOpen = !isUserMenuOpen" class="avatar-btn">
                        <img :src="'https://ui-avatars.com/api/?name=' + (user?.name || 'User') + '&background=random'" alt="Avatar">
                    </button>
                    
                    <Transition name="scale">
                        <div v-if="isUserMenuOpen" class="user-card shadow-2xl overflow-hidden">
                            <div class="user-card-inner">
                                <div class="p-6 bg-gradient-to-br from-indigo-600/20 to-purple-600/20">
                                    <div class="flex items-center gap-4">
                                        <div class="w-16 h-16 rounded-2xl border-2 border-indigo-500/30 overflow-hidden shadow-lg">
                                            <img :src="'https://ui-avatars.com/api/?name=' + (user?.name || 'User') + '&background=random'" class="w-full h-full object-cover">
                                        </div>
                                        <div>
                                            <div class="text-lg font-black text-white leading-none mb-1">{{ user?.name }}</div>
                                            <div class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest">{{ user?.role }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-4 grid grid-cols-2 gap-2">
                                    <button class="flex flex-col items-center gap-2 p-3 rounded-xl hover:bg-white/5 transition-all group" @click="openApp('nexus')">
                                        <div class="w-10 h-10 rounded-lg bg-indigo-500/10 flex items-center justify-center group-hover:bg-indigo-500/20">
                                            <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                                        </div>
                                        <span class="text-[10px] font-bold text-slate-400">Hub</span>
                                    </button>
                                    <button class="flex flex-col items-center gap-2 p-3 rounded-xl hover:bg-white/5 transition-all group">
                                        <div class="w-10 h-10 rounded-lg bg-emerald-500/10 flex items-center justify-center group-hover:bg-emerald-500/20">
                                            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /></svg>
                                        </div>
                                        <span class="text-[10px] font-bold text-slate-400">Settings</span>
                                    </button>
                                </div>
                                <div class="px-4 pb-4">
                                    <button @click="logout" class="w-full flex items-center justify-center gap-3 py-3 rounded-xl bg-red-500/10 hover:bg-red-500/20 text-red-400 text-xs font-black transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                                        TERMINATE SESSION
                                    </button>
                                </div>
                                <div class="bg-black/20 p-3 flex items-center justify-between">
                                    <div class="text-[8px] font-black tracking-[0.2em] text-indigo-500 uppercase">Nexus OS v4.0.2</div>
                                    <div class="flex gap-2">
                                        <div class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse"></div>
                                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </div>

        <!-- Notification Panel -->
        <div class="fixed top-6 right-6 z-[10005] flex flex-col gap-3 w-80 pointer-events-none">
            <TransitionGroup name="notification">
                <div 
                    v-for="n in notifications" 
                    :key="n.id"
                    class="notification-item pointer-events-auto bg-[#1a1b26]/90 backdrop-blur-xl border border-white/10 rounded-2xl p-4 shadow-2xl flex gap-4"
                >
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" :class="n.type === 'success' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-indigo-500/20 text-indigo-400'">
                        <svg v-if="n.type === 'success'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div>
                        <div class="text-xs font-black text-white uppercase tracking-wider mb-1">{{ n.title }}</div>
                        <div class="text-[11px] text-slate-400 leading-relaxed">{{ n.message }}</div>
                    </div>
                </div>
            </TransitionGroup>
        </div>

        <!-- Custom Context Menu -->
        <Transition name="fade">
            <div 
                v-if="contextMenu.show" 
                class="fixed z-[10000] bg-[#1a1b26]/95 backdrop-blur-xl border border-white/10 rounded-2xl shadow-2xl py-2 w-56 overflow-hidden"
                :style="{ top: contextMenu.y + 'px', left: contextMenu.x + 'px' }"
                @click.stop
            >
                <template v-if="contextMenu.type === 'desktop'">
                    <button class="w-full text-left px-4 py-2 hover:bg-white/10 text-white text-xs font-bold flex items-center gap-3 transition-colors" @click="location.reload()">
                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        Refresh Desktop
                    </button>
                    <div class="h-px bg-white/5 mx-2 my-1"></div>
                    <div class="px-4 py-2 text-[9px] font-black uppercase text-slate-500 tracking-widest">Select Wallpaper</div>
                    <button 
                        v-for="wp in availableWallpapers" 
                        :key="wp.name"
                        class="w-full text-left px-4 py-2 hover:bg-white/10 text-white text-xs transition-colors flex items-center justify-between group"
                        @click="setWallpaper(wp.url)"
                    >
                        <span>{{ wp.name }}</span>
                        <div v-if="wallpaper === wp.url" class="w-1.5 h-1.5 rounded-full bg-indigo-500"></div>
                    </button>
                    <div class="h-px bg-white/5 mx-2 my-1"></div>
                    <button class="w-full text-left px-4 py-2 hover:bg-red-500/10 text-red-500 text-xs font-bold flex items-center gap-3 transition-colors" @click="logout">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                        Logout System
                    </button>
                </template>
            </div>
        </Transition>

        <!-- Alt+Tab App Switcher -->
        <Transition name="scale">
            <div v-if="isAltTabOpen" class="fixed inset-0 z-[10001] flex items-center justify-center bg-black/20 backdrop-blur-sm">
                <div class="bg-gray-900/90 border border-white/10 rounded-[2.5rem] p-8 shadow-2xl flex gap-6 overflow-x-auto max-w-[90vw] scrollbar-none">
                    <div 
                        v-for="(win, idx) in openWindows" 
                        :key="'switcher-'+win.id"
                        class="flex flex-col items-center gap-4 transition-all duration-300"
                        :class="activeSwitcherIndex === idx ? 'scale-110' : 'opacity-50 grayscale'"
                    >
                        <div 
                            class="w-24 h-24 rounded-3xl flex items-center justify-center text-4xl shadow-xl transition-all"
                            :style="{ backgroundColor: activeSwitcherIndex === idx ? win.color : 'rgba(255,255,255,0.05)', border: activeSwitcherIndex === idx ? '3px solid white' : '1px solid rgba(255,255,255,0.1)' }"
                        >
                            <img v-if="appRegistry[win.id]?.icon" :src="appRegistry[win.id].icon" class="w-16 h-16 object-contain" />
                            <span v-else>{{ win.badge }}</span>
                        </div>
                        <span class="text-xs font-black text-white uppercase tracking-widest text-center">{{ win.title }}</span>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
/* ========== CORE LAYOUT ========== */
.webde-desktop {
    position: fixed;
    inset: 0;
    overflow: hidden;
    font-family: 'Inter', 'Outfit', -apple-system, sans-serif;
    transition: all 1.5s cubic-bezier(0.4, 0, 0.2, 1);
}

/* ========== SCENE BACKGROUNDS ========== */
.scene-bg {
    position: absolute;
    inset: 0;
    transition: background 2s ease;
    background: url('/images/bg.png') no-repeat center center;
    background-size: cover;
}

/* Optional Overlay for Time of Day (Subtle tint) */
.scene-grain {
    position: absolute;
    inset: 0;
    opacity: 0.015; /* Reduced grain for cleaner look */
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
    pointer-events: none;
}

.scene-morning .scene-bg {
    box-shadow: inset 0 0 200px rgba(255, 200, 100, 0.1);
}
.scene-day .scene-bg {
    box-shadow: inset 0 0 200px rgba(255, 255, 255, 0.05); /* Very subtle daylight */
}
.scene-evening .scene-bg {
    box-shadow: inset 0 0 200px rgba(255, 150, 50, 0.15);
}
.scene-night .scene-bg {
    box-shadow: inset 0 0 200px rgba(0, 0, 50, 0.3);
}
.scene-deep-night .scene-bg {
    box-shadow: inset 0 0 200px rgba(0, 0, 0, 0.5);
}

/* ========== DESKTOP ICONS ========== */
.desktop-icons {
    position: absolute;
    inset: 0;
    bottom: 56px;
    z-index: 10;
    pointer-events: none; /* Allow clicks to pass through empty space */
}

.desktop-icon {
    position: absolute;
    left: 16px; /* Initial position */
    top: 16px; /* Initial position */
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 88px;
    padding: 8px 4px 6px;
    border-radius: 10px;
    cursor: move;
    user-select: none;
    transition: none; /* Disable transitions during drag */
    pointer-events: auto; /* Re-enable pointer events for icons */
}

/* Position each icon in a vertical stack initially */
.desktop-icon:nth-child(1) { top: 16px; }
.desktop-icon:nth-child(2) { top: 104px; }
.desktop-icon:nth-child(3) { top: 192px; }
.desktop-icon:nth-child(4) { top: 280px; }
.desktop-icon:nth-child(5) { top: 368px; }
.desktop-icon:nth-child(6) { top: 456px; }
.desktop-icon:hover {
    background: rgba(255, 255, 255, 0.06);
}
.desktop-icon:active {
    transform: scale(0.95);
}

/* Folder Icon */
.folder-wrapper {
    position: relative;
    width: 56px;
    height: 46px;
    margin-bottom: 4px;
    transition: transform 0.2s ease;
}
.desktop-icon:hover .folder-wrapper {
    transform: translateY(-2px);
}
.folder-svg {
    width: 100%;
    height: 100%;
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
}
.folder-badge {
    position: absolute;
    bottom: 6px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 18px;
    line-height: 1;
    filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.5));
}

.icon-label {
    font-size: 10px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.85);
    text-align: center;
    line-height: 1.2;
    max-width: 80px;
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.8);
    word-wrap: break-word;
}

/* ========== WINDOWS LAYER ========== */
.windows-layer {
    position: absolute;
    inset: 0;
    bottom: 48px;
}

.window-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    gap: 12px;
    color: rgba(255, 255, 255, 0.3);
}
.window-placeholder .placeholder-badge {
    font-size: 48px;
    opacity: 0.5;
}
.window-placeholder h3 {
    font-size: 18px;
    font-weight: 700;
    color: rgba(255, 255, 255, 0.5);
}
.window-placeholder p {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.2);
}

/* ========== TASKBAR REVAMP ========== */
.taskbar {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    width: 100%;
    height: 56px;
    background: rgba(20, 20, 28, 0.75);
    backdrop-filter: blur(32px) saturate(180%);
    -webkit-backdrop-filter: blur(32px) saturate(180%);
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 16px;
    z-index: 1000;
    box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.3);
}

/* Left: Search Bar */
.taskbar-left {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 12px;
}

.start-btn {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    padding: 2px;
}

.start-btn:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: scale(1.05);
}

.start-logo {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 6px;
}

.search-container {
    display: flex;
    align-items: center;
    background: rgba(255, 255, 255, 0.08);
    border-radius: 10px;
    padding: 4px 12px;
    width: 200px;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    border: 1px solid transparent;
}
.search-container.focused {
    width: 240px;
    background: rgba(255, 255, 255, 0.12);
    border-color: rgba(255, 255, 255, 0.2);
    box-shadow: 0 0 20px rgba(255, 255, 255, 0.05);
}
.search-icon { color: rgba(255, 255, 255, 0.4); margin-right: 8px; }
.search-input {
    background: none;
    border: none;
    color: #fff;
    font-size: 13px;
    width: 100%;
    outline: none;
    padding: 4px 0;
}
.search-results {
    position: absolute;
    bottom: 110%;
    left: 0;
    width: 240px;
    background: rgba(25, 25, 35, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    padding: 8px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
}
.search-result-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 13px;
    transition: background 0.2s;
}
.search-result-item:hover { background: rgba(255, 255, 255, 0.08); }
.res-badge { font-size: 16px; width: 24px; text-align: center; }

.res-icon-wrapper {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.res-icon-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

/* Center: Dock */
.taskbar-center {
    display: flex;
    justify-content: center;
    align-items: center;
    flex: 2;
}
.dock {
    display: flex;
    align-items: flex-end;
    gap: 6px;
    padding: 2px 0;
}
.dock-item {
    position: relative;
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    transform-origin: bottom center;
}
.dock-item:hover { 
    transform: translateY(-10px) scale(1.2); 
    filter: drop-shadow(0 10px 15px rgba(0, 0, 0, 0.3));
}
.dock-item:active {
    transform: translateY(-5px) scale(1.1);
}
.custom-dock-icon {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
.custom-dock-icon svg {
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
}
.dock-circle {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    border: 1.5px solid transparent;
    display: flex;
    align-items: center;
    justify-content: center;
    background-clip: padding-box;
    position: relative;
    transition: all 0.3s ease;
}
.dock-item.active .dock-circle {
    background: rgba(255, 255, 255, 0.05);
    border-color: rgba(255, 255, 255, 0.2);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}
.dock-badge { font-size: 20px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3)); }
.dock-indicator {
    position: absolute;
    bottom: -8px;
    left: 50%;
    transform: translateX(-50%);
    width: 4px;
    height: 4px;
    background: #6366f1;
    border-radius: 50%;
    box-shadow: 0 0 10px #6366f1, 0 0 20px #6366f1;
    animation: indicator-pulse 2s infinite;
}

@keyframes indicator-pulse {
    0%, 100% { opacity: 1; transform: translateX(-50%) scale(1); }
    50% { opacity: 0.5; transform: translateX(-50%) scale(0.8); }
}

/* Right: System Tray */
.taskbar-right {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
}
.tray-control { position: relative; }
.tray-btn {
    background: rgba(255, 255, 255, 0.05);
    border: none;
    color: rgba(255, 255, 255, 0.7);
    width: 36px;
    height: 36px;
    border-radius: 10px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}
.tray-btn:hover { background: rgba(255, 255, 255, 0.1); color: #fff; }

.volume-popover {
    position: absolute;
    bottom: 120%;
    right: 0;
    background: rgba(25, 25, 35, 0.95);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    padding: 16px;
    border-radius: 12px;
    width: 40px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}
.volume-slider {
    writing-mode: bt-lr; /* Vertical slider */
    appearance: slider-vertical;
    width: 4px;
    height: 100px;
}
.vol-percentage { font-size: 10px; font-weight: 700; color: #fff; }

.clock-display {
    text-align: right;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 8px;
    transition: background 0.2s;
}
.clock-display:hover { background: rgba(255, 255, 255, 0.05); }
.tray-time { font-size: 13px; font-weight: 700; color: #fff; line-height: 1; }
.tray-date { font-size: 10px; color: rgba(255,255,255,0.4); font-weight: 500; }

.user-control { position: relative; }
.avatar-btn {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    border: 1.5px solid rgba(255, 255, 255, 0.1);
    overflow: hidden;
    padding: 0;
    cursor: pointer;
    background: none;
}
.avatar-btn img { width: 100%; height: 100%; object-fit: cover; }

/* Revamped User Card (Start Menu) */
.user-card {
    position: absolute;
    bottom: 120%;
    right: 0;
    width: 320px;
    background: rgba(25, 27, 38, 0.75);
    backdrop-filter: blur(32px) saturate(200%);
    -webkit-backdrop-filter: blur(32px) saturate(200%);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 24px;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5), inset 0 0 0 1px rgba(255, 255, 255, 0.05);
}

.user-card-inner {
    position: relative;
    z-index: 1;
}

/* Notifications */
.notification-item {
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.notification-enter-from {
    opacity: 0;
    transform: translateX(100px) scale(0.9);
}

.notification-leave-to {
    opacity: 0;
    transform: translateX(50px);
}

/* Calendar Popover */
.calendar-popover {
    position: absolute;
    bottom: 120%;
    right: 0;
    width: 280px;
    background: rgba(25, 27, 38, 0.75);
    backdrop-filter: blur(32px) saturate(200%);
    -webkit-backdrop-filter: blur(32px) saturate(200%);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 24px;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
}

/* Custom Icons */
.custom-icon-wrapper {
    width: 56px;
    height: 46px;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.2s ease;
}
.desktop-icon:hover .custom-icon-wrapper {
    transform: translateY(-2px);
}
.custom-icon-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.3));
}

.custom-dock-icon {
    border: none !important;
    background: none !important;
}
.dock-icon-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
    transition: transform 0.2s;
}
.dock-item:hover .dock-icon-img {
    transform: scale(1.1);
}

/* Selection Marquee Style */
.marquee-box {
    position: fixed;
    border: 1px solid rgba(79, 70, 229, 0.5);
    background: rgba(79, 70, 229, 0.1);
    z-index: 5;
    pointer-events: none;
}

/* Animations */
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.scale-enter-active, .scale-leave-active { transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
.scale-enter-from, .scale-leave-to { opacity: 0; transform: scale(0.9); }

.scrollbar-none::-webkit-scrollbar { display: none; }
.scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }

/* Window Flow Transition */
.window-flow-enter-active {
    transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}
.window-flow-leave-active {
    transition: all 0.3s cubic-bezier(0.7, 0, 0.84, 0);
}
.window-flow-enter-from {
    opacity: 0;
    transform: scale(0.5) translateY(100px);
    filter: blur(10px);
}
.window-flow-leave-to {
    opacity: 0;
    transform: scale(0.2) translateY(200px);
    filter: blur(20px);
}
</style>
