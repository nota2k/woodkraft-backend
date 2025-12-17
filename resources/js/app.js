import './bootstrap';
import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import App from './App.vue';
import AdminLayout from './components/admin/AdminLayout.vue';
import Login from './pages/admin/Login.vue';
import ProductsList from './pages/admin/ProductsList.vue';
import ProductForm from './pages/admin/ProductForm.vue';
import OrdersList from './pages/admin/OrdersList.vue';
import OrderDetail from './pages/admin/OrderDetail.vue';
import UsersList from './pages/admin/UsersList.vue';
import UserForm from './pages/admin/UserForm.vue';
import Dashboard from './pages/admin/Dashboard.vue';
import { authService } from './services/api';

const routes = [
    {
        path: '/admin/login',
        name: 'login',
        component: Login,
    },
    {
        path: '/admin',
        component: AdminLayout,
        children: [
            {
                path: '',
                name: 'dashboard',
                component: Dashboard,
            },
            {
                path: 'products',
                name: 'products',
                component: ProductsList,
            },
            {
                path: 'products/new',
                name: 'product-new',
                component: ProductForm,
            },
            {
                path: 'products/:id/edit',
                name: 'product-edit',
                component: ProductForm,
                props: true,
            },
            {
                path: 'orders',
                name: 'orders',
                component: OrdersList,
            },
            {
                path: 'orders/:id',
                name: 'order-detail',
                component: OrderDetail,
                props: true,
            },
            {
                path: 'users',
                name: 'users',
                component: UsersList,
            },
            {
                path: 'users/new',
                name: 'user-new',
                component: UserForm,
            },
            {
                path: 'users/:id/edit',
                name: 'user-edit',
                component: UserForm,
                props: true,
            },
        ],
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Garde de route pour l'authentification
router.beforeEach(async (to, from, next) => {
    // Toutes les routes /admin nécessitent une authentification sauf /admin/login
    const isAdminRoute = to.path.startsWith('/admin');
    const isLoginPage = to.path === '/admin/login';
    
    if (isAdminRoute && !isLoginPage) {
        try {
            // Vérifier si l'utilisateur est connecté
            const response = await authService.getUser();
            if (response.data && response.data.user) {
                next();
            } else {
                next('/admin/login');
            }
        } catch (error) {
            // Si non authentifié, rediriger vers login
            console.log('Non authentifié, redirection vers login');
            next('/admin/login');
        }
    } else if (isLoginPage) {
        // Si déjà sur la page login, vérifier si déjà connecté pour éviter de rester sur login
        try {
            const response = await authService.getUser();
            if (response.data && response.data.user) {
                next('/admin');
            } else {
                next();
            }
        } catch (error) {
            next();
        }
    } else {
        next();
    }
});

// Ne monter l'app que si on est sur la page admin
if (document.getElementById('app')) {
    const app = createApp(App);
    app.use(router);
    app.mount('#app');
}
