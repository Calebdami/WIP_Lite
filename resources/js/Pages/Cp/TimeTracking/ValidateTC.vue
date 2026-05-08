<script setup>
import CpLayout from '@/Layouts/CpLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';
import { formatHours, formatHoursShort } from '@/Utils/formatHours';

// PrimeVue components
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import Tag from 'primevue/tag';
import Timeline from 'primevue/timeline';
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

// Validation
const validateSingle = (id) => {
    confirm.require({
        message: 'Valider cette feuille de temps ?',
        header: 'Confirmation',
        icon: 'pi pi-exclamation-triangle',
        acceptProps: { label: 'Valider', severity: 'success' },
        rejectProps: { label: 'Annuler', severity: 'secondary', outlined: true },
        accept: async () => {
            try {
                await axios.post(`/api/timesheets/${id}/validate`);
                toast.add({ severity: 'success', summary: 'Succès', detail: 'Feuille validée', life: 3000 });
                fetchSubmittedTimesheets();
            } catch (error) {
                toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de valider', life: 3000 });
            }
        }
    });
};

const validateBatch = () => {
    if (selectedTimesheets.value.length === 0) return;
    confirm.require({
        message: `Valider les ${selectedTimesheets.value.length} feuilles sélectionnées ?`,
        header: 'Validation en lot',
        icon: 'pi pi-check-circle',
        acceptProps: { label: 'Tout valider', severity: 'success' },
        rejectProps: { label: 'Annuler', severity: 'secondary', outlined: true },
        accept: async () => {
            try {
                await axios.post('/api/timesheets/validate-batch', {
                    timesheet_ids: selectedTimesheets.value.map(ts => ts.id)
                });
                toast.add({ severity: 'success', summary: 'Succès', detail: 'Toutes les feuilles sont validées', life: 3000 });
                selectedTimesheets.value = [];
                fetchSubmittedTimesheets();
            } catch (error) {
                toast.add({ severity: 'error', summary: 'Erreur', detail: 'Une erreur est survenue', life: 3000 });
            }
        }
    });
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

const rejectTimesheet = () => {
    if (!rejectReason.value.trim()) return;
    confirm.require({
        message: 'Confirmer le rejet de cette feuille de temps ?',
        header: 'Rejet de feuille',
        icon: 'pi pi-exclamation-triangle',
        acceptProps: { label: 'Confirmer le Rejet', severity: 'danger' },
        rejectProps: { label: 'Annuler', severity: 'secondary', outlined: true },
        accept: async () => {
            try {
                await axios.post(`/api/timesheets/${rejectingTimesheet.value.id}/reject`, {
                    reason: rejectReason.value
                });
                toast.add({ severity: 'success', summary: 'Rejete', detail: 'La feuille a été rejetée', life: 3000 });
                displayRejectDialog.value = false;
                fetchSubmittedTimesheets();
            } catch (error) {
                toast.add({ severity: 'error', summary: 'Erreur', detail: 'Erreur lors du rejet', life: 3000 });
            }
        }
    });
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

// Historique
const displayHistoryDialog = ref(false);
const historyItems = ref([]);
const loadingHistory = ref(false);

const openHistory = async (ts) => {
    loadingHistory.value = true;
    displayHistoryDialog.value = true;
    try {
        const response = await axios.get(`/api/timesheets/${ts.id}/history`);
        historyItems.value = response.data;
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de charger l\'historique', life: 3000 });
    } finally {
        loadingHistory.value = false;
    }
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
    <Head title="Validation Heures — CP" />
    <CpLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Validation des Heures</h1>
                    <p class="text-xs text-charcoal-400 mt-0.5">Validation finale des feuilles de temps soumises par les superviseurs</p>
                </div>
                <Button label="Valider la sélection" icon="pi pi-check-circle" 
                    severity="success"
                    class="px-5 py-2.5"
                    @click="validateBatch" :disabled="!selectedTimesheets.length" />
            </div>
        </template>

        <div class="bg-white rounded-2xl border border-pearl-200 shadow-premium p-6">
            <DataTable :value="submittedTimesheets" v-model:selection="selectedTimesheets" :loading="loading" 
                dataKey="id" stripedRows responsiveLayout="scroll" paginator :rows="8" class="p-datatable-sm">
                <template #empty>
                    <div class="text-center p-8 text-charcoal-400 flex flex-col items-center gap-3">
                        <i class="pi pi-verified text-4xl opacity-20"></i>
                        <p>Aucune feuille en attente de validation.</p>
                    </div>
                </template>
                <Column selectionMode="multiple" headerStyle="width: 3rem"></Column>
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
                <Column header="Heures" sortable>
                    <template #body="{ data }">
                        <div class="flex flex-col">
                            <span :class="{'text-amber-600 font-black': data.hours_deviation > 2 || data.hours_deviation < -2, 'text-charcoal-700 font-bold': !(data.hours_deviation > 2 || data.hours_deviation < -2)}">
                                {{ formatHours(data.total_hours) }}
                            </span>
                            <span class="text-[10px] text-charcoal-400 uppercase tracking-tighter">Prévu: {{ formatHours(data.total_planned_hours) }}</span>
                        </div>
                    </template>
                </Column>
                <Column header="Écart" sortable>
                    <template #body="{ data }">
                        <Tag v-if="data.hours_deviation > 0" :value="`+${Math.round(data.hours_deviation)}h`" severity="warn" />
                        <Tag v-else-if="data.hours_deviation < 0" :value="`${Math.round(data.hours_deviation)}h`" severity="danger" />
                        <Tag v-else value="CONFORME" severity="success" class="opacity-50" />
                    </template>
                </Column>
                <Column header="Actions">
                    <template #body="{ data }">
                        <div class="flex gap-1">
                            <Button icon="pi pi-eye" text severity="secondary" rounded @click="openDetails(data)" title="Détails" />
                            <Button icon="pi pi-check" text severity="success" rounded @click="validateSingle(data.id)" title="Valider" />
                            <Button icon="pi pi-times" text severity="danger" rounded @click="openRejectDialog(data)" title="Rejeter" />
                            <Button icon="pi pi-history" text severity="info" rounded @click="openHistory(data)" title="Historique" />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- Modale de Rejet -->
        <Dialog v-model:visible="displayRejectDialog" header=" " :style="{ width: '500px' }" modal :closable="false">
            <div class="flex flex-col items-center text-center py-6 px-4">
                <div class="w-20 h-20 rounded-full bg-red-50 text-red-500 flex items-center justify-center mb-6 shadow-inner">
                    <i class="pi pi-times-circle text-4xl"></i>
                </div>
                <h3 class="text-2xl font-black text-charcoal-900 mb-3 tracking-tight">Rejeter la feuille ?</h3>
                <p class="text-sm text-charcoal-500 leading-relaxed">
                    Vous allez renvoyer la fiche de <strong>{{ rejectingTimesheet?.employee?.first_name }} {{ rejectingTimesheet?.employee?.last_name }}</strong> en brouillon.
                </p>

                <div class="w-full mt-8 flex flex-col gap-3 text-left">
                    <label class="text-[10px] font-black uppercase tracking-widest text-charcoal-400 px-1">Motif du rejet (Obligatoire)</label>
                    <Textarea v-model="rejectReason" rows="4" placeholder="Expliquez les corrections nécessaires..." 
                        class="w-full rounded-2xl border-pearl-200 bg-pearl-50 focus:bg-white transition-all p-4 text-sm" autoResize />
                </div>
            </div>
            <template #footer>
                <div class="flex justify-center gap-3 pb-6 w-full">
                    <Button label="Annuler" text severity="secondary" class="rounded-xl px-6" @click="displayRejectDialog = false" />
                    <Button label="Confirmer le rejet" icon="pi pi-times" severity="danger" class="rounded-xl px-8 shadow-lg shadow-red-100" @click="rejectTimesheet" :disabled="!rejectReason.trim()" />
                </div>
            </template>
        </Dialog>

        <!-- Modale de Détails -->
        <Dialog v-model:visible="displayDetailsDialog" header="Analyse Détaillée de l'Activité" :style="{ width: '1100px' }" modal maximizable>
            <div v-if="loadingDetails" class="text-center py-20">
                <i class="pi pi-spin pi-spinner text-5xl text-gold-500 mb-4"></i>
                <p class="text-charcoal-400 font-medium tracking-wide">Chargement de l'historique détaillé...</p>
            </div>
            <div v-else-if="currentTimesheetDetails">
                <div class="mb-6 grid grid-cols-4 gap-4 bg-pearl-50 p-5 rounded-2xl border border-pearl-200">
                    <div class="flex flex-col">
                        <span class="text-[10px] font-black uppercase tracking-widest text-charcoal-400 mb-1">Total Réalisé</span>
                        <span class="text-2xl font-black text-charcoal-700">{{ formatHours(currentTimesheetDetails.timesheet.total_hours) }}</span>
                    </div>
                    <div class="flex flex-col border-x border-pearl-200 px-4">
                        <span class="text-[10px] font-black uppercase tracking-widest text-charcoal-400 mb-1">Prévu Planning</span>
                        <span class="text-2xl font-black text-charcoal-400">{{ formatHours(currentTimesheetDetails.timesheet.planned_hours) }}</span>
                    </div>
                    <div class="flex flex-col border-r border-pearl-200 pr-4">
                        <span class="text-[10px] font-black uppercase tracking-widest text-charcoal-400 mb-1">Écart Analysé</span>
                        <span class="text-2xl font-black" :class="currentTimesheetDetails.timesheet.deviation < 0 ? 'text-red-600' : (currentTimesheetDetails.timesheet.deviation > 0 ? 'text-amber-600' : 'text-emerald-600')">
                            {{ currentTimesheetDetails.timesheet.deviation > 0 ? '+' : '' }}{{ Math.round(currentTimesheetDetails.timesheet.deviation) }}h
                        </span>
                    </div>
                    <div class="flex flex-col items-end">
                        <span class="text-[10px] font-black uppercase tracking-widest text-charcoal-400 mb-2">Décision Requise</span>
                        <Tag :value="currentTimesheetDetails.timesheet.status.toUpperCase()" :severity="getStatusSeverity(currentTimesheetDetails.timesheet.status)" />
                    </div>
                </div>

                <DataTable :value="currentTimesheetDetails.entries" responsiveLayout="scroll" class="p-datatable-sm">
                    <Column field="date" header="Date">
                        <template #body="{ data }">
                            <span class="font-bold text-charcoal-700">{{ data.date }}</span>
                        </template>
                    </Column>
                    <Column header="Horaires">
                        <template #body="{ data }">
                            <div v-if="data.check_in" class="flex items-center gap-2">
                                <span class="px-2 py-0.5 bg-pearl-100 rounded text-xs font-mono text-charcoal-600 border border-pearl-200">{{ data.check_in }}</span>
                                <i class="pi pi-arrow-right text-[10px] text-charcoal-300"></i>
                                <span class="px-2 py-0.5 bg-pearl-100 rounded text-xs font-mono text-charcoal-600 border border-pearl-200">{{ data.check_out }}</span>
                            </div>
                            <Tag v-else-if="data.is_absence" :value="data.absence_type" severity="warn" />
                            <span v-else class="text-charcoal-300 text-xs italic">Aucune donnée</span>
                        </template>
                    </Column>
                    <Column field="total_hours" header="Total">
                         <template #body="{ data }">
                            <span class="font-bold text-charcoal-700">{{ formatHours(data.total_hours) }}</span>
                        </template>
                    </Column>
                    <Column header="Anomalies / Alertes">
                        <template #body="{ data }">
                            <div v-if="data.alerts && data.alerts.length" class="flex flex-col gap-1">
                                <div v-for="(alert, idx) in data.alerts" :key="idx" 
                                     class="flex items-center gap-2 px-2 py-1 rounded text-[10px] border"
                                     :class="{
                                         'bg-red-50 text-red-700 border-red-100': alert.type === 'error',
                                         'bg-amber-50 text-amber-700 border-amber-100': alert.type === 'warn',
                                         'bg-blue-50 text-blue-700 border-blue-100': alert.type === 'info'
                                     }">
                                    <i :class="`pi pi-${alert.type === 'error' ? 'times-circle' : 'exclamation-triangle'} text-[10px]`"></i>
                                    {{ alert.message }}
                                </div>
                            </div>
                            <span v-else class="text-emerald-600 text-[10px] font-bold flex items-center gap-1">
                                <i class="pi pi-check text-[10px]"></i> CONFORME
                            </span>
                        </template>
                    </Column>
                    <Column field="comment" header="Commentaire">
                        <template #body="{ data }">
                            <span class="text-xs text-charcoal-500 italic">{{ data.comment || '-' }}</span>
                        </template>
                    </Column>
                </DataTable>
            </div>
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
                        <span class="flex w-8 h-8 items-center justify-center text-white rounded-full z-10 shadow-sm" :class="slotProps.item.new_status === 'valide' ? 'bg-emerald-500' : 'bg-charcoal-700'">
                            <i :class="slotProps.item.new_status === 'valide' ? 'pi pi-check' : 'pi pi-sync'" class="text-[10px]"></i>
                        </span>
                    </template>
                </Timeline>
            </div>
            <template #footer>
                <Button label="Fermer" text severity="secondary" @click="displayHistoryDialog = false" />
            </template>
        </Dialog>
    </CpLayout>
</template>

<style scoped>
.customized-timeline :deep(.p-timeline-event-opposite) {
    display: none;
}
.customized-timeline :deep(.p-timeline-event-content) {
    padding-left: 2rem;
}
</style>
