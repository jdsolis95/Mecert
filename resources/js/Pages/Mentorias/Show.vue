<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    mentoria: Object,
    puedeEditar: Boolean,
});

function eliminar() {
    if (confirm('¿Desea eliminar esta mentoría? Esta acción no se puede deshacer.')) {
        router.delete(`/mentorias/${props.mentoria.id}`);
    }
}
</script>

<template>
    <AppLayout title="Mentoría">
        <div class="p-6 max-w-3xl mx-auto">
            <Link href="/mentorias" class="text-sm text-blue-600 hover:underline">← Volver al listado</Link>

            <div class="bg-white border rounded shadow-sm mt-4 overflow-hidden">
                <img v-if="mentoria.multimedia?.tipo === 'imagen'" :src="mentoria.multimedia.url"
                    class="w-full max-h-96 object-cover" />

                <div class="p-6">
                    <h1 class="text-2xl font-semibold text-gray-800 mb-2">{{ mentoria.titulo }}</h1>
                    <p class="text-xs text-gray-400 mb-4">{{ mentoria.autor }} · {{ mentoria.fecha }}</p>

                    <div v-if="mentoria.etiquetas.length" class="flex flex-wrap gap-1 mb-4">
                        <span v-for="etiqueta in mentoria.etiquetas" :key="etiqueta.id"
                            class="text-xs px-2 py-0.5 rounded-full bg-blue-50 text-blue-700">
                            {{ etiqueta.nombre }}
                        </span>
                    </div>

                    <p class="text-gray-700 whitespace-pre-line mb-6">{{ mentoria.descripcion }}</p>

                    <div v-if="mentoria.multimedia?.tipo === 'documento'" class="mb-6">
                        <a :href="mentoria.multimedia.url" target="_blank" rel="noopener noreferrer"
                            class="text-blue-600 hover:underline text-sm">
                            📄 Ver documento: {{ mentoria.multimedia.nombre_original }}
                        </a>
                    </div>
                    <div v-else-if="mentoria.multimedia?.tipo === 'video' && mentoria.multimedia.nombre_original" class="mb-6">
                        <video controls class="w-full rounded" preload="metadata">
                            <source :src="mentoria.multimedia.url" type="video/mp4" />
                            Tu navegador no soporta la reproducción de video.
                        </video>
                    </div>
                    <div v-else-if="mentoria.multimedia?.tipo === 'video'" class="mb-6">
                        <a :href="mentoria.multimedia.url" target="_blank" rel="noopener noreferrer"
                            class="text-blue-600 hover:underline text-sm">
                            ▶ Ver video
                        </a>
                    </div>

                    <div v-if="mentoria.enlaces.length" class="mb-6">
                        <h2 class="text-sm font-medium text-gray-600 mb-2">Enlaces externos</h2>
                        <ul class="space-y-1">
                            <li v-for="(enlace, indice) in mentoria.enlaces" :key="indice">
                                <a :href="enlace.url" target="_blank" rel="noopener noreferrer"
                                    class="text-blue-600 hover:underline text-sm">
                                    {{ enlace.texto || enlace.url }}
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div v-if="puedeEditar" class="flex gap-3 pt-4 border-t">
                        <Link :href="`/mentorias/${mentoria.id}/edit`"
                            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            Editar
                        </Link>
                        <button @click="eliminar"
                            class="border border-red-300 text-red-600 px-4 py-2 rounded hover:bg-red-50">
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
