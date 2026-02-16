<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const isMenuOpen = ref(false);
const isScrolled = ref(false);
const isBrightMode = ref(false);

const toggleBrightMode = () => {
    isBrightMode.value = !isBrightMode.value;
    if (isBrightMode.value) {
        document.documentElement.classList.add('bright-mode');
    } else {
        document.documentElement.classList.remove('bright-mode');
    }
};

const navLinks = [
    { name: 'Home', route: 'home', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
    { name: 'About', route: 'about', icon: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
    { name: 'Events', route: 'events', icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' },
    { name: 'Projects', route: 'projects', icon: 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4' },
    { name: 'Election', route: 'election-portal', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
    { name: 'Portal', route: 'transparency', icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' },
    { name: 'Hall', route: 'hall-of-fame', icon: 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-7.714 2.143L11 21l-2.286-6.857L1 12l7.714-2.143L11 3z' },
    { name: 'News', route: 'news', icon: 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z' },
    { name: 'Contact', route: 'contact', icon: 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z' },
];

const handleScroll = () => {
    isScrolled.value = window.scrollY > 20;
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});

const toggleMenu = () => {
    isMenuOpen.value = !isMenuOpen.value;
    if (isMenuOpen.value) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = 'auto';
    }
};
</script>

<template>
    <nav 
        :class="[
            'fixed top-0 left-0 right-0 z-[100] transition-all duration-500 ease-[cubic-bezier(0.23,1,0.32,1)] px-4 py-3 sm:px-6 md:px-10',
            isScrolled ? 'bg-black/60 backdrop-blur-2xl border-b border-white/5 py-3 shadow-2xl' : 'bg-transparent py-6'
        ]"
    >
        <div class="max-w-[1600px] mx-auto flex justify-between items-center">
            <!-- Left: Logo & Brand -->
            <Link :href="route('home')" class="flex items-center space-x-2 sm:space-x-3 group shrink-0">
                <div class="relative w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 overflow-hidden rounded-full border border-white/10 group-hover:border-primary-blue/50 transition-all shrink-0">
                    <img src="/images/logo.jpg" alt="IT Club Logo" class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-500" />
                </div>
                <div class="flex flex-col -space-y-1 overflow-hidden">
                    <span class="text-[10px] xs:text-xs sm:text-base font-black tracking-tighter text-white uppercase group-hover:text-primary-blue transition-colors truncate max-w-[80px] xs:max-w-none">
                        IT CLUB <span class="text-secondary-teal">CCT</span>
                    </span>
                    <span class="text-[7px] font-bold text-gray-500 uppercase tracking-[0.2em] hidden sm:block">Innovation Hub</span>
                </div>
            </Link>

            <!-- Center: Desktop Links (Minimalist) -->
            <div class="hidden lg:flex items-center space-x-1 bg-white/[0.03] backdrop-blur-md border border-white/5 rounded-full p-1 shadow-inner">
                <Link 
                    v-for="link in navLinks" 
                    :key="link.name"
                    :href="route(link.route)"
                    class="px-4 py-2 text-[10px] font-bold uppercase tracking-[0.1em] text-gray-400 hover:text-white rounded-full transition-all relative group"
                    :class="{ 'text-white bg-white/5 shadow-sm': $page.url === route(link.route, {}, false) }"
                >
                    {{ link.name }}
                    <span class="absolute bottom-1 left-1/2 -translate-x-1/2 w-0 h-[1px] bg-primary-blue transition-all group-hover:w-1/2" :class="{ 'w-1/2': $page.url === route(link.route, {}, false) }"></span>
                </Link>
            </div>

            <!-- Right: Actions -->
            <div class="flex items-center space-x-2 sm:space-x-4">
                <!-- Bright Mode Toggle -->
                <button 
                    @click="toggleBrightMode"
                    class="w-10 h-10 flex items-center justify-center rounded-full bg-white/5 border border-white/5 hover:border-white/10 text-gray-400 hover:text-primary-blue transition-all"
                    :title="isBrightMode ? 'Switch to Dark Protocol' : 'Switch to Bright Mode'"
                >
                    <svg v-if="!isBrightMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M16.95 16.95l.707.707M7.05 7.05l.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"></path></svg>
                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                </button>

                <div class="hidden sm:flex items-center space-x-2">
                    <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-xs font-black uppercase italic hover:bg-primary-blue hover:text-white transition-all overflow-hidden group">
                         <span class="group-hover:hidden transition-all">{{ $page.props.auth.user.name.charAt(0) }}</span>
                         <svg class="hidden group-hover:block w-5 h-5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </Link>
                    <Link v-else :href="route('login')" class="group relative px-6 py-2.5 overflow-hidden rounded-full bg-white/5 border border-white/10 text-[10px] font-black uppercase tracking-widest text-white transition-all hover:border-primary-blue">
                        <span class="relative z-10 transition-all group-hover:text-white">Access</span>
                        <div class="absolute inset-0 bg-primary-blue w-0 group-hover:w-full transition-all duration-500 ease-out"></div>
                    </Link>
                </div>

                <!-- Menu Toggle (Always visible for minimalism/consistency) -->
                <button 
                    @click="toggleMenu"
                    aria-label="Toggle Menu"
                    class="w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center bg-white/5 border border-white/5 hover:border-white/20 rounded-full text-gray-400 hover:text-white transition-all overflow-hidden group"
                >
                    <div class="relative w-6 h-5 flex flex-col justify-between">
                        <span class="w-full h-[2px] bg-current rounded-full transition-all duration-300" :class="{ 'rotate-45 translate-y-[9px]': isMenuOpen }"></span>
                        <span class="w-2/3 h-[2px] bg-current rounded-full transition-all duration-300 ml-auto" :class="{ 'opacity-0': isMenuOpen }"></span>
                        <span class="w-full h-[2px] bg-current rounded-full transition-all duration-300" :class="{ '-rotate-45 -translate-y-[9px]': isMenuOpen }"></span>
                    </div>
                </button>
            </div>
        </div>
    </nav>

    <!-- Neural Menu Layer -->
    <Transition
        enter-active-class="transition duration-700 cubic-bezier(0.23,1,0.32,1)"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition duration-500 cubic-bezier(0.23,1,0.32,1)"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
    >
        <div v-if="isMenuOpen" class="fixed inset-0 z-[110] bg-[#050608] flex flex-col items-center justify-center p-6 sm:p-20 overflow-hidden">
            <!-- Neural Background Pattern -->
            <div class="absolute inset-0 z-0 pointer-events-none opacity-20">
                <div class="absolute top-0 left-0 w-full h-full bg-[radial-gradient(#1e293b_1px,transparent_1px)] [background-size:40px_40px]"></div>
                <div class="absolute top-1/4 right-1/4 w-[600px] h-[600px] bg-primary-blue/10 rounded-full blur-[150px] animate-pulse"></div>
                <div class="absolute bottom-1/4 left-1/4 w-[600px] h-[600px] bg-secondary-teal/10 rounded-full blur-[150px] animate-pulse" style="animation-delay: 2s"></div>
            </div>

            <!-- Content Area -->
            <div class="relative z-10 w-full max-w-5xl flex flex-col items-center text-center">
                <div class="mb-12 opacity-50 scale-150 rotate-6 select-none pointer-events-none">
                    <img src="/images/logo.jpg" alt="Ghost Logo" class="w-32 h-32 rounded-full grayscale mix-blend-screen opacity-20" />
                </div>

                <nav class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-20 gap-y-4 sm:gap-y-8 text-center">
                    <Link 
                        v-for="(link, i) in navLinks" 
                        :key="link.name"
                        :href="route(link.route)"
                        @click="toggleMenu"
                        class="group relative flex items-center justify-center sm:justify-start space-x-6 text-2xl sm:text-5xl font-black text-white/40 hover:text-white transition-all italic tracking-tighter"
                        :style="{ transitionDelay: `${i * 30}ms` }"
                    >
                        <span class="hidden sm:block text-[10px] font-black text-primary-blue opacity-0 group-hover:opacity-100 transition-opacity tracking-widest mt-4">0{{ i + 1 }}</span>
                        <span class="relative">
                            {{ link.name }}
                            <span class="absolute -bottom-1 left-0 w-0 h-2 bg-gradient-to-r from-primary-blue/30 to-transparent group-hover:w-full transition-all duration-500 -z-10"></span>
                        </span>
                    </Link>
                </nav>

                <div class="mt-20 pt-12 border-t border-white/5 w-full flex flex-col sm:flex-row justify-between items-center gap-10 opacity-60">
                    <div class="text-[10px] font-black uppercase tracking-[0.4em] text-gray-500">
                        Protocol: V.4.0.1 // Secured Terminal
                    </div>
                    <div class="flex space-x-12">
                        <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="text-xs font-black uppercase text-secondary-teal hover:text-white transition-colors">Command Center</Link>
                        <Link v-else :href="route('login')" class="text-xs font-black uppercase text-primary-blue hover:text-white transition-colors">Member Access</Link>
                        <button @click="toggleMenu" class="text-xs font-black uppercase text-red-500/50 hover:text-red-500 transition-colors">Terminate</button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
@media (max-width: 350px) {
    .text-sm { font-size: 10px; }
    .w-10 { width: 32px; height: 32px; }
}

.italic { font-style: italic; }
</style>
