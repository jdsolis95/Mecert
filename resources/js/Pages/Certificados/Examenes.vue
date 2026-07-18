<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    examenes: Array,
});

const mostrarModal = ref(false);
const examenSeleccionado = ref(null);

const form = useForm({
    accion: '',
    fecha_aprobada: '',
    lugar_aprobado: '',
    comentario: '',
});

function abrirModal(examen, accion) {
    examenSeleccionado.value = examen;
    form.reset();
    form.clearErrors();
    form.accion = accion;
    mostrarModal.value = true;
}

function cerrarModal() {
    mostrarModal.value = false;
    examenSeleccionado.value = null;
}

function decidir() {
    form
        .transform((datos) => ({ ...datos, _method: 'patch' }))
        .post(`/certificados-examenes/${examenSeleccionado.value.id}`, {
            onSuccess: () => cerrarModal(),
        });
}
</script>

<template>
    <AppLayout title="Exámenes pendientes">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-800">Exámenes de renovación pendientes</h1>
                <a href="/certificados" class="border px-4 py-2 rounded hover:bg-gray-50">Volver a certificados</a>
            </div>

            <div v-if="examenes.length === 0" class="text-center text-gray-400 py-12">
                No hay exámenes pendientes de aprobación.
            </div>

            <table v-else class="w-full border-collapse bg-white shadow rounded">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3 text-left text-sm">Colaborador</th>
                        <th class="p-3 text-left text-sm">Certificado</th>
                        <th class="p-3 text-left text-sm">Fecha propuesta</th>
                        <th class="p-3 text-left text-sm">Lugar propuesto</th>
                        <th class="p-3 text-left text-sm">Propuesto por</th>
                        <th class="p-3 text-left text-sm">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="examen in examenes" :key="examen.id" class="border-t hover:bg-gray-50">
                        <td class="p-3 text-sm">{{ examen.certificado.colaborador }}</td>
                        <td class="p-3 text-sm">{{ examen.certificado.tipo_certificado }}</td>
                        <td class="p-3 text-sm">{{ examen.fecha_propuesta }}</td>
                        <td class="p-3 text-sm">{{ examen.lugar_propuesto ?? '—' }}</td>
                        <td class="p-3 text-sm">{{ examen.propuesto_por }}</td>
                        <td class="p-3 text-sm flex gap-2">
                            <button @click="abrirModal(examen, 'aprobar')"
                                class="text-green-600 hover:underline">Aprobar</button>
                            <button @click="abrirModal(examen, 'rechazar')"
                                class="text-red-600 hover:underline">Rechazar</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Modal :show="mostrarModal" @close="cerrarModal">
            <div class="p-6" v-if="examenSeleccionado">
                <h2 class="text-lg font-semibold mb-4">
                    {{ form.accion === 'aprobar' ? 'Aprobar' : 'Rechazar' }} examen —
                    {{ examenSeleccionado.certificado.colaborador }} ({{ examenSeleccionado.certificado.tipo_certificado }})
                </h2>
                <form @submit.prevent="decidir" class="space-y-4">
                    <template v-if="form.accion === 'aprobar'">
                        <div>
                            <label class="block text-sm font-medium mb-1">Fecha para presentarse</label>
                            <input v-model="form.fecha_aprobada" type="date" required
                                class="w-full border rounded p-2" />
                            <p v-if="form.errors.fecha_aprobada" class="text-red-500 text-xs mt-1">
                                {{ form.errors.fecha_aprobada }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Lugar para presentarse</label>
                            <input v-model="form.lugar_aprobado" type="text" required maxlength="150"
                                class="w-full border rounded p-2" />
                            <p v-if="form.errors.lugar_aprobado" class="text-red-500 text-xs mt-1">
                                {{ form.errors.lugar_aprobado }}
                            </p>
                        </div>
                    </template>
                    <div>
                        <label class="block text-sm font-medium mb-1">Comentario (opcional)</label>
                        <textarea v-model="form.comentario" rows="3" maxlength="500"
                            class="w-full border rounded p-2"></textarea>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="submit" :disabled="form.processing"
                            class="bg-brand text-white px-6 py-2 rounded hover:bg-brand-dark">
                            Confirmar
                        </button>
                        <button type="button" @click="cerrarModal"
                            class="border px-6 py-2 rounded hover:bg-gray-50">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AppLayout>
</template>
