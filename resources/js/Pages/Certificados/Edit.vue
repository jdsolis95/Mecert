<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    certificado: Object,
    colaboradores: Array,
    colaboradorFijo: Object,
});

const form = useForm({
    colaborador_id: props.certificado.colaborador_id,
    tipo_certificado: props.certificado.tipo_certificado,
    fecha_emision: props.certificado.fecha_emision,
    fecha_vencimiento: props.certificado.fecha_vencimiento,
    documento_adjunto: null,
});

function alSeleccionarArchivo(evento) {
    form.documento_adjunto = evento.target.files[0] ?? null;
}

function guardar() {
    form.transform((datos) => ({ ...datos, _method: 'put' })).post(`/certificados/${props.certificado.id}`);
}
</script>

<template>
    <AppLayout title="Editar Certificado">
        <div class="p-6 max-w-2xl mx-auto">
            <h1 class="text-2xl font-semibold mb-6">Editar Certificado</h1>

            <form @submit.prevent="guardar" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Colaborador</label>
                    <select v-if="colaboradores.length" v-model="form.colaborador_id"
                        class="w-full border rounded p-2">
                        <option v-for="colaborador in colaboradores" :key="colaborador.id" :value="colaborador.id">
                            {{ colaborador.nombre }}
                        </option>
                    </select>
                    <input v-else :value="colaboradorFijo?.nombre" type="text" disabled
                        class="w-full border rounded p-2 bg-gray-50 text-gray-500" />
                    <p v-if="form.errors.colaborador_id" class="text-red-500 text-xs mt-1">
                        {{ form.errors.colaborador_id }}
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Tipo de certificado</label>
                    <input v-model="form.tipo_certificado" type="text" required maxlength="150"
                        class="w-full border rounded p-2" />
                    <p v-if="form.errors.tipo_certificado" class="text-red-500 text-xs mt-1">
                        {{ form.errors.tipo_certificado }}
                    </p>
                </div>

                <div class="flex gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-medium mb-1">Fecha de emisión</label>
                        <input v-model="form.fecha_emision" type="date" required
                            class="w-full border rounded p-2" />
                        <p v-if="form.errors.fecha_emision" class="text-red-500 text-xs mt-1">
                            {{ form.errors.fecha_emision }}
                        </p>
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium mb-1">Fecha de vencimiento</label>
                        <input v-model="form.fecha_vencimiento" type="date" required
                            class="w-full border rounded p-2" />
                        <p v-if="form.errors.fecha_vencimiento" class="text-red-500 text-xs mt-1">
                            {{ form.errors.fecha_vencimiento }}
                        </p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Documento adjunto</label>
                    <p v-if="certificado.documento_nombre_original" class="text-sm mb-2">
                        Actual:
                        <a :href="certificado.documento_url" target="_blank" class="text-brand-darker hover:underline">
                            {{ certificado.documento_nombre_original }}
                        </a>
                    </p>
                    <input type="file" accept=".pdf,.jpg,.jpeg,.png" @change="alSeleccionarArchivo"
                        class="w-full border rounded p-2" />
                    <p class="text-xs text-gray-500 mt-1">Deja este campo vacío para conservar el documento actual.</p>
                    <p v-if="form.errors.documento_adjunto" class="text-red-500 text-xs mt-1">
                        {{ form.errors.documento_adjunto }}
                    </p>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" :disabled="form.processing"
                        class="bg-brand text-white px-6 py-2 rounded hover:bg-brand-dark">
                        Guardar
                    </button>
                    <a href="/certificados" class="border px-6 py-2 rounded hover:bg-gray-50">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
