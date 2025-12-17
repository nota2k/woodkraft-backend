<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Gestion des Clients</h1>
            <router-link
                to="/admin/users/new"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
            >
                + Nouveau Client
            </router-link>
        </div>

        <!-- Recherche -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <input
                v-model="search"
                type="text"
                placeholder="Rechercher un client..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                @input="debounceSearch"
            />
        </div>

        <!-- Message d'erreur -->
        <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ error }}
        </div>

        <!-- Message de chargement -->
        <div v-if="loading" class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
            Chargement des clients...
        </div>

        <!-- Tableau des clients -->
        <div v-if="!loading" class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date d'inscription</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ user.name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ user.email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(user.created_at) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <router-link
                                :to="`/admin/users/${user.id}/edit`"
                                class="text-blue-600 hover:text-blue-900 mr-4"
                            >
                                Modifier
                            </router-link>
                            <button
                                @click="deleteUser(user.id)"
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
import { userService } from '../../services/api';

const users = ref([]);
const search = ref('');
const pagination = ref(null);
const loading = ref(false);
const error = ref(null);
let searchTimeout = null;

const loadUsers = async (page = 1) => {
    loading.value = true;
    error.value = null;
    try {
        const params = { page, per_page: 15 };
        if (search.value) {
            params.search = search.value;
        }
        const response = await userService.getAll(params);
        console.log('Réponse clients:', response);
        if (response.data && response.data.data) {
            users.value = response.data.data;
            pagination.value = {
                current_page: response.data.current_page,
                last_page: response.data.last_page,
                per_page: response.data.per_page,
                total: response.data.total,
            };
        } else {
            users.value = Array.isArray(response.data) ? response.data : [];
            pagination.value = null;
        }
    } catch (error) {
        console.error('Erreur lors du chargement des clients:', error);
        error.value = 'Impossible de charger les clients. Vérifiez la console pour plus de détails.';
        users.value = [];
    } finally {
        loading.value = false;
    }
};

const debounceSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        loadUsers(1);
    }, 500);
};

const loadPage = (page) => {
    loadUsers(page);
};

const deleteUser = async (id) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce client ?')) {
        try {
            await userService.delete(id);
            loadUsers(pagination.value.current_page);
        } catch (error) {
            console.error('Erreur lors de la suppression:', error);
            alert('Erreur lors de la suppression du client');
        }
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('fr-FR');
};

onMounted(() => {
    loadUsers();
});
</script>

