<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button :type="type" class="inline-flex items-center px-8 py-3 bg-primary-blue border border-transparent rounded-full font-bold text-white text-[10px] uppercase tracking-[0.2em] hover:bg-primary-blue/90 hover:scale-105 active:scale-95 focus:outline-none focus:ring-2 focus:ring-primary-blue/30 transition-all duration-300 shadow-xl shadow-primary-blue/10">
        <slot />
    </button>
</template>
