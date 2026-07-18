<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    auditorias: Object,
    filtros: Object,
});

const accion = ref(props.filtros.accion ?? '');
const expandido = ref(null);

const acciones = [
    { valor: '', etiqueta: 'Todas' },
    { valor: 'creado', etiqueta: 'Creación' },
    { valor: 'modificado', etiqueta: 'Modificación' },
    { valor: 'eliminado', etiqueta: 'Eliminación' },
];

const estiloAccion = {
    creado: 'bg-green-50 text-green-700',
    modificado: 'bg-yellow-50 text-yellow-700',
    eliminado: 'bg-red-50 text-red-700',
};

function filtrar() {
    router.get('/bitacoras', { accion: accion.value }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function alternarDetalle(id) {
    expandido.value = expandido.value === id ? null : id;
}
</script>

<template>
    <AppLayout title="Bitácoras">
        <div class="p-6">
            <h1 class="text-2xl font-semibold text-gray-800 mb-6">Bitácoras de auditoría</h1>

            <div class="flex flex-wrap gap-2 mb-6">
                <button v-for="opcion in acciones" :key="opcion.valor"
                    @click="accion = opcion.valor; filtrar()"
                    type="button"
                    :class="accion === opcion.valor
                        ? 'bg-brand text-gray-900 border-brand'
                        : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50'"
                    class="text-xs px-3 py-1 rounded-full border">
                    {{ opcion.etiqueta }}
                </button>
            </div>

            <div v-if="auditorias.data.length === 0" class="text-center text-gray-400 py-12">
                No hay registros de auditoría que coincidan con el filtro.
            </div>

            <div v-else class="bg-white border rounded shadow-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-left">
                        <tr>
                            <th class="p-3 font-medium">Fecha</th>
                            <th class="p-3 font-medium">Módulo</th>
                            <th class="p-3 font-medium">Acción</th>
                            <th class="p-3 font-medium">Usuario</th>
                            <th class="p-3 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-for="registro in auditorias.data" :key="registro.id">
                            <tr class="border-t">
                                <td class="p-3 text-gray-500 whitespace-nowrap">{{ registro.fecha }}</td>
                                <td class="p-3">{{ registro.modulo }}</td>
                                <td class="p-3">
                                    <span class="text-xs px-2 py-0.5 rounded-full" :class="estiloAccion[registro.accion]">
                                        {{ registro.accion }}
                                    </span>
                                </td>
                                <td class="p-3">{{ registro.usuario }}</td>
                                <td class="p-3 text-right">
                                    <button type="button" @click="alternarDetalle(registro.id)"
                                        class="text-brand-darker hover:underline text-xs">
                                        {{ expandido === registro.id ? 'Ocultar' : 'Ver detalle' }}
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="expandido === registro.id" class="border-t bg-gray-50">
                                <td colspan="5" class="p-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                                        <div>
                                            <p class="font-medium text-gray-500 mb-1">Datos anteriores</p>
                                            <pre class="whitespace-pre-wrap break-words bg-white border rounded p-2">{{ registro.datos_anteriores ? JSON.stringify(registro.datos_anteriores, null, 2) : '—' }}</pre>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-500 mb-1">Datos nuevos</p>
                                            <pre class="whitespace-pre-wrap break-words bg-white border rounded p-2">{{ registro.datos_nuevos ? JSON.stringify(registro.datos_nuevos, null, 2) : '—' }}</pre>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div v-if="auditorias.links.length > 3" class="flex flex-wrap gap-1 mt-6">
                <template v-for="(link, indice) in auditorias.links" :key="indice">
                    <Link v-if="link.url" :href="link.url" preserve-scroll
                        :class="link.active ? 'bg-brand text-gray-900 border-brand' : 'bg-white text-gray-600 hover:bg-gray-50'"
                        class="text-sm px-3 py-1 rounded border" v-html="link.label" />
                    <span v-else class="text-sm px-3 py-1 rounded border text-gray-300" v-html="link.label" />
                </template>
            </div>
        </div>
    </AppLayout>
</template>
