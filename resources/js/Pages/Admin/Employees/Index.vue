<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    employees: Object,
    filters: Object,
    roles: Array,
    positions: Array,
});

// Custom debounce function to avoid lodash dependency
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

watch([search, role_id, status, assignment_status], updateFilters);

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
                <button 
                    @click="openCreateModal"
                    class="px-4 py-2 bg-gold-gradient text-charcoal-900 rounded-lg text-xs font-bold shadow-gold hover:opacity-90 transition-opacity"
                >
                    + Ajouter un employé
                </button>
            </div>
        </template>

        <!-- Filters Bar -->
        <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4 bg-white p-4 rounded-xl border border-pearl-200 shadow-sm">
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-charcoal-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input 
                    v-model="search"
                    type="text" 
                    placeholder="Rechercher (Nom, matricule...)" 
                    class="block w-full pl-10 pr-3 py-2 border border-pearl-200 rounded-lg text-xs focus:ring-gold-500 focus:border-gold-500"
                />
            </div>
            
            <select v-model="role_id" class="block w-full py-2 border border-pearl-200 rounded-lg text-xs focus:ring-gold-500 focus:border-gold-500">
                <option value="">Tous les rôles</option>
                <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name.toUpperCase() }}</option>
            </select>

            <select v-model="status" class="block w-full py-2 border border-pearl-200 rounded-lg text-xs focus:ring-gold-500 focus:border-gold-500">
                <option value="">Tous les statuts</option>
                <option value="actif">Actif</option>
                <option value="inactif">Inactif</option>
                <option value="suspendu">Suspendu</option>
            </select>

            <select v-model="assignment_status" class="block w-full py-2 border border-pearl-200 rounded-lg text-xs focus:ring-gold-500 focus:border-gold-500">
                <option value="">Statut d'affectation</option>
                <option value="assigned">Affecté</option>
                <option value="not_assigned">Non affecté</option>
            </select>
        </div>

        <!-- Employees Table -->
        <div class="bg-white rounded-xl border border-pearl-200 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-pearl-50 border-b border-pearl-200">
                            <th class="px-6 py-4 text-[10px] font-bold text-charcoal-400 uppercase tracking-widest">Matricule / Nom</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-charcoal-400 uppercase tracking-widest">Poste / Rôle</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-charcoal-400 uppercase tracking-widest">Contact</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-charcoal-400 uppercase tracking-widest">Affectation</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-charcoal-400 uppercase tracking-widest">Statut</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-charcoal-400 uppercase tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-pearl-100">
                        <tr v-for="emp in employees.data" :key="emp.id" class="hover:bg-pearl-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="text-[10px] font-bold text-gold-600 mb-0.5">{{ emp.matricule }}</div>
                                <div class="text-sm font-bold text-charcoal-700">{{ emp.first_name }} {{ emp.last_name }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-xs font-semibold text-charcoal-700">{{ emp.position?.name }}</div>
                                <div class="text-[10px] text-charcoal-400 uppercase">{{ emp.user?.role?.name }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-xs text-charcoal-600">{{ emp.email }}</div>
                                <div class="text-[10px] text-charcoal-400">{{ emp.phone }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    <span :class="getAssignmentBadge(emp) === 'Affecté' ? 'text-gold-600' : 'text-charcoal-400'" class="text-[10px] font-bold uppercase tracking-tighter">
                                        {{ getAssignmentBadge(emp) }}
                                    </span>
                                    <div class="text-[10px] text-charcoal-400 truncate max-w-[120px]" v-if="getCampaignName(emp) !== '-'">
                                        {{ getCampaignName(emp) }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="{
                                    'bg-emerald-100 text-emerald-700': emp.status === 'actif',
                                    'bg-red-100 text-red-700': emp.status === 'inactif',
                                    'bg-amber-100 text-amber-700': emp.status === 'suspendu',
                                }" class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase">
                                    {{ emp.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-3">
                                <button 
                                    @click="openViewModal(emp)"
                                    class="text-emerald-600 font-bold hover:underline text-xs"
                                >
                                    Voir
                                </button>
                                <button 
                                    @click="openEditModal(emp)"
                                    class="text-charcoal-900 font-bold hover:text-gold-600 transition-colors text-xs"
                                >
                                    Modifier
                                </button>
                            </td>
                        </tr>
                        <tr v-if="employees.data.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-charcoal-400 italic text-sm">
                                Aucun employé trouvé pour ces critères.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="px-6 py-4 bg-pearl-50 border-t border-pearl-200 flex items-center justify-between">
                <div class="text-xs text-charcoal-400">
                    Affichage de {{ employees.from }} à {{ employees.to }} sur {{ employees.total }} résultats
                </div>
                <div class="flex gap-2">
                    <Link 
                        v-for="link in employees.links" 
                        :key="link.label"
                        :href="link.url || '#'"
                        v-html="link.label"
                        class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all"
                        :class="{
                            'bg-gold-gradient text-charcoal-900 shadow-sm': link.active,
                            'bg-white text-charcoal-600 border border-pearl-200 hover:bg-pearl-100': !link.active && link.url,
                            'opacity-50 cursor-not-allowed': !link.url
                        }"
                    />
                </div>
            </div>
        </div>

        <!-- Modal (Create/Edit) -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div v-if="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-charcoal-900/80 backdrop-blur-sm">
                <div class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden border border-pearl-200">
                    <div class="px-6 py-4 bg-pearl-50 border-b border-pearl-200 flex justify-between items-center">
                        <h2 class="text-lg font-black text-charcoal-700">
                            {{ editingEmployee ? 'Modifier l\'employé' : 'Nouvel employé' }}
                        </h2>
                        <button @click="showModal = false" class="text-charcoal-400 hover:text-charcoal-600">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form @submit.prevent="submit" class="p-6 overflow-y-auto max-h-[70vh]">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Identité -->
                            <div class="md:col-span-2 text-[10px] font-bold text-gold-600 uppercase tracking-widest border-b border-pearl-100 pb-1 mb-2">
                                Identité
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-charcoal-400 uppercase mb-1">Prénom</label>
                                <input v-model="form.first_name" type="text" class="w-full px-3 py-2 border border-pearl-200 rounded-lg text-xs focus:ring-gold-500 focus:border-gold-500" :disabled="editingEmployee" />
                                <div v-if="form.errors.first_name" class="text-red-500 text-[10px] mt-1">{{ form.errors.first_name }}</div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-charcoal-400 uppercase mb-1">Nom</label>
                                <input v-model="form.last_name" type="text" class="w-full px-3 py-2 border border-pearl-200 rounded-lg text-xs focus:ring-gold-500 focus:border-gold-500" :disabled="editingEmployee" />
                                <div v-if="form.errors.last_name" class="text-red-500 text-[10px] mt-1">{{ form.errors.last_name }}</div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-charcoal-400 uppercase mb-1">Date de naissance</label>
                                <input v-model="form.birth_date" type="date" class="w-full px-3 py-2 border border-pearl-200 rounded-lg text-xs focus:ring-gold-500 focus:border-gold-500" :disabled="editingEmployee" />
                                <div v-if="form.errors.birth_date" class="text-red-500 text-[10px] mt-1">{{ form.errors.birth_date }}</div>
                            </div>

                            <!-- Contact -->
                            <div class="md:col-span-2 text-[10px] font-bold text-gold-600 uppercase tracking-widest border-b border-pearl-100 pb-1 mb-2 mt-2">
                                Contact
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-charcoal-400 uppercase mb-1">Email</label>
                                <input v-model="form.email" type="email" class="w-full px-3 py-2 border border-pearl-200 rounded-lg text-xs focus:ring-gold-500 focus:border-gold-500" />
                                <div v-if="form.errors.email" class="text-red-500 text-[10px] mt-1">{{ form.errors.email }}</div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-charcoal-400 uppercase mb-1">Téléphone</label>
                                <input v-model="form.phone" type="text" class="w-full px-3 py-2 border border-pearl-200 rounded-lg text-xs focus:ring-gold-500 focus:border-gold-500" />
                                <div v-if="form.errors.phone" class="text-red-500 text-[10px] mt-1">{{ form.errors.phone }}</div>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-bold text-charcoal-400 uppercase mb-1">Adresse</label>
                                <textarea v-model="form.address" rows="2" class="w-full px-3 py-2 border border-pearl-200 rounded-lg text-xs focus:ring-gold-500 focus:border-gold-500"></textarea>
                                <div v-if="form.errors.address" class="text-red-500 text-[10px] mt-1">{{ form.errors.address }}</div>
                            </div>

                            <!-- Professionnel -->
                            <div class="md:col-span-2 text-[10px] font-bold text-gold-600 uppercase tracking-widest border-b border-pearl-100 pb-1 mb-2 mt-2">
                                Professionnel
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-charcoal-400 uppercase mb-1">Date d'embauche</label>
                                <input v-model="form.hire_date" type="date" class="w-full px-3 py-2 border border-pearl-200 rounded-lg text-xs focus:ring-gold-500 focus:border-gold-500" :disabled="editingEmployee" />
                                <div v-if="form.errors.hire_date" class="text-red-500 text-[10px] mt-1">{{ form.errors.hire_date }}</div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-charcoal-400 uppercase mb-1">Poste</label>
                                <select v-model="form.position_id" class="w-full px-3 py-2 border border-pearl-200 rounded-lg text-xs focus:ring-gold-500 focus:border-gold-500">
                                    <option value="">Sélectionner un poste</option>
                                    <option v-for="pos in positions" :key="pos.id" :value="pos.id">{{ pos.name }}</option>
                                </select>
                                <div v-if="form.errors.position_id" class="text-red-500 text-[10px] mt-1">{{ form.errors.position_id }}</div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-charcoal-400 uppercase mb-1">Salaire de base</label>
                                <input v-model="form.salary_base" type="number" step="0.01" class="w-full px-3 py-2 border border-pearl-200 rounded-lg text-xs focus:ring-gold-500 focus:border-gold-500" />
                                <div v-if="form.errors.salary_base" class="text-red-500 text-[10px] mt-1">{{ form.errors.salary_base }}</div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-charcoal-400 uppercase mb-1">Statut</label>
                                <select v-model="form.status" class="w-full px-3 py-2 border border-pearl-200 rounded-lg text-xs focus:ring-gold-500 focus:border-gold-500">
                                    <option value="actif">Actif</option>
                                    <option value="inactif">Inactif</option>
                                    <option value="suspendu">Suspendu</option>
                                </select>
                                <div v-if="form.errors.status" class="text-red-500 text-[10px] mt-1">{{ form.errors.status }}</div>
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end gap-3">
                            <button 
                                type="button"
                                @click="showModal = false"
                                class="px-4 py-2 text-xs font-bold text-charcoal-400 hover:text-charcoal-600 transition-colors"
                            >
                                Annuler
                            </button>
                            <button 
                                type="submit"
                                :disabled="form.processing"
                                class="px-6 py-2 bg-gold-gradient text-charcoal-900 rounded-lg text-xs font-bold shadow-gold hover:opacity-90 transition-opacity disabled:opacity-50"
                            >
                                {{ form.processing ? 'Enregistrement...' : (editingEmployee ? 'Mettre à jour' : 'Enregistrer') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>

        <!-- View Modal -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div v-if="showViewModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-charcoal-900/80 backdrop-blur-sm">
                <div class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden border border-pearl-200">
                    <div class="px-6 py-4 bg-pearl-50 border-b border-pearl-200 flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gold-gradient flex items-center justify-center text-charcoal-900 font-bold shadow-gold">
                                {{ selectedEmployee?.first_name?.charAt(0) }}{{ selectedEmployee?.last_name?.charAt(0) }}
                            </div>
                            <div>
                                <h2 class="text-lg font-black text-charcoal-700">Fiche Employé</h2>
                                <p class="text-[10px] font-bold text-gold-600 uppercase tracking-widest">{{ selectedEmployee?.matricule }}</p>
                            </div>
                        </div>
                        <button @click="showViewModal = false" class="text-charcoal-400 hover:text-charcoal-600">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="p-8 overflow-y-auto max-h-[80vh]">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Section Identité -->
                            <div>
                                <h3 class="text-[10px] font-bold text-charcoal-400 uppercase tracking-widest mb-4 border-b border-pearl-100 pb-1">Identité</h3>
                                <div class="space-y-4">
                                    <div>
                                        <p class="text-[9px] text-charcoal-400 uppercase font-bold">Nom complet</p>
                                        <p class="text-sm font-bold text-charcoal-700">{{ selectedEmployee?.first_name }} {{ selectedEmployee?.last_name }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[9px] text-charcoal-400 uppercase font-bold">Date de naissance</p>
                                        <p class="text-sm font-medium text-charcoal-700">{{ selectedEmployee?.birth_date }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[9px] text-charcoal-400 uppercase font-bold">Statut actuel</p>
                                        <span :class="{
                                            'bg-emerald-100 text-emerald-700': selectedEmployee?.status === 'actif',
                                            'bg-red-100 text-red-700': selectedEmployee?.status === 'inactif',
                                            'bg-amber-100 text-amber-700': selectedEmployee?.status === 'suspendu',
                                        }" class="inline-block mt-1 px-2 py-0.5 rounded-full text-[9px] font-bold uppercase">
                                            {{ selectedEmployee?.status }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Section Contact -->
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
                                    <div>
                                        <p class="text-[9px] text-charcoal-400 uppercase font-bold">Adresse</p>
                                        <p class="text-sm font-medium text-charcoal-700 leading-relaxed">{{ selectedEmployee?.address || 'Non renseignée' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Section Professionnelle -->
                            <div class="md:col-span-2 bg-pearl-50 rounded-xl p-5 border border-pearl-100 grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <p class="text-[9px] text-charcoal-400 uppercase font-bold">Poste</p>
                                    <p class="text-sm font-bold text-charcoal-700">{{ selectedEmployee?.position?.name }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] text-charcoal-400 uppercase font-bold">Date d'embauche</p>
                                    <p class="text-sm font-medium text-charcoal-700">{{ selectedEmployee?.hire_date }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] text-charcoal-400 uppercase font-bold">Salaire de base</p>
                                    <p class="text-sm font-black text-gold-600">{{ selectedEmployee?.salary_base }} €</p>
                                </div>
                                <div>
                                    <p class="text-[9px] text-charcoal-400 uppercase font-bold mb-1">Affectations actuelles</p>
                                    <div v-if="selectedEmployee?.assignments?.filter(a => a.status === 'active').length > 0" class="flex flex-wrap gap-2">
                                        <div v-for="assign in selectedEmployee.assignments.filter(a => a.status === 'active')" :key="assign.id" class="px-2 py-1 bg-gold-50 text-gold-700 rounded border border-gold-100 text-[10px] font-bold">
                                            {{ assign.campaign?.name }}
                                        </div>
                                    </div>
                                    <p v-else class="text-xs font-bold text-charcoal-700">-</p>
                                </div>
                                <div>
                                    <p class="text-[9px] text-charcoal-400 uppercase font-bold">Compte Utilisateur</p>
                                    <p class="text-xs font-medium text-charcoal-700">{{ selectedEmployee?.user ? 'Activé' : 'Aucun compte' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end">
                            <button 
                                @click="showViewModal = false"
                                class="px-6 py-2 bg-charcoal-900 text-white rounded-lg text-xs font-bold hover:bg-charcoal-800 transition-colors"
                            >
                                Fermer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>

<style scoped>
.bg-gold-gradient {
    background: linear-gradient(135deg, #D4A017 0%, #8B6914 100%);
}

.shadow-gold {
    box-shadow: 0 4px 14px 0 rgba(212, 160, 23, 0.39);
}

/* Custom scrollbar for form */
form::-webkit-scrollbar {
    width: 4px;
}
form::-webkit-scrollbar-track {
    background: #f1f1f1;
}
form::-webkit-scrollbar-thumb {
    background: #D4A017;
    border-radius: 10px;
}
</style>
