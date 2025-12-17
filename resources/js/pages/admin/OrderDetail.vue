<template>
    <div>
        <div class="mb-6">
            <router-link to="/admin/orders" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">
                ← Retour à la liste
            </router-link>
            <h1 class="text-3xl font-bold">Détails de la commande {{ order.order_number }}</h1>
        </div>

        <div v-if="loading" class="text-center py-8">Chargement...</div>

        <div v-else-if="order" class="space-y-6">
            <!-- Informations de la commande -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold mb-4">Informations de la commande</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm text-gray-500">Statut</label>
                        <select
                            v-model="order.status"
                            @change="updateStatus"
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="pending">En attente</option>
                            <option value="processing">En traitement</option>
                            <option value="shipped">Expédiée</option>
                            <option value="delivered">Livrée</option>
                            <option value="cancelled">Annulée</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">Montant total</label>
                        <p class="mt-1 text-lg font-semibold">{{ formatPrice(order.total_amount) }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">Date de commande</label>
                        <p class="mt-1">{{ formatDate(order.created_at) }}</p>
                    </div>
                </div>
            </div>

            <!-- Informations client -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold mb-4">Informations client</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm text-gray-500">Nom</label>
                        <p class="mt-1">{{ order.customer_name }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">Email</label>
                        <p class="mt-1">{{ order.customer_email }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">Téléphone</label>
                        <p class="mt-1">{{ order.customer_phone || 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- Adresses -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold mb-4">Adresses</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm text-gray-500">Adresse de livraison</label>
                        <p class="mt-1">{{ order.shipping_address }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">Adresse de facturation</label>
                        <p class="mt-1">{{ order.billing_address }}</p>
                    </div>
                </div>
            </div>

            <!-- Articles de la commande -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold mb-4">Articles</h2>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produit</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantité</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prix unitaire</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="item in order.items" :key="item.id">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 mr-4">
                                        <img
                                            v-if="item.product && item.product.images && item.product.images.length > 0"
                                            :src="item.product.images[0].image_path"
                                            :alt="item.product.title"
                                            class="h-10 w-10 rounded object-cover"
                                        />
                                        <div v-else class="h-10 w-10 bg-gray-200 rounded"></div>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ item.product ? item.product.title : 'Produit supprimé' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ item.quantity }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatPrice(item.unit_price) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ formatPrice(item.total_price) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { orderService } from '../../services/api';

const route = useRoute();
const order = ref(null);
const loading = ref(true);

const loadOrder = async () => {
    try {
        const response = await orderService.getById(route.params.id);
        order.value = response.data;
    } catch (error) {
        console.error('Erreur lors du chargement de la commande:', error);
    } finally {
        loading.value = false;
    }
};

const updateStatus = async () => {
    try {
        await orderService.update(route.params.id, {
            status: order.value.status,
        });
    } catch (error) {
        console.error('Erreur lors de la mise à jour:', error);
        alert('Erreur lors de la mise à jour du statut');
    }
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR',
    }).format(price);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

onMounted(() => {
    loadOrder();
});
</script>

