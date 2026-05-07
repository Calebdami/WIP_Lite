<script setup>
import CpLayout from '@/Layouts/CpLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { useConfirm } from "primevue/useconfirm";

const confirm = useConfirm();

const props = defineProps({
    assignments: Object,
    stats: Object,
    filters: {
        type: Object,
        default: () => ({ search: '', status: 'all' })
    }
});

const assignmentsData = computed(() => props.assignments.data);

const search = ref((props.filters || {}).search || '');
const statusFilter = ref((props.filters || {}).status || 'all');
const selectedIds = ref([]);

// Debounce pour la recherche
let timeout;
watch([search, statusFilter], ([newSearch, newStatus]) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('cp.schedules.validation'), { 
            search: newSearch, 
            status: newStatus 
        }, {
            preserveState: true,
            replace: true
        });
    }, 300);
});

const toggleSelectAll = () => {
    if (selectedIds.value.length === assignmentsData.value.length) {
        selectedIds.value = [];
    } else {
        selectedIds.value = assignmentsData.value.map(a => a.id);
    }
};

const updateStatus = (id, status, reason = '') => {
    const isCritical = status === 'suspendu';
    
    const action = () => {
        router.patch(route('admin.assignments.validation.status', id), {
            status,
            reason
        }, {
            onSuccess: () => {
                selectedIds.value = selectedIds.value.filter(sid => sid !== id);
            }
        });
    };

    if (isCritical) {
        confirm.require({
            message: 'Êtes-vous sûr de vouloir suspendre ce planning ?',
            header: 'Confirmation de suspension',
            icon: 'pi pi-exclamation-circle',
            rejectLabel: 'Annuler',
            acceptLabel: 'Suspendre',
            rejectClass: 'p-button-secondary p-button-outlined',
            acceptClass: 'p-button-danger',
            accept: action
        });
    } else {
        action();
    }
};

const bulkUpdate = (status) => {
    if (selectedIds.value.length === 0) return;
    
    confirm.require({
        message: `Voulez-vous vraiment changer le statut de ${selectedIds.value.length} affectation(s) vers "${status}" ?`,
        header: 'Action groupée',
        icon: 'pi pi-info-circle',
        rejectLabel: 'Annuler',
        acceptLabel: 'Confirmer',
        rejectClass: 'p-button-secondary p-button-outlined',
        acceptClass: status === 'validé' ? 'p-button-success' : 'p-button-primary',
        accept: () => {
            router.patch(route('admin.assignments.validation.bulk'), {
                ids: selectedIds.value,
                status: status
            }, {
                onSuccess: () => {
                    selectedIds.value = [];
                }
            });
        }
    });
};

const formatPeriod = (start, end) => {
    const s = new Date(start).toLocaleDateString();
    const e = end ? new Date(end).toLocaleDateString() : 'Indéfinie';
    return `${s} au ${e}`;
};

const getStatusClass = (status) => {
    switch (status) {
        case 'en attente': return 'bg-yellow-100 text-yellow-700';
        case 'validé': return 'bg-green-100 text-green-700';
        case 'suspendu': return 'bg-red-100 text-red-700';
        default: return 'bg-gray-100 text-gray-700';
    }
};

const getRoleClass = (code) => {
    return code === 'SUP' ? 'bg-blue-100 text-blue-700' : 'bg-indigo-100 text-indigo-700';
};
</script>

<template>
    <Head title="Validation des Plannings — CP" />
    <CpLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Validation des Plannings</h1>
                    <p class="text-xs text-charcoal-400 mt-0.5">Consultation et validation des affectations de planning</p>
                </div>
            </div>
        </template>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl border border-pearl-200 p-6 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <div class="text-2xl font-black text-charcoal-700">{{ stats.pending }}</div>
                    <div class="text-xs font-bold text-charcoal-400 uppercase tracking-widest">En attente</div>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-pearl-200 p-6 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-green-50 text-green-500 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <div class="text-2xl font-black text-charcoal-700">{{ stats.validated }}</div>
                    <div class="text-xs font-bold text-charcoal-400 uppercase tracking-widest">Validé</div>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-pearl-200 p-6 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-red-50 text-red-500 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <div class="text-2xl font-black text-charcoal-700">{{ stats.suspended }}</div>
                    <div class="text-xs font-bold text-charcoal-400 uppercase tracking-widest">Suspendu</div>
                </div>
            </div>
        </div>

        <!-- Filters & Actions -->
        <div class="bg-white rounded-xl border border-pearl-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-pearl-100 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="relative flex-1 max-w-2xl">
                    <input 
                        v-model="search"
                        type="text" 
                        placeholder="Rechercher par employé ou modèle..."
                        class="w-full bg-pearl-50 border border-pearl-200 rounded-lg pl-10 pr-4 py-2 text-sm text-charcoal-700 focus:border-gold-400 outline-none transition-all"
                    />
                    <svg class="w-4 h-4 text-charcoal-300 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <div class="flex items-center gap-3">
                    <select 
                        v-model="statusFilter"
                        class="bg-white border border-pearl-200 rounded-lg px-4 py-2 text-xs font-bold text-charcoal-600 outline-none focus:border-gold-400 transition-all cursor-pointer"
                    >
                        <option value="all">Tous les statuts</option>
                        <option value="en attente">En attente</option>
                        <option value="validé">Validé</option>
                        <option value="suspendu">Suspendu</option>
                    </select>
                    
                    <button 
                        @click="toggleSelectAll"
                        class="px-4 py-2 bg-white border border-pearl-200 rounded-lg text-xs font-bold text-charcoal-600 hover:bg-pearl-50 transition-all active:scale-95"
                    >
                        {{ selectedIds.length === assignmentsData.length && assignmentsData.length > 0 ? 'Tout désélectionner' : 'Tout sélectionner' }}
                    </button>
                    
                    <div v-if="selectedIds.length > 0" class="flex items-center gap-2 border-l border-pearl-100 pl-3">
                        <button 
                            @click="bulkUpdate('validé')"
                            class="px-3 py-2 bg-green-600 text-white rounded-lg text-[10px] font-black uppercase tracking-wider hover:bg-green-700 transition-all shadow-sm"
                        >
                            Valider ({{ selectedIds.length }})
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-pearl-50/50 text-[10px] font-black text-charcoal-400 uppercase tracking-widest border-b border-pearl-100">
                            <th class="px-6 py-4 w-10"></th>
                            <th class="px-6 py-4">Employé</th>
                            <th class="px-6 py-4">Rôle</th>
                            <th class="px-6 py-4">Modèle</th>
                            <th class="px-6 py-4">Période</th>
                            <th class="px-6 py-4">Statut</th>
                            <th class="px-6 py-4">Validation</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-pearl-50">
                        <tr v-for="a in assignmentsData" :key="a.id" class="hover:bg-pearl-50/30 transition-colors group">
                            <td class="px-6 py-4">
                                <input 
                                    type="checkbox" 
                                    v-model="selectedIds" 
                                    :value="a.id"
                                    class="w-4 h-4 rounded border-pearl-200 text-gold-500 focus:ring-gold-400 transition-all cursor-pointer"
                                />
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-sm text-charcoal-700">{{ a.employee.first_name }} {{ a.employee.last_name }}</div>
                                <div class="text-[10px] text-charcoal-400 uppercase tracking-tighter">{{ a.employee.matricule }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span 
                                    class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-widest shadow-sm"
                                    :class="getRoleClass(a.employee.position.code)"
                                >
                                    {{ a.employee.position.code === 'SUP' ? 'Superviseur' : 'Téléconseiller' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-xs font-bold text-charcoal-700">{{ a.planning_model.name }}</div>
                                <div class="text-[10px] text-charcoal-400">{{ Math.round(a.planning_model.total_hours) }}h / semaine</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-[11px] font-medium text-charcoal-600">{{ formatPeriod(a.start_date, a.end_date) }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span 
                                    class="px-2 py-1 rounded-full text-[10px] font-bold capitalize"
                                    :class="getStatusClass(a.status)"
                                >
                                    {{ a.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div v-if="a.validator" class="text-[10px]">
                                    <div class="text-charcoal-400 font-bold uppercase tracking-tighter">
                                        {{ new Date(a.validated_at).toLocaleDateString() }}
                                    </div>
                                    <div class="text-charcoal-300 italic">Par: {{ a.validator.email.split('@')[0] }}</div>
                                </div>
                                <div v-else class="text-charcoal-200">—</div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <template v-if="a.status === 'en attente'">
                                        <button 
                                            @click="updateStatus(a.id, 'validé')"
                                            class="px-4 py-1.5 bg-blue-600 text-white rounded text-[11px] font-bold hover:bg-blue-700 transition-all active:scale-95 shadow-sm"
                                        >
                                            Valider
                                        </button>
                                        <button 
                                            @click="updateStatus(a.id, 'suspendu', 'Refus initial')"
                                            class="px-4 py-1.5 bg-red-600 text-white rounded text-[11px] font-bold hover:bg-red-700 transition-all active:scale-95 shadow-sm"
                                        >
                                            Refuser
                                        </button>
                                    </template>
                                    <template v-else-if="a.status === 'validé'">
                                        <button 
                                            @click="updateStatus(a.id, 'suspendu')"
                                            class="px-4 py-1.5 bg-white border border-pearl-200 text-charcoal-600 rounded text-[11px] font-bold hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-all active:scale-95 shadow-sm"
                                        >
                                            Suspendre
                                        </button>
                                    </template>
                                    <template v-else-if="a.status === 'suspendu'">
                                        <button 
                                            @click="updateStatus(a.id, 'en attente')"
                                            class="px-4 py-1.5 bg-blue-500 text-white rounded text-[11px] font-bold hover:bg-blue-600 transition-all active:scale-95 shadow-sm"
                                        >
                                            Réactiver
                                        </button>
                                    </template>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="assignmentsData.length === 0">
                            <td colspan="8" class="px-6 py-20 text-center">
                                <div class="text-charcoal-300 italic text-sm">Aucune affectation trouvée pour cette recherche.</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="assignments.links.length > 3" class="mt-8 flex items-center justify-center gap-1">
            <template v-for="(link, k) in assignments.links" :key="k">
                <div v-if="link.url === null" 
                     class="px-4 py-2 text-xs font-bold text-charcoal-300 cursor-not-allowed"
                     v-html="link.label" />
                <Link v-else
                      :href="link.url"
                      class="px-4 py-2 text-xs font-bold rounded-lg transition-all"
                      :class="link.active 
                        ? 'bg-gold-gradient text-white shadow-gold' 
                        : 'bg-white border border-pearl-200 text-charcoal-500 hover:bg-pearl-50'"
                      v-html="link.label" />
            </template>
        </div>
    </CpLayout>
</template>

<style scoped>
.bg-gold-gradient {
    background: linear-gradient(135deg, #D4A017 0%, #8B6914 100%);
}
.shadow-gold {
    box-shadow: 0 4px 15px -3px rgba(212, 160, 23, 0.4);
}
</style>
