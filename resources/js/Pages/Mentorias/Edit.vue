<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    mentoria: Object,
    etiquetasDisponibles: Array,
});

const form = useForm({
    titulo: props.mentoria.titulo,
    descripcion: props.mentoria.descripcion,
    etiquetas: [...props.mentoria.etiquetas],
    multimedia_tipo: props.mentoria.multimedia_tipo ?? '',
    multimedia_archivo: null,
    enlaces: props.mentoria.enlaces.map((enlace) => ({ ...enlace })),
});

const nuevaEtiqueta = ref('');

function agregarEtiqueta() {
    const valor = nuevaEtiqueta.value.trim();
    if (valor && !form.etiquetas.includes(valor)) {
        form.etiquetas.push(valor);
    }
    nuevaEtiqueta.value = '';
}

function quitarEtiqueta(indice) {
    form.etiquetas.splice(indice, 1);
}

function alSeleccionarArchivo(evento) {
    form.multimedia_archivo = evento.target.files[0] ?? null;
}

function agregarEnlace() {
    form.enlaces.push({ url: '', texto: '' });
}

function quitarEnlace(indice) {
    form.enlaces.splice(indice, 1);
}

function guardar() {
    form.transform((datos) => ({ ...datos, _method: 'put' })).post(`/mentorias/${props.mentoria.id}`);
}
</script>

<template>
    <AppLayout title="Editar Mentoría">
        <div class="p-6 max-w-2xl mx-auto">
            <h1 class="text-2xl font-semibold mb-6">Editar Mentoría</h1>

            <form @submit.prevent="guardar" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Título</label>
                    <input v-model="form.titulo" type="text" required maxlength="150"
                        class="w-full border rounded p-2" />
                    <p v-if="form.errors.titulo" class="text-red-500 text-xs mt-1">{{ form.errors.titulo }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Descripción</label>
                    <textarea v-model="form.descripcion" required rows="5"
                        class="w-full border rounded p-2"></textarea>
                    <p v-if="form.errors.descripcion" class="text-red-500 text-xs mt-1">{{ form.errors.descripcion }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Etiquetas</label>
                    <div class="flex gap-2">
                        <input v-model="nuevaEtiqueta" type="text" list="etiquetas-list"
                            @keydown.enter.prevent="agregarEtiqueta"
                            placeholder="Ej. Avaya, Ruckus, Cisco..."
                            class="flex-1 border rounded p-2" />
                        <datalist id="etiquetas-list">
                            <option v-for="etiqueta in etiquetasDisponibles" :key="etiqueta" :value="etiqueta" />
                        </datalist>
                        <button type="button" @click="agregarEtiqueta"
                            class="border px-4 rounded hover:bg-gray-50">
                            Agregar
                        </button>
                    </div>
                    <div v-if="form.etiquetas.length" class="flex flex-wrap gap-1 mt-2">
                        <span v-for="(etiqueta, indice) in form.etiquetas" :key="etiqueta"
                            class="text-xs pl-2 pr-1 py-0.5 rounded-full bg-blue-50 text-blue-700 flex items-center gap-1">
                            {{ etiqueta }}
                            <button type="button" @click="quitarEtiqueta(indice)"
                                class="text-blue-400 hover:text-blue-700">×</button>
                        </span>
                    </div>
                    <p v-if="form.errors.etiquetas" class="text-red-500 text-xs mt-1">{{ form.errors.etiquetas }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Multimedia</label>
                    <select v-model="form.multimedia_tipo" class="w-full border rounded p-2">
                        <option value="">Ninguno</option>
                        <option value="imagen">Imagen</option>
                        <option value="documento">Documento</option>
                        <option value="video">Video</option>
                    </select>

                    <p v-if="mentoria.multimedia_tipo === form.multimedia_tipo && (mentoria.multimedia_preview_url || mentoria.multimedia_nombre_original)"
                        class="text-xs text-gray-500 mt-1">
                        Archivo actual: {{ mentoria.multimedia_nombre_original || 'imagen cargada' }}
                        (deja el campo vacío para conservarlo)
                    </p>

                    <div v-if="form.multimedia_tipo === 'imagen'" class="mt-2">
                        <input type="file" accept="image/png,image/jpeg,image/webp" @change="alSeleccionarArchivo"
                            class="w-full border rounded p-2" />
                    </div>
                    <div v-else-if="form.multimedia_tipo === 'documento'" class="mt-2">
                        <input type="file" accept=".pdf" @change="alSeleccionarArchivo"
                            class="w-full border rounded p-2" />
                    </div>
                    <div v-else-if="form.multimedia_tipo === 'video'" class="mt-2 space-y-2">
                        <input type="file" accept="video/mp4,.mp4" @change="alSeleccionarArchivo"
                            class="w-full border rounded p-2" />
                        <p class="text-xs text-gray-500">Tamaño máximo de video: 100MB. Formato aceptado: .mp4</p>
                    </div>

                    <p v-if="form.errors.multimedia_archivo" class="text-red-500 text-xs mt-1">{{ form.errors.multimedia_archivo }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Enlaces externos</label>
                    <div v-for="(enlace, indice) in form.enlaces" :key="indice" class="flex gap-2 mb-2">
                        <input v-model="enlace.texto" type="text" placeholder="Texto (opcional)"
                            class="w-1/3 border rounded p-2" />
                        <input v-model="enlace.url" type="url" placeholder="https://..."
                            class="flex-1 border rounded p-2" />
                        <button type="button" @click="quitarEnlace(indice)"
                            class="border px-3 rounded text-red-600 hover:bg-red-50">
                            Quitar
                        </button>
                        <p v-if="form.errors[`enlaces.${indice}.url`]" class="text-red-500 text-xs mt-1">
                            {{ form.errors[`enlaces.${indice}.url`] }}
                        </p>
                    </div>
                    <button type="button" @click="agregarEnlace"
                        class="border px-4 py-1 rounded text-sm hover:bg-gray-50">
                        + Agregar enlace
                    </button>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" :disabled="form.processing"
                        class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        Guardar
                    </button>
                    <a :href="`/mentorias/${mentoria.id}`" class="border px-6 py-2 rounded hover:bg-gray-50">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
