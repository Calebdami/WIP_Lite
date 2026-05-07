<script setup>
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-6">
        <header class="mb-6">
            <h2 class="text-xl font-black text-red-600 uppercase tracking-tight">
                Supprimer le Compte
            </h2>

            <p class="mt-1 text-sm text-charcoal-500 font-medium leading-relaxed">
                Une fois votre compte supprimé, toutes ses ressources et données seront définitivement effacées.
            </p>
        </header>

        <Button label="Demander la suppression du compte" severity="danger" text class="hover:bg-red-50 rounded-xl px-4 border border-red-100" @click="confirmUserDeletion" />

        <Dialog v-model:visible="confirmingUserDeletion" header=" " :style="{ width: '500px' }" modal :closable="false">
            <div class="flex flex-col items-center text-center py-6 px-4">
                <div class="w-20 h-20 rounded-full bg-red-50 text-red-500 flex items-center justify-center mb-6 shadow-inner">
                    <i class="pi pi-exclamation-triangle text-4xl"></i>
                </div>
                <h3 class="text-2xl font-black text-charcoal-900 mb-3 tracking-tight">Action Irréversible</h3>
                <p class="text-sm text-charcoal-500 leading-relaxed mb-8">
                    Veuillez saisir votre mot de passe pour confirmer que vous souhaitez supprimer définitivement votre compte.
                </p>

                <div class="w-full text-left">
                    <label for="password" class="text-[10px] font-black uppercase tracking-widest text-charcoal-400 px-1">Mot de passe de confirmation</label>
                    <InputText
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-full"
                        placeholder="••••••••"
                        @keyup.enter="deleteUser"
                        autofocus
                    />
                    <InputError :message="form.errors.password" class="mt-2" />
                </div>
            </div>
            
            <template #footer>
                <div class="flex justify-center gap-3 pb-6 w-full">
                    <Button label="Annuler" text severity="secondary" class="rounded-xl px-6" @click="closeModal" />
                    <Button
                        label="Confirmer la suppression"
                        severity="danger"
                        class="rounded-xl px-8 shadow-lg shadow-red-100"
                        :loading="form.processing"
                        @click="deleteUser"
                    />
                </div>
            </template>
        </Dialog>
    </section>
</template>
