<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
</script>

<template>
    <AppLayout title="Acerca de">
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-8 space-y-6">

            <div class="text-center">
                <h1 class="text-3xl font-bold text-brand-darker">MeCert</h1>
                <p class="text-gray-500 text-sm mt-1">
                    Sistema de Mentorías y Certificaciones
            </div>

            <hr class="border-gray-100" />

            <!-- Informacoin Basica -->
            <div class="space-y-3 text-sm text-gray-700">
                <div class="flex justify-between">
                    <span class="font-medium text-gray-500">Versión</span>
                    <span>1.0</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium text-gray-500">Empresa</span>
                    <span>Datagrama Comunicaciones S.A.</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium text-gray-500">Desarrollador</span>
                    <span>Juan Diego Solís</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium text-gray-500">Stack</span>
                    <span>PHP | Laravel 12 · Vue 3 · Inertia.js</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium text-gray-500">Fecha Versión</span>
                    <span>07 Agosto 2026</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium text-gray-500">Acrónimo</span>
                    <span>MeCert</span>
                </div>
            </div>

            <hr class="border-gray-100" />

            <!-- Descripción -->
            <p class="text-sm text-gray-600 leading-relaxed">
                MeCert centraliza la gestión de certificaciones técnicas de los 
                ingenieros de Datagrama y el repositorio multimedia de mentorías.
            </p>

            <hr class="border-gray-100" />

            <!-- Notas de version -->
            <div class="text-sm text-gray-600">
                <h2 class="font-medium text-gray-700 mb-2">Notas de la versión</h2>
                <p class="font-medium text-gray-500 text-xs mb-1">1.0.0</p>
                <ul class="list-disc list-inside space-y-1 text-xs">
                    <li>Diseño visual: paleta unificada, tablas con filas alternadas y acciones en iconos.</li>
                    <li>Filtros y ordenamiento en Certificados, Bitácoras y Usuarios.</li>
                    <li>Vista de detalle de solo lectura en Usuarios.</li>
                </ul>
            </div>

        </div>
    </AppLayout>
</template>