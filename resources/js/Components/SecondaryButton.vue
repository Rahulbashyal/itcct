<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button :type="type" class="inline-flex items-center px-6 py-3 bg-transparent border border-secondary-teal rounded-xl font-semibold text-secondary-teal tracking-widest hover:bg-secondary-teal/10 active:bg-secondary-teal/20 focus:outline-none focus:ring-2 focus:ring-secondary-teal focus:ring-offset-2 focus:ring-offset-dark-gray transition-all duration-150">
        <slot />
    </button>
</template>
