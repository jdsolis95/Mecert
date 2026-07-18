<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    certificado: Object,
    historiales: Array,
    examenes: Array,
    puedeEditar: Boolean,
});

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

const etiquetaEstadoExamen = {
    pendiente: 'Pendiente',
    aprobado: 'Aprobado',
    rechazado: 'Rechazado',
};

const estiloEstadoExamen = {
    pendiente: 'bg-yellow-100 text-yellow-700',
    aprobado: 'bg-green-100 text-green-700',
    rechazado: 'bg-red-100 text-red-700',
};
</script>

<template>
    <AppLayout title="Detalle del Certificado">
        <div class="p-6 max-w-3xl mx-auto space-y-6">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-semibold text-gray-800">Detalle del Certificado</h1>
                <div class="flex gap-3">
                    <Link v-if="puedeEditar" :href="`/certificados/${certificado.id}/edit`"
                        class="border px-4 py-2 rounded hover:bg-gray-50">
                        Editar
                    </Link>
                    <Link href="/certificados" class="border px-4 py-2 rounded hover:bg-gray-50">
                        Volver
                    </Link>
                </div>
            </div>

            <div class="bg-white shadow rounded p-6 grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Colaborador</p>
                    <p class="font-medium">{{ certificado.colaborador }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Tipo de certificado</p>
                    <p class="font-medium">{{ certificado.tipo_certificado }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Fecha de emisión</p>
                    <p class="font-medium">{{ certificado.fecha_emision }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Fecha de vencimiento</p>
                    <p class="font-medium">{{ certificado.fecha_vencimiento }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Estado</p>
                    <span :class="estiloEstado[certificado.estado]" class="px-2 py-1 rounded text-xs font-medium">
                        {{ etiquetaEstado[certificado.estado] }}
                    </span>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Documento adjunto</p>
                    <a v-if="certificado.documento_url" :href="certificado.documento_url" target="_blank"
                        class="text-blue-600 hover:underline">
                        {{ certificado.documento_nombre_original }}
                    </a>
                    <p v-else class="text-gray-400">Sin documento adjunto</p>
                </div>
            </div>

            <div class="bg-white shadow rounded p-6">
                <h2 class="text-lg font-semibold mb-4">Exámenes de renovación</h2>
                <div v-if="examenes.length === 0" class="text-gray-400 text-sm">
                    No hay exámenes calendarizados.
                </div>
                <ul v-else class="space-y-3">
                    <li v-for="examen in examenes" :key="examen.id" class="border-t pt-3 first:border-t-0 first:pt-0">
                        <div class="flex justify-between items-center">
                            <p class="text-sm">
                                Propuesto por <span class="font-medium">{{ examen.propuesto_por }}</span>
                                para el <span class="font-medium">{{ examen.fecha_propuesta }}</span>
                                <span v-if="examen.lugar_propuesto"> en {{ examen.lugar_propuesto }}</span>
                            </p>
                            <span :class="estiloEstadoExamen[examen.estado]" class="px-2 py-1 rounded text-xs font-medium">
                                {{ etiquetaEstadoExamen[examen.estado] }}
                            </span>
                        </div>
                        <p v-if="examen.estado !== 'pendiente'" class="text-sm text-gray-500 mt-1">
                            {{ examen.estado === 'aprobado' ? 'Aprobado' : 'Rechazado' }} por {{ examen.decidido_por }}
                            <span v-if="examen.fecha_aprobada"> — fecha aprobada: {{ examen.fecha_aprobada }}</span>
                            <span v-if="examen.lugar_aprobado"> en {{ examen.lugar_aprobado }}</span>
                        </p>
                        <p v-if="examen.comentario" class="text-sm text-gray-500 mt-1">
                            Comentario: {{ examen.comentario }}
                        </p>
                    </li>
                </ul>
            </div>

            <div class="bg-white shadow rounded p-6">
                <h2 class="text-lg font-semibold mb-4">Historial de cambios</h2>
                <div v-if="historiales.length === 0" class="text-gray-400 text-sm">
                    Sin cambios registrados.
                </div>
                <ul v-else class="space-y-3">
                    <li v-for="historial in historiales" :key="historial.id" class="border-t pt-3 first:border-t-0 first:pt-0">
                        <p class="text-sm">
                            Modificado por <span class="font-medium">{{ historial.editado_por }}</span>
                            el {{ historial.fecha }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            Valores anteriores: {{ historial.datos_anteriores.tipo_certificado }},
                            {{ historial.datos_anteriores.fecha_emision }} — {{ historial.datos_anteriores.fecha_vencimiento }}
                        </p>
                    </li>
                </ul>
            </div>
        </div>
    </AppLayout>
</template>
