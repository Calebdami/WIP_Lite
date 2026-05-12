<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Select from 'primevue/select';
import DatePicker from 'primevue/datepicker';

const props = defineProps({
    reports: Object,
    campaigns: Array,
    filters: Object,
});

const selectedReport = ref(null);
const showViewDialog = ref(false);

const viewReport = (report) => {
    selectedReport.value = report;
    showViewDialog.value = true;
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('fr-FR');
};
</script>

<template>
    <Head title="Tous les Rapports" />

    <AdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Console des Rapports Journaliers</h2>
                <a :href="route('admin.reports.export')" class="p-button p-component p-button-secondary">
                    <span class="pi pi-download mr-2"></span> Exporter (CSV)
                </a>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Card>
                    <template #content>
                        <DataTable :value="reports.data" paginator :rows="15" tableStyle="min-width: 50rem">
                            <Column field="report_date" header="Date">
                                <template #body="slotProps">
                                    {{ formatDate(slotProps.data.report_date) }}
                                </template>
                            </Column>
                            <Column field="employee" header="Émetteur">
                                <template #body="slotProps">
                                    <div class="flex flex-col">
                                        <span class="font-medium">{{ slotProps.data.employee.first_name }} {{ slotProps.data.employee.last_name }}</span>
                                        <span class="text-xs text-gray-500">{{ slotProps.data.employee.position?.name }}</span>
                                    </div>
                                </template>
                            </Column>
                            <Column field="manager" header="Destinataire">
                                <template #body="slotProps">
                                    <span v-if="slotProps.data.manager">{{ slotProps.data.manager.first_name }} {{ slotProps.data.manager.last_name }}</span>
                                    <span v-else class="text-gray-400 italic">Aucun</span>
                                </template>
                            </Column>
                            <Column field="campaign.name" header="Campagne"></Column>
                            <Column header="Actions">
                                <template #body="slotProps">
                                    <Button icon="pi pi-eye" text rounded @click="viewReport(slotProps.data)" />
                                </template>
                            </Column>
                        </DataTable>
                    </template>
                </Card>
            </div>
        </div>

        <!-- Dialog Vue -->
        <Dialog v-model:visible="showViewDialog" modal :header="'Détails du rapport - ' + (selectedReport ? formatDate(selectedReport.report_date) : '')" :style="{ width: '50vw' }">
            <div v-if="selectedReport" class="space-y-6">
                <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg">
                    <div>
                        <p class="text-sm text-gray-500">Émetteur</p>
                        <p class="font-medium">{{ selectedReport.employee.first_name }} {{ selectedReport.employee.last_name }}</p>
                        <p class="text-xs text-gray-400">{{ selectedReport.employee.position?.name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Campagne</p>
                        <p class="font-medium">{{ selectedReport.campaign.name }}</p>
                    </div>
                </div>

                <div>
                    <h4 class="font-bold text-gray-700 mb-2">Tâches accomplies</h4>
                    <p class="whitespace-pre-wrap text-gray-600">{{ selectedReport.tasks_completed }}</p>
                </div>

                <div v-if="selectedReport.issues">
                    <h4 class="font-bold text-gray-700 mb-2 text-red-600">Difficultés rencontrées</h4>
                    <p class="whitespace-pre-wrap text-gray-600">{{ selectedReport.issues }}</p>
                </div>

                <div v-if="selectedReport.next_day_plan">
                    <h4 class="font-bold text-gray-700 mb-2 text-blue-600">Objectifs pour demain</h4>
                    <p class="whitespace-pre-wrap text-gray-600">{{ selectedReport.next_day_plan }}</p>
                </div>
            </div>
        </Dialog>
    </AdminLayout>
</template>
