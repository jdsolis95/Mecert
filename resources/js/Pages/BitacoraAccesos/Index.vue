<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import BitacorasTabs from '@/Components/BitacorasTabs.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    accesos: Object,
    filtros: Object,
    usuarios: Array,
});

const usuarioId = ref(props.filtros.usuario_id ?? '');
const desde = ref(props.filtros.desde ?? '');
const hasta = ref(props.filtros.hasta ?? '');

function filtrar() {
    router.get('/bitacora-accesos', {
        usuario_id: usuarioId.value,
        desde: desde.value,
        hasta: hasta.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}
</script>

<template>
    <AppLayout title="Bitácora de accesos">
        <div class="p-6">
            <h1 class="text-2xl font-semibold text-gray-800 mb-6">Bitácoras</h1>

            <BitacorasTabs activa="accesos" />

            <div class="flex flex-wrap items-end gap-3 mb-6">
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

            <div v-if="accesos.data.length === 0" class="text-center text-gray-400 py-12">
                No hay accesos registrados que coincidan con el filtro.
            </div>

            <div v-else class="bg-white border rounded shadow-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-left">
                        <tr>
                            <th class="p-3 font-medium">Código_ingreso</th>
                            <th class="p-3 font-medium">Usuario</th>
                            <th class="p-3 font-medium">Fecha y hora de ingreso</th>
                            <th class="p-3 font-medium">Fecha y hora de salida</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(registro, indice) in accesos.data" :key="registro.id"
                            :class="indice % 2 === 0 ? 'bg-white' : 'bg-gray-50'" class="border-t hover:bg-gray-100">
                            <td class="p-3 text-gray-500">{{ registro.id }}</td>
                            <td class="p-3">{{ registro.usuario }}</td>
                            <td class="p-3 whitespace-nowrap">{{ registro.fecha_ingreso }}</td>
                            <td class="p-3 whitespace-nowrap">
                                <span v-if="registro.fecha_salida">{{ registro.fecha_salida }}</span>
                                <span v-else class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700">En sesión</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="accesos.links.length > 3" class="flex flex-wrap gap-1 mt-6">
                <template v-for="(link, indice) in accesos.links" :key="indice">
                    <Link v-if="link.url" :href="link.url" preserve-scroll
                        :class="link.active ? 'bg-brand text-white border-brand' : 'bg-white text-gray-600 hover:bg-gray-50'"
                        class="text-sm px-3 py-1 rounded border" v-html="link.label" />
                    <span v-else class="text-sm px-3 py-1 rounded border text-gray-300" v-html="link.label" />
                </template>
            </div>
        </div>
    </AppLayout>
</template>
