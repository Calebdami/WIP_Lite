<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
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
const cpList = ref([]);

const fetchCPs = async () => {
    try {
        const response = await axios.get('/api/employees');
        cpList.value = response.data.map(e => ({
            id: e.id,
            name: `${e.first_name} ${e.last_name}`
        }));
    } catch (error) {
        console.error('Erreur lors du chargement des CPs');
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
    fetchCPs();
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

const formatDate = (date) => {
    if (!date) return null;
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const createTimesheet = async () => {
    if (!newTimesheet.value.employee_id || !newTimesheet.value.period) return;
    
    const start = newTimesheet.value.period[0];
    const end = newTimesheet.value.period[1] || start;

    try {
        await axios.post('/api/timesheets', {
            employee_id: newTimesheet.value.employee_id,
            period_start: formatDate(start),
            period_end: formatDate(end)
        });
        toast.add({ severity: 'success', summary: 'Succès', detail: 'Feuille de temps CP créée', life: 3000 });
        displayCreateDialog.value = false;
        fetchTimesheets();
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Erreur', detail: error.response?.data?.message || 'Erreur lors de la création', life: 3000 });
    }
};

// Saisie des heures
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
        toast.add({ severity: 'success', summary: 'Enregistré', detail: 'Heures CP enregistrées', life: 3000 });
        displayEditDialog.value = false;
        fetchTimesheets();
    } catch (error) {
        const message = error.response?.data?.message || 'Erreur lors de l\'enregistrement';
        toast.add({ severity: 'error', summary: 'Erreur', detail: message, life: 5000 });
    } finally {
        savingEntries.value = false;
    }
};

// Validation
const validateTimesheet = (id) => {
    confirm.require({
        message: 'Êtes-vous sûr de vouloir valider définitivement cette feuille de temps ?',
        header: 'Confirmation de validation',
        icon: 'pi pi-exclamation-triangle',
        rejectProps: {
            label: 'Annuler',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Valider',
            severity: 'success'
        },
        accept: async () => {
            try {
                await axios.post(`/api/timesheets/${id}/validate`);
                toast.add({ severity: 'success', summary: 'Validé', detail: 'Feuille de temps CP validée', life: 3000 });
                fetchTimesheets();
            } catch (error) {
                const message = error.response?.data?.message || 'Erreur lors de la validation';
                toast.add({ severity: 'error', summary: 'Validation impossible', detail: message, life: 5000 });
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
        case 'valide': return 'success';
        case 'rejete': return 'danger';
        default: return 'info';
    }
};
</script>

<template>
    <Head title="Suivi & Clôture des Heures — Admin" />
    <AdminLayout>
        <template #header>
            <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Suivi & Clôture des Heures</h1>
            <p class="text-xs text-charcoal-400 mt-0.5">Validation finale et clôture des périodes de paie</p>
        </template>
        <div class="bg-white rounded-xl border border-pearl-200 p-8 text-center text-charcoal-400">
            Interface de suivi et de clôture des heures travaillées.
        </div>
    </AdminLayout>
</template>
