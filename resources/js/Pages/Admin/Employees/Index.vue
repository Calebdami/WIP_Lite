<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

// PrimeVue components
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import InputText from 'primevue/inputtext';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';

const employees = ref([
    { id: 1, first_name: 'Jean', last_name: 'Dupont', role: 'Téléconseiller', team: 'Campagne Alpha', status: 'actif' },
    { id: 2, first_name: 'Marie', last_name: 'Martin', role: 'Superviseur', team: 'Campagne Beta', status: 'actif' },
    { id: 3, first_name: 'Luc', last_name: 'Leroy', role: 'Téléconseiller', team: 'Campagne Alpha', status: 'inactif' },
    { id: 4, first_name: 'Sophie', last_name: 'Petit', role: 'Chef de Plateau', team: 'Multi-plateau', status: 'actif' },
]);

const filters = ref({
    global: { value: null, matchMode: 'contains' }
});

const getStatusSeverity = (status) => {
    switch (status.toLowerCase()) {
        case 'actif': return 'success';
        case 'inactif': return 'danger';
        default: return 'info';
    }
};
</script>

<template>
    <Head title="Gestion des Employés — Admin" />
    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Gestion des Employés</h1>
                    <p class="text-xs text-charcoal-400 mt-0.5">Liste complète et gestion du personnel du centre</p>
                </div>
                <div class="flex items-center gap-3">
                    <IconField iconPosition="left">
                        <InputIcon class="pi pi-search" />
                        <InputText v-model="filters['global'].value" placeholder="Rechercher..." class="rounded-xl border-pearl-200 text-xs w-64" />
                    </IconField>
                    <Button label="Nouvel Employé" icon="pi pi-plus" 
                        severity="primary"
                        class="px-5 py-2.5 shadow-gold-premium" />
                </div>
            </div>
        </template>

        <div class="bg-white rounded-2xl border border-pearl-200 shadow-premium p-6">
            <DataTable :value="employees" :filters="filters" paginator :rows="8" stripedRows responsiveLayout="scroll" class="p-datatable-sm">
                <template #empty>
                    <div class="text-center p-8 text-charcoal-400 flex flex-col items-center gap-3">
                        <i class="pi pi-users text-4xl opacity-20"></i>
                        <p>Aucun employé trouvé.</p>
                    </div>
                </template>
                <Column header="Employé" sortable field="last_name">
                    <template #body="{ data }">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-pearl-100 flex items-center justify-center text-charcoal-700 font-black text-xs border border-pearl-200 shadow-sm">
                                {{ data.first_name.charAt(0) }}{{ data.last_name.charAt(0) }}
                            </div>
                            <div class="flex flex-col">
                                <span class="font-bold text-charcoal-700">{{ data.first_name }} {{ data.last_name }}</span>
                                <span class="text-[10px] text-charcoal-400 font-medium">ID: #{{ String(data.id).padStart(4, '0') }}</span>
                            </div>
                        </div>
                    </template>
                </Column>
                <Column field="role" header="Rôle" sortable>
                    <template #body="{ data }">
                        <div class="flex items-center gap-2">
                            <i class="pi pi-briefcase text-[10px] text-gold-500"></i>
                            <span class="text-xs font-semibold text-charcoal-600">{{ data.role }}</span>
                        </div>
                    </template>
                </Column>
                <Column field="team" header="Équipe / Campagne" sortable>
                    <template #body="{ data }">
                        <Tag :value="data.team" severity="secondary" class="bg-pearl-100 text-charcoal-600 border border-pearl-200" />
                    </template>
                </Column>
                <Column field="status" header="Statut" sortable>
                    <template #body="{ data }">
                        <Tag :value="data.status.toUpperCase()" :severity="getStatusSeverity(data.status)" />
                    </template>
                </Column>
                <Column header="Actions">
                    <template #body="{ data }">
                        <div class="flex gap-1">
                            <Button icon="pi pi-pencil" text severity="secondary" rounded title="Modifier" />
                            <Button icon="pi pi-id-card" text severity="info" rounded title="Détails du contrat" />
                            <Button icon="pi pi-trash" text severity="danger" rounded title="Désactiver" />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>
    </AdminLayout>
</template>
