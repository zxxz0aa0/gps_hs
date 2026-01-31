<script setup>
import FileUploader from '../components/FileUploader.vue';
</script>

<template>
    <div class="page-container">
        <h2 style="color: #333;">檔案上傳</h2>
        <p class="description">請點選上傳按鈕，選擇要上傳的GPS Excel檔案。</p>
        <FileUploader />
    </div>
</template>

<style scoped>
.page-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}
.description {
    margin-bottom: 20px;
    color: #666;
}
</style>
