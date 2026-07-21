<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Pencil, Trash2, Ban, CircleCheck } from 'lucide-vue-next';

const props = defineProps({
    tipos: Array,
    filtros: Object,
});

const q = ref(props.filtros.q ?? '');
let temporizador = null;

function buscar() {
    router.get('/tipos-certificacion', { q: q.value }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function alEscribir() {
    clearTimeout(temporizador);
    temporizador = setTimeout(buscar, 400);
}

function eliminar(tipo) {
    if (!confirm(`¿Desea eliminar el tipo de certificación "${tipo.nombre}"?`)) {
        return;
    }

    router.delete(`/tipos-certificacion/${tipo.id}`, {
        onError: (errors) => {
            if (errors.mensaje) {
                alert(errors.mensaje);
            }
        },
    });
}

function alternar(tipo) {
    router.patch(`/tipos-certificacion/${tipo.id}/alternar`, {}, { preserveScroll: true });
}
</script>

<template>
    <AppLayout title="Tipos de Certificación">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-800">Mantenimiento de Tipos de Certificación</h1>
                <Link href="/tipos-certificacion/create"
                    class="bg-brand text-white px-4 py-2 rounded hover:bg-brand-dark">
                    + Nuevo Tipo
                </Link>
            </div>

            <div class="mb-4">
                <input v-model="q" @input="alEscribir" type="text"
                    placeholder="Buscar por nombre..."
                    class="w-full border rounded p-2" />
            </div>

            <div v-if="tipos.length === 0" class="text-center text-gray-400 py-12">
                No hay tipos de certificación que coincidan con la búsqueda.
            </div>

            <table v-else class="w-full border-collapse bg-white shadow rounded">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3 text-left text-sm">Nombre</th>
                        <th class="p-3 text-left text-sm">Estado</th>
                        <th class="p-3 text-left text-sm">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="tipo in tipos" :key="tipo.id"
                        class="border-t even:bg-gray-50 hover:bg-gray-100">
                        <td class="p-3 text-sm font-medium">{{ tipo.nombre }}</td>
                        <td class="p-3 text-sm">
                            <span :class="tipo.activo
                                ? 'bg-green-100 text-green-700'
                                : 'bg-red-100 text-red-700'"
                                class="px-2 py-1 rounded text-xs font-medium inline-block text-center min-w-[70px]">
                                {{ tipo.activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="p-3 text-sm">
                            <div class="flex gap-1">
                                <Link :href="`/tipos-certificacion/${tipo.id}/edit`"
                                    title="Editar" class="inline-flex items-center justify-center rounded p-1.5 text-gray-600 hover:bg-gray-200">
                                    <Pencil class="h-4 w-4" />
                                </Link>
                                <button @click="alternar(tipo)"
                                    :title="tipo.activo ? 'Deshabilitar' : 'Habilitar'"
                                    class="inline-flex items-center justify-center rounded p-1.5 text-gray-600 hover:bg-gray-200">
                                    <component :is="tipo.activo ? Ban : CircleCheck" class="h-4 w-4" />
                                </button>
                                <button v-if="!tipo.en_uso" @click="eliminar(tipo)"
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
