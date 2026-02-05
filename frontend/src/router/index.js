import { createRouter, createWebHistory } from 'vue-router';
import authStore from '../stores/authStore';

const routes = [
    {
        path: '/',
        redirect: '/query'
    },
    {
        path: '/login',
        name: 'Login',
        component: () => import('../views/LoginPage.vue'),
        meta: { guest: true }
    },
    {
        path: '/register',
        name: 'Register',
        component: () => import('../views/RegisterPage.vue'),
        meta: { guest: true }
    },
    {
        path: '/upload',
        name: 'Upload',
        component: () => import('../views/UploadPage.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/query',
        name: 'Query',
        component: () => import('../views/TrackQueryPage.vue'),
        meta: { requiresAuth: true }
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

let authChecked = false;

router.beforeEach(async (to, from, next) => {
    if (!authChecked) {
        await authStore.checkAuth();
        authChecked = true;
    }

    const isAuthenticated = authStore.state.isAuthenticated;

    if (to.meta.requiresAuth && !isAuthenticated) {
        return next({ name: 'Login', query: { redirect: to.fullPath } });
    }

    if (to.meta.guest && isAuthenticated) {
        return next({ name: 'Query' });
    }

    next();
});

export default router;
