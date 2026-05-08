<script setup>
import SupLayout from '@/Layouts/SupLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';
import { formatHours, formatHoursShort } from '@/Utils/formatHours';

// PrimeVue components
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Card from 'primevue/card';

const toast = useToast();

const timesheets = ref([]);
const loading = ref(false);

const fetchDiscrepancies = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/timesheets');
        // Filter only those with absolute deviation > 0
        timesheets.value = response.data.filter(ts => Math.abs(ts.hours_deviation) > 0);
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de charger les écarts', life: 3000 });
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchDiscrepancies();
});

const totalOvertime = computed(() => {
    return Math.round(timesheets.value.reduce((acc, ts) => acc + (parseFloat(ts.total_overtime_hours) || 0), 0));
});

const totalAbsenceDeviation = computed(() => {
    const negativeDeviations = timesheets.value.filter(ts => ts.hours_deviation < 0);
    return Math.round(negativeDeviations.reduce((acc, ts) => acc + Math.abs(ts.hours_deviation), 0));
});

const severeDiscrepanciesCount = computed(() => {
    return timesheets.value.filter(ts => Math.abs(ts.hours_deviation) >= 5).length;
});

const formatDateDisplay = (dateStr) => {
    if (!dateStr) return '';
    const datePart = dateStr.includes('T') ? dateStr.split('T')[0] : (dateStr.includes(' ') ? dateStr.split(' ')[0] : dateStr);
    const parts = datePart.split('-');
    if (parts.length !== 3) return dateStr;
    const [year, month, day] = parts;
    return `${day}/${month}/${year}`;
};

const getStatusSeverity = (status) => {
    switch (status) {
        case 'brouillon': return 'secondary';
        case 'soumis': return 'info';
        case 'valide': return 'success';
        case 'rejete': return 'danger';
        default: return 'info';
    }
};
</script>

<template>
    <Head title="Analyse des Écarts — Superviseur" />
    <SupLayout>
        <template #header>
            <div class="flex justify-between items-end">
                <div>
                    <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Analyse des Écarts</h1>
                    <p class="text-xs text-charcoal-400 mt-0.5">Suivi de la productivité et de l'assiduité de votre équipe</p>
                </div>
            </div>
        </template>

        <!-- KPI Cards Premium -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-2xl border border-pearl-200 p-6 shadow-premium hover:shadow-gold-premium transition-all duration-300 group overflow-hidden relative">
                <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-gold-50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="flex items-start justify-between relative z-10">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-charcoal-400 mb-2">Heures Supplémentaires</p>
                        <h3 class="text-3xl font-black text-amber-600 tracking-tighter">+{{ totalOvertime }}h</h3>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600 border border-amber-100">
                        <i class="pi pi-clock text-lg"></i>
                    </div>
                </div>
                <p class="text-[10px] text-charcoal-400 mt-4 font-medium uppercase tracking-tight italic">Volume global hebdomadaire</p>
            </div>

            <div class="bg-white rounded-2xl border border-pearl-200 p-6 shadow-premium hover:shadow-premium transition-all duration-300 group overflow-hidden relative">
                <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-red-50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="flex items-start justify-between relative z-10">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-charcoal-400 mb-2">Sous-productivité / Absences</p>
                        <h3 class="text-3xl font-black text-red-600 tracking-tighter">-{{ totalAbsenceDeviation }}h</h3>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center text-red-600 border border-red-100">
                        <i class="pi pi-user-minus text-lg"></i>
                    </div>
                </div>
                <p class="text-[10px] text-charcoal-400 mt-4 font-medium uppercase tracking-tight italic">Total des déficits horaires</p>
            </div>

            <div class="bg-white rounded-2xl border border-pearl-200 p-6 shadow-premium hover:shadow-premium transition-all duration-300 group overflow-hidden relative">
                <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-charcoal-50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="flex items-start justify-between relative z-10">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-charcoal-400 mb-2">Anomalies Critiques</p>
                        <h3 class="text-3xl font-black text-charcoal-700 tracking-tighter">{{ severeDiscrepanciesCount }}</h3>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-charcoal-700 flex items-center justify-center text-white border border-charcoal-800 shadow-md">
                        <i class="pi pi-exclamation-triangle text-lg"></i>
                    </div>
                </div>
                <p class="text-[10px] text-charcoal-400 mt-4 font-medium uppercase tracking-tight italic">Écarts supérieurs à 5 heures</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-pearl-200 shadow-premium p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-8 h-8 rounded-full bg-gold-500 flex items-center justify-center text-white shadow-gold-premium">
                    <i class="pi pi-list text-xs"></i>
                </div>
                <h2 class="text-lg font-black text-charcoal-700 tracking-tight uppercase">Registre des Écarts Détectés</h2>
            </div>
            
            <DataTable :value="timesheets" :loading="loading" stripedRows responsiveLayout="scroll" 
                class="p-datatable-sm custom-discrepancies-table" paginator :rows="10">
                <template #empty>
                    <div class="text-center py-12 text-charcoal-300 flex flex-col items-center gap-4">
                        <div class="w-16 h-16 rounded-full bg-pearl-50 flex items-center justify-center">
                            <i class="pi pi-check-circle text-3xl"></i>
                        </div>
                        <p class="font-bold tracking-wide uppercase text-[10px]">Parfait ! Aucun écart significatif détecté.</p>
                    </div>
                </template>
                
                <Column header="Employé" sortable>
                    <template #body="{ data }">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-pearl-100 flex items-center justify-center text-charcoal-700 font-bold text-xs border border-pearl-200">
                                {{ data.employee?.first_name?.charAt(0) }}{{ data.employee?.last_name?.charAt(0) }}
                            </div>
                            <span class="font-bold text-charcoal-700">{{ data.employee?.first_name }} {{ data.employee?.last_name }}</span>
                        </div>
                    </template>
                </Column>
                <Column header="Période" sortable>
                    <template #body="{ data }">
                         <div class="flex items-center gap-2 text-charcoal-600">
                            <i class="pi pi-calendar text-xs text-gold-500"></i>
                            <span class="text-xs font-bold">{{ formatDateDisplay(data.period_start) }} au {{ formatDateDisplay(data.period_end) }}</span>
                        </div>
                    </template>
                </Column>
                <Column header="Prévu" sortable>
                    <template #body="{ data }">
                        <span class="text-xs font-black text-charcoal-400 italic">{{ formatHours(data.total_planned_hours) }}</span>
                    </template>
                </Column>
                <Column header="Réalisé" sortable>
                    <template #body="{ data }">
                        <span class="text-xs font-black text-charcoal-700">{{ formatHours(data.total_hours) }}</span>
                    </template>
                </Column>
                <Column header="Écart Analysé" sortable>
                    <template #body="{ data }">
                        <div class="flex items-center gap-2">
                            <Tag v-if="data.hours_deviation > 0" :value="`+${Math.round(data.hours_deviation)}h`" severity="warn" />
                            <Tag v-else-if="data.hours_deviation < 0" :value="`${Math.round(data.hours_deviation)}h`" severity="danger" />
                            
                            <i v-if="Math.abs(data.hours_deviation) >= 5" class="pi pi-exclamation-circle text-red-500 animate-pulse" title="Écart Sévère"></i>
                        </div>
                    </template>
                </Column>
                <Column field="status" header="Statut" sortable>
                    <template #body="{ data }">
                        <Tag :value="data.status.toUpperCase()" :severity="getStatusSeverity(data.status)" />
                    </template>
                </Column>
            </DataTable>
        </div>
    </SupLayout>
</template>

<style scoped>
:deep(.custom-discrepancies-table .p-datatable-thead > tr > th) {
    background: #fcfcfc;
    text-transform: uppercase;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: 0.1em;
    color: #94a3b8;
    padding: 1.25rem 1rem;
    border-bottom: 2px solid #f1f5f9;
}
</style>
