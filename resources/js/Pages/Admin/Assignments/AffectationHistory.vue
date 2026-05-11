<script setup>
import { ref, computed } from 'vue'
import { Link, router, Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Tag from 'primevue/tag'
import Dialog from 'primevue/dialog'

const props = defineProps({
    assignments: Object,
    campaigns: Array,
    positions: Array,
    filters: Object
})

// Search state
const search = ref(props.filters.search || '')
const selectedCampaign = ref(props.filters.campaign_id || '')
const selectedRole = ref(props.filters.role || '')
const selectedDate = ref(props.filters.date || '')
const selectedAction = ref(props.filters.action_type || '')

// Modals
const showDetailsModal = ref(false)
const selectedAssignmentDetails = ref(null)

// Filtering functions
const triggerSearch = () => {
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
    triggerSearch()
}

// Utility functions
const getActionType = (assignment) => {
    if (assignment.status === 'active') return 'Affectation'
    if (assignment.status === 'terminated') return 'Libération'
    return 'Réaffectation'
}

const getStatusSeverity = (status) => {
    switch (status) {
        case 'active': return 'success'
        case 'terminated': return 'danger'
        case 'suspended': return 'warning'
        default: return 'info'
    }
}

const getStatusText = (status) => {
    const texts = {
        active: 'Affecté',
        terminated: 'Libéré',
        suspended: 'Suspendu'
    }
    return texts[status] || status
}

const formatDate = (dateString) => {
    if (!dateString) return '-'
    return new Date(dateString).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    })
}

const openDetails = (assignment) => {
    selectedAssignmentDetails.value = assignment
    showDetailsModal.value = true
}

const getImpactCascade = (assignment) => {
    if (!assignment) return '-'
    if (assignment.position?.code === 'SUP' && assignment.status === 'terminated') {
        return 'Désaffectation automatique des TC subordonnés'
    }
    if (assignment.position?.code === 'CP' && assignment.status === 'terminated') {
        return 'Désaffectation automatique des SUP et TC subordonnés'
    }
    return 'Aucun impact en cascade'
}
</script>

<template>
    <Head title="Historique des Affectations — Admin" />
    <AdminLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Historique des Affectations</h1>
                    <p class="text-xs text-charcoal-400 mt-0.5">Audit complet des mouvements et changements d'équipes</p>
                </div>
                
                <div class="flex items-center gap-3">
                    <Link :href="route('admin.assignments.structure')"
                        class="inline-flex items-center px-4 py-2 bg-charcoal-900 text-white text-[10px] font-black uppercase tracking-wider rounded-xl hover:bg-gold-600 transition-all shadow-md">
                        <i class="pi pi-sitemap mr-2"></i>
                        Voir la Structure
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Advanced Filters Card -->
            <div class="bg-white rounded-2xl border border-pearl-200 shadow-sm p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <!-- Search -->
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-charcoal-400 uppercase tracking-widest ml-1">Employé</label>
                        <div class="relative">
                            <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-charcoal-300 text-xs"></i>
                            <input 
                                v-model="search"
                                type="text" 
                                placeholder="Nom ou matricule..."
                                @keydown.enter="triggerSearch"
                                class="w-full bg-pearl-50 border border-pearl-100 rounded-xl pl-9 pr-4 py-2.5 text-xs text-charcoal-700 focus:border-gold-400 outline-none transition-all"
                            />
                        </div>
                    </div>

                    <!-- Campaign -->
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-charcoal-400 uppercase tracking-widest ml-1">Campagne</label>
                        <select 
                            v-model="selectedCampaign"
                            class="w-full bg-pearl-50 border border-pearl-100 rounded-xl px-4 py-2.5 text-xs text-charcoal-700 focus:border-gold-400 outline-none transition-all appearance-none cursor-pointer"
                        >
                            <option value="">Toutes les campagnes</option>
                            <option v-for="c in campaigns" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>

                    <!-- Role -->
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-charcoal-400 uppercase tracking-widest ml-1">Rôle</label>
                        <select 
                            v-model="selectedRole"
                            class="w-full bg-pearl-50 border border-pearl-100 rounded-xl px-4 py-2.5 text-xs text-charcoal-700 focus:border-gold-400 outline-none transition-all appearance-none cursor-pointer"
                        >
                            <option value="">Tous les rôles</option>
                            <option v-for="p in positions" :key="p.code" :value="p.code">{{ p.code }} - {{ p.name }}</option>
                        </select>
                    </div>

                    <!-- Date -->
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-charcoal-400 uppercase tracking-widest ml-1">Date</label>
                        <input 
                            v-model="selectedDate"
                            type="date"
                            class="w-full bg-pearl-50 border border-pearl-100 rounded-xl px-4 py-2.5 text-xs text-charcoal-700 focus:border-gold-400 outline-none transition-all"
                        />
                    </div>

                    <!-- Actions Buttons -->
                    <div class="flex items-end gap-2">
                        <button 
                            @click="triggerSearch"
                            class="flex-1 bg-gold-gradient text-charcoal-900 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest hover:opacity-90 transition-all shadow-gold-sm"
                        >
                            Filtrer
                        </button>
                        <button 
                            @click="resetFilters"
                            class="px-3 bg-pearl-100 text-charcoal-400 py-2.5 rounded-xl hover:bg-pearl-200 transition-all"
                            title="Réinitialiser"
                        >
                            <i class="pi pi-refresh"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Main Data Table -->
            <div class="bg-white rounded-2xl border border-pearl-200 shadow-sm overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-charcoal-900 border-b border-charcoal-800">
                            <th class="px-6 py-4 text-[10px] font-black text-pearl-400 uppercase tracking-widest">Employé</th>
                            <th class="px-6 py-4 text-[10px] font-black text-pearl-400 uppercase tracking-widest">Poste</th>
                            <th class="px-6 py-4 text-[10px] font-black text-pearl-400 uppercase tracking-widest text-center">Action</th>
                            <th class="px-6 py-4 text-[10px] font-black text-pearl-400 uppercase tracking-widest">Campagne</th>
                            <th class="px-6 py-4 text-[10px] font-black text-pearl-400 uppercase tracking-widest">Hiérarchie</th>
                            <th class="px-6 py-4 text-[10px] font-black text-pearl-400 uppercase tracking-widest">Période</th>
                            <th class="px-6 py-4 text-[10px] font-black text-pearl-400 uppercase tracking-widest text-center">Statut</th>
                            <th class="px-6 py-4 text-[10px] font-black text-pearl-400 uppercase tracking-widest text-right">Détails</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-pearl-100">
                        <tr v-for="assignment in assignments.data" :key="assignment.id" class="hover:bg-pearl-50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-pearl-100 flex items-center justify-center text-charcoal-700 font-bold text-[10px] border border-pearl-200">
                                        {{ assignment.employee?.first_name.charAt(0) }}{{ assignment.employee?.last_name.charAt(0) }}
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-charcoal-800">{{ assignment.employee?.first_name }} {{ assignment.employee?.last_name }}</div>
                                        <div class="text-[9px] text-charcoal-400 font-bold uppercase tracking-tighter">{{ assignment.employee?.matricule }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 bg-pearl-100 border border-pearl-200 rounded-md text-[9px] font-black text-charcoal-600 uppercase tracking-tighter">
                                    {{ assignment.position?.code }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span :class="assignment.status === 'active' ? 'text-emerald-600' : 'text-rose-500'" class="text-[10px] font-black uppercase tracking-widest">
                                    {{ getActionType(assignment) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs font-bold text-charcoal-600">
                                {{ assignment.campaign?.name || '-' }}
                            </td>
                            <td class="px-6 py-4">
                                <div v-if="assignment.manager" class="flex flex-col">
                                    <span class="text-[10px] font-bold text-charcoal-700">{{ assignment.manager?.first_name }} {{ assignment.manager?.last_name }}</span>
                                    <span class="text-[8px] text-charcoal-400 uppercase font-black tracking-tighter">Responsable lié</span>
                                </div>
                                <span v-else class="text-pearl-400 text-xs">-</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col text-[10px] font-bold text-charcoal-600">
                                    <div class="flex items-center gap-1">
                                        <i class="pi pi-arrow-right text-[8px] text-emerald-500"></i>
                                        <span>{{ formatDate(assignment.start_date) }}</span>
                                    </div>
                                    <div v-if="assignment.end_date" class="flex items-center gap-1 text-charcoal-400">
                                        <i class="pi pi-arrow-left text-[8px] text-rose-400"></i>
                                        <span>{{ formatDate(assignment.end_date) }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <Tag :value="getStatusText(assignment.status)" :severity="getStatusSeverity(assignment.status)" class="!text-[8px] !font-black !uppercase !tracking-widest !px-3" />
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button 
                                    @click="openDetails(assignment)"
                                    class="p-2 bg-pearl-50 text-charcoal-400 hover:text-gold-600 hover:bg-gold-50 rounded-lg transition-all border border-pearl-100 group-hover:border-gold-200"
                                >
                                    <i class="pi pi-eye text-xs"></i>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="assignments.data.length === 0">
                            <td colspan="8" class="px-6 py-20 text-center text-charcoal-400 italic text-sm">
                                <i class="pi pi-history text-3xl mb-3 opacity-20 block"></i>
                                Aucune trace d'affectation trouvée avec ces critères.
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination Footer -->
                <div v-if="assignments.links.length > 3" class="px-6 py-4 bg-pearl-50 border-t border-pearl-100 flex items-center justify-between">
                    <span class="text-[10px] font-black text-charcoal-400 uppercase tracking-widest">
                        Affichage de {{ assignments.from }} à {{ assignments.to }} sur {{ assignments.total }}
                    </span>
                    <div class="flex gap-1">
                        <Link v-for="(link, k) in assignments.links" :key="k" 
                            :href="link.url || '#'" 
                            v-html="link.label"
                            class="px-3 py-1.5 rounded-lg text-[10px] font-bold transition-all"
                            :class="{
                                'bg-gold-gradient text-charcoal-900 shadow-sm': link.active,
                                'bg-white border border-pearl-200 text-charcoal-500 hover:bg-pearl-100': !link.active && link.url,
                                'opacity-30 cursor-not-allowed': !link.url
                            }"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Details Modal -->
        <Dialog v-model:visible="showDetailsModal" modal header="Détails de l'Action" :style="{ width: '50rem' }" class="p-fluid">
            <template #header>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gold-gradient flex items-center justify-center text-charcoal-900 shadow-lg">
                        <i class="pi pi-info-circle text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-charcoal-800 uppercase tracking-tight">Fiche d'Audit Affectation</h3>
                        <p class="text-[10px] text-charcoal-400 font-bold uppercase tracking-widest">ID Trace: #{{ selectedAssignmentDetails?.id }}</p>
                    </div>
                </div>
            </template>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 py-4">
                <!-- Left Column: Employee & Roles -->
                <div class="space-y-6">
                    <div>
                        <label class="text-[10px] font-black text-charcoal-400 uppercase tracking-widest mb-3 block">Ressource Audité</label>
                        <div class="bg-pearl-50 rounded-2xl p-4 border border-pearl-100">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 rounded-2xl bg-white flex items-center justify-center text-charcoal-900 text-xl font-black border border-pearl-200 shadow-sm">
                                    {{ selectedAssignmentDetails?.employee?.first_name.charAt(0) }}{{ selectedAssignmentDetails?.employee?.last_name.charAt(0) }}
                                </div>
                                <div>
                                    <div class="text-base font-black text-charcoal-800">{{ selectedAssignmentDetails?.employee?.first_name }} {{ selectedAssignmentDetails?.employee?.last_name }}</div>
                                    <div class="text-xs text-gold-600 font-bold uppercase">{{ selectedAssignmentDetails?.position?.name }}</div>
                                    <div class="text-[10px] text-charcoal-400 font-bold">Matricule: {{ selectedAssignmentDetails?.employee?.matricule }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-white border border-pearl-200 rounded-xl p-3">
                            <div class="text-[9px] font-black text-charcoal-400 uppercase tracking-tighter mb-1">Date d'Effet</div>
                            <div class="text-sm font-bold text-charcoal-700">{{ formatDate(selectedAssignmentDetails?.start_date) }}</div>
                        </div>
                        <div class="bg-white border border-pearl-200 rounded-xl p-3">
                            <div class="text-[9px] font-black text-charcoal-400 uppercase tracking-tighter mb-1">Date de Fin</div>
                            <div class="text-sm font-bold text-charcoal-700">{{ formatDate(selectedAssignmentDetails?.end_date) }}</div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Campaign & Hierarchy -->
                <div class="space-y-6">
                    <div>
                        <label class="text-[10px] font-black text-charcoal-400 uppercase tracking-widest mb-3 block">Contexte d'Affectation</label>
                        <div class="bg-charcoal-900 rounded-2xl p-4 text-white shadow-xl relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-gold-500/10 rounded-full -mr-16 -mt-16 blur-3xl"></div>
                            <div class="relative z-10">
                                <div class="flex items-center gap-2 mb-4">
                                    <div class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></div>
                                    <span class="text-[10px] font-black uppercase tracking-widest text-pearl-400">Campagne Active</span>
                                </div>
                                <div class="text-lg font-black uppercase tracking-tight mb-4">{{ selectedAssignmentDetails?.campaign?.name || 'Non spécifié' }}</div>
                                
                                <div v-if="selectedAssignmentDetails?.manager" class="pt-4 border-t border-charcoal-800">
                                    <div class="text-[9px] font-black text-pearl-500 uppercase tracking-widest mb-2">Responsable Hiérarchique</div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded bg-charcoal-800 flex items-center justify-center text-[9px] font-black text-gold-400 border border-charcoal-700">
                                            {{ selectedAssignmentDetails?.manager?.first_name.charAt(0) }}
                                        </div>
                                        <span class="text-xs font-bold">{{ selectedAssignmentDetails?.manager?.first_name }} {{ selectedAssignmentDetails?.manager?.last_name }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="pi pi-exclamation-triangle text-amber-600"></i>
                            <span class="text-[10px] font-black text-amber-800 uppercase tracking-widest">Impact sur la Structure</span>
                        </div>
                        <p class="text-xs text-amber-700 font-medium italic">
                            {{ getImpactCascade(selectedAssignmentDetails) }}
                        </p>
                    </div>
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end pt-4 border-t border-pearl-100">
                    <button 
                        @click="showDetailsModal = false"
                        class="px-8 py-2.5 bg-charcoal-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-gold-600 transition-all shadow-lg"
                    >
                        Fermer
                    </button>
                </div>
            </template>
        </Dialog>
    </AdminLayout>
</template>

<style scoped>
/* Custom transitions and scrollbar styles if needed */
::-webkit-scrollbar {
    width: 6px;
}
::-webkit-scrollbar-track {
    background: #f8fafc;
}
::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
