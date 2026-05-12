<script setup>
import { ref } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import CpLayout from '@/Layouts/CpLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import Select from 'primevue/select';
import Message from 'primevue/message';

const props = defineProps({
    reports: Object,
    campaigns: Array,
    myManager: Object,
    filters: Object,
});

const showCreateDialog = ref(false);
const selectedReport = ref(null);
const showViewDialog = ref(false);

const form = useForm({
    report_date: new Date().toISOString().substr(0, 10),
    campaign_id: props.campaigns[0]?.id || '',
    tasks_completed: '',
    issues: '',
    next_day_plan: '',
});

const submitReport = () => {
    form.post(route('cp.reports.store'), {
        onSuccess: () => {
            showCreateDialog.value = false;
            form.reset('tasks_completed', 'issues', 'next_day_plan');
        },
    });
};

const viewReport = (report) => {
    selectedReport.value = report;
    showViewDialog.value = true;
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('fr-FR');
};
</script>

<template>
    <Head title="Rapports Journaliers" />

    <CpLayout>
        <template #header>
            <div class="flex justify-between items-center gap-2">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Rapports Journaliers (CP)</h2>
                <div class="flex gap-2">
                    <a :href="route('cp.reports.export')" class="p-button p-component p-button-secondary">
                        <span class="pi pi-download mr-2"></span> Exporter
                    </a>
                    <Button label="Nouveau Rapport" icon="pi pi-plus" @click="showCreateDialog = true" />
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Card>
                    <template #content>
                        <DataTable :value="reports.data" paginator :rows="10" tableStyle="min-width: 50rem">
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
                            <Column field="campaign.name" header="Campagne"></Column>
                            <Column field="tasks_completed" header="Tâches">
                                <template #body="slotProps">
                                    <span class="truncate block max-w-xs">{{ slotProps.data.tasks_completed }}</span>
                                </template>
                            </Column>
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

        <!-- Dialog Création -->
        <Dialog v-model:visible="showCreateDialog" modal header="Envoyer mon rapport journalier" :style="{ width: '50vw' }">
            <form @submit.prevent="submitReport" class="space-y-4">
                <div v-if="myManager" class="mb-4">
                    <Message severity="info">Ce rapport sera envoyé à : <strong>{{ myManager.first_name }} {{ myManager.last_name }}</strong></Message>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="report_date" value="Date du rapport" />
                        <TextInput id="report_date" type="date" class="mt-1 block w-full" v-model="form.report_date" required />
                        <InputError class="mt-2" :message="form.errors.report_date" />
                    </div>
                    <div>
                        <InputLabel for="campaign_id" value="Campagne" />
                        <Select v-model="form.campaign_id" :options="campaigns" optionLabel="name" optionValue="id" placeholder="Sélectionnez une campagne" class="w-full mt-1" required />
                        <InputError class="mt-2" :message="form.errors.campaign_id" />
                    </div>
                </div>

                <div>
                    <InputLabel for="tasks_completed" value="Tâches accomplies" />
                    <Textarea id="tasks_completed" class="mt-1 block w-full" v-model="form.tasks_completed" rows="3" required />
                    <InputError class="mt-2" :message="form.errors.tasks_completed" />
                </div>

                <div>
                    <InputLabel for="issues" value="Difficultés rencontrées" />
                    <Textarea id="issues" class="mt-1 block w-full" v-model="form.issues" rows="2" />
                    <InputError class="mt-2" :message="form.errors.issues" />
                </div>

                <div>
                    <InputLabel for="next_day_plan" value="Objectifs pour demain" />
                    <Textarea id="next_day_plan" class="mt-1 block w-full" v-model="form.next_day_plan" rows="2" />
                    <InputError class="mt-2" :message="form.errors.next_day_plan" />
                </div>

                <div class="flex justify-end gap-2">
                    <Button type="button" label="Annuler" severity="secondary" @click="showCreateDialog = false" />
                    <Button type="submit" label="Envoyer" :loading="form.processing" />
                </div>
            </form>
        </Dialog>

        <!-- Dialog Vue -->
        <Dialog v-model:visible="showViewDialog" modal :header="'Détails du rapport - ' + (selectedReport ? formatDate(selectedReport.report_date) : '')" :style="{ width: '50vw' }">
            <div v-if="selectedReport" class="space-y-6">
                <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg">
                    <div>
                        <p class="text-sm text-gray-500">Émetteur</p>
                        <p class="font-medium">{{ selectedReport.employee.first_name }} {{ selectedReport.employee.last_name }}</p>
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
    </CpLayout>
</template>
