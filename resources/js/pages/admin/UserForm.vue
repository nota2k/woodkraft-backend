<template>
    <div>
        <div class="mb-6">
            <router-link to="/admin/users" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">
                ← Retour à la liste
            </router-link>
            <h1 class="text-3xl font-bold">{{ isEdit ? 'Modifier le client' : 'Nouveau client' }}</h1>
        </div>

        <form @submit.prevent="submitForm" class="bg-white rounded-lg shadow p-6 space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nom *</label>
                <input
                    v-model="form.name"
                    type="text"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                <input
                    v-model="form.email"
                    type="email"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    {{ isEdit ? 'Nouveau mot de passe (laisser vide pour ne pas changer)' : 'Mot de passe *' }}
                </label>
                <input
                    v-model="form.password"
                    type="password"
                    :required="!isEdit"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
            </div>

            <div class="flex gap-4">
                <button
                    type="submit"
                    :disabled="loading"
                    class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
                >
                    {{ loading ? 'Enregistrement...' : 'Enregistrer' }}
                </button>
                <router-link
                    to="/admin/users"
                    class="bg-gray-200 text-gray-800 px-6 py-2 rounded-lg hover:bg-gray-300 transition-colors"
                >
                    Annuler
                </router-link>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { userService } from '../../services/api';

const route = useRoute();
const router = useRouter();
const loading = ref(false);
const isEdit = computed(() => !!route.params.id);

const form = ref({
    name: '',
    email: '',
    password: '',
});

const loadUser = async () => {
    if (isEdit.value) {
        try {
            const response = await userService.getById(route.params.id);
            const user = response.data;
            form.value = {
                name: user.name,
                email: user.email,
                password: '',
            };
        } catch (error) {
            console.error('Erreur lors du chargement du client:', error);
        }
    }
};

const submitForm = async () => {
    loading.value = true;
    try {
        const data = { ...form.value };
        if (isEdit.value && !data.password) {
            delete data.password;
        }
        if (isEdit.value) {
            await userService.update(route.params.id, data);
        } else {
            await userService.create(data);
        }
        router.push('/admin/users');
    } catch (error) {
        console.error('Erreur lors de l\'enregistrement:', error);
        alert('Erreur lors de l\'enregistrement du client');
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    loadUser();
});
</script>

