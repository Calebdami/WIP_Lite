<script setup>
import SupLayout from '@/Layouts/SupLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';

// PrimeVue components
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import Calendar from 'primevue/calendar';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Tag from 'primevue/tag';

const toast = useToast();

const timesheets = ref([]);
const loading = ref(false);

// Dummy team members since there's no specific API for it yet
const teamMembers = ref([
    { id: 1, name: 'Alice Durand' },
    { id: 2, name: 'Bob Lemoine' },
    { id: 3, name: 'Charlie Vidal' },
]);

const fetchTimesheets = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/timesheets');
        timesheets.value = response.data;
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de charger les feuilles de temps', life: 3000 });
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchTimesheets();
});

// Modale de Création
const displayCreateDialog = ref(false);
const newTimesheet = ref({
    employee_id: null,
    period: null
});

const openCreateDialog = () => {
    newTimesheet.value = { employee_id: null, period: null };
    displayCreateDialog.value = true;
};

const createTimesheet = async () => {
    if (!newTimesheet.value.employee_id || !newTimesheet.value.period) return;
    
    // Convert Dates to YYYY-MM-DD
    const start = newTimesheet.value.period[0];
    const end = newTimesheet.value.period[1] || start;

    const startStr = start.toISOString().split('T')[0];
    const endStr = end.toISOString().split('T')[0];

    try {
        await axios.post('/api/timesheets', {
            employee_id: newTimesheet.value.employee_id,
            period_start: startStr,
            period_end: endStr
        });
        toast.add({ severity: 'success', summary: 'Succès', detail: 'Feuille de temps créée', life: 3000 });
        displayCreateDialog.value = false;
        fetchTimesheets();
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Erreur', detail: error.response?.data?.message || 'Erreur lors de la création', life: 3000 });
    }
};

// Modale d'Édition (Saisie des entrées)
const displayEditDialog = ref(false);
const currentTimesheet = ref(null);
const timesheetEntries = ref([]);
const savingEntries = ref(false);

const openEditDialog = async (ts) => {
    currentTimesheet.value = ts;
    try {
        const response = await axios.get(`/api/timesheets/${ts.id}/entries`);
        timesheetEntries.value = response.data.entries;
        displayEditDialog.value = true;
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de charger les entrées', life: 3000 });
    }
};

const saveEntries = async () => {
    savingEntries.value = true;
    try {
        await axios.put(`/api/timesheets/${currentTimesheet.value.id}/entries/batch`, {
            entries: timesheetEntries.value
        });
        toast.add({ severity: 'success', summary: 'Enregistré', detail: 'Heures enregistrées avec succès', life: 3000 });
        displayEditDialog.value = false;
        fetchTimesheets();
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Erreur', detail: 'Erreur lors de l\'enregistrement', life: 3000 });
    } finally {
        savingEntries.value = false;
    }
};

const submitTimesheet = async (id) => {
    try {
        await axios.post(`/api/timesheets/${id}/submit`);
        toast.add({ severity: 'success', summary: 'Soumis', detail: 'Feuille soumise au CP', life: 3000 });
        fetchTimesheets();
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Erreur', detail: error.response?.data?.message || 'Erreur lors de la soumission', life: 3000 });
    }
};

const getStatusSeverity = (status) => {
    switch (status) {
        case 'brouillon': return 'secondary';
        case 'soumis': return 'info';
        case 'validé': return 'success';
        case 'rejeté': return 'danger';
        default: return 'info';
    }
};
</script>

<template>
    <Head title="Saisie des Heures — Superviseur" />
    <SupLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Saisie des Heures</h1>
                    <p class="text-xs text-charcoal-400 mt-0.5">Enregistrement des temps de travail de vos agents</p>
                </div>
                <Button label="Nouvelle Feuille" icon="pi pi-plus" class="p-button-sm p-button-primary" @click="openCreateDialog" />
            </div>
        </template>

        <div class="bg-white rounded-xl border border-pearl-200 shadow-sm p-4">
            <DataTable :value="timesheets" :loading="loading" stripedRows responsiveLayout="scroll" class="p-datatable-sm">
                <template #empty>
                    <div class="text-center p-4 text-charcoal-400">Aucune feuille de temps trouvée.</div>
                </template>
                <Column field="id" header="ID" sortable></Column>
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
                        {{ data.total_hours }} h
                    </template>
                </Column>
                <Column field="status" header="Statut" sortable>
                    <template #body="{ data }">
                        <Tag :value="data.status.toUpperCase()" :severity="getStatusSeverity(data.status)" />
                    </template>
                </Column>
                <Column header="Actions">
                    <template #body="{ data }">
                        <div class="flex gap-2">
                            <Button icon="pi pi-pencil" class="bg-pearl-100 hover:bg-pearl-200 text-charcoal-700 w-8 h-8 rounded-full flex items-center justify-center border border-pearl-300 transition-colors" 
                                @click="openEditDialog(data)" 
                                :disabled="data.status === 'validé' || data.status === 'soumis'" 
                                title="Saisir les heures" />
                            <Button icon="pi pi-send" class="bg-blue-100 hover:bg-blue-200 text-blue-700 w-8 h-8 rounded-full flex items-center justify-center border border-blue-300 transition-colors" 
                                @click="submitTimesheet(data.id)" 
                                :disabled="data.status !== 'brouillon' && data.status !== 'rejeté'" 
                                title="Soumettre au CP" />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- Modale de Création -->
        <Dialog v-model:visible="displayCreateDialog" header="Créer une Feuille de Temps" :style="{ width: '400px' }" modal>
            <div class="flex flex-col gap-4 mt-2">
                <div class="flex flex-col gap-2">
                    <label for="employee" class="text-sm font-bold text-charcoal-700">Employé</label>
                    <Dropdown id="employee" v-model="newTimesheet.employee_id" :options="teamMembers" optionLabel="name" optionValue="id" placeholder="Sélectionner un agent" class="w-full" />
                </div>
                <div class="flex flex-col gap-2">
                    <label for="period" class="text-sm font-bold text-charcoal-700">Période (Semaine)</label>
                    <Calendar id="period" v-model="newTimesheet.period" selectionMode="range" :manualInput="false" showIcon placeholder="Début - Fin" class="w-full" />
                </div>
            </div>
            <template #footer>
                <Button label="Annuler" icon="pi pi-times" class="p-button-text p-button-secondary" @click="displayCreateDialog = false" />
                <Button label="Créer" icon="pi pi-check" class="p-button-primary" @click="createTimesheet" :disabled="!newTimesheet.employee_id || !newTimesheet.period" />
            </template>
        </Dialog>

        <!-- Modale de Saisie des Heures -->
        <Dialog v-model:visible="displayEditDialog" :header="`Saisie des Heures`" :style="{ width: '900px' }" modal maximizable>
            <div class="mb-4 bg-pearl-50 p-3 rounded-lg flex justify-between items-center border border-pearl-200">
                <div>
                    <span class="text-xs text-charcoal-400 block">Employé</span>
                    <span class="font-bold text-charcoal-700">{{ currentTimesheet?.employee?.first_name }} {{ currentTimesheet?.employee?.last_name }}</span>
                </div>
                <div>
                    <span class="text-xs text-charcoal-400 block">Période</span>
                    <span class="font-bold text-charcoal-700">{{ currentTimesheet?.period_start }} au {{ currentTimesheet?.period_end }}</span>
                </div>
                <div>
                    <span class="text-xs text-charcoal-400 block">Statut</span>
                    <Tag :value="currentTimesheet?.status?.toUpperCase()" :severity="getStatusSeverity(currentTimesheet?.status)" />
                </div>
            </div>

            <DataTable :value="timesheetEntries" responsiveLayout="scroll" class="p-datatable-sm" editMode="cell">
                <Column field="date" header="Date">
                    <template #body="{ data }">
                        <span class="font-medium">{{ data.date }}</span>
                    </template>
                </Column>
                <Column header="Arrivée">
                    <template #body="{ data }">
                        <InputText v-model="data.check_in" placeholder="09:00" class="w-full p-inputtext-sm" />
                    </template>
                </Column>
                <Column header="Départ">
                    <template #body="{ data }">
                        <InputText v-model="data.check_out" placeholder="17:00" class="w-full p-inputtext-sm" />
                    </template>
                </Column>
                <Column header="Pause (min)">
                    <template #body="{ data }">
                        <InputNumber v-model="data.break_duration" class="w-full p-inputtext-sm" />
                    </template>
                </Column>
                <Column header="Absence">
                    <template #body="{ data }">
                        <InputText v-model="data.absence_type" placeholder="Maladie, etc." class="w-full p-inputtext-sm" />
                    </template>
                </Column>
                <Column header="Commentaire">
                    <template #body="{ data }">
                        <InputText v-model="data.comment" class="w-full p-inputtext-sm" />
                    </template>
                </Column>
            </DataTable>

            <template #footer>
                <Button label="Annuler" icon="pi pi-times" class="p-button-text p-button-secondary" @click="displayEditDialog = false" />
                <Button label="Enregistrer" icon="pi pi-save" class="p-button-primary" @click="saveEntries" :loading="savingEntries" />
            </template>
        </Dialog>
    </SupLayout>
</template>
