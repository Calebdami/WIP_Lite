<script setup>
import { Head } from '@inertiajs/vue3';
import CpLayout from '@/Layouts/CpLayout.vue';
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import ProgressBar from 'primevue/progressbar';

const props = defineProps({
    statsByCampaign: Array,
    evolution: Array,
    topEmployees: Array,
});
</script>

<template>
    <Head title="Tableau de Bord Reporting" />

    <CpLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Analyse de Performance (CP)</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <Card>
                        <template #title>Performance des Campagnes</template>
                        <template #content>
                            <div v-for="campaign in statsByCampaign" :key="campaign.name" class="mb-6">
                                <div class="flex justify-between mb-2">
                                    <span class="font-medium">{{ campaign.name }}</span>
                                    <span class="text-sm text-gray-500">{{ campaign.realized }}h / {{ campaign.planned }}h</span>
                                </div>
                                <ProgressBar :value="Math.min(100, (campaign.realized / campaign.planned) * 100)" :showValue="false" style="height: 10px" />
                                <div class="flex justify-end mt-1">
                                    <span class="text-xs" :class="campaign.gap >= 0 ? 'text-green-600' : 'text-red-600'">
                                        Écart: {{ campaign.gap > 0 ? '+' : '' }}{{ campaign.gap }}h
                                    </span>
                                </div>
                            </div>
                        </template>
                    </Card>

                    <Card>
                        <template #title>Top 5 de mon périmètre</template>
                        <template #content>
                            <DataTable :value="topEmployees" class="p-datatable-sm">
                                <Column field="name" header="Employé"></Column>
                                <Column field="hours" header="Heures réalisées"></Column>
                            </DataTable>
                        </template>
                    </Card>
                </div>

                <Card>
                    <template #title>Activité hebdomadaire de l'équipe</template>
                    <template #content>
                        <div class="h-64 flex items-end justify-around gap-2 px-4">
                            <div v-for="day in evolution" :key="day.date" class="flex flex-col items-center w-full">
                                <div class="flex gap-1 w-full justify-center">
                                    <div class="bg-blue-400 w-4 rounded-t" :style="{ height: (day.planned * 2) + 'px' }"></div>
                                    <div class="bg-green-400 w-4 rounded-t" :style="{ height: (day.realized * 2) + 'px' }"></div>
                                </div>
                                <span class="text-xs mt-2 text-gray-500">{{ day.date }}</span>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>
        </div>
    </CpLayout>
</template>
