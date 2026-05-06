<script setup>
import TcLayout from '@/Layouts/TcLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Mon Planning — Téléconseiller" />
    <TcLayout>
        <template #header>
            <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Mon Planning</h1>
            <p class="text-xs text-charcoal-400 mt-0.5">Consultez vos horaires de travail</p>
        </template>
        <div class="bg-white rounded-xl border border-pearl-200 p-8 h-96 flex flex-col items-center justify-center text-charcoal-300">
            <svg class="w-16 h-16 mb-4 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <p class="italic">Votre planning hebdomadaire s'affichera ici</p>
        </div>
    </TcLayout>
</template>
