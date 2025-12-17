<template>
    <div>
        <h1 class="text-3xl font-bold mb-8">Tableau de bord</h1>
        
        <!-- Message d'erreur -->
        <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ error }}
        </div>
        
        <!-- Message de chargement -->
        <div v-if="loading" class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
            Chargement des statistiques...
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-gray-500 text-sm font-medium mb-2">Total Produits</h3>
                <p class="text-3xl font-bold">{{ stats.products || 0 }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-gray-500 text-sm font-medium mb-2">Total Commandes</h3>
                <p class="text-3xl font-bold">{{ stats.orders || 0 }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-gray-500 text-sm font-medium mb-2">Total Clients</h3>
                <p class="text-3xl font-bold">{{ stats.users || 0 }}</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { productService, orderService, userService } from '../../services/api';

const stats = ref({
    products: 0,
    orders: 0,
    users: 0,
});

const loading = ref(true);
const error = ref(null);

onMounted(async () => {
    console.log('📊 Chargement du dashboard...');
    try {
        console.log('📊 Tentative de chargement des statistiques...');
        const [productsRes, ordersRes, usersRes] = await Promise.all([
            productService.getAll({ per_page: 1 }),
            orderService.getAll({ per_page: 1 }),
            userService.getAll({ per_page: 1 }),
        ]);
        
        console.log('📊 Réponses reçues:', {
            products: productsRes,
            orders: ordersRes,
            users: usersRes,
        });
        
        stats.value = {
            products: productsRes.data?.total || productsRes.data?.length || 0,
            orders: ordersRes.data?.total || ordersRes.data?.length || 0,
            users: usersRes.data?.total || usersRes.data?.length || 0,
        };
        
        console.log('📊 Statistiques calculées:', stats.value);
    } catch (error) {
        console.error('❌ Erreur lors du chargement des statistiques:', error);
        error.value = 'Impossible de charger les statistiques';
    } finally {
        loading.value = false;
    }
});
</script>

