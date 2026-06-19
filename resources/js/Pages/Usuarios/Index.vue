<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';


// Props que vienen desde el controlador creado para usuarios *****
const props = defineProps({
    usuarios: Array,
});


function deshabilitar(id) {
    if (confirm('¿Desea deshabilitar este usuario?')) {
        router.delete(`/usuarios/${id}`);
    }
}

</script>

<template>
    <AppLayout title="Usuarios">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-800">Gestión de Usuarios</h1>
                <Link href="/usuarios/create"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    + Nuevo Usuario
                </Link>
            </div>

            <table class="w-full border-collapse bg-white shadow rounded">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3 text-left text-sm">Cédula</th>
                        <th class="p-3 text-left text-sm">Nombre</th>
                        <th class="p-3 text-left text-sm">Correo</th>
                        <th class="p-3 text-left text-sm">Rol</th>
                        <th class="p-3 text-left text-sm">Estado</th>
                        <th class="p-3 text-left text-sm">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="usuario in usuarios" :key="usuario.id"
                        class="border-t hover:bg-gray-50">
                        <td class="p-3 text-sm">{{ usuario.cedula }}</td>
                        <td class="p-3 text-sm">{{ usuario.nombre_completo }}</td>
                        <td class="p-3 text-sm">{{ usuario.email }}</td>
                        <td class="p-3 text-sm">{{ usuario.rol }}</td>
                        <td class="p-3 text-sm">
                            <span :class="usuario.esta_activo
                                ? 'bg-green-100 text-green-700'
                                : 'bg-red-100 text-red-700'"
                                class="px-2 py-1 rounded text-xs font-medium">
                                {{ usuario.esta_activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="p-3 text-sm flex gap-2">
                            <Link :href="`/usuarios/${usuario.id}/edit`"
                                class="text-blue-600 hover:underline">Editar</Link>
                            <button @click="deshabilitar(usuario.id)"
                                class="text-red-600 hover:underline">Deshabilitar</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>