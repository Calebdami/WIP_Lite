<script setup>
import CpLayout from '@/Layouts/CpLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';

// PrimeVue components
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import Tag from 'primevue/tag';
import Message from 'primevue/message';

const toast = useToast();
const confirm = useConfirm();

const submittedTimesheets = ref([]);
const selectedTimesheets = ref([]);
const loading = ref(false);

const fetchSubmittedTimesheets = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/timesheets?status=soumis');
        submittedTimesheets.value = response.data;
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de charger les feuilles soumises', life: 3000 });
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchSubmittedTimesheets();
});

const validateBatch = async () => {
    if (!selectedTimesheets.value.length) return;
    const ids = selectedTimesheets.value.map(ts => ts.id);
    
    try {
        await axios.post('/api/timesheets/validate-batch', { timesheet_ids: ids });
        toast.add({ severity: 'success', summary: 'Validé', detail: `${ids.length} feuilles validées`, life: 3000 });
        selectedTimesheets.value = [];
        fetchSubmittedTimesheets();
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Erreur', detail: 'Erreur lors de la validation par lot', life: 3000 });
    }
};

const validateSingle = async (id) => {
    try {
        await axios.post(`/api/timesheets/${id}/validate`);
        toast.add({ severity: 'success', summary: 'Validé', detail: 'Feuille validée', life: 3000 });
        fetchSubmittedTimesheets();
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Erreur', detail: 'Erreur lors de la validation', life: 3000 });
    }
};

// Rejet
const displayRejectDialog = ref(false);
const rejectingTimesheet = ref(null);
const rejectReason = ref('');

const openRejectDialog = (ts) => {
    rejectingTimesheet.value = ts;
    rejectReason.value = '';
    displayRejectDialog.value = true;
};

const rejectTimesheet = async () => {
    if (!rejectReason.value.trim()) return;
    
    try {
        await axios.post(`/api/timesheets/${rejectingTimesheet.value.id}/reject`, {
            reason: rejectReason.value
        });
        toast.add({ severity: 'success', summary: 'Rejeté', detail: 'La feuille a été rejetée', life: 3000 });
        displayRejectDialog.value = false;
        fetchSubmittedTimesheets();
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Erreur', detail: 'Erreur lors du rejet', life: 3000 });
    }
};

// Détails des entrées et anomalies
const displayDetailsDialog = ref(false);
const currentTimesheetDetails = ref(null);
const loadingDetails = ref(false);

const openDetails = async (ts) => {
    loadingDetails.value = true;
    displayDetailsDialog.value = true;
    try {
        const response = await axios.get(`/api/timesheets/${ts.id}/entries`);
        currentTimesheetDetails.value = response.data;
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de charger les détails', life: 3000 });
        displayDetailsDialog.value = false;
    } finally {
        loadingDetails.value = false;
    }
};
</script>

<template>
    <Head title="Validation Heures — CP" />
    <CpLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Validation des Heures</h1>
                    <p class="text-xs text-charcoal-400 mt-0.5">Validation finale des feuilles de temps soumises</p>
                </div>
                <Button label="Valider la sélection" icon="pi pi-check-circle" class="p-button-sm p-button-success" 
                    @click="validateBatch" :disabled="!selectedTimesheets.length" />
            </div>
        </template>

        <div class="bg-white rounded-xl border border-pearl-200 shadow-sm p-4">
            <DataTable :value="submittedTimesheets" v-model:selection="selectedTimesheets" :loading="loading" dataKey="id" stripedRows responsiveLayout="scroll" class="p-datatable-sm">
                <template #empty>
                    <div class="text-center p-4 text-charcoal-400">Aucune feuille en attente de validation.</div>
                </template>
                <Column selectionMode="multiple" headerStyle="width: 3rem"></Column>
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
                <Column header="Heures" sortable>
                    <template #body="{ data }">
                        <span :class="{'text-amber-600 font-bold': data.hours_deviation > 2 || data.hours_deviation < -2}">
                            {{ data.total_hours }} h
                        </span>
                        <span class="text-xs text-charcoal-400 block">Prévu: {{ data.total_planned_hours }} h</span>
                    </template>
                </Column>
                <Column header="Écart" sortable>
                    <template #body="{ data }">
                        <Tag v-if="data.hours_deviation > 0" :value="`+${data.hours_deviation}h`" severity="warning" />
                        <Tag v-else-if="data.hours_deviation < 0" :value="`${data.hours_deviation}h`" severity="danger" />
                        <span v-else class="text-charcoal-400 text-sm">Conforme</span>
                    </template>
                </Column>
                <Column header="Actions">
                    <template #body="{ data }">
                        <div class="flex gap-2">
                            <Button icon="pi pi-eye" class="bg-pearl-100 hover:bg-pearl-200 text-charcoal-700 w-8 h-8 rounded-full flex items-center justify-center border border-pearl-300 transition-colors" 
                                @click="openDetails(data)" title="Voir les détails" />
                            <Button icon="pi pi-check" class="bg-emerald-100 hover:bg-emerald-200 text-emerald-700 w-8 h-8 rounded-full flex items-center justify-center border border-emerald-300 transition-colors" 
                                @click="validateSingle(data.id)" title="Valider" />
                            <Button icon="pi pi-times" class="bg-red-100 hover:bg-red-200 text-red-700 w-8 h-8 rounded-full flex items-center justify-center border border-red-300 transition-colors" 
                                @click="openRejectDialog(data)" title="Rejeter" />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- Modale de Rejet -->
        <Dialog v-model:visible="displayRejectDialog" header="Rejeter la feuille de temps" :style="{ width: '450px' }" modal>
            <div class="flex flex-col gap-3 mt-2">
                <p class="text-sm text-charcoal-600">
                    Veuillez indiquer le motif du rejet pour <strong>{{ rejectingTimesheet?.employee?.first_name }} {{ rejectingTimesheet?.employee?.last_name }}</strong>. La feuille repassera au statut brouillon.
                </p>
                <Textarea v-model="rejectReason" rows="4" placeholder="Motif du rejet (obligatoire)" class="w-full" autoResize />
            </div>
            <template #footer>
                <Button label="Annuler" icon="pi pi-times" class="p-button-text p-button-secondary" @click="displayRejectDialog = false" />
                <Button label="Confirmer le rejet" icon="pi pi-check" class="p-button-danger" @click="rejectTimesheet" :disabled="!rejectReason.trim()" />
            </template>
        </Dialog>

        <!-- Modale de Détails -->
        <Dialog v-model:visible="displayDetailsDialog" header="Détails et Anomalies" :style="{ width: '900px' }" modal maximizable>
            <div v-if="loadingDetails" class="text-center p-8">Chargement...</div>
            <div v-else-if="currentTimesheetDetails">
                <div class="mb-4 bg-pearl-50 p-3 rounded-lg flex justify-between items-center border border-pearl-200">
                    <div>
                        <span class="text-xs text-charcoal-400 block">Total Heures</span>
                        <span class="font-bold text-charcoal-700">{{ currentTimesheetDetails.timesheet.total_hours }}h</span>
                    </div>
                    <div>
                        <span class="text-xs text-charcoal-400 block">Heures Supplémentaires</span>
                        <span class="font-bold text-amber-600">{{ currentTimesheetDetails.timesheet.overtime }}h</span>
                    </div>
                    <div>
                        <span class="text-xs text-charcoal-400 block">Écart Global</span>
                        <span class="font-bold text-red-600">{{ currentTimesheetDetails.timesheet.deviation }}h</span>
                    </div>
                </div>

                <DataTable :value="currentTimesheetDetails.entries" responsiveLayout="scroll" class="p-datatable-sm">
                    <Column field="date" header="Date">
                        <template #body="{ data }">
                            <span class="font-medium">{{ data.date }}</span>
                        </template>
                    </Column>
                    <Column header="Horaires">
                        <template #body="{ data }">
                            <span v-if="data.check_in">{{ data.check_in }} - {{ data.check_out }}</span>
                            <span v-else-if="data.is_absence" class="text-amber-600">{{ data.absence_type }}</span>
                            <span v-else class="text-charcoal-300">-</span>
                        </template>
                    </Column>
                    <Column field="total_hours" header="Total (h)"></Column>
                    <Column header="Alertes">
                        <template #body="{ data }">
                            <div v-if="data.alerts && data.alerts.length" class="flex flex-col gap-1">
                                <Message v-for="(alert, idx) in data.alerts" :key="idx" :severity="alert.type" :closable="false" class="m-0 p-1 text-[10px]">
                                    {{ alert.message }}
                                </Message>
                            </div>
                            <span v-else class="text-charcoal-300 text-xs">Aucune anomalie</span>
                        </template>
                    </Column>
                    <Column field="comment" header="Commentaire"></Column>
                </DataTable>
            </div>
        </Dialog>
    </CpLayout>
</template>
