<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
</script>

<template>
    <AppLayout title="Acerca de">
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-8 space-y-6">

            <div class="text-center">
                <h1 class="text-3xl font-bold text-brand-darker">MeCert</h1>
                <p class="text-gray-500 text-sm mt-1">
                    Sistema de Mentorías y Certificaciones (MeCert)
                </p>
            </div>

            <hr class="border-gray-100" />

            <!-- Informacoin Basica -->
            <div class="space-y-3 text-sm text-gray-700">
                <div class="flex justify-between">
                    <span class="font-medium text-gray-500">Versión</span>
                    <span>1.0.0</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium text-gray-500">Empresa</span>
                    <span>Datagrama Comunicaciones S.A.</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium text-gray-500">Desarrollado por</span>
                    <span>Juan Diego Solís</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium text-gray-500">Stack</span>
                    <span>Laravel 12 · Vue 3 · Inertia.js</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium text-gray-500">Año</span>
                    <span>2026</span>
                </div>
            </div>

            <hr class="border-gray-100" />

            <!-- Descripción -->
            <p class="text-sm text-gray-600 leading-relaxed">
                MeCert centraliza la gestión de certificaciones técnicas de los ingenieros
                de Datagrama y el repositorio multimedia de mentorías, permitiendo a los
                ejecutivos de negocio consultar esta información durante procesos de licitación.
            </p>

        </div>
    </AppLayout>
</template>