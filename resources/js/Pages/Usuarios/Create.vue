<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';

defineProps({
    roles: Array,
});

// useForm maneja el estado del formulario, errores y el procesamiento de la operación
const form = useForm({
    cedula: '',
    name: '',
    primer_apellido: '',
    segundo_apellido: '',
    email: '',
    password: '',
    password_confirmation: '',
    rol: '',
});

function guardar() {
    form.post('/usuarios');
}
</script>

<template>
    <AppLayout title="Nuevo Usuario">
        <div class="p-6 max-w-2xl mx-auto">
            <h1 class="text-2xl font-semibold mb-6">Nuevo Usuario</h1>

            <form @submit.prevent="guardar" class="space-y-4">

                <div>
                    <label class="block text-sm font-medium mb-1">Cédula</label>
                    <input v-model="form.cedula" type="text" inputmode="numeric" maxlength="9" required
                        title="Formato requerido: 9 números sin espacios ni guiones."
                        class="w-full border rounded p-2" />
                    <p v-if="form.errors.cedula" class="text-red-500 text-xs mt-1">{{ form.errors.cedula }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Nombre</label>
                        <input v-model="form.name" type="text" required
                            title="No debe contener números."
                            class="w-full border rounded p-2" />
                        <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Primer Apellido</label>
                        <input v-model="form.primer_apellido" type="text" required
                            title="No debe contener números."
                            class="w-full border rounded p-2" />
                        <p v-if="form.errors.primer_apellido" class="text-red-500 text-xs mt-1">{{ form.errors.primer_apellido }}</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Segundo Apellido</label>
                    <input v-model="form.segundo_apellido" type="text" required
                        title="No debe contener números."
                        class="w-full border rounded p-2" />
                    <p v-if="form.errors.segundo_apellido" class="text-red-500 text-xs mt-1">{{ form.errors.segundo_apellido }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Correo Electrónico</label>
                    <input v-model="form.email" type="email" required
                        title="Formato requerido: nombre.apellido@datacr.com"
                        class="w-full border rounded p-2" />
                    <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Contraseña</label>
                        <input v-model="form.password" type="password" required
                            title="Mínimo 8 caracteres, una mayúscula, un número y un carácter especial."
                            class="w-full border rounded p-2" />
                        <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Confirmar Contraseña</label>
                        <input v-model="form.password_confirmation" type="password" required
                            title="Debe coincidir con la contraseña."
                            class="w-full border rounded p-2" />
                        <p v-if="form.errors.password_confirmation" class="text-red-500 text-xs mt-1">{{ form.errors.password_confirmation }}</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Rol</label>
                    <select v-model="form.rol" required class="w-full border rounded p-2">
                        <option value="">-- Seleccione un rol --</option>
                        <option v-for="rol in roles" :key="rol" :value="rol">
                            {{ rol }}
                        </option>
                    </select>
                    <p v-if="form.errors.rol" class="text-red-500 text-xs mt-1">{{ form.errors.rol }}</p>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" :disabled="form.processing"
                        class="bg-brand text-white px-6 py-2 rounded hover:bg-brand-dark">
                        Guardar
                    </button>
                    <a href="/usuarios"
                        class="border px-6 py-2 rounded hover:bg-gray-50">
                        Cancelar
                    </a>
                </div>

            </form>
        </div>
    </AppLayout>
</template>