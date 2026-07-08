<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    Award,
    BarChart3,
    BookOpen,
    CircleHelp,
    Home,
    Info,
    Menu,
    ShieldCheck,
    Users,
    X,
} from 'lucide-vue-next';

const pagina = usePage();

const usuario = computed(() => pagina.props.auth.user);
const rolActual = computed(() => pagina.props.auth.rol ?? '');
const permisosActuales = computed(() => pagina.props.auth.permisos ?? []);
const menuAbierto = ref(false);

defineProps({
    title: String,
});

// permiso: null = visible para cualquier usuario autenticado (sin control por módulo)
const itemsMenu = [
    {
        label: 'Dashboard',
        href: '/dashboard',
        permiso: null,
        icono: Home,
    },
    {
        label: 'Usuarios',
        href: '/usuarios',
        soloAdministrador: true,
        icono: Users,
    },
    {
        label: 'Roles',
        href: '/roles',
        soloAdministrador: true,
        icono: ShieldCheck,
    },
    {
        label: 'Certificaciones',
        href: '/certificaciones',
        permiso: 'modulo.certificaciones',
        icono: Award,
    },
    {
        label: 'Mentorias',
        href: '/mentorias',
        permiso: 'modulo.mentorias',
        icono: BookOpen,
    },
    {
        label: 'Reportes',
        href: '/reportes',
        permiso: 'modulo.reportes',
        icono: BarChart3,
    },
    {
        label: 'Acerca de',
        href: '/acerca-de',
        permiso: 'modulo.acerca',
        icono: Info,
    },
    {
        label: 'Ayuda',
        href: '/ayuda',
        permiso: 'modulo.ayuda',
        icono: CircleHelp,
    },
];

const menuVisible = computed(() =>
    itemsMenu.filter((item) => {
        if (item.soloAdministrador) {
            return rolActual.value === 'Administrador';
        }

        return !item.permiso || permisosActuales.value.includes(item.permiso);
    }),
);

const estaActivo = (href) => pagina.url === href || pagina.url.startsWith(`${href}/`);
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <nav class="border-b border-gray-200 bg-white shadow-sm">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4">
                <div class="flex items-center gap-3">
                    <span class="text-xl font-bold text-blue-700">MeCert</span>
                    <span class="hidden text-sm text-gray-400 sm:block">
                        Datagrama Comunicaciones S.A.
                    </span>
                </div>

                <div class="hidden gap-1 md:flex">
                    <Link
                        v-for="item in menuVisible"
                        :key="item.href"
                        :href="item.href"
                        class="flex items-center gap-1 rounded px-3 py-2 text-sm text-gray-600 transition-colors hover:bg-blue-50 hover:text-blue-700"
                        :class="{
                            'bg-blue-50 font-medium text-blue-700': estaActivo(item.href),
                        }"
                    >
                        <component :is="item.icono" class="h-4 w-4" />
                        <span>{{ item.label }}</span>
                    </Link>
                </div>

                <div class="hidden items-center gap-3 md:flex">
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
                        class="rounded border border-gray-200 px-3 py-1 text-xs text-gray-500 transition-colors hover:border-red-300 hover:text-red-600"
                    >
                        Salir
                    </Link>
                </div>

                <button
                    type="button"
                    @click="menuAbierto = !menuAbierto"
                    class="rounded p-2 text-gray-500 hover:bg-gray-100 md:hidden"
                    aria-label="Abrir menu"
                >
                    <Menu v-if="!menuAbierto" class="h-5 w-5" />
                    <X v-else class="h-5 w-5" />
                </button>
            </div>

            <div v-if="menuAbierto" class="border-t border-gray-100 bg-white px-4 py-2 md:hidden">
                <Link
                    v-for="item in menuVisible"
                    :key="item.href"
                    :href="item.href"
                    class="flex items-center gap-2 rounded border-b border-gray-50 px-3 py-3 text-sm text-gray-600 hover:bg-blue-50 hover:text-blue-700"
                    :class="{
                        'bg-blue-50 font-medium text-blue-700': estaActivo(item.href),
                    }"
                    @click="menuAbierto = false"
                >
                    <component :is="item.icono" class="h-4 w-4" />
                    <span>{{ item.label }}</span>
                </Link>

                <div class="flex items-center justify-between pb-2 pt-3">
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
                        class="rounded border border-red-200 px-3 py-1 text-xs text-red-500"
                    >
                        Salir
                    </Link>
                </div>
            </div>
        </nav>

        <header v-if="title" class="border-b border-gray-100 bg-white">
            <div class="mx-auto max-w-7xl px-4 py-4">
                <h1 class="text-lg font-semibold text-gray-800">{{ title }}</h1>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-6">
            <slot />
        </main>
    </div>
</template>
