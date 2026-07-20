<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    activa: String,
});
</script>

<template>
    <div class="flex gap-1 border-b border-gray-200 mb-6">
        <Link href="/bitacoras"
            :class="activa === 'movimientos' ? 'border-brand text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700'"
            class="px-4 py-2 text-sm font-medium border-b-2 -mb-px">
            Movimientos
        </Link>
        <Link href="/bitacora-accesos"
            :class="activa === 'accesos' ? 'border-brand text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700'"
            class="px-4 py-2 text-sm font-medium border-b-2 -mb-px">
            Ingresos y salidas
        </Link>
    </div>
</template>
