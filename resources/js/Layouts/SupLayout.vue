<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth?.user);

// Dropdown state
const openMenu = ref(null);

const toggleMenu = (name) => {
    openMenu.value = openMenu.value === name ? null : name;
};

const closeMenus = () => {
    openMenu.value = null;
};

// Navigation items for Superviseur (SUP)
const navItems = [
    {
        label: 'Tableau de bord',
        href: route('sup.dashboard'),
        active: route().current('sup.dashboard'),
    },
    {
        label: 'Mon Équipe',
        href: route('sup.team'),
        active: route().current('sup.team'),
    },
    {
        label: 'Planning d\'Équipe',
        href: route('sup.schedule'),
        active: route().current('sup.schedule'),
    },
    {
        label: 'Saisie des Heures',
        href: route('sup.time-tracking'),
        active: route().current('sup.time-tracking'),
    },
];
</script>

<template>
    <div class="min-h-screen bg-pearl-100 font-sans">
        <!-- Navbar -->
        <nav class="fixed top-0 left-0 right-0 z-50 bg-charcoal-900 shadow-charcoal border-b border-charcoal-700">
            <div class="flex items-center h-14 px-4">
                <!-- Logo -->
                <Link :href="route('sup.dashboard')" class="flex items-center gap-3 min-w-[200px] border-r border-charcoal-700 pr-5 mr-2 h-full" @click="closeMenus">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gold-gradient shadow-gold">
                        <span class="text-charcoal-900 font-black text-sm">W</span>
                    </div>
                    <div class="flex flex-col leading-tight">
                        <span class="text-white font-bold text-sm tracking-wide">WIP Lite</span>
                        <span class="text-charcoal-400 text-[10px] font-medium tracking-widest uppercase">Superviseur</span>
                    </div>
                </Link>

                <!-- Nav Items -->
                <div class="flex items-center flex-1 h-full ml-4">
                    <template v-for="item in navItems" :key="item.label">
                        <Link
                            :href="item.href"
                            class="flex items-center gap-1.5 h-full px-4 text-xs font-medium whitespace-nowrap transition-all duration-150"
                            :class="item.active
                                ? 'text-gold-400 border-b-2 border-gold-400 bg-charcoal-800'
                                : 'text-charcoal-400 hover:text-white hover:bg-charcoal-800'"
                            @click="closeMenus"
                        >
                            {{ item.label }}
                        </Link>
                    </template>
                </div>

                <!-- User Dropdown -->
                <div class="relative ml-2 pl-3 border-l border-charcoal-700 flex items-center h-full">
                    <button @click="toggleMenu('user')" class="flex items-center gap-2 px-2 py-1.5 rounded-lg transition-all duration-150 hover:bg-charcoal-800">
                        <div class="w-7 h-7 rounded-full bg-gold-gradient flex items-center justify-center text-charcoal-900 font-bold text-xs shadow-gold flex-shrink-0">
                            {{ user?.name?.charAt(0)?.toUpperCase() }}
                        </div>
                        <div class="text-left hidden md:block">
                            <div class="text-white text-xs font-semibold leading-tight">{{ user?.name }}</div>
                            <div class="text-charcoal-400 text-[10px] leading-tight">Superviseur</div>
                        </div>
                        <svg class="w-3 h-3 text-charcoal-400 transition-transform duration-200" :class="openMenu === 'user' ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <Transition
                        enter-active-class="transition ease-out duration-150"
                        enter-from-class="opacity-0 translate-y-1"
                        enter-to-class="opacity-100 translate-y-0"
                        leave-active-class="transition ease-in duration-100"
                        leave-from-class="opacity-100 translate-y-0"
                        leave-to-class="opacity-0 translate-y-1"
                    >
                        <div v-if="openMenu === 'user'" class="absolute top-full right-0 mt-0 w-48 bg-white rounded-b-lg shadow-lg border border-pearl-200 overflow-hidden z-50">
                            <div class="px-4 py-3 border-b border-pearl-200 bg-pearl-50">
                                <div class="text-xs font-bold text-charcoal-700">{{ user?.name }}</div>
                                <div class="text-[11px] text-charcoal-400 truncate">{{ user?.email }}</div>
                            </div>
                            <Link :href="route('profile.edit')" class="flex items-center gap-2 px-4 py-2.5 text-xs text-charcoal-600 hover:bg-pearl-100 hover:text-gold-600 transition-colors border-b border-pearl-200" @click="closeMenus">
                                Profil
                            </Link>
                            <Link :href="route('logout')" method="post" as="button" class="w-full flex items-center gap-2 px-4 py-2.5 text-xs text-red-500 hover:bg-red-50 transition-colors" @click="closeMenus">
                                Déconnexion
                            </Link>
                        </div>
                    </Transition>
                </div>
            </div>
        </nav>

        <!-- Backdrop -->
        <div v-if="openMenu" class="fixed inset-0 z-40" @click="closeMenus"></div>

        <!-- Main Content -->
        <main class="pt-14 min-h-screen bg-pearl-100">
            <div v-if="$slots.header" class="bg-white border-b border-pearl-200 px-6 py-4 shadow-sm">
                <slot name="header" />
            </div>
            <div class="p-6">
                <slot />
            </div>
        </main>
    </div>
</template>

<style scoped>
.bg-gold-gradient {
    background: linear-gradient(135deg, #D4A017 0%, #8B6914 100%);
}
</style>
