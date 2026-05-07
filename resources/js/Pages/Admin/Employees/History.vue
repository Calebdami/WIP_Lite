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
    careerHistories: Object,
    evaluations: Object,
    contracts: Object,
    filters: Object,
});

const activeTab = ref('career');

// Custom debounce
const debounce = (fn, delay) => {
    let timeoutId;
    return (...args) => {
        if (timeoutId) clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
            fn(...args);
        }, delay);
    };
};

const search = ref(props.filters.search || '');

const updateFilters = debounce(() => {
    router.get(route('admin.employees.history'), {
        search: search.value,
    }, {
        preserveState: true,
        replace: true,
    });
}, 300);

watch(search, updateFilters);

const formatDate = (dateString, includeTime = false) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: includeTime ? '2-digit' : undefined,
        minute: includeTime ? '2-digit' : undefined
    });
};

const formatCurrency = (amount) => {
    if (!amount) return '-';
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(amount);
};
</script>

<template>
    <Head title="Historique des Employés — Admin" />
    <AdminLayout>
        <template #header>
            <div>
                <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Historique des Employés</h1>
                <p class="text-xs text-charcoal-400 mt-0.5">Audit complet : carrières, évaluations et documents contractuels</p>
            </div>
        </template>

        <!-- Search & Tabs Bar -->
        <div class="mb-6 bg-white p-4 rounded-xl border border-pearl-200 shadow-sm flex flex-wrap items-center justify-between gap-4">
            <div class="relative max-w-md w-full">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-charcoal-400">
                    <i class="pi pi-search text-xs"></i>
                </span>
                <InputText 
                    v-model="search"
                    placeholder="Rechercher un employé..." 
                    class="block w-full pl-10 text-xs border-pearl-200 focus:border-gold outline-none"
                />
            </div>

            <div class="flex bg-pearl-50 p-1 rounded-xl border border-pearl-100">
                <button 
                    v-for="tab in [{id: 'career', label: 'Carrière'}, {id: 'eval', label: 'Évaluations'}, {id: 'contracts', label: 'Contrats'}]"
                    :key="tab.id"
                    @click="activeTab = tab.id"
                    :class="activeTab === tab.id ? 'bg-white text-gold-600 shadow-sm border border-pearl-200' : 'text-charcoal-400 hover:text-charcoal-600'"
                    class="px-6 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all"
                >
                    {{ tab.label }}
                </button>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-pearl-200 shadow-premium p-6">
            <!-- 1. CAREER TAB -->
            <div v-if="activeTab === 'career'">
                <DataTable :value="careerHistories.data" responsiveLayout="scroll" class="p-datatable-sm" stripedRows>
                    <template #empty><div class="text-center p-8 text-charcoal-400">Aucun historique de carrière.</div></template>
                    <Column header="Date" style="width: 150px">
                        <template #body="{ data }">{{ formatDate(data.created_at, true) }}</template>
                    </Column>
                    <Column header="Employé">
                        <template #body="{ data }">
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-charcoal-700 uppercase">{{ data.employee?.first_name }} {{ data.employee?.last_name }}</span>
                                <span class="text-[9px] font-black text-gold-600 tracking-tighter">{{ data.employee?.matricule }}</span>
                            </div>
                        </template>
                    </Column>
                    <Column header="Évolution Poste">
                        <template #body="{ data }">
                            <div class="flex items-center gap-2 text-[11px]">
                                <span class="text-charcoal-400">{{ data.oldPosition?.name || 'Initial' }}</span>
                                <i class="pi pi-arrow-right text-[8px] text-gold-500"></i>
                                <span class="font-bold text-charcoal-700">{{ data.newPosition?.name }}</span>
                            </div>
                            <div class="mt-1 flex items-center gap-2">
                                <Tag :value="data.old_status?.toUpperCase() || '-'" severity="secondary" class="text-[8px]" />
                                <i class="pi pi-angle-right text-[8px] text-charcoal-300"></i>
                                <Tag :value="data.new_status?.toUpperCase()" severity="success" class="text-[8px]" />
                            </div>
                        </template>
                    </Column>
                    <Column header="Salaire">
                        <template #body="{ data }">
                            <div class="flex items-center gap-2 text-[11px]">
                                <span class="text-charcoal-400">{{ formatCurrency(data.old_salary) }}</span>
                                <i class="pi pi-arrow-right text-[8px] text-gold-500"></i>
                                <span class="font-black text-charcoal-800">{{ formatCurrency(data.new_salary) }}</span>
                            </div>
                            <div v-if="data.new_salary > data.old_salary" class="text-[9px] text-emerald-600 font-bold mt-0.5">
                                +{{ (((data.new_salary - data.old_salary) / data.old_salary) * 100).toFixed(1) }}%
                            </div>
                        </template>
                    </Column>
                    <Column header="Détails" class="text-right">
                        <template #body="{ data }">
                            <div class="text-[10px] text-charcoal-500 italic max-w-[150px] truncate" :title="data.reason">
                                {{ data.reason || 'Mise à jour dossier' }}
                            </div>
                            <div class="text-[8px] text-charcoal-400 mt-1 uppercase font-black">Par: {{ data.modifier?.name || 'Système' }}</div>
                        </template>
                    </Column>
                </DataTable>
            </div>

            <!-- 2. EVALUATIONS TAB -->
            <div v-if="activeTab === 'eval'">
                <DataTable :value="evaluations.data" responsiveLayout="scroll" class="p-datatable-sm" stripedRows>
                    <template #empty><div class="text-center p-8 text-charcoal-400">Aucune évaluation enregistrée.</div></template>
                    <Column header="Date" style="width: 150px">
                        <template #body="{ data }">{{ formatDate(data.evaluation_date) }}</template>
                    </Column>
                    <Column header="Employé">
                        <template #body="{ data }">
                            <span class="text-xs font-bold text-charcoal-700 uppercase">{{ data.employee?.first_name }} {{ data.employee?.last_name }}</span>
                        </template>
                    </Column>
                    <Column field="period" header="Période" class="text-xs font-medium" />
                    <Column header="Score">
                        <template #body="{ data }">
                            <div class="flex items-center gap-2">
                                <div class="w-20 bg-pearl-100 h-1.5 rounded-full overflow-hidden border border-pearl-200">
                                    <div class="bg-gold-gradient h-full" :style="{ width: data.score + '%' }"></div>
                                </div>
                                <span class="text-xs font-black text-charcoal-800">{{ data.score }}/100</span>
                            </div>
                        </template>
                    </Column>
                    <Column field="evaluator.name" header="Évaluateur" class="text-xs" />
                    <Column header="Commentaires">
                        <template #body="{ data }">
                            <p class="text-[10px] text-charcoal-500 italic max-w-xs truncate" :title="data.comments">{{ data.comments }}</p>
                        </template>
                    </Column>
                </DataTable>
            </div>

            <!-- 3. CONTRACTS TAB -->
            <div v-if="activeTab === 'contracts'">
                <DataTable :value="contracts.data" responsiveLayout="scroll" class="p-datatable-sm" stripedRows>
                    <template #empty><div class="text-center p-8 text-charcoal-400">Aucun contrat enregistré.</div></template>
                    <Column header="Date début" style="width: 150px">
                        <template #body="{ data }">{{ formatDate(data.start_date) }}</template>
                    </Column>
                    <Column header="Employé">
                        <template #body="{ data }">
                            <span class="text-xs font-bold text-charcoal-700 uppercase">{{ data.employee?.first_name }} {{ data.employee?.last_name }}</span>
                        </template>
                    </Column>
                    <Column header="Type">
                        <template #body="{ data }">
                            <Tag :value="data.type?.toUpperCase()" severity="info" class="text-[9px]" />
                        </template>
                    </Column>
                    <Column header="Date fin">
                        <template #body="{ data }">{{ formatDate(data.end_date) }}</template>
                    </Column>
                    <Column header="Statut">
                        <template #body="{ data }">
                            <Tag :value="data.status === 'active' ? 'EN COURS' : 'ARCHIVÉ'" :severity="data.status === 'active' ? 'success' : 'secondary'" class="text-[9px]" />
                        </template>
                    </Column>
                    <Column header="Actions" class="text-right">
                        <template #body>
                            <Button icon="pi pi-file-pdf" text severity="info" size="small" label="Document" class="text-[10px] font-bold" />
                        </template>
                    </Column>
                </DataTable>
            </div>

            <!-- Pagination simplified -->
            <div class="mt-8 flex justify-center border-t border-pearl-100 pt-6">
                <div class="flex gap-2">
                    <Link 
                        v-for="link in (activeTab === 'career' ? careerHistories.links : (activeTab === 'eval' ? evaluations.links : contracts.links))" 
                        :key="link.label"
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
</style>
