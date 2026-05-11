<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';

const enterprise = ref({
    name: 'Grande Entreprise SARL',
    siret: '123 456 789 00012',
    address: '123 Avenue des Champs-Élysées\n75008 Paris, France'
});

const departments = ref([
    { id: 1, name: 'Commercial' },
    { id: 2, name: 'Support' },
    { id: 3, name: 'Ventes' },
    { id: 4, name: 'SAV' },
    { id: 5, name: 'Marketing' },
    { id: 6, name: 'RH' },
]);

const poles = ref([
    { id: 1, name: 'Télémarketing', employees: 254 },
    { id: 2, name: 'Service Client', employees: 189 },
    { id: 3, name: 'Ventes B2B', employees: 150 },
    { id: 4, name: 'Support Technique', employees: 90 },
    { id: 5, name: 'Administration', employees: 45 },
    { id: 6, name: 'Formation', employees: 23 },
]);
</script>

<template>
    <Head title="Structure de l'Entreprise" />

    <div class="min-h-screen bg-[#f4f5f7] font-sans flex text-gray-800">
        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Horizontal Navbar -->
            <header class="bg-white border-b border-gray-200 h-16 flex items-center px-4 md:px-6 shrink-0 z-10 shadow-sm relative">
                <!-- Title / Logo Area -->
                <div class="flex items-center gap-3 w-64 shrink-0">
                    <div class="w-10 h-10 bg-gradient-to-br from-yellow-600 to-amber-800 rounded flex items-center justify-center text-white font-bold text-xs shadow-md">
                        NS<br>BU
                    </div>
                    <div>
                        <h1 class="font-bold text-lg leading-tight text-gray-900">Administration</h1>
                        <p class="text-[10px] text-gray-500 uppercase tracking-wide">Gestion d'entreprise</p>
                    </div>
                </div>

                <!-- Navigation Menu -->
                <nav class="flex-1 flex items-center justify-center gap-1 md:gap-4 overflow-x-auto text-sm text-gray-600 font-medium">
                    <a href="#" class="flex items-center gap-2 hover:text-amber-600 px-2 py-1 rounded transition-colors whitespace-nowrap">
                        <i class="pi pi-th-large text-gray-400"></i>
                        Tableau de bord
                    </a>
                    
                    <div class="relative group cursor-pointer whitespace-nowrap">
                        <div class="flex items-center gap-2 hover:text-amber-600 px-2 py-1 rounded transition-colors">
                            <i class="pi pi-users text-gray-400"></i>
                            Personnel
                            <i class="pi pi-angle-down text-xs ml-1"></i>
                        </div>
                    </div>

                    <a href="#" class="flex items-center gap-2 hover:text-amber-600 px-2 py-1 rounded transition-colors whitespace-nowrap">
                        <i class="pi pi-megaphone text-gray-400"></i>
                        Campagnes
                    </a>

                    <div class="relative group cursor-pointer whitespace-nowrap">
                        <div class="flex items-center gap-2 hover:text-amber-600 px-2 py-1 rounded transition-colors">
                            <i class="pi pi-sitemap text-gray-400"></i>
                            Affectations
                            <i class="pi pi-angle-down text-xs ml-1"></i>
                        </div>
                    </div>

                    <div class="relative group cursor-pointer whitespace-nowrap">
                        <div class="flex items-center gap-2 hover:text-amber-600 px-2 py-1 rounded transition-colors">
                            <i class="pi pi-clock text-gray-400"></i>
                            Temps de travail
                            <i class="pi pi-angle-down text-xs ml-1"></i>
                        </div>
                    </div>

                    <div class="relative group cursor-pointer whitespace-nowrap">
                        <div class="flex items-center gap-2 text-amber-600 px-2 py-1 rounded transition-colors border-b-2 border-amber-600 pb-1 mt-1">
                            <i class="pi pi-cog"></i>
                            Configuration
                            <i class="pi pi-angle-down text-xs ml-1"></i>
                        </div>
                        <!-- Active Dropdown indicator based on image -->
                        <div class="absolute top-full mt-2 bg-white border border-gray-100 shadow-xl rounded-lg py-2 w-48 z-50 text-gray-700 font-normal">
                            <a href="#" class="block px-4 py-2 hover:bg-gray-50 text-amber-600 font-medium">Entreprise</a>
                            <a href="#" class="block px-4 py-2 hover:bg-gray-50">Référentiels</a>
                            <a href="#" class="block px-4 py-2 hover:bg-gray-50">Rôles & Permissions</a>
                        </div>
                    </div>

                    <div class="relative group cursor-pointer whitespace-nowrap">
                        <div class="flex items-center gap-2 hover:text-amber-600 px-2 py-1 rounded transition-colors">
                            <i class="pi pi-shield text-gray-400"></i>
                            Sécurité
                            <i class="pi pi-angle-down text-xs ml-1"></i>
                        </div>
                    </div>

                    <a href="#" class="flex items-center gap-2 hover:text-amber-600 px-2 py-1 rounded transition-colors whitespace-nowrap">
                        <i class="pi pi-calendar text-gray-400"></i>
                        Calendrier
                    </a>

                    <div class="relative group cursor-pointer whitespace-nowrap">
                        <div class="flex items-center gap-2 hover:text-amber-600 px-2 py-1 rounded transition-colors">
                            <i class="pi pi-bell text-gray-400"></i>
                            Notifications
                            <i class="pi pi-angle-down text-xs ml-1"></i>
                        </div>
                    </div>

                    <a href="#" class="flex items-center gap-2 hover:text-amber-600 px-2 py-1 rounded transition-colors whitespace-nowrap">
                        <i class="pi pi-wrench text-gray-400"></i>
                        Maintenance
                    </a>
                </nav>

                <!-- User Profile -->
                <div class="flex items-center gap-4 shrink-0 pl-4 border-l border-gray-200 ml-4">
                    <div class="text-right hidden md:block">
                        <div class="font-bold text-sm text-gray-900">Admin</div>
                        <div class="text-xs text-gray-500">Administrateur</div>
                    </div>
                    <div class="w-8 h-8 rounded-full bg-gradient-to-r from-gray-500 to-gray-700 text-white flex items-center justify-center">
                        <i class="pi pi-user"></i>
                    </div>
                    <button class="text-gray-400 hover:text-gray-700 transition-colors">
                        <i class="pi pi-sign-out text-lg"></i>
                    </button>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-6 lg:p-8 overflow-y-auto">
                <!-- Header -->
                <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Structure de l'Entreprise</h2>
                        <p class="text-gray-500 text-sm mt-1">Départements, pôles et informations légales</p>
                    </div>
                    <button class="bg-gradient-to-r from-yellow-500 to-amber-600 hover:from-yellow-600 hover:to-amber-700 text-white px-6 py-2 rounded-md shadow-md font-medium transition-all flex items-center gap-2">
                        <i class="pi pi-save"></i>
                        Enregistrer
                    </button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Left Column: Informations générales -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-6 flex items-center gap-2">
                            <i class="pi pi-building text-gray-400"></i>
                            Informations générales
                        </h3>

                        <div class="space-y-5">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1 uppercase tracking-wider">Nom de l'entreprise</label>
                                <input type="text" v-model="enterprise.name" class="w-full border-gray-300 rounded-md shadow-sm focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50 text-sm py-2 px-3 transition-colors">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1 uppercase tracking-wider">SIRET</label>
                                <input type="text" v-model="enterprise.siret" class="w-full border-gray-300 rounded-md shadow-sm focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50 text-sm py-2 px-3 transition-colors">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1 uppercase tracking-wider">Adresse</label>
                                <textarea v-model="enterprise.address" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50 text-sm py-2 px-3 transition-colors resize-none"></textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-2 uppercase tracking-wider">Logo</label>
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 bg-gray-50 border border-gray-200 rounded-lg flex items-center justify-center text-gray-300">
                                        <i class="pi pi-image text-2xl"></i>
                                    </div>
                                    <button class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-md text-sm font-medium transition-colors flex items-center gap-2">
                                        <i class="pi pi-upload"></i>
                                        Télécharger
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Départements -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 flex flex-col">
                        <div class="p-6 border-b border-gray-100 flex-1">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Départements</h3>
                            
                            <div class="space-y-1">
                                <div v-for="dept in departments" :key="dept.id" class="flex items-center justify-between py-3 hover:bg-gray-50 px-2 -mx-2 rounded transition-colors group">
                                    <span class="text-sm font-medium text-gray-700">{{ dept.name }}</span>
                                    <div class="flex items-center gap-4 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button class="text-xs font-medium text-gray-500 hover:text-amber-600 transition-colors">Modifier</button>
                                        <button class="text-xs font-medium text-gray-500 hover:text-red-600 transition-colors">Supprimer</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="p-4 bg-gray-50 border-t border-gray-100 rounded-b-xl">
                            <button class="w-full py-2 bg-white border border-gray-300 border-dashed rounded-md text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-amber-600 hover:border-amber-400 transition-colors">
                                Ajouter un département
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Bottom Row: Pôles d'activité -->
                <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-6">Pôles d'activité</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div v-for="pole in poles" :key="pole.id" class="border border-gray-100 rounded-lg p-5 hover:shadow-md transition-shadow hover:border-amber-200 bg-gray-50 hover:bg-white group">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h4 class="font-semibold text-gray-800 text-sm mb-1">{{ pole.name }}</h4>
                                    <span class="text-xs text-gray-500">{{ pole.employees }} employés</span>
                                </div>
                            </div>
                            <button class="text-xs font-medium text-gray-500 hover:text-amber-600 transition-colors opacity-0 group-hover:opacity-100">
                                Modifier
                            </button>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>
