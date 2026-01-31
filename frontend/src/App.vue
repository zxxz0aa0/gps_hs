<script setup>
// Layout wrapper can go here
</script>

<template>
  <div id="app-layout">
    <nav class="navbar">
      <div class="brand"><span class="icon">🛰️</span>大豐軌跡查詢系統</div>
      <div class="links">
        <router-link to="/upload" class="nav-link" active-class="active">上傳</router-link>
        <router-link to="/query" class="nav-link" active-class="active">查詢</router-link>
      </div>
    </nav>
    
    <main class="content">
      <router-view></router-view>
    </main>
  </div>
</template>

<style scoped>
.navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: #333;
  padding: 1rem 2rem;
  color: white;
}
.brand {
    font-size: 1.2rem;
    font-weight: bold;
}
.links {
    display: flex;
    gap: 20px;
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
.content {
    background-color: #f0f2f5;
    min-height: calc(100vh - 60px);
}
</style>
