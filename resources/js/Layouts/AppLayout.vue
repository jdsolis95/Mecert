<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

// usePage() da acceso a los datos globales que Laravel comparte en cada request
const pagina = usePage();

// El usuario autenticado y su rol vienen en pagina.props.auth
const usuario = computed(() => pagina.props.auth.user);
const rolActual = computed(() => pagina.props.auth.rol ?? '');

// Para el menú móvil (abrir/cerrar)
const menuAbierto = ref(false);

// Props que recibe el layout
defineProps({
    title: String,
});

// ── Definición del menú por rol ──────────────────────────────────────────────
// Cada item tiene: label, href, y roles[] que pueden verlo.

// Si roles está vacío, lo ven todos.
const itemsMenu = [
    {
        label: 'Dashboard',
        href: '/dashboard',
        roles: ['Administrador', 'Controller', 'Colaborador', 'Comercial'],
        icono: '🏠',
    },
    {
        label: 'Usuarios',
        href: '/usuarios',
        roles: ['Administrador'],          // solo Administrador
        icono: '👥',
    },
    {
        label: 'Certificaciones',
        href: '/certificaciones',
        roles: ['Administrador', 'Controller', 'Colaborador', 'Comercial'],
        icono: '🏅',
    },
    {
        label: 'Mentorías',
        href: '/mentorias',
        roles: ['Administrador', 'Controller', 'Colaborador'],
        icono: '📚',
    },
    {
        label: 'Reportes',
        href: '/reportes',
        roles: ['Administrador', 'Controller', 'Comercial'],
        icono: '📊',
    },
];

// Filtra los items según el rol del usuario autenticado
const menuVisible = computed(() =>
    itemsMenu.filter(item => item.roles.includes(rolActual.value))
);
</script>

<template>
    <div class="min-h-screen bg-gray-100">

        <nav class="bg-white border-b border-gray-200 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 flex justify-between items-center h-16">

                <!-- Logo / Nombre del sistema -->
                <div class="flex items-center gap-3">
                    <span class="text-xl font-bold text-blue-700">MeCert</span>
                    <span class="text-gray-400 text-sm hidden sm:block">
                        Datagrama Comunicaciones S.A.
                    </span>
                </div>

                <!-- Menú principal (desktop) -->
                <div class="hidden md:flex gap-1">
                    <Link
                        v-for="item in menuVisible"
                        :key="item.href"
                        :href="item.href"
                        class="flex items-center gap-1 px-3 py-2 rounded text-sm text-gray-600
                               hover:bg-blue-50 hover:text-blue-700 transition-colors"
                        :class="{
                            'bg-blue-50 text-blue-700 font-medium':
                                $page.url.startsWith(item.href)
                        }"
                    >
                        <span>{{ item.icono }}</span>
                        <span>{{ item.label }}</span>
                    </Link>
                </div>

                <!-- Info del usuario + logout (desktop) -->
                <div class="hidden md:flex items-center gap-3">
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-700">
                            {{ usuario?.name }} {{ usuario?.primer_apellido }}
                        </p>
                        <p class="text-xs text-gray-400">{{ rolActual }}</p>
                    </div>
                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        class="text-xs text-gray-500 hover:text-red-600 border border-gray-200
                               px-3 py-1 rounded hover:border-red-300 transition-colors"
                    >
                        Salir
                    </Link>
                </div>

                <!-- Botón hamburguesa (móvil) -->
                <button
                    @click="menuAbierto = !menuAbierto"
                    class="md:hidden p-2 rounded text-gray-500 hover:bg-gray-100"
                >
                    <span v-if="!menuAbierto">☰</span>
                    <span v-else>✕</span>
                </button>
            </div>

            <!-- Menú móvil desplegable -->
            <div v-if="menuAbierto" class="md:hidden border-t border-gray-100 bg-white px-4 py-2">
                <Link
                    v-for="item in menuVisible"
                    :key="item.href"
                    :href="item.href"
                    class="flex items-center gap-2 px-3 py-3 rounded text-sm text-gray-600
                           hover:bg-blue-50 hover:text-blue-700 border-b border-gray-50"
                    @click="menuAbierto = false"
                >
                    <span>{{ item.icono }}</span>
                    <span>{{ item.label }}</span>
                </Link>

                <!-- Usuario y logout en móvil -->
                <div class="pt-3 pb-2 flex justify-between items-center">
                    <div>
                        <p class="text-sm font-medium text-gray-700">
                            {{ usuario?.name }} {{ usuario?.primer_apellido }}
                        </p>
                        <p class="text-xs text-gray-400">{{ rolActual }}</p>
                    </div>
                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        class="text-xs text-red-500 border border-red-200 px-3 py-1 rounded"
                    >
                        Salir
                    </Link>
                </div>
            </div>
        </nav>

        <!-- ── TÍTULO DE PÁGINA (opcional) ──────────────────────────── -->
        <header v-if="title" class="bg-white border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 py-4">
                <h1 class="text-lg font-semibold text-gray-800">{{ title }}</h1>
            </div>
        </header>

        <!-- ── CONTENIDO PRINCIPAL ───────────────────────────────────── -->
        <!-- Aquí se inyecta el componente hijo (Index.vue, Create.vue, etc.) -->
        <main class="max-w-7xl mx-auto py-6 px-4">
            <slot />
        </main>

    </div>
</template>