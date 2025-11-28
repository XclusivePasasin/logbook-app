import { defineStore } from 'pinia';
import api from '../api/axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        loading: false,
    }),

    getters: {
        isAuthenticated: (state) => !!state.user,
        isAdmin: (state) => state.user?.role === 'admin',
    },

    actions: {
        async login(credentials) {
            await api.get('/sanctum/csrf-cookie');
            await api.post('/login', credentials);
            await this.fetchUser();
        },

        async logout() {
            await api.post('/logout');
            this.user = null;
        },

        async fetchUser() {
            this.loading = true;
            try {
                const response = await api.get('/user');
                this.user = response.data;
            } catch (error) {
                this.user = null;
            } finally {
                this.loading = false;
            }
        },
    },
});
