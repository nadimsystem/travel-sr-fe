import { createRouter, createWebHistory } from 'vue-router';
import Dashboard from '../views/Dashboard.vue';
import Contacts from '../views/Contacts.vue';
import Broadcast from '../views/Broadcast.vue';
import Login from '../views/Login.vue';

const routes = [
    { path: '/', redirect: '/dashboard' },
    { path: '/login', component: Login, meta: { guest: true } },
    { path: '/dashboard', component: Dashboard, meta: { requiresAuth: true } },
    { path: '/contacts', component: Contacts, meta: { requiresAuth: true } },
    { path: '/broadcast', component: Broadcast, meta: { requiresAuth: true } },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Navigation Guard (Mock for now, will implement real auth check later)
router.beforeEach((to, from, next) => {
    const isAuthenticated = localStorage.getItem('user');
    if (to.meta.requiresAuth && !isAuthenticated) {
        next('/login');
    } else if (to.meta.guest && isAuthenticated) {
        next('/dashboard');
    } else {
        next();
    }
});

export default router;
