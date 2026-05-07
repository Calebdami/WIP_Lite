<script setup>
import TcLayout from '@/Layouts/TcLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';

// PrimeVue components
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';

const toast = useToast();

const timesheets = ref([]);
const loading = ref(false);

const fetchMyHours = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/timesheets/my/hours');
        timesheets.value = response.data;
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de charger votre historique', life: 3000 });
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchMyHours();
});

// Vue détaillée en lecture seule
const displayDetailsDialog = ref(false);
const currentTimesheetDetails = ref(null);

const openDetails = (ts) => {
    currentTimesheetDetails.value = ts;
    displayDetailsDialog.value = true;
};

const formatDateDisplay = (dateStr) => {
    if (!dateStr) return '';
    const datePart = dateStr.includes('T') ? dateStr.split('T')[0] : (dateStr.includes(' ') ? dateStr.split(' ')[0] : dateStr);
    const parts = datePart.split('-');
    if (parts.length !== 3) return dateStr;
    const [year, month, day] = parts;
    return `${day}/${month}/${year}`;
};
</script>

<template>
    <Head title="Mes Heures Validées — Téléconseiller" />
    <TcLayout>
        <template #header>
            <div>
                <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Mes Heures Validées</h1>
                <p class="text-xs text-charcoal-400 mt-0.5">Historique de vos temps de travail confirmés (12 derniers mois)</p>
            </div>
        </template>
        
        <div class="bg-white rounded-2xl border border-pearl-200 shadow-premium p-6">
            <DataTable :value="timesheets" :loading="loading" stripedRows responsiveLayout="scroll" class="p-datatable-sm" paginator :rows="8">
                <template #empty>
                    <div class="text-center p-12 text-charcoal-400 flex flex-col items-center gap-3">
                        <i class="pi pi-verified text-4xl opacity-20"></i>
                        <p>Aucune heure validée trouvée pour les 12 derniers mois.</p>
                    </div>
                </template>
                
                <Column header="Période" sortable>
                    <template #body="{ data }">
                        <div class="flex items-center gap-2">
                            <i class="pi pi-calendar text-gold-500 text-xs"></i>
                            <span class="font-bold text-charcoal-700">{{ formatDateDisplay(data.period_start) }} au {{ formatDateDisplay(data.period_end) }}</span>
                        </div>
                    </template>
                </Column>
                <Column header="Total Heures" sortable>
                    <template #body="{ data }">
                        <span class="font-black text-charcoal-700">{{ data.total_hours }} h</span>
                    </template>
                </Column>
                <Column header="Heures Suppl." sortable>
                    <template #body="{ data }">
                        <Tag v-if="data.overtime_hours > 0" :value="`+${data.overtime_hours} h`" severity="warn" />
                        <span v-else class="text-charcoal-300">-</span>
                    </template>
                </Column>
                <Column header="Statut" sortable>
                    <template #body="{ data }">
                        <Tag :value="data.status.toUpperCase()" severity="success" />
                    </template>
                </Column>
                <Column header="Validation">
                    <template #body="{ data }">
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-charcoal-600">Validé le</span>
                            <span class="text-[10px] text-charcoal-400 uppercase tracking-tighter">{{ data.validated_at ? formatDateDisplay(data.validated_at) : 'En attente' }}</span>
                        </div>
                    </template>
                </Column>
                <Column header="Actions">
                    <template #body="{ data }">
                        <Button icon="pi pi-eye" text severity="secondary" rounded @click="openDetails(data)" title="Détails" />
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- Modale de Détails -->
        <Dialog v-model:visible="displayDetailsDialog" header="Récapitulatif Hebdomadaire" :style="{ width: '850px' }" modal>
            <div v-if="currentTimesheetDetails">
                <div class="mb-6 grid grid-cols-2 gap-4 bg-pearl-50 p-4 rounded-2xl border border-pearl-200">
                    <div class="flex flex-col">
                        <span class="text-[10px] font-black uppercase tracking-widest text-charcoal-400">Total Réalisé</span>
                        <span class="text-2xl font-black text-charcoal-700">{{ currentTimesheetDetails.total_hours }}h</span>
                    </div>
                    <div class="flex flex-col border-l border-pearl-200 pl-4 items-end">
                        <span class="text-[10px] font-black uppercase tracking-widest text-charcoal-400">Prévu au Planning</span>
                        <span class="text-2xl font-black text-charcoal-400">{{ currentTimesheetDetails.planned_hours }}h</span>
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
                            <span v-else class="text-charcoal-300 text-xs italic">Repos</span>
                        </template>
                    </Column>
                    <Column header="Pause">
                        <template #body="{ data }">
                            <span v-if="data.break_duration > 0" class="text-xs text-charcoal-500">{{ data.break_duration }} min</span>
                            <span v-else class="text-charcoal-300">-</span>
                        </template>
                    </Column>
                    <Column field="total_hours" header="Total">
                        <template #body="{ data }">
                            <span class="font-bold text-charcoal-700">{{ data.total_hours }}h</span>
                        </template>
                    </Column>
                    <Column header="Absence/Congé">
                        <template #body="{ data }">
                            <Tag v-if="data.absence_type" :value="data.absence_type" severity="warn" />
                            <span v-else class="text-charcoal-300 text-[10px]">-</span>
                        </template>
                    </Column>
                </DataTable>
            </div>
            <template #footer>
                <Button label="Fermer" text severity="secondary" @click="displayDetailsDialog = false" />
            </template>
        </Dialog>
    </TcLayout>
</template>
