import axios from 'axios';

// Configuration pour MAMP
// Utilise l'URL de l'environnement ou l'URL relative par défaut
// Si VITE_API_URL n'est pas défini, utilise une URL relative qui fonctionnera avec le domaine actuel
const API_BASE_URL = import.meta.env.VITE_API_URL || (window.location.origin + '/api/v1');

console.log('🚀 Configuration API:', {
    baseURL: API_BASE_URL,
    env: import.meta.env.VITE_API_URL,
    currentURL: window.location.origin,
});

const api = axios.create({
    baseURL: API_BASE_URL,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
    withCredentials: false,
    timeout: 10000, // 10 secondes de timeout
});

// Intercepteur pour les requêtes
api.interceptors.request.use(
    config => {
        const fullUrl = (config.baseURL || '') + (config.url || '');
        console.log('🔵 Requête API:', config.method?.toUpperCase(), fullUrl);
        console.log('🔵 Paramètres:', config.params);
        console.log('🔵 Données:', config.data);
        return config;
    },
    error => {
        console.error('❌ Erreur de requête:', error);
        return Promise.reject(error);
    }
);

// Intercepteur pour les réponses
api.interceptors.response.use(
    response => {
        console.log('✅ Réponse API réussie:', response.status, response.config.url);
        console.log('✅ Données reçues:', response.data);
        return response;
    },
    error => {
        if (error.response) {
            console.error('❌ Erreur API (réponse):', error.response.status, error.response.data);
            console.error('❌ URL:', error.config?.baseURL + error.config?.url);
        } else if (error.request) {
            console.error('❌ Erreur de connexion (pas de réponse du serveur)');
            console.error('❌ URL tentée:', error.config?.baseURL + error.config?.url);
            console.error('❌ Requête:', error.request);
            console.error('❌ Vérifiez que le serveur MAMP est démarré et accessible');
        } else {
            console.error('❌ Erreur:', error.message);
        }
        return Promise.reject(error);
    }
);

// Service pour les produits
export const productService = {
    getAll: (params = {}) => api.get('/admin/products', { params }),
    getById: (id) => api.get(`/admin/products/${id}`),
    create: (data) => api.post('/admin/products', data),
    update: (id, data) => api.put(`/admin/products/${id}`, data),
    delete: (id) => api.delete(`/admin/products/${id}`),
};

// Service pour les commandes
export const orderService = {
    getAll: (params = {}) => api.get('/admin/orders', { params }),
    getById: (id) => api.get(`/admin/orders/${id}`),
    update: (id, data) => api.put(`/admin/orders/${id}`, data),
    delete: (id) => api.delete(`/admin/orders/${id}`),
};

// Service pour les utilisateurs/clients
export const userService = {
    getAll: (params = {}) => api.get('/admin/users', { params }),
    getById: (id) => api.get(`/admin/users/${id}`),
    create: (data) => api.post('/admin/users', data),
    update: (id, data) => api.put(`/admin/users/${id}`, data),
    delete: (id) => api.delete(`/admin/users/${id}`),
};

// Service pour les catégories
export const categoryService = {
    getAll: () => api.get('/categories'),
};

// Service pour les images
export const imageService = {
    upload: (file) => {
        const formData = new FormData();
        formData.append('image', file);
        return api.post('/admin/images/upload', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });
    },
    delete: (path) => api.delete('/admin/images/delete', { data: { path } }),
};

export default api;

