<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 w-64 bg-gray-900 text-white">
            <div class="flex flex-col h-full">
                <div class="p-6 border-b border-gray-800">
                    <h1 class="text-2xl font-bold">Woodkraft Admin</h1>
                </div>
                <nav class="flex-1 p-4 space-y-2">
                    <router-link
                        to="/admin"
                        class="flex items-center px-4 py-3 rounded-lg hover:bg-gray-800 transition-colors"
                        active-class="bg-gray-800"
                    >
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Tableau de bord
                    </router-link>
                    <router-link
                        to="/admin/products"
                        class="flex items-center px-4 py-3 rounded-lg hover:bg-gray-800 transition-colors"
                        active-class="bg-gray-800"
                    >
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        Produits
                    </router-link>
                    <router-link
                        to="/admin/orders"
                        class="flex items-center px-4 py-3 rounded-lg hover:bg-gray-800 transition-colors"
                        active-class="bg-gray-800"
                    >
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Commandes
                    </router-link>
                    <router-link
                        to="/admin/users"
                        class="flex items-center px-4 py-3 rounded-lg hover:bg-gray-800 transition-colors"
                        active-class="bg-gray-800"
                    >
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Clients
                    </router-link>
                </nav>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="ml-64 p-8">
            <router-view />
        </main>
    </div>
</template>

<script setup>
</script>

