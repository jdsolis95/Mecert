<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    usuario: Object,
    esPropio: Boolean,
});
</script>

<template>
    <AppLayout title="Detalle de Usuario">
        <div class="p-6 max-w-2xl mx-auto">
            <Link href="/usuarios" class="text-sm text-brand-darker hover:underline">← Volver al listado</Link>

            <div class="bg-white border rounded shadow-sm mt-4 p-6">
                <div class="flex items-center gap-2 mb-6">
                    <h1 class="text-2xl font-semibold text-gray-800">{{ usuario.nombre_completo }}</h1>
                    <span v-if="esPropio" class="bg-gray-100 text-gray-500 px-2 py-0.5 rounded text-xs">Tú</span>
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Cédula</dt>
                        <dd class="font-medium">{{ usuario.cedula }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Correo</dt>
                        <dd class="font-medium">{{ usuario.email }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Rol</dt>
                        <dd class="font-medium">{{ usuario.rol }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Estado</dt>
                        <dd>
                            <span :class="usuario.esta_activo
                                ? 'bg-green-100 text-green-700'
                                : 'bg-red-100 text-red-700'"
                                class="px-2 py-1 rounded text-xs font-medium inline-block text-center min-w-[70px]">
                                {{ usuario.esta_activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </AppLayout>
</template>
