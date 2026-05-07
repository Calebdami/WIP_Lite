<script setup>
import SupLayout from '@/Layouts/SupLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';

// PrimeVue components
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Select from 'primevue/select';
import DatePicker from 'primevue/datepicker';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Tag from 'primevue/tag';
import Timeline from 'primevue/timeline';

const toast = useToast();
const confirm = useConfirm();

const timesheets = ref([]);
const loading = ref(false);

const teamMembers = ref([]);

const fetchTeamMembers = async () => {
    try {
        const response = await axios.get('/api/employees'); 
        teamMembers.value = response.data.map(e => ({
            id: e.id,
            name: `${e.first_name} ${e.last_name}`
        }));
    } catch (error) {
        console.error('Erreur agents:', error);
    }
};

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
    fetchTeamMembers();
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
    
    const formatDate = (date) => {
        if (!date) return null;
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };

    const start = newTimesheet.value.period[0];
    const end = newTimesheet.value.period[1] || start;

    const startStr = formatDate(start);
    const endStr = formatDate(end);

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

// Saisie Multiple (Quick Fill)
const quickFill = ref({
    check_in: '09:00',
    check_out: '17:00',
    break_duration: 60
});

const applyQuickFill = () => {
    timesheetEntries.value = timesheetEntries.value.map(entry => ({
        ...entry,
        check_in: quickFill.value.check_in,
        check_out: quickFill.value.check_out,
        break_duration: quickFill.value.break_duration
    }));
    toast.add({ severity: 'info', summary: 'Remplissage rapide', detail: 'Horaires appliqués à toute la semaine', life: 2000 });
};

// Saisie Groupée (Batch Entry)
const selectedTimesheets = ref([]);
const displayBatchDialog = ref(false);
const displayConfirmBatch = ref(false);
const batchSchedule = ref({
    check_in: '09:00',
    check_out: '17:00',
    break_duration: 60
});

const confirmBatchEntry = () => {
    displayBatchDialog.value = false;
    displayConfirmBatch.value = true;
};

const applyBatchEntry = async () => {
    displayConfirmBatch.value = false;
    savingEntries.value = true;
    try {
        const ids = selectedTimesheets.value.map(ts => ts.id);
        await axios.post('/api/timesheets/batch-update-hours', {
            timesheet_ids: ids,
            schedule: batchSchedule.value
        });
        
        toast.add({ 
            severity: 'success', 
            summary: 'Saisie Groupée', 
            detail: `${ids.length} fiches mises à jour avec succès`, 
            life: 3000 
        });
        displayBatchDialog.value = false;
        selectedTimesheets.value = [];
        fetchTimesheets();
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec de la saisie groupée', life: 3000 });
    } finally {
        savingEntries.value = false;
    }
};

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

// Calculs d'écarts et alertes en direct
const totalRealizedHours = computed(() => {
    return timesheetEntries.value.reduce((acc, entry) => acc + (parseFloat(entry.total_hours) || 0), 0);
});

const globalDeviation = computed(() => {
    if (!currentTimesheet.value) return 0;
    const planned = parseFloat(currentTimesheet.value.total_planned_hours) || 35; // Default 35 if not set
    return totalRealizedHours.value - planned;
});

const getEntryAlerts = (entry) => {
    const alerts = [];
    if (entry.check_in && entry.check_out) {
        const [h1, m1] = entry.check_in.split(':').map(Number);
        const [h2, m2] = entry.check_out.split(':').map(Number);
        const diff = (h2 * 60 + m2) - (h1 * 60 + m1) - (entry.break_duration || 0);
        
        if (diff < 0) alerts.push({ type: 'error', message: 'Horaire incohérent' });
        if (diff > 600) alerts.push({ type: 'warn', message: 'Amplitude > 10h' });
    } else if (!entry.absence_type) {
        // Optionnel : alerte si vide sans motif d'absence
    }
    return alerts;
};

const formatDateDisplay = (dateStr) => {
    if (!dateStr) return '';
    const datePart = dateStr.includes('T') ? dateStr.split('T')[0] : (dateStr.includes(' ') ? dateStr.split(' ')[0] : dateStr);
    const parts = datePart.split('-');
    if (parts.length !== 3) return dateStr;
    const [year, month, day] = parts;
    return `${day}/${month}/${year}`;
};

const formatDateTime = (dateStr) => {
    if (!dateStr) return '';
    const datePart = dateStr.includes('T') ? dateStr.split('T')[0] : (dateStr.includes(' ') ? dateStr.split(' ')[0] : dateStr);
    const timePart = dateStr.includes('T') ? dateStr.split('T')[1].substring(0, 5) : (dateStr.includes(' ') ? dateStr.split(' ')[1].substring(0, 5) : '');
    const [year, month, day] = datePart.split('-');
    return `${day}/${month}/${year}${timePart ? ' à ' + timePart : ''}`;
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

const submitTimesheet = (id) => {
    confirm.require({
        message: 'Soumettre cette feuille de temps au Chef de Plateau pour validation ? Elle ne sera plus modifiable.',
        header: 'Soumission finale',
        icon: 'pi pi-send',
        acceptProps: { label: 'Confirmer la Soumission', severity: 'primary' },
        rejectProps: { label: 'Annuler', severity: 'secondary', outlined: true },
        accept: async () => {
            try {
                await axios.post(`/api/timesheets/${id}/submit`);
                toast.add({ severity: 'success', summary: 'Soumis', detail: 'Feuille soumise au CP', life: 3000 });
                fetchTimesheets();
            } catch (error) {
                toast.add({ severity: 'error', summary: 'Erreur', detail: error.response?.data?.message || 'Erreur lors de la soumission', life: 3000 });
            }
        }
    });
};

// Historique
const displayHistoryDialog = ref(false);
const historyItems = ref([]);
const loadingHistory = ref(false);

const openHistory = async (ts) => {
    currentTimesheet.value = ts;
    displayHistoryDialog.value = true;
    loadingHistory.value = true;
    try {
        const response = await axios.get(`/api/timesheets/${ts.id}/history`);
        historyItems.value = response.data;
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de charger l\'historique', life: 3000 });
    } finally {
        loadingHistory.value = false;
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
                <Button label="Nouvelle Feuille" icon="pi pi-plus" 
                    severity="primary"
                    class="px-5 py-2.5"
                    @click="openCreateDialog" />
            </div>
        </template>

        <div class="bg-white rounded-2xl border border-pearl-200 shadow-premium p-6">
            <div v-if="selectedTimesheets.length" class="mb-4 p-4 bg-pearl-50 border border-pearl-200 rounded-xl flex items-center justify-between animate-in fade-in slide-in-from-top-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gold-500 flex items-center justify-center text-white shadow-lg shadow-gold-200">
                        <i class="pi pi-users text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-black text-charcoal-700">{{ selectedTimesheets.length }} fiches sélectionnées</p>
                        <p class="text-xs text-charcoal-400">Appliquez un horaire identique à tous ces agents</p>
                    </div>
                </div>
                <Button label="Saisie Groupée" icon="pi pi-bolt" severity="warn" class="rounded-lg shadow-md shadow-gold-100" @click="displayBatchDialog = true" />
            </div>

            <DataTable :value="timesheets" v-model:selection="selectedTimesheets" :loading="loading" stripedRows responsiveLayout="scroll" 
                paginator :rows="8" class="p-datatable-sm custom-selection-table" dataKey="id">
                <template #empty>
                    <div class="text-center p-8 text-charcoal-400 flex flex-col items-center gap-3">
                        <i class="pi pi-calendar-times text-4xl opacity-20"></i>
                        <p>Aucune feuille de temps trouvée.</p>
                    </div>
                </template>
                <Column selectionMode="multiple" headerStyle="width: 3rem"></Column>
                <Column field="id" header="ID" sortable headerClass="w-20"></Column>
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
                            <i class="pi pi-calendar text-xs"></i>
                            <span class="text-xs font-medium">{{ formatDateDisplay(data.period_start) }} au {{ formatDateDisplay(data.period_end) }}</span>
                        </div>
                    </template>
                </Column>
                <Column header="Total Heures" sortable>
                    <template #body="{ data }">
                        <span class="font-black text-charcoal-700">{{ data.total_hours }}h</span>
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
                            <Button label="Saisir" icon="pi pi-pencil" 
                                size="small"
                                severity="secondary"
                                text
                                class="hover:bg-pearl-100"
                                @click="openEditDialog(data)" 
                                :disabled="data.status === 'validé' || data.status === 'soumis'" />
                            <Button label="Soumettre" icon="pi pi-send" 
                                size="small"
                                severity="info"
                                text
                                @click="submitTimesheet(data.id)" 
                                :disabled="data.status !== 'brouillon' && data.status !== 'rejeté'" />
                            <Button icon="pi pi-history" text severity="info" rounded 
                                @click="openHistory(data)" 
                                title="Historique" />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- Modale de Création -->
        <Dialog v-model:visible="displayCreateDialog" header="Créer une Feuille de Temps" :style="{ width: '400px' }" modal>
            <div class="flex flex-col gap-6 mt-2">
                <div class="flex flex-col gap-2">
                    <label for="employee" class="text-xs font-black uppercase tracking-widest text-charcoal-400">Employé</label>
                    <Select id="employee" v-model="newTimesheet.employee_id" :options="teamMembers" optionLabel="name" optionValue="id" placeholder="Sélectionner un agent" class="w-full" />
                </div>
                <div class="flex flex-col gap-2">
                    <label for="period" class="text-xs font-black uppercase tracking-widest text-charcoal-400">Période (Semaine)</label>
                    <DatePicker id="period" v-model="newTimesheet.period" selectionMode="range" :manualInput="false" showIcon placeholder="Début - Fin" class="w-full" />
                </div>
            </div>
            <template #footer>
                <Button label="Annuler" text severity="secondary" @click="displayCreateDialog = false" />
                <Button label="Créer la feuille" icon="pi pi-check" severity="primary" @click="createTimesheet" :disabled="!newTimesheet.employee_id || !newTimesheet.period" />
            </template>
        </Dialog>

        <!-- Modale de Saisie des Heures -->
        <Dialog v-model:visible="displayEditDialog" :header="`Saisie des Heures`" :style="{ width: '1000px' }" modal maximizable>
            <div class="mb-6 grid grid-cols-4 gap-4 bg-pearl-50 p-4 rounded-2xl border border-pearl-200">
                <div class="flex flex-col">
                    <span class="text-[10px] font-black uppercase tracking-widest text-charcoal-400">Employé</span>
                    <span class="font-bold text-charcoal-700">{{ currentTimesheet?.employee?.first_name }} {{ currentTimesheet?.employee?.last_name }}</span>
                </div>
                <div class="flex flex-col border-x border-pearl-200 px-4">
                    <span class="text-[10px] font-black uppercase tracking-widest text-charcoal-400">Période</span>
                    <span class="text-sm font-black text-charcoal-700">{{ formatDateDisplay(currentTimesheet?.period_start) }} au {{ formatDateDisplay(currentTimesheet?.period_end) }}</span>
                </div>
                <div class="flex flex-col border-r border-pearl-200 pr-4">
                    <span class="text-[10px] font-black uppercase tracking-widest text-charcoal-400">Analyse des Écarts</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-black" :class="globalDeviation < 0 ? 'text-red-600' : 'text-emerald-600'">
                            {{ globalDeviation > 0 ? '+' : '' }}{{ globalDeviation }}h
                        </span>
                        <span class="text-[10px] text-charcoal-400 font-bold uppercase">/ {{ currentTimesheet?.total_planned_hours || 35 }}h prévues</span>
                    </div>
                </div>
                <div class="flex flex-col items-end">
                    <span class="text-[10px] font-black uppercase tracking-widest text-charcoal-400">Statut actuel</span>
                    <Tag :value="currentTimesheet?.status?.toUpperCase()" :severity="getStatusSeverity(currentTimesheet?.status)" />
                </div>
            </div>

            <!-- Saisie Multiple -->
            <div class="mb-6 p-4 bg-gold-50/50 border border-gold-100 rounded-2xl flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-gold-500 flex items-center justify-center text-white">
                        <i class="pi pi-bolt text-xs"></i>
                    </div>
                    <div>
                        <p class="text-xs font-black text-gold-700 uppercase tracking-tight">Saisie Rapide</p>
                        <p class="text-[10px] text-gold-600">Appliquer un horaire type à tous les jours de la semaine</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 bg-white p-2 rounded-2xl border border-gold-100 shadow-sm ml-auto">
                    <div class="flex flex-col gap-1">
                        <span class="text-[9px] font-black text-charcoal-400 px-1">ARRIVÉE</span>
                        <InputText v-model="quickFill.check_in" placeholder="09:00" class="p-inputtext-sm w-24 text-center font-mono !bg-pearl-50/50" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-[9px] font-black text-charcoal-400 px-1">DÉPART</span>
                        <InputText v-model="quickFill.check_out" placeholder="17:00" class="p-inputtext-sm w-24 text-center font-mono !bg-pearl-50/50" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-[9px] font-black text-charcoal-400 px-1">PAUSE (MIN)</span>
                        <InputNumber v-model="quickFill.break_duration" class="p-inputtext-sm w-20 !bg-pearl-50/50" />
                    </div>
                    <Button label="Appliquer partout" icon="pi pi-bolt" size="small" severity="warn" class="rounded-xl px-6 h-10 shadow-gold-premium" @click="applyQuickFill" />
                </div>
            </div>

            <DataTable :value="timesheetEntries" responsiveLayout="scroll" class="p-datatable-sm entry-form-table">
                <Column field="date" header="Date" headerClass="w-40">
                    <template #body="{ data }">
                        <div class="flex flex-col">
                            <span class="font-bold text-charcoal-700 text-sm">{{ data.date }}</span>
                            <span class="text-[9px] text-charcoal-400 font-bold uppercase tracking-tighter">Journée de travail</span>
                        </div>
                    </template>
                </Column>
                <Column header="Arrivée" headerClass="w-28">
                    <template #body="{ data }">
                        <InputText v-model="data.check_in" placeholder="09:00" class="w-full text-center font-mono text-sm" />
                    </template>
                </Column>
                <Column header="Départ" headerClass="w-28">
                    <template #body="{ data }">
                        <InputText v-model="data.check_out" placeholder="17:00" class="w-full text-center font-mono text-sm" />
                    </template>
                </Column>
                <Column header="Pause (min)" headerClass="w-28">
                    <template #body="{ data }">
                        <InputNumber v-model="data.break_duration" class="w-full text-center" :min="0" :max="120" />
                    </template>
                </Column>
                <Column header="Absence / Motif" headerClass="w-44">
                    <template #body="{ data }">
                        <InputText v-model="data.absence_type" placeholder="Type d'absence..." class="w-full p-inputtext-sm text-xs" />
                    </template>
                </Column>
                <Column header="Commentaire">
                    <template #body="{ data }">
                        <InputText v-model="data.comment" placeholder="Note interne..." class="w-full p-inputtext-sm text-xs" />
                    </template>
                </Column>
                <Column header="Alertes" headerClass="w-40">
                    <template #body="{ data }">
                        <div v-if="getEntryAlerts(data).length" class="flex flex-col gap-1">
                            <div v-for="(alert, idx) in getEntryAlerts(data)" :key="idx" 
                                 class="flex items-center gap-1.5 px-2 py-0.5 rounded text-[9px] font-bold border"
                                 :class="alert.type === 'error' ? 'bg-red-50 text-red-700 border-red-100' : 'bg-amber-50 text-amber-700 border-amber-100'">
                                <i :class="`pi pi-${alert.type === 'error' ? 'times-circle' : 'exclamation-triangle'} text-[9px]`"></i>
                                {{ alert.message }}
                            </div>
                        </div>
                        <span v-else-if="data.check_in && data.check_out" class="text-emerald-600 text-[9px] font-bold flex items-center gap-1">
                            <i class="pi pi-check text-[9px]"></i> CONFORME
                        </span>
                        <span v-else class="text-charcoal-300 text-[9px] italic">-</span>
                    </template>
                </Column>
            </DataTable>

            <template #footer>
                <div class="flex justify-between items-center w-full pt-4 border-t border-pearl-100">
                    <p class="text-xs text-charcoal-400 italic font-medium">Toutes les modifications sont enregistrées localement avant validation.</p>
                    <div class="flex gap-3">
                        <Button label="Abandonner" text severity="secondary" class="rounded-xl px-6" @click="displayEditDialog = false" />
                        <Button label="Enregistrer la Saisie" icon="pi pi-save" severity="primary" class="rounded-xl px-8 shadow-premium" @click="saveEntries" :loading="savingEntries" />
                    </div>
                </div>
            </template>
        </Dialog>
        <!-- Modale d'Historique -->
        <Dialog v-model:visible="displayHistoryDialog" header="Journal des modifications" :style="{ width: '600px' }" modal>
            <div v-if="loadingHistory" class="text-center p-12">
                <i class="pi pi-spin pi-spinner text-4xl text-gold-500"></i>
            </div>
            <div v-else class="p-2">
                <Timeline :value="historyItems" class="customized-timeline">
                    <template #content="slotProps">
                        <div class="flex flex-col mb-6 bg-pearl-50 p-4 rounded-2xl border border-pearl-100">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex flex-col">
                                    <span class="text-[10px] font-black uppercase tracking-widest text-charcoal-400">Changement</span>
                                    <div class="flex items-center gap-2 mt-1">
                                        <Tag :value="slotProps.item.old_status?.toUpperCase() || 'CRÉATION'" severity="secondary" class="opacity-50" />
                                        <i class="pi pi-arrow-right text-[10px] text-charcoal-300"></i>
                                        <Tag :value="slotProps.item.new_status?.toUpperCase()" :severity="getStatusSeverity(slotProps.item.new_status)" />
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] font-black uppercase tracking-widest text-charcoal-400">Date</span>
                                    <div class="text-xs font-bold text-charcoal-600 mt-1">{{ formatDateTime(slotProps.item.created_at) }}</div>
                                </div>
                            </div>
                            <div class="mt-2 pt-2 border-t border-pearl-200">
                                <span class="text-[10px] font-black uppercase tracking-widest text-charcoal-400">Par</span>
                                <div class="text-xs font-black text-charcoal-700">{{ slotProps.item.author?.first_name }} {{ slotProps.item.author?.last_name }}</div>
                                <div v-if="slotProps.item.reason" class="mt-2 text-xs text-charcoal-500 italic bg-white p-2 rounded-lg border border-pearl-100">
                                    "{{ slotProps.item.reason }}"
                                </div>
                            </div>
                        </div>
                    </template>
                    <template #marker="slotProps">
                        <span class="flex w-8 h-8 items-center justify-center text-white rounded-full z-10 shadow-sm" :class="slotProps.item.new_status === 'validé' ? 'bg-emerald-500' : 'bg-charcoal-700'">
                            <i :class="slotProps.item.new_status === 'validé' ? 'pi pi-check' : 'pi pi-sync'" class="text-[10px]"></i>
                        </span>
                    </template>
                </Timeline>
            </div>
            <template #footer>
                <Button label="Fermer" text severity="secondary" @click="displayHistoryDialog = false" />
            </template>
        </Dialog>

        <!-- Modale de Saisie Groupée -->
        <Dialog v-model:visible="displayBatchDialog" header="Saisie Groupée (Multi-agents)" :style="{ width: '500px' }" modal>
            <div class="p-4 bg-amber-50 border border-amber-100 rounded-2xl flex gap-4 mb-6">
                <i class="pi pi-exclamation-triangle text-amber-500 text-xl"></i>
                <p class="text-xs text-amber-700 leading-relaxed">
                    Vous allez appliquer cet horaire à <strong>{{ selectedTimesheets.length }} agents</strong> pour l'ensemble de leur période. 
                    Les données existantes sur ces fiches seront remplacées.
                </p>
            </div>
            <div class="flex flex-col gap-6">
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-charcoal-400">Heure d'arrivée</label>
                        <InputText v-model="batchSchedule.check_in" placeholder="09:00" class="w-full rounded-xl font-mono" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-charcoal-400">Heure de départ</label>
                        <InputText v-model="batchSchedule.check_out" placeholder="17:00" class="w-full rounded-xl font-mono" />
                    </div>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-charcoal-400">Pause (minutes)</label>
                    <InputNumber v-model="batchSchedule.break_duration" class="w-full rounded-xl" :min="0" :max="120" />
                </div>
            </div>
            <template #footer>
                <div class="flex flex-col items-center gap-4 w-full pt-4 border-t border-pearl-100">
                    <p class="text-[10px] text-charcoal-400 font-bold uppercase tracking-widest">Confirmation de la Saisie Rapide</p>
                    <div class="flex gap-3">
                        <Button label="Annuler" text severity="secondary" class="rounded-xl px-6" @click="displayBatchDialog = false" />
                        <Button label="Suivant" icon="pi pi-arrow-right" severity="warn" class="rounded-xl px-8 shadow-gold-premium" @click="confirmBatchEntry" />
                    </div>
                </div>
            </template>
        </Dialog>

        <!-- Confirmation Saisie Groupée -->
        <Dialog v-model:visible="displayConfirmBatch" header=" " :style="{ width: '450px' }" modal :closable="false">
            <div class="flex flex-col items-center text-center py-6 px-4">
                <div class="w-24 h-24 rounded-full bg-gold-50 text-gold-500 flex items-center justify-center mb-6 shadow-inner">
                    <i class="pi pi-bolt text-5xl"></i>
                </div>
                <h3 class="text-2xl font-black text-charcoal-900 mb-3 tracking-tight">Appliquer à la sélection ?</h3>
                <p class="text-sm text-charcoal-500 leading-relaxed max-w-[300px]">
                    Vous allez mettre à jour les horaires de <strong>{{ selectedTimesheets.length }} agents</strong> simultanément.
                </p>
                
                <div class="mt-8 grid grid-cols-3 gap-2 w-full p-4 bg-pearl-50 rounded-2xl border border-pearl-100">
                    <div class="flex flex-col items-center">
                        <span class="text-[9px] font-black text-charcoal-400 uppercase">Arrivée</span>
                        <span class="font-mono font-bold text-charcoal-700">{{ batchSchedule.check_in }}</span>
                    </div>
                    <div class="flex flex-col items-center border-x border-pearl-200">
                        <span class="text-[9px] font-black text-charcoal-400 uppercase">Départ</span>
                        <span class="font-mono font-bold text-charcoal-700">{{ batchSchedule.check_out }}</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <span class="text-[9px] font-black text-charcoal-400 uppercase">Pause</span>
                        <span class="font-mono font-bold text-charcoal-700">{{ batchSchedule.break_duration }}m</span>
                    </div>
                </div>
            </div>
            <template #footer>
                <div class="flex justify-center gap-3 pb-4 w-full">
                    <Button label="Annuler" text severity="secondary" class="rounded-xl px-6" @click="displayConfirmBatch = false; displayBatchDialog = true" />
                    <Button label="Oui, appliquer maintenant" icon="pi pi-check-circle" severity="warn" class="rounded-xl px-8 shadow-gold-premium" @click="applyBatchEntry" :loading="savingEntries" />
                </div>
            </template>
        </Dialog>
    </SupLayout>
</template>

<style scoped>
:deep(.custom-selection-table .p-checkbox) {
    width: 20px;
    height: 20px;
}

:deep(.custom-selection-table .p-checkbox .p-checkbox-box) {
    border: 2px solid #e2e8f0;
    border-radius: 6px;
    transition: all 0.2s;
}

:deep(.custom-selection-table .p-checkbox .p-checkbox-box.p-highlight) {
    background: #d4af37;
    border-color: #d4af37;
}

:deep(.custom-selection-table .p-checkbox:not(.p-checkbox-disabled):hover .p-checkbox-box) {
    border-color: #d4af37;
}
</style>

<style scoped>
.customized-timeline :deep(.p-timeline-event-opposite) {
    display: none;
}
.customized-timeline :deep(.p-timeline-event-content) {
    padding-left: 2rem;
}
</style>
