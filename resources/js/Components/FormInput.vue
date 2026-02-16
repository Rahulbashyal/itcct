<script setup>
const model = defineModel({
    type: String,
    required: true,
});

defineProps({
    label: String,
    type: {
        type: String,
        default: 'text',
    },
    error: String,
    placeholder: String,
});
</script>

<template>
    <div class="w-full">
        <label v-if="label" class="block text-sm font-medium text-gray-400 mb-2">{{ label }}</label>
        <input 
            v-model="model"
            :type="type" 
            :placeholder="placeholder"
            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-secondary-teal focus:ring-1 focus:ring-secondary-teal transition-all placeholder:text-gray-600"
            :class="{'border-red-500 focus:border-red-500 focus:ring-red-500': error}"
        >
        <div v-if="error" class="text-red-400 text-xs mt-1">{{ error }}</div>
    </div>
</template>
