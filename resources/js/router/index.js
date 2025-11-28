import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/login',
            name: 'login',
            component: () => import('../views/Login.vue'),
            meta: { guest: true },
        },
        {
            path: '/',
            component: () => import('../layouts/MainLayout.vue'),
            meta: { requiresAuth: true },
            children: [
                {
                    path: '',
                    name: 'dashboard',
                    component: () => import('../views/Dashboard.vue'),
                },
                {
                    path: 'trips',
                    name: 'trips.index',
                    component: () => import('../views/trips/TripsList.vue'),
                },
                {
                    path: 'trips/create',
                    name: 'trips.create',
                    component: () => import('../views/trips/TripForm.vue'),
                },
                {
                    path: 'trips/:id/edit',
                    name: 'trips.edit',
                    component: () => import('../views/trips/TripForm.vue'),
                },
                {
                    path: 'drivers',
                    name: 'drivers.index',
                    component: () => import('../views/drivers/DriversList.vue'),
                },
                {
                    path: 'users',
                    name: 'users.index',
                    component: () => import('../views/users/UsersList.vue'),
                    meta: { adminOnly: true },
                },
                {
                    path: 'reports',
                    name: 'reports',
                    component: () => import('../views/reports/ReportsView.vue'),
                },
            ],
        },
    ],
});

router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore();

    if (!authStore.user && !authStore.loading) {
        await authStore.fetchUser();
    }

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        next({ name: 'login' });
    } else if (to.meta.guest && authStore.isAuthenticated) {
        next({ name: 'dashboard' });
    } else if (to.meta.adminOnly && !authStore.isAdmin) {
        next({ name: 'dashboard' });
    } else {
        next();
    }
});

export default router;
