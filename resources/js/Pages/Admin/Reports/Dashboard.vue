<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Card from 'primevue/card';
import MeterGroup from 'primevue/metergroup';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import ProgressBar from 'primevue/progressbar';

const props = defineProps({
    statsByCampaign: Array,
    evolution: Array,
    topEmployees: Array,
});

const getGapSeverity = (gap) => {
    if (gap >= 0) return 'success';
    if (gap > -5) return 'warn';
    return 'danger';
};
</script>

<template>
    <Head title="Tableau de Bord Reporting" />

    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tableau de Bord Analytique</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Statistiques par Campagne -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <Card>
                        <template #title>Performance par Campagne</template>
                        <template #content>
                            <div v-for="campaign in statsByCampaign" :key="campaign.name" class="mb-6">
                                <div class="flex justify-between mb-2">
                                    <span class="font-medium">{{ campaign.name }}</span>
                                    <span class="text-sm text-gray-500">{{ campaign.realized }}h / {{ campaign.planned }}h</span>
                                </div>
                                <ProgressBar :value="Math.min(100, (campaign.realized / campaign.planned) * 100)" 
                                            :showValue="false" 
                                            style="height: 10px" />
                                <div class="flex justify-end mt-1">
                                    <span class="text-xs" :class="campaign.gap >= 0 ? 'text-green-600' : 'text-red-600'">
                                        Écart: {{ campaign.gap > 0 ? '+' : '' }}{{ campaign.gap }}h
                                    </span>
                                </div>
                            </div>
                        </template>
                    </Card>

                    <Card>
                        <template #title>Top 5 Employés (Heures)</template>
                        <template #content>
                            <DataTable :value="topEmployees" class="p-datatable-sm">
                                <Column field="name" header="Employé"></Column>
                                <Column field="hours" header="Heures">
                                    <template #body="slotProps">
                                        {{ slotProps.data.hours }}h
                                    </template>
                                </Column>
                            </DataTable>
                        </template>
                    </Card>
                </div>

                <!-- Évolution Temporelle -->
                <Card>
                    <template #title>Activité des 7 derniers jours</template>
                    <template #content>
                        <div class="h-64 flex items-end justify-around gap-2 px-4">
                            <div v-for="day in evolution" :key="day.date" class="flex flex-col items-center w-full">
                                <div class="flex gap-1 w-full justify-center">
                                    <div class="bg-blue-400 w-4 rounded-t" :style="{ height: (day.planned * 2) + 'px' }" v-tooltip="'Prévu: ' + day.planned"></div>
                                    <div class="bg-green-400 w-4 rounded-t" :style="{ height: (day.realized * 2) + 'px' }" v-tooltip="'Réalisé: ' + day.realized"></div>
                                </div>
                                <span class="text-xs mt-2 text-gray-500">{{ day.date }}</span>
                            </div>
                        </div>
                        <div class="flex justify-center gap-6 mt-4">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 bg-blue-400 rounded"></div>
                                <span class="text-xs text-gray-600">Prévu</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 bg-green-400 rounded"></div>
                                <span class="text-xs text-gray-600">Réalisé</span>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>
        </div>
    </AdminLayout>
</template>
