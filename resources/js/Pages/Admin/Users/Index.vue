<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

// PrimeVue components
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import ConfirmDialogBox from '@/Components/ConfirmDialog.vue';

const props = defineProps({
    users: Object, 
    employeesWithoutAccount: Array,
    roles: Array
});

// Computed property pour les employés avec champ de recherche combiné
const employeesWithoutAccountWithSearch = computed(() => {
    return props.employeesWithoutAccount.map(emp => ({
        ...emp,
        searchField: `${emp.first_name} ${emp.last_name} ${emp.email}`.toLowerCase()
    }));
});

const showCreateModal = ref(false);
const showEditModal = ref(false);
const showConfirmModal = ref(false);
const userToEdit = ref(null);
const userToToggle = ref(null);

const searchQuery = ref('');
const roleFilter = ref(null);
const statusFilter = ref(null);

// Manual search functions
const triggerSearch = () => {
    // La recherche est déjà gérée par le computed property et le watch existant
    // Pas besoin de modification supplémentaire ici
};

// Handle Enter key press
const handleSearchKeydown = (event) => {
    if (event.key === 'Enter') {
        event.preventDefault();
        triggerSearch();
    }
};

const filteredUsers = computed(() => {
    return props.users.data.filter(user => {
        const fullName = user.employee 
            ? `${user.employee.first_name} ${user.employee.last_name}`.toLowerCase()
            : user.email.toLowerCase();
        const email = user.email.toLowerCase();
        const search = searchQuery.value.toLowerCase();
        
        const matchesSearch = fullName.includes(search) || email.includes(search);
        const matchesRole = !roleFilter.value || user.role_id === roleFilter.value;
        const matchesStatus = !statusFilter.value || user.status === statusFilter.value;
        
        return matchesSearch && matchesRole && matchesStatus;
    });
});

const createForm = useForm({
    employee_id: null,
    role_id: null,
    email: '',
    password: '',
    password_confirmation: '',
});

const editForm = useForm({
    role_id: null,
});

const openCreate = () => {
    createForm.reset();
    createForm.clearErrors();
    showCreateModal.value = true;
};

const openEdit = (user) => {
    userToEdit.value = user;
    editForm.role_id = user.role_id;
    editForm.clearErrors();
    showEditModal.value = true;
};

const confirmToggleStatus = (user) => {
    userToToggle.value = user;
    showConfirmModal.value = true;
};

watch(() => createForm.employee_id, (id) => {
    const emp = props.employeesWithoutAccount.find(e => e.id === id);
    if (emp) {
        createForm.email = emp.email;
        createForm.role_id = emp.position_id;
    }
});

const submitCreate = () => {
    createForm.post(route('admin.users.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        }
    });
};

const submitUpdate = () => {
    editForm.patch(route('admin.users.update', userToEdit.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showEditModal.value = false;
        }
    });
};

const handleToggleStatus = () => {
    if (!userToToggle.value) return;

    router.patch(route('admin.users.toggle-status', userToToggle.value.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            showConfirmModal.value = false;
            userToToggle.value = null;
        }
    });
};

const getStatusSeverity = (status) => {
    return status === 'actif' ? 'success' : 'danger';
};

const passwordConfirmClass = computed(() => {
    if (!createForm.password_confirmation) return 'w-full text-xs';
    const isMatch = createForm.password === createForm.password_confirmation;
    return `w-full text-xs transition-all duration-300 ${isMatch ? '!border-green-500 !bg-green-50 !text-green-700' : '!border-red-500 !bg-red-50 !text-red-700'}`;
});
</script>

<template>
    <Head title="Comptes & Rôles" />
    <AdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-xl font-bold text-charcoal-700">Comptes & Rôles</h1>
                    <p class="text-xs text-charcoal-400 mt-0.5">Gestion des accès utilisateurs et des permissions</p>
                </div>
                <Button label="Créer un compte" icon="pi pi-user-plus" 
                    severity="primary" 
                    @click="openCreate"
                    class="px-6 py-2.5 shadow-gold-premium font-black text-[10px] uppercase tracking-widest" />
            </div>
        </template>

        <!-- Search and Filters Bar -->
        <div class="mt-6 mb-6 grid grid-cols-1 md:grid-cols-3 gap-4 bg-white p-4 rounded-xl border border-pearl-200 shadow-sm">
            <div class="flex flex-col gap-1.5">
                <label class="text-[9px] font-black text-charcoal-400 uppercase tracking-widest">Rechercher</label>
                <span class="p-input-icon-left w-full relative">
                    <InputText v-model="searchQuery" placeholder="Nom ou email..." 
                        @keydown="handleSearchKeydown"
                        class="w-full pl-10 pr-20 text-xs border-pearl-300 focus:border-gold outline-none" />
                    <button @click="triggerSearch"
                        class="absolute inset-y-0 right-0 px-4 bg-gold-gradient text-white rounded-r-xl hover:bg-gold-700 transition-all">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </span>
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="text-[9px] font-black text-charcoal-400 uppercase tracking-widest">Filtrer par Rôle</label>
                <Dropdown v-model="roleFilter" :options="roles" optionLabel="name" optionValue="id" placeholder="Tous les rôles" class="w-full text-xs" showClear />
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="text-[9px] font-black text-charcoal-400 uppercase tracking-widest">Filtrer par Statut</label>
                <Dropdown v-model="statusFilter" :options="[{label: 'Actif', value: 'actif'}, {label: 'Inactif', value: 'inactif'}]" optionLabel="label" optionValue="value" placeholder="Tous les statuts" class="w-full text-xs" showClear />
            </div>
        </div>

        <!-- Users Table (PrimeVue DataTable) -->
        <div class="bg-white rounded-2xl border border-pearl-200 shadow-premium p-6">
            <DataTable 
                :value="filteredUsers" 
                responsiveLayout="scroll" 
                class="p-datatable-sm"
                stripedRows
            >
                <template #empty>
                    <div class="text-center p-8 text-charcoal-400 flex flex-col items-center gap-3">
                        <i class="pi pi-users text-4xl opacity-20"></i>
                        <p>Aucun utilisateur trouvé.</p>
                    </div>
                </template>
                <Column header="Utilisateur" sortable>
                    <template #body="{ data }">
                        <div class="flex flex-col">
                            <span class="text-sm font-bold text-charcoal-700 uppercase">
                                {{ data.employee ? `${data.employee.first_name} ${data.employee.last_name}` : data.email.split('@')[0] }}
                            </span>
                            <span class="text-[9px] text-charcoal-400 font-black tracking-tighter uppercase">ID: #{{ data.id }}</span>
                        </div>
                    </template>
                </Column>
                <Column field="email" header="Email" sortable>
                    <template #body="{ data }">
                        <span class="text-xs font-medium text-charcoal-600">{{ data.email }}</span>
                    </template>
                </Column>
                <Column header="Rôle" sortable field="role.name">
                    <template #body="{ data }">
                        <span class="bg-pearl-100 text-charcoal-600 px-2 py-1 rounded text-[9px] font-black uppercase border border-pearl-200">
                            {{ data.role?.name || 'N/A' }}
                        </span>
                    </template>
                </Column>
                <Column field="status" header="Statut" class="text-center">
                    <template #body="{ data }">
                        <Tag :value="data.status.toUpperCase()" :severity="getStatusSeverity(data.status)" class="text-[9px]" />
                    </template>
                </Column>
                <Column header="Actions" class="text-right" headerClass="flex justify-center">
                    <template #body="{ data }">
                        <div class="flex justify-end gap-3">
                            <button @click="openEdit(data)" class="text-charcoal-900 font-black text-[10px] uppercase hover:underline">Modifier</button>
                            <button @click="confirmToggleStatus(data)" 
                                    :class="data.status === 'actif' ? 'text-red-500' : 'text-green-600'"
                                    class="font-black text-[10px] uppercase hover:underline">
                                {{ data.status === 'actif' ? 'Désactiver' : 'Activer' }}
                            </button>
                        </div>
                    </template>
                </Column>
            </DataTable>

            <!-- Pagination (if needed, although computed filtering is done client-side here, props.users is paginated) -->
            <div v-if="users.links.length > 3" class="mt-6 flex items-center justify-between border-t border-pearl-100 pt-6">
                <div class="text-xs text-charcoal-400 font-medium uppercase tracking-widest">
                    Page <span class="text-charcoal-700 font-bold">{{ users.current_page }}</span> sur <span class="text-charcoal-700 font-bold">{{ users.last_page }}</span>
                </div>
                <div class="flex gap-2">
                    <Link 
                        v-for="link in users.links" 
                        :key="link.label"
                        :href="link.url || '#'"
                        v-html="link.label"
                        class="px-3 py-1.5 rounded-xl text-[10px] font-black uppercase transition-all border"
                        :class="{
                            'bg-gold-gradient text-charcoal-900 border-transparent shadow-gold-sm': link.active,
                            'bg-white text-charcoal-500 border-pearl-200 hover:border-gold hover:text-gold-600': !link.active && link.url,
                            'opacity-40 cursor-not-allowed border-pearl-100 text-charcoal-300': !link.url
                        }"
                    />
                </div>
            </div>
        </div>

        <!-- Confirm Status Toggle Modal -->
        <ConfirmDialogBox
            v-model="showConfirmModal"
            title="Confirmation"
            :description="userToToggle ? `Êtes-vous sûr de vouloir ${userToToggle.status === 'actif' ? 'désactiver' : 'activer'} ce compte ?` : ''"
            confirmLabel="Confirmer"
            cancelLabel="Annuler"
            :confirmSeverity="userToToggle?.status === 'actif' ? 'danger' : 'success'"
            icon="pi pi-exclamation-triangle"
            iconBgClass="bg-pearl-100"
            iconTextClass="text-charcoal-700"
            width="420px"
            :closable="false"
            className="max-w-sm"
            @confirm="handleToggleStatus"
            @cancel="showConfirmModal = false"
        >
            <p class="text-[10px] text-charcoal-400 mt-1 uppercase font-black">
                {{ userToToggle?.email }}
            </p>
        </ConfirmDialogBox>

        <!-- Create Account Modal -->
        <Dialog v-model:visible="showCreateModal" header="NOUVEAU COMPTE" :modal="true" :draggable="false" class="w-full max-w-md mx-4">
            <form @submit.prevent="submitCreate" class="flex flex-col gap-4 mt-2">
                <div class="flex flex-col gap-1">
                    <label class="text-[10px] font-black text-charcoal-400 uppercase">Employé</label>
                    <Dropdown v-model="createForm.employee_id" :options="employeesWithoutAccountWithSearch" optionValue="id" optionLabel="searchField" placeholder="Choisir un employé..." class="w-full text-xs" filter>
                        <template #option="slot">{{ slot.option.first_name }} {{ slot.option.last_name }}</template>
                        <template #value="slot">
                            <span v-if="slot.value">{{ employeesWithoutAccount.find(e => e.id === slot.value)?.first_name }} {{ employeesWithoutAccount.find(e => e.id === slot.value)?.last_name }}</span>
                            <span v-else>Choisir un employé...</span>
                        </template>
                    </Dropdown>
                    <small v-if="createForm.errors.employee_id" class="text-red-500 text-[9px] font-bold">{{ createForm.errors.employee_id }}</small>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-[10px] font-black text-charcoal-400 uppercase">Rôle</label>
                    <Dropdown v-model="createForm.role_id" :options="roles" optionLabel="name" optionValue="id" class="w-full text-xs" />
                    <small v-if="createForm.errors.role_id" class="text-red-500 text-[9px] font-bold">{{ createForm.errors.role_id }}</small>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-[10px] font-black text-charcoal-400 uppercase">Email</label>
                    <InputText v-model="createForm.email" class="w-full text-xs" />
                    <small v-if="createForm.errors.email" class="text-red-500 text-[9px] font-bold">{{ createForm.errors.email }}</small>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-[10px] font-black text-charcoal-400 uppercase">Mot de passe</label>
                    <Password v-model="createForm.password" :feedback="false" toggleMask class="w-full" inputClass="w-full text-xs" />
                    <small v-if="createForm.errors.password" class="text-red-500 text-[9px] font-bold">{{ createForm.errors.password }}</small>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-[10px] font-black text-charcoal-400 uppercase">Confirmation</label>
                    <Password v-model="createForm.password_confirmation" :feedback="false" toggleMask class="w-full" :inputClass="passwordConfirmClass" />
                    <small v-if="createForm.password_confirmation && createForm.password !== createForm.password_confirmation" class="text-red-500 text-[9px] font-bold">Les mots de passe ne correspondent pas</small>
                </div>
                <div class="flex justify-end gap-3 mt-4 pt-4 border-t border-pearl-100">
                    <Button label="Annuler" text severity="secondary" @click="showCreateModal = false" class="text-[10px] uppercase font-black" />
                    <Button label="Créer le compte" severity="primary" type="submit" :loading="createForm.processing" class="px-8 shadow-gold-premium text-[10px] uppercase font-black" />
                </div>
            </form>
        </Dialog>

        <!-- Edit Role Modal -->
        <Dialog v-model:visible="showEditModal" header="MODIFIER LE RÔLE" :modal="true" :draggable="false" class="w-full max-w-sm mx-4">
            <form @submit.prevent="submitUpdate" class="flex flex-col gap-4 mt-2">
                <div class="flex flex-col gap-1">
                    <label class="text-[10px] font-black text-charcoal-400 uppercase">Nouveau Rôle</label>
                    <Dropdown v-model="editForm.role_id" :options="roles" optionLabel="name" optionValue="id" class="w-full text-xs" />
                </div>
                <div class="flex justify-end gap-3 mt-4 pt-4 border-t border-pearl-100">
                    <Button label="Annuler" text severity="secondary" @click="showEditModal = false" class="text-[10px] uppercase font-black" />
                    <Button label="Enregistrer" severity="primary" type="submit" :loading="editForm.processing" class="px-8 shadow-gold-premium text-[10px] uppercase font-black" />
                </div>
            </form>
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

:deep(.p-dropdown), :deep(.p-inputtext), :deep(.p-password-input) {
    border-color: #E2E8F0;
    border-radius: 0.75rem;
}
</style>
