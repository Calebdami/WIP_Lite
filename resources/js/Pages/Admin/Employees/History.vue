<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

// PrimeVue components
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import InputText from 'primevue/inputtext';
import LinkPagination from '@/Components/NavLink.vue'; // Using standard link logic if needed, but we'll use custom links

const props = defineProps({
    careerHistories: Object,
    filters: Object,
});

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

// Manual search functions
const triggerSearch = () => {
    router.get(route('admin.employees.history'), {
        search: search.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

// Handle Enter key press
const handleSearchKeydown = (event) => {
    if (event.key === 'Enter') {
        event.preventDefault();
        triggerSearch();
    }
};

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
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Historique des Employés</h1>
                    <p class="text-xs text-charcoal-400 mt-0.5">Suivi chronologique des carrières et changements de postes</p>
                </div>
            </div>
        </template>

        <!-- Search Bar -->
        <div class="mb-6 bg-white p-4 rounded-xl border border-pearl-200 shadow-sm flex items-center justify-between gap-4">
            <div class="relative max-w-md w-full">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-charcoal-400">
                    <i class="pi pi-search text-xs"></i>
                </span>
                <div class="relative max-w-md w-full">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-charcoal-400">
                    <i class="pi pi-search text-xs"></i>
                </span>
                <InputText 
                    v-model="search"
                    placeholder="Rechercher par nom, matricule..." 
                    @keydown="handleSearchKeydown"
                    class="block w-full pl-10 pr-20 text-xs border-pearl-200 focus:border-gold outline-none"
                />
                <button @click="triggerSearch"
                    class="absolute inset-y-0 right-0 px-4 bg-gold-gradient text-white rounded-r-lg hover:bg-gold-700 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
            </div>
            </div>
            <div class="flex items-center gap-2 text-[10px] font-bold text-charcoal-400 uppercase tracking-widest">
                <i class="pi pi-history text-gold-500"></i>
                Journal des carrières
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-pearl-200 shadow-premium p-6">
            <DataTable :value="careerHistories.data" responsiveLayout="scroll" class="p-datatable-sm" stripedRows>
                <template #empty><div class="text-center p-12 text-charcoal-400 italic">Aucun historique de carrière trouvé.</div></template>
                
                <Column header="Date & Heure" style="width: 150px">
                    <template #body="{ data }">
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-charcoal-700">{{ formatDate(data.created_at) }}</span>
                            <span class="text-[9px] text-charcoal-400 font-mono">{{ formatDate(data.created_at, true).split(' ')[1] }}</span>
                        </div>
                    </template>
                </Column>

                <Column header="Employé">
                    <template #body="{ data }">
                        <div class="flex flex-col">
                            <span class="text-xs font-black text-charcoal-700 uppercase tracking-tight">{{ data.employee?.first_name }} {{ data.employee?.last_name }}</span>
                            <span class="text-[9px] font-black text-gold-600 tracking-[0.1em]">{{ data.employee?.matricule }}</span>
                        </div>
                    </template>
                </Column>

                <Column header="Évolution du Poste">
                    <template #body="{ data }">
                        <div class="flex items-center gap-3 text-[11px]">
                            <span class="text-charcoal-400 bg-pearl-50 px-2 py-0.5 rounded border border-pearl-100">{{ data.old_position?.name || 'INITIAL' }}</span>
                            <i class="pi pi-arrow-right text-[9px] text-gold-500"></i>
                            <span class="font-black text-charcoal-800 bg-pearl-100 px-2 py-0.5 rounded border border-pearl-200">{{ data.new_position?.name }}</span>
                        </div>
                        <div class="mt-2 flex items-center gap-2">
                            <Tag :value="data.old_status?.toUpperCase() || '-'" severity="secondary" class="text-[8px] font-bold" />
                            <i class="pi pi-chevron-right text-[8px] text-charcoal-300"></i>
                            <Tag :value="data.new_status?.toUpperCase()" :severity="data.new_status === 'actif' ? 'success' : 'danger'" class="text-[8px] font-bold" />
                        </div>
                    </template>
                </Column>

                <Column header="Salaire">
                    <template #body="{ data }">
                        <div class="flex items-center gap-2 text-[11px]">
                            <span class="text-charcoal-400">{{ data.old_salary > 0 ? formatCurrency(data.old_salary) : '-' }}</span>
                            <i class="pi pi-arrow-right text-[8px] text-gold-500" v-if="data.old_salary > 0"></i>
                            <span class="font-black text-charcoal-800">{{ formatCurrency(data.new_salary) }}</span>
                        </div>
                        <div v-if="data.old_salary > 0 && data.new_salary > data.old_salary" class="text-[9px] text-emerald-600 font-bold mt-1">
                            <i class="pi pi-caret-up"></i> +{{ (((data.new_salary - data.old_salary) / data.old_salary) * 100).toFixed(1) }}%
                        </div>
                    </template>
                </Column>

                <Column header="Modification">
                    <template #body="{ data }">
                        <div class="text-[10px] text-charcoal-500 italic max-w-[180px] leading-relaxed mb-1" :title="data.reason">
                            {{ data.reason || 'Mise à jour dossier' }}
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="w-4 h-4 rounded-full bg-pearl-200 flex items-center justify-center text-[8px] text-charcoal-600">
                                <i class="pi pi-user"></i>
                            </div>
                            <span class="text-[8px] text-charcoal-400 uppercase font-black">{{ data.modifier?.name || 'Système' }}</span>
                        </div>
                    </template>
                </Column>
            </DataTable>

            <!-- Pagination -->
            <div class="mt-8 flex justify-center border-t border-pearl-50 pt-6">
                <div class="flex gap-2">
                    <button 
                        v-for="link in careerHistories.links" 
                        :key="link.label"
                        @click="link.url ? router.visit(link.url) : null"
                        v-html="link.label"
                        class="px-4 py-2 rounded-xl text-[10px] font-black uppercase transition-all border"
                        :disabled="!link.url"
                        :class="{
                            'bg-gold-gradient text-charcoal-900 border-transparent shadow-gold-sm': link.active,
                            'bg-white text-charcoal-500 border-pearl-200 hover:border-gold-300 hover:text-gold-600': !link.active && link.url,
                            'opacity-30 cursor-not-allowed border-pearl-50 text-charcoal-300': !link.url
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
    box-shadow: 0 4px 12px -2px rgba(212, 160, 23, 0.4);
}
.shadow-premium {
    box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05);
}
</style>
