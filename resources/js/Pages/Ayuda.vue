<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    manualUrl: {
        type: String,
        required: true,
    },
    manualExiste: {
        type: Boolean,
        required: true,
    },
    puedeAdministrar: {
        type: Boolean,
        required: true,
    },
});

const form = useForm({
    manual: null,
});

function alSeleccionarArchivo(evento) {
    form.manual = evento.target.files[0] ?? null;
}

function subirManual() {
    form.post('/ayuda/manual', {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
        },
    });
}
</script>

<template>
    <AppLayout title="Ayuda">
        <section class="space-y-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Manual de usuario</h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Consulta el manual operativo de MeCert.
                    </p>
                </div>

                <a
                    v-if="manualExiste"
                    :href="manualUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center justify-center rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                >
                    Abrir manual
                </a>
            </div>

            <div v-if="puedeAdministrar" class="rounded border border-gray-200 bg-white p-4 shadow-sm">
                <h3 class="text-sm font-medium text-gray-700 mb-2">Actualizar manual (solo Administrador)</h3>
                <form @submit.prevent="subirManual" class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <input type="file" accept="application/pdf,.pdf" @change="alSeleccionarArchivo"
                        class="flex-1 border rounded p-2 text-sm" />
                    <button type="submit" :disabled="form.processing || !form.manual"
                        class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                        Subir PDF
                    </button>
                </form>
                <p v-if="form.errors.manual" class="text-red-500 text-xs mt-1">{{ form.errors.manual }}</p>
                <p class="text-xs text-gray-400 mt-1">Solo PDF, máximo 20MB. Reemplaza el manual actual.</p>
            </div>

            <div v-if="manualExiste" class="overflow-hidden rounded border border-gray-200 bg-white shadow-sm">
                <iframe
                    :src="manualUrl"
                    title="Manual de usuario MeCert"
                    class="h-[72vh] w-full"
                />
            </div>
            <div v-else class="rounded border border-gray-200 bg-white p-6 text-center text-sm text-gray-400 shadow-sm">
                Todavía no se ha cargado el manual de usuario.
            </div>
        </section>
    </AppLayout>
</template>
