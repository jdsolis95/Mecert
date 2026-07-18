<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { Pencil, Trash2 } from 'lucide-vue-next';

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
                    class="bg-brand text-white px-4 py-2 rounded hover:bg-brand-dark">
                    + Nuevo Rol
                </Link>
            </div>

            <table class="w-full border-collapse bg-white shadow rounded">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3 text-left text-sm">Rol</th>
                        <th class="p-3 text-left text-sm">Módulos habilitados</th>
                        <th class="p-3 text-right text-sm">Usuarios</th>
                        <th class="p-3 text-left text-sm">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="rol in roles" :key="rol.id"
                        :class="rol.cantidad_usuarios === 0 ? 'text-gray-400' : ''"
                        class="border-t even:bg-gray-50 hover:bg-gray-100">
                        <td class="p-3 text-sm font-medium">
                            {{ rol.nombre }}
                            <span v-if="rol.es_rol_base"
                                title="Rol del sistema, no se puede eliminar"
                                class="ml-2 bg-gray-100 text-gray-500 px-2 py-0.5 rounded text-xs">
                                Base
                            </span>
                        </td>
                        <td class="p-3 text-sm">
                            <span v-if="rol.modulos.length === 0" class="text-gray-400">Sin módulos</span>
                            <span v-else class="flex flex-wrap gap-1">
                                <span v-for="modulo in rol.modulos" :key="modulo"
                                    class="bg-brand-light text-brand-darker px-2 py-0.5 rounded text-xs">
                                    {{ modulo }}
                                </span>
                            </span>
                        </td>
                        <td class="p-3 text-sm text-right">
                            <Link v-if="rol.cantidad_usuarios > 0" :href="`/usuarios?rol=${encodeURIComponent(rol.nombre)}`"
                                class="hover:underline">
                                {{ rol.cantidad_usuarios }}
                            </Link>
                            <span v-else>{{ rol.cantidad_usuarios }}</span>
                        </td>
                        <td class="p-3 text-sm">
                            <div class="flex gap-1">
                                <Link :href="`/roles/${rol.id}/edit`"
                                    title="Editar" class="inline-flex items-center justify-center rounded p-1.5 text-gray-600 hover:bg-gray-200">
                                    <Pencil class="h-4 w-4" />
                                </Link>
                                <button
                                    v-if="!rol.es_rol_base && rol.cantidad_usuarios === 0"
                                    @click="eliminar(rol)"
                                    title="Eliminar" class="inline-flex items-center justify-center rounded p-1.5 text-red-600 hover:bg-red-50">
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
