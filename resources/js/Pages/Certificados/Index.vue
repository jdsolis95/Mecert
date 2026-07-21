<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Eye, Pencil, CalendarPlus, Trash2, ChevronUp, ChevronDown, ChevronsUpDown } from 'lucide-vue-next';

const props = defineProps({
    certificados: Array,
    filtros: Object,
    puedeCrear: Boolean,
    puedeAprobarExamenes: Boolean,
    examenesPendientesCount: Number,
    puedeAdministrarCatalogos: Boolean,
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

const ordenColumna = ref(null);
const ordenAscendente = ref(true);
const rangoEstado = { rojo: 0, amarillo: 1, verde: 2 };

function ordenarPor(columna) {
    if (ordenColumna.value === columna) {
        ordenAscendente.value = !ordenAscendente.value;
    } else {
        ordenColumna.value = columna;
        ordenAscendente.value = true;
    }
}

function fechaAOrden(fecha) {
    const [dia, mes, anio] = fecha.split('/');
    return Number(anio) * 10000 + Number(mes) * 100 + Number(dia);
}

const certificadosOrdenados = computed(() => {
    if (!ordenColumna.value) {
        return props.certificados;
    }

    const factor = ordenAscendente.value ? 1 : -1;

    return [...props.certificados].sort((a, b) => {
        let valorA;
        let valorB;

        if (ordenColumna.value === 'estado') {
            valorA = rangoEstado[a.estado];
            valorB = rangoEstado[b.estado];
        } else if (ordenColumna.value === 'fecha_emision' || ordenColumna.value === 'fecha_vencimiento') {
            valorA = fechaAOrden(a[ordenColumna.value]);
            valorB = fechaAOrden(b[ordenColumna.value]);
        } else {
            valorA = a[ordenColumna.value];
            valorB = b[ordenColumna.value];
        }

        if (valorA < valorB) return -1 * factor;
        if (valorA > valorB) return 1 * factor;
        return 0;
    });
});

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
                    <Link v-if="puedeAdministrarCatalogos" href="/tipos-certificacion"
                        class="border px-4 py-2 rounded hover:bg-gray-50">
                        Tipos de certificación
                    </Link>
                    <Link v-if="puedeAprobarExamenes" href="/certificados-examenes"
                        class="border px-4 py-2 rounded hover:bg-gray-50">
                        Exámenes pendientes<span v-if="examenesPendientesCount > 0"> ({{ examenesPendientesCount }})</span>
                    </Link>
                    <Link v-if="puedeCrear" href="/certificados/create"
                        class="bg-brand text-white px-4 py-2 rounded hover:bg-brand-dark">
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
                        <th class="p-3 text-left text-sm">
                            <button type="button" @click="ordenarPor('colaborador')" class="inline-flex items-center gap-1 hover:text-gray-900">
                                Colaborador
                                <component :is="ordenColumna === 'colaborador' ? (ordenAscendente ? ChevronUp : ChevronDown) : ChevronsUpDown" class="h-3.5 w-3.5 text-gray-400" />
                            </button>
                        </th>
                        <th class="p-3 text-left text-sm">Tipo de certificado</th>
                        <th class="p-3 text-left text-sm">
                            <button type="button" @click="ordenarPor('fecha_emision')" class="inline-flex items-center gap-1 hover:text-gray-900">
                                Emisión
                                <component :is="ordenColumna === 'fecha_emision' ? (ordenAscendente ? ChevronUp : ChevronDown) : ChevronsUpDown" class="h-3.5 w-3.5 text-gray-400" />
                            </button>
                        </th>
                        <th class="p-3 text-left text-sm">
                            <button type="button" @click="ordenarPor('fecha_vencimiento')" class="inline-flex items-center gap-1 hover:text-gray-900">
                                Vencimiento
                                <component :is="ordenColumna === 'fecha_vencimiento' ? (ordenAscendente ? ChevronUp : ChevronDown) : ChevronsUpDown" class="h-3.5 w-3.5 text-gray-400" />
                            </button>
                        </th>
                        <th class="p-3 text-left text-sm">
                            <button type="button" @click="ordenarPor('estado')" class="inline-flex items-center gap-1 hover:text-gray-900">
                                Estado
                                <component :is="ordenColumna === 'estado' ? (ordenAscendente ? ChevronUp : ChevronDown) : ChevronsUpDown" class="h-3.5 w-3.5 text-gray-400" />
                            </button>
                        </th>
                        <th class="p-3 text-left text-sm">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="certificado in certificadosOrdenados" :key="certificado.id"
                        class="border-t even:bg-gray-50 hover:bg-gray-100">
                        <td class="p-3 text-sm">{{ certificado.colaborador }}</td>
                        <td class="p-3 text-sm">{{ certificado.tipo_certificado }}</td>
                        <td class="p-3 text-sm">{{ certificado.fecha_emision }}</td>
                        <td class="p-3 text-sm">{{ certificado.fecha_vencimiento }}</td>
                        <td class="p-3 text-sm">
                            <span :class="estiloEstado[certificado.estado]"
                                class="px-2 py-1 rounded text-xs font-medium inline-block text-center min-w-[88px]">
                                {{ etiquetaEstado[certificado.estado] }}
                            </span>
                        </td>
                        <td class="p-3 text-sm">
                            <div class="flex gap-1">
                                <Link v-if="certificado.puede_ver" :href="`/certificados/${certificado.id}`"
                                    title="Ver" class="inline-flex items-center justify-center rounded p-1.5 text-gray-600 hover:bg-gray-200">
                                    <Eye class="h-4 w-4" />
                                </Link>
                                <Link v-if="certificado.puede_editar" :href="`/certificados/${certificado.id}/edit`"
                                    title="Editar" class="inline-flex items-center justify-center rounded p-1.5 text-gray-600 hover:bg-gray-200">
                                    <Pencil class="h-4 w-4" />
                                </Link>
                                <button v-if="certificado.puede_proponer_examen"
                                    @click="abrirModalExamen(certificado)"
                                    title="Calendarizar examen" class="inline-flex items-center justify-center rounded p-1.5 text-gray-600 hover:bg-gray-200">
                                    <CalendarPlus class="h-4 w-4" />
                                </button>
                                <button v-if="certificado.puede_eliminar" @click="eliminar(certificado.id)"
                                    title="Eliminar" class="inline-flex items-center justify-center rounded p-1.5 text-red-600 hover:bg-red-50">
                                    <Trash2 class="h-4 w-4" />
                                </button>
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
                            class="bg-brand text-white px-6 py-2 rounded hover:bg-brand-dark">
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
