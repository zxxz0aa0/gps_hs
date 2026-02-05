<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import authStore from '../stores/authStore';

const router = useRouter();
const form = ref({ name: '', password: '' });
const errorMessage = ref('');
const isSubmitting = ref(false);

async function handleLogin() {
    errorMessage.value = '';
    isSubmitting.value = true;

    try {
        await authStore.login(form.value);
        router.push('/query');
    } catch (error) {
        errorMessage.value = error.response?.data?.message
            || error.response?.data?.errors?.name?.[0]
            || '登入失敗，請稍後再試';
    } finally {
        isSubmitting.value = false;
    }
}
</script>

<template>
    <div class="auth-container">
        <div class="auth-card">
            <h2>登入</h2>
            <form @submit.prevent="handleLogin">
                <div class="form-group">
                    <label for="name">帳號</label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        autocomplete="username"
                    />
                </div>
                <div class="form-group">
                    <label for="password">密碼</label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        required
                        autocomplete="current-password"
                    />
                </div>
                <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
                <button type="submit" :disabled="isSubmitting">
                    {{ isSubmitting ? '登入中...' : '登入' }}
                </button>
            </form>
            <!--p class="switch-link">
                還沒有帳號？<router-link to="/register">註冊</router-link>
            </p>-->
        </div>
    </div>
</template>

<style scoped>
.auth-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100%;
    background: linear-gradient(135deg, #FFF8F0 0%, #FFE8D6 50%, #FFDAB9 100%);
}

.auth-card {
    background: rgba(255, 255, 255, 0.95);
    padding: 2.5rem;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(210, 140, 90, 0.15);
    width: 100%;
    max-width: 380px;
    border: 1px solid rgba(255, 200, 150, 0.3);
}

.auth-card h2 {
    text-align: center;
    margin-bottom: 2rem;
    color: #5D4037;
    font-weight: 600;
    font-size: 1.75rem;
}

.form-group {
    margin-bottom: 1.25rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: #6D4C41;
    font-weight: 500;
    font-size: 0.9rem;
}

.form-group input {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 1.5px solid #E8D5C4;
    border-radius: 10px;
    font-size: 1rem;
    box-sizing: border-box;
    background: #FFFAF6;
    color: #4E342E;
    transition: all 0.2s ease;
}

.form-group input:focus {
    outline: none;
    border-color: #E07B39;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(224, 123, 57, 0.1);
}

.form-group input::placeholder {
    color: #BCAAA4;
}

.error {
    color: #C62828;
    font-size: 0.875rem;
    margin-bottom: 1rem;
    padding: 0.5rem 0.75rem;
    background: #FFEBEE;
    border-radius: 6px;
}

button[type="submit"] {
    width: 100%;
    padding: 0.9rem;
    background: linear-gradient(135deg, #E07B39 0%, #D2691E 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.25s ease;
    margin-top: 0.5rem;
}

button[type="submit"]:hover:not(:disabled) {
    background: linear-gradient(135deg, #C96A2B 0%, #B8581A 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(210, 105, 30, 0.3);
}

button[type="submit"]:disabled {
    background: #D7CCC8;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.switch-link {
    text-align: center;
    margin-top: 1.5rem;
    color: #8D6E63;
    font-size: 0.9rem;
}

.switch-link a {
    color: #D2691E;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s ease;
}

.switch-link a:hover {
    color: #E07B39;
    text-decoration: underline;
}
</style>
