<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';

defineProps({
    roles: Array,
});

function eliminar(rol) {
    if (!confirm(`¿Desea eliminar el rol "${rol.nombre}"?`)) {
        return;
    }

    router.delete(`/roles/${rol.id}`, {
        onError: (errors) => {
            if (errors.mensaje) {
                alert(errors.mensaje);
            }
        },
    });
}
</script>

<template>
    <AppLayout title="Administración de Roles">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-800">Administración de Roles</h1>
                <Link href="/roles/create"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    + Nuevo Rol
                </Link>
            </div>

            <table class="w-full border-collapse bg-white shadow rounded">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3 text-left text-sm">Rol</th>
                        <th class="p-3 text-left text-sm">Módulos habilitados</th>
                        <th class="p-3 text-left text-sm">Usuarios</th>
                        <th class="p-3 text-left text-sm">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="rol in roles" :key="rol.id" class="border-t hover:bg-gray-50">
                        <td class="p-3 text-sm font-medium">
                            {{ rol.nombre }}
                            <span v-if="rol.es_rol_base"
                                class="ml-2 bg-gray-100 text-gray-500 px-2 py-0.5 rounded text-xs">
                                Base
                            </span>
                        </td>
                        <td class="p-3 text-sm">
                            <span v-if="rol.modulos.length === 0" class="text-gray-400">Sin módulos</span>
                            <span v-else class="flex flex-wrap gap-1">
                                <span v-for="modulo in rol.modulos" :key="modulo"
                                    class="bg-blue-50 text-blue-700 px-2 py-0.5 rounded text-xs">
                                    {{ modulo }}
                                </span>
                            </span>
                        </td>
                        <td class="p-3 text-sm">{{ rol.cantidad_usuarios }}</td>
                        <td class="p-3 text-sm flex gap-2">
                            <Link :href="`/roles/${rol.id}/edit`"
                                class="text-blue-600 hover:underline">Editar</Link>
                            <button
                                v-if="!rol.es_rol_base && rol.cantidad_usuarios === 0"
                                @click="eliminar(rol)"
                                class="text-red-600 hover:underline">
                                Eliminar
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
