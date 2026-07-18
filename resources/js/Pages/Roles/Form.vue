<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    rol: Object, // null cuando se está creando
    modulos: Object, // { 'modulo.usuarios': 'Usuarios', ... }
    permisosAsignados: Array,
});

const esEdicion = computed(() => props.rol !== null);
const esRolBase = computed(() => props.rol?.es_rol_base ?? false);

const form = useForm({
    nombre: props.rol?.nombre ?? '',
    permisos: [...props.permisosAsignados],
});

function guardar() {
    if (esEdicion.value) {
        form.put(`/roles/${props.rol.id}`);
    } else {
        form.post('/roles');
    }
}
</script>

<template>
    <AppLayout :title="esEdicion ? 'Editar Rol' : 'Nuevo Rol'">
        <div class="p-6 max-w-2xl mx-auto">
            <h1 class="text-2xl font-semibold mb-6">{{ esEdicion ? 'Editar Rol' : 'Nuevo Rol' }}</h1>

            <form @submit.prevent="guardar" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Nombre del rol</label>
                    <input v-model="form.nombre" type="text" required :disabled="esRolBase"
                        class="w-full border rounded p-2 disabled:bg-gray-100 disabled:text-gray-500" />
                    <p v-if="esRolBase" class="text-xs text-gray-400 mt-1">
                        Los roles base del sistema no se pueden renombrar.
                    </p>
                    <p v-if="form.errors.nombre" class="text-red-500 text-xs mt-1">{{ form.errors.nombre }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Módulos habilitados</label>
                    <div class="grid grid-cols-2 gap-2 border rounded p-3">
                        <label v-for="(etiqueta, permiso) in modulos" :key="permiso"
                            class="flex items-center gap-2 text-sm">
                            <input type="checkbox" :value="permiso" v-model="form.permisos" />
                            {{ etiqueta }}
                        </label>
                    </div>
                    <p v-if="form.errors.permisos" class="text-red-500 text-xs mt-1">{{ form.errors.permisos }}</p>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" :disabled="form.processing"
                        class="bg-brand text-gray-900 px-6 py-2 rounded hover:bg-brand-dark">
                        Guardar
                    </button>
                    <a href="/roles" class="border px-6 py-2 rounded hover:bg-gray-50">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
