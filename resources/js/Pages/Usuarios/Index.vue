<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Eye, Pencil, KeyRound, UserX, UserCheck } from 'lucide-vue-next';

const page = usePage();

// Props que vienen desde el controlador creado para usuarios *****
const props = defineProps({
    usuarios: Array,
});

const busqueda = ref('');
const filtroRol = ref(new URLSearchParams(window.location.search).get('rol') ?? '');
const filtroEstado = ref('');

const rolesDisponibles = computed(() => [...new Set(props.usuarios.map((u) => u.rol))].sort());

const usuariosFiltrados = computed(() => {
    const termino = busqueda.value.trim().toLowerCase();

    return props.usuarios.filter((usuario) => {
        const coincideTexto = !termino
            || usuario.nombre_completo.toLowerCase().includes(termino)
            || usuario.cedula.includes(termino);
        const coincideRol = !filtroRol.value || usuario.rol === filtroRol.value;
        const coincideEstado = !filtroEstado.value
            || (filtroEstado.value === 'activo' ? usuario.esta_activo : !usuario.esta_activo);

        return coincideTexto && coincideRol && coincideEstado;
    });
});

function alternarEstado(usuario) {
    const accion = usuario.esta_activo ? 'deshabilitar' : 'habilitar';
    if (confirm(`¿Desea ${accion} este usuario?`)) {
        router.delete(`/usuarios/${usuario.id}`);
    }
}

function resetPassword(usuario) {
    if (confirm(`¿Enviar reset de contraseña a ${usuario.email}?`)) {
        router.post(`/usuarios/${usuario.id}/reset-password`, {}, {
            onSuccess: () => {
                alert('Correo de reset enviado correctamente');
            }
        });
    }
}

</script>

<template>
    <AppLayout title="Usuarios">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-800">Gestión de Usuarios</h1>
                <Link href="/usuarios/create"
                    class="bg-brand text-white px-4 py-2 rounded hover:bg-brand-dark">
                    + Nuevo Usuario
                </Link>
            </div>

            <div class="flex flex-wrap gap-3 mb-4">
                <input v-model="busqueda" type="text" placeholder="Buscar por nombre o cédula..."
                    class="flex-1 min-w-[200px] border rounded p-2 text-sm" />
                <select v-model="filtroRol" class="border rounded p-2 text-sm">
                    <option value="">Todos los roles</option>
                    <option v-for="rol in rolesDisponibles" :key="rol" :value="rol">{{ rol }}</option>
                </select>
                <select v-model="filtroEstado" class="border rounded p-2 text-sm">
                    <option value="">Todos los estados</option>
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>
            </div>

            <div v-if="usuariosFiltrados.length === 0" class="text-center text-gray-400 py-12">
                No hay usuarios que coincidan con la búsqueda.
            </div>

            <table v-else class="w-full border-collapse bg-white shadow rounded">
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
                    <tr v-for="usuario in usuariosFiltrados" :key="usuario.id"
                        class="border-t even:bg-gray-50 hover:bg-gray-100">
                        <td class="p-3 text-sm">{{ usuario.cedula }}</td>
                        <td class="p-3 text-sm">
                            {{ usuario.nombre_completo }}
                            <span v-if="page.props.auth?.user?.id === usuario.id"
                                class="ml-2 bg-gray-100 text-gray-500 px-2 py-0.5 rounded text-xs">
                                Tú
                            </span>
                        </td>
                        <td class="p-3 text-sm">{{ usuario.email }}</td>
                        <td class="p-3 text-sm">{{ usuario.rol }}</td>
                        <td class="p-3 text-sm">
                            <span :class="usuario.esta_activo
                                ? 'bg-green-100 text-green-700'
                                : 'bg-red-100 text-red-700'"
                                class="px-2 py-1 rounded text-xs font-medium inline-block text-center min-w-[70px]">
                                {{ usuario.esta_activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="p-3 text-sm">
                            <div class="flex gap-1">
                                <Link :href="`/usuarios/${usuario.id}`"
                                    title="Ver" class="inline-flex items-center justify-center rounded p-1.5 text-gray-600 hover:bg-gray-200">
                                    <Eye class="h-4 w-4" />
                                </Link>
                                <template v-if="page.props.auth?.user?.id !== usuario.id">
                                    <Link :href="`/usuarios/${usuario.id}/edit`"
                                        title="Editar" class="inline-flex items-center justify-center rounded p-1.5 text-gray-600 hover:bg-gray-200">
                                        <Pencil class="h-4 w-4" />
                                    </Link>
                                    <button
                                        @click="resetPassword(usuario)"
                                        title="Restablecer contraseña" class="inline-flex items-center justify-center rounded p-1.5 text-gray-600 hover:bg-gray-200">
                                        <KeyRound class="h-4 w-4" />
                                    </button>
                                    <button @click="alternarEstado(usuario)"
                                        :title="usuario.esta_activo ? 'Deshabilitar' : 'Habilitar'"
                                        :class="usuario.esta_activo ? 'text-red-600 hover:bg-red-50' : 'text-gray-600 hover:bg-gray-200'"
                                        class="inline-flex items-center justify-center rounded p-1.5">
                                        <component :is="usuario.esta_activo ? UserX : UserCheck" class="h-4 w-4" />
                                    </button>
                                </template>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
