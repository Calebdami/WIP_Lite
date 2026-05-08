<script setup>
import { ref, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { useForm } from '@inertiajs/vue3'
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    assignments: Object,
    campaigns: Array,
    positions: Array,
    filters: Object
})

// État du formulaire de recherche
const search = ref(props.filters.search || '')
const selectedCampaign = ref(props.filters.campaign_id || '')
const selectedRole = ref(props.filters.role || '')
const selectedDate = ref(props.filters.date || '')
const selectedAction = ref(props.filters.action_type || '')

// Formulaires pour les actions
const showFilters = ref(false)
const showDetails = ref(null)

// Fonctions de filtrage
const applyFilters = () => {
    router.get(route('admin.assignments.affectation-history'), {
        search: search.value,
        campaign_id: selectedCampaign.value,
        role: selectedRole.value,
        date: selectedDate.value,
        action_type: selectedAction.value
    }, {
        preserveState: true,
        preserveScroll: true
    })
}

const resetFilters = () => {
    search.value = ''
    selectedCampaign.value = ''
    selectedRole.value = ''
    selectedDate.value = ''
    selectedAction.value = ''
    applyFilters()
}

// Fonctions utilitaires
const getActionType = (assignment) => {
    if (assignment.status === 'active') return 'Affectation'
    if (assignment.status === 'terminated') return 'Libération'
    return 'Réaffectation'
}

const getStatusBadge = (status) => {
    const badges = {
        active: 'bg-green-100 text-green-800',
        terminated: 'bg-red-100 text-red-800',
        suspended: 'bg-yellow-100 text-yellow-800'
    }
    return badges[status] || 'bg-gray-100 text-gray-800'
}

const getStatusText = (status) => {
    const texts = {
        active: 'Affecté',
        terminated: 'Non affecté',
        suspended: 'Suspendu'
    }
    return texts[status] || status
}

const getRoleText = (code) => {
    const roles = {
        CP: 'CP',
        SUP: 'SUP',
        TC: 'TC'
    }
    return roles[code] || code
}

const formatDate = (dateString) => {
    if (!dateString) return '-'
    return new Date(dateString).toLocaleDateString('fr-FR')
}

const getImpactCascade = (assignment) => {
    if (assignment.position?.code === 'SUP' && assignment.status === 'terminated') {
        return 'TC libérés après SUP'
    }
    if (assignment.position?.code === 'CP' && assignment.status === 'terminated') {
        return 'SUP et TC libérés après CP'
    }
    return '-'
}

// Watchers pour la recherche automatique
watch([search, selectedCampaign, selectedRole, selectedDate, selectedAction], () => {
    if (search.value !== props.filters.search ||
        selectedCampaign.value !== props.filters.campaign_id ||
        selectedRole.value !== props.filters.role ||
        selectedDate.value !== props.filters.date ||
        selectedAction.value !== props.filters.action_type) {
        applyFilters()
    }
}, { deep: true })
</script>

<template>
    <Head title="Historique des Affectations — Admin" />
    <AdminLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- En-tête -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Historique des Affectations</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Consultez l'historique complet des affectations avec filtres avancés
                    </p>
                </div>

                <!-- Filtres -->
                <div class="bg-white shadow rounded-lg p-4 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Filtres</h2>
                        <button 
                            @click="showFilters = !showFilters"
                            class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                        >
                            {{ showFilters ? 'Masquer' : 'Afficher' }} les filtres
                        </button>
                    </div>

                    <div v-show="showFilters" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <!-- Recherche -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Recherche (Nom, Matricule)
                            </label>
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Rechercher un employé..."
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>

                        <!-- Campagne -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Campagne
                            </label>
                            <select
                                v-model="selectedCampaign"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">Toutes les campagnes</option>
                                <option v-for="campaign in campaigns" :key="campaign.id" :value="campaign.id">
                                    {{ campaign.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Rôle -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Rôle
                            </label>
                            <select
                                v-model="selectedRole"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">Tous les rôles</option>
                                <option v-for="position in positions" :key="position.code" :value="position.code">
                                    {{ getRoleText(position.code) }} - {{ position.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Date
                            </label>
                            <input
                                v-model="selectedDate"
                                type="date"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>

                        <!-- Type d'action -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Type d'action
                            </label>
                            <select
                                v-model="selectedAction"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">Toutes les actions</option>
                                <option value="assignment">Affectation</option>
                                <option value="reassignment">Réaffectation</option>
                                <option value="release">Libération</option>
                            </select>
                        </div>
                    </div>

                    <!-- Boutons d'action -->
                    <div v-show="showFilters" class="flex gap-2 mt-4">
                        <button
                            @click="applyFilters"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium"
                        >
                            Appliquer les filtres
                        </button>
                        <button
                            @click="resetFilters"
                            class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 text-sm font-medium"
                        >
                            Réinitialiser
                        </button>
                    </div>
                </div>

                <!-- Tableau des affectations -->
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Employé
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Poste
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Type d'action
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Campagne
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Responsable lié
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date début
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date fin
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Statut
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="assignment in assignments.data" :key="assignment.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ assignment.employee?.first_name }} {{ assignment.employee?.last_name }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ assignment.employee?.matricule }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ getRoleText(assignment.position?.code) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ getActionType(assignment) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ assignment.campaign?.name || '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div v-if="assignment.manager">
                                            {{ assignment.manager?.first_name }} {{ assignment.manager?.last_name }}
                                            <div class="text-xs text-gray-500">
                                                {{ assignment.manager?.matricule }}
                                            </div>
                                        </div>
                                        <span v-else class="text-gray-400">-</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ formatDate(assignment.start_date) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ formatDate(assignment.end_date) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusBadge(assignment.status)}`">
                                            {{ getStatusText(assignment.status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <button
                                            @click="showDetails = showDetails === assignment.id ? null : assignment.id"
                                            class="text-blue-600 hover:text-blue-900 font-medium"
                                        >
                                            Détails
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Détails de l'affectation -->
                    <div v-if="showDetails" class="border-t border-gray-200 bg-gray-50 p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-2">Informations générales</h4>
                                <dl class="space-y-1">
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">ID Affectation:</dt>
                                        <dd class="text-sm text-gray-900">{{ showDetails }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Motif:</dt>
                                        <dd class="text-sm text-gray-900">Affectation initiale</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Auteur action:</dt>
                                        <dd class="text-sm text-gray-900">Admin Système</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Impact cascade:</dt>
                                        <dd class="text-sm text-gray-900">{{ getImpactCascade(assignments.data.find(a => a.id === showDetails)) }}</dd>
                                    </div>
                                </dl>
                            </div>
                            
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-2">Ancienne affectation</h4>
                                <dl class="space-y-1">
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Campagne:</dt>
                                        <dd class="text-sm text-gray-900">-</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Manager:</dt>
                                        <dd class="text-sm text-gray-900">-</dd>
                                    </div>
                                </dl>
                            </div>
                            
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-2">Nouvelle affectation</h4>
                                <dl class="space-y-1">
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Campagne:</dt>
                                        <dd class="text-sm text-gray-900">{{ assignments.data.find(a => a.id === showDetails)?.campaign?.name || '-' }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Manager:</dt>
                                        <dd class="text-sm text-gray-900">{{ assignments.data.find(a => a.id === showDetails)?.manager?.first_name }} {{ assignments.data.find(a => a.id === showDetails)?.manager?.last_name || '-' }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="assignments.links" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                        <div class="flex-1 flex justify-between sm:hidden">
                            <Link
                                :href="assignments.prev"
                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Précédent
                            </Link>
                            <Link
                                :href="assignments.next"
                                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Suivant
                            </Link>
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Affichage de
                                    <span class="font-medium">{{ assignments.from }}</span>
                                    à
                                    <span class="font-medium">{{ assignments.to }}</span>
                                    sur
                                    <span class="font-medium">{{ assignments.total }}</span>
                                    résultats
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                    <template v-for="(link, key) in assignments.links" :key="key">
                                        <Link
                                            v-if="link.url"
                                            :href="link.url"
                                            :class="link.active ? 'relative inline-flex items-center px-4 py-2 border border-blue-500 text-sm font-medium rounded-md bg-blue-50 text-blue-600' : 'relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50'"
                                        >
                                            <span v-html="link.label"></span>
                                        </Link>
                                        <span
                                            v-else
                                            :class="link.active ? 'relative inline-flex items-center px-4 py-2 border border-blue-500 text-sm font-medium rounded-md bg-blue-50 text-blue-600' : 'relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50'"
                                        >
                                            <span v-html="link.label"></span>
                                        </span>
                                    </template>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Message si aucun résultat -->
                <div v-if="assignments.data.length === 0" class="bg-white shadow rounded-lg p-6 text-center">
                    <div class="text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707H19a2 2 0 012 2v1a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune affectation trouvée</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Essayez de modifier vos filtres ou votre recherche.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
