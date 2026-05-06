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
</script>

<template>
    <Head title="Mes Heures Validées — Téléconseiller" />
    <TcLayout>
        <template #header>
            <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Mes Heures Validées</h1>
            <p class="text-xs text-charcoal-400 mt-0.5">Historique de vos temps de travail confirmés (12 derniers mois)</p>
        </template>
        
        <div class="bg-white rounded-xl border border-pearl-200 shadow-sm p-4">
            <DataTable :value="timesheets" :loading="loading" stripedRows responsiveLayout="scroll" class="p-datatable-sm" paginator :rows="10">
                <template #empty>
                    <div class="text-center p-4 text-charcoal-400">Aucune heure validée trouvée pour les 12 derniers mois.</div>
                </template>
                
                <Column header="Période" sortable>
                    <template #body="{ data }">
                        <span class="font-bold text-charcoal-700">{{ data.period_start }} au {{ data.period_end }}</span>
                    </template>
                </Column>
                <Column header="Total Heures" sortable>
                    <template #body="{ data }">
                        {{ data.total_hours }} h
                    </template>
                </Column>
                <Column header="Heures Suppl." sortable>
                    <template #body="{ data }">
                        <span v-if="data.overtime_hours > 0" class="text-amber-600 font-bold">+{{ data.overtime_hours }} h</span>
                        <span v-else class="text-charcoal-300">-</span>
                    </template>
                </Column>
                <Column header="Statut" sortable>
                    <template #body="{ data }">
                        <Tag :value="data.status.toUpperCase()" severity="success" />
                    </template>
                </Column>
                <Column header="Date de validation">
                    <template #body="{ data }">
                        <span class="text-xs text-charcoal-400">{{ data.validated_at || 'Inconnue' }}</span>
                    </template>
                </Column>
                <Column header="Détails">
                    <template #body="{ data }">
                        <Button icon="pi pi-search" class="p-button-rounded p-button-text p-button-secondary p-button-sm" 
                            @click="openDetails(data)" title="Voir les détails journaliers" />
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- Modale de Détails -->
        <Dialog v-model:visible="displayDetailsDialog" header="Détails de la semaine" :style="{ width: '800px' }" modal>
            <div v-if="currentTimesheetDetails">
                <div class="mb-4 bg-pearl-50 p-3 rounded-lg flex justify-between items-center border border-pearl-200">
                    <div>
                        <span class="text-xs text-charcoal-400 block">Total Réalisé</span>
                        <span class="font-bold text-charcoal-700">{{ currentTimesheetDetails.total_hours }}h</span>
                    </div>
                    <div>
                        <span class="text-xs text-charcoal-400 block">Prévu au Planning</span>
                        <span class="font-bold text-charcoal-700">{{ currentTimesheetDetails.planned_hours }}h</span>
                    </div>
                </div>

                <DataTable :value="currentTimesheetDetails.entries" responsiveLayout="scroll" class="p-datatable-sm">
                    <Column field="date" header="Date">
                        <template #body="{ data }">
                            <span class="font-medium">{{ data.date }}</span>
                        </template>
                    </Column>
                    <Column header="Arrivée - Départ">
                        <template #body="{ data }">
                            <span v-if="data.check_in">{{ data.check_in }} - {{ data.check_out }}</span>
                            <span v-else class="text-charcoal-300">-</span>
                        </template>
                    </Column>
                    <Column header="Pause">
                        <template #body="{ data }">
                            <span v-if="data.break_duration > 0">{{ data.break_duration }} min</span>
                            <span v-else class="text-charcoal-300">-</span>
                        </template>
                    </Column>
                    <Column field="total_hours" header="Total (h)"></Column>
                    <Column header="Absence">
                        <template #body="{ data }">
                            <span v-if="data.absence_type" class="text-amber-600 text-xs px-2 py-1 bg-amber-100 rounded-full">{{ data.absence_type }}</span>
                            <span v-else class="text-charcoal-300">-</span>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </Dialog>
    </TcLayout>
</template>
