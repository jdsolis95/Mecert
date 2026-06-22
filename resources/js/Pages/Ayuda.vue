<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    manualUrl: {
        type: String,
        required: true,
    },
});
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
                    :href="manualUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center justify-center rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                >
                    Abrir manual
                </a>
            </div>

            <div class="overflow-hidden rounded border border-gray-200 bg-white shadow-sm">
                <iframe
                    :src="manualUrl"
                    title="Manual de usuario MeCert"
                    class="h-[72vh] w-full"
                />
            </div>
        </section>
    </AppLayout>
</template>
