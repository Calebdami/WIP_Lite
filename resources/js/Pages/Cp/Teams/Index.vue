<script setup>
import CpLayout from '@/Layouts/CpLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    supervisors: Array
});

const selectedMember = ref(null);
const showDetailsModal = ref(false);

const openDetails = (member) => {
    selectedMember.value = member;
    showDetailsModal.value = true;
};
</script>

<template>
    <Head title="Mes Équipes — CP" />
    <CpLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Mes Équipes</h1>
                    <p class="text-xs text-charcoal-400 mt-0.5">Visualisez vos superviseurs et leurs téléconseillers par campagne</p>
                </div>
            </div>
        </template>

        <div class="p-6">
            <div v-if="supervisors.length === 0" class="bg-white rounded-2xl border border-pearl-200 p-12 text-center shadow-sm">
                <div class="w-16 h-16 bg-pearl-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-pearl-100">
                    <svg class="w-8 h-8 text-charcoal-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <h3 class="text-charcoal-700 font-bold">Aucune équipe affectée</h3>
                <p class="text-xs text-charcoal-400 mt-1">Vous n'avez pas encore de superviseurs affectés sous votre responsabilité.</p>
            </div>

            <div v-else class="grid grid-cols-1 gap-8">
                <div v-for="sup in supervisors" :key="sup.id" class="bg-white rounded-2xl border border-pearl-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                    <!-- Supervisor Header -->
                    <div class="px-6 py-4 bg-charcoal-900 flex justify-between items-center">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-gold-gradient flex items-center justify-center text-charcoal-900 font-black text-sm shadow-gold">
                                {{ sup.employee.first_name.charAt(0) }}{{ sup.employee.last_name.charAt(0) }}
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-white tracking-wide">{{ sup.employee.first_name }} {{ sup.employee.last_name }}</h3>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-[9px] text-gold-400 font-black uppercase tracking-widest">{{ sup.campaign.name }}</span>
                                    <span class="w-1 h-1 rounded-full bg-charcoal-700"></span>
                                    <span class="text-[9px] text-pearl-400 font-bold uppercase tracking-widest">Superviseur</span>
                                </div>
                            </div>
                        </div>
                        <button 
                            @click="openDetails(sup.employee)"
                            class="px-4 py-2 bg-charcoal-800 text-white border border-charcoal-700 rounded-lg text-[10px] font-bold uppercase tracking-widest hover:bg-charcoal-700 transition-colors"
                        >
                            Voir détails
                        </button>
                    </div>

                    <!-- TCs List -->
                    <div class="p-6 bg-pearl-50/50">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="text-[10px] font-black text-charcoal-400 uppercase tracking-widest">Téléconseillers ({{ sup.tcs.length }})</span>
                            <div class="flex-1 h-px bg-pearl-200"></div>
                        </div>

                        <div v-if="sup.tcs.length === 0" class="text-center py-6 text-charcoal-400 italic text-xs">
                            Aucun téléconseiller affecté à ce superviseur.
                        </div>

                        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div v-for="tcAssignment in sup.tcs" :key="tcAssignment.id" class="bg-white p-4 rounded-xl border border-pearl-200 shadow-sm flex items-center justify-between group hover:border-gold-300 transition-all">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-pearl-100 flex items-center justify-center text-charcoal-700 font-bold text-xs">
                                        {{ tcAssignment.employee.first_name.charAt(0) }}{{ tcAssignment.employee.last_name.charAt(0) }}
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-charcoal-700">{{ tcAssignment.employee.first_name }} {{ tcAssignment.employee.last_name }}</div>
                                        <div class="text-[9px] text-charcoal-400 font-bold uppercase">Téléconseiller</div>
                                    </div>
                                </div>
                                <button 
                                    @click="openDetails(tcAssignment.employee)"
                                    class="p-1.5 text-pearl-300 hover:text-gold-600 transition-colors"
                                    title="Détails"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
                            <h3 class="text-lg font-black tracking-tight">Fiche Ressource</h3>
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
                                    {{ selectedMember?.position?.name }}
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
                            <div>
                                <p class="text-[9px] text-charcoal-400 uppercase font-black tracking-widest">Date d'embauche</p>
                                <p class="text-sm font-bold text-charcoal-700 mt-0.5">{{ selectedMember?.hire_date }}</p>
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
    </CpLayout>
</template>
