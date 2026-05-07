<script setup>
import CpLayout from '@/Layouts/CpLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';

const validatedTimesheets = ref([]);
const loading = ref(false);

const fetchMyHours = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/timesheets/my/hours');
        validatedTimesheets.value = response.data;
    } catch (error) {
        console.error('Erreur lors du chargement des heures');
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchMyHours();
});

// Détails
const displayDetailsDialog = ref(false);
const selectedTimesheet = ref(null);

const openDetails = (ts) => {
    selectedTimesheet.value = ts;
    displayDetailsDialog.value = true;
};
</script>

<template>
    <Head title="Mes Heures Validées — CP" />
    <CpLayout>
        <template #header>
            <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Mes Heures Validées</h1>
            <p class="text-xs text-charcoal-400 mt-0.5">Historique de mes temps de travail validés par l'administration</p>
        </template>

        <div class="bg-white rounded-xl border border-pearl-200 shadow-sm p-4">
            <DataTable :value="validatedTimesheets" :loading="loading" stripedRows responsiveLayout="scroll" class="p-datatable-sm">
                <template #empty>
                    <div class="text-center p-4 text-charcoal-400">Aucun historique d'heures validées.</div>
                </template>
                <Column field="period_start" header="Début" sortable></Column>
                <Column field="period_end" header="Fin" sortable></Column>
                <Column header="Total Heures">
                    <template #body="{ data }">
                        <span class="font-bold">{{ data.total_hours }} h</span>
                    </template>
                </Column>
                <Column header="Écart">
                    <template #body="{ data }">
                        <Tag v-if="data.deviation > 0" :value="`+${data.deviation}h`" severity="warning" />
                        <Tag v-else-if="data.deviation < 0" :value="`${data.deviation}h`" severity="danger" />
                        <span v-else class="text-charcoal-400 text-xs">Conforme</span>
                    </template>
                </Column>
                <Column header="Date Validation">
                    <template #body="{ data }">
                        <span class="text-xs">{{ data.validated_at || 'N/A' }}</span>
                    </template>
                </Column>
                <Column header="Détails">
                    <template #body="{ data }">
                        <Button icon="pi pi-eye" class="p-button-text p-button-sm p-button-secondary" @click="openDetails(data)" />
                    </template>
                </Column>
            </DataTable>
        </div>

        <Dialog v-model:visible="displayDetailsDialog" header="Détails de la période" :style="{ width: '700px' }" modal>
            <div v-if="selectedTimesheet">
                <DataTable :value="selectedTimesheet.entries" class="p-datatable-sm">
                    <Column field="date" header="Date"></Column>
                    <Column header="Horaires">
                        <template #body="{ data }">
                            <span v-if="data.check_in">{{ data.check_in }} - {{ data.check_out }}</span>
                            <span v-else-if="data.absence_type" class="text-amber-600 italic">{{ data.absence_type }}</span>
                            <span v-else class="text-charcoal-300">-</span>
                        </template>
                    </Column>
                    <Column field="total_hours" header="Total"></Column>
                    <Column field="comment" header="Commentaire"></Column>
                </DataTable>
            </div>
        </Dialog>
    </CpLayout>
</template>
