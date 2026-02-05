import api from './api';

const authService = {
    async getCsrfCookie() {
        const baseUrl = import.meta.env.VITE_API_URL?.replace('/api', '') || 'http://127.0.0.1:8000';
        await api.get('/sanctum/csrf-cookie', { baseURL: baseUrl });
    },

    async login(credentials) {
        await this.getCsrfCookie();
        const response = await api.post('/login', credentials);
        return response.data;
    },

    async register(userData) {
        await this.getCsrfCookie();
        const response = await api.post('/register', userData);
        return response.data;
    },

    async logout() {
        const response = await api.post('/logout');
        return response.data;
    },

    async fetchUser() {
        const response = await api.get('/user');
        return response.data;
    }
};

export default authService;
