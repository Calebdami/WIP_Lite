<script setup>
import SupLayout from '@/Layouts/SupLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

// PrimeVue components
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Avatar from 'primevue/avatar';

const team = ref([
    { id: 1, first_name: 'Alice', last_name: 'Durand', position: 'Téléconseiller', status: 'En ligne' },
    { id: 2, first_name: 'Bob', last_name: 'Lemoine', position: 'Téléconseiller', status: 'En pause' },
    { id: 3, first_name: 'Charlie', last_name: 'Vidal', position: 'Téléconseiller', status: 'Hors ligne' },
]);

const getStatusSeverity = (status) => {
    switch (status) {
        case 'En ligne': return 'success';
        case 'En pause': return 'warn';
        case 'Hors ligne': return 'secondary';
        default: return 'info';
    }
};
</script>

<template>
    <Head title="Mon Équipe — Superviseur" />
    <SupLayout>
        <template #header>
            <div>
                <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Mon Équipe</h1>
                <p class="text-xs text-charcoal-400 mt-0.5">Suivi en temps réel des agents sous votre supervision</p>
            </div>
        </template>

        <div class="bg-white rounded-2xl border border-pearl-200 shadow-premium p-6">
            <DataTable :value="team" stripedRows responsiveLayout="scroll" class="p-datatable-sm" paginator :rows="8">
                <template #empty>
                    <div class="text-center p-8 text-charcoal-400">Aucun agent assigné.</div>
                </template>
                
                <Column header="Agent" sortable field="last_name">
                    <template #body="{ data }">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-pearl-100 flex items-center justify-center text-charcoal-700 font-bold text-xs border border-pearl-200">
                                {{ data.first_name.charAt(0) }}{{ data.last_name.charAt(0) }}
                            </div>
                            <span class="font-bold text-charcoal-700">{{ data.first_name }} {{ data.last_name }}</span>
                        </div>
                    </template>
                </Column>
                
                <Column field="position" header="Poste" sortable>
                    <template #body="{ data }">
                        <span class="text-xs font-semibold text-charcoal-600">{{ data.position }}</span>
                    </template>
                </Column>
                
                <Column field="status" header="Statut" sortable>
                    <template #body="{ data }">
                        <Tag :value="data.status.toUpperCase()" :severity="getStatusSeverity(data.status)" class="text-[10px]" />
                    </template>
                </Column>
                
                <Column header="Actions">
                    <template #body>
                        <div class="flex gap-1">
                            <Button icon="pi pi-chart-bar" text severity="info" rounded title="Performance" />
                            <Button icon="pi pi-calendar" text severity="secondary" rounded title="Planning" />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>
    </SupLayout>
</template>
