<script setup>
import TcLayout from '@/Layouts/TcLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
</script>

<template>
    <Head title="Mon Profil — Téléconseiller" />
    <TcLayout>
        <template #header>
            <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Mon Profil</h1>
            <p class="text-xs text-charcoal-400 mt-0.5">Informations personnelles et professionnelles</p>
        </template>
        
        <div class="max-w-2xl">
            <div class="bg-white rounded-xl border border-pearl-200 p-6 shadow-sm mb-6">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-16 h-16 rounded-full bg-gold-gradient flex items-center justify-center text-charcoal-900 font-bold text-2xl shadow-gold">
                        {{ (user?.name || user?.email || 'U').charAt(0).toUpperCase() }}
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-charcoal-700">{{ user?.name || user?.email }}</h2>
                        <p class="text-sm text-charcoal-400">Téléconseiller</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-bold text-charcoal-400 uppercase tracking-widest mb-1">Email</label>
                        <p class="text-sm font-medium text-charcoal-700">{{ user.email }}</p>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-charcoal-400 uppercase tracking-widest mb-1">Date d'embauche</label>
                        <p class="text-sm font-medium text-charcoal-700">01/01/2024</p>
                    </div>
                </div>
            </div>
        </div>
    </TcLayout>
</template>

<style scoped>
.bg-gold-gradient {
    background: linear-gradient(135deg, #D4A017 0%, #8B6914 100%);
}
</style>
