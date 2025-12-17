<template>
    <div class="min-h-screen bg-gray-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-lg shadow-lg">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Connexion Admin
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Woodkraft Backoffice
                </p>
            </div>
            <form class="mt-8 space-y-6" @submit.prevent="handleLogin">
                <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ error }}
                </div>
                
                <div class="rounded-md shadow-sm -space-y-px">
                    <div>
                        <label for="email" class="sr-only">Email</label>
                        <input
                            id="email"
                            v-model="form.email"
                            name="email"
                            type="email"
                            autocomplete="email"
                            required
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                            placeholder="Adresse email"
                        />
                    </div>
                    <div>
                        <label for="password" class="sr-only">Mot de passe</label>
                        <input
                            id="password"
                            v-model="form.password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            required
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                            placeholder="Mot de passe"
                        />
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input
                            id="remember"
                            v-model="form.remember"
                            name="remember"
                            type="checkbox"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        />
                        <label for="remember" class="ml-2 block text-sm text-gray-900">
                            Se souvenir de moi
                        </label>
                    </div>
                </div>

                <div>
                    <button
                        type="submit"
                        :disabled="loading"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="loading">Connexion...</span>
                        <span v-else>Se connecter</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { authService } from '../../services/api';

const router = useRouter();

const form = ref({
    email: '',
    password: '',
    remember: false,
});

const loading = ref(false);
const error = ref(null);

const handleLogin = async () => {
    loading.value = true;
    error.value = null;

    try {
        const response = await authService.login(form.value);
        console.log('Connexion réussie:', response.data);
        
        // Rediriger vers le dashboard
        router.push('/admin');
    } catch (err) {
        console.error('Erreur de connexion:', err);
        if (err.response && err.response.data) {
            const errors = err.response.data.errors || err.response.data.message;
            error.value = typeof errors === 'string' ? errors : 'Les identifiants sont incorrects.';
        } else {
            error.value = 'Une erreur est survenue lors de la connexion.';
        }
    } finally {
        loading.value = false;
    }
};
</script>

