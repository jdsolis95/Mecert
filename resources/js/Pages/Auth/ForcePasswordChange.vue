<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset('current_password', 'password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Cambiar Contraseña" />

        <div class="mb-6">
            <h1 class="text-lg font-semibold text-gray-900">
                Debes cambiar tu contraseña para continuar.
            </h1>
            <p class="mt-2 text-sm text-gray-600">
                Se requiere una contraseña nueva!
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <InputLabel for="current_password" value="Contraseña Actual" />
                <TextInput
                    id="current_password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.current_password"
                    required
                    title="Ingresa tu contraseña actual."
                    autofocus
                    autocomplete="current-password"
                />
                <InputError class="mt-2" :message="form.errors.current_password" />
            </div>

            <div>
                <InputLabel for="password" value="Nueva Contraseña" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    title="Mínimo 8 caracteres, una mayúscula, un número y un carácter especial."
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div>
                <InputLabel for="password_confirmation" value="Confirmar Contraseña" />
                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    title="Debe coincidir con la contraseña nueva."
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div class="flex items-center justify-end">
                <PrimaryButton :disabled="form.processing">
                    Guardar contraseña
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>