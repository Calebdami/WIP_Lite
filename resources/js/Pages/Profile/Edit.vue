<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import SupLayout from '@/Layouts/SupLayout.vue';
import CpLayout from '@/Layouts/CpLayout.vue';
import TcLayout from '@/Layouts/TcLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Button from 'primevue/button';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const role = computed(() => user.value?.role?.name?.toLowerCase() || 'tc');

const currentLayout = computed(() => {
    switch (role.value) {
        case 'admin': return AdminLayout;
        case 'sup': return SupLayout;
        case 'cp': return CpLayout;
        default: return TcLayout;
    }
});

const goBack = () => {
    window.history.back();
};
</script>

<template>
    <Head title="Mon Profil" />

    <component :is="currentLayout">
        <div class="py-12 bg-pearl-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-8">
                <!-- Header avec Bouton Retour -->
                <div class="flex items-center justify-between mb-8 bg-white p-6 rounded-3xl shadow-sm border border-pearl-200">
                    <div>
                        <h1 class="text-3xl font-black text-charcoal-900 tracking-tight">Mon Profil</h1>
                        <p class="text-charcoal-500 text-sm font-medium">Gérez vos informations personnelles et la sécurité de votre compte.</p>
                    </div>
                    <Button icon="pi pi-arrow-left" label="Retour" severity="secondary" text class="hover:bg-pearl-100 rounded-xl font-bold" @click="goBack" />
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Section Informations -->
                    <div class="lg:col-span-2 space-y-8">
                        <div class="bg-white p-8 rounded-3xl shadow-sm border border-pearl-200 hover:shadow-md transition-shadow">
                            <UpdateProfileInformationForm
                                :must-verify-email="mustVerifyEmail"
                                :status="status"
                            />
                        </div>

                        <div class="bg-white p-8 rounded-3xl shadow-sm border border-pearl-200 hover:shadow-md transition-shadow">
                            <UpdatePasswordForm />
                        </div>
                    </div>

                    <!-- Section Danger / Autres -->
                    <div class="space-y-8">
                        <div class="bg-white p-8 rounded-3xl shadow-sm border border-red-100 hover:shadow-md transition-shadow">
                            <DeleteUserForm />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </component>
</template>
