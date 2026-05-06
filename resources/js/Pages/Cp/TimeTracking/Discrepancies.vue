<script setup>
import CpLayout from '@/Layouts/CpLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';

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
        // Fetch all timesheets and filter in frontend for the demo, 
        // ideally an API endpoint ?deviation=true would be better
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
    return timesheets.value.reduce((acc, ts) => acc + (ts.total_overtime_hours || 0), 0).toFixed(1);
});

const totalAbsenceDeviation = computed(() => {
    const negativeDeviations = timesheets.value.filter(ts => ts.hours_deviation < 0);
    return negativeDeviations.reduce((acc, ts) => acc + Math.abs(ts.hours_deviation), 0).toFixed(1);
});

const severeDiscrepanciesCount = computed(() => {
    return timesheets.value.filter(ts => Math.abs(ts.hours_deviation) >= 5).length;
});
</script>

<template>
    <Head title="Analyse des Écarts — CP" />
    <CpLayout>
        <template #header>
            <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Analyse des Écarts</h1>
            <p class="text-xs text-charcoal-400 mt-0.5">Rapports de productivité et d'assiduité</p>
        </template>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <Card class="shadow-sm border border-pearl-200">
                <template #title>
                    <div class="text-sm text-charcoal-400">Heures Supplémentaires (Global)</div>
                </template>
                <template #content>
                    <div class="text-3xl font-black text-amber-600">+{{ totalOvertime }} h</div>
                </template>
            </Card>

            <Card class="shadow-sm border border-pearl-200">
                <template #title>
                    <div class="text-sm text-charcoal-400">Déficit d'heures / Absences</div>
                </template>
                <template #content>
                    <div class="text-3xl font-black text-red-600">-{{ totalAbsenceDeviation }} h</div>
                </template>
            </Card>

            <Card class="shadow-sm border border-pearl-200">
                <template #title>
                    <div class="text-sm text-charcoal-400">Écarts Sévères (> 5h)</div>
                </template>
                <template #content>
                    <div class="text-3xl font-black text-charcoal-700">{{ severeDiscrepanciesCount }}</div>
                </template>
            </Card>
        </div>

        <div class="bg-white rounded-xl border border-pearl-200 shadow-sm p-4">
            <h2 class="text-lg font-bold text-charcoal-700 mb-4 px-2">Liste des écarts identifiés</h2>
            
            <DataTable :value="timesheets" :loading="loading" stripedRows responsiveLayout="scroll" class="p-datatable-sm" paginator :rows="10">
                <template #empty>
                    <div class="text-center p-4 text-charcoal-400">Aucun écart significatif détecté.</div>
                </template>
                
                <Column header="Employé" sortable>
                    <template #body="{ data }">
                        <span class="font-bold text-charcoal-700">{{ data.employee?.first_name }} {{ data.employee?.last_name }}</span>
                    </template>
                </Column>
                <Column header="Période" sortable>
                    <template #body="{ data }">
                        {{ data.period_start }} au {{ data.period_end }}
                    </template>
                </Column>
                <Column header="Prévu" sortable>
                    <template #body="{ data }">
                        <span class="text-charcoal-400">{{ data.total_planned_hours }} h</span>
                    </template>
                </Column>
                <Column header="Réalisé" sortable>
                    <template #body="{ data }">
                        <span class="font-medium text-charcoal-700">{{ data.total_hours }} h</span>
                    </template>
                </Column>
                <Column header="Écart" sortable>
                    <template #body="{ data }">
                        <Tag v-if="data.hours_deviation > 0" :value="`+${data.hours_deviation}h`" severity="warning" />
                        <Tag v-else-if="data.hours_deviation < 0" :value="`${data.hours_deviation}h`" severity="danger" />
                    </template>
                </Column>
                <Column field="status" header="Statut" sortable>
                    <template #body="{ data }">
                        <Tag :value="data.status.toUpperCase()" :severity="data.status === 'validé' ? 'success' : 'info'" />
                    </template>
                </Column>
            </DataTable>
        </div>
    </CpLayout>
</template>
