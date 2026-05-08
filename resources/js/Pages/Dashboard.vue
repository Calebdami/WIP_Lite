<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    auth: {
        type: Object,
        required: true
    }
});

const userName = computed(() => {
    return props.auth.user?.name || 'Utilisateur';
});

const userEmail = computed(() => {
    return props.auth.user?.email || '';
});
</script>

<template>
    <Head title="En attente d'attribution de rôle" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Bienvenue, {{ userName }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <!-- Message principal -->
                <div class="bg-blue-50 border-l-4 border-blue-400 p-6 mb-8 rounded-lg shadow">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-blue-800">
                                En attente d'attribution de rôle
                            </h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <p>
                                    Votre compte a été créé avec succès, mais aucun rôle ne vous a encore été attribué.
                                    Veuillez patienter pendant qu'un administrateur configure votre accès au système.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informations utilisateur -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Vos informations</h3>
                    </div>
                    <div class="p-6">
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Nom d'utilisateur</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ userName }}</dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Adresse email</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ userEmail }}</dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Rôle actuel</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Non défini
                                    </span>
                                </dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Statut du compte</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Actif
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Que faire maintenant ?</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 h-6 w-6 text-green-500">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-medium text-gray-900">Patientez</h4>
                                    <p class="mt-1 text-sm text-gray-600">
                                        Un administrateur va vous attribuer un rôle dans les plus brefs délais.
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="flex-shrink-0 h-6 w-6 text-blue-500">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-medium text-gray-900">Vérifiez vos emails</h4>
                                    <p class="mt-1 text-sm text-gray-600">
                                        Vous recevrez une notification par email lorsque votre rôle sera attribué.
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="flex-shrink-0 h-6 w-6 text-purple-500">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-medium text-gray-900">Contactez le support</h4>
                                    <p class="mt-1 text-sm text-gray-600">
                                        Si vous attendez depuis plus de 24h, contactez votre administrateur système.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rôles disponibles -->
                <div class="bg-gray-50 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Rôles disponibles dans WIP Lite</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-white p-4 rounded-lg border border-gray-200">
                                <div class="flex items-center mb-2">
                                    <div class="w-2 h-2 bg-red-500 rounded-full mr-2"></div>
                                    <h4 class="font-medium text-gray-900">Administrateur</h4>
                                </div>
                                <p class="text-sm text-gray-600">Gestion complète du système, utilisateurs et configurations.</p>
                            </div>
                            
                            <div class="bg-white p-4 rounded-lg border border-gray-200">
                                <div class="flex items-center mb-2">
                                    <div class="w-2 h-2 bg-blue-500 rounded-full mr-2"></div>
                                    <h4 class="font-medium text-gray-900">Chef de Plateau</h4>
                                </div>
                                <p class="text-sm text-gray-600">Supervision des équipes, gestion des campagnes et des plannings.</p>
                            </div>
                            
                            <div class="bg-white p-4 rounded-lg border border-gray-200">
                                <div class="flex items-center mb-2">
                                    <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                    <h4 class="font-medium text-gray-900">Superviseur</h4>
                                </div>
                                <p class="text-sm text-gray-600">Encadrement des téléconseillers et validation des feuilles de temps.</p>
                            </div>
                            
                            <div class="bg-white p-4 rounded-lg border border-gray-200">
                                <div class="flex items-center mb-2">
                                    <div class="w-2 h-2 bg-yellow-500 rounded-full mr-2"></div>
                                    <h4 class="font-medium text-gray-900">Téléconseiller</h4>
                                </div>
                                <p class="text-sm text-gray-600">Traitement des appels et gestion des temps de travail.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bouton de rafraîchissement -->
                <div class="mt-8 text-center">
                    <button 
                        @click="$inertia.reload()"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                    >
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Vérifier si un rôle a été attribué
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
