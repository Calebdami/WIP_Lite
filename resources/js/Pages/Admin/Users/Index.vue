<script setup>
/**
 * Importation des outils Inertia et Vue
 */
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { useToast } from 'primevue/usetoast';

/**
 * Composants PrimeVue
 */
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Toast from 'primevue/toast';

/**
 * Réception des données du serveur
 */
const props = defineProps({
    users: Object, 
    employeesWithoutAccount: Array,
    roles: Array
});

const toast = useToast();
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showConfirmModal = ref(false); // État pour la modale de confirmation
const userToEdit = ref(null);
const userToToggle = ref(null); // Utilisateur ciblé pour le changement de statut

// États pour la recherche et les filtres
const searchQuery = ref('');
const roleFilter = ref(null);
const statusFilter = ref(null);

/**
 * Logique de filtrage des utilisateurs
 */
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

/**
 * Formulaires
 */
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

/**
 * Actions
 */
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

/**
 * Ouvre la modale de confirmation pour le changement de statut
 */
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
            toast.add({ severity: 'success', summary: 'Succès', detail: 'Compte créé avec succès', life: 3000 });
            createForm.reset();
        }
    });
};

const submitUpdate = () => {
    editForm.patch(route('admin.users.update', userToEdit.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showEditModal.value = false;
            toast.add({ severity: 'success', summary: 'Succès', detail: 'Rôle mis à jour', life: 3000 });
        }
    });
};

/**
 * Exécute le changement de statut après confirmation
 */
const handleToggleStatus = () => {
    if (!userToToggle.value) return;

    router.patch(route('admin.users.toggle-status', userToToggle.value.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            showConfirmModal.value = false;
            toast.add({ severity: 'success', summary: 'Mis à jour', detail: 'Statut modifié', life: 3000 });
            userToToggle.value = null;
        }
    });
};
</script>

<template>
    <Head title="Comptes & Rôles" />
    <AdminLayout>
        <Toast />

        <template #header>
            <div class="flex justify-between items-center">
                <h1 class="text-xl font-bold text-charcoal-700">Comptes & Rôles</h1>
                <button @click="openCreate" class="bg-gold-gradient text-charcoal-900 px-6 py-2 rounded-lg font-black text-[10px] uppercase tracking-widest shadow-gold transition-transform hover:scale-105">
                    Créer un compte
                </button>
            </div>
        </template>

        <!-- Zone de Recherche et Filtres -->
        <div class="mt-6 mb-6 flex flex-wrap gap-4 items-end bg-white p-4 rounded-xl border border-pearl-200 shadow-sm">
            <div class="flex flex-col gap-1.5 flex-1 min-w-[200px]">
                <label class="text-[9px] font-black text-charcoal-400 uppercase tracking-widest">Rechercher</label>
                <span class="p-input-icon-left w-full">
                    <i class="pi pi-search text-charcoal-300 ml-3" />
                    <InputText v-model="searchQuery" placeholder="Nom ou email..." class="w-full pl-10 text-xs border-pearl-300 focus:border-gold" />
                </span>
            </div>

            <div class="flex flex-col gap-1.5 w-48">
                <label class="text-[9px] font-black text-charcoal-400 uppercase tracking-widest">Filtrer par Rôle</label>
                <Dropdown v-model="roleFilter" :options="roles" optionLabel="name" optionValue="id" placeholder="Tous les rôles" class="w-full text-xs" showClear />
            </div>

            <div class="flex flex-col gap-1.5 w-48">
                <label class="text-[9px] font-black text-charcoal-400 uppercase tracking-widest">Filtrer par Statut</label>
                <Dropdown v-model="statusFilter" :options="[{label: 'Actif', value: 'actif'}, {label: 'Inactif', value: 'inactif'}]" optionLabel="label" optionValue="value" placeholder="Tous les statuts" class="w-full text-xs" showClear />
            </div>
        </div>

        <!-- Tableau des utilisateurs -->
        <div class="bg-white rounded-xl border border-pearl-200 shadow-sm overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-pearl-50 border-b border-pearl-200">
                    <tr>
                        <th class="px-6 py-4 text-[10px] font-black text-charcoal-400 uppercase tracking-widest">Utilisateur</th>
                        <th class="px-6 py-4 text-[10px] font-black text-charcoal-400 uppercase tracking-widest">Email</th>
                        <th class="px-6 py-4 text-[10px] font-black text-charcoal-400 uppercase tracking-widest">Rôle</th>
                        <th class="px-6 py-4 text-[10px] font-black text-charcoal-400 uppercase tracking-widest text-center">Statut</th>
                        <th class="px-6 py-4 text-[10px] font-black text-charcoal-400 uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-pearl-100">
                    <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-pearl-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-charcoal-700 uppercase">
                                    <template v-if="user.employee">
                                        {{ user.employee.first_name }} {{ user.employee.last_name }}
                                    </template>
                                    <template v-else>
                                        {{ user.email.split('@')[0] }}
                                    </template>
                                </span>
                                <span class="text-[9px] text-charcoal-400 font-black tracking-tighter">ID: #{{ user.id }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-charcoal-600">{{ user.email }}</td>
                        <td class="px-6 py-4">
                            <span class="bg-pearl-100 text-charcoal-600 px-2 py-1 rounded text-[10px] font-black uppercase border border-pearl-200">
                                {{ user.role?.name || 'N/A' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span v-if="user.status === 'actif'" class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-[10px] font-black uppercase border border-green-200">ACTIF</span>
                            <span v-else class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-[10px] font-black uppercase border border-red-200">INACTIF</span>
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <button @click="openEdit(user)" class="text-charcoal-900 font-black text-[10px] uppercase mr-4 hover:underline">Modifier</button>
                            <!-- Appel de la confirmation au lieu du changement direct -->
                            <button @click="confirmToggleStatus(user)" 
                                    :class="user.status === 'actif' ? 'text-red-500' : 'text-green-600'"
                                    class="font-black text-[10px] uppercase hover:underline">
                                {{ user.status === 'actif' ? 'Désactiver' : 'Activer' }}
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="users.links.length > 3" class="px-6 py-4 bg-pearl-50 border-t border-pearl-200 flex justify-center gap-2">
                <template v-for="(link, k) in users.links" :key="k">
                    <div v-if="link.url === null" 
                         class="px-3 py-1 text-[10px] font-black text-charcoal-300 uppercase border border-transparent" 
                         v-html="link.label" />
                    <Link v-else 
                          :href="link.url" 
                          class="px-3 py-1 text-[10px] font-black uppercase rounded-md border transition-all"
                          :class="link.active ? 'bg-gold-gradient text-charcoal-900 border-gold shadow-sm' : 'bg-white text-charcoal-500 border-pearl-200 hover:border-gold'"
                          v-html="link.label" />
                </template>
            </div>
        </div>

        <!-- Modale de Confirmation (Désactivation/Activation) -->
        <Dialog v-model:visible="showConfirmModal" header="CONFIRMATION" :modal="true" :draggable="false" class="w-full max-w-sm mx-4">
            <div class="flex flex-col items-center text-center gap-4 py-4">
                <div class="w-12 h-12 rounded-full flex items-center justify-center bg-pearl-100">
                    <i class="pi pi-exclamation-triangle text-xl" :class="userToToggle?.status === 'actif' ? 'text-red-500' : 'text-green-600'"></i>
                </div>
                <div>
                    <p class="text-sm font-bold text-charcoal-700">
                        Êtes-vous sûr de vouloir {{ userToToggle?.status === 'actif' ? 'désactiver' : 'activer' }} ce compte ?
                    </p>
                    <p class="text-[10px] text-charcoal-400 mt-1 uppercase font-black">
                        {{ userToToggle?.email }}
                    </p>
                </div>
                <div class="flex gap-3 w-full mt-4">
                    <button @click="showConfirmModal = false" class="flex-1 px-4 py-2 text-[10px] font-black uppercase text-charcoal-400 bg-pearl-50 rounded-lg border border-pearl-200 hover:bg-pearl-100 transition-colors">
                        Annuler
                    </button>
                    <button @click="handleToggleStatus" 
                            :class="userToToggle?.status === 'actif' ? 'bg-red-500' : 'bg-green-600'"
                            class="flex-1 px-4 py-2 text-[10px] font-black uppercase text-white rounded-lg shadow-sm hover:opacity-90 transition-all">
                        Confirmer
                    </button>
                </div>
            </div>
        </Dialog>

        <!-- Modale de Création -->
        <Dialog v-model:visible="showCreateModal" header="NOUVEAU COMPTE" :modal="true" :draggable="false" class="w-full max-w-md mx-4 shadow-2xl">
            <form @submit.prevent="submitCreate" class="flex flex-col gap-4 mt-2">
                <div class="flex flex-col gap-1">
                    <label class="text-[10px] font-black text-charcoal-400 uppercase">Employé</label>
                    <Dropdown v-model="createForm.employee_id" :options="employeesWithoutAccount" optionValue="id" placeholder="Choisir..." class="w-full" filter>
                        <template #option="slot">{{ slot.option.first_name }} {{ slot.option.last_name }}</template>
                        <template #value="slot">
                            <span v-if="slot.value">{{ employeesWithoutAccount.find(e => e.id === slot.value)?.first_name }} {{ employeesWithoutAccount.find(e => e.id === slot.value)?.last_name }}</span>
                            <span v-else>Choisir...</span>
                        </template>
                    </Dropdown>
                    <small v-if="createForm.errors.employee_id" class="text-red-500 text-[9px] font-bold">{{ createForm.errors.employee_id }}</small>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-[10px] font-black text-charcoal-400 uppercase">Rôle</label>
                    <Dropdown v-model="createForm.role_id" :options="roles" optionLabel="name" optionValue="id" class="w-full" />
                    <small v-if="createForm.errors.role_id" class="text-red-500 text-[9px] font-bold">{{ createForm.errors.role_id }}</small>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-[10px] font-black text-charcoal-400 uppercase">Email</label>
                    <InputText v-model="createForm.email" class="w-full" />
                    <small v-if="createForm.errors.email" class="text-red-500 text-[9px] font-bold">{{ createForm.errors.email }}</small>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-[10px] font-black text-charcoal-400 uppercase">Mot de passe</label>
                    <Password v-model="createForm.password" :feedback="false" toggleMask class="w-full" inputClass="w-full" />
                    <small v-if="createForm.errors.password" class="text-red-500 text-[9px] font-bold">{{ createForm.errors.password }}</small>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-[10px] font-black text-charcoal-400 uppercase">Confirmation</label>
                    <Password v-model="createForm.password_confirmation" :feedback="false" toggleMask class="w-full" inputClass="w-full" />
                </div>
                <div class="flex justify-end gap-3 mt-4 pt-4 border-t border-pearl-100">
                    <button type="button" @click="showCreateModal = false" class="text-[10px] font-black uppercase text-charcoal-400 hover:text-charcoal-600 transition-colors">Annuler</button>
                    <button type="submit" :disabled="createForm.processing" class="bg-gold-gradient text-charcoal-900 px-8 py-2.5 rounded-lg font-black text-[10px] uppercase shadow-gold transition-all hover:scale-105 active:scale-95">
                        {{ createForm.processing ? 'Création...' : 'Créer' }}
                    </button>
                </div>
            </form>
        </Dialog>

        <!-- Modale de Modification -->
        <Dialog v-model:visible="showEditModal" header="MODIFIER LE RÔLE" :modal="true" :draggable="false" class="w-full max-w-sm mx-4">
            <form @submit.prevent="submitUpdate" class="flex flex-col gap-4 mt-2">
                <div class="flex flex-col gap-1">
                    <label class="text-[10px] font-black text-charcoal-400 uppercase">Nouveau Rôle</label>
                    <Dropdown v-model="editForm.role_id" :options="roles" optionLabel="name" optionValue="id" class="w-full" />
                </div>
                <div class="flex justify-end gap-3 mt-4 pt-4 border-t border-pearl-100">
                    <button type="button" @click="showEditModal = false" class="text-[10px] font-black uppercase text-charcoal-400 hover:text-charcoal-600 transition-colors">Annuler</button>
                    <button type="submit" :disabled="editForm.processing" class="bg-gold-gradient text-charcoal-900 px-8 py-2.5 rounded-lg font-black text-[10px] uppercase shadow-gold transition-all hover:scale-105">
                        Enregistrer
                    </button>
                </div>
            </form>
        </Dialog>

    </AdminLayout>
</template>

<style scoped>
.bg-gold-gradient { background: linear-gradient(135deg, #D4A017 0%, #8B6914 100%); }
.shadow-gold { box-shadow: 0 4px 14px 0 rgba(212, 160, 23, 0.25); }
:deep(.p-dropdown), :deep(.p-inputtext), :deep(.p-password-input) { border-color: #E2E8F0; border-radius: 0.5rem; }
:deep(.p-dropdown-label), :deep(.p-inputtext) { font-size: 0.75rem; }
</style>
