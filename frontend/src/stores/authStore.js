import { reactive, readonly } from 'vue';
import authService from '../services/authService';

const state = reactive({
    user: null,
    isAuthenticated: false,
    isLoading: true,
});

const authStore = {
    state: readonly(state),

    async login(credentials) {
        const { user } = await authService.login(credentials);
        state.user = user;
        state.isAuthenticated = true;
        localStorage.setItem('isLoggedIn', 'true');
        return user;
    },

    async register(userData) {
        const { user } = await authService.register(userData);
        state.user = user;
        state.isAuthenticated = true;
        localStorage.setItem('isLoggedIn', 'true');
        return user;
    },

    async logout() {
        await authService.logout();
        state.user = null;
        state.isAuthenticated = false;
        localStorage.removeItem('isLoggedIn');
    },

    async checkAuth() {
        state.isLoading = true;

        if (!localStorage.getItem('isLoggedIn')) {
            state.isLoading = false;
            return false;
        }

        try {
            const { user } = await authService.fetchUser();
            state.user = user;
            state.isAuthenticated = true;
            return true;
        } catch {
            state.user = null;
            state.isAuthenticated = false;
            localStorage.removeItem('isLoggedIn');
            return false;
        } finally {
            state.isLoading = false;
        }
    },

    clearAuth() {
        state.user = null;
        state.isAuthenticated = false;
        localStorage.removeItem('isLoggedIn');
    }
};

export default authStore;
