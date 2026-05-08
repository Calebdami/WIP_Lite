<script setup>
import { computed } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Confirmation',
    },
    description: {
        type: String,
        default: '',
    },
    confirmLabel: {
        type: String,
        default: 'Confirmer',
    },
    cancelLabel: {
        type: String,
        default: 'Annuler',
    },
    confirmSeverity: {
        type: String,
        default: 'danger',
    },
    icon: {
        type: String,
        default: 'pi pi-exclamation-triangle',
    },
    iconBgClass: {
        type: String,
        default: 'bg-amber-50',
    },
    iconTextClass: {
        type: String,
        default: 'text-gold-500',
    },
    width: {
        type: String,
        default: '420px',
    },
    closable: {
        type: Boolean,
        default: false,
    },
    className: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['update:modelValue', 'confirm', 'cancel']);

const dialogClass = computed(() => `p-confirm-dialog w-full mx-4 ${props.className}`.trim());

const close = () => {
    if (props.closable) {
        emit('update:modelValue', false);
        emit('cancel');
    }
};

const confirm = () => emit('confirm');
</script>

<template>
    <Dialog
        v-model:visible="modelValue"
        :modal="true"
        :draggable="false"
        :closable="closable"
        :class="dialogClass"
        :style="{ width: width }"
    >
        <div class="p-6 sm:p-8">
            <div class="flex flex-col items-center text-center gap-4">
                <div :class="['w-14 h-14 rounded-full flex items-center justify-center', iconBgClass]">
                    <i :class="[icon, iconTextClass, 'text-2xl']" />
                </div>
                <div>
                    <h3 class="text-lg font-black text-charcoal-700 uppercase tracking-[0.03em]">
                        {{ title }}
                    </h3>
                    <p v-if="description" class="text-sm text-charcoal-500 mt-3 leading-relaxed">
                        {{ description }}
                    </p>
                    <slot />
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 justify-end mt-6">
                <Button
                    :label="cancelLabel"
                    text
                    severity="secondary"
                    class="flex-1 sm:flex-none text-[10px] uppercase font-black"
                    @click="close"
                />
                <Button
                    :label="confirmLabel"
                    :severity="confirmSeverity"
                    class="flex-1 sm:flex-none text-[10px] uppercase font-black"
                    @click="confirm"
                />
            </div>
        </div>
    </Dialog>
</template>
