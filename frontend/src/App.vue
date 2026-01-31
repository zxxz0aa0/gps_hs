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
    flex: 1;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}
</style>
