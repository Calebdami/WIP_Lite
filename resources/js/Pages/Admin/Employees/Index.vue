<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

// PrimeVue components
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';

const props = defineProps({
    employees: Object,
    filters: Object,
    roles: Array,
    positions: Array,
});

// Custom debounce function
const debounce = (fn, delay) => {
    let timeoutId;
    return (...args) => {
        if (timeoutId) clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
            fn(...args);
        }, delay);
    };
};

// Search & Filtering
const search = ref(props.filters.search || '');
const role_id = ref(props.filters.role_id || '');
const status = ref(props.filters.status || '');
const assignment_status = ref(props.filters.assignment_status || '');

// Manual search functions
const triggerSearch = () => {
    router.get(route('admin.employees.index'), {
        search: search.value,
        role_id: role_id.value,
        status: status.value,
        assignment_status: assignment_status.value,
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

// Custom debounce (for filters only, not search)
const updateFilters = debounce(() => {
    router.get(route('admin.employees.index'), {
        search: search.value,
        role_id: role_id.value,
        status: status.value,
        assignment_status: assignment_status.value,
    }, {
        preserveState: true,
        replace: true,
    });
}, 300);

// Only watch filters, not search
watch([role_id, status, assignment_status], updateFilters);

// Form Management (Add/Edit/View)
const showModal = ref(false);
const showViewModal = ref(false);
const editingEmployee = ref(null);
const selectedEmployee = ref(null);

const form = useForm({
    first_name: '',
    last_name: '',
    birth_date: '',
    email: '',
    phone: '',
    address: '',
    hire_date: '',
    position_id: '',
    salary_base: '',
    status: 'actif',
});

const openCreateModal = () => {
    editingEmployee.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const openEditModal = (employee) => {
    editingEmployee.value = employee;
    form.first_name = employee.first_name;
    form.last_name = employee.last_name;
    form.birth_date = employee.birth_date;
    form.email = employee.email;
    form.phone = employee.phone;
    form.address = employee.address;
    form.hire_date = employee.hire_date;
    form.position_id = employee.position_id;
    form.salary_base = employee.salary_base;
    form.status = employee.status;
    form.clearErrors();
    showModal.value = true;
};

const openViewModal = (employee) => {
    selectedEmployee.value = employee;
    showViewModal.value = true;
};

const submit = () => {
    if (editingEmployee.value) {
        form.put(route('admin.employees.update', editingEmployee.value.id), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    } else {
        form.post(route('admin.employees.store'), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    }
};

const getAssignmentBadge = (employee) => {
    const hasActive = employee.assignments?.some(a => a.status === 'active');
    return hasActive ? 'Affecté' : 'Non affecté';
};

const getCampaignName = (employee) => {
    const active = employee?.assignments?.filter(a => a.status === 'active') || [];
    if (active.length === 0) return '-';
    return active.map(a => a.campaign?.name).join(', ');
};

const getStatusSeverity = (status) => {
    switch (status?.toLowerCase()) {
        case 'actif': return 'success';
        case 'inactif': return 'danger';
        case 'suspendu': return 'warn';
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
                    <p class="text-xs text-charcoal-400 mt-0.5">Liste complète et gestion dynamique du personnel</p>
                </div>
                <Button label="Nouvel Employé" icon="pi pi-plus" 
                    severity="primary" 
                    @click="openCreateModal"
                    class="px-4 py-2 text-xs font-bold shadow-gold-premium" />
            </div>
        </template>

        <!-- Filters Bar -->
        <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4 bg-white p-4 rounded-xl border border-pearl-200 shadow-sm">
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-charcoal-400">
                    <i class="pi pi-search text-xs"></i>
                </span>
                <input 
                    v-model="search"
                    type="text" 
                    placeholder="Rechercher (Nom, matricule...)" 
                    @keydown="handleSearchKeydown"
                    class="block w-full pl-10 pr-20 py-2 border border-pearl-200 rounded-lg text-xs focus:ring-gold-500 focus:border-gold-500 outline-none"
                />
                <button @click="triggerSearch"
                    class="absolute inset-y-0 right-0 px-4 bg-gold-gradient text-white rounded-r-lg hover:bg-gold-700 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
            </div>
            
            <select v-model="role_id" class="block w-full py-2 border border-pearl-200 rounded-lg text-xs focus:ring-gold-500 focus:border-gold-500 outline-none">
                <option value="">Tous les rôles</option>
                <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name.toUpperCase() }}</option>
            </select>

            <select v-model="status" class="block w-full py-2 border border-pearl-200 rounded-lg text-xs focus:ring-gold-500 focus:border-gold-500 outline-none">
                <option value="">Tous les statuts</option>
                <option value="actif">Actif</option>
                <option value="inactif">Inactif</option>
                <option value="suspendu">Suspendu</option>
            </select>

            <select v-model="assignment_status" class="block w-full py-2 border border-pearl-200 rounded-lg text-xs focus:ring-gold-500 focus:border-gold-500 outline-none">
                <option value="">Statut d'affectation</option>
                <option value="assigned">Affecté</option>
                <option value="not_assigned">Non affecté</option>
            </select>
        </div>

        <!-- Employees Table (PrimeVue DataTable) -->
        <div class="bg-white rounded-2xl border border-pearl-200 shadow-premium p-6">
            <DataTable 
                :value="employees.data" 
                responsiveLayout="scroll" 
                class="p-datatable-sm"
                stripedRows
            >
                <template #empty>
                    <div class="text-center p-8 text-charcoal-400 flex flex-col items-center gap-3">
                        <i class="pi pi-users text-4xl opacity-20"></i>
                        <p>Aucun employé trouvé.</p>
                    </div>
                </template>
                <Column header="Matricule / Nom" sortable field="last_name">
                    <template #body="{ data }">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-pearl-100 flex items-center justify-center text-charcoal-700 font-black text-[10px] border border-pearl-200">
                                {{ data.first_name.charAt(0) }}{{ data.last_name.charAt(0) }}
                            </div>
                            <div class="flex flex-col">
                                <span class="text-[9px] font-bold text-gold-600 leading-none mb-0.5">{{ data.matricule }}</span>
                                <span class="font-bold text-charcoal-700">{{ data.first_name }} {{ data.last_name }}</span>
                            </div>
                        </div>
                    </template>
                </Column>
                <Column field="position.name" header="Poste">
                    <template #body="{ data }">
                        <div class="flex flex-col">
                            <span class="text-xs font-semibold text-charcoal-700">{{ data.position?.name || '-' }}</span>
                            <span class="text-[10px] text-charcoal-400 uppercase font-bold tracking-tighter">{{ data.user?.role?.name || 'SANS COMPTE' }}</span>
                        </div>
                    </template>
                </Column>
                <Column field="email" header="Contact">
                    <template #body="{ data }">
                        <div class="flex flex-col">
                            <span class="text-xs text-charcoal-600">{{ data.email }}</span>
                            <span class="text-[10px] text-charcoal-400">{{ data.phone || '-' }}</span>
                        </div>
                    </template>
                </Column>
                <Column header="Affectation">
                    <template #body="{ data }">
                        <div class="flex flex-col gap-1">
                            <span :class="getAssignmentBadge(data) === 'Affecté' ? 'text-gold-600' : 'text-charcoal-400'" class="text-[9px] font-black uppercase">
                                {{ getAssignmentBadge(data) }}
                            </span>
                            <div class="text-[10px] text-charcoal-400 truncate max-w-[150px]" v-if="getCampaignName(data) !== '-'">
                                {{ getCampaignName(data) }}
                            </div>
                        </div>
                    </template>
                </Column>
                <Column field="status" header="Statut" sortable>
                    <template #body="{ data }">
                        <Tag :value="data.status.toUpperCase()" :severity="getStatusSeverity(data.status)" class="text-[9px]" />
                    </template>
                </Column>
                <Column header="Actions" class="text-right">
                    <template #body="{ data }">
                        <div class="flex justify-end gap-1">
                            <Button icon="pi pi-eye" text severity="info" rounded @click="openViewModal(data)" title="Voir la fiche" />
                            <Button icon="pi pi-pencil" text severity="secondary" rounded @click="openEditModal(data)" title="Modifier" />
                        </div>
                    </template>
                </Column>
            </DataTable>

            <!-- Pagination -->
            <div class="mt-6 flex items-center justify-between border-t border-pearl-100 pt-6">
                <div class="text-xs text-charcoal-400 font-medium">
                    Affichage de <span class="text-charcoal-700 font-bold">{{ employees.from || 0 }}</span> à <span class="text-charcoal-700 font-bold">{{ employees.to || 0 }}</span> sur <span class="text-charcoal-700 font-bold">{{ employees.total || 0 }}</span> employés
                </div>
                <div class="flex gap-2">
                    <Link 
                        v-for="link in employees.links" 
                        :key="link.label"
                        :href="link.url || '#'"
                        v-html="link.label"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all border"
                        :class="{
                            'bg-gold-gradient text-charcoal-900 border-transparent shadow-gold-sm': link.active,
                            'bg-white text-charcoal-600 border-pearl-200 hover:border-gold-300 hover:text-gold-600': !link.active && link.url,
                            'opacity-40 cursor-not-allowed border-pearl-100 text-charcoal-300': !link.url
                        }"
                    />
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <Dialog v-model:visible="showModal" :header="editingEmployee ? 'Modifier l\'employé' : 'Nouvel employé'" :modal="true" :draggable="false" class="w-full max-w-2xl mx-4">
            <form @submit.prevent="submit" class="p-6 overflow-y-auto max-h-[70vh]">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Identité -->
                    <div class="md:col-span-2 text-[10px] font-bold text-gold-600 uppercase tracking-widest border-b border-pearl-100 pb-1 mb-2">
                        Identité
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-charcoal-400 uppercase mb-1">Prénom</label>
                        <InputText v-model="form.first_name" class="w-full text-xs" :disabled="!!editingEmployee" />
                        <div v-if="form.errors.first_name" class="text-red-500 text-[10px] mt-1">{{ form.errors.first_name }}</div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-charcoal-400 uppercase mb-1">Nom</label>
                        <InputText v-model="form.last_name" class="w-full text-xs" :disabled="!!editingEmployee" />
                        <div v-if="form.errors.last_name" class="text-red-500 text-[10px] mt-1">{{ form.errors.last_name }}</div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-charcoal-400 uppercase mb-1">Date de naissance</label>
                        <InputText v-model="form.birth_date" type="date" class="w-full text-xs" :disabled="!!editingEmployee" />
                        <div v-if="form.errors.birth_date" class="text-red-500 text-[10px] mt-1">{{ form.errors.birth_date }}</div>
                    </div>

                    <!-- Contact -->
                    <div class="md:col-span-2 text-[10px] font-bold text-gold-600 uppercase tracking-widest border-b border-pearl-100 pb-1 mb-2 mt-2">
                        Contact
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-charcoal-400 uppercase mb-1">Email</label>
                        <InputText v-model="form.email" type="email" class="w-full text-xs" />
                        <div v-if="form.errors.email" class="text-red-500 text-[10px] mt-1">{{ form.errors.email }}</div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-charcoal-400 uppercase mb-1">Téléphone</label>
                        <InputText v-model="form.phone" class="w-full text-xs" />
                        <div v-if="form.errors.phone" class="text-red-500 text-[10px] mt-1">{{ form.errors.phone }}</div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-bold text-charcoal-400 uppercase mb-1">Adresse</label>
                        <textarea v-model="form.address" rows="2" class="w-full px-3 py-2 border border-pearl-200 rounded-xl text-xs focus:ring-gold-500 focus:border-gold-500 outline-none"></textarea>
                    </div>

                    <!-- Professionnel -->
                    <div class="md:col-span-2 text-[10px] font-bold text-gold-600 uppercase tracking-widest border-b border-pearl-100 pb-1 mb-2 mt-2">
                        Professionnel
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-charcoal-400 uppercase mb-1">Date d'embauche</label>
                        <InputText v-model="form.hire_date" type="date" class="w-full text-xs" :disabled="!!editingEmployee" />
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-charcoal-400 uppercase mb-1">Poste</label>
                        <select v-model="form.position_id" class="w-full px-3 py-2 border border-pearl-200 rounded-xl text-xs focus:ring-gold-500 focus:border-gold-500 outline-none">
                            <option value="">Sélectionner un poste</option>
                            <option v-for="pos in positions" :key="pos.id" :value="pos.id">{{ pos.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-charcoal-400 uppercase mb-1">Salaire de base</label>
                        <InputText v-model="form.salary_base" type="number" step="0.01" class="w-full text-xs" />
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-charcoal-400 uppercase mb-1">Statut</label>
                        <select v-model="form.status" class="w-full px-3 py-2 border border-pearl-200 rounded-xl text-xs focus:ring-gold-500 focus:border-gold-500 outline-none">
                            <option value="actif">Actif</option>
                            <option value="inactif">Inactif</option>
                            <option value="suspendu">Suspendu</option>
                        </select>
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3 border-t border-pearl-100 pt-6">
                    <Button label="Annuler" text severity="secondary" @click="showModal = false" class="text-xs" />
                    <Button :label="editingEmployee ? 'Mettre à jour' : 'Enregistrer'" 
                        severity="primary" 
                        type="submit" 
                        :loading="form.processing"
                        class="px-6 py-2 shadow-gold-premium text-xs" />
                </div>
            </form>
        </Dialog>

        <!-- View Modal -->
        <Dialog v-model:visible="showViewModal" header="FICHE EMPLOYÉ" :modal="true" :draggable="false" class="w-full max-w-2xl mx-4">
            <div class="p-4 overflow-y-auto max-h-[80vh]">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-16 h-16 rounded-full bg-gold-gradient flex items-center justify-center text-charcoal-900 font-bold text-xl shadow-gold">
                        {{ selectedEmployee?.first_name?.charAt(0) }}{{ selectedEmployee?.last_name?.charAt(0) }}
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-charcoal-700">{{ selectedEmployee?.first_name }} {{ selectedEmployee?.last_name }}</h2>
                        <p class="text-[10px] font-bold text-gold-600 uppercase tracking-widest">{{ selectedEmployee?.matricule }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Identité -->
                    <div>
                        <h3 class="text-[10px] font-bold text-charcoal-400 uppercase tracking-widest mb-4 border-b border-pearl-100 pb-1">Identité</h3>
                        <div class="space-y-4">
                            <div>
                                <p class="text-[9px] text-charcoal-400 uppercase font-bold">Date de naissance</p>
                                <p class="text-sm font-medium text-charcoal-700">{{ selectedEmployee?.birth_date || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[9px] text-charcoal-400 uppercase font-bold">Statut actuel</p>
                                <Tag :value="selectedEmployee?.status?.toUpperCase()" :severity="getStatusSeverity(selectedEmployee?.status)" class="mt-1" />
                            </div>
                        </div>
                    </div>

                    <!-- Contact -->
                    <div>
                        <h3 class="text-[10px] font-bold text-charcoal-400 uppercase tracking-widest mb-4 border-b border-pearl-100 pb-1">Contact</h3>
                        <div class="space-y-4">
                            <div>
                                <p class="text-[9px] text-charcoal-400 uppercase font-bold">Email</p>
                                <p class="text-sm font-medium text-charcoal-700">{{ selectedEmployee?.email }}</p>
                            </div>
                            <div>
                                <p class="text-[9px] text-charcoal-400 uppercase font-bold">Téléphone</p>
                                <p class="text-sm font-medium text-charcoal-700">{{ selectedEmployee?.phone || 'Non renseigné' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 bg-pearl-50 rounded-2xl p-5 border border-pearl-100">
                    <h3 class="text-[10px] font-bold text-charcoal-400 uppercase tracking-widest mb-4 border-b border-pearl-200 pb-1">Professionnel</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <p class="text-[9px] text-charcoal-400 uppercase font-bold">Poste</p>
                            <p class="text-sm font-bold text-charcoal-700">{{ selectedEmployee?.position?.name || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[9px] text-charcoal-400 uppercase font-bold">Date d'embauche</p>
                            <p class="text-sm font-medium text-charcoal-700">{{ selectedEmployee?.hire_date || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[9px] text-charcoal-400 uppercase font-bold">Salaire de base</p>
                            <p class="text-sm font-black text-gold-600">{{ selectedEmployee?.salary_base ? selectedEmployee.salary_base + ' €' : '-' }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-end">
                    <Button label="Fermer" severity="primary" @click="showViewModal = false" class="px-8 shadow-gold-premium" />
                </div>
            </div>
        </Dialog>
    </AdminLayout>
</template>

<style scoped>
.bg-gold-gradient {
    background: linear-gradient(135deg, #D4A017 0%, #8B6914 100%);
}

.shadow-gold-premium {
    box-shadow: 0 10px 20px -10px rgba(212, 160, 23, 0.4);
}

.shadow-gold-sm {
    box-shadow: 0 4px 10px -5px rgba(212, 160, 23, 0.5);
}

.shadow-premium {
    box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05);
}
</style>
