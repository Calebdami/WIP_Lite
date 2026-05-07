<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

// PrimeVue components
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import InputText from 'primevue/inputtext';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';

const assignmentHistory = ref([
    { id: 101, employee: 'Jean Dupont', old_team: 'Campagne Alpha', new_team: 'Campagne Gamma', type: 'Mutation', date: '2026-05-01', changed_by: 'Sophie Petit' },
    { id: 102, employee: 'Luc Leroy', old_team: '-', new_team: 'Campagne Alpha', type: 'Recrutement', date: '2026-05-02', changed_by: 'Admin' },
    { id: 103, employee: 'Marie Martin', old_team: 'Campagne Beta', new_team: 'Campagne Beta', type: 'Promotion', date: '2026-05-04', changed_by: 'Sophie Petit' },
]);

const filters = ref({
    global: { value: null, matchMode: 'contains' }
});

const getTypeSeverity = (type) => {
    switch (type) {
        case 'Mutation': return 'info';
        case 'Recrutement': return 'success';
        case 'Promotion': return 'warn';
        case 'Départ': return 'danger';
        default: return 'secondary';
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
</script>

<template>
    <Head title="Historique des Affectations — Admin" />
    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Historique des Affectations</h1>
                    <p class="text-xs text-charcoal-400 mt-0.5">Archive des mouvements et changements d'équipes</p>
                </div>
                <IconField iconPosition="left">
                    <InputIcon class="pi pi-search" />
                    <InputText v-model="filters['global'].value" placeholder="Rechercher un mouvement..." class="rounded-xl border-pearl-200 text-xs w-72" />
                </IconField>
            </div>
        </template>

        <div class="bg-white rounded-2xl border border-pearl-200 shadow-premium p-6">
            <DataTable :value="assignmentHistory" :filters="filters" paginator :rows="8" stripedRows responsiveLayout="scroll" class="p-datatable-sm">
                <template #empty>
                    <div class="text-center p-8 text-charcoal-400">Aucun historique d'affectation trouvé.</div>
                </template>
                <Column field="date" header="Date" sortable>
                    <template #body="{ data }">
                        <span class="font-mono text-xs font-bold text-charcoal-600">{{ formatDateDisplay(data.date) }}</span>
                    </template>
                </Column>
                <Column field="employee" header="Employé" sortable>
                    <template #body="{ data }">
                        <span class="font-bold text-charcoal-700">{{ data.employee }}</span>
                    </template>
                </Column>
                <Column field="type" header="Type de Mouvement" sortable>
                    <template #body="{ data }">
                        <Tag :value="data.type" :severity="getTypeSeverity(data.type)" />
                    </template>
                </Column>
                <Column header="Mouvement">
                    <template #body="{ data }">
                        <div class="flex items-center gap-2 text-xs">
                            <span class="text-charcoal-400 italic">{{ data.old_team }}</span>
                            <i class="pi pi-arrow-right text-[10px] text-gold-500"></i>
                            <span class="font-bold text-charcoal-700">{{ data.new_team }}</span>
                        </div>
                    </template>
                </Column>
                <Column field="changed_by" header="Validé par" sortable>
                    <template #body="{ data }">
                        <span class="text-xs font-medium text-charcoal-500">{{ data.changed_by }}</span>
                    </template>
                </Column>
            </DataTable>
        </div>
    </AdminLayout>
</template>
