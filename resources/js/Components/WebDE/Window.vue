<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import interact from 'interactjs';

const props = defineProps({
    title: {
        type: String,
        required: true
    },
    icon: {
        type: String,
        default: 'M4 6h16M4 12h16M4 18h16'
    },
    initialPosition: {
        type: Object,
        default: () => ({ x: 100, y: 100 })
    },
    initialSize: {
        type: Object,
        default: () => ({ width: 800, height: 600 })
    },
    initialState: {
        type: Object,
        default: null
    },
    appId: {
        type: String,
        required: true
    }
});

const emit = defineEmits(['close', 'minimize', 'focus', 'update:state']);

const windowRef = ref(null);
// Use initialState if provided, otherwise fall back to initialPosition/Size
const position = ref(props.initialState?.position || { ...props.initialPosition });
const size = ref(props.initialState?.size || { ...props.initialSize });
const isMaximized = ref(props.initialState?.isMaximized || false);
const isMinimized = ref(props.initialState?.isMinimized || false);
const zIndex = ref(props.initialState?.zIndex || 100);
const preMaximizeState = ref({ position: { x: 0, y: 0 }, size: { width: 0, height: 0 } });

// Sync with parent state changes (e.g. restore from taskbar)
watch(() => props.initialState?.isMinimized, (newVal) => {
    if (newVal !== undefined) isMinimized.value = newVal;
});

watch(() => props.initialState?.zIndex, (newVal) => {
    if (newVal !== undefined) zIndex.value = newVal;
});

watch(() => props.initialState?.isMaximized, (newVal) => {
    if (newVal !== undefined) isMaximized.value = newVal;
});

const windowStyle = computed(() => {
    const baseStyle = {
        zIndex: zIndex.value,
        opacity: isMinimized.value ? 0 : 1,
        pointerEvents: isMinimized.value ? 'none' : 'auto',
    };

    if (isMaximized.value) {
        return {
            ...baseStyle,
            transform: `translate(0px, 0px) ${isMinimized.value ? 'scale(0.8) translateY(100px)' : 'scale(1) translateY(0)'}`,
            width: `100%`,
            height: `calc(100% - 48px)`,
        };
    }

    return {
        ...baseStyle,
        transform: `translate(${position.value.x}px, ${position.value.y}px) ${isMinimized.value ? 'scale(0.8) translateY(100px)' : 'scale(1) translateY(0)'}`,
        width: `${size.value.width}px`,
        height: `${size.value.height}px`,
    };
});

const emitStateUpdate = () => {
    emit('update:state', {
        appId: props.appId,
        position: { ...position.value },
        size: { ...size.value },
        isMaximized: isMaximized.value,
        isMinimized: isMinimized.value,
        zIndex: zIndex.value
    });
};

onMounted(() => {
    // Emit initial state
    emitStateUpdate();

    if (!windowRef.value) return;

    // Make window draggable
    interact(windowRef.value)
        .draggable({
            allowFrom: '.window-header',
            enabled: !isMaximized.value, // Disable when maximized
            listeners: {
                move(event) {
                    if (isMaximized.value) return;
                    position.value.x += event.dx;
                    position.value.y += event.dy;
                    emitStateUpdate(); // Emit on move
                }
            },
            modifiers: [
                interact.modifiers.restrictRect({
                    restriction: 'parent',
                    endOnly: true
                })
            ]
        })
        .resizable({
            edges: { left: true, right: true, bottom: true, top: false },
            enabled: !isMaximized.value, // Disable when maximized
            listeners: {
                move(event) {
                    if (isMaximized.value) return;
                    size.value.width = event.rect.width;
                    size.value.height = event.rect.height;
                    position.value.x += event.deltaRect.left;
                    position.value.y += event.deltaRect.top;
                    emitStateUpdate(); // Emit on resize
                }
            },
            modifiers: [
                interact.modifiers.restrictSize({
                    min: { width: 400, height: 300 }
                })
            ]
        });
});

// Watch for maximize state change to enable/disable interact
watch(isMaximized, (maximized) => {
    if (windowRef.value) {
        interact(windowRef.value).draggable({ enabled: !maximized });
        interact(windowRef.value).resizable({ enabled: !maximized });
    }
    emitStateUpdate();
});

const handleFocus = () => {
    zIndex.value = Date.now();
    emit('focus', props.appId);
    emitStateUpdate();
};

const handleClose = () => {
    emit('close', props.appId);
};

const handleMinimize = () => {
    isMinimized.value = true;
    emit('minimize', props.appId);
    emitStateUpdate();
};

const handleMaximize = () => {
    if (isMaximized.value) {
        // Restore
        isMaximized.value = false;
    } else {
        // Maximize
        preMaximizeState.value = {
            position: { ...position.value },
            size: { ...size.value }
        };
        isMaximized.value = true;
    }
    handleFocus(); // Bring to front
};

const restore = () => {
    isMinimized.value = false;
    handleFocus();
    emitStateUpdate();
};

defineExpose({ restore });
</script>

<template>
        <div 
            ref="windowRef"
            :style="windowStyle"
            @mousedown="handleFocus"
            class="window-container fixed flex flex-col bg-[#2D2D2D] rounded-2xl shadow-2xl overflow-hidden border border-white/10"
        >
            <!-- Header Bar -->
            <div class="window-header h-12 bg-[#2D2D2D] flex items-center justify-between px-4 cursor-move select-none border-b border-white/5">
                <div class="flex items-center space-x-3">
                    <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icon"></path>
                    </svg>
                    <span class="text-xs font-black uppercase tracking-widest text-white">{{ title }}</span>
                </div>
                <div class="flex items-center space-x-2">
                    <button 
                        @click.stop="handleMinimize"
                        class="w-8 h-8 rounded-lg hover:bg-white/10 flex items-center justify-center text-gray-400 hover:text-white transition-colors"
                        title="Minimize"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                        </svg>
                    </button>
                    <button 
                        @click.stop="handleMaximize"
                        class="w-8 h-8 rounded-lg hover:bg-white/10 flex items-center justify-center text-gray-400 hover:text-white transition-colors"
                        :title="isMaximized ? 'Restore' : 'Maximize'"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path v-if="!isMaximized" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 14h6m-6 0v6m0-6l7 7m10-7h-6m6 0v6m0-6l-7 7m-10-4h6m-6 0v-6m0 6l7-7m10 4h-6m6 0v-6m0 6l-7-7"></path>
                        </svg>
                    </button>
                    <button 
                        @click.stop="handleClose"
                        class="w-8 h-8 rounded-lg hover:bg-red-500/20 flex items-center justify-center text-gray-400 hover:text-red-500 transition-colors"
                        title="Close"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Content Area -->
            <div class="flex-1 overflow-hidden bg-[#1a1b26]">
                <slot />
            </div>
        </div>
</template>

<style scoped>
.window-container {
    touch-action: none;
    transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), 
                width 0.4s cubic-bezier(0.16, 1, 0.3, 1), 
                height 0.4s cubic-bezier(0.16, 1, 0.3, 1),
                opacity 0.3s ease;
}
</style>
