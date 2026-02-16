<script setup>
import GuestNav from '@/Components/GuestNav.vue';
import { Link, Head } from '@inertiajs/vue3';
</script>

<template>
    <Head>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">
    </Head>
    
    <div class="min-h-screen bright-bg bright-text font-sans selection:bg-primary-blue selection:text-white overflow-x-hidden">
        <GuestNav />
        
        <main>
            <slot />
        </main>

        <footer class="bg-black/5 bright-border border-t py-24 px-6 sm:px-12 mt-40">
            <div class="max-w-7xl mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-16 mb-20">
                    <div class="lg:col-span-2 space-y-8">
                        <Link href="/" class="flex items-center space-x-3 group">
                            <div class="w-10 h-10 bg-primary-blue rounded-xl flex items-center justify-center font-black text-white italic transform group-hover:rotate-12 transition-transform">I</div>
                            <span class="text-xl font-black tracking-tighter bright-text uppercase">IT CLUB <span class="text-secondary-teal">CCT</span></span>
                        </Link>
                        <p class="bright-muted max-w-sm font-medium leading-relaxed italic">
                            Building the digital backbone of innovation and governance for the IT community at Crimson College of Technology. We lead with code and ethics.
                        </p>
                    </div>

                    <div class="space-y-6">
                        <h4 class="text-[10px] font-black uppercase tracking-[0.4em] bright-muted">The Nexus</h4>
                        <nav class="flex flex-col space-y-3 font-black text-xs uppercase tracking-widest bright-muted">
                            <Link :href="route('about')" class="hover:text-primary-blue transition-colors">Origins</Link>
                            <Link :href="route('projects')" class="hover:text-primary-blue transition-colors">Technical Index</Link>
                            <Link :href="route('events')" class="hover:text-primary-blue transition-colors">Tactical List</Link>
                            <Link :href="route('election-portal')" class="hover:text-primary-blue transition-colors">Digital Election</Link>
                        </nav>
                    </div>

                    <div class="space-y-6">
                        <h4 class="text-[10px] font-black uppercase tracking-[0.4em] bright-muted">Operation</h4>
                        <nav class="flex flex-col space-y-3 font-black text-xs uppercase tracking-widest bright-muted">
                            <Link :href="route('transparency')" class="hover:text-secondary-teal transition-colors">Transparency Portal</Link>
                            <Link :href="route('hall-of-fame')" class="hover:text-secondary-teal transition-colors">Hall of Fame</Link>
                            <Link :href="route('news')" class="hover:text-secondary-teal transition-colors">Dispatch/News</Link>
                            <Link :href="route('contact')" class="hover:text-secondary-teal transition-colors">Direct Link</Link>
                        </nav>
                    </div>
                </div>

                <div class="pt-12 border-t bright-border flex flex-col sm:flex-row justify-between items-center gap-8">
                    <div class="text-[10px] font-black uppercase tracking-widest bright-muted">
                        &copy; {{ new Date().getFullYear() }} IT CLUB CCT. ALL PROTOCOLS ENFORCED.
                    </div>
                    <div class="flex space-x-8">
                        <a href="#" class="bright-muted hover:text-primary-blue transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                        </a>
                        <a href="#" class="bright-muted hover:text-primary-blue transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
