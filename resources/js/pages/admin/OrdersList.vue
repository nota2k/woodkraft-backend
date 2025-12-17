<template>
    <div>
        <h1 class="text-3xl font-bold mb-6">Gestion des Commandes</h1>

        <!-- Recherche et filtres -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <div class="flex gap-4">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Rechercher une commande..."
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    @input="debounceSearch"
                />
                <select
                    v-model="statusFilter"
                    @change="loadOrders(1)"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">Tous les statuts</option>
                    <option value="pending">En attente</option>
                    <option value="processing">En traitement</option>
                    <option value="shipped">Expédiée</option>
                    <option value="delivered">Livrée</option>
                    <option value="cancelled">Annulée</option>
                </select>
            </div>
        </div>

        <!-- Message d'erreur -->
        <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ error }}
        </div>

        <!-- Message de chargement -->
        <div v-if="loading" class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
            Chargement des commandes...
        </div>

        <!-- Tableau des commandes -->
        <div v-if="!loading" class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N° Commande</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="order in orders" :key="order.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ order.order_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ order.customer_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(order.created_at) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatPrice(order.total_amount) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span :class="getStatusClass(order.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                                {{ getStatusLabel(order.status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <router-link
                                :to="`/admin/orders/${order.id}`"
                                class="text-blue-600 hover:text-blue-900"
                            >
                                Voir
                            </router-link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="pagination" class="mt-6 flex justify-center">
            <div class="flex gap-2">
                <button
                    v-if="pagination.current_page > 1"
                    @click="loadOrders(pagination.current_page - 1)"
                    class="px-4 py-2 border rounded-lg hover:bg-gray-50"
                >
                    Précédent
                </button>
                <span class="px-4 py-2">
                    Page {{ pagination.current_page }} sur {{ pagination.last_page }}
                </span>
                <button
                    v-if="pagination.current_page < pagination.last_page"
                    @click="loadOrders(pagination.current_page + 1)"
                    class="px-4 py-2 border rounded-lg hover:bg-gray-50"
                >
                    Suivant
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { orderService } from '../../services/api';

const orders = ref([]);
const search = ref('');
const statusFilter = ref('');
const pagination = ref(null);
const loading = ref(false);
const error = ref(null);
let searchTimeout = null;

const loadOrders = async (page = 1) => {
    loading.value = true;
    error.value = null;
    try {
        const params = { page, per_page: 15 };
        if (search.value) {
            params.search = search.value;
        }
        if (statusFilter.value) {
            params.status = statusFilter.value;
        }
        const response = await orderService.getAll(params);
        console.log('Réponse commandes:', response);
        if (response.data && response.data.data) {
            orders.value = response.data.data;
            pagination.value = {
                current_page: response.data.current_page,
                last_page: response.data.last_page,
                per_page: response.data.per_page,
                total: response.data.total,
            };
        } else {
            orders.value = Array.isArray(response.data) ? response.data : [];
            pagination.value = null;
        }
    } catch (error) {
        console.error('Erreur lors du chargement des commandes:', error);
        error.value = 'Impossible de charger les commandes. Vérifiez la console pour plus de détails.';
        orders.value = [];
    } finally {
        loading.value = false;
    }
};

const debounceSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        loadOrders(1);
    }, 500);
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR',
    }).format(price);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('fr-FR');
};

const getStatusLabel = (status) => {
    const labels = {
        pending: 'En attente',
        processing: 'En traitement',
        shipped: 'Expédiée',
        delivered: 'Livrée',
        cancelled: 'Annulée',
    };
    return labels[status] || status;
};

const getStatusClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        processing: 'bg-blue-100 text-blue-800',
        shipped: 'bg-purple-100 text-purple-800',
        delivered: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

onMounted(() => {
    loadOrders();
});
</script>

