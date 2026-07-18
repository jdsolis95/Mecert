<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    mentorias: Object,
    etiquetasDisponibles: Array,
    filtros: Object,
});

const q = ref(props.filtros.q ?? '');
const seleccionadas = ref([...(props.filtros.etiquetas ?? [])]);
let temporizador = null;

function buscar() {
    router.get('/mentorias', { q: q.value, etiquetas: seleccionadas.value }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function alEscribir() {
    clearTimeout(temporizador);
    temporizador = setTimeout(buscar, 400);
}

function alternarEtiqueta(id) {
    const indice = seleccionadas.value.indexOf(id);
    if (indice === -1) {
        seleccionadas.value.push(id);
    } else {
        seleccionadas.value.splice(indice, 1);
    }
    buscar();
}
</script>

<template>
    <AppLayout title="Mentorías">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-800">Mentorías</h1>
                <Link href="/mentorias/create"
                    class="bg-brand text-gray-900 px-4 py-2 rounded hover:bg-brand-dark">
                    + Nueva Mentoría
                </Link>
            </div>

            <div class="mb-4">
                <input v-model="q" @input="alEscribir" type="text"
                    placeholder="Buscar por título, autor o etiqueta..."
                    class="w-full border rounded p-2" />
            </div>

            <div v-if="etiquetasDisponibles.length" class="flex flex-wrap gap-2 mb-6">
                <button v-for="etiqueta in etiquetasDisponibles" :key="etiqueta.id"
                    @click="alternarEtiqueta(etiqueta.id)"
                    type="button"
                    :class="seleccionadas.includes(etiqueta.id)
                        ? 'bg-brand text-gray-900 border-brand'
                        : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50'"
                    class="text-xs px-3 py-1 rounded-full border">
                    {{ etiqueta.nombre }}
                </button>
            </div>

            <div v-if="mentorias.data.length === 0" class="text-center text-gray-400 py-12">
                No hay mentorías que coincidan con la búsqueda.
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Link v-for="mentoria in mentorias.data" :key="mentoria.id"
                    :href="`/mentorias/${mentoria.id}`"
                    class="block bg-white border rounded shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                    <img v-if="mentoria.multimedia?.tipo === 'imagen'" :src="mentoria.multimedia.url"
                        class="w-full h-40 object-cover" />
                    <div v-else-if="mentoria.multimedia?.tipo === 'documento'"
                        class="w-full h-40 bg-gray-50 flex items-center justify-center text-gray-400 text-sm">
                        📄 {{ mentoria.multimedia.nombre_original }}
                    </div>
                    <div v-else-if="mentoria.multimedia?.tipo === 'video'"
                        class="w-full h-40 bg-gray-50 flex items-center justify-center text-gray-400 text-sm">
                        ▶ {{ mentoria.multimedia.nombre_original || 'Video' }}
                    </div>

                    <div class="p-4">
                        <h2 class="font-semibold text-gray-800 mb-1">{{ mentoria.titulo }}</h2>
                        <p class="text-sm text-gray-500 mb-3">{{ mentoria.descripcion }}</p>

                        <div v-if="mentoria.etiquetas.length" class="flex flex-wrap gap-1 mb-3">
                            <span v-for="etiqueta in mentoria.etiquetas" :key="etiqueta.id"
                                class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-black">
                                {{ etiqueta.nombre }}
                            </span>
                        </div>

                        <p class="text-xs text-gray-400">{{ mentoria.autor }} · {{ mentoria.fecha }}</p>
                    </div>
                </Link>
            </div>

            <div v-if="mentorias.links.length > 3" class="flex flex-wrap gap-1 mt-6">
                <template v-for="(link, indice) in mentorias.links" :key="indice">
                    <Link v-if="link.url" :href="link.url" preserve-scroll
                        :class="link.active ? 'bg-brand text-gray-900 border-brand' : 'bg-white text-gray-600 hover:bg-gray-50'"
                        class="text-sm px-3 py-1 rounded border" v-html="link.label" />
                    <span v-else class="text-sm px-3 py-1 rounded border text-gray-300" v-html="link.label" />
                </template>
            </div>
        </div>
    </AppLayout>
</template>
