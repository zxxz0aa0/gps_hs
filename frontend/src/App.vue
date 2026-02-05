<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import authStore from './stores/authStore';

const router = useRouter();
const isAuthenticated = computed(() => authStore.state.isAuthenticated);
const userName = computed(() => authStore.state.user?.name);
const isLoading = computed(() => authStore.state.isLoading);

async function handleLogout() {
    await authStore.logout();
    router.push('/login');
}
</script>

<template>
    <div id="app-layout">
        <nav v-if="isAuthenticated" class="navbar">
            <div class="brand"><span class="icon">&#128752;</span>大豐軌跡查詢系統</div>
            <div class="links">
                <router-link to="/upload" class="nav-link" active-class="active">上傳</router-link>
                <router-link to="/query" class="nav-link" active-class="active">查詢</router-link>
                <span class="user-name">{{ userName }}</span>
                <button class="logout-btn" @click="handleLogout">登出</button>
            </div>
        </nav>

        <main class="content">
            <div v-if="isLoading" class="loading">載入中...</div>
            <router-view v-else></router-view>
        </main>
    </div>
</template>

<style scoped>
#app-layout {
    display: flex;
    flex-direction: column;
    height: 100vh;
    overflow: hidden;
}

.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #333;
    padding: 0 2rem;
    height: 50px;
    color: white;
    flex-shrink: 0;
}

.brand {
    font-size: 1.2rem;
    font-weight: bold;
}

.icon {
    margin-right: 0.5rem;
}

.links {
    display: flex;
    gap: 20px;
    align-items: center;
}

.nav-link {
    color: #ccc;
    text-decoration: none;
    font-weight: 500;
}

.nav-link:hover {
    color: white;
}

.nav-link.active {
    color: #fff;
    border-bottom: 2px solid #FF8C00;
}

.user-name {
    color: #FF8C00;
    font-weight: 500;
    margin-left: 10px;
}

.logout-btn {
    background: transparent;
    border: 1px solid #ccc;
    color: #ccc;
    padding: 0.25rem 0.75rem;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.logout-btn:hover {
    background: #555;
    border-color: white;
    color: white;
}

.content {
    background-color: #f0f2f5;
    flex: 1;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.loading {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100%;
    color: #666;
    font-size: 1.2rem;
}
</style>
