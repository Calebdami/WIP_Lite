<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import Dialog from 'primevue/dialog';
import Tag from 'primevue/tag';

// Components simplifiés

const props = defineProps({
    history: Object,
    stats: Object,
    planningModels: Array,
    filters: {
        type: Object,
        default: () => ({ search: '', status: 'all', model_id: 'all' })
    }
});

const page = usePage();

// Custom debounce function
const debounce = (fn, delay) => {
    let timeoutId;
    return (...args) => {
        if (timeoutId) clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
            fn(...args);
        }, delay);
    };
};

// Search & Filtering
const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || 'all');
const model_id = ref(props.filters?.model_id || 'all');

// Modal state
const detailVisible = ref(false);
const selectedHistory = ref(null);

// Manual search functions
const triggerSearch = () => {
    router.get(route('admin.assignments.planning-history'), {
        search: search.value,
        status: status.value,
        model_id: model_id.value
    }, {
        preserveState: true,
        preserveScroll: true
    });
};

// Afficher les détails
const showDetails = (historyItem) => {
    selectedHistory.value = historyItem;
    detailVisible.value = true;
};

const getStatusSeverity = (status) => {
    if (!status) return 'secondary';
    switch (status.toLowerCase()) {
        case 'en attente': return 'warn';
        case 'validé': return 'success';
        case 'suspendu': return 'danger';
        case 'actif': return 'success';
        case 'clôturé': return 'info';
        default: return 'secondary';
    }
};

const getStatusClass = (status) => {
    if (!status) return 'bg-gray-100 text-gray-600';
    switch (status.toLowerCase()) {
        case 'en attente': return 'bg-yellow-100 text-yellow-700';
        case 'validé': return 'bg-green-100 text-green-700';
        case 'suspendu': return 'bg-red-100 text-red-700';
        case 'actif': return 'bg-green-100 text-green-700';
        case 'clôturé': return 'bg-blue-100 text-blue-700';
        default: return 'bg-gray-100 text-gray-600';
    }
};

const formatStatus = (status) => {
    if (!status) return 'Initial';
    return status.charAt(0).toUpperCase() + status.slice(1);
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};

const formatTime = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleTimeString('fr-FR', {
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Historique des Plannings — Admin" />
    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Historique des Plannings</h1>
                    <p class="text-xs text-charcoal-400 mt-0.5">Suivi chronologique des changements d'états et affectations</p>
                </div>
            </div>
        </template>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div v-for="stat in [
                { label: 'Total Événements', value: stats?.total || 0, icon: 'pi-history', color: 'charcoal' },
                { label: 'Créations', value: stats?.creations || 0, icon: 'pi-plus-circle', color: 'blue' },
                { label: 'Validations', value: stats?.validations || 0, icon: 'pi-check-circle', color: 'emerald' },
                { label: 'Suspensions', value: stats?.suspensions || 0, icon: 'pi-exclamation-circle', color: 'red' }
            ]" :key="stat.label" 
            class="bg-white rounded-3xl border border-pearl-100 p-6 shadow-premium flex items-center gap-4 hover:shadow-gold-premium transition-all duration-300">
                <div :class="`w-12 h-12 rounded-2xl bg-${stat.color}-50 text-${stat.color}-500 flex items-center justify-center`">
                    <i :class="`pi ${stat.icon} text-lg`"></i>
                </div>
                <div>
                    <div class="text-2xl font-black text-charcoal-700">{{ stat.value }}</div>
                    <div class="text-[10px] font-bold text-charcoal-400 uppercase tracking-widest">{{ stat.label }}</div>
                </div>
            </div>
        </div>

        <!-- Explanatory Note -->
        <div class="bg-pearl-50 border border-pearl-200 rounded-xl p-4 mb-8 flex items-start gap-4">
            <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-pearl-400 shadow-sm flex-shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="text-[11px] leading-relaxed text-charcoal-500 italic">
                <span class="font-bold text-charcoal-700 not-italic block mb-1">À propos de la table d'historique :</span>
                Chaque action effectuée sur une affectation de planning (création, validation, suspension, réactivation) est archivée dans cette table. Cela permet de garantir une traçabilité totale des changements d'état et des interventions des administrateurs ou chefs de plateau sur les ressources de l'entreprise.
            </div>
        </div>

        <!-- Content -->
        <div class="bg-white rounded-xl border border-pearl-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-pearl-100 bg-white flex flex-col md:flex-row md:items-center gap-4">
                <div class="relative flex-1 max-w-md">
                    <input 
                        v-model="search"
                        type="text" 
                        placeholder="Rechercher un employé ou une raison..."
                        @keydown.enter="triggerSearch"
                        class="w-full bg-pearl-50 border border-pearl-200 rounded-lg pl-10 pr-4 py-2 text-sm text-charcoal-700 focus:border-gold-400 outline-none transition-all"
                    />
                    <svg class="w-4 h-4 text-charcoal-300 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>

                <div class="flex items-center gap-3">
                    <select 
                        v-model="status"
                        class="bg-pearl-50 border border-pearl-200 rounded-lg px-4 py-2 text-xs font-bold text-charcoal-600 focus:border-gold-400 outline-none transition-all"
                    >
                        <option value="all">Tous les statuts</option>
                        <option value="en attente">En attente</option>
                        <option value="validé">Validé</option>
                        <option value="suspendu">Suspendu</option>
                    </select>

                    <select 
                        v-model="model_id"
                        class="bg-pearl-50 border border-pearl-200 rounded-lg px-4 py-2 text-xs font-bold text-charcoal-600 focus:border-gold-400 outline-none transition-all max-w-[200px]"
                    >
                        <option value="all">Tous les modèles</option>
                        <option v-for="m in planningModels" :key="m.id" :value="m.id">{{ m.name }}</option>
                    </select>

                    <button 
                        @click="triggerSearch"
                        class="bg-gold-gradient text-white px-6 py-2 rounded-lg text-xs font-black uppercase tracking-widest hover:opacity-90 transition-all shadow-md"
                    >
                        <i class="pi pi-filter mr-2"></i>
                        Filtrer
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-pearl-50/50 text-[10px] font-black text-charcoal-400 uppercase tracking-widest border-b border-pearl-100">
                            <th class="px-6 py-4">Employé</th>
                            <th class="px-6 py-4">Planning</th>
                            <th class="px-6 py-4">Transition Statut</th>
                            <th class="px-6 py-4">Par</th>
                            <th class="px-6 py-4">Raison</th>
                            <th class="px-6 py-4 text-right">Date & Heure</th>
                            <th class="px-6 py-4 text-center font-black text-charcoal-700 uppercase text-[10px] tracking-widest">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-pearl-50">
                        <tr v-for="item in history.data" :key="item.id" class="hover:bg-pearl-50 transition-all duration-200">
                            <td class="py-4 px-6">
                                <div v-if="item.assignment?.employee" class="flex flex-col">
                                    <span class="text-sm font-bold text-charcoal-700 uppercase">
                                        {{ item.assignment.employee.first_name }} {{ item.assignment.employee.last_name }}
                                    </span>
                                    <span class="text-[10px] font-black text-gold-500 tracking-tighter">
                                        {{ item.assignment.employee.matricule }}
                                    </span>
                                </div>
                                <span v-else class="text-charcoal-400 italic text-sm">Inconnu</span>
                            </td>
                            <td class="py-4 px-6">
                                <div v-if="item.assignment?.planning_model" class="text-sm font-bold text-charcoal-600">
                                    {{ item.assignment.planning_model.name }}
                                </div>
                                <span v-else class="text-charcoal-400">—</span>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-2">
                                    <span class="px-3 py-1 text-[10px] font-bold rounded-xl" :class="getStatusClass(item.old_status)">
                                        {{ formatStatus(item.old_status) }}
                                    </span>
                                    <i class="pi pi-arrow-right text-xs text-charcoal-400"></i>
                                    <span class="px-3 py-1 text-[10px] font-bold rounded-xl" :class="getStatusClass(item.new_status)">
                                        {{ formatStatus(item.new_status) }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <div v-if="item.author" class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-pearl-50 flex items-center justify-center text-xs font-black text-gold-500 border border-pearl-200 uppercase">
                                        {{ item.author.email.charAt(0) }}
                                    </div>
                                    <div class="text-sm font-bold text-charcoal-600 truncate max-w-[120px]">
                                        {{ item.author.email.split('@')[0] }}
                                    </div>
                                </div>
                                <span v-else class="text-charcoal-400 italic text-sm">Système</span>
                            </td>
                            <td class="py-4 px-6">
                                <div class="text-xs text-charcoal-500 italic max-w-xs truncate" :title="item.reason">
                                    {{ item.reason || 'Aucune raison spécifiée' }}
                                </div>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="text-sm font-bold text-charcoal-700">{{ formatDate(item.created_at) }}</div>
                                <div class="text-xs text-charcoal-400 font-medium">{{ formatTime(item.created_at) }}</div>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <button 
                                    @click="showDetails(item)"
                                    class="px-4 py-2 bg-pearl-50 text-charcoal-600 text-xs font-black uppercase tracking-widest rounded-xl hover:bg-gold-gradient hover:text-white transition-all duration-200 border border-pearl-200"
                                >
                                    <i class="pi pi-eye mr-2"></i>
                                    Détail
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div v-if="!history.data || history.data.length === 0" class="text-center py-16 text-charcoal-400">
                <i class="pi pi-inbox text-6xl mb-6 opacity-20"></i>
                <p class="text-lg italic">Aucun historique trouvé.</p>
            </div>

            <!-- Pagination -->
            <div v-if="history.links.length > 3" class="py-6 border-t border-pearl-100 flex justify-center">
                <div class="flex gap-3">
                    <Link 
                        v-for="(link, k) in history.links" 
                        :key="k"
                        :href="link.url || '#'"
                        v-html="link.label"
                        class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all duration-200 border"
                        :class="{
                            'bg-gold-gradient text-charcoal-900 border-transparent shadow-gold-premium': link.active,
                            'bg-white text-charcoal-500 border-pearl-200 hover:border-gold-500 hover:text-charcoal-900': !link.active && link.url,
                            'opacity-40 cursor-not-allowed border-pearl-100 text-charcoal-300': !link.url
                        }"
                    />
                </div>
            </div>
        </div>

        <!-- Explanatory Note -->
        <div class="mt-8 bg-pearl-50 border border-pearl-100 rounded-3xl p-6 flex items-start gap-4">
            <div class="w-12 h-12 rounded-full bg-white flex items-center justify-center text-gold-500 shadow-premium flex-shrink-0">
                <i class="pi pi-info-circle text-xl"></i>
            </div>
            <div class="text-sm leading-relaxed text-charcoal-500 italic">
                <span class="font-bold text-charcoal-700 not-italic block mb-2 uppercase tracking-widest text-xs">Traçabilité des plannings :</span>
                Chaque action effectuée sur une affectation de planning (création, validation, suspension, réactivation) est archivée dans cette table. Cela permet de garantir une traçabilité totale des changements d'état et des interventions des administrateurs ou chefs de plateau sur les ressources de l'entreprise.
            </div>
        </div>

        <!-- Détails de l'Historique Modal -->
        <Dialog v-model:visible="detailVisible" modal :style="{ width: '50vw' }" class="p-0 overflow-hidden border-none" :showHeader="false">
            <div v-if="selectedHistory" class="bg-white rounded-3xl overflow-hidden">
                <!-- Custom Header -->
                <div class="bg-charcoal-900 px-8 py-6 flex items-center justify-between border-b border-charcoal-800">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-gold-gradient flex items-center justify-center text-white shadow-gold-premium">
                            <i class="pi pi-history text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-white tracking-tight leading-none uppercase">Détails de l'événement</h3>
                            <p class="text-[10px] text-gold-500 font-black uppercase tracking-[0.2em] mt-1.5 opacity-80">Audit Trail #{{ selectedHistory.id }}</p>
                        </div>
                    </div>
                    <button @click="detailVisible = false" class="w-10 h-10 rounded-xl bg-charcoal-800 text-charcoal-400 hover:text-white hover:bg-charcoal-700 transition-premium flex items-center justify-center">
                        <i class="pi pi-times"></i>
                    </button>
                </div>

                <div class="p-8 space-y-8">
                    <!-- Section Employé -->
                    <div class="bg-pearl-50/50 p-6 rounded-[2rem] border border-pearl-100 flex items-center gap-6 shadow-sm">
                        <div class="w-20 h-20 rounded-[1.5rem] bg-white border border-pearl-100 flex items-center justify-center text-3xl font-black text-charcoal-900 shadow-premium">
                            {{ selectedHistory.assignment?.employee?.first_name?.charAt(0) }}{{ selectedHistory.assignment?.employee?.last_name?.charAt(0) }}
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xl font-black text-charcoal-900 uppercase tracking-tight">
                                    {{ selectedHistory.assignment?.employee?.first_name }} {{ selectedHistory.assignment?.employee?.last_name }}
                                </h4>
                                <span class="px-4 py-1.5 bg-gold-500 text-charcoal-900 text-[10px] font-black uppercase tracking-widest rounded-xl shadow-gold-sm">
                                    {{ selectedHistory.assignment?.employee?.matricule }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2 mt-2">
                                <i class="pi pi-calendar text-gold-600 text-xs"></i>
                                <span class="text-sm font-bold text-charcoal-400">
                                    Modèle : {{ selectedHistory.assignment?.planning_model?.name || 'Standard' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <!-- Transition -->
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-charcoal-400 uppercase tracking-widest block ml-1">Flux d'état</label>
                            <div class="flex items-center gap-4 bg-white p-5 rounded-2xl border border-pearl-100 shadow-sm h-16">
                                <Tag :value="formatStatus(selectedHistory.old_status)" :severity="getStatusSeverity(selectedHistory.old_status)" class="font-black px-4 py-2" />
                                <i class="pi pi-arrow-right text-xs text-charcoal-300"></i>
                                <Tag :value="formatStatus(selectedHistory.new_status)" :severity="getStatusSeverity(selectedHistory.new_status)" class="font-black px-4 py-2" />
                            </div>
                        </div>

                        <!-- Date -->
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-charcoal-400 uppercase tracking-widest block ml-1">Horodatage</label>
                            <div class="flex items-center gap-4 bg-white p-5 rounded-2xl border border-pearl-100 shadow-sm h-16">
                                <div class="w-10 h-10 rounded-xl bg-pearl-50 flex items-center justify-center text-gold-600">
                                    <i class="pi pi-clock"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-black text-charcoal-900">{{ formatDate(selectedHistory.created_at) }}</div>
                                    <div class="text-[10px] font-bold text-charcoal-400 uppercase tracking-tighter">à {{ formatTime(selectedHistory.created_at) }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Auteur -->
                        <div class="col-span-2 space-y-3">
                            <label class="text-[10px] font-black text-charcoal-400 uppercase tracking-widest block ml-1">Effectué par</label>
                            <div class="flex items-center gap-4 bg-white p-5 rounded-2xl border border-pearl-100 shadow-sm">
                                <div class="w-10 h-10 rounded-xl bg-charcoal-900 flex items-center justify-center text-[10px] font-black text-gold-500 uppercase">
                                    {{ selectedHistory.author?.email?.charAt(0) || 'S' }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-black text-charcoal-900">{{ selectedHistory.author?.email || 'Système automatique' }}</span>
                                    <span class="text-[10px] font-bold text-charcoal-400 uppercase tracking-widest">Administrateur / CP</span>
                                </div>
                            </div>
                        </div>

                        <!-- Raison -->
                        <div class="col-span-2 space-y-3">
                            <label class="text-[10px] font-black text-charcoal-400 uppercase tracking-widest block ml-1">Motif du changement</label>
                            <div class="bg-pearl-50 p-6 rounded-3xl border border-pearl-100 italic text-charcoal-600 text-sm leading-relaxed relative">
                                <i class="pi pi-quote-left absolute -top-2 -left-2 text-gold-500/20 text-4xl"></i>
                                "{{ selectedHistory.reason || 'Aucune raison spécifiée pour cette action.' }}"
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-pearl-50/50 px-8 py-6 border-t border-pearl-100 flex justify-end">
                    <button @click="detailVisible = false" 
                        class="px-10 py-3.5 bg-charcoal-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] hover:bg-charcoal-800 transition-premium shadow-premium">
                        Fermer le rapport
                    </button>
                </div>
            </div>
        </Dialog>
    </AdminLayout>
</template>

<style scoped>
.bg-gold-gradient {
    background: linear-gradient(135deg, #D4A017 0%, #8B6914 100%);
}

.shadow-gold-sm {
    box-shadow: 0 4px 10px -5px rgba(212, 160, 23, 0.5);
}

.shadow-premium {
    box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05);
}

/* Colors for stats - manually adding classes since dynamic tailwind classes might be purged */
.bg-charcoal-50 { background-color: #f8fafc; }
.text-charcoal-500 { color: #64748b; }
.bg-blue-50 { background-color: #eff6ff; }
.text-blue-500 { color: #3b82f6; }
.bg-emerald-50 { background-color: #ecfdf5; }
.text-emerald-500 { color: #10b981; }
.bg-red-50 { background-color: #fef2f2; }
.text-red-500 { color: #ef4444; }
</style>
