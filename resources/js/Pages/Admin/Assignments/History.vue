<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

// PrimeVue components
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';

const props = defineProps({
    history: Object,
    stats: Object,
    filters: {
        type: Object,
        default: () => ({ search: '' })
    }
});

const search = ref(props.filters?.search || '');

// Debounce for search
let timeout;
watch(search, (value) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('admin.assignments.history'), { search: value }, {
            preserveState: true,
            replace: true
        });
    }, 300);
});

const getStatusSeverity = (status) => {
    if (!status) return 'secondary';
    switch (status.toLowerCase()) {
        case 'en attente': return 'warn';
        case 'validé': return 'success';
        case 'suspendu': return 'danger';
        case 'actif': return 'success';
        case 'clôturé': return 'info';
        default: return 'secondary';
    }
};

const formatStatus = (status) => {
    if (!status) return 'Initial';
    return status.charAt(0).toUpperCase() + status.slice(1);
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};

const formatTime = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleTimeString('fr-FR', {
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Historique des Plannings — Admin" />
    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Historique des Plannings</h1>
                    <p class="text-xs text-charcoal-400 mt-0.5">Suivi chronologique des changements d'états et affectations</p>
                </div>
                <div class="relative max-w-md">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-charcoal-400">
                        <i class="pi pi-search text-xs"></i>
                    </span>
                    <InputText 
                        v-model="search"
                        placeholder="Rechercher un employé ou une raison..." 
                        class="block w-full pl-10 text-xs border-pearl-200 focus:border-gold outline-none"
                    />
                </div>
            </div>
        </template>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div v-for="stat in [
                { label: 'Total Événements', value: stats?.total || 0, icon: 'pi-history', color: 'charcoal' },
                { label: 'Créations', value: stats?.creations || 0, icon: 'pi-plus-circle', color: 'blue' },
                { label: 'Validations', value: stats?.validations || 0, icon: 'pi-check-circle', color: 'emerald' },
                { label: 'Suspensions', value: stats?.suspensions || 0, icon: 'pi-exclamation-circle', color: 'red' }
            ]" :key="stat.label" 
            class="bg-white rounded-2xl border border-pearl-200 p-6 shadow-sm flex items-center gap-4 hover:shadow-md transition-shadow">
                <div :class="`w-12 h-12 rounded-xl bg-${stat.color}-50 text-${stat.color}-500 flex items-center justify-center`">
                    <i :class="`pi ${stat.icon} text-lg`"></i>
                </div>
                <div>
                    <div class="text-2xl font-black text-charcoal-700">{{ stat.value }}</div>
                    <div class="text-[10px] font-bold text-charcoal-400 uppercase tracking-widest">{{ stat.label }}</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-pearl-200 shadow-premium overflow-hidden">
            <DataTable :value="history.data" responsiveLayout="scroll" class="p-datatable-sm" stripedRows>
                <template #empty>
                    <div class="text-center p-12 text-charcoal-400">
                        <i class="pi pi-inbox text-4xl mb-4 opacity-20"></i>
                        <p class="text-sm italic">Aucun historique trouvé.</p>
                    </div>
                </template>

                <Column header="Employé">
                    <template #body="{ data }">
                        <div v-if="data.assignment?.employee" class="flex flex-col">
                            <span class="text-xs font-bold text-charcoal-700 uppercase">
                                {{ data.assignment.employee.first_name }} {{ data.assignment.employee.last_name }}
                            </span>
                            <span class="text-[9px] font-black text-gold-600 tracking-tighter">
                                {{ data.assignment.employee.matricule }}
                            </span>
                        </div>
                        <span v-else class="text-charcoal-300 italic text-xs">Inconnu</span>
                    </template>
                </Column>

                <Column header="Planning">
                    <template #body="{ data }">
                        <div v-if="data.assignment?.planning_model" class="text-xs font-bold text-charcoal-600">
                            {{ data.assignment.planning_model.name }}
                        </div>
                        <span v-else class="text-charcoal-300">—</span>
                    </template>
                </Column>

                <Column header="Transition Statut">
                    <template #body="{ data }">
                        <div class="flex items-center gap-2">
                            <Tag :value="formatStatus(data.old_status)" :severity="getStatusSeverity(data.old_status)" class="text-[8px]" />
                            <i class="pi pi-arrow-right text-[8px] text-charcoal-300"></i>
                            <Tag :value="formatStatus(data.new_status)" :severity="getStatusSeverity(data.new_status)" class="text-[8px]" />
                        </div>
                    </template>
                </Column>

                <Column header="Validé par">
                    <template #body="{ data }">
                        <div v-if="data.author" class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full bg-pearl-100 flex items-center justify-center text-[10px] font-black text-gold-700 border border-pearl-200 uppercase">
                                {{ data.author.email.charAt(0) }}
                            </div>
                            <div class="text-[11px] font-bold text-charcoal-600 truncate max-w-[100px]">
                                {{ data.author.email.split('@')[0] }}
                            </div>
                        </div>
                        <span v-else class="text-charcoal-300 italic text-xs">Système</span>
                    </template>
                </Column>

                <Column header="Raison">
                    <template #body="{ data }">
                        <div class="text-[10px] text-charcoal-500 italic max-w-xs truncate" :title="data.reason">
                            {{ data.reason || 'Aucune raison spécifiée' }}
                        </div>
                    </template>
                </Column>

                <Column header="Date & Heure" class="text-right">
                    <template #body="{ data }">
                        <div class="text-xs font-bold text-charcoal-700">{{ formatDate(data.created_at) }}</div>
                        <div class="text-[10px] text-charcoal-400 font-medium">{{ formatTime(data.created_at) }}</div>
                    </template>
                </Column>
            </DataTable>

            <!-- Pagination -->
            <div v-if="history.links.length > 3" class="p-6 border-t border-pearl-100 flex justify-center">
                <div class="flex gap-2">
                    <Link 
                        v-for="(link, k) in history.links" 
                        :key="k"
                        :href="link.url || '#'"
                        v-html="link.label"
                        class="px-3.5 py-1.5 rounded-xl text-[10px] font-black uppercase transition-all border"
                        :class="{
                            'bg-gold-gradient text-charcoal-900 border-transparent shadow-gold-sm': link.active,
                            'bg-white text-charcoal-500 border-pearl-200 hover:border-gold-300 hover:text-gold-600': !link.active && link.url,
                            'opacity-40 cursor-not-allowed border-pearl-100 text-charcoal-300': !link.url
                        }"
                    />
                </div>
            </div>
        </div>

        <!-- Explanatory Note -->
        <div class="mt-8 bg-pearl-50 border border-pearl-200 rounded-2xl p-5 flex items-start gap-4">
            <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-gold-500 shadow-sm flex-shrink-0">
                <i class="pi pi-info-circle text-lg"></i>
            </div>
            <div class="text-[11px] leading-relaxed text-charcoal-500 italic">
                <span class="font-bold text-charcoal-700 not-italic block mb-1 uppercase tracking-widest text-[10px]">Traçabilité des plannings :</span>
                Chaque action effectuée sur une affectation de planning (création, validation, suspension, réactivation) est archivée dans cette table. Cela permet de garantir une traçabilité totale des changements d'état et des interventions des administrateurs ou chefs de plateau sur les ressources de l'entreprise.
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.bg-gold-gradient {
    background: linear-gradient(135deg, #D4A017 0%, #8B6914 100%);
}

.shadow-gold-sm {
    box-shadow: 0 4px 10px -5px rgba(212, 160, 23, 0.5);
}

.shadow-premium {
    box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05);
}

/* Colors for stats - manually adding classes since dynamic tailwind classes might be purged */
.bg-charcoal-50 { background-color: #f8fafc; }
.text-charcoal-500 { color: #64748b; }
.bg-blue-50 { background-color: #eff6ff; }
.text-blue-500 { color: #3b82f6; }
.bg-emerald-50 { background-color: #ecfdf5; }
.text-emerald-500 { color: #10b981; }
.bg-red-50 { background-color: #fef2f2; }
.text-red-500 { color: #ef4444; }
</style>
