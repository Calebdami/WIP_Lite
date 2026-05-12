<script setup>
import { Head } from '@inertiajs/vue3';
import TcLayout from '@/Layouts/TcLayout.vue';
import Card from 'primevue/card';
import ProgressBar from 'primevue/progressbar';

const props = defineProps({
    statsByCampaign: Array,
    evolution: Array,
});
</script>

<template>
    <Head title="Ma Performance" />

    <TcLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ma Performance Individuelle</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <Card>
                        <template #title>Mes Heures par Campagne</template>
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
                        <template #title>Résumé d'Activité</template>
                        <template #content>
                            <div class="flex flex-col gap-4">
                                <div class="p-4 bg-blue-50 rounded-lg">
                                    <p class="text-sm text-blue-600">Heures totales prévues (7j)</p>
                                    <p class="text-2xl font-bold">{{ evolution.reduce((acc, d) => acc + d.planned, 0) }}h</p>
                                </div>
                                <div class="p-4 bg-green-50 rounded-lg">
                                    <p class="text-sm text-green-600">Heures totales réalisées (7j)</p>
                                    <p class="text-2xl font-bold">{{ evolution.reduce((acc, d) => acc + d.realized, 0) }}h</p>
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>

                <Card>
                    <template #title>Activité des 7 derniers jours</template>
                    <template #content>
                        <div class="h-64 flex items-end justify-around gap-2 px-4">
                            <div v-for="day in evolution" :key="day.date" class="flex flex-col items-center w-full">
                                <div class="flex gap-1 w-full justify-center">
                                    <div class="bg-blue-400 w-6 rounded-t" :style="{ height: (day.planned * 10) + 'px' }"></div>
                                    <div class="bg-green-400 w-6 rounded-t" :style="{ height: (day.realized * 10) + 'px' }"></div>
                                </div>
                                <span class="text-xs mt-2 text-gray-500">{{ day.date }}</span>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>
        </div>
    </TcLayout>
</template>
