<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import BitacorasTabs from '@/Components/BitacorasTabs.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Eye, EyeOff } from 'lucide-vue-next';

const props = defineProps({
    auditorias: Object,
    filtros: Object,
    modulos: Array,
    usuarios: Array,
});

const accion = ref(props.filtros.accion ?? '');
const modulo = ref(props.filtros.modulo ?? '');
const usuarioId = ref(props.filtros.usuario_id ?? '');
const desde = ref(props.filtros.desde ?? '');
const hasta = ref(props.filtros.hasta ?? '');
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
    router.get('/bitacoras', {
        accion: accion.value,
        modulo: modulo.value,
        usuario_id: usuarioId.value,
        desde: desde.value,
        hasta: hasta.value,
    }, {
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
            <h1 class="text-2xl font-semibold text-gray-800 mb-6">Bitácoras</h1>

            <BitacorasTabs activa="movimientos" />

            <div class="flex flex-wrap gap-2 mb-4">
                <button v-for="opcion in acciones" :key="opcion.valor"
                    @click="accion = opcion.valor; filtrar()"
                    type="button"
                    :class="accion === opcion.valor
                        ? 'bg-brand text-white border-brand'
                        : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50'"
                    class="text-xs px-3 py-1 rounded-full border">
                    {{ opcion.etiqueta }}
                </button>
            </div>

            <div class="flex flex-wrap items-end gap-3 mb-6">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Módulo</label>
                    <select v-model="modulo" @change="filtrar" class="border rounded p-2 text-sm">
                        <option value="">Todos</option>
                        <option v-for="opcion in modulos" :key="opcion" :value="opcion">{{ opcion }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Usuario</label>
                    <select v-model="usuarioId" @change="filtrar" class="border rounded p-2 text-sm">
                        <option value="">Todos</option>
                        <option v-for="usuario in usuarios" :key="usuario.id" :value="usuario.id">{{ usuario.nombre }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Desde</label>
                    <input v-model="desde" @change="filtrar" type="date" class="border rounded p-2 text-sm" />
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Hasta</label>
                    <input v-model="hasta" @change="filtrar" type="date" class="border rounded p-2 text-sm" />
                </div>
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
                        <template v-for="(registro, indice) in auditorias.data" :key="registro.id">
                            <tr :class="indice % 2 === 0 ? 'bg-white' : 'bg-gray-50'" class="border-t hover:bg-gray-100">
                                <td class="p-3 text-gray-500 whitespace-nowrap">{{ registro.fecha }}</td>
                                <td class="p-3">{{ registro.modulo }}</td>
                                <td class="p-3">
                                    <span class="text-xs px-2 py-0.5 rounded-full inline-block text-center min-w-[80px]" :class="estiloAccion[registro.accion]">
                                        {{ registro.accion }}
                                    </span>
                                </td>
                                <td class="p-3">{{ registro.usuario }}</td>
                                <td class="p-3 text-right">
                                    <button type="button" @click="alternarDetalle(registro.id)"
                                        class="inline-flex items-center gap-1 border rounded-full px-3 py-1 text-xs text-brand-darker hover:bg-gray-100">
                                        <component :is="expandido === registro.id ? EyeOff : Eye" class="h-3.5 w-3.5" />
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
                        :class="link.active ? 'bg-brand text-white border-brand' : 'bg-white text-gray-600 hover:bg-gray-50'"
                        class="text-sm px-3 py-1 rounded border" v-html="link.label" />
                    <span v-else class="text-sm px-3 py-1 rounded border text-gray-300" v-html="link.label" />
                </template>
            </div>
        </div>
    </AppLayout>
</template>
