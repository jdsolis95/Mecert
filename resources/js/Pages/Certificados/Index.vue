<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    certificados: Array,
    filtros: Object,
    puedeCrear: Boolean,
    puedeAprobarExamenes: Boolean,
});

const q = ref(props.filtros.q ?? '');
const estado = ref(props.filtros.estado ?? '');
let temporizador = null;

function buscar() {
    router.get('/certificados', { q: q.value, estado: estado.value }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function alEscribir() {
    clearTimeout(temporizador);
    temporizador = setTimeout(buscar, 400);
}

const estiloEstado = {
    verde: 'bg-green-100 text-green-700',
    amarillo: 'bg-yellow-100 text-yellow-700',
    rojo: 'bg-red-100 text-red-700',
};

const etiquetaEstado = {
    verde: 'Vigente',
    amarillo: 'Por vencer',
    rojo: 'Vencido',
};

function eliminar(id) {
    if (confirm('¿Desea eliminar este certificado?')) {
        router.delete(`/certificados/${id}`);
    }
}

const mostrarModalExamen = ref(false);
const certificadoSeleccionado = ref(null);

const formExamen = useForm({
    fecha_propuesta: '',
    lugar_propuesto: '',
});

function abrirModalExamen(certificado) {
    certificadoSeleccionado.value = certificado;
    formExamen.reset();
    formExamen.clearErrors();
    mostrarModalExamen.value = true;
}

function cerrarModalExamen() {
    mostrarModalExamen.value = false;
    certificadoSeleccionado.value = null;
}

function proponerExamen() {
    formExamen.post(`/certificados/${certificadoSeleccionado.value.id}/examenes`, {
        onSuccess: () => cerrarModalExamen(),
    });
}
</script>

<template>
    <AppLayout title="Certificados">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-800">Certificados</h1>
                <div class="flex gap-3">
                    <Link v-if="puedeAprobarExamenes" href="/certificados-examenes"
                        class="border px-4 py-2 rounded hover:bg-gray-50">
                        Exámenes pendientes
                    </Link>
                    <Link v-if="puedeCrear" href="/certificados/create"
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        + Nuevo certificado
                    </Link>
                </div>
            </div>

            <div class="flex gap-3 mb-4">
                <input v-model="q" @input="alEscribir" type="text"
                    placeholder="Buscar por colaborador o tipo de certificado..."
                    class="flex-1 border rounded p-2" />
                <select v-model="estado" @change="buscar" class="border rounded p-2">
                    <option value="">Todos los estados</option>
                    <option value="verde">Vigente</option>
                    <option value="amarillo">Por vencer</option>
                    <option value="rojo">Vencido</option>
                </select>
            </div>

            <div v-if="certificados.length === 0" class="text-center text-gray-400 py-12">
                No hay certificados que coincidan con la búsqueda.
            </div>

            <table v-else class="w-full border-collapse bg-white shadow rounded">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3 text-left text-sm">Colaborador</th>
                        <th class="p-3 text-left text-sm">Tipo de certificado</th>
                        <th class="p-3 text-left text-sm">Emisión</th>
                        <th class="p-3 text-left text-sm">Vencimiento</th>
                        <th class="p-3 text-left text-sm">Estado</th>
                        <th class="p-3 text-left text-sm">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="certificado in certificados" :key="certificado.id"
                        class="border-t hover:bg-gray-50">
                        <td class="p-3 text-sm">{{ certificado.colaborador }}</td>
                        <td class="p-3 text-sm">{{ certificado.tipo_certificado }}</td>
                        <td class="p-3 text-sm">{{ certificado.fecha_emision }}</td>
                        <td class="p-3 text-sm">{{ certificado.fecha_vencimiento }}</td>
                        <td class="p-3 text-sm">
                            <span :class="estiloEstado[certificado.estado]"
                                class="px-2 py-1 rounded text-xs font-medium">
                                {{ etiquetaEstado[certificado.estado] }}
                            </span>
                        </td>
                        <td class="p-3 text-sm">
                            <div class="flex gap-2 flex-wrap">
                                <Link v-if="certificado.puede_ver" :href="`/certificados/${certificado.id}`"
                                    class="text-black hover:underline">Ver</Link>
                                <Link v-if="certificado.puede_editar" :href="`/certificados/${certificado.id}/edit`"
                                    class="text-black hover:underline">Editar</Link>
                                <button v-if="certificado.puede_proponer_examen"
                                    @click="abrirModalExamen(certificado)"
                                    class="text-black hover:underline">Calendarizar examen</button>
                                <button v-if="certificado.puede_eliminar" @click="eliminar(certificado.id)"
                                    class="text-red-600 hover:underline">Eliminar</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Modal :show="mostrarModalExamen" @close="cerrarModalExamen">
            <div class="p-6" v-if="certificadoSeleccionado">
                <h2 class="text-lg font-semibold mb-4">
                    Calendarizar examen de renovación — {{ certificadoSeleccionado.tipo_certificado }}
                </h2>
                <form @submit.prevent="proponerExamen" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Fecha propuesta</label>
                        <input v-model="formExamen.fecha_propuesta" type="date" required
                            class="w-full border rounded p-2" />
                        <p v-if="formExamen.errors.fecha_propuesta" class="text-red-500 text-xs mt-1">
                            {{ formExamen.errors.fecha_propuesta }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Lugar propuesto (opcional)</label>
                        <input v-model="formExamen.lugar_propuesto" type="text" maxlength="150"
                            class="w-full border rounded p-2" />
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="submit" :disabled="formExamen.processing"
                            class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                            Enviar
                        </button>
                        <button type="button" @click="cerrarModalExamen"
                            class="border px-6 py-2 rounded hover:bg-gray-50">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AppLayout>
</template>
