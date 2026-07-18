<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Verificación de Correo" />

        <div class="mb-4 text-sm text-gray-600">
            ¡Gracias por registrarte! Antes de comenzar, ¿podrías verificar tu
            correo electrónico haciendo clic en el enlace que te acabamos de
            enviar? Si no recibiste el correo, con gusto te enviaremos otro.
        </div>

        <div
            class="mb-4 text-sm font-medium text-green-600"
            v-if="verificationLinkSent"
        >
            Se ha enviado un nuevo enlace de verificación a la dirección de
            correo que indicaste al registrarte.
        </div>

        <form @submit.prevent="submit">
            <div class="mt-4 flex items-center justify-between">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Reenviar Correo de Verificación
                </PrimaryButton>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2"
                    >Salir</Link
                >
            </div>
        </form>
    </GuestLayout>
</template>
