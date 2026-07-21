<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    etiqueta: Object, // null cuando se está creando
});

const esEdicion = computed(() => props.etiqueta !== null);

const form = useForm({
    nombre: props.etiqueta?.nombre ?? '',
});

function guardar() {
    if (esEdicion.value) {
        form.put(`/etiquetas/${props.etiqueta.id}`);
    } else {
        form.post('/etiquetas');
    }
}
</script>

<template>
    <AppLayout :title="esEdicion ? 'Editar Etiqueta' : 'Nueva Etiqueta'">
        <div class="p-6 max-w-2xl mx-auto">
            <h1 class="text-2xl font-semibold mb-6">{{ esEdicion ? 'Editar Etiqueta' : 'Nueva Etiqueta' }}</h1>

            <form @submit.prevent="guardar" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Nombre</label>
                    <input v-model="form.nombre" type="text" required maxlength="50"
                        placeholder="Ej. Avaya, Cisco, Docker..."
                        class="w-full border rounded p-2" />
                    <p v-if="form.errors.nombre" class="text-red-500 text-xs mt-1">{{ form.errors.nombre }}</p>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" :disabled="form.processing"
                        class="bg-brand text-white px-6 py-2 rounded hover:bg-brand-dark">
                        Guardar
                    </button>
                    <a href="/etiquetas" class="border px-6 py-2 rounded hover:bg-gray-50">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
