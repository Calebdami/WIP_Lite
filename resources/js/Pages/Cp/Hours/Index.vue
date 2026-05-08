<script setup>
import CpLayout from '@/Layouts/CpLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';
import { formatHours, formatHoursShort } from '@/Utils/formatHours';

// PrimeVue components
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import DatePicker from 'primevue/datepicker';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';

const toast = useToast();
const confirm = useConfirm();
const page = usePage();
const currentUser = computed(() => page.props.auth?.user || {});

const timesheets = ref([]);
const loading = ref(false);

const displayCreateDialog = ref(false);
const newPeriod = ref(null);
const displayEditDialog = ref(false);
const currentTimesheet = ref(null);
const timesheetEntries = ref([]);
const savingEntries = ref(false);

const fetchMyHours = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/timesheets/my/hours', {
            params: { all: true }
        });
        timesheets.value = response.data;
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de charger vos feuilles', life: 3000 });
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchMyHours();
});

const formatDateDisplay = (dateStr) => {
    if (!dateStr) return '';
    const datePart = dateStr.includes('T') ? dateStr.split('T')[0] : (dateStr.includes(' ') ? dateStr.split(' ')[0] : dateStr);
    const parts = datePart.split('-');
    if (parts.length !== 3) return dateStr;
    const [year, month, day] = parts;
    return `${day}/${month}/${year}`;
};

const openCreateDialog = () => {
    newPeriod.value = null;
    displayCreateDialog.value = true;
};

const createTimesheet = async () => {
    if (!newPeriod.value || !newPeriod.value[0] || !newPeriod.value[1]) return;

    const formatDate = (date) => {
        if (!date) return null;
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };

    try {
        await axios.post('/api/timesheets', {
            employee_id: currentUser.value.id,
            period_start: formatDate(newPeriod.value[0]),
            period_end: formatDate(newPeriod.value[1]),
        });
        toast.add({ severity: 'success', summary: 'Succès', detail: 'Feuille de temps créée', life: 3000 });
        displayCreateDialog.value = false;
        fetchMyHours();
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Erreur', detail: error.response?.data?.message || 'Erreur lors de la création', life: 5000 });
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

const saveEntries = async () => {
    if (!currentTimesheet.value) return;

    savingEntries.value = true;
    try {
        await axios.put(`/api/timesheets/${currentTimesheet.value.id}/entries/batch`, {
            entries: timesheetEntries.value,
        });
        toast.add({ severity: 'success', summary: 'Enregistré', detail: 'Heures enregistrées avec succès', life: 3000 });
        displayEditDialog.value = false;
        fetchMyHours();
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Erreur', detail: error.response?.data?.message || 'Erreur lors de l\'enregistrement', life: 5000 });
    } finally {
        savingEntries.value = false;
    }
};

const validateTimesheet = (id) => {
    confirm.require({
        message: 'Valider définitivement cette feuille de temps ?',
        header: 'Validation personnelle',
        icon: 'pi pi-check-circle',
        acceptProps: { label: 'Valider', severity: 'success' },
        rejectProps: { label: 'Annuler', severity: 'secondary', outlined: true },
        accept: async () => {
            try {
                await axios.post(`/api/timesheets/${id}/validate`);
                toast.add({ severity: 'success', summary: 'Validé', detail: 'Votre feuille a été validée', life: 3000 });
                fetchMyHours();
            } catch (error) {
                toast.add({ severity: 'error', summary: 'Erreur', detail: error.response?.data?.message || 'Impossible de valider', life: 5000 });
            }
        }
    });
};

const isEditable = (status) => ['brouillon', 'rejete'].includes(status);
</script>

<template>
    <Head title="Mes Heures — Chef de Plateau" />
    <CpLayout>
        <template #header>
            <div class="flex flex-col gap-1">
                <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Mes heures</h1>
                <p class="text-xs text-charcoal-400 mt-0.5">Saisissez vos heures et validez vos propres feuilles de temps.</p>
            </div>
        </template>

        <div class="bg-white rounded-2xl border border-pearl-200 shadow-premium p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                <div>
                    <p class="text-sm font-medium text-charcoal-700">Suivi de vos feuilles de temps</p>
                    <p class="text-xs text-charcoal-400">Vous pouvez créer une nouvelle feuille, saisir vos heures et la valider vous-même.</p>
                </div>
                <Button label="Nouvelle feuille" icon="pi pi-plus" severity="primary" class="px-5 py-2.5 shadow-premium" @click="openCreateDialog" />
            </div>

            <DataTable :value="timesheets" :loading="loading" stripedRows responsiveLayout="scroll" class="p-datatable-sm" paginator :rows="8">
                <template #empty>
                    <div class="text-center p-12 text-charcoal-400 flex flex-col items-center gap-3">
                        <i class="pi pi-calendar-times text-4xl opacity-20"></i>
                        <p>Aucune feuille de temps trouvée pour les 12 derniers mois.</p>
                    </div>
                </template>

                <Column header="Période" sortable>
                    <template #body="{ data }">
                        <div class="flex flex-col gap-1">
                            <span class="font-bold text-charcoal-700">{{ formatDateDisplay(data.period_start) }} au {{ formatDateDisplay(data.period_end) }}</span>
                            <span class="text-[10px] text-charcoal-400 uppercase tracking-widest">{{ data.status.toUpperCase() }}</span>
                        </div>
                    </template>
                </Column>
                <Column header="Total heures" sortable>
                    <template #body="{ data }">
                        <span class="font-black text-charcoal-700">{{ formatHours(data.total_hours) }}</span>
                    </template>
                </Column>
                <Column header="Heures sup" sortable>
                    <template #body="{ data }">
                        <span class="font-bold" :class="data.overtime_hours > 0 ? 'text-amber-600' : 'text-charcoal-400'">{{ formatHours(data.overtime_hours) }}</span>
                    </template>
                </Column>
                <Column header="Validé le">
                    <template #body="{ data }">
                        <span class="text-xs text-charcoal-500">{{ data.validated_at ? formatDateDisplay(data.validated_at) : 'Pas encore' }}</span>
                    </template>
                </Column>
                <Column header="Actions">
                    <template #body="{ data }">
                        <div class="flex flex-wrap gap-2">
                            <Button icon="pi pi-eye" text severity="secondary" rounded @click="openEditDialog(data)" title="Voir / Modifier" />
                            <Button v-if="isEditable(data.status) || data.status === 'soumis'" icon="pi pi-check-circle" text severity="success" rounded @click="validateTimesheet(data.id)" title="Valider" />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <Dialog v-model:visible="displayCreateDialog" header="Nouvelle feuille de temps" :style="{ width: '480px' }" modal>
            <div class="flex flex-col gap-4">
                <div class="flex flex-col gap-2">
                    <label class="text-xs font-black uppercase tracking-widest text-charcoal-400">Période</label>
                    <DatePicker v-model="newPeriod" selectionMode="range" :manualInput="false" showIcon placeholder="Sélectionnez la période" class="w-full rounded-xl" />
                </div>
            </div>
            <template #footer>
                <Button label="Annuler" text severity="secondary" @click="displayCreateDialog = false" />
                <Button label="Créer" icon="pi pi-plus" severity="primary" @click="createTimesheet" :disabled="!newPeriod || !newPeriod[0] || !newPeriod[1]" />
            </template>
        </Dialog>

        <Dialog v-model:visible="displayEditDialog" header="Saisie de la feuille de temps" :style="{ width: '1100px' }" modal maximizable>
            <div v-if="currentTimesheet" class="mb-6 grid grid-cols-3 gap-4 bg-pearl-50 p-4 rounded-2xl border border-pearl-200">
                <div class="flex flex-col">
                    <span class="text-[10px] font-black uppercase tracking-widest text-charcoal-400">Période</span>
                    <span class="text-sm font-black text-charcoal-700">{{ formatDateDisplay(currentTimesheet.period_start) }} au {{ formatDateDisplay(currentTimesheet.period_end) }}</span>
                </div>
                <div class="flex flex-col border-x border-pearl-200 px-4">
                    <span class="text-[10px] font-black uppercase tracking-widest text-charcoal-400">Statut</span>
                    <Tag :value="currentTimesheet.status.toUpperCase()" :severity="currentTimesheet.status === 'valide' ? 'success' : currentTimesheet.status === 'soumis' ? 'info' : currentTimesheet.status === 'rejete' ? 'danger' : 'secondary'" />
                </div>
                <div class="flex flex-col items-end">
                    <span class="text-[10px] font-black uppercase tracking-widest text-charcoal-400">Total actuel</span>
                    <span class="text-2xl font-black text-charcoal-700">{{ formatHours(currentTimesheet.total_hours) }}</span>
                </div>
            </div>

            <DataTable :value="timesheetEntries" responsiveLayout="scroll" class="p-datatable-sm">
                <Column field="date" header="Date" headerClass="w-32">
                    <template #body="{ data }">
                        <span class="font-bold text-charcoal-700">{{ data.date }}</span>
                    </template>
                </Column>
                <Column header="Arrivée" headerClass="w-28">
                    <template #body="{ data }">
                        <InputText v-model="data.check_in" placeholder="09:00" class="w-full text-center font-mono text-sm" :disabled="!isEditable(currentTimesheet.status)" />
                    </template>
                </Column>
                <Column header="Départ" headerClass="w-28">
                    <template #body="{ data }">
                        <InputText v-model="data.check_out" placeholder="17:00" class="w-full text-center font-mono text-sm" :disabled="!isEditable(currentTimesheet.status)" />
                    </template>
                </Column>
                <Column header="Pause" headerClass="w-24">
                    <template #body="{ data }">
                        <InputNumber v-model="data.break_duration" class="w-full text-center" :min="0" :max="480" :disabled="!isEditable(currentTimesheet.status)" />
                    </template>
                </Column>
                <Column header="Management (h)" headerClass="w-28">
                    <template #body="{ data }">
                        <InputNumber v-model="data.management_hours" class="w-full text-center" :min="0" :max="24" :step="0.25" :disabled="!isEditable(currentTimesheet.status)" />
                    </template>
                </Column>
                <Column header="Astreinte (h)" headerClass="w-28">
                    <template #body="{ data }">
                        <InputNumber v-model="data.on_call_hours" class="w-full text-center" :min="0" :max="24" :step="0.25" :disabled="!isEditable(currentTimesheet.status)" />
                    </template>
                </Column>
                <Column header="Formation (h)" headerClass="w-28">
                    <template #body="{ data }">
                        <InputNumber v-model="data.training_hours" class="w-full text-center" :min="0" :max="24" :step="0.25" :disabled="!isEditable(currentTimesheet.status)" />
                    </template>
                </Column>
                <Column header="Absence / Motif" headerClass="w-40">
                    <template #body="{ data }">
                        <InputText v-model="data.absence_type" placeholder="Type..." class="w-full p-inputtext-sm text-xs" :disabled="!isEditable(currentTimesheet.status)" />
                    </template>
                </Column>
                <Column header="Commentaire">
                    <template #body="{ data }">
                        <InputText v-model="data.comment" class="w-full p-inputtext-sm text-xs" :disabled="!isEditable(currentTimesheet.status)" />
                    </template>
                </Column>
            </DataTable>

            <template #footer>
                <div class="flex justify-between items-center w-full pt-4 border-t border-pearl-100">
                    <p class="text-xs text-charcoal-400 italic font-medium">Modifiez votre feuille de temps si elle est encore en brouillon ou rejetée.</p>
                    <div class="flex gap-3">
                        <Button label="Fermer" text severity="secondary" class="rounded-xl px-6" @click="displayEditDialog = false" />
                        <Button v-if="isEditable(currentTimesheet.status)" label="Enregistrer" icon="pi pi-save" severity="primary" class="rounded-xl px-8 shadow-premium" @click="saveEntries" :loading="savingEntries" />
                    </div>
                </div>
            </template>
        </Dialog>
    </CpLayout>
</template>
