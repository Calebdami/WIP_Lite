<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth?.user);

// Mobile menu
const mobileMenuOpen = ref(false);

// Dropdown state pour chaque menu
const openMenu = ref(null);

const toggleMenu = (name) => {
    openMenu.value = openMenu.value === name ? null : name;
};

const closeMenus = () => {
    openMenu.value = null;
};

// Navigation items (fidèle au screenshot)
const navItems = [
    {
        label: 'Tableau de bord',
        icon: '',
        href: route('admin.dashboard'),
        active: route().current('admin.dashboard'),
    },
    {
        label: 'Personnel',
        icon: '',
        key: 'personnel',
        children: [
            { label: 'Employés', href: route('admin.employees.index') },
            { label: 'Comptes & Rôles', href: route('admin.users.index') },
        ],
    },
    {
        label: 'Campagnes',
        icon: '',
        key: 'campagnes',
        href: route('admin.campaigns.index'),
        active: route().current('admin.campaigns.index'),
    },
    {
        label: 'Affectations',
        icon: '',
        key: 'affectations',
        children: [
            { label: 'Structure', href: route('admin.assignments.structure') },
            { label: 'Ressources disponibles', href: route('admin.assignments.resources') },
            { label: 'Historique', href: route('admin.assignments.history') },
        ],
    },
    {
        label: 'Temps de travail',
        icon: '',
        key: 'temps',
        children: [
            { label: 'Plannings', href: route('admin.assignments.schedules') },
            { label: 'Validations', href: route('admin.time.tracking') },
            { label: 'Suivi & Clôture', href: route('admin.time.tracking') },
        ],
    },
    {
        label: 'Configuration',
        icon: '',
        key: 'config',
        children: [
            { label: 'Entreprise', href: route('admin.config.company') },
            { label: 'Référentiels', href: route('admin.config.referentials') },
            { label: 'Rôles & Permissions', href: route('admin.config.permissions') },
        ],
    },
    {
        label: 'Sécurité',
        icon: '',
        key: 'securite',
        children: [
            { label: 'Audit Logs', href: route('admin.security.logs') },
            { label: 'Sessions actives', href: route('admin.security.sessions') },
        ],
    },
    {
        label: 'Calendrier',
        icon: '',
        href: route('admin.calendar'),
        active: route().current('admin.calendar'),
    },
    {
        label: 'Notifications',
        icon: '',
        key: 'notifications',
        children: [
            { label: 'Modèles', href: route('admin.notifications.templates') },
            { label: 'Diffusion', href: route('admin.notifications.broadcast') },
        ],
    },
    {
        label: 'Maintenance',
        icon: '',
        href: route('admin.maintenance'),
        active: route().current('admin.maintenance'),
    },
];
</script>

<template>
    <div class="min-h-screen bg-pearl-100 font-sans">

        <!-- ═══════════════════ NAVBAR HORIZONTALE ═══════════════════ -->
        <nav class="fixed top-0 left-0 right-0 z-50 bg-charcoal-900 shadow-charcoal border-b border-charcoal-700">
            <div class="flex items-center h-14 px-4 gap-0">

                <!-- Logo + Titre -->
                <Link :href="route('admin.dashboard')" class="flex items-center gap-3 min-w-[200px] border-r border-charcoal-700 pr-5 mr-2 h-full" @click="closeMenus">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gold-gradient shadow-gold">
                        <span class="text-charcoal-900 font-black text-sm">W</span>
                    </div>
                    <div class="flex flex-col leading-tight">
                        <span class="text-white font-bold text-sm tracking-wide">Administration</span>
                        <span class="text-charcoal-400 text-[10px] font-medium tracking-widest uppercase">Gestion d'Entreprise</span>
                    </div>
                </Link>

                <!-- Navigation principale -->
                <div class="flex items-center flex-1 h-full ml-4">
                    <template v-for="item in navItems" :key="item.label">

                        <!-- Lien simple -->
                        <template v-if="!item.children && !item.key">
                            <Link
                                :href="item.href"
                                class="flex items-center gap-1.5 h-full px-3 text-xs font-medium whitespace-nowrap transition-all duration-150"
                                :class="item.active
                                    ? 'text-gold-400 border-b-2 border-gold-400 bg-charcoal-800'
                                    : 'text-charcoal-400 hover:text-white hover:bg-charcoal-800'"
                                @click="closeMenus"
                            >
                                <span class="text-sm">{{ item.icon }}</span>
                                {{ item.label }}
                            </Link>
                        </template>

                        <!-- Lien simple (pas de dropdown, a une clé) -->
                        <template v-else-if="item.href && !item.children">
                            <Link
                                :href="item.href"
                                class="flex items-center gap-1.5 h-full px-3 text-xs font-medium whitespace-nowrap transition-all duration-150 text-charcoal-400 hover:text-white hover:bg-charcoal-800"
                                @click="closeMenus"
                            >
                                <span class="text-sm">{{ item.icon }}</span>
                                {{ item.label }}
                            </Link>
                        </template>

                        <!-- Dropdown -->
                        <div v-else class="relative h-full">
                            <button
                                @click="toggleMenu(item.key)"
                                class="flex items-center gap-1.5 h-full px-3 text-xs font-medium whitespace-nowrap transition-all duration-150"
                                :class="openMenu === item.key
                                    ? 'text-gold-400 bg-charcoal-800 border-b-2 border-gold-400'
                                    : 'text-charcoal-400 hover:text-white hover:bg-charcoal-800'"
                            >
                                <span class="text-sm">{{ item.icon }}</span>
                                {{ item.label }}
                                <svg class="w-3 h-3 transition-transform duration-200" :class="openMenu === item.key ? 'rotate-180 text-gold-400' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <!-- Dropdown panel -->
                            <Transition
                                enter-active-class="transition ease-out duration-150"
                                enter-from-class="opacity-0 translate-y-1"
                                enter-to-class="opacity-100 translate-y-0"
                                leave-active-class="transition ease-in duration-100"
                                leave-from-class="opacity-100 translate-y-0"
                                leave-to-class="opacity-0 translate-y-1"
                            >
                                <div
                                    v-if="openMenu === item.key"
                                    class="absolute top-full left-0 mt-0 w-52 bg-white rounded-b-lg shadow-lg border border-pearl-200 overflow-hidden z-50"
                                >
                                    <Link
                                        v-for="child in item.children"
                                        :key="child.label"
                                        :href="child.href"
                                        class="block px-4 py-2.5 text-xs text-charcoal-600 hover:bg-pearl-100 hover:text-gold-600 transition-colors duration-100 border-b border-pearl-200 last:border-0"
                                        @click="closeMenus"
                                    >
                                        {{ child.label }}
                                    </Link>
                                </div>
                            </Transition>
                        </div>
                    </template>
                </div>

                <!-- Avatar / User Dropdown -->
                <div class="relative ml-2 pl-3 border-l border-charcoal-700 flex items-center h-full">
                    <button
                        @click="toggleMenu('user')"
                        class="flex items-center gap-2 px-2 py-1.5 rounded-lg transition-all duration-150 hover:bg-charcoal-800"
                    >
                        <div class="w-7 h-7 rounded-full bg-gold-gradient flex items-center justify-center text-charcoal-900 font-bold text-xs shadow-gold flex-shrink-0">
                            {{ user?.name?.charAt(0)?.toUpperCase() || 'A' }}
                        </div>
                        <div class="text-left hidden md:block">
                            <div class="text-white text-xs font-semibold leading-tight">{{ user?.name || 'Admin' }}</div>
                            <div class="text-charcoal-400 text-[10px] leading-tight">Administrateur</div>
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
                        <div
                            v-if="openMenu === 'user'"
                            class="absolute top-full right-0 mt-0 w-48 bg-white rounded-b-lg shadow-lg border border-pearl-200 overflow-hidden z-50"
                        >
                            <div class="px-4 py-3 border-b border-pearl-200 bg-pearl-50">
                                <div class="text-xs font-bold text-charcoal-700">{{ user?.name || 'Admin' }}</div>
                                <div class="text-[11px] text-charcoal-400 truncate">{{ user?.email }}</div>
                            </div>
                            <Link
                                :href="route('profile.edit')"
                                class="flex items-center gap-2 px-4 py-2.5 text-xs text-charcoal-600 hover:bg-pearl-100 hover:text-gold-600 transition-colors border-b border-pearl-200"
                                @click="closeMenus"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Profil
                            </Link>
                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="w-full flex items-center gap-2 px-4 py-2.5 text-xs text-red-500 hover:bg-red-50 transition-colors"
                                @click="closeMenus"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Déconnexion
                            </Link>
                        </div>
                    </Transition>
                </div>
            </div>
        </nav>

        <!-- Backdrop pour fermer les menus -->
        <div
            v-if="openMenu"
            class="fixed inset-0 z-40"
            @click="closeMenus"
        ></div>

        <!-- ═══════════════════ CONTENU PRINCIPAL ═══════════════════ -->
        <main class="pt-14 min-h-screen bg-pearl-100">

            <!-- Page Header -->
            <div v-if="$slots.header" class="bg-white border-b border-pearl-200 px-6 py-4 shadow-sm">
                <slot name="header" />
            </div>

            <!-- Slot principal -->
            <div class="p-6">
                <slot />
            </div>
        </main>
    </div>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

.bg-gold-gradient {
    background: linear-gradient(135deg, #D4A017 0%, #8B6914 100%);
}
</style>
