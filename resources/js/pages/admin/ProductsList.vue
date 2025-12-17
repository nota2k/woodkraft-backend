<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Gestion des Produits</h1>
            <router-link
                to="/admin/products/new"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
            >
                + Nouveau Produit
            </router-link>
        </div>

        <!-- Recherche et filtres -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <div class="flex gap-4">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Rechercher un produit..."
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    @input="debounceSearch"
                />
            </div>
        </div>

        <!-- Message d'erreur -->
        <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ error }}
        </div>

        <!-- Message de chargement -->
        <div v-if="loading" class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
            Chargement des produits...
        </div>

        <!-- Tableau des produits -->
        <div v-if="!loading" class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Référence</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prix</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="product in products" :key="product.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <img
                                        v-if="product.images && product.images.length > 0"
                                        :src="product.images[0].image_path"
                                        :alt="product.title"
                                        class="h-10 w-10 rounded object-cover"
                                    />
                                    <div v-else class="h-10 w-10 bg-gray-200 rounded"></div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ product.title }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ product.reference }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatPrice(product.price) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ product.quantity }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <router-link
                                :to="`/admin/products/${product.id}/edit`"
                                class="text-blue-600 hover:text-blue-900 mr-4"
                            >
                                Modifier
                            </router-link>
                            <button
                                @click="deleteProduct(product.id)"
                                class="text-red-600 hover:text-red-900"
                            >
                                Supprimer
                            </button>
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
                    @click="loadPage(pagination.current_page - 1)"
                    class="px-4 py-2 border rounded-lg hover:bg-gray-50"
                >
                    Précédent
                </button>
                <span class="px-4 py-2">
                    Page {{ pagination.current_page }} sur {{ pagination.last_page }}
                </span>
                <button
                    v-if="pagination.current_page < pagination.last_page"
                    @click="loadPage(pagination.current_page + 1)"
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
import { productService } from '../../services/api';

const products = ref([]);
const search = ref('');
const pagination = ref(null);
let searchTimeout = null;

const loading = ref(false);
const error = ref(null);

const loadProducts = async (page = 1) => {
    loading.value = true;
    error.value = null;
    try {
        const params = { page, per_page: 15 };
        if (search.value) {
            params.search = search.value;
        }
        console.log('🔄 Chargement des produits, page:', page);
        const response = await productService.getAll(params);
        console.log('📦 Réponse complète:', response);
        console.log('📦 Type de réponse:', typeof response);
        console.log('📦 response.data:', response.data);
        console.log('📦 response.data.data:', response.data?.data);
        
        if (response && response.data) {
            if (response.data.data && Array.isArray(response.data.data)) {
                // Réponse paginée
                products.value = response.data.data;
                pagination.value = {
                    current_page: response.data.current_page,
                    last_page: response.data.last_page,
                    per_page: response.data.per_page,
                    total: response.data.total,
                };
                console.log('✅ Produits chargés (paginés):', products.value.length);
            } else if (Array.isArray(response.data)) {
                // Réponse directe (tableau)
                products.value = response.data;
                pagination.value = null;
                console.log('✅ Produits chargés (direct):', products.value.length);
            } else {
                console.warn('⚠️ Format de réponse inattendu:', response.data);
                products.value = [];
            }
        } else {
            console.error('❌ Réponse invalide:', response);
            products.value = [];
        }
    } catch (error) {
        console.error('Erreur lors du chargement des produits:', error);
        error.value = 'Impossible de charger les produits. Vérifiez la console pour plus de détails.';
        products.value = [];
    } finally {
        loading.value = false;
    }
};

const debounceSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        loadProducts(1);
    }, 500);
};

const loadPage = (page) => {
    loadProducts(page);
};

const deleteProduct = async (id) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')) {
        try {
            await productService.delete(id);
            loadProducts(pagination.value.current_page);
        } catch (error) {
            console.error('Erreur lors de la suppression:', error);
            alert('Erreur lors de la suppression du produit');
        }
    }
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR',
    }).format(price);
};

onMounted(() => {
    loadProducts();
});
</script>

