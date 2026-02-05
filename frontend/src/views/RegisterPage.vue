<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import authStore from '../stores/authStore';

const router = useRouter();
const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
});
const errorMessage = ref('');
const isSubmitting = ref(false);

async function handleRegister() {
    errorMessage.value = '';
    isSubmitting.value = true;

    try {
        await authStore.register(form.value);
        router.push('/upload');
    } catch (error) {
        const errors = error.response?.data?.errors;
        errorMessage.value = errors
            ? Object.values(errors).flat()[0]
            : '註冊失敗，請稍後再試';
    } finally {
        isSubmitting.value = false;
    }
}
</script>

<template>
    <div class="auth-container">
        <div class="auth-card">
            <h2>註冊</h2>
            <form @submit.prevent="handleRegister">
                <div class="form-group">
                    <label for="name">帳號 *</label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                    />
                </div>
                <div class="form-group">
                    <label for="email">Email（選填）</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                    />
                </div>
                <div class="form-group">
                    <label for="password">密碼 *</label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        required
                    />
                </div>
                <div class="form-group">
                    <label for="password_confirmation">確認密碼 *</label>
                    <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        required
                    />
                </div>
                <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
                <button type="submit" :disabled="isSubmitting">
                    {{ isSubmitting ? '註冊中...' : '註冊' }}
                </button>
            </form>
            <p class="switch-link">
                已有帳號？<router-link to="/login">登入</router-link>
            </p>
        </div>
    </div>
</template>

<style scoped>
.auth-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100%;
    background-color: #f0f2f5;
}

.auth-card {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 400px;
}

.auth-card h2 {
    text-align: center;
    margin-bottom: 1.5rem;
    color: #333;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: #555;
    font-weight: 500;
}

.form-group input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
    box-sizing: border-box;
}

.form-group input:focus {
    outline: none;
    border-color: #FF8C00;
}

.error {
    color: #dc3545;
    font-size: 0.875rem;
    margin-bottom: 1rem;
}

button[type="submit"] {
    width: 100%;
    padding: 0.75rem;
    background-color: #333;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color 0.2s;
}

button[type="submit"]:hover:not(:disabled) {
    background-color: #555;
}

button[type="submit"]:disabled {
    background-color: #999;
    cursor: not-allowed;
}

.switch-link {
    text-align: center;
    margin-top: 1rem;
    color: #666;
}

.switch-link a {
    color: #FF8C00;
    text-decoration: none;
}

.switch-link a:hover {
    text-decoration: underline;
}
</style>
