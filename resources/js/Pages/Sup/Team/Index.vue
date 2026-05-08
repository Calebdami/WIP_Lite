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
    <Head title="Mon Équipe — SUP" />
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

        <!-- Details Modal -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="showDetailsModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-charcoal-900/60 backdrop-blur-sm">
                <div class="bg-white rounded-2xl w-full max-w-md overflow-hidden shadow-2xl">
                    <div class="p-6 bg-charcoal-900 text-white flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-black tracking-tight">Fiche Téléconseiller</h3>
                            <p class="text-[10px] font-bold text-gold-400 uppercase tracking-widest">{{ selectedMember?.matricule }}</p>
                        </div>
                        <button @click="showDetailsModal = false" class="text-charcoal-400 hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="p-8 space-y-6">
                        <div class="flex items-center gap-6">
                            <div class="w-20 h-20 rounded-2xl bg-pearl-100 flex items-center justify-center text-charcoal-700 font-black text-2xl border border-pearl-200 shadow-inner">
                                {{ selectedMember?.first_name.charAt(0) }}{{ selectedMember?.last_name.charAt(0) }}
                            </div>
                            <div>
                                <h4 class="text-lg font-bold text-charcoal-700">{{ selectedMember?.first_name }} {{ selectedMember?.last_name }}</h4>
                                <div class="px-2 py-1 bg-pearl-100 rounded text-[9px] font-black uppercase text-charcoal-500 inline-block mt-1 tracking-widest">
                                    Téléconseiller
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 bg-pearl-50 p-5 rounded-xl border border-pearl-100">
                            <div>
                                <p class="text-[9px] text-charcoal-400 uppercase font-black tracking-widest">Email Professionnel</p>
                                <p class="text-sm font-bold text-charcoal-700 mt-0.5">{{ selectedMember?.email }}</p>
                            </div>
                            <div>
                                <p class="text-[9px] text-charcoal-400 uppercase font-black tracking-widest">Téléphone</p>
                                <p class="text-sm font-bold text-charcoal-700 mt-0.5">{{ selectedMember?.phone || 'Non renseigné' }}</p>
                            </div>
                        </div>

                        <button 
                            @click="showDetailsModal = false"
                            class="w-full py-3 bg-charcoal-900 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-charcoal-800 transition-all shadow-lg active:scale-95"
                        >
                            Fermer
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </SupLayout>
</template>
