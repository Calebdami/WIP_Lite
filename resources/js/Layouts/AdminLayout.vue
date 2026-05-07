<script setup>
import { ref, computed, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import ConfirmDialog from 'primevue/confirmdialog';

const page = usePage();
const toast = useToast();
const user = computed(() => page.props.auth?.user);
const flash = computed(() => page.props.flash);

// Watch for flash messages
watch(flash, (newFlash) => {
    if (newFlash?.success) {
        toast.add({
            severity: 'success',
            summary: 'Succès',
            detail: newFlash.success,
            life: 3000
        });
    }
    if (newFlash?.error) {
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: newFlash.error,
            life: 5000
        });
    }
}, { deep: true, immediate: true });

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
            { label: 'Historique des employés', href: route('admin.employees.history') },
            { label: 'Scoring & Performance', href: route('admin.scoring.index') },
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
            { label: 'Validation Plannings', href: route('admin.assignments.validation') },
            { label: 'Historique plannings', href: route('admin.assignments.history') },
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
    <div class="h-screen flex flex-col bg-pearl-100 font-sans selection:bg-gold-200 selection:text-gold-900">
        <!-- Navbar -->
        <nav class="fixed top-0 left-0 right-0 z-50 bg-charcoal-900 shadow-premium border-b border-charcoal-800 backdrop-blur-md bg-charcoal-900/95">
            <div class="flex items-center h-16 px-6">
                <!-- Logo -->
                <Link :href="route('admin.dashboard')" class="flex items-center gap-4 min-w-[220px] border-r border-charcoal-800 pr-8 mr-4 h-full group" @click="closeMenus">
                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gold-gradient shadow-gold-premium group-hover:scale-105 transition-premium">
                        <span class="text-charcoal-900 font-black text-lg">W</span>
                    </div>
                    <div class="flex flex-col leading-tight">
                        <span class="text-white font-black text-base tracking-tight">Administration</span>
                        <span class="text-gold-500 text-[9px] font-black tracking-[0.2em] uppercase opacity-80">Console de Gestion</span>
                        <span class="text-gold-500 text-[9px] font-black tracking-[0.2em] uppercase opacity-80">Console</span>
                    </div>
                </Link>

                <!-- Navigation principale -->
                <div class="flex items-center flex-1 h-full ml-2 gap-1">
                    <template v-for="item in navItems" :key="item.label">
                        <!-- Lien simple -->
                        <template v-if="!item.children">
                            <Link
                                :href="item.href"
                                class="flex items-center gap-2 h-10 px-4 rounded-xl text-xs font-black uppercase tracking-widest transition-premium whitespace-nowrap"
                                :class="item.active
                                    ? 'text-gold-400 bg-charcoal-800 shadow-inner'
                                    : 'text-charcoal-400 hover:text-white hover:bg-charcoal-800/50'"
                                @click="closeMenus"
                            >
                                {{ item.label }}
                            </Link>
                        </template>

                        <!-- Dropdown -->
                        <div v-else class="relative h-full flex items-center">
                            <button
                                @click="toggleMenu(item.key)"
                                class="flex items-center gap-2 h-10 px-4 rounded-xl text-xs font-black uppercase tracking-widest transition-premium whitespace-nowrap"
                                :class="openMenu === item.key
                                    ? 'text-gold-400 bg-charcoal-800 shadow-inner'
                                    : 'text-charcoal-400 hover:text-white hover:bg-charcoal-800/50'"
                            >
                                {{ item.label }}
                                <i class="pi pi-chevron-down text-[10px] transition-premium" :class="openMenu === item.key ? 'rotate-180 text-gold-400' : ''"></i>
                            </button>

                            <Transition
                                enter-active-class="transition ease-out duration-200"
                                enter-from-class="opacity-0 translate-y-2 scale-95"
                                enter-to-class="opacity-100 translate-y-0 scale-100"
                                leave-active-class="transition ease-in duration-150"
                                leave-from-class="opacity-100 translate-y-0 scale-100"
                                leave-to-class="opacity-0 translate-y-2 scale-95"
                            >
                                <div v-if="openMenu === item.key" class="absolute top-[calc(100%-8px)] left-0 w-64 bg-white rounded-2xl shadow-premium border border-pearl-100 overflow-hidden z-50 p-2">
                                    <Link
                                        v-for="child in item.children"
                                        :key="child.label"
                                        :href="child.href"
                                        class="block px-4 py-2.5 rounded-xl text-xs font-bold text-charcoal-600 hover:bg-pearl-50 hover:text-gold-600 transition-premium border-b border-pearl-50 last:border-0"
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
                <div class="relative ml-4 pl-6 border-l border-charcoal-800 flex items-center h-full">
                    <button @click="toggleMenu('user')" class="flex items-center gap-3 px-3 py-2 rounded-2xl transition-premium hover:bg-charcoal-800 group">
                        <div class="w-9 h-9 rounded-full bg-gold-gradient flex items-center justify-center text-charcoal-900 font-black text-sm shadow-gold-premium group-hover:rotate-12 transition-premium flex-shrink-0">
                            {{ (user?.name || user?.email || 'A').charAt(0).toUpperCase() }}
                        </div>
                        <div class="text-left hidden lg:block">
                            <div class="text-white text-xs font-black leading-tight">{{ user?.name || 'Admin' }}</div>
                            <div class="text-charcoal-400 text-[10px] font-bold uppercase tracking-tighter opacity-60">Administrateur</div>
                        </div>
                        <i class="pi pi-chevron-down text-[10px] text-charcoal-500 transition-premium" :class="openMenu === 'user' ? 'rotate-180 text-gold-400' : ''"></i>
                    </button>

                    <Transition
                        enter-active-class="transition ease-out duration-200"
                        enter-from-class="opacity-0 translate-y-2 scale-95"
                        enter-to-class="opacity-100 translate-y-0 scale-100"
                        leave-active-class="transition ease-in duration-150"
                        leave-from-class="opacity-100 translate-y-0 scale-100"
                        leave-to-class="opacity-0 translate-y-2 scale-95"
                    >
                        <div v-if="openMenu === 'user'" class="absolute top-[calc(100%-8px)] right-0 w-56 bg-white rounded-2xl shadow-premium border border-pearl-100 overflow-hidden z-50">
                            <div class="px-5 py-4 border-b border-pearl-50 bg-pearl-50/50">
                                <div class="text-xs font-black text-charcoal-900">{{ user?.name || 'Admin' }}</div>
                                <div class="text-[10px] text-charcoal-400 font-medium truncate mt-0.5">{{ user?.email }}</div>
                            </div>
                            <div class="p-2">
                                <Link :href="route('logout')" method="post" as="button" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold text-red-500 hover:bg-red-50 transition-premium" @click="closeMenus">
                                    <i class="pi pi-power-off text-sm opacity-60"></i>
                                    Déconnexion
                                </Link>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </nav>

        <!-- Backdrop -->
        <div v-if="openMenu" class="fixed inset-0 z-40 bg-charcoal-900/10 backdrop-blur-[2px]" @click="closeMenus"></div>

        <!-- Main Content -->
        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto pt-16 bg-pearl-100">
            <div v-if="$slots.header" class="bg-white border-b border-pearl-100 px-8 py-6 shadow-sm sticky top-0 z-10">
                <div class="max-w-7xl mx-auto">
                    <slot name="header" />
                </div>
            </div>
            <div class="p-8 max-w-[1600px] mx-auto">
                <slot />
            </div>
        </main>
        <Toast position="top-right" />
        <ConfirmDialog />
    </div>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

.bg-gold-gradient {
    background: linear-gradient(135deg, #D4A017 0%, #8B6914 100%);
}

:deep(label.text-xs), :deep(label.text-\[10px\]) {
    font-weight: 900 !important;
    letter-spacing: 0.1em !important;
    color: #64748b !important; /* charcoal-400 equivalent */
    margin-bottom: 0.5rem !important;
    display: inline-block;
}
</style>
