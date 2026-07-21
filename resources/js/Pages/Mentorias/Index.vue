<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Image as ImageIcon, FileText, Video, LayoutGrid, Grid3x3, List } from 'lucide-vue-next';

//tipo de icono
const iconoPorTipo = { imagen: ImageIcon, documento: FileText, video: Video };

//definir props
const props = defineProps({
    mentorias: Object,
    etiquetasDisponibles: Array,
    filtros: Object,
    puedeAdministrarCatalogos: Boolean,
});

//defiincion tipo de vistas
const vistas = [
    { valor: 'grande', etiqueta: 'Cuadrícula grande', icono: LayoutGrid },
    { valor: 'pequena', etiqueta: 'Cuadrícula pequeña', icono: Grid3x3 },
    { valor: 'lista', etiqueta: 'Lista con detalles', icono: List },
];

//variable modos de vista
const modoVista = ref(localStorage.getItem('mentorias_vista') ?? 'grande');

function cambiarVista(valor) {
    modoVista.value = valor;
    localStorage.setItem('mentorias_vista', valor);
}

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

//aplica cambio de etiqueta
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
                <div class="flex gap-3">
                    <Link v-if="puedeAdministrarCatalogos" href="/etiquetas"
                        class="border px-4 py-2 rounded hover:bg-gray-50">
                        Etiquetas
                    </Link>
                    <Link href="/mentorias/create"
                        class="bg-brand text-white px-4 py-2 rounded hover:bg-brand-dark">
                        + Agregar Mentoría
                    </Link>
                </div>
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
                        ? 'bg-brand text-white border-brand'
                        : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50'"
                    class="text-xs px-3 py-1 rounded-full border capitalize">
                    {{ etiqueta.nombre }}
                </button>
            </div>
            <!-- OPCIONES DE CAMBIO DE VISTAS EN MODULO MENTORIAS -->
            <div class="flex items-center justify-end gap-1 mb-4">
                <button v-for="vista in vistas" :key="vista.valor"
                    type="button"
                    @click="cambiarVista(vista.valor)"
                    :title="vista.etiqueta"
                    :class="modoVista === vista.valor
                        ? 'bg-brand text-white border-brand'
                        : 'bg-white text-gray-500 border-gray-300 hover:bg-gray-100'"
                    class="inline-flex items-center justify-center rounded p-1.5 border">
                    <component :is="vista.icono" class="h-4 w-4" />
                </button>
            </div>

            <div v-if="mentorias.data.length === 0" class="text-center text-gray-400 py-12">
                No hay mentorías que coincidan con la búsqueda.
            </div>

            <!-- Vista: iconos grandes -->
            <div v-else-if="modoVista === 'grande'" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Link v-for="mentoria in mentorias.data" :key="mentoria.id"
                    :href="`/mentorias/${mentoria.id}`"
                    class="block bg-white border rounded shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                    <div class="relative">
                        <img v-if="mentoria.multimedia?.tipo === 'imagen'" :src="mentoria.multimedia.url"
                            class="w-full h-40 object-cover" />
                        <div v-else-if="mentoria.multimedia?.tipo === 'documento'"
                            class="w-full h-40 bg-gray-50 flex items-center justify-center text-gray-400 text-sm">
                            {{ mentoria.multimedia.nombre_original }}
                        </div>
                        <div v-else-if="mentoria.multimedia?.tipo === 'video'"
                            class="w-full h-40 bg-gray-50 flex items-center justify-center text-gray-400 text-sm">
                            {{ mentoria.multimedia.nombre_original || 'Video' }}
                        </div>

                        <span v-if="mentoria.multimedia?.tipo" class="absolute top-2 left-2 inline-flex items-center justify-center rounded-full bg-white/90 p-1.5 shadow">
                            <component :is="iconoPorTipo[mentoria.multimedia.tipo]" class="h-4 w-4 text-gray-600" />
                        </span>
                    </div>

                    <div class="p-4">
                        <h2 class="font-semibold text-gray-800 mb-1">{{ mentoria.titulo }}</h2>
                        <p class="text-sm text-gray-500 mb-3">{{ mentoria.descripcion }}</p>

                        <div v-if="mentoria.etiquetas.length" class="flex flex-wrap gap-1 mb-3">
                            <span v-for="etiqueta in mentoria.etiquetas" :key="etiqueta.id"
                                class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-black capitalize">
                                {{ etiqueta.nombre }}
                            </span>
                        </div>

                        <p class="text-xs text-gray-400">{{ mentoria.autor }} · {{ mentoria.fecha }}</p>
                    </div>
                </Link>
            </div>

            <!-- Vista: iconos pequeños -->
            <div v-else-if="modoVista === 'pequena'" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                <Link v-for="mentoria in mentorias.data" :key="mentoria.id"
                    :href="`/mentorias/${mentoria.id}`"
                    class="block bg-white border rounded shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                    <div class="relative">
                        <img v-if="mentoria.multimedia?.tipo === 'imagen'" :src="mentoria.multimedia.url"
                            class="w-full h-20 object-cover" />
                        <div v-else
                            class="w-full h-20 bg-gray-50 flex items-center justify-center text-gray-300">
                            <component :is="mentoria.multimedia?.tipo ? iconoPorTipo[mentoria.multimedia.tipo] : ImageIcon" class="h-6 w-6" />
                        </div>
                    </div>
                    <p class="text-xs font-medium text-gray-700 p-2 truncate">{{ mentoria.titulo }}</p>
                </Link>
            </div>

            <!-- Vista: lista con detalles -->
            <div v-else class="space-y-2">
                <Link v-for="mentoria in mentorias.data" :key="mentoria.id"
                    :href="`/mentorias/${mentoria.id}`"
                    class="flex items-center gap-4 bg-white border rounded p-3 hover:bg-gray-50 transition-colors">
                    <div class="relative shrink-0">
                        <img v-if="mentoria.multimedia?.tipo === 'imagen'" :src="mentoria.multimedia.url"
                            class="h-14 w-14 rounded object-cover" />
                        <div v-else class="h-14 w-14 rounded bg-gray-50 flex items-center justify-center text-gray-300">
                            <component :is="mentoria.multimedia?.tipo ? iconoPorTipo[mentoria.multimedia.tipo] : ImageIcon" class="h-6 w-6" />
                        </div>
                    </div>

                    <div class="min-w-0 flex-1">
                        <h2 class="font-semibold text-gray-800">{{ mentoria.titulo }}</h2>
                        <p class="text-sm text-gray-500 truncate">{{ mentoria.descripcion }}</p>
                        <div v-if="mentoria.etiquetas.length" class="flex flex-wrap gap-1 mt-1">
                            <span v-for="etiqueta in mentoria.etiquetas" :key="etiqueta.id"
                                class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-black capitalize">
                                {{ etiqueta.nombre }}
                            </span>
                        </div>
                    </div>

                    <p class="text-xs text-gray-400 shrink-0 whitespace-nowrap">{{ mentoria.autor }} · {{ mentoria.fecha }}</p>
                </Link>
            </div>

            <div v-if="mentorias.links.length > 3" class="flex flex-wrap gap-1 mt-6">
                <template v-for="(link, indice) in mentorias.links" :key="indice">
                    <Link v-if="link.url" :href="link.url" preserve-scroll
                        :class="link.active ? 'bg-brand text-white border-brand' : 'bg-white text-gray-600 hover:bg-gray-50'"
                        class="text-sm px-3 py-1 rounded border" v-html="link.label" />
                    <span v-else class="text-sm px-3 py-1 rounded border text-gray-300" v-html="link.label" />
                </template>
            </div>
        </div>
    </AppLayout>
</template>
